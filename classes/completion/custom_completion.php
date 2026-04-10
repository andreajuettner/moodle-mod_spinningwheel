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

declare(strict_types=1);

namespace mod_spinningwheel\completion;

use core_completion\activity_custom_completion;

/**
 * Activity custom completion subclass for mod_spinningwheel.
 *
 * @package    mod_spinningwheel
 * @copyright  2026 Andrea Juettner, andrea.juettner@eledia.de; AI-assisted by Claude (Anthropic).
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class custom_completion extends activity_custom_completion {

    /**
     * Fetch the completion state for a given completion rule.
     *
     * @param string $rule The completion rule name.
     * @return int The completion state (COMPLETION_COMPLETE or COMPLETION_INCOMPLETE).
     */
    #[\Override]
    public function get_state(string $rule): int {
        global $DB;

        $this->validate_rule($rule);

        $status = $DB->record_exists('spinningwheel_spins', [
            'wheelid' => $this->cm->instance,
            'userid' => $this->userid,
        ]);

        return $status ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
    }

    /**
     * Return the list of custom completion rules defined by this activity.
     *
     * @return array The custom rule names.
     */
    #[\Override]
    public static function get_defined_custom_rules(): array {
        return ['completionspin'];
    }

    /**
     * Return human-readable descriptions for each custom completion rule.
     *
     * @return array Rule name to description mapping.
     */
    #[\Override]
    public function get_custom_rule_descriptions(): array {
        return [
            'completionspin' => get_string('completiondetail:spin', 'spinningwheel'),
        ];
    }

    /**
     * Return the sort order for completion rules.
     *
     * @return array The ordered rule names.
     */
    #[\Override]
    public function get_sort_order(): array {
        return [
            'completionview',
            'completionspin',
        ];
    }
}
