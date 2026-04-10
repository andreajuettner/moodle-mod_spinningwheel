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
 * Restore task for mod_spinningwheel.
 *
 * @package    mod_spinningwheel
 * @subpackage backup-moodle2
 * @copyright  2026 Andrea Juettner, andrea.juettner@eledia.de; AI-assisted by Claude (Anthropic).
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/spinningwheel/backup/moodle2/restore_spinningwheel_stepslib.php');

class restore_spinningwheel_activity_task extends restore_activity_task {

    /**
     * Define activity-specific restore settings.
     */
    #[\Override]
    protected function define_my_settings(): void {
    }

    /**
     * Define the restore steps for this activity.
     */
    #[\Override]
    protected function define_my_steps(): void {
        $this->add_step(new restore_spinningwheel_activity_structure_step('spinningwheel_structure', 'spinningwheel.xml'));
    }

    /**
     * Define the contents in the activity that must be processed by the link decoder.
     *
     * @return array Array of restore_decode_content objects.
     */
    #[\Override]
    public static function define_decode_contents() {
        $contents = [];
        $contents[] = new restore_decode_content('spinningwheel', ['intro'], 'spinningwheel');
        return $contents;
    }

    /**
     * Define the decoding rules for links belonging to this activity.
     *
     * @return array Array of restore_decode_rule objects.
     */
    #[\Override]
    public static function define_decode_rules() {
        $rules = [];
        $rules[] = new restore_decode_rule('SPINNINGWHEELVIEWBYID', '/mod/spinningwheel/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('SPINNINGWHEELINDEX', '/mod/spinningwheel/index.php?id=$1', 'course');
        return $rules;
    }

    /**
     * Define the restore log rules for this activity.
     *
     * @return array Array of restore_log_rule objects.
     */
    #[\Override]
    public static function define_restore_log_rules() {
        $rules = [];
        $rules[] = new restore_log_rule('spinningwheel', 'add', 'view.php?id={course_module}', '{spinningwheel}');
        $rules[] = new restore_log_rule('spinningwheel', 'update', 'view.php?id={course_module}', '{spinningwheel}');
        $rules[] = new restore_log_rule('spinningwheel', 'view', 'view.php?id={course_module}', '{spinningwheel}');
        $rules[] = new restore_log_rule('spinningwheel', 'spin', 'view.php?id={course_module}', '{spinningwheel}');
        return $rules;
    }

    /**
     * Define the restore log rules for the course level.
     *
     * @return array Array of restore_log_rule objects.
     */
    public static function define_restore_log_rules_for_course() {
        $rules = [];
        $rules[] = new restore_log_rule('spinningwheel', 'view all', 'index.php?id={course}', null);
        return $rules;
    }
}
