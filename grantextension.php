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

require_once('../../config.php');
require_once('/locallib.php');
require_once('/mod_publication_grantextension_form.php');

$id = optional_param('id', 0, PARAM_INT); /// Course Module ID
$userids = required_param_array('userids', PARAM_INT); // User id

$url = new moodle_url('/mod/publication/grantextension.php', array('id' => $id));
if(!$cm = get_coursemodule_from_id('publication', $id, 0, false, MUST_EXIST)){
	print_error('invalidcoursemodule');
}

if(!$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST)){
	print_error('coursemisconf');
}

require_login($course, false, $cm);

$context = context_module::instance($cm->id);

require_capability('mod/publication:grantextension', $context);

$publication = new publication($context, $cm, $course);


$url = new moodle_url('/mod/publication/grantextension.php',array('cmid'=>$cm->id));
if(!empty($id)){
	$url->param('id',$id);
}

$PAGE->set_url($url);

// Create a new form object
$mform = new mod_publication_grantextension_form(null,
		array('publication'=>$publication, 'userids'=>$userids));


if ($mform->is_cancelled()) {
    redirect(new moodle_url('/mod/publication/view.php', array('id'=>$cm->id)));
    
} else if ($data = $mform->get_data()) {	
	// Store updated set of files
	$dataobject = array();
	$dataobject['publication'] = $publication->get_instance()->id;
	
	foreach($data->userids as $uid){
		$dataobject['userid'] = $uid;
		
		$DB->delete_records('publication_extduedates', $dataobject);
		
		if($data->extensionduedate > 0){
			// create new record
			$dataobject['extensionduedate'] = $data->extensionduedate;
			$DB->insert_record('publication_extduedates', $dataobject);
		}
	}
	
	redirect(new moodle_url('/mod/publication/view.php', array('id'=>$cm->id)));
}

// Load existing files into draft area 

echo $OUTPUT->header();

echo $OUTPUT->heading(format_string($publication->get_instance()->name));

$publication->display_intro();

$mform->display();

echo $OUTPUT->footer();
