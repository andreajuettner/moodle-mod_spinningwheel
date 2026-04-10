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
 * Upgrade steps for mod_spinningwheel.
 *
 * @package    mod_spinningwheel
 * @copyright  2026 Andrea Juettner, andrea.juettner@eledia.de; AI-assisted by Claude (Anthropic).
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_spinningwheel_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2026021200) {
        $table = new xmldb_table('spinningwheel');

        $field = new xmldb_field('displaymode', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'colors');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('maxvisible', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'displaymode');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('showconfetti', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '1', 'maxvisible');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('showshadow', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '1', 'showconfetti');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('showtitle', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '1', 'showshadow');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('tickingsound', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '1', 'showtitle');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('winnermessage', XMLDB_TYPE_TEXT, null, null, null, null, null, 'tickingsound');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2026021200, 'spinningwheel');
    }

    if ($oldversion < 2026021201) {
        $table = new xmldb_table('spinningwheel');

        $field = new xmldb_field('celebratesound', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '1', 'winnermessage');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2026021201, 'spinningwheel');
    }

    if ($oldversion < 2026021202) {
        $table = new xmldb_table('spinningwheel');

        $field = new xmldb_field('nameformat', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'celebratesound');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2026021202, 'spinningwheel');
    }

    if ($oldversion < 2026022002) {
        $table = new xmldb_table('spinningwheel');

        $field = new xmldb_field('embedoncourse', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'completionspin');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2026022002, 'spinningwheel');
    }

    return true;
}
