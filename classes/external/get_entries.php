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
 * External function to get wheel entries.
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
use core_external\external_multiple_structure;

class get_entries extends external_api {

    /**
     * Describe the parameters for the get_entries external function.
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
     * Get the active entries for a wheel instance.
     *
     * @param int $cmid Course module ID.
     * @param int $groupid Group ID (0 for all).
     * @return array The entries data.
     */

    public static function execute(int $cmid, int $groupid = 0): array {
        global $DB, $PAGE;

        $params = self::validate_parameters(self::execute_parameters(), [
            'cmid' => $cmid,
            'groupid' => $groupid,
        ]);

        [$course, $cm] = get_course_and_cm_from_cmid($params['cmid'], 'spinningwheel');
        $context = \context_module::instance($cm->id);
        self::validate_context($context);
        $PAGE->set_context($context);
        require_capability('mod/spinningwheel:view', $context);

        $spinningwheel = $DB->get_record('spinningwheel', ['id' => $cm->instance], '*', MUST_EXIST);

        require_once(__DIR__ . '/../../lib.php');
        $entries = spinningwheel_get_active_entries($spinningwheel, $context, $params['groupid']);

        $displaymode = (int)($spinningwheel->displaymode ?? 0);
        $isparticipant = ($spinningwheel->entrysource == SPINNINGWHEEL_SOURCE_PARTICIPANTS);

        $result = [];
        foreach ($entries as $entry) {
            $pictureurl = '';
            if ($isparticipant && $displaymode > 0 && !empty($entry->userrecord)) {
                $userpicture = new \core\output\user_picture($entry->userrecord);
                $userpicture->size = 100;
                $userpicture->link = false;
                $pictureurl = $userpicture->get_url($PAGE)->out(false);
            }
            $result[] = [
                'id' => $entry->id ?? 0,
                'text' => $entry->text,
                'picture' => $pictureurl,
            ];
        }

        return ['entries' => $result];
    }

    /**
     * Describe the return value for the get_entries external function.
     *
     * @return external_single_structure The return value definition.
     */

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'entries' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'Entry ID'),
                    'text' => new external_value(PARAM_TEXT, 'Entry text'),
                    'picture' => new external_value(PARAM_URL, 'Profile picture URL', VALUE_OPTIONAL, ''),
                ])
            ),
        ]);
    }
}
