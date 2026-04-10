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
 * External function to clear spin history.
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

class clear_history extends external_api {

    /**
     * Describe the parameters for the clear_history external function.
     *
     * @return external_function_parameters The parameter definition.
     */

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
        ]);
    }

    /**
     * Clear all spin history for a wheel instance.
     *
     * @param int $cmid Course module ID.
     * @return array Success status.
     */

    public static function execute(int $cmid): array {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'cmid' => $cmid,
        ]);

        [$course, $cm] = get_course_and_cm_from_cmid($params['cmid'], 'spinningwheel');
        $context = \context_module::instance($cm->id);
        self::validate_context($context);

        require_capability('mod/spinningwheel:clearhistory', $context);

        $spinningwheel = $DB->get_record('spinningwheel', ['id' => $cm->instance], '*', MUST_EXIST);

        $DB->delete_records('spinningwheel_spins', ['wheelid' => $spinningwheel->id]);

        return ['success' => true];
    }

    /**
     * Describe the return value for the clear_history external function.
     *
     * @return external_single_structure The return value definition.
     */

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Whether the history was cleared successfully'),
        ]);
    }
}
