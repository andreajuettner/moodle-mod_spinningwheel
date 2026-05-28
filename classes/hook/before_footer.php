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

namespace mod_spinningwheel\hook;

use core\hook\output\before_footer_html_generation;
use html_writer;
use moodle_url;

/**
 * Shows a "return to Spinning Wheel" button on activities unlocked via the wheel.
 *
 * @package   mod_spinningwheel
 * @copyright 2026 Andrea Juettner, andrea.juettner@eledia.de; AI-assisted by Claude (Anthropic).
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class before_footer {
    /**
     * Render the return-to-wheel button while the user has a pending activity from a wheel.
     *
     * @param before_footer_html_generation $hook The footer hook instance.
     */
    public static function callback(before_footer_html_generation $hook): void {
        global $PAGE, $SESSION;

        if (empty($SESSION->spinningwheel_return)) {
            return;
        }
        $marker = $SESSION->spinningwheel_return;

        // Only on activity pages of the originating course, and never on the wheel itself.
        if (!($PAGE->context instanceof \context_module)) {
            return;
        }
        if ((int) $PAGE->course->id !== (int) $marker->courseid) {
            return;
        }
        if ((int) $PAGE->context->instanceid === (int) $marker->wheelcmid) {
            return;
        }

        $wheelurl = new moodle_url('/mod/spinningwheel/view.php', ['id' => $marker->wheelcmid]);
        $label = html_writer::tag('span', '↺ ', ['class' => 'mod-spinningwheel-returnicon', 'aria-hidden' => 'true'])
            . get_string('backtowheel', 'spinningwheel');
        $button = html_writer::link($wheelurl, $label, ['class' => 'mod-spinningwheel-returnbtn btn btn-primary']);
        $hook->add_html(html_writer::div($button, 'mod-spinningwheel-returnbar'));

        // Move the button into the activity content region (top, right-aligned) so it reads as part of the activity.
        $PAGE->requires->js_call_amd('mod_spinningwheel/returnbutton', 'init');
    }
}
