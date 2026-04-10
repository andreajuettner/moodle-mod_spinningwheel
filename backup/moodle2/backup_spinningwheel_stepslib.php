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
 * Backup steps for mod_spinningwheel.
 *
 * @package    mod_spinningwheel
 * @subpackage backup-moodle2
 * @copyright  2026 Andrea Juettner, andrea.juettner@eledia.de; AI-assisted by Claude (Anthropic).
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Backup structure step for mod_spinningwheel.
 */
class backup_spinningwheel_activity_structure_step extends backup_activity_structure_step {

    /**
     * Define the backup structure for the spinningwheel activity.
     *
     * @return backup_nested_element The root element wrapped in standard activity structure.
     */
    #[\Override]
    protected function define_structure() {
        $userinfo = $this->get_setting_value('userinfo');

        $spinningwheel = new backup_nested_element('spinningwheel', ['id'], [
            'name', 'intro', 'introformat', 'entrysource', 'rolefilter',
            'removeafter', 'spintime', 'colors', 'displaymode', 'maxvisible',
            'showconfetti', 'showshadow', 'showtitle', 'tickingsound', 'winnermessage', 'celebratesound',
            'nameformat', 'allowstudentspin', 'maxspins', 'completionspin', 'embedoncourse', 'timecreated', 'timemodified',
        ]);

        $entries = new backup_nested_element('entries');
        $entry = new backup_nested_element('entry', ['id'], [
            'userid', 'text', 'sortorder', 'active', 'timecreated',
        ]);

        $spins = new backup_nested_element('spins');
        $spin = new backup_nested_element('spin', ['id'], [
            'userid', 'selectedentryid', 'selecteduserid', 'selectedtext',
            'groupid', 'timecreated',
        ]);

        $spinningwheel->add_child($entries);
        $entries->add_child($entry);

        $spinningwheel->add_child($spins);
        $spins->add_child($spin);

        $spinningwheel->set_source_table('spinningwheel', ['id' => backup::VAR_ACTIVITYID]);
        $entry->set_source_table('spinningwheel_entries', ['wheelid' => backup::VAR_PARENTID], 'id ASC');

        if ($userinfo) {
            $spin->set_source_table('spinningwheel_spins', ['wheelid' => backup::VAR_PARENTID]);
        }

        $entry->annotate_ids('user', 'userid');
        $spin->annotate_ids('user', 'userid');
        $spin->annotate_ids('user', 'selecteduserid');

        $spinningwheel->annotate_files('mod_spinningwheel', 'intro', null);

        return $this->prepare_activity_structure($spinningwheel);
    }
}
