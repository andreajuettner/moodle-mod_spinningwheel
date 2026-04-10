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
 * The mod_spinningwheel wheel spun event.
 *
 * @package    mod_spinningwheel
 * @copyright  2026 Andrea Juettner, andrea.juettner@eledia.de; AI-assisted by Claude (Anthropic).
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_spinningwheel\event;

defined('MOODLE_INTERNAL') || die();

class wheel_spun extends \core\event\base {

    /**
     * Initialise the event.
     */
    #[\Override]
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'spinningwheel_spins';
    }

    /**
     * Return the localised event name.
     *
     * @return string The event name.
     */
    #[\Override]
    public static function get_name() {
        return get_string('eventwheel_spun', 'spinningwheel');
    }

    /**
     * Return a non-localised event description.
     *
     * @return string The event description.
     */
    #[\Override]
    public function get_description() {
        return "The user with id '$this->userid' spun the wheel with course module id " .
            "'$this->contextinstanceid' and selected '{$this->other['selectedtext']}'.";
    }

    /**
     * Return the URL related to this event.
     *
     * @return \moodle_url The event URL.
     */
    #[\Override]
    public function get_url() {
        return new \moodle_url('/mod/spinningwheel/view.php', ['id' => $this->contextinstanceid]);
    }

    /**
     * Return the mapping of objectid for restore.
     *
     * @return array The objectid mapping.
     */
    #[\Override]
    public static function get_objectid_mapping() {
        return ['db' => 'spinningwheel_spins', 'restore' => 'spinningwheel_spin'];
    }

    /**
     * Return the mapping of 'other' fields for restore.
     *
     * @return array The other field mappings.
     */
    #[\Override]
    public static function get_other_mapping() {
        return [
            'wheelid' => ['db' => 'spinningwheel', 'restore' => 'spinningwheel'],
        ];
    }
}
