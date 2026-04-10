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
 * Library of functions for mod_spinningwheel.
 *
 * @package   mod_spinningwheel
 * @copyright 2026 Andrea Juettner, andrea.juettner@eledia.de; AI-assisted by Claude (Anthropic).
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define('SPINNINGWHEEL_SOURCE_PARTICIPANTS', 0);
define('SPINNINGWHEEL_SOURCE_MANUAL', 1);
define('SPINNINGWHEEL_SOURCE_ACTIVITIES', 2);

/**
 * Returns whether the module supports a particular feature.
 *
 * @param string $feature FEATURE_xx constant for requested feature.
 * @return mixed True if module supports feature, false if not, null if doesn't know.
 */
function spinningwheel_supports(string $feature): bool|string|null {
    return match ($feature) {
        FEATURE_GROUPS => true,
        FEATURE_GROUPINGS => true,
        FEATURE_MOD_INTRO => true,
        FEATURE_COMPLETION_TRACKS_VIEWS => true,
        FEATURE_COMPLETION_HAS_RULES => true,
        FEATURE_GRADE_HAS_GRADE => false,
        FEATURE_GRADE_OUTCOMES => false,
        FEATURE_BACKUP_MOODLE2 => true,
        FEATURE_SHOW_DESCRIPTION => true,
        FEATURE_MOD_PURPOSE => MOD_PURPOSE_OTHER,
        default => null,
    };
}

/**
 * Add a new spinningwheel instance.
 *
 * @param stdClass $data Form data.
 * @return int New instance id.
 */
function spinningwheel_add_instance(stdClass $data): int {
    global $DB;

    $data->timemodified = time();
    $data->timecreated = time();

    $data->id = $DB->insert_record('spinningwheel', $data);

    if ($data->entrysource == SPINNINGWHEEL_SOURCE_MANUAL && !empty($data->manualentries)) {
        spinningwheel_save_manual_entries($data->id, $data->manualentries);
    }

    if (!empty($data->completionexpected)) {
        \core_completion\api::update_completion_date_event(
            $data->coursemodule,
            'spinningwheel',
            $data->id,
            $data->completionexpected
        );
    }

    return $data->id;
}

/**
 * Update an existing spinningwheel instance.
 *
 * @param stdClass $data Form data.
 * @return bool True on success.
 */
function spinningwheel_update_instance(stdClass $data): bool {
    global $DB;

    $data->id = $data->instance;
    $data->timemodified = time();

    if ($data->entrysource == SPINNINGWHEEL_SOURCE_MANUAL && !empty($data->manualentries)) {
        $DB->delete_records('spinningwheel_entries', ['wheelid' => $data->id, 'userid' => null]);
        spinningwheel_save_manual_entries($data->id, $data->manualentries);
    }

    $completionexpected = (!empty($data->completionexpected)) ? $data->completionexpected : null;
    \core_completion\api::update_completion_date_event($data->coursemodule, 'spinningwheel', $data->id, $completionexpected);

    return $DB->update_record('spinningwheel', $data);
}

/**
 * Delete a spinningwheel instance.
 *
 * @param int $id Instance id.
 * @return bool True on success.
 */
function spinningwheel_delete_instance(int $id): bool {
    global $DB;

    if (!$DB->get_record('spinningwheel', ['id' => $id])) {
        return false;
    }

    $DB->delete_records('spinningwheel_spins', ['wheelid' => $id]);
    $DB->delete_records('spinningwheel_entries', ['wheelid' => $id]);
    $DB->delete_records('spinningwheel', ['id' => $id]);

    return true;
}

/**
 * Save manual entries for a wheel.
 *
 * @param int $wheelid Wheel instance id.
 * @param string $entries Newline-separated entries.
 */
function spinningwheel_save_manual_entries(int $wheelid, string $entries): void {
    global $DB;

    $lines = array_filter(array_map('trim', explode("\n", $entries)));
    $sortorder = 0;
    foreach ($lines as $line) {
        $record = new stdClass();
        $record->wheelid = $wheelid;
        $record->userid = null;
        $record->text = $line;
        $record->sortorder = $sortorder++;
        $record->active = 1;
        $record->timecreated = time();
        $DB->insert_record('spinningwheel_entries', $record);
    }
}

/**
 * Format a user name according to the configured format.
 *
 * @param stdClass $user User record.
 * @param int $format 0=fullname, 1=firstname, 2=lastname, 3=firstname + last initial.
 * @return string Formatted name.
 */
function spinningwheel_format_name(stdClass $user, int $format): string {
    return match ($format) {
        1 => $user->firstname,
        2 => $user->lastname,
        3 => $user->firstname . ' ' . mb_strtoupper(mb_substr($user->lastname, 0, 1)) . '.',
        default => fullname($user),
    };
}

/**
 * Mark the activity completed and trigger the course_module_viewed event.
 *
 * @param stdClass $spinningwheel Instance record.
 * @param stdClass $course Course record.
 * @param stdClass|cm_info $cm Course module record.
 * @param context_module $context Module context.
 */
function spinningwheel_view(stdClass $spinningwheel, stdClass $course, object $cm, context_module $context): void {
    $event = \mod_spinningwheel\event\course_module_viewed::create([
        'context' => $context,
        'objectid' => $spinningwheel->id,
    ]);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('spinningwheel', $spinningwheel);
    $event->trigger();

    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}

/**
 * Get active entries for a wheel instance.
 *
 * @param stdClass $spinningwheel Instance record.
 * @param context_module $context Module context.
 * @param int $groupid Group id (0 for all).
 * @return array Array of entry objects with 'id', 'text', 'userid'.
 */
function spinningwheel_get_active_entries(stdClass $spinningwheel, context_module $context, int $groupid = 0): array {
    global $DB;

    if ($spinningwheel->entrysource == SPINNINGWHEEL_SOURCE_MANUAL) {
        return array_values($DB->get_records('spinningwheel_entries', [
            'wheelid' => $spinningwheel->id,
            'active' => 1,
        ], 'sortorder ASC'));
    }

    if ($spinningwheel->entrysource == SPINNINGWHEEL_SOURCE_ACTIVITIES) {
        return spinningwheel_get_course_activity_entries($spinningwheel, $context);
    }

    $roleids = !empty($spinningwheel->rolefilter) ? explode(',', $spinningwheel->rolefilter) : [];
    $picturefields = implode(',', array_map(fn($f) => "u.$f", \core_user\fields::get_picture_fields()));
    $users = get_enrolled_users($context, '', $groupid, $picturefields);

    if (!empty($roleids)) {
        $filtered = [];
        foreach ($users as $user) {
            $userroles = get_user_roles($context, $user->id, false);
            foreach ($userroles as $role) {
                if (in_array($role->roleid, $roleids)) {
                    $filtered[$user->id] = $user;
                    break;
                }
            }
        }
        $users = $filtered;
    }

    $entries = [];
    foreach ($users as $user) {
        $entry = new stdClass();
        $entry->id = 0;
        $entry->userid = $user->id;
        $entry->text = spinningwheel_format_name($user, (int)($spinningwheel->nameformat ?? 0));
        $entry->active = 1;
        $entry->userrecord = $user;
        $entries[] = $entry;
    }

    if ($spinningwheel->removeafter) {
        $spunuserids = $DB->get_fieldset_sql(
            'SELECT DISTINCT selecteduserid FROM {spinningwheel_spins}
              WHERE wheelid = ? AND selecteduserid IS NOT NULL',
            [$spinningwheel->id]
        );
        $entries = array_filter($entries, function ($entry) use ($spunuserids) {
            return !in_array($entry->userid, $spunuserids);
        });
    }

    // Apply maxvisible limit.
    if (!empty($spinningwheel->maxvisible) && $spinningwheel->maxvisible > 0 && count($entries) > $spinningwheel->maxvisible) {
        shuffle($entries);
        $entries = array_slice($entries, 0, $spinningwheel->maxvisible);
    }

    return array_values($entries);
}

/**
 * Get course activities as wheel entries.
 *
 * Returns all visible activities in the course (excluding the wheel itself)
 * as entry objects for the spinning wheel.
 *
 * @param stdClass $spinningwheel Instance record.
 * @param context_module $context Module context.
 * @return array Array of entry objects with 'id' (cmid), 'text' (activity name), 'cmid'.
 */
function spinningwheel_get_course_activity_entries(stdClass $spinningwheel, context_module $context): array {
    global $DB, $USER;

    $cm = get_coursemodule_from_instance('spinningwheel', $spinningwheel->id);
    $modinfo = get_fast_modinfo($spinningwheel->course);
    $coursecontext = \context_course::instance($spinningwheel->course);
    $entries = [];

    $course = get_course($spinningwheel->course);
    $completion = new completion_info($course);

    foreach ($modinfo->get_cms() as $othercm) {
        // Skip the wheel itself, hidden activities, and activities being deleted.
        if ($othercm->id == $cm->id || !$othercm->visible || $othercm->deletioninprogress) {
            continue;
        }

        $entry = new stdClass();
        $entry->id = 0;
        $entry->cmid = $othercm->id;
        $entry->userid = null;
        $entry->text = format_string($othercm->name, true, ['context' => $coursecontext]);
        $entry->active = 1;
        $entry->completed = false;

        // Check completion status for current user.
        if ($completion->is_enabled($othercm)) {
            $completiondata = $completion->get_data($othercm, true, $USER->id);
            if ($completiondata->completionstate != COMPLETION_INCOMPLETE) {
                $entry->completed = true;
                $entry->active = 0;
                $entry->text .= "\n" . get_string('completed', 'spinningwheel');
            }
        }

        $entries[] = $entry;
    }

    // Remove already-spun activities if removeafter is enabled.
    if ($spinningwheel->removeafter) {
        $spuncmids = $DB->get_fieldset_sql(
            'SELECT DISTINCT selectedcmid FROM {spinningwheel_spins}
              WHERE wheelid = ? AND selectedcmid IS NOT NULL AND userid = ?',
            [$spinningwheel->id, $USER->id]
        );
        $entries = array_filter($entries, function ($entry) use ($spuncmids) {
            return !in_array($entry->cmid, $spuncmids);
        });
    }

    return array_values($entries);
}

/**
 * Check if the user has a previously unlocked activity that is not yet completed.
 *
 * Returns the pending activity info if found, or null if the user can spin again.
 *
 * @param int $wheelid The spinning wheel instance ID.
 * @param int $userid The user ID to check.
 * @param stdClass $course The course object.
 * @return stdClass|null Object with cmid and name if pending, null if free to spin.
 */
function spinningwheel_get_pending_activity(int $wheelid, int $userid, stdClass $course): ?stdClass {
    global $DB;

    // Get all activities this user has unlocked via this wheel.
    $spuncmids = $DB->get_fieldset_sql(
        'SELECT DISTINCT selectedcmid FROM {spinningwheel_spins}
          WHERE wheelid = ? AND userid = ? AND selectedcmid IS NOT NULL',
        [$wheelid, $userid]
    );

    if (empty($spuncmids)) {
        return null;
    }

    $completion = new completion_info($course);
    if (!$completion->is_enabled()) {
        return null;
    }

    $modinfo = get_fast_modinfo($course);

    // Check each unlocked activity — is it still incomplete?
    foreach ($spuncmids as $cmid) {
        if (!isset($modinfo->cms[$cmid])) {
            continue;
        }
        $cm = $modinfo->cms[$cmid];
        if ($cm->deletioninprogress) {
            continue;
        }
        $completiondata = $completion->get_data($cm, false, $userid);
        if ($completiondata->completionstate == COMPLETION_INCOMPLETE) {
            return (object)['cmid' => $cmid, 'name' => format_string($cm->name)];
        }
    }

    return null;
}

/**
 * Get the user summary for the outline report.
 *
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $spinningwheel
 * @return object|null
 */
function spinningwheel_user_outline($course, $user, $mod, $spinningwheel) {
    global $DB;

    $count = $DB->count_records('spinningwheel_spins', ['wheelid' => $spinningwheel->id, 'userid' => $user->id]);
    if ($count > 0) {
        $last = $DB->get_field_sql(
            'SELECT MAX(timecreated) FROM {spinningwheel_spins} WHERE wheelid = ? AND userid = ?',
            [$spinningwheel->id, $user->id]
        );
        return (object) [
            'info' => get_string('spincount', 'spinningwheel', $count),
            'time' => $last,
        ];
    }
    return null;
}

/**
 * Prints the complete activity report for a user.
 *
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $spinningwheel
 */
function spinningwheel_user_complete($course, $user, $mod, $spinningwheel) {
    global $DB;

    $spins = $DB->get_records('spinningwheel_spins', ['wheelid' => $spinningwheel->id, 'userid' => $user->id], 'timecreated DESC');
    if ($spins) {
        foreach ($spins as $spin) {
            echo get_string('result', 'spinningwheel', $spin->selectedtext) . ' - ' .
                userdate($spin->timecreated) . '<br>';
        }
    } else {
        echo get_string('nospinsyet', 'spinningwheel');
    }
}

/**
 * List the actions that correspond to a view of this module.
 *
 * @return array
 */
function spinningwheel_get_view_actions(): array {
    return ['view'];
}

/**
 * List the actions that correspond to a post of this module.
 *
 * @return array
 */
function spinningwheel_get_post_actions(): array {
    return ['spin'];
}

/**
 * Implementation of the function for printing the form elements that control
 * whether the course reset functionality affects the spinningwheel.
 *
 * @param MoodleQuickForm $mform form passed by reference.
 */
function spinningwheel_reset_course_form_definition(&$mform): void {
    $mform->addElement('header', 'spinningwheelheader', get_string('modulenameplural', 'spinningwheel'));
    $mform->addElement('advcheckbox', 'reset_spinningwheel', get_string('removespins', 'spinningwheel'));
}

/**
 * Course reset form defaults.
 *
 * @param stdClass $course
 * @return array
 */
function spinningwheel_reset_course_form_defaults($course): array {
    return ['reset_spinningwheel' => 1];
}

/**
 * Actual implementation of the reset course functionality.
 *
 * @param object $data the data submitted from the reset course.
 * @return array status array.
 */
function spinningwheel_reset_userdata($data): array {
    global $DB;

    $componentstr = get_string('modulenameplural', 'spinningwheel');
    $status = [];

    if (!empty($data->reset_spinningwheel)) {
        $sql = "SELECT fw.id FROM {spinningwheel} fw WHERE fw.course = ?";
        $DB->delete_records_select('spinningwheel_spins', "wheelid IN ($sql)", [$data->courseid]);

        $DB->execute(
            "UPDATE {spinningwheel_entries} SET active = 1 WHERE wheelid IN ($sql)",
            [$data->courseid]
        );

        $status[] = [
            'component' => $componentstr,
            'item' => get_string('removespins', 'spinningwheel'),
            'error' => false,
        ];
    }

    return $status;
}

/**
 * Add extra information to the course module info.
 *
 * @param stdClass $coursemodule
 * @return cached_cm_info|false
 */
function spinningwheel_get_coursemodule_info($coursemodule) {
    global $DB;

    $fields = 'id, name, intro, introformat, completionspin, embedoncourse';
    if (!$spinningwheel = $DB->get_record('spinningwheel', ['id' => $coursemodule->instance], $fields)) {
        return false;
    }

    $result = new cached_cm_info();
    $result->name = $spinningwheel->name;

    if ($coursemodule->showdescription) {
        $result->content = format_module_intro('spinningwheel', $spinningwheel, $coursemodule->id, false);
    }

    if ($coursemodule->completion == COMPLETION_TRACKING_AUTOMATIC) {
        $result->customdata['customcompletionrules']['completionspin'] = $spinningwheel->completionspin;
    }

    $result->customdata['embedoncourse'] = (int) $spinningwheel->embedoncourse;

    return $result;
}

/**
 * Sets the Spinning Wheel display on the course page when inline embedding is enabled.
 *
 * @param cm_info $cm Course-module object.
 */
function spinningwheel_cm_info_view(cm_info $cm) {
    $mode = (int) ($cm->customdata['embedoncourse'] ?? 0);
    if ($mode !== 1) {
        return;
    }

    if (!$cm->uservisible || !has_capability('mod/spinningwheel:view', $cm->context)) {
        return;
    }

    $embedurl = new moodle_url('/mod/spinningwheel/embed.php', ['id' => $cm->id]);
    $iframe = '<iframe src="' . $embedurl->out(true) . '" '
        . 'width="100%" height="650" '
        . 'class="mod-spinningwheel-embed">'
        . '</iframe>';
    $cm->set_content($iframe);
}

/**
 * Modifies the course module link behaviour for display modes 2 (open) and 3 (popup).
 *
 * @param cm_info $cm Course-module object.
 */
function spinningwheel_cm_info_dynamic(cm_info $cm) {
    $mode = (int) ($cm->customdata['embedoncourse'] ?? 0);
    if ($mode < 2) {
        return;
    }

    $embedurl = new moodle_url('/mod/spinningwheel/embed.php', ['id' => $cm->id]);

    if ($mode == 2) {
        // Open in same tab (accessible).
        $cm->set_on_click("window.location.href='" . $embedurl->out(true) . "'; return false;");
    } else if ($mode == 3) {
        // Popup window.
        $cm->set_on_click("window.open('" . $embedurl->out(true)
            . "', 'spinningwheel', 'width=550,height=700,scrollbars=no,resizable=yes'); return false;");
    }
}

/**
 * Callback which returns human-readable strings describing the active completion custom rules.
 *
 * @param cm_info|stdClass $cm
 * @return array
 */
function mod_spinningwheel_get_completion_active_rule_descriptions($cm): array {
    if (
        empty($cm->customdata['customcompletionrules'])
        || $cm->completion != COMPLETION_TRACKING_AUTOMATIC
    ) {
        return [];
    }

    $descriptions = [];
    foreach ($cm->customdata['customcompletionrules'] as $key => $val) {
        if ($key === 'completionspin' && !empty($val)) {
            $descriptions[] = get_string('completiondetail:spin', 'spinningwheel');
        }
    }
    return $descriptions;
}

/**
 * Return a list of page types.
 *
 * @param string $pagetype current page type.
 * @param stdClass $parentcontext Block's parent context.
 * @param stdClass $currentcontext Current context of block.
 * @return array
 */
function spinningwheel_page_type_list($pagetype, $parentcontext, $currentcontext): array {
    return ['mod-spinningwheel-*' => get_string('page-mod-spinningwheel-x', 'spinningwheel')];
}
