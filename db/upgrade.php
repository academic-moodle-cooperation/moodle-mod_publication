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
 * db/upgrade.php
 *
 * @package       mod_publication
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Philipp Hager (office@phager.at)
 * @author        Andreas Windbichler
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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

    return true;
}
