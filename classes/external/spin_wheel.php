<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * External function to spin the wheel.
 *
 * @package    mod_spinningwheel
 * @copyright  2026 Andrea Juettner, andrea.juettner@eledia.de; AI-assisted by Claude (Anthropic).
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_spinningwheel\external;

defined('MOODLE_INTERNAL') || die();

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;

class spin_wheel extends external_api {

    /**
     * Describe the parameters for the spin_wheel external function.
     *
     * @return external_function_parameters The parameter definition.
     */

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
            'groupid' => new external_value(PARAM_INT, 'Group ID', VALUE_DEFAULT, 0),
        ]);
    }

    /**
     * Spin the wheel and return the selected entry.
     *
     * @param int $cmid Course module ID.
     * @param int $groupid Group ID (0 for all).
     * @return array The spin result.
     */

    public static function execute(int $cmid, int $groupid = 0): array {
        global $DB, $USER, $PAGE;

        $params = self::validate_parameters(self::execute_parameters(), [
            'cmid' => $cmid,
            'groupid' => $groupid,
        ]);

        [$course, $cm] = get_course_and_cm_from_cmid($params['cmid'], 'spinningwheel');
        $context = \context_module::instance($cm->id);
        self::validate_context($context);
        $PAGE->set_context($context);

        $spinningwheel = $DB->get_record('spinningwheel', ['id' => $cm->instance], '*', MUST_EXIST);

        $canspin = has_capability('mod/spinningwheel:spin', $context)
            || ($spinningwheel->allowstudentspin && has_capability('mod/spinningwheel:view', $context));

        if (!$canspin) {
            throw new \required_capability_exception($context, 'mod/spinningwheel:spin', 'nopermissions', '');
        }

        // Check max spins.
        if ($spinningwheel->maxspins > 0) {
            $spincount = $DB->count_records('spinningwheel_spins', ['wheelid' => $spinningwheel->id]);
            if ($spincount >= $spinningwheel->maxspins) {
                throw new \moodle_exception('maxspinsreached', 'spinningwheel');
            }
        }

        // Get active entries.
        require_once(__DIR__ . '/../../lib.php');
        $entries = spinningwheel_get_active_entries($spinningwheel, $context, $params['groupid']);

        if (empty($entries)) {
            throw new \moodle_exception('noentries', 'spinningwheel');
        }

        // Cryptographically secure random selection.
        $selectedindex = random_int(0, count($entries) - 1);
        $selected = $entries[$selectedindex];

        // Record the spin.
        $spin = new \stdClass();
        $spin->wheelid = $spinningwheel->id;
        $spin->userid = $USER->id;
        $spin->selectedentryid = $selected->id ?: null;
        $spin->selecteduserid = $selected->userid ?: null;
        $spin->selectedtext = $selected->text;
        $spin->groupid = $params['groupid'] ?: null;
        $spin->timecreated = time();
        $spin->id = $DB->insert_record('spinningwheel_spins', $spin);

        // Deactivate entry if removeafter is enabled (manual entries only).
        if ($spinningwheel->removeafter && !empty($selected->id)) {
            $DB->set_field('spinningwheel_entries', 'active', 0, ['id' => $selected->id]);
        }

        // Trigger event.
        $event = \mod_spinningwheel\event\wheel_spun::create([
            'objectid' => $spin->id,
            'context' => $context,
            'other' => [
                'wheelid' => $spinningwheel->id,
                'selectedtext' => $selected->text,
            ],
        ]);
        $event->trigger();

        // Update completion if needed.
        $completion = new \completion_info($course);
        if ($completion->is_enabled($cm) && $spinningwheel->completionspin) {
            $completion->update_state($cm, COMPLETION_COMPLETE);
        }

        // Generate picture URL for the selected entry (always for participant wheels).
        $pictureurl = '';
        if ($spinningwheel->entrysource == SPINNINGWHEEL_SOURCE_PARTICIPANTS
                && !empty($selected->userrecord)) {
            $userpicture = new \core\output\user_picture($selected->userrecord);
            $userpicture->size = 100;
            $userpicture->link = false;
            $pictureurl = $userpicture->get_url($PAGE)->out(false);
        }

        return [
            'selectedindex' => $selectedindex,
            'selectedtext' => $selected->text,
            'pictureurl' => $pictureurl,
            'entryid' => $selected->id ?? 0,
            'remainingcount' => count($entries) - ($spinningwheel->removeafter ? 1 : 0),
        ];
    }

    /**
     * Describe the return value for the spin_wheel external function.
     *
     * @return external_single_structure The return value definition.
     */

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'selectedindex' => new external_value(PARAM_INT, 'Index of the selected entry'),
            'selectedtext' => new external_value(PARAM_TEXT, 'Text of the selected entry'),
            'pictureurl' => new external_value(PARAM_URL, 'Profile picture URL', VALUE_OPTIONAL, ''),
            'entryid' => new external_value(PARAM_INT, 'ID of the selected entry'),
            'remainingcount' => new external_value(PARAM_INT, 'Number of remaining entries'),
        ]);
    }
}
