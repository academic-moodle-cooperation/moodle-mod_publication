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
        $DB->delete_records('config_plugins', [
                'plugin' => 'publication',
                'name' => 'requiremodintro'
        ]);

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
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('fileid', XMLDB_KEY_FOREIGN, ['fileid'], 'publication_files', ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);

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

    // Moodle v3.3.0 release upgrade line.
    // Put any upgrade step following this!

    if ($oldversion < 2017071200) {
        // Get all old filetype-restrictions and convert them!
        $rs = $DB->get_recordset_sql("SELECT id id, allowedfiletypes allowedfiletypes
                                        FROM {publication}
                                       WHERE " . $DB->sql_isnotempty('publication', 'allowedfiletypes', false, true));
        echo "<pre>";
        foreach ($rs as $cur) {
            // We only convert old style entries!
            if (!preg_match('/^([\.A-Za-z0-9]+([ ]*[,][ ]*[\.A-Za-z0-9]+)*)$/', $cur->allowedfiletypes)) {
                echo "Skipping record with ID " . $cur->id . " having filetypes '" . $cur->allowedfiletypes . "'' allowed!<br />\n";
                continue;
            }

            $allowedfiletypes = preg_split('([ ]*[,][ ]*)', $cur->allowedfiletypes);
            array_walk($allowedfiletypes, function (&$type) {
                if ((strpos($type, '.') === false) || (strpos($type, '.') !== 0)) {
                    $type = '.' . $type;
                }
            });
            echo "Update allowedfiletypes for ID " . $cur->id . ": " . $cur->allowedfiletypes . " --> " .
                    implode('; ', $allowedfiletypes) .
                    "<br />\n";
            $cur->allowedfiletypes = implode('; ', $allowedfiletypes);
            $DB->update_record('publication', $cur);
        }
        echo "</pre>";
        $rs->close();

        // Publication savepoint reached.
        upgrade_mod_savepoint(true, 2017071200, 'publication');
    }

    if ($oldversion < 2019052100) {

        // Define field notifyteacher to be added to publication.
        $table = new xmldb_table('publication');
        $field = new xmldb_field('notifyteacher', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '1', 'groupapproval');
        $field2 = new xmldb_field('notifystudents', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'notifyteacher');

        // Conditionally launch add field notifyteacher.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }

        // Publication savepoint reached.
        upgrade_mod_savepoint(true, 2019052100, 'publication');
    }

    if ($oldversion < 2020010500) {

        // Changing the default of field teacherapproval on table publication_file to 3.
        $table = new xmldb_table('publication_file');
        $field = new xmldb_field('teacherapproval', XMLDB_TYPE_INTEGER, '2', null, null, null, '3', 'type');

        $DB->set_field('publication_file', 'teacherapproval', 3, ['teacherapproval' => null]);

        // Launch change of default for field teacherapproval.
        $dbman->change_field_default($table, $field);

        // Publication savepoint reached.
        upgrade_mod_savepoint(true, 2020010500, 'publication');
    }

    if ($oldversion < 2021052500) {

        // Define field id to be added to publication_file.
        $table = new xmldb_table('publication_file');
        $index = new xmldb_index('publication', XMLDB_INDEX_NOTUNIQUE, ['publication']);

        // Conditionally launch add index publication.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        $index = new xmldb_index('userid', XMLDB_INDEX_NOTUNIQUE, ['userid']);

        // Conditionally launch add index userid.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Publication savepoint reached.
        upgrade_mod_savepoint(true, 2021052500, 'publication');
    }

    if ($oldversion < 2021052501) {

        $table = new xmldb_table('publication_groupapproval');
        $DB->set_field('publication_groupapproval', 'timecreated', 0, ['timecreated' => null]);
        $DB->set_field('publication_groupapproval', 'timemodified', 0, ['timemodified' => null]);
        $field = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, true, null, '0', 'approval');
        $dbman->change_field_default($table, $field);
        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, true, null, '0', 'timecreated');
        $dbman->change_field_default($table, $field);
        // Publication savepoint reached.
        upgrade_mod_savepoint(true, 2021052501, 'publication');
    }

    return true;
}
