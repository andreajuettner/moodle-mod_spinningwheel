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

namespace mod_spinningwheel\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use core_privacy\tests\provider_testcase;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy provider tests for mod_spinningwheel.
 *
 * @package    mod_spinningwheel
 * @copyright  2026 Andrea Juettner, andrea.juettner@eledia.de; AI-assisted by Claude (Anthropic).
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_spinningwheel\privacy\provider
 */
class provider_test extends provider_testcase {

    /**
     * Helper to create a spinningwheel instance, enrol a user, and insert a spin record.
     *
     * @return array [$course, $spinningwheel, $user, $context]
     */
    private function create_test_data(): array {
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');

        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', ['course' => $course->id]);

        $spin = new \stdClass();
        $spin->wheelid = $spinningwheel->id;
        $spin->userid = $user->id;
        $spin->selectedtext = 'Test Winner';
        $spin->timecreated = time();
        $DB->insert_record('spinningwheel_spins', $spin);

        $cm = get_coursemodule_from_instance('spinningwheel', $spinningwheel->id);
        $context = \context_module::instance($cm->id);

        return [$course, $spinningwheel, $user, $context];
    }

    public function test_get_metadata(): void {
        $collection = new collection('mod_spinningwheel');
        $result = provider::get_metadata($collection);
        $this->assertNotEmpty($result);

        $items = $result->get_collection();
        $this->assertCount(2, $items);
    }

    public function test_get_contexts_for_userid(): void {
        $this->resetAfterTest();

        [$course, $spinningwheel, $user, $context] = $this->create_test_data();

        $contextlist = provider::get_contexts_for_userid($user->id);
        $this->assertCount(1, $contextlist);
        $this->assertEquals($context->id, $contextlist->get_contextids()[0]);
    }

    public function test_get_contexts_for_userid_no_data(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $contextlist = provider::get_contexts_for_userid($user->id);
        $this->assertCount(0, $contextlist);
    }

    public function test_get_users_in_context(): void {
        $this->resetAfterTest();

        [$course, $spinningwheel, $user, $context] = $this->create_test_data();

        $userlist = new userlist($context, 'mod_spinningwheel');
        provider::get_users_in_context($userlist);

        $this->assertCount(1, $userlist);
        $this->assertContains((int) $user->id, $userlist->get_userids());
    }

    public function test_get_users_in_context_no_data(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', ['course' => $course->id]);

        $cm = get_coursemodule_from_instance('spinningwheel', $spinningwheel->id);
        $context = \context_module::instance($cm->id);

        $userlist = new userlist($context, 'mod_spinningwheel');
        provider::get_users_in_context($userlist);

        $this->assertCount(0, $userlist);
    }

    public function test_get_users_in_context_wrong_context(): void {
        $this->resetAfterTest();

        $context = \context_system::instance();
        $userlist = new userlist($context, 'mod_spinningwheel');
        provider::get_users_in_context($userlist);

        $this->assertCount(0, $userlist);
    }

    public function test_export_user_data(): void {
        $this->resetAfterTest();

        [$course, $spinningwheel, $user, $context] = $this->create_test_data();

        $contextlist = new approved_contextlist($user, 'mod_spinningwheel', [$context->id]);
        provider::export_user_data($contextlist);

        $data = writer::with_context($context)->get_data(['spins']);
        $this->assertNotEmpty($data);
        $this->assertNotEmpty($data->spins);
        $this->assertCount(1, $data->spins);
        $this->assertEquals('Test Winner', $data->spins[0]['selectedtext']);
    }

    public function test_export_user_data_multiple_spins(): void {
        $this->resetAfterTest();
        global $DB;

        [$course, $spinningwheel, $user, $context] = $this->create_test_data();

        // Add a second spin.
        $spin = new \stdClass();
        $spin->wheelid = $spinningwheel->id;
        $spin->userid = $user->id;
        $spin->selectedtext = 'Second Winner';
        $spin->timecreated = time() + 1;
        $DB->insert_record('spinningwheel_spins', $spin);

        $contextlist = new approved_contextlist($user, 'mod_spinningwheel', [$context->id]);
        provider::export_user_data($contextlist);

        $data = writer::with_context($context)->get_data(['spins']);
        $this->assertCount(2, $data->spins);
    }

    public function test_delete_data_for_user(): void {
        $this->resetAfterTest();
        global $DB;

        [$course, $spinningwheel, $user, $context] = $this->create_test_data();

        // Add a spin from another user.
        $user2 = $this->getDataGenerator()->create_user();
        $spin = new \stdClass();
        $spin->wheelid = $spinningwheel->id;
        $spin->userid = $user2->id;
        $spin->selectedtext = 'User2 Win';
        $spin->timecreated = time();
        $DB->insert_record('spinningwheel_spins', $spin);

        $contextlist = new approved_contextlist($user, 'mod_spinningwheel', [$context->id]);
        provider::delete_data_for_user($contextlist);

        // User's spins should be deleted.
        $this->assertFalse($DB->record_exists('spinningwheel_spins', [
            'wheelid' => $spinningwheel->id,
            'userid' => $user->id,
        ]));
        // Other user's spins should remain.
        $this->assertTrue($DB->record_exists('spinningwheel_spins', [
            'wheelid' => $spinningwheel->id,
            'userid' => $user2->id,
        ]));
    }

    public function test_delete_data_for_all_users_in_context(): void {
        $this->resetAfterTest();
        global $DB;

        [$course, $spinningwheel, $user, $context] = $this->create_test_data();

        // Add spins from a second user.
        $user2 = $this->getDataGenerator()->create_user();
        $spin = new \stdClass();
        $spin->wheelid = $spinningwheel->id;
        $spin->userid = $user2->id;
        $spin->selectedtext = 'User2 Win';
        $spin->timecreated = time();
        $DB->insert_record('spinningwheel_spins', $spin);

        provider::delete_data_for_all_users_in_context($context);

        // All spins should be deleted.
        $this->assertEquals(0, $DB->count_records('spinningwheel_spins', ['wheelid' => $spinningwheel->id]));
    }

    public function test_delete_data_for_all_users_wrong_context(): void {
        $this->resetAfterTest();
        global $DB;

        [$course, $spinningwheel, $user, $context] = $this->create_test_data();

        // Pass a system context — should not delete anything.
        provider::delete_data_for_all_users_in_context(\context_system::instance());

        $this->assertTrue($DB->record_exists('spinningwheel_spins', ['wheelid' => $spinningwheel->id]));
    }

    public function test_delete_data_for_users(): void {
        $this->resetAfterTest();
        global $DB;

        [$course, $spinningwheel, $user, $context] = $this->create_test_data();

        // Add spins from two more users.
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();

        foreach ([$user2, $user3] as $u) {
            $spin = new \stdClass();
            $spin->wheelid = $spinningwheel->id;
            $spin->userid = $u->id;
            $spin->selectedtext = 'Win';
            $spin->timecreated = time();
            $DB->insert_record('spinningwheel_spins', $spin);
        }

        // Delete data for user and user2, but not user3.
        $userlist = new approved_userlist($context, 'mod_spinningwheel', [$user->id, $user2->id]);
        provider::delete_data_for_users($userlist);

        $this->assertFalse($DB->record_exists('spinningwheel_spins', ['userid' => $user->id]));
        $this->assertFalse($DB->record_exists('spinningwheel_spins', ['userid' => $user2->id]));
        $this->assertTrue($DB->record_exists('spinningwheel_spins', ['userid' => $user3->id]));
    }

    public function test_get_contexts_for_userid_as_selected_user(): void {
        $this->resetAfterTest();
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $spinner = $this->getDataGenerator()->create_user();
        $selected = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($spinner->id, $course->id, 'student');
        $this->getDataGenerator()->enrol_user($selected->id, $course->id, 'student');

        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', ['course' => $course->id]);

        $spin = new \stdClass();
        $spin->wheelid = $spinningwheel->id;
        $spin->userid = $spinner->id;
        $spin->selecteduserid = $selected->id;
        $spin->selectedtext = fullname($selected);
        $spin->timecreated = time();
        $DB->insert_record('spinningwheel_spins', $spin);

        $cm = get_coursemodule_from_instance('spinningwheel', $spinningwheel->id);
        $context = \context_module::instance($cm->id);

        $contextlist = provider::get_contexts_for_userid($selected->id);
        $this->assertCount(1, $contextlist);
        $this->assertEquals($context->id, $contextlist->get_contextids()[0]);
    }

    public function test_get_users_in_context_includes_selected_users(): void {
        $this->resetAfterTest();
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $spinner = $this->getDataGenerator()->create_user();
        $selected = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($spinner->id, $course->id, 'student');
        $this->getDataGenerator()->enrol_user($selected->id, $course->id, 'student');

        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', ['course' => $course->id]);

        $spin = new \stdClass();
        $spin->wheelid = $spinningwheel->id;
        $spin->userid = $spinner->id;
        $spin->selecteduserid = $selected->id;
        $spin->selectedtext = fullname($selected);
        $spin->timecreated = time();
        $DB->insert_record('spinningwheel_spins', $spin);

        $cm = get_coursemodule_from_instance('spinningwheel', $spinningwheel->id);
        $context = \context_module::instance($cm->id);

        $userlist = new userlist($context, 'mod_spinningwheel');
        provider::get_users_in_context($userlist);

        $userids = $userlist->get_userids();
        $this->assertContains((int) $spinner->id, $userids);
        $this->assertContains((int) $selected->id, $userids);
    }

    public function test_export_user_data_as_selected_user(): void {
        $this->resetAfterTest();
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $spinner = $this->getDataGenerator()->create_user();
        $selected = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($spinner->id, $course->id, 'student');
        $this->getDataGenerator()->enrol_user($selected->id, $course->id, 'student');

        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', ['course' => $course->id]);

        $spin = new \stdClass();
        $spin->wheelid = $spinningwheel->id;
        $spin->userid = $spinner->id;
        $spin->selecteduserid = $selected->id;
        $spin->selectedtext = fullname($selected);
        $spin->timecreated = time();
        $DB->insert_record('spinningwheel_spins', $spin);

        $cm = get_coursemodule_from_instance('spinningwheel', $spinningwheel->id);
        $context = \context_module::instance($cm->id);

        $contextlist = new approved_contextlist($selected, 'mod_spinningwheel', [$context->id]);
        provider::export_user_data($contextlist);

        $data = writer::with_context($context)->get_data(['selections']);
        $this->assertNotEmpty($data);
        $this->assertNotEmpty($data->selections);
        $this->assertCount(1, $data->selections);
        $this->assertEquals(fullname($selected), $data->selections[0]['selectedtext']);
    }

    public function test_export_user_data_entries(): void {
        $this->resetAfterTest();
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');

        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', ['course' => $course->id]);

        $entry = new \stdClass();
        $entry->wheelid = $spinningwheel->id;
        $entry->userid = $user->id;
        $entry->text = fullname($user);
        $entry->timecreated = time();
        $DB->insert_record('spinningwheel_entries', $entry);

        $cm = get_coursemodule_from_instance('spinningwheel', $spinningwheel->id);
        $context = \context_module::instance($cm->id);

        $contextlist = new approved_contextlist($user, 'mod_spinningwheel', [$context->id]);
        provider::export_user_data($contextlist);

        $data = writer::with_context($context)->get_data(['entries']);
        $this->assertNotEmpty($data);
        $this->assertNotEmpty($data->entries);
        $this->assertCount(1, $data->entries);
        $this->assertEquals(fullname($user), $data->entries[0]['text']);
    }

    public function test_delete_data_for_user_anonymises_selected(): void {
        $this->resetAfterTest();
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $spinner = $this->getDataGenerator()->create_user();
        $selected = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($spinner->id, $course->id, 'student');
        $this->getDataGenerator()->enrol_user($selected->id, $course->id, 'student');

        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', ['course' => $course->id]);

        $spin = new \stdClass();
        $spin->wheelid = $spinningwheel->id;
        $spin->userid = $spinner->id;
        $spin->selecteduserid = $selected->id;
        $spin->selectedtext = fullname($selected);
        $spin->timecreated = time();
        $spinid = $DB->insert_record('spinningwheel_spins', $spin);

        $cm = get_coursemodule_from_instance('spinningwheel', $spinningwheel->id);
        $context = \context_module::instance($cm->id);

        $contextlist = new approved_contextlist($selected, 'mod_spinningwheel', [$context->id]);
        provider::delete_data_for_user($contextlist);

        $record = $DB->get_record('spinningwheel_spins', ['id' => $spinid]);
        $this->assertNull($record->selecteduserid);
        $this->assertEquals('Deleted User', $record->selectedtext);
    }

    public function test_delete_data_for_user_deletes_entries(): void {
        $this->resetAfterTest();
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');

        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', ['course' => $course->id]);

        $entry = new \stdClass();
        $entry->wheelid = $spinningwheel->id;
        $entry->userid = $user->id;
        $entry->text = fullname($user);
        $entry->timecreated = time();
        $DB->insert_record('spinningwheel_entries', $entry);

        $cm = get_coursemodule_from_instance('spinningwheel', $spinningwheel->id);
        $context = \context_module::instance($cm->id);

        $contextlist = new approved_contextlist($user, 'mod_spinningwheel', [$context->id]);
        provider::delete_data_for_user($contextlist);

        $this->assertFalse($DB->record_exists('spinningwheel_entries', [
            'wheelid' => $spinningwheel->id,
            'userid' => $user->id,
        ]));
    }

    public function test_delete_data_for_users_anonymises_selected(): void {
        $this->resetAfterTest();
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $spinner = $this->getDataGenerator()->create_user();
        $selected = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($spinner->id, $course->id, 'student');
        $this->getDataGenerator()->enrol_user($selected->id, $course->id, 'student');

        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', ['course' => $course->id]);

        $spin = new \stdClass();
        $spin->wheelid = $spinningwheel->id;
        $spin->userid = $spinner->id;
        $spin->selecteduserid = $selected->id;
        $spin->selectedtext = fullname($selected);
        $spin->timecreated = time();
        $spinid = $DB->insert_record('spinningwheel_spins', $spin);

        $cm = get_coursemodule_from_instance('spinningwheel', $spinningwheel->id);
        $context = \context_module::instance($cm->id);

        $userlist = new approved_userlist($context, 'mod_spinningwheel', [$selected->id]);
        provider::delete_data_for_users($userlist);

        $record = $DB->get_record('spinningwheel_spins', ['id' => $spinid]);
        $this->assertNull($record->selecteduserid);
        $this->assertEquals('Deleted User', $record->selectedtext);
    }

    public function test_delete_data_for_users_deletes_entries(): void {
        $this->resetAfterTest();
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');

        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', ['course' => $course->id]);

        $entry = new \stdClass();
        $entry->wheelid = $spinningwheel->id;
        $entry->userid = $user->id;
        $entry->text = fullname($user);
        $entry->timecreated = time();
        $DB->insert_record('spinningwheel_entries', $entry);

        $cm = get_coursemodule_from_instance('spinningwheel', $spinningwheel->id);
        $context = \context_module::instance($cm->id);

        $userlist = new approved_userlist($context, 'mod_spinningwheel', [$user->id]);
        provider::delete_data_for_users($userlist);

        $this->assertFalse($DB->record_exists('spinningwheel_entries', [
            'wheelid' => $spinningwheel->id,
            'userid' => $user->id,
        ]));
    }
}
