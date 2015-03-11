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
// If not, see <http://www.gnu.org/licenses/>.

/**
 * mod_form.php
 *
 * @package       mod_publication
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Andreas Windbichler
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/publication/locallib.php');

/**
 * Form for creating and editing rssplayer instances
 * 
 * @author Andreas Windbichler
 */
class mod_publication_mod_form extends moodleform_mod{
    
    /**
     * Define this form - called by the parent constructor
     */
    public function definition() {
        global $DB, $CFG, $COURSE, $PAGE, $OUTPUT;
        
        $jsmodule = array(
        		'name' => 'mod_publication',
        		'fullpath' => '/mod/publication/publication.js',
        		'requires' => array('node-base', 'node-event-simulate'),
        );
        
        $PAGE->requires->js_init_call('M.mod_publication.init_mod_form', array(), false, $jsmodule);
        
        $config = get_config('publication');

        $mform = $this->_form;
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Name.
        $mform->addElement('text', 'name', get_string('name', 'publication'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');

        $requireintro = (isset($config->requiremodintro) && $config->requiremodintro == 1) ? true : false;
        
        $this->add_intro_editor($requireintro);
        
        // Publication specific elements
        $mform->addElement('header','publication', get_string('modulename','publication'));
        $mform->setExpanded('publication');
        
        if(isset($this->current->id)){
        	$filecount = $DB->count_records('publication_file', array('publication'=>$this->current->id));
        }
        
        $disabled = array();
        if($filecount > 0){
        	$disabled['disabled'] = 'disabled';
        }
        
        $modearray = array();
		$modearray[] =& $mform->createElement('radio', 'mode', '', get_string('modeupload', 'publication'), PUBLICATION_MODE_UPLOAD, $disabled);
		$modearray[] =& $mform->createElement('radio', 'mode', '', get_string('modeimport', 'publication'), PUBLICATION_MODE_IMPORT, $disabled);
		$mform->addGroup($modearray, 'modegrp', get_string('mode', 'publication'), array(' '), false);
		$mform->addHelpButton('modegrp','mode','publication');
		if(count($filecount) == 0){
			$mform->addRule('modegrp', null, 'required', null, 'client');
		}  
		
		// Publication mode import specific elements
		
		$choices = array();
		$choices[-1] = get_string('choose', 'publication');
		$assigninstances = $DB->get_records('assign',array('course'=>$COURSE->id));
		foreach($assigninstances as $assigninstance){
			$choices[$assigninstance->id] = $assigninstance->name;
		}
		
		$mform->addElement('select', 'importfrom', get_string('assignment', 'publication'), $choices, $disabled);
		if(count($disabled) == 0){
			$mform->disabledIf('importfrom', 'mode', 'neq', PUBLICATION_MODE_IMPORT);
		}
		
		$attributes = array();
		if(isset($this->current->id) && isset($this->current->obtainstudentapproval)){
			if($this->current->obtainstudentapproval){
				$message = get_string('warning_changefromobtainstudentapproval', 'publication');
				$showwhen = "0";
			}else{
				$message =  get_string('warning_changetoobtainstudentapproval', 'publication');
				$showwhen = "1";
			}
			 
			$message = trim(preg_replace('/\s+/', ' ', $message));
			$message = str_replace('\'','\\\'', $message);
			$attributes['onChange'] = "if(this.value==".$showwhen."){alert('" . $message .  "')}";
		}
		
		$mform->addElement('selectyesno', 'obtainstudentapproval', get_string('obtainstudentapproval','publication'), $attributes);
		$mform->setDefault('obtainstudentapproval', get_config('publication','obtainstudentapproval'));
		$mform->addHelpButton('obtainstudentapproval','obtainstudentapproval','publication');
		$mform->disabledIf('obtainstudentapproval', 'mode', 'neq', PUBLICATION_MODE_IMPORT);
		
		
		// Publication mode upload specific elements
		
		$maxfiles = array();
		for ($i=1; $i<=100 || $i <= get_config('publication', 'maxfiles'); $i++) {
			$maxfiles[$i] = $i;
		}
		
		$mform->addElement('select', 'maxfiles', get_string('maxfiles', 'publication'), $maxfiles);
		$mform->setDefault('maxfiles', get_config('publication', 'maxfiles'));
		$mform->disabledIf('maxfiles', 'mode', 'neq', PUBLICATION_MODE_UPLOAD);
		
		$choices = get_max_upload_sizes($CFG->maxbytes, $COURSE->maxbytes);
		$choices[0] = get_string('courseuploadlimit','publication') . ' ('.display_size($COURSE->maxbytes).')';
		$mform->addElement('select', 'maxbytes', get_string('maxbytes', 'publication'), $choices);
		$mform->setDefault('maxbytes', get_config('publication', 'maxbytes'));
		$mform->disabledIf('maxbytes', 'mode', 'neq', PUBLICATION_MODE_UPLOAD);
		
        $mform->addElement('text', 'allowedfiletypes', get_string('allowedfiletypes', 'publication'), array('size' => '45'));
        $mform->setType('allowedfiletypes',PARAM_RAW);
        $mform->addHelpButton('allowedfiletypes', 'allowedfiletypes', 'publication');
        $mform->addRule('allowedfiletypes', get_string('allowedfiletypes_err', 'publication'), 'regex',
                '/^([A-Za-z0-9]+([ ]*[,][ ]*[A-Za-z0-9]+)*)$/', 'client', false, false);
        $mform->disabledIf('allowedfiletypes', 'mode', 'neq', PUBLICATION_MODE_UPLOAD);
		
        $attributes = array();
        if(isset($this->current->id) && isset($this->current->obtainteacherapproval)){
        	if(!$this->current->obtainteacherapproval){
        		$message = get_string('warning_changefromobtainteacherapproval', 'publication');
        		$showwhen = "1";
        	}else{
        		$message =  get_string('warning_changetoobtainteacherapproval', 'publication');
        		$showwhen = "0";
        	}
        	
        	$message = trim(preg_replace('/\s+/', ' ', $message));
        	$attributes['onChange'] = "if(this.value==".$showwhen."){alert('" . $message .  "')}";
        }
        
        $mform->addElement('selectyesno', 'obtainteacherapproval', get_string('obtainteacherapproval','publication'), $attributes);
        $mform->setDefault('obtainteacherapproval', get_config('publication','obtainteacherapproval'));
        $mform->addHelpButton('obtainteacherapproval','obtainteacherapproval','publication');
        $mform->disabledIf('obtainteacherapproval', 'mode', 'neq', PUBLICATION_MODE_UPLOAD);
        
        // availability
        $mform->addElement('header', 'availability', get_string('availability', 'publication'));
        $mform->setExpanded('availability', true);
        
        $name = get_string('allowsubmissionsfromdate', 'publication');
        $options = array('optional'=>true);
        $mform->addElement('date_time_selector', 'allowsubmissionsfromdate', $name, $options);
        $mform->addHelpButton('allowsubmissionsfromdate', 'allowsubmissionsfromdateh', 'publication');
        $mform->setDefault('allowsubmissionsfromdate', time());
        
        $name = get_string('duedate', 'publication');
        $mform->addElement('date_time_selector', 'duedate', $name, array('optional'=>true));
        //$mform->addHelpButton('duedate', 'duedate', 'publication');
        $mform->setDefault('duedate', time()+7*24*3600);
        /*
         $name = get_string('cutoffdate', 'publication');
        $mform->addElement('date_time_selector', 'cutoffdate', $name, array('optional'=>true));
        $mform->addHelpButton('cutoffdate', 'cutoffdate', 'publication');
        */
        $mform->addElement('hidden', 'cutoffdate', false);
        $mform->setType('cutoffdate', PARAM_BOOL);
        
        /*
        $mform->addElement('checkbox', 'alwaysshowdescription', get_string('alwaysshowdescription', 'publication'));
        $mform->addHelpButton('alwaysshowdescription', 'alwaysshowdescription', 'publication');
        $mform->setDefault('alwaysshowdescription', 1);
        $mform->disabledIf('alwaysshowdescription', 'allowsubmissionsfromdate[enabled]', 'notchecked');
        */
        $mform->addElement('hidden', 'alwaysshowdescription', true);
        $mform->setType('alwaysshowdescription', PARAM_BOOL);
        
        // Standard coursemodule elements
        $this->standard_coursemodule_elements();
        
        // Buttons.
        $this->add_action_buttons();
    }

    public function data_preprocessing(&$default_values) {
        // Prepares the data to show up in the edit form.

    }

    /**
     * Perform minimal validation on the settings form
     * @param array $data
     * @param array $files
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if ($data['allowsubmissionsfromdate'] && $data['duedate']) {
            if ($data['allowsubmissionsfromdate'] > $data['duedate']) {
                $errors['duedate'] = get_string('duedatevalidation', 'publication');
            }
        }
        if ($data['duedate'] && $data['cutoffdate']) {
            if ($data['duedate'] > $data['cutoffdate']) {
                $errors['cutoffdate'] = get_string('cutoffdatevalidation', 'publication');
            }
        }
        if ($data['allowsubmissionsfromdate'] && $data['cutoffdate']) {
            if ($data['allowsubmissionsfromdate'] > $data['cutoffdate']) {
                $errors['cutoffdate'] = get_string('cutoffdatefromdatevalidation', 'publication');
            }
        }
        
        if ($data['mode'] == PUBLICATION_MODE_IMPORT){
        	if ($data['importfrom'] == "0"){
        		$errors['importfrom'] = get_string('importfrom_err', 'publication');
        	}
        }

        return $errors;
    }
}