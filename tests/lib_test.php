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
}
