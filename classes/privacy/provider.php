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
 * Privacy subsystem implementation for mod_spinningwheel.
 *
 * @package    mod_spinningwheel
 * @category   privacy
 * @copyright  2026 Andrea Juettner, andrea.juettner@eledia.de; AI-assisted by Claude (Anthropic).
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_spinningwheel\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\helper;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy provider for mod_spinningwheel.
 *
 * Handles metadata export, user data export, and data deletion
 * for the Spinning Wheel activity module.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    /**
     * Describe the types of personal data stored by this plugin.
     *
     * @param collection $items The collection to add metadata to.
     * @return collection The updated collection.
     */
    #[\Override]
    public static function get_metadata(collection $items): collection {
        $items->add_database_table(
            'spinningwheel_entries',
            [
                'userid' => 'privacy:metadata:spinningwheel_entries:userid',
                'text' => 'privacy:metadata:spinningwheel_entries:text',
                'timecreated' => 'privacy:metadata:spinningwheel_entries:timecreated',
            ],
            'privacy:metadata:spinningwheel_entries'
        );

        $items->add_database_table(
            'spinningwheel_spins',
            [
                'userid' => 'privacy:metadata:spinningwheel_spins:userid',
                'selectedtext' => 'privacy:metadata:spinningwheel_spins:selectedtext',
                'selecteduserid' => 'privacy:metadata:spinningwheel_spins:selecteduserid',
                'selectedentryid' => 'privacy:metadata:spinningwheel_spins:selectedentryid',
                'groupid' => 'privacy:metadata:spinningwheel_spins:groupid',
                'timecreated' => 'privacy:metadata:spinningwheel_spins:timecreated',
            ],
            'privacy:metadata:spinningwheel_spins'
        );

        return $items;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The ID of the user.
     * @return contextlist The list of contexts.
     */
    #[\Override]
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        $sql = "SELECT c.id
                  FROM {context} c
            INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
            INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
            INNER JOIN {spinningwheel} ly ON ly.id = cm.instance
             LEFT JOIN {spinningwheel_spins} ls ON ls.wheelid = ly.id AND ls.userid = :userid1
             LEFT JOIN {spinningwheel_spins} ls2 ON ls2.wheelid = ly.id AND ls2.selecteduserid = :userid3
             LEFT JOIN {spinningwheel_entries} le ON le.wheelid = ly.id AND le.userid = :userid2
                 WHERE ls.id IS NOT NULL OR ls2.id IS NOT NULL OR le.id IS NOT NULL";

        $params = [
            'modname' => 'spinningwheel',
            'contextlevel' => CONTEXT_MODULE,
            'userid1' => $userid,
            'userid2' => $userid,
            'userid3' => $userid,
        ];

        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist to add users to.
     */
    #[\Override]
    public static function get_users_in_context(userlist $userlist): void {
        $context = $userlist->get_context();

        if (!$context instanceof \context_module) {
            return;
        }

        $sql = "SELECT ls.userid
                  FROM {course_modules} cm
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                  JOIN {spinningwheel} ly ON ly.id = cm.instance
                  JOIN {spinningwheel_spins} ls ON ls.wheelid = ly.id
                 WHERE cm.id = :cmid";

        $params = ['cmid' => $context->instanceid, 'modname' => 'spinningwheel'];
        $userlist->add_from_sql('userid', $sql, $params);

        $sql = "SELECT le.userid
                  FROM {course_modules} cm
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                  JOIN {spinningwheel} ly ON ly.id = cm.instance
                  JOIN {spinningwheel_entries} le ON le.wheelid = ly.id
                 WHERE cm.id = :cmid AND le.userid IS NOT NULL";

        $userlist->add_from_sql('userid', $sql, $params);

        $sql = "SELECT ls.selecteduserid AS userid
                  FROM {course_modules} cm
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                  JOIN {spinningwheel} ly ON ly.id = cm.instance
                  JOIN {spinningwheel_spins} ls ON ls.wheelid = ly.id
                 WHERE cm.id = :cmid AND ls.selecteduserid IS NOT NULL";

        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Export all user data for the specified approved contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export data for.
     */
    #[\Override]
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $user = $contextlist->get_user();

        [$contextsql, $contextparams] = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        // Export spins as spinner (subcontext 'spins').
        $sql = "SELECT cm.id AS cmid, ls.selectedtext, ls.timecreated
                  FROM {context} c
            INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
            INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
            INNER JOIN {spinningwheel} ly ON ly.id = cm.instance
            INNER JOIN {spinningwheel_spins} ls ON ls.wheelid = ly.id
                 WHERE c.id {$contextsql}
                       AND ls.userid = :userid
              ORDER BY cm.id";

        $params = ['modname' => 'spinningwheel', 'contextlevel' => CONTEXT_MODULE, 'userid' => $user->id] + $contextparams;
        $spins = $DB->get_recordset_sql($sql, $params);
        self::export_spin_records($spins, $user, 'spins');

        // Export spins as selected person (subcontext 'selections').
        $sql = "SELECT cm.id AS cmid, ls.selectedtext, ls.timecreated
                  FROM {context} c
            INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
            INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
            INNER JOIN {spinningwheel} ly ON ly.id = cm.instance
            INNER JOIN {spinningwheel_spins} ls ON ls.wheelid = ly.id
                 WHERE c.id {$contextsql}
                       AND ls.selecteduserid = :userid
              ORDER BY cm.id";

        $params = ['modname' => 'spinningwheel', 'contextlevel' => CONTEXT_MODULE, 'userid' => $user->id] + $contextparams;
        $selections = $DB->get_recordset_sql($sql, $params);
        self::export_spin_records($selections, $user, 'selections');

        // Export entries (subcontext 'entries').
        $sql = "SELECT cm.id AS cmid, le.text, le.timecreated
                  FROM {context} c
            INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
            INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
            INNER JOIN {spinningwheel} ly ON ly.id = cm.instance
            INNER JOIN {spinningwheel_entries} le ON le.wheelid = ly.id
                 WHERE c.id {$contextsql}
                       AND le.userid = :userid
              ORDER BY cm.id";

        $params = ['modname' => 'spinningwheel', 'contextlevel' => CONTEXT_MODULE, 'userid' => $user->id] + $contextparams;

        $lastcmid = null;
        $entrydata = [];

        $entries = $DB->get_recordset_sql($sql, $params);
        foreach ($entries as $entry) {
            if ($lastcmid !== null && $lastcmid != $entry->cmid) {
                $context = \context_module::instance($lastcmid);
                writer::with_context($context)->export_data(['entries'], (object) ['entries' => $entrydata]);
                $entrydata = [];
            }
            $entrydata[] = [
                'text' => $entry->text,
                'timecreated' => \core_privacy\local\request\transform::datetime($entry->timecreated),
            ];
            $lastcmid = $entry->cmid;
        }
        $entries->close();

        if (!empty($entrydata)) {
            $context = \context_module::instance($lastcmid);
            writer::with_context($context)->export_data(['entries'], (object) ['entries' => $entrydata]);
        }
    }

    /**
     * Helper to export spin recordsets grouped by course module.
     *
     * @param \moodle_recordset $recordset The recordset of spin records.
     * @param \stdClass $user The user whose data is being exported.
     * @param string $subcontext The subcontext name ('spins' or 'selections').
     */
    protected static function export_spin_records(\moodle_recordset $recordset, \stdClass $user, string $subcontext): void {
        $lastcmid = null;
        $spindata = [];

        foreach ($recordset as $spin) {
            if ($lastcmid !== null && $lastcmid != $spin->cmid) {
                $context = \context_module::instance($lastcmid);
                $contextdata = helper::get_context_data($context, $user);
                $contextdata = (object) array_merge((array) $contextdata, [$subcontext => $spindata]);
                writer::with_context($context)->export_data([$subcontext], $contextdata);
                helper::export_context_files($context, $user);
                $spindata = [];
            }
            $spindata[] = [
                'selectedtext' => $spin->selectedtext,
                'timecreated' => \core_privacy\local\request\transform::datetime($spin->timecreated),
            ];
            $lastcmid = $spin->cmid;
        }
        $recordset->close();

        if (!empty($spindata)) {
            $context = \context_module::instance($lastcmid);
            $contextdata = helper::get_context_data($context, $user);
            $contextdata = (object) array_merge((array) $contextdata, [$subcontext => $spindata]);
            writer::with_context($context)->export_data([$subcontext], $contextdata);
            helper::export_context_files($context, $user);
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context The specific context to delete data for.
     */
    #[\Override]
    public static function delete_data_for_all_users_in_context(\context $context): void {
        global $DB;

        if (!$context instanceof \context_module) {
            return;
        }

        if ($cm = get_coursemodule_from_id('spinningwheel', $context->instanceid)) {
            $DB->delete_records('spinningwheel_spins', ['wheelid' => $cm->instance]);
            $DB->delete_records_select('spinningwheel_entries', 'wheelid = ? AND userid IS NOT NULL', [$cm->instance]);
        }
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete data for.
     */
    #[\Override]
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        $deleteduser = get_string('deleteduser', 'mod_spinningwheel');

        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_module) {
                continue;
            }
            $instanceid = $DB->get_field('course_modules', 'instance', ['id' => $context->instanceid]);
            if (!$instanceid) {
                continue;
            }
            // Delete spins where user was the spinner.
            $DB->delete_records('spinningwheel_spins', ['wheelid' => $instanceid, 'userid' => $userid]);

            // Anonymize spins where user was the selected person.
            $DB->execute(
                "UPDATE {spinningwheel_spins}
                    SET selecteduserid = NULL, selectedtext = ?
                  WHERE wheelid = ? AND selecteduserid = ?",
                [$deleteduser, $instanceid, $userid]
            );

            // Delete entries.
            $DB->delete_records('spinningwheel_entries', ['wheelid' => $instanceid, 'userid' => $userid]);
        }
    }

    /**
     * Delete multiple users' data within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete data for.
     */
    #[\Override]
    public static function delete_data_for_users(approved_userlist $userlist): void {
        global $DB;

        $context = $userlist->get_context();
        if (!$context instanceof \context_module) {
            return;
        }

        $cm = get_coursemodule_from_id('spinningwheel', $context->instanceid);
        if (!$cm) {
            return;
        }

        $userids = $userlist->get_userids();
        [$usersql, $userparams] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $deleteduser = get_string('deleteduser', 'mod_spinningwheel');

        // Delete spins where user was the spinner.
        $select = "wheelid = :wheelid AND userid $usersql";
        $params = ['wheelid' => $cm->instance] + $userparams;
        $DB->delete_records_select('spinningwheel_spins', $select, $params);

        // Anonymize spins where user was the selected person.
        [$usersql2, $userparams2] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $DB->execute(
            "UPDATE {spinningwheel_spins}
                SET selecteduserid = NULL, selectedtext = :deleteduser
              WHERE wheelid = :wheelid2 AND selecteduserid $usersql2",
            ['deleteduser' => $deleteduser, 'wheelid2' => $cm->instance] + $userparams2
        );

        // Delete entries.
        [$usersql3, $userparams3] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $select = "wheelid = :wheelid3 AND userid $usersql3";
        $params = ['wheelid3' => $cm->instance] + $userparams3;
        $DB->delete_records_select('spinningwheel_entries', $select, $params);
    }
}
