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
 * Displays the Spinning Wheel wheel activity.
 *
 * @package    mod_spinningwheel
 * @copyright  2026 Andrea Juettner, andrea.juettner@eledia.de; AI-assisted by Claude (Anthropic).
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/mod/spinningwheel/lib.php');

$id = required_param('id', PARAM_INT);
$embed = optional_param('embed', 0, PARAM_BOOL);

[$course, $cm] = get_course_and_cm_from_cmid($id, 'spinningwheel');
$context = context_module::instance($cm->id);

require_login($course, true, $cm);
require_capability('mod/spinningwheel:view', $context);

$spinningwheel = $DB->get_record('spinningwheel', ['id' => $cm->instance], '*', MUST_EXIST);

spinningwheel_view($spinningwheel, $course, $cm, $context);

$groupmode = groups_get_activity_groupmode($cm);
$currentgroup = ($groupmode > 0) ? groups_get_activity_group($cm, true) : 0;

$entries = spinningwheel_get_active_entries($spinningwheel, $context, $currentgroup);

$canspin = has_capability('mod/spinningwheel:spin', $context)
    || ($spinningwheel->allowstudentspin && has_capability('mod/spinningwheel:view', $context));
$canviewhistory = has_capability('mod/spinningwheel:viewhistory', $context);

$PAGE->set_url('/mod/spinningwheel/view.php', ['id' => $id]);
$PAGE->set_title($spinningwheel->name);
$PAGE->set_heading($course->fullname);

if ($embed) {
    $PAGE->set_pagelayout('embedded');
    $PAGE->activityheader->disable();
    $PAGE->set_heading('');
    $PAGE->set_title($spinningwheel->name);
} else {
    $PAGE->add_body_class('limitedwidth');
}

$renderable = new \mod_spinningwheel\output\view_page(
    $spinningwheel,
    $entries,
    $canspin,
    $canviewhistory,
    $cm->id,
    $currentgroup
);

$renderer = $PAGE->get_renderer('mod_spinningwheel');

echo $OUTPUT->header();

if (!$embed && $groupmode) {
    $groupsactivitymenu = groups_print_activity_menu(
        $cm,
        new moodle_url('/mod/spinningwheel/view.php', ['id' => $id]),
        true
    );
    echo html_writer::div($groupsactivitymenu, 'mb-3');
}

echo $renderer->render($renderable);

echo $OUTPUT->footer();
