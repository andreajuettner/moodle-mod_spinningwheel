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
 * Didactic examples page for mod_spinningwheel.
 *
 * @package    mod_spinningwheel
 * @copyright  2026 Andrea Juettner, andrea.juettner@eledia.de; AI-assisted by Claude (Anthropic).
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);

[$course, $cm] = get_course_and_cm_from_cmid($id, 'spinningwheel');
$context = context_module::instance($cm->id);

require_login($course, true, $cm);
require_capability('mod/spinningwheel:viewexamples', $context);

$PAGE->set_url('/mod/spinningwheel/examples.php', ['id' => $id]);
$PAGE->set_title(get_string('examples', 'spinningwheel'));
$PAGE->set_heading($course->fullname);
$PAGE->add_body_class('limitedwidth');

// Build scenarios array from language strings.
$scenarios = [];
for ($i = 1; $i <= 10; $i++) {
    $scenarios[] = [
        'number' => $i,
        'title' => get_string("example{$i}_title", 'spinningwheel'),
        'situation' => get_string("example{$i}_situation", 'spinningwheel'),
        'implementation' => get_string("example{$i}_implementation", 'spinningwheel'),
        'benefit' => get_string("example{$i}_benefit", 'spinningwheel'),
        'settings' => get_string("example{$i}_settings", 'spinningwheel'),
    ];
}

$data = [
    'cmid' => $cm->id,
    'viewurl' => (new moodle_url('/mod/spinningwheel/view.php', ['id' => $cm->id]))->out(false),
    'scenarios' => $scenarios,
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('mod_spinningwheel/examples', $data);
echo $OUTPUT->footer();
