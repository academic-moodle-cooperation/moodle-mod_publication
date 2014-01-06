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
require_once($CFG->dirroot . '/mod/publication/locallib.php');
require_once($CFG->dirroot . '/mod/publication/mod_publication_files_form.php');
require_once($CFG->dirroot . '/mod/publication/mod_publication_allfiles_form.php');

$id = required_param('id', PARAM_INT); // Course Module ID

$url = new moodle_url('/mod/publication/view.php', array('id' => $id));
$cm = get_coursemodule_from_id('publication', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

require_login($course, true, $cm);
$PAGE->set_url($url);

$context = context_module::instance($cm->id);

require_capability('mod/publication:view', $context);

$publication = new publication($context, $cm, $course);

add_to_log($course->id, "publication", "view", "view.php?id={$cm->id}", $id, $cm->id);


$pagetitle = strip_tags($course->shortname.': '.format_string($publication->get_instance()->name));
$action = optional_param('action', 'view', PARAM_ALPHA);
$download = optional_param('download',0, PARAM_INT);
if($download > 0){
	$publication->download_file($download);
}

if($action == "zip"){
	$publication->download_zip();
}else if($action == "zipusers"){
	$users = optional_param_array('selectedeuser', array(), PARAM_INT);
	$users = array_keys($users);
	$publication->download_zip($users);
}else if($action == "import"){
	require_sesskey();
	
	if(!isset($_POST['confirm'])){
		$message = get_string('updatefileswarning', 'publication');
		
		echo $OUTPUT->header();
		echo $OUTPUT->heading(format_string($publication->get_instance()->name),1);
		echo $OUTPUT->confirm($message, 'view.php?id='.$id.'&action=import&confirm=1', 'view.php?id='.$id);
		echo $OUTPUT->footer();
		exit;
	}
}

$submissionid = $USER->id;


$filesform = new mod_publication_files_form(null, array('publication'=>$publication,'sid'=>$submissionid, 'filearea'=>'attachment'));

if($data = $filesform->get_data() && $publication->is_open()){
	$datasubmitted = $filesform->get_submitted_data();
	
	if(isset($datasubmitted->gotoupload)){
		redirect(new moodle_url('/mod/publication/upload.php',
		array('id'=>$publication->get_instance()->id,'cmid'=>$cm->id)));
	}
	
	$studentapproval = optional_param_array('studentapproval', array(), PARAM_INT);
	
	$conditions = array();
	$conditions['publication'] = $publication->get_instance()->id;
	$conditions['userid'] = $USER->id;
	
	// update records
	foreach($studentapproval as $idx => $approval){
		$conditions['fileid'] = $idx;
		
		$approval = ($approval >= 1) ? $approval -1 : null;
				
		$DB->set_field('publication_file', 'studentapproval', $approval,$conditions);
	}
	
}

$filesform = new mod_publication_files_form(null, array('publication'=>$publication,'sid'=>$submissionid, 'filearea'=>'attachment'));

// Print the page header.
$PAGE->set_title($pagetitle);
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();

// Print the main part of the page.
echo $OUTPUT->heading(format_string($publication->get_instance()->name),1);

echo $publication->display_intro();
echo $publication->display_availability();
echo $publication->display_importlink();

$filesform->display();

$publication->display_allfilesform();

echo $OUTPUT->footer();