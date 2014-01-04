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

define('PUBLICATION_MODE_UPLOAD', 0);
define('PUBLICATION_MODE_IMPORT', 1);

class publication{
	
	private $instance;	
	private $context;
	private $course;
	private $coursemodule;
	
	public function __construct($coursemodulecontext, $coursemodule, $course){
		global $PAGE, $DB;
		
		$this->context = context_module::instance($coursemodule->id);
		$this->coursemodule = $coursemodule;
		$this->course = $course;
		$this->instance = $DB->get_record("publication", array("id"=>$coursemodule->instance));
		
		$this->instance->obtainteacherapproval = !$this->instance->obtainteacherapproval;
	}
	
	public function show_intro(){
		if($this->get_instance()->alwaysshowdescription ||
				time() > $this->get_instance()->allowsubmissionfromdate){
			return true;
		}
		return false;
	}
	
	public function display_intro(){
		global $OUTPUT;
		
		if($this->show_intro()){
			if ($this->instance->intro) {
				echo $OUTPUT->box_start('generalbox boxaligncenter', 'intro');
				echo $this->instance->intro;
				echo $OUTPUT->box_end();
			}
		}else{
			if($this->alwaysshowdescription){
				$message = get_string('allowsubmissionsfromdatesummary','publication',userdate($this->instance->allowsubmissionsfromdate));
			}else{
				$message = get_string('allowsubmissionsanddescriptionfromdatesummary','publication',userdate($this->instance->allowsubmissionsfromdate));
			}
			echo html_writer::div($message,'',array('id'=>'intro'));
		}
	}
	
	public function display_availability(){
		// display availability dates
		$textsuffix = ($this->instance->mode == PUBLICATION_MODE_IMPORT) ? "_import" : "_upload";
		
		if($this->instance->allowsubmissionsfromdate){
			echo get_string('allowsubmissionsfromdate' . $textsuffix,'publication') . ': ' . userdate($this->instance->allowsubmissionsfromdate) . "<br/>";
		}
		if($this->instance->cutoffdate){
			echo get_string('cutoffdate' . $textsuffix,'publication') . ': ' . userdate($this->instance->cutoffdate) . "<br/>";
		}
		if($this->instance->duedate){
			echo get_string('duedate' . $textsuffix,'publication') . ': ' . userdate($this->instance->duedate) . "<br/>";
		}
	}
	
	/**
	 * If the mode is set to import then the link to the corresponding
	 * assignment will be displayed
	 */
	public function display_importlink(){
		global $DB;
		
		if($this->instance->mode == PUBLICATION_MODE_IMPORT){
			$assign = $DB->get_record('assign', array('id'=> $this->instance->importfrom));
			$assigncm = $DB->get_record('course_modules', array('course'=>$this->course->id, 'instance'=>$this->instance->importfrom));
		
			echo html_writer::start_div('assignurl');
			if($assign && $assigncm){
				$assignurl = new moodle_url('/mod/assign/view.php', array('id' => $assigncm->id));
				echo get_string('assignment','publication') . ':' . html_writer::link($assignurl, $assign->name);
			}else{
				echo get_string('assignment_notfound', 'publication');
			}
			echo html_writer::end_div();
		}
		
	}
	
	/**
	 * Display Link to upload form if submission date is open
	 * and the user has the capability to upload files
	 */
	public function display_uploadlink(){
		global $OUTPUT;
		
		if($this->instance->mode == PUBLICATION_MODE_UPLOAD){
			if(has_capability('mod/publication:upload', $this->context)){
				if($this->is_open()){
					$url = new moodle_url('/mod/publication/upload.php',
							array('id'=>$this->instance->id,'cmid'=>$this->coursemodule->id));
					$label = get_string('edit_uploads','publication');
					$editbutton = $OUTPUT->single_button($url, $label);
						
					return $editbutton;
				}else{
					return get_string('edit_timeover','publication');
				}
			}else{
				return get_string('edit_notcapable', 'publication');
			}
		}		
	}
	
	public function is_open(){
		$now = time();

		if(
			($this->get_instance()->allowsubmissionsfromdate == 0 || $this->get_instance()->allowsubmissionsfromdate < $now) &&
			($this->get_instance()->duedate == 0 || $this->get_instance()->duedate > $now)
		){
			return true;
		}
		
		return false;
	}

	public function get_instance(){
		return $this->instance;
	}
	
	public function get_context(){
		return $this->context;
	}
	
	public function get_coursemodule(){
		return $this->coursemodule;
	}
}