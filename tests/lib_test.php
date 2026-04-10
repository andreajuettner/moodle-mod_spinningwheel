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

namespace mod_spinningwheel;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/spinningwheel/lib.php');

/**
 * Unit tests for mod_spinningwheel lib functions.
 *
 * @package    mod_spinningwheel
 * @copyright  2026 Andrea Juettner, andrea.juettner@eledia.de; AI-assisted by Claude (Anthropic).
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \spinningwheel_supports
 * @covers     \spinningwheel_add_instance
 * @covers     \spinningwheel_delete_instance
 * @covers     \spinningwheel_update_instance
 * @covers     \spinningwheel_get_active_entries
 * @covers     \spinningwheel_format_name
 * @covers     \spinningwheel_view
 * @covers     \spinningwheel_reset_userdata
 * @covers     \spinningwheel_user_outline
 * @covers     \spinningwheel_save_manual_entries
 * @covers     \spinningwheel_get_coursemodule_info
 * @covers     \spinningwheel_page_type_list
 */
class lib_test extends \advanced_testcase {

    public function test_supports(): void {
        $this->assertTrue(spinningwheel_supports(FEATURE_MOD_INTRO));
        $this->assertTrue(spinningwheel_supports(FEATURE_BACKUP_MOODLE2));
        $this->assertTrue(spinningwheel_supports(FEATURE_COMPLETION_TRACKS_VIEWS));
        $this->assertTrue(spinningwheel_supports(FEATURE_COMPLETION_HAS_RULES));
        $this->assertTrue(spinningwheel_supports(FEATURE_GROUPS));
        $this->assertFalse(spinningwheel_supports(FEATURE_GRADE_HAS_GRADE));
        $this->assertNull(spinningwheel_supports('unknown_feature'));
    }

    public function test_add_and_delete_instance(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', ['course' => $course->id]);

        global $DB;
        $this->assertTrue($DB->record_exists('spinningwheel', ['id' => $spinningwheel->id]));

        $result = spinningwheel_delete_instance($spinningwheel->id);
        $this->assertTrue($result);
        $this->assertFalse($DB->record_exists('spinningwheel', ['id' => $spinningwheel->id]));
    }

    public function test_delete_instance_nonexistent(): void {
        $this->resetAfterTest();
        $this->assertFalse(spinningwheel_delete_instance(99999));
    }

    public function test_delete_instance_cascades(): void {
        $this->resetAfterTest();
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', [
            'course' => $course->id,
            'entrysource' => SPINNINGWHEEL_SOURCE_MANUAL,
        ]);

        $entry = new \stdClass();
        $entry->wheelid = $spinningwheel->id;
        $entry->text = 'Test';
        $entry->sortorder = 0;
        $entry->active = 1;
        $entry->timecreated = time();
        $DB->insert_record('spinningwheel_entries', $entry);

        $spin = new \stdClass();
        $spin->wheelid = $spinningwheel->id;
        $spin->userid = 2;
        $spin->selectedtext = 'Test';
        $spin->timecreated = time();
        $DB->insert_record('spinningwheel_spins', $spin);

        spinningwheel_delete_instance($spinningwheel->id);

        $this->assertFalse($DB->record_exists('spinningwheel_entries', ['wheelid' => $spinningwheel->id]));
        $this->assertFalse($DB->record_exists('spinningwheel_spins', ['wheelid' => $spinningwheel->id]));
    }

    public function test_update_instance(): void {
        $this->resetAfterTest();
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', ['course' => $course->id]);

        $data = $DB->get_record('spinningwheel', ['id' => $spinningwheel->id]);
        $data->instance = $data->id;
        $data->coursemodule = $spinningwheel->cmid;
        $data->name = 'Updated Wheel';
        $data->spintime = 8000;

        $result = spinningwheel_update_instance($data);
        $this->assertTrue($result);

        $updated = $DB->get_record('spinningwheel', ['id' => $spinningwheel->id]);
        $this->assertEquals('Updated Wheel', $updated->name);
        $this->assertEquals(8000, $updated->spintime);
    }

    public function test_get_active_entries_manual(): void {
        $this->resetAfterTest();
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', [
            'course' => $course->id,
            'entrysource' => SPINNINGWHEEL_SOURCE_MANUAL,
        ]);

        $cm = get_coursemodule_from_instance('spinningwheel', $spinningwheel->id);
        $context = \context_module::instance($cm->id);

        for ($i = 1; $i <= 5; $i++) {
            $entry = new \stdClass();
            $entry->wheelid = $spinningwheel->id;
            $entry->text = "Entry $i";
            $entry->sortorder = $i;
            $entry->active = 1;
            $entry->timecreated = time();
            $DB->insert_record('spinningwheel_entries', $entry);
        }

        $instance = $DB->get_record('spinningwheel', ['id' => $spinningwheel->id]);
        $entries = spinningwheel_get_active_entries($instance, $context);
        $this->assertCount(5, $entries);
        $this->assertEquals('Entry 1', $entries[0]->text);
    }

    public function test_get_active_entries_participants(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', [
            'course' => $course->id,
            'entrysource' => SPINNINGWHEEL_SOURCE_PARTICIPANTS,
        ]);

        for ($i = 0; $i < 3; $i++) {
            $user = $this->getDataGenerator()->create_user();
            $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        }

        global $DB;
        $cm = get_coursemodule_from_instance('spinningwheel', $spinningwheel->id);
        $context = \context_module::instance($cm->id);
        $instance = $DB->get_record('spinningwheel', ['id' => $spinningwheel->id]);

        $entries = spinningwheel_get_active_entries($instance, $context);
        $this->assertCount(3, $entries);
    }

    public function test_get_active_entries_maxvisible(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', [
            'course' => $course->id,
            'entrysource' => SPINNINGWHEEL_SOURCE_PARTICIPANTS,
            'maxvisible' => 3,
        ]);

        for ($i = 0; $i < 10; $i++) {
            $user = $this->getDataGenerator()->create_user();
            $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        }

        global $DB;
        $cm = get_coursemodule_from_instance('spinningwheel', $spinningwheel->id);
        $context = \context_module::instance($cm->id);
        $instance = $DB->get_record('spinningwheel', ['id' => $spinningwheel->id]);

        $entries = spinningwheel_get_active_entries($instance, $context);
        $this->assertCount(3, $entries);
    }

    public function test_get_active_entries_empty(): void {
        $this->resetAfterTest();
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', [
            'course' => $course->id,
            'entrysource' => SPINNINGWHEEL_SOURCE_MANUAL,
        ]);

        $cm = get_coursemodule_from_instance('spinningwheel', $spinningwheel->id);
        $context = \context_module::instance($cm->id);
        $instance = $DB->get_record('spinningwheel', ['id' => $spinningwheel->id]);

        $entries = spinningwheel_get_active_entries($instance, $context);
        $this->assertEmpty($entries);
    }

    public function test_format_name_fullname(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user(['firstname' => 'Max', 'lastname' => 'Mustermann']);
        $this->assertEquals(fullname($user), spinningwheel_format_name($user, 0));
    }

    public function test_format_name_firstname(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user(['firstname' => 'Max', 'lastname' => 'Mustermann']);
        $this->assertEquals('Max', spinningwheel_format_name($user, 1));
    }

    public function test_format_name_lastname(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user(['firstname' => 'Max', 'lastname' => 'Mustermann']);
        $this->assertEquals('Mustermann', spinningwheel_format_name($user, 2));
    }

    public function test_format_name_firstinitial(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user(['firstname' => 'Max', 'lastname' => 'Mustermann']);
        $this->assertEquals('Max M.', spinningwheel_format_name($user, 3));
    }

    public function test_view_triggers_event(): void {
        $this->resetAfterTest();
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', ['course' => $course->id]);

        $cm = get_coursemodule_from_instance('spinningwheel', $spinningwheel->id);
        $context = \context_module::instance($cm->id);
        $instance = $DB->get_record('spinningwheel', ['id' => $spinningwheel->id]);

        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);
        $this->setUser($user);

        $sink = $this->redirectEvents();
        spinningwheel_view($instance, $course, $cm, $context);
        $events = $sink->get_events();
        $sink->close();

        $this->assertNotEmpty($events);
        $event = reset($events);
        $this->assertInstanceOf(\mod_spinningwheel\event\course_module_viewed::class, $event);
        $this->assertEquals($context, $event->get_context());
    }

    public function test_reset_userdata(): void {
        $this->resetAfterTest();
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', [
            'course' => $course->id,
            'entrysource' => SPINNINGWHEEL_SOURCE_MANUAL,
        ]);

        $entry = new \stdClass();
        $entry->wheelid = $spinningwheel->id;
        $entry->text = 'Test';
        $entry->sortorder = 0;
        $entry->active = 0;
        $entry->timecreated = time();
        $DB->insert_record('spinningwheel_entries', $entry);

        $spin = new \stdClass();
        $spin->wheelid = $spinningwheel->id;
        $spin->userid = 2;
        $spin->selectedtext = 'Test';
        $spin->timecreated = time();
        $DB->insert_record('spinningwheel_spins', $spin);

        $data = (object) ['courseid' => $course->id, 'reset_spinningwheel' => 1];
        $status = spinningwheel_reset_userdata($data);

        $this->assertNotEmpty($status);
        $this->assertFalse($DB->record_exists('spinningwheel_spins', ['wheelid' => $spinningwheel->id]));

        $reactivated = $DB->get_record('spinningwheel_entries', ['wheelid' => $spinningwheel->id]);
        $this->assertEquals(1, $reactivated->active);
    }

    public function test_user_outline_with_spins(): void {
        $this->resetAfterTest();
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', ['course' => $course->id]);

        $spin = new \stdClass();
        $spin->wheelid = $spinningwheel->id;
        $spin->userid = $user->id;
        $spin->selectedtext = 'Winner';
        $spin->timecreated = time();
        $DB->insert_record('spinningwheel_spins', $spin);

        $instance = $DB->get_record('spinningwheel', ['id' => $spinningwheel->id]);
        $result = spinningwheel_user_outline($course, $user, null, $instance);

        $this->assertNotNull($result);
        $this->assertStringContainsString('1', $result->info);
    }

    public function test_user_outline_no_spins(): void {
        $this->resetAfterTest();
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', ['course' => $course->id]);

        $instance = $DB->get_record('spinningwheel', ['id' => $spinningwheel->id]);
        $result = spinningwheel_user_outline($course, $user, null, $instance);

        $this->assertNull($result);
    }

    public function test_get_coursemodule_info(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', [
            'course' => $course->id,
            'name' => 'Test Wheel',
        ]);

        $cm = get_coursemodule_from_instance('spinningwheel', $spinningwheel->id);
        $info = spinningwheel_get_coursemodule_info($cm);

        $this->assertInstanceOf(\cached_cm_info::class, $info);
        $this->assertEquals('Test Wheel', $info->name);
    }

    public function test_page_type_list(): void {
        $result = spinningwheel_page_type_list('mod-spinningwheel-view', null, null);
        $this->assertArrayHasKey('mod-spinningwheel-*', $result);
    }

    public function test_view_actions(): void {
        $this->assertEquals(['view'], spinningwheel_get_view_actions());
        $this->assertEquals(['spin'], spinningwheel_get_post_actions());
    }

    public function test_save_manual_entries(): void {
        $this->resetAfterTest();
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', ['course' => $course->id]);

        spinningwheel_save_manual_entries($spinningwheel->id, "Alice\nBob\nCharlie");

        $entries = $DB->get_records('spinningwheel_entries', ['wheelid' => $spinningwheel->id], 'sortorder ASC');
        $this->assertCount(3, $entries);
        $entries = array_values($entries);
        $this->assertEquals('Alice', $entries[0]->text);
        $this->assertEquals('Bob', $entries[1]->text);
        $this->assertEquals('Charlie', $entries[2]->text);
        $this->assertEquals(0, $entries[0]->sortorder);
        $this->assertEquals(2, $entries[2]->sortorder);
    }

    /**
     * Test that get_active_entries in activities mode returns course activities.
     */
    public function test_get_active_entries_activities_mode(): void {
        $this->resetAfterTest();
        global $DB;

        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $this->setUser($user);

        // Create some other activities in the course.
        $assign = $this->getDataGenerator()->create_module('assign', ['course' => $course->id, 'name' => 'Test Assignment']);
        $page = $this->getDataGenerator()->create_module('page', ['course' => $course->id, 'name' => 'Test Page']);
        $forum = $this->getDataGenerator()->create_module('forum', ['course' => $course->id, 'name' => 'Test Forum']);

        // Create the spinning wheel with activities mode.
        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', [
            'course' => $course->id,
            'entrysource' => SPINNINGWHEEL_SOURCE_ACTIVITIES,
        ]);

        $cm = get_coursemodule_from_instance('spinningwheel', $spinningwheel->id);
        $context = \context_module::instance($cm->id);
        $instance = $DB->get_record('spinningwheel', ['id' => $spinningwheel->id]);

        $entries = spinningwheel_get_active_entries($instance, $context);

        // Should contain the 3 other activities but not the wheel itself.
        $this->assertGreaterThanOrEqual(3, count($entries));
        $texts = array_column($entries, 'text');
        $this->assertContains('Test Assignment', $texts);
        $this->assertContains('Test Page', $texts);
        $this->assertContains('Test Forum', $texts);
    }

    /**
     * Test that get_course_activity_entries excludes the wheel itself.
     */
    public function test_get_course_activity_entries_excludes_self(): void {
        $this->resetAfterTest();
        global $DB;

        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $this->setUser($user);

        $assign = $this->getDataGenerator()->create_module('assign', ['course' => $course->id, 'name' => 'My Assignment']);

        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', [
            'course' => $course->id,
            'entrysource' => SPINNINGWHEEL_SOURCE_ACTIVITIES,
            'name' => 'My Wheel',
        ]);

        $cm = get_coursemodule_from_instance('spinningwheel', $spinningwheel->id);
        $context = \context_module::instance($cm->id);
        $instance = $DB->get_record('spinningwheel', ['id' => $spinningwheel->id]);

        $entries = spinningwheel_get_course_activity_entries($instance, $context);

        $cmids = array_map('intval', array_column($entries, 'cmid'));
        $this->assertNotContains((int) $cm->id, $cmids, 'Wheel itself should not be in entries');

        // The assignment should be in the list.
        $assigncm = get_coursemodule_from_instance('assign', $assign->id);
        $this->assertContains((int) $assigncm->id, $cmids, 'Assignment should be in entries');
    }

    /**
     * Test that completed activities are marked with completed=true and active=0.
     */
    public function test_get_course_activity_entries_completed_marked(): void {
        $this->resetAfterTest();
        global $DB;

        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $this->setUser($user);

        // Create an activity with completion enabled.
        $assign = $this->getDataGenerator()->create_module('assign', [
            'course' => $course->id,
            'name' => 'Completable Assignment',
            'completion' => COMPLETION_TRACKING_MANUAL,
        ]);

        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', [
            'course' => $course->id,
            'entrysource' => SPINNINGWHEEL_SOURCE_ACTIVITIES,
        ]);

        $assigncm = get_coursemodule_from_instance('assign', $assign->id);

        // Mark the activity as complete for the user.
        $completion = new \completion_info($course);
        $completion->update_state($assigncm, COMPLETION_COMPLETE, $user->id);

        $cm = get_coursemodule_from_instance('spinningwheel', $spinningwheel->id);
        $context = \context_module::instance($cm->id);
        $instance = $DB->get_record('spinningwheel', ['id' => $spinningwheel->id]);

        $entries = spinningwheel_get_course_activity_entries($instance, $context);

        // Find the assignment entry.
        $assignentry = null;
        foreach ($entries as $entry) {
            if ($entry->cmid == $assigncm->id) {
                $assignentry = $entry;
                break;
            }
        }

        $this->assertNotNull($assignentry, 'Assignment entry should exist in entries list');
        $this->assertTrue($assignentry->completed);
        $this->assertEquals(0, $assignentry->active);
    }

    /**
     * Test that get_pending_activity returns null when no spins have been recorded.
     */
    public function test_get_pending_activity_none(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $this->setUser($user);

        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', [
            'course' => $course->id,
            'entrysource' => SPINNINGWHEEL_SOURCE_ACTIVITIES,
        ]);

        $result = spinningwheel_get_pending_activity($spinningwheel->id, $user->id, $course);
        $this->assertNull($result);
    }

    /**
     * Test that get_pending_activity returns the activity info when a spin has been
     * recorded but the activity is not yet completed.
     */
    public function test_get_pending_activity_with_incomplete(): void {
        $this->resetAfterTest();
        global $DB;

        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $this->setUser($user);

        $assign = $this->getDataGenerator()->create_module('assign', [
            'course' => $course->id,
            'name' => 'Pending Assignment',
            'completion' => COMPLETION_TRACKING_MANUAL,
        ]);
        $assigncm = get_coursemodule_from_instance('assign', $assign->id);

        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', [
            'course' => $course->id,
            'entrysource' => SPINNINGWHEEL_SOURCE_ACTIVITIES,
        ]);

        // Record a spin with selectedcmid pointing to the assignment.
        $spin = new \stdClass();
        $spin->wheelid = $spinningwheel->id;
        $spin->userid = $user->id;
        $spin->selectedcmid = $assigncm->id;
        $spin->selectedtext = 'Pending Assignment';
        $spin->timecreated = time();
        $DB->insert_record('spinningwheel_spins', $spin);

        $result = spinningwheel_get_pending_activity($spinningwheel->id, $user->id, $course);

        $this->assertNotNull($result);
        $this->assertEquals($assigncm->id, $result->cmid);
        $this->assertStringContainsString('Pending Assignment', $result->name);
    }

    /**
     * Test that get_pending_activity returns null when the spun activity has been completed.
     */
    public function test_get_pending_activity_after_completion(): void {
        $this->resetAfterTest();
        global $DB;

        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $this->setUser($user);

        $assign = $this->getDataGenerator()->create_module('assign', [
            'course' => $course->id,
            'name' => 'Done Assignment',
            'completion' => COMPLETION_TRACKING_MANUAL,
        ]);
        $assigncm = get_coursemodule_from_instance('assign', $assign->id);

        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', [
            'course' => $course->id,
            'entrysource' => SPINNINGWHEEL_SOURCE_ACTIVITIES,
        ]);

        // Record a spin with selectedcmid pointing to the assignment.
        $spin = new \stdClass();
        $spin->wheelid = $spinningwheel->id;
        $spin->userid = $user->id;
        $spin->selectedcmid = $assigncm->id;
        $spin->selectedtext = 'Done Assignment';
        $spin->timecreated = time();
        $DB->insert_record('spinningwheel_spins', $spin);

        // Mark the activity as complete.
        $completion = new \completion_info($course);
        $completion->update_state($assigncm, COMPLETION_COMPLETE, $user->id);

        $result = spinningwheel_get_pending_activity($spinningwheel->id, $user->id, $course);
        $this->assertNull($result);
    }
}
