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
 * backup/moodle2/backup_publication_stepslieb.php
 *
 * @package       mod_publication
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Philipp Hager (office@phager.at)
 * @author        Andreas Windbichler
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Class used to design mod_publications data structure to back up
 *
 * @package       mod_publication
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Philipp Hager (office@phager.at)
 * @author        Andreas Windbichler
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_publication_activity_structure_step extends backup_activity_structure_step {

    /**
     * Define the structure for the publication activity
     * @return void
     */
    protected function define_structure() {

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated.
        $publication = new backup_nested_element('publication', array('id'), array('name',
                                                                                   'intro',
                                                                                   'introformat',
                                                                                   'alwaysshowdescription',
                                                                                   'duedate',
                                                                                   'allowsubmissionsfromdate',
                                                                                   'timemodified',
                                                                                   'cutoffdate',
                                                                                   'mode',
                                                                                   'importfrom',
                                                                                   'obtainstudentapproval',
                                                                                   'maxfiles',
                                                                                   'maxbytes',
                                                                                   'allowedfiletypes',
                                                                                   'obtainteacherapproval'));

        $extduedates = new backup_nested_element('extduedates');

        $extduedate = new backup_nested_element('extduedate', array('id'), array('userid',
                                                                                 'publication',
                                                                                 'extensionduedate'));

        $files = new backup_nested_element('files');

        $file = new backup_nested_element('file', array('id'), array('userid',
                                                                     'timecreated',
                                                                     'fileid',
                                                                     'filename',
                                                                     'contenthash',
                                                                     'type',
                                                                     'teacherapproval',
                                                                     'studentapproval'));

        // Define sources.
        $publication->set_source_table('publication', array('id' => backup::VAR_ACTIVITYID));

        if ($userinfo) {
            // Build the tree.
            $publication->add_child($extduedates);
            $extduedates->add_child($extduedate);
            $publication->add_child($files);
            $files->add_child($file);

            $extduedate->set_source_table('publication_extduedates', array('publication' => backup::VAR_PARENTID));

            $file->set_source_table('publication_file', array('publication' => backup::VAR_PARENTID));

            $file->annotate_files('mod_publication', 'attachment', null);

            // Define id annotations.
            $extduedate->annotate_ids('user', 'userid');
            $file->annotate_ids('user', 'userid');

            // Define file annotations.
            // This file area hasn't itemid.
            $publication->annotate_files('mod_publication', 'attachment', null);
        }
        // Return the root element (publication), wrapped into standard activity structure.

        return $this->prepare_activity_structure($publication);
    }
}
