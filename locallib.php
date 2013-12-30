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

require_once($CFG->dirroot . '/mod/publication/renderable.php');

define('PUBLICATION_MODE_UPLOAD', 0);
define('PUBLICATION_MODE_IMPORT', 1);

class publication{
	
	private $instance;	
	private $context;
	private $course;
	private $adminconfig;
	private $coursemodule;
	
	private $maxbytes = 25000;
	
	public function __construct($coursemodulecontext, $coursemodule, $course){
		global $PAGE;
		
		$this->context = context_module::instance($coursemodule->id);
		$this->coursemodule = $coursemodule;
		$this->course = $course;
	}
	
	protected function show_intro(){
		if($this->get_instance()->alwaysshowdescription ||
				time() > $this->get_instance()->allowsubmissionfromdate){
			return true;
		}
		return false;
	}
}