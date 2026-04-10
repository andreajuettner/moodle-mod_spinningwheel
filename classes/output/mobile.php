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
 * Mobile output class for mod_spinningwheel.
 *
 * @package    mod_spinningwheel
 * @copyright  2026 Andrea Juettner, andrea.juettner@eledia.de; AI-assisted by Claude (Anthropic).
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_spinningwheel\output;

defined('MOODLE_INTERNAL') || die();

/**
 * Mobile output class for mod_spinningwheel.
 *
 * Provides the handler method called by the Moodle mobile app
 * via the CoreCourseModuleDelegate.
 */
class mobile {

    /**
     * Returns the initial page when viewing the activity in the mobile app.
     *
     * @param array $args Arguments from the mobile app (cmid required).
     * @return array Template, javascript and otherdata for the mobile app.
     */
    public static function mobile_view_activity($args) {
        global $OUTPUT, $DB, $CFG;

        $args = (object) $args;
        $cmid = $args->cmid;

        [$course, $cm] = get_course_and_cm_from_cmid($cmid, 'spinningwheel');
        $context = \context_module::instance($cm->id);

        $spinningwheel = $DB->get_record('spinningwheel', ['id' => $cm->instance], '*', MUST_EXIST);

        // Trigger view event and completion.
        require_once(__DIR__ . '/../../lib.php');
        spinningwheel_view($spinningwheel, $course, $cm, $context);

        // Check spin permission.
        $canspin = has_capability('mod/spinningwheel:spin', $context);
        if (!$canspin && !empty($spinningwheel->allowstudentspin)) {
            $canspin = has_capability('mod/spinningwheel:view', $context);
        }

        $canviewhistory = has_capability('mod/spinningwheel:viewhistory', $context);
        $canclearhistory = has_capability('mod/spinningwheel:clearhistory', $context);

        // Check max spins.
        $maxspinsreached = false;
        if ($canspin && !empty($spinningwheel->maxspins)) {
            $spincount = $DB->count_records('spinningwheel_spins', ['wheelid' => $spinningwheel->id]);
            if ($spincount >= $spinningwheel->maxspins) {
                $maxspinsreached = true;
                $canspin = false;
            }
        }

        // Get entries (with picture URLs for canvas rendering).
        $entries = spinningwheel_get_active_entries($spinningwheel, $context);
        $displaymode = (int) ($spinningwheel->displaymode ?? 0);
        $isparticipant = ($spinningwheel->entrysource == SPINNINGWHEEL_SOURCE_PARTICIPANTS);

        $entriesdata = [];
        foreach ($entries as $entry) {
            $entrydata = ['text' => $entry->text, 'picture' => ''];

            if ($isparticipant && $displaymode > 0 && !empty($entry->userrecord)) {
                if ($entry->userrecord->picture > 0) {
                    // User has a custom profile picture — embed as base64 data URL.
                    $fs = get_file_storage();
                    $usercontext = \context_user::instance($entry->userrecord->id);
                    $files = $fs->get_area_files($usercontext->id, 'user', 'icon', 0, 'filename', false);
                    foreach ($files as $file) {
                        if (str_starts_with($file->get_filename(), 'f1')) {
                            $content = $file->get_content();
                            $mimetype = $file->get_mimetype();
                            if ($content && $mimetype) {
                                $entrydata['picture'] = 'data:' . $mimetype . ';base64,'
                                    . base64_encode($content);
                            }
                            break;
                        }
                    }
                }

                // Fallback: SVG initials circle matching Moodle's default avatar style.
                if (empty($entrydata['picture'])) {
                    $initials = \core_user::get_initials($entry->userrecord);
                    if ($initials !== '') {
                        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100">'
                            . '<circle cx="50" cy="50" r="50" fill="#e9ecef"/>'
                            . '<text x="50" y="50" text-anchor="middle" dy=".35em" '
                            . 'fill="#495057" font-size="40" font-family="Arial, sans-serif">'
                            . htmlspecialchars($initials, ENT_XML1) . '</text></svg>';
                        $entrydata['picture'] = 'data:image/svg+xml;base64,' . base64_encode($svg);
                    }
                }
            }

            $entriesdata[] = $entrydata;
        }

        // Parse wheel colors.
        $defaultcolors = ['#E8174A', '#3A86FF', '#F9C80E', '#1B998B', '#7B2FF7', '#FF6B35'];
        $wheelcolors = $defaultcolors;
        if (!empty($spinningwheel->colors)) {
            $custom = array_filter(array_map('trim', explode("\n", $spinningwheel->colors)));
            if (!empty($custom)) {
                $wheelcolors = array_values($custom);
            }
        }

        // Build celebration sound as base64 data URL for mobile app.
        // Embedding avoids authentication issues with static files in the WebView.
        $celebratesoundurl = '';
        $celebratesound = (int) ($spinningwheel->celebratesound ?? 1);
        if ($celebratesound > 0) {
            $soundfiles = [
                1 => 'driken5482-applause-cheer-236786.mp3',
                2 => 'freesound_community-applause-105579.mp3',
                3 => 'u_1s41v2luip-crowd-applause-113728.mp3',
            ];
            if (isset($soundfiles[$celebratesound])) {
                $soundpath = $CFG->dirroot . '/mod/spinningwheel/sounds/' . $soundfiles[$celebratesound];
                if (file_exists($soundpath)) {
                    $celebratesoundurl = 'data:audio/mpeg;base64,' . base64_encode(file_get_contents($soundpath));
                }
            }
        }

        // Build config for the JavaScript spinner.
        $jsconfig = [
            'spintime' => (int) $spinningwheel->spintime,
            'removeafter' => (bool) $spinningwheel->removeafter,
            'displaymode' => $displaymode,
            'showconfetti' => (bool) ($spinningwheel->showconfetti ?? true),
            'showshadow' => (bool) ($spinningwheel->showshadow ?? true),
            'tickingsound' => (bool) ($spinningwheel->tickingsound ?? true),
            'celebratesoundurl' => $celebratesoundurl,
            'winnermessage' => $spinningwheel->winnermessage ?? '',
        ];

        // Get last spin result.
        $lastresult = '';
        $lastspin = $DB->get_records('spinningwheel_spins', [
            'wheelid' => $spinningwheel->id,
        ], 'timecreated DESC', 'selectedtext', 0, 1);
        if ($lastspin) {
            $spin = reset($lastspin);
            $lastresult = $spin->selectedtext;
        }

        // Get history.
        $historydata = [];
        if ($canviewhistory) {
            $spins = $DB->get_records('spinningwheel_spins', [
                'wheelid' => $spinningwheel->id,
            ], 'timecreated DESC', '*', 0, 50);
            foreach ($spins as $spin) {
                $spinner = \core_user::get_user($spin->userid);
                $historydata[] = [
                    'spunby' => $spinner ? fullname($spinner) : '?',
                    'selectedtext' => $spin->selectedtext,
                    'timeformatted' => userdate($spin->timecreated),
                ];
            }
        }

        // Template data (rendered server-side via Mustache).
        $data = [
            'cmid' => $cmid,
            'canspin' => $canspin,
            'maxspinsreached' => $maxspinsreached,
            'canviewhistory' => $canviewhistory,
            'canclearhistory' => $canclearhistory,
            'hasentries' => !empty($entriesdata),
            'hasresult' => !empty($lastresult),
            'resulttext' => !empty($lastresult) ? get_string('result', 'spinningwheel', $lastresult) : '',
            'hashistory' => !empty($historydata),
            'history' => $historydata,
        ];

        // Load the standalone mobile spinner JavaScript.
        $jspath = $CFG->dirroot . '/mod/spinningwheel/mobile/js/mobile_spinner.js';
        $javascript = file_exists($jspath) ? file_get_contents($jspath) : '';

        return [
            'templates' => [
                [
                    'id' => 'main',
                    'html' => $OUTPUT->render_from_template('mod_spinningwheel/mobile_view', $data),
                ],
            ],
            'javascript' => $javascript,
            'otherdata' => [
                'cmid' => (string) $cmid,
                'intro' => format_module_intro('spinningwheel', $spinningwheel, $cmid),
                'entries' => json_encode($entriesdata),
                'colors' => json_encode(array_values($wheelcolors)),
                'config' => json_encode($jsconfig),
                'labels' => json_encode([
                    'result' => get_string('result', 'spinningwheel', '{$a}'),
                    'error' => get_string('error'),
                ]),
            ],
        ];
    }
}
