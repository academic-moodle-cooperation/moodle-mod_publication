<?php
// This file is part of mod_publication for Moodle - http://moodle.org/
//
// It is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// It is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Keeps track of all DB(-structure) changes and other upgrade steps for mod_publication
 *
 * @package       mod_publication
 * @author        Philipp Hager
 * @author        Andreas Windbichler
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Handles all the upgrade steps for mod_publication
 *
 * @param int $oldversion the currently installed publication version
 * @return bool true if everythings allright
 */
function xmldb_publication_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2014032201) {
        $table = new xmldb_table('publication_file');

        // Add field alwaysshowdescription.
        $field = new xmldb_field('filesourceid', XMLDB_TYPE_INTEGER, '10', false, false, false, '0', 'fileid');

        // Conditionally launch add field alwaysshowdescription.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Organizer savepoint reached.
        upgrade_mod_savepoint(true, 2014032201, 'publication');
    }

    if ($oldversion < 2015120201) {
        // Remove unused settings (requiremodintro and duplicates of stdexamplecount and requiremodintro)!
        $DB->delete_records('config_plugins', array('plugin' => 'publication',
                                                    'name'   => 'requiremodintro'));

        upgrade_mod_savepoint(true, 2015120201, 'publication');
    }

    // Moodle v3.1.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2016051200) {

        // Define field autoimport to be added to publication.
        $table = new xmldb_table('publication');
        $field = new xmldb_field('autoimport', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'obtainteacherapproval');

        // Conditionally launch add field autoimport.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Assign savepoint reached.
        upgrade_mod_savepoint(true, 2016051200, 'publication');
    }

    if ($oldversion < 2016062201) {

        // Define field groupapproval to be added to publication.
        $table = new xmldb_table('publication');
        $field = new xmldb_field('groupapproval', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'autoimport');

        // Conditionally launch add field groupapproval.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define table publication_groupapproval to be created.
        $table = new xmldb_table('publication_groupapproval');

        // Adding fields to table publication_groupapproval.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('fileid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('approval', XMLDB_TYPE_INTEGER, '4', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Adding keys to table publication_groupapproval.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('fileid', XMLDB_KEY_FOREIGN, array('fileid'), 'publication_files', array('id'));
        $table->add_key('userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));

        // Conditionally launch create table for publication_groupapproval.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define field groupapproval to be added to publication.
        $table = new xmldb_table('publication_groupapproval');

        $field = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'approval');
        // Conditionally launch add field groupapproval.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'timecreated');
        // Conditionally launch add field groupapproval.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Publication savepoint reached.
        upgrade_mod_savepoint(true, 2016062201, 'publication');
    }

    // Moodle v3.2.0 release upgrade line.
    // Put any upgrade step following this!

    return true;
}
