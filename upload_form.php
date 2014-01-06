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

require_once($CFG->libdir.'/formslib.php'); // Putting this is as a safety as i got a class not found error.

class mod_publication_upload_form extends moodleform {

    public function definition() {
        $mform = $this->_form;
        
        $currententry		= $this->_customdata['current'];
        $publication		= $this->_customdata['publication'];
        $cm                = $this->_customdata['cm'];
        $definitionoptions = $this->_customdata['definitionoptions'];
        $attachmentoptions = $this->_customdata['attachmentoptions'];
        
        $context  = context_module::instance($cm->id);
        // Prepare format_string/text options
        $fmtoptions = array(
        		'context' => $context);
        
        //-----------------------------------
              
        if($publication->get_instance()->obtainteacherapproval){
        	$text = get_string('published_aftercheck','publication');
        }else{
        	$text = get_string('published_emediately','publication');
        }        
        
        $mform->addElement('header','myfiles',get_string('myfiles','publication'));
        
        $mform->addElement('static','guideline',get_string('guideline','publication'),$text);
        
        $mform->addElement('filemanager', 'attachment_filemanager', '', null, $attachmentoptions);

        // Hidden params.
        
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);

        // Buttons.
        $this->add_action_buttons(true, get_string('save_changes', 'publication'));
        
        $this->set_data($currententry);
    }
}
