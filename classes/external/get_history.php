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
 * External function to get spin history.
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

class get_history extends external_api {

    /**
     * Describe the parameters for the get_history external function.
     *
     * @return external_function_parameters The parameter definition.
     */

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
        ]);
    }

    /**
     * Get the spin history for a wheel instance.
     *
     * @param int $cmid Course module ID.
     * @return array The spin history data.
     */

    public static function execute(int $cmid): array {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'cmid' => $cmid,
        ]);

        [$course, $cm] = get_course_and_cm_from_cmid($params['cmid'], 'spinningwheel');
        $context = \context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/spinningwheel:viewhistory', $context);

        $spinningwheel = $DB->get_record('spinningwheel', ['id' => $cm->instance], '*', MUST_EXIST);

        $namefields = implode(',', array_map(fn($f) => "u.$f", \core_user\fields::get_name_fields()));
        $sql = "SELECT s.id, s.selectedtext, s.timecreated, {$namefields}
                  FROM {spinningwheel_spins} s
                  JOIN {user} u ON u.id = s.userid
                 WHERE s.wheelid = :wheelid
              ORDER BY s.timecreated DESC";

        $spins = $DB->get_records_sql($sql, ['wheelid' => $spinningwheel->id], 0, 100);

        $result = [];
        foreach ($spins as $spin) {
            $result[] = [
                'id' => $spin->id,
                'spunby' => fullname($spin),
                'selectedtext' => $spin->selectedtext,
                'timecreated' => $spin->timecreated,
                'timeformatted' => userdate($spin->timecreated),
            ];
        }

        return ['spins' => $result];
    }

    /**
     * Describe the return value for the get_history external function.
     *
     * @return external_single_structure The return value definition.
     */

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'spins' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'Spin record ID'),
                    'spunby' => new external_value(PARAM_TEXT, 'Full name of user who spun'),
                    'selectedtext' => new external_value(PARAM_TEXT, 'Selected entry text'),
                    'timecreated' => new external_value(PARAM_INT, 'Unix timestamp'),
                    'timeformatted' => new external_value(PARAM_TEXT, 'Formatted date string'),
                ])
            ),
        ]);
    }
}
