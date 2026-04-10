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
 * Restore steps for mod_spinningwheel.
 *
 * @package    mod_spinningwheel
 * @subpackage backup-moodle2
 * @copyright  2026 Andrea Juettner, andrea.juettner@eledia.de; AI-assisted by Claude (Anthropic).
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class restore_spinningwheel_activity_structure_step extends restore_activity_structure_step {

    /**
     * Define the restore structure for the spinningwheel activity.
     *
     * @return array The restore paths.
     */
    #[\Override]
    protected function define_structure() {
        $paths = [];
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('spinningwheel', '/activity/spinningwheel');
        $paths[] = new restore_path_element('spinningwheel_entry', '/activity/spinningwheel/entries/entry');
        if ($userinfo) {
            $paths[] = new restore_path_element('spinningwheel_spin', '/activity/spinningwheel/spins/spin');
        }

        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process the spinningwheel element data during restore.
     *
     * @param array $data The element data.
     */
    protected function process_spinningwheel($data) {
        global $DB;

        $data = (object) $data;
        $data->course = $this->get_courseid();

        $newitemid = $DB->insert_record('spinningwheel', $data);
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Process a spinningwheel entry element during restore.
     *
     * @param array $data The element data.
     */
    protected function process_spinningwheel_entry($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        $data->wheelid = $this->get_new_parentid('spinningwheel');
        if (!empty($data->userid)) {
            $data->userid = $this->get_mappingid('user', $data->userid);
        }

        $newitemid = $DB->insert_record('spinningwheel_entries', $data);
        $this->set_mapping('spinningwheel_entry', $oldid, $newitemid);
    }

    /**
     * Process a spinningwheel spin element during restore.
     *
     * @param array $data The element data.
     */
    protected function process_spinningwheel_spin($data) {
        global $DB;

        $data = (object) $data;

        $data->wheelid = $this->get_new_parentid('spinningwheel');
        $data->userid = $this->get_mappingid('user', $data->userid);
        if (!empty($data->selectedentryid)) {
            $data->selectedentryid = $this->get_mappingid('spinningwheel_entry', $data->selectedentryid);
        }
        if (!empty($data->selecteduserid)) {
            $data->selecteduserid = $this->get_mappingid('user', $data->selecteduserid);
        }
        if (!empty($data->selectedcmid)) {
            $data->selectedcmid = $this->get_mappingid('course_module', $data->selectedcmid);
        }

        $DB->insert_record('spinningwheel_spins', $data);
    }

    /**
     * Execute actions after all elements have been restored.
     */
    #[\Override]
    protected function after_execute() {
        $this->add_related_files('mod_spinningwheel', 'intro', null);
    }
}
