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
 * Data generator for mod_spinningwheel.
 *
 * @package    mod_spinningwheel
 * @copyright  2026 Andrea Juettner, andrea.juettner@eledia.de; AI-assisted by Claude (Anthropic).
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class mod_spinningwheel_generator extends testing_module_generator {

    /**
     * Create a new spinningwheel instance for testing.
     *
     * @param object|array|null $record Data for the instance.
     * @param array|null $options Additional options.
     * @return stdClass The created instance record.
     */
    #[\Override]
    public function create_instance($record = null, ?array $options = null) {
        $record = (object) (array) $record;

        $defaults = [
            'entrysource' => 0,
            'rolefilter' => '',
            'removeafter' => 0,
            'spintime' => 5000,
            'colors' => '',
            'displaymode' => 0,
            'maxvisible' => 0,
            'showconfetti' => 1,
            'showshadow' => 1,
            'showtitle' => 1,
            'tickingsound' => 1,
            'winnermessage' => '',
            'celebratesound' => 1,
            'nameformat' => 0,
            'allowstudentspin' => 0,
            'maxspins' => 0,
            'completionspin' => 0,
            'timecreated' => time(),
            'timemodified' => time(),
        ];

        foreach ($defaults as $key => $value) {
            if (!isset($record->$key)) {
                $record->$key = $value;
            }
        }

        return parent::create_instance($record, $options);
    }
}
