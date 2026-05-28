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
 * Minimal embed view for the Spinning Wheel — designed for iframe embedding.
 *
 * Outputs only the wheel + spin button without any Moodle page chrome
 * (no header, navigation, footer, breadcrumbs, activity header).
 *
 * Usage: <iframe src=".../mod/spinningwheel/embed.php?id=123"></iframe>
 * Or via Moodle URL resource with "Einbetten" display mode.
 *
 * @package    mod_spinningwheel
 * @copyright  2026 Andrea Juettner, andrea.juettner@eledia.de; AI-assisted by Claude (Anthropic).
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/mod/spinningwheel/lib.php');

$id = required_param('id', PARAM_INT);

[$course, $cm] = get_course_and_cm_from_cmid($id, 'spinningwheel');
$context = context_module::instance($cm->id);

require_login($course, true, $cm);
require_capability('mod/spinningwheel:view', $context);

$spinningwheel = $DB->get_record('spinningwheel', ['id' => $cm->instance], '*', MUST_EXIST);

spinningwheel_view($spinningwheel, $course, $cm, $context);
spinningwheel_update_return_marker($spinningwheel, $cm, $course, $USER->id);

$groupmode = groups_get_activity_groupmode($cm);
$currentgroup = ($groupmode > 0) ? groups_get_activity_group($cm, true) : 0;

$entries = spinningwheel_get_active_entries($spinningwheel, $context, $currentgroup);

$canspin = has_capability('mod/spinningwheel:spin', $context)
    || ($spinningwheel->allowstudentspin && has_capability('mod/spinningwheel:view', $context));
$canviewhistory = has_capability('mod/spinningwheel:viewhistory', $context);

// Use the embedded layout — strips all page chrome.
$PAGE->set_url('/mod/spinningwheel/embed.php', ['id' => $id]);
$PAGE->set_pagelayout('embedded');
$PAGE->activityheader->disable();
$PAGE->set_secondary_navigation(false);
$PAGE->set_heading('');
$PAGE->set_title($spinningwheel->name);

$renderable = new \mod_spinningwheel\output\view_page(
    $spinningwheel,
    $entries,
    $canspin,
    $canviewhistory,
    $cm->id,
    $currentgroup,
    true
);

$renderer = $PAGE->get_renderer('mod_spinningwheel');

$courseurl = new moodle_url('/course/view.php', ['id' => $course->id]);

// Output minimal page — embedded layout + no activity header = only wheel content.
echo $OUTPUT->header();

// Back button — hidden via styles.css, shown by the backbutton AMD module when not inside an iframe.
echo html_writer::div(
    html_writer::link(
        $courseurl,
        '&larr; ' . get_string('backtocourse', 'spinningwheel'),
        ['class' => 'btn btn-sm btn-outline-secondary']
    ),
    '',
    ['id' => 'spinningwheel-back']
);
$PAGE->requires->js_call_amd('mod_spinningwheel/backbutton', 'init');

// All embed styles are in styles.css using body.pagelayout-embedded and .pagelayout-embedded selectors.

echo $renderer->render($renderable);
echo $OUTPUT->footer();
