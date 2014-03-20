<?php
// This plugin is for Moodle - http://moodle.org/
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
 *
 * @package mod_publication
 * @author Andreas Windbichler
 * @copyright TSC
 */

defined('MOODLE_INTERNAL') || die();

class restore_publication_activity_structure_step extends restore_activity_structure_step {

    /**
     * Define the structure of the restore workflow.
     *
     * @return restore_path_element $structure
     */
    protected function define_structure() {

        $paths = array();
        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated.
        $paths[] = new restore_path_element('publication', '/activity/publication');
        if ($userinfo) {
            $files = new restore_path_element('publication_file',
                                                   '/activity/publication/files/file');
            $paths[] = $files;
            
            $extduedates = new restore_path_element('publication_extduedates',
            		'/activity/publication/extduedates/extduedate');
            
            $paths[] = $extduedates;
  //          $this->add_subplugin_structure('assignsubmission', $submission);

//            $paths[] = $grade;
//            $this->add_subplugin_structure('assignfeedback', $grade);
        }

        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process an assign restore.
     *
     * @param object $data The data in object form
     * @return void
     */
    protected function process_publication($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

//        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $data->allowsubmissionsfromdate = $this->apply_date_offset($data->allowsubmissionsfromdate);
        $data->duedate = $this->apply_date_offset($data->duedate);

        if (!isset($data->cutoffdate)) {
            $data->cutoffdate = 0;
        }

        if (!empty($data->preventlatesubmissions)) {
            $data->cutoffdate = $data->duedate;
        } else {
            $data->cutoffdate = $this->apply_date_offset($data->cutoffdate);
        }
        
        // delete importfrom after restore
        $data->importfrom = -1;


        $newitemid = $DB->insert_record('publication', $data);

        $this->apply_activity_instance($newitemid);
    }

    /**
     * Process a submission restore
     * @param object $data The data in object form
     * @return void
     */
    protected function process_publication_file($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->publication = $this->get_new_parentid('publication');

        $data->timecreated = $this->apply_date_offset($data->timecreated);
        if ($data->userid > 0) {
            $data->userid = $this->get_mappingid('user', $data->userid);
        }


        $newitemid = $DB->insert_record('publication_file', $data);

        // Note - the old contextid is required in order to be able to restore files stored in
        // sub plugin file areas attached to the submissionid.
        //$this->set_mapping('publication', $oldid, $newitemid, false, null, $this->task->get_old_contextid());
    }

    /**
     * Process a user_flags restore
     * @param object $data The data in object form
     * @return void
     */
    protected function process_publication_extduedates($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->publication = $this->get_new_parentid('publication');

        $data->userid = $this->get_mappingid('user', $data->userid);
        if (!empty($data->extensionduedate)) {
            $data->extensionduedate = $this->apply_date_offset($data->extensionduedate);
        } else {
            $data->extensionduedate = 0;
        }
        // Flags mailed and locked need no translation on restore.

        $newitemid = $DB->insert_record('publication_extduedates', $data);
    }

    /**
     * Once the database tables have been fully restored, restore the files
     * @return void
     */
    protected function after_execute() {
    	$this->add_related_files('mod_publication', 'attachment', null);    	
    }
    
    protected function after_restore(){
    	global $DB;
    	
    	// get set new fileids after restoring   	
    	
    	$pubid = $this->get_new_parentid('publication');   	
    	
    	$coursemodule = get_coursemodule_from_instance('publication', $pubid);

    	$context = context_module::instance($coursemodule->id);
		
		$contextid = $context->id;
    	
		$fs = get_file_storage();
    	$files = $fs->get_area_files($contextid, 'mod_publication', 'attachment');

    	foreach($files as $file){    		
    		$DB->set_field('publication_file', 'fileid', $file->get_id(),
    				array('publication'=>$pubid,
    						'userid'=>$file->get_userid(),
    						'filename'=>$file->get_filename()
    		));    		
    	}
    }
}
