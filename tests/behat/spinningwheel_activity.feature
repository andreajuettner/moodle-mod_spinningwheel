# This file is part of Moodle - http://moodle.org/
#
# Moodle is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# Moodle is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
#
# @package    mod_spinningwheel
# @copyright  2026 Andrea Juettner, andrea.juettner@eledia.de; AI-assisted by Claude (Anthropic).
# @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

@mod @mod_spinningwheel
Feature: Spinning Wheel wheel activity
  In order to randomly select participants
  As a teacher
  I need to create and configure a Spinning Wheel wheel activity

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | One      | teacher1@example.com |
      | student1 | Student   | One      | student1@example.com |
      | student2 | Student   | Two      | student2@example.com |
      | student3 | Student   | Three    | student3@example.com |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
      | student3 | C1     | student        |

  @javascript
  Scenario: Teacher configures a Spinning Wheel activity with manual entries
    Given the following "activities" exist:
      | activity     | name       | course | idnumber      | entrysource |
      | spinningwheel | Test Wheel | C1     | spinningwheel1 | 1           |
    And I log in as "teacher1"
    And I am on the "Test Wheel" "spinningwheel activity" page
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I set the field "Entry source" to "Manual entries"
    And I set the field "Manual entries" to multiline:
      """
      Alice
      Bob
      Charlie
      """
    And I press "Save and display"
    Then I should see "Test Wheel"

  @javascript
  Scenario: Teacher views a Spinning Wheel activity with participants
    Given the following "activities" exist:
      | activity     | name              | course | idnumber      | entrysource |
      | spinningwheel | Participant Wheel | C1     | spinningwheel1 | 0           |
    And I log in as "teacher1"
    And I am on the "Participant Wheel" "spinningwheel activity" page
    Then I should see "Participant Wheel"

  @javascript
  Scenario: Student can view the Spinning Wheel activity
    Given the following "activities" exist:
      | activity | name       | course | idnumber  | entrysource |
      | spinningwheel | Test Wheel | C1     | spinningwheel1 | 1           |
    And I log in as "student1"
    And I am on "Course 1" course homepage
    When I follow "Test Wheel"
    Then I should see "Test Wheel"

  @javascript
  Scenario: Student without spin permission cannot spin
    Given the following "activities" exist:
      | activity | name       | course | idnumber  | entrysource | allowstudentspin |
      | spinningwheel | Test Wheel | C1     | spinningwheel1 | 0           | 0                |
    And I log in as "student1"
    And I am on "Course 1" course homepage
    When I follow "Test Wheel"
    Then "Spin!" "button" should not exist

  @javascript
  Scenario: Student with spin permission can see spin button
    Given the following "activities" exist:
      | activity | name       | course | idnumber  | entrysource | allowstudentspin |
      | spinningwheel | Test Wheel | C1     | spinningwheel1 | 0           | 1                |
    And I log in as "student1"
    And I am on "Course 1" course homepage
    When I follow "Test Wheel"
    Then "Spin!" "button" should exist

  @javascript
  Scenario: Teacher can view spin history
    Given the following "activities" exist:
      | activity | name       | course | idnumber  | entrysource |
      | spinningwheel | Test Wheel | C1     | spinningwheel1 | 0           |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    When I follow "Test Wheel"
    Then I should see "Spin history"

  @javascript
  Scenario: Student cannot view spin history
    Given the following "activities" exist:
      | activity | name       | course | idnumber  | entrysource | allowstudentspin |
      | spinningwheel | Test Wheel | C1     | spinningwheel1 | 0           | 1                |
    And I log in as "student1"
    And I am on "Course 1" course homepage
    When I follow "Test Wheel"
    Then I should not see "Spin history"

  @javascript
  Scenario: Teacher can update Spinning Wheel settings
    Given the following "activities" exist:
      | activity | name       | course | idnumber  | entrysource |
      | spinningwheel | Test Wheel | C1     | spinningwheel1 | 1           |
    And I log in as "teacher1"
    And I am on the "Test Wheel" "spinningwheel activity" page
    And I navigate to "Settings" in current page administration
    And I set the following fields to these values:
      | Name          | Updated Wheel |
      | Spin duration | 8000          |
    And I press "Save and display"
    Then I should see "Updated Wheel"

  Scenario: Spinning Wheel activity appears on course page
    Given the following "activities" exist:
      | activity | name       | course | idnumber  |
      | spinningwheel | My Wheel   | C1     | spinningwheel1 |
    When I log in as "student1"
    And I am on "Course 1" course homepage
    Then I should see "My Wheel"
