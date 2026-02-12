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
 * Mobile app support for mod_spinningwheel.
 *
 * @package    mod_spinningwheel
 * @copyright  2026 Andrea Juettner, andrea.juettner@eledia.de; AI-assisted by Claude (Anthropic).
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$addons = [
    'mod_spinningwheel' => [
        'handlers' => [
            'spinningwheelview' => [
                'displaydata' => [
                    'icon' => $CFG->wwwroot . '/mod/spinningwheel/pix/monologo.svg',
                    'class' => '',
                ],
                'delegate' => 'CoreCourseModuleDelegate',
                'method' => 'mobile_view_activity',
                'offlinefunctions' => [
                    'mod_spinningwheel_get_entries' => [],
                ],
                'downloadbutton' => false,
                'isresource' => false,
            ],
        ],
        'lang' => [
            ['pluginname', 'spinningwheel'],
            ['spin', 'spinningwheel'],
            ['history', 'spinningwheel'],
            ['noentries', 'spinningwheel'],
            ['result', 'spinningwheel'],
            ['spunby', 'spinningwheel'],
            ['selectedentry', 'spinningwheel'],
            ['spinnedat', 'spinningwheel'],
            ['nospinsyet', 'spinningwheel'],
            ['maxspinsreached', 'spinningwheel'],
            ['mobile:spinresult', 'spinningwheel'],
            ['mobile:taptoopen', 'spinningwheel'],
            ['clearhistory', 'spinningwheel'],
            ['clearhistoryconfirm', 'spinningwheel'],
        ],
    ],
];
