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
 * External functions definition for mod_spinningwheel.
 *
 * @package    mod_spinningwheel
 * @copyright  2026 Andrea Juettner, andrea.juettner@eledia.de; AI-assisted by Claude (Anthropic).
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'mod_spinningwheel_spin_wheel' => [
        'classname' => 'mod_spinningwheel\external\spin_wheel',
        'description' => 'Spin the wheel and get a random result.',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'mod/spinningwheel:spin',
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
    'mod_spinningwheel_get_entries' => [
        'classname' => 'mod_spinningwheel\external\get_entries',
        'description' => 'Get active wheel entries.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'mod/spinningwheel:view',
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
    'mod_spinningwheel_get_history' => [
        'classname' => 'mod_spinningwheel\external\get_history',
        'description' => 'Get spin history for the wheel.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'mod/spinningwheel:viewhistory',
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
    'mod_spinningwheel_clear_history' => [
        'classname' => 'mod_spinningwheel\external\clear_history',
        'description' => 'Clear all spin history for the wheel.',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'mod/spinningwheel:clearhistory',
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
];
