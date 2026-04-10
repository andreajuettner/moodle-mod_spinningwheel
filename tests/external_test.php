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

use mod_spinningwheel\external\spin_wheel;
use mod_spinningwheel\external\get_entries;
use mod_spinningwheel\external\get_history;

/**
 * Unit tests for mod_spinningwheel external API functions.
 *
 * @package    mod_spinningwheel
 * @copyright  2026 Andrea Juettner, andrea.juettner@eledia.de; AI-assisted by Claude (Anthropic).
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_spinningwheel\external\spin_wheel
 * @covers     \mod_spinningwheel\external\get_entries
 * @covers     \mod_spinningwheel\external\get_history
 */
class external_test extends \advanced_testcase {

    public function test_spin_wheel_manual(): void {
        $this->resetAfterTest();
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');

        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', [
            'course' => $course->id,
            'entrysource' => SPINNINGWHEEL_SOURCE_MANUAL,
        ]);

        spinningwheel_save_manual_entries($spinningwheel->id, "Alice\nBob\nCharlie");

        $cm = get_coursemodule_from_instance('spinningwheel', $spinningwheel->id);
        $this->setUser($teacher);

        $result = spin_wheel::execute($cm->id);
        $result = \core_external\external_api::clean_returnvalue(spin_wheel::execute_returns(), $result);

        $this->assertNotEmpty($result['selectedtext']);
        $this->assertContains($result['selectedtext'], ['Alice', 'Bob', 'Charlie']);
        $this->assertGreaterThanOrEqual(0, $result['selectedindex']);
        $this->assertLessThanOrEqual(2, $result['selectedindex']);

        // Verify spin was recorded.
        $this->assertTrue($DB->record_exists('spinningwheel_spins', [
            'wheelid' => $spinningwheel->id,
            'userid' => $teacher->id,
        ]));
    }

    public function test_spin_wheel_no_permission(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');

        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', [
            'course' => $course->id,
            'entrysource' => SPINNINGWHEEL_SOURCE_MANUAL,
            'allowstudentspin' => 0,
        ]);

        spinningwheel_save_manual_entries($spinningwheel->id, "Alice\nBob");

        $cm = get_coursemodule_from_instance('spinningwheel', $spinningwheel->id);
        $this->setUser($student);

        $this->expectException(\required_capability_exception::class);
        spin_wheel::execute($cm->id);
    }

    public function test_spin_wheel_student_allowed(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');

        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', [
            'course' => $course->id,
            'entrysource' => SPINNINGWHEEL_SOURCE_MANUAL,
            'allowstudentspin' => 1,
        ]);

        spinningwheel_save_manual_entries($spinningwheel->id, "Alice\nBob");

        $cm = get_coursemodule_from_instance('spinningwheel', $spinningwheel->id);
        $this->setUser($student);

        $result = spin_wheel::execute($cm->id);
        $result = \core_external\external_api::clean_returnvalue(spin_wheel::execute_returns(), $result);

        $this->assertContains($result['selectedtext'], ['Alice', 'Bob']);
    }

    public function test_spin_wheel_max_spins_reached(): void {
        $this->resetAfterTest();
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');

        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', [
            'course' => $course->id,
            'entrysource' => SPINNINGWHEEL_SOURCE_MANUAL,
            'maxspins' => 1,
        ]);

        spinningwheel_save_manual_entries($spinningwheel->id, "Alice\nBob");

        // Insert a spin record to reach max.
        $spin = new \stdClass();
        $spin->wheelid = $spinningwheel->id;
        $spin->userid = $teacher->id;
        $spin->selectedtext = 'Alice';
        $spin->timecreated = time();
        $DB->insert_record('spinningwheel_spins', $spin);

        $cm = get_coursemodule_from_instance('spinningwheel', $spinningwheel->id);
        $this->setUser($teacher);

        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('maxspinsreached', 'spinningwheel'));
        spin_wheel::execute($cm->id);
    }

    public function test_spin_wheel_empty_entries(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');

        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', [
            'course' => $course->id,
            'entrysource' => SPINNINGWHEEL_SOURCE_MANUAL,
        ]);

        $cm = get_coursemodule_from_instance('spinningwheel', $spinningwheel->id);
        $this->setUser($teacher);

        $this->expectException(\moodle_exception::class);
        spin_wheel::execute($cm->id);
    }

    public function test_spin_wheel_removeafter(): void {
        $this->resetAfterTest();
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');

        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', [
            'course' => $course->id,
            'entrysource' => SPINNINGWHEEL_SOURCE_MANUAL,
            'removeafter' => 1,
        ]);

        // Add a single entry.
        spinningwheel_save_manual_entries($spinningwheel->id, "OnlyEntry");

        $cm = get_coursemodule_from_instance('spinningwheel', $spinningwheel->id);
        $this->setUser($teacher);

        $result = spin_wheel::execute($cm->id);
        $result = \core_external\external_api::clean_returnvalue(spin_wheel::execute_returns(), $result);

        $this->assertEquals('OnlyEntry', $result['selectedtext']);
        $this->assertEquals(0, $result['remainingcount']);

        // The entry should now be deactivated.
        $entry = $DB->get_record('spinningwheel_entries', ['wheelid' => $spinningwheel->id]);
        $this->assertEquals(0, (int) $entry->active);
    }

    public function test_spin_wheel_triggers_event(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');

        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', [
            'course' => $course->id,
            'entrysource' => SPINNINGWHEEL_SOURCE_MANUAL,
        ]);

        spinningwheel_save_manual_entries($spinningwheel->id, "Alice\nBob");

        $cm = get_coursemodule_from_instance('spinningwheel', $spinningwheel->id);
        $this->setUser($teacher);

        $sink = $this->redirectEvents();
        spin_wheel::execute($cm->id);
        $events = $sink->get_events();
        $sink->close();

        $spunevents = array_filter($events, function ($e) {
            return $e instanceof \mod_spinningwheel\event\wheel_spun;
        });
        $this->assertNotEmpty($spunevents);
    }

    public function test_get_entries_manual(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');

        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', [
            'course' => $course->id,
            'entrysource' => SPINNINGWHEEL_SOURCE_MANUAL,
        ]);

        spinningwheel_save_manual_entries($spinningwheel->id, "Alice\nBob\nCharlie");

        $cm = get_coursemodule_from_instance('spinningwheel', $spinningwheel->id);
        $this->setUser($teacher);

        $result = get_entries::execute($cm->id);
        $result = \core_external\external_api::clean_returnvalue(get_entries::execute_returns(), $result);

        $this->assertCount(3, $result['entries']);
        $texts = array_column($result['entries'], 'text');
        $this->assertContains('Alice', $texts);
        $this->assertContains('Bob', $texts);
        $this->assertContains('Charlie', $texts);
    }

    public function test_get_entries_participants(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');

        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', [
            'course' => $course->id,
            'entrysource' => SPINNINGWHEEL_SOURCE_PARTICIPANTS,
        ]);

        for ($i = 0; $i < 3; $i++) {
            $user = $this->getDataGenerator()->create_user();
            $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        }

        $cm = get_coursemodule_from_instance('spinningwheel', $spinningwheel->id);
        $this->setUser($teacher);

        $result = get_entries::execute($cm->id);
        $result = \core_external\external_api::clean_returnvalue(get_entries::execute_returns(), $result);

        // At least 3 students + the teacher.
        $this->assertGreaterThanOrEqual(3, count($result['entries']));
    }

    public function test_get_entries_empty(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');

        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', [
            'course' => $course->id,
            'entrysource' => SPINNINGWHEEL_SOURCE_MANUAL,
        ]);

        $cm = get_coursemodule_from_instance('spinningwheel', $spinningwheel->id);
        $this->setUser($teacher);

        $result = get_entries::execute($cm->id);
        $result = \core_external\external_api::clean_returnvalue(get_entries::execute_returns(), $result);

        $this->assertEmpty($result['entries']);
    }

    public function test_get_history(): void {
        $this->resetAfterTest();
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');

        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', [
            'course' => $course->id,
        ]);

        // Insert spin records.
        for ($i = 1; $i <= 3; $i++) {
            $spin = new \stdClass();
            $spin->wheelid = $spinningwheel->id;
            $spin->userid = $teacher->id;
            $spin->selectedtext = "Entry $i";
            $spin->timecreated = time() + $i;
            $DB->insert_record('spinningwheel_spins', $spin);
        }

        $cm = get_coursemodule_from_instance('spinningwheel', $spinningwheel->id);
        $this->setUser($teacher);

        $result = get_history::execute($cm->id);
        $result = \core_external\external_api::clean_returnvalue(get_history::execute_returns(), $result);

        $this->assertCount(3, $result['spins']);
        // Ordered by timecreated DESC.
        $this->assertEquals('Entry 3', $result['spins'][0]['selectedtext']);
        $this->assertEquals('Entry 1', $result['spins'][2]['selectedtext']);
    }

    public function test_get_history_no_permission(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');

        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', [
            'course' => $course->id,
        ]);

        $cm = get_coursemodule_from_instance('spinningwheel', $spinningwheel->id);
        $this->setUser($student);

        $this->expectException(\required_capability_exception::class);
        get_history::execute($cm->id);
    }

    public function test_get_history_empty(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');

        $spinningwheel = $this->getDataGenerator()->create_module('spinningwheel', [
            'course' => $course->id,
        ]);

        $cm = get_coursemodule_from_instance('spinningwheel', $spinningwheel->id);
        $this->setUser($teacher);

        $result = get_history::execute($cm->id);
        $result = \core_external\external_api::clean_returnvalue(get_history::execute_returns(), $result);

        $this->assertEmpty($result['spins']);
    }
}
