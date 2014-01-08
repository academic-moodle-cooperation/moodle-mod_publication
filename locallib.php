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
		global $USER;
		
		// display availability dates
		$textsuffix = ($this->instance->mode == PUBLICATION_MODE_IMPORT) ? "_import" : "_upload";
		
		echo html_writer::start_div('availability');
		
		if($this->instance->allowsubmissionsfromdate){
			echo html_writer::start_div();
			echo html_Writer::tag('span', get_string('allowsubmissionsfromdate' . $textsuffix,'publication') . ': ') . userdate($this->instance->allowsubmissionsfromdate);
			echo html_writer::end_div();
		}
		if($this->instance->duedate){
			echo html_writer::start_div();
			echo html_Writer::tag('span', get_string('duedate' . $textsuffix,'publication') . ': ') . userdate($this->instance->duedate);
			echo html_writer::end_div();
		}
		if($this->instance->cutoffdate){
			echo html_writer::start_div();
			echo html_Writer::tag('span',  get_string('cutoffdate' . $textsuffix,'publication') . ': ') . userdate($this->instance->cutoffdate);
			echo html_writer::end_div();
		}
		
		$extensionduedate = $extensionduedate = $this->user_extensionduedate($USER->id);
		
		if($extensionduedate){
			echo html_writer::start_div();
			echo html_Writer::tag('span',  get_string('extensionto','publication') . ': ') . userdate($extensionduedate);
			echo html_writer::end_div();
		}
		echo html_writer::end_div();
	}
	
	/**
	 * If the mode is set to import then the link to the corresponding
	 * assignment will be displayed
	 */
	public function display_importlink(){
		global $DB, $OUTPUT;
		
		if($this->instance->mode == PUBLICATION_MODE_IMPORT){
			$assign = $DB->get_record('assign', array('id'=> $this->instance->importfrom));
			$assigncm = $DB->get_record('course_modules', array('course'=>$this->course->id, 'instance'=>$this->instance->importfrom));
		
			echo html_writer::start_div('assignurl');
			if($assign && $assigncm){
				$assignurl = new moodle_url('/mod/assign/view.php', array('id' => $assigncm->id));
				echo get_string('assignment','publication') . ':' . html_writer::link($assignurl, $assign->name);
				
				if(has_capability('mod/publication:addinstance', $this->context)){
					$url = new moodle_url('/mod/publication/view.php',
							array('id'=>$this->coursemodule->id, 'sesskey'=>sesskey(), 'action'=>'import'));
					$label = get_string('updatefiles','publication');
					
					echo $OUTPUT->single_button($url, $label);
				}
				
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
	
	public function user_extensionduedate($uid){
		global $DB;
		
		$extensionduedate = $DB->get_field('publication_extduedates','extensionduedate', array(
				'publication'=>$this->get_instance()->id,
				'userid'=>$uid));
		
		if(!$extensionduedate){
			return 0;
		}
		
		return $extensionduedate;
	}
	
	public function is_open(){
		global $USER;
		
		$now = time();

		$from = $this->get_instance()->allowsubmissionsfromdate;
		$due = $this->get_instance()->duedate;
		
		if ($this->get_instance()->cutoffdate) {
			$due = $this->get_instance()->cutoffdate;
		}
		
		$extensionduedate = $this->user_extensionduedate($USER->id);
		
		if($extensionduedate){
			$due = $extensionduedate;
		}
		
		if(($from == 0 || $from < $now) &&
			($due == 0 || $due > $now)){
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

	
	
	public function display_allfilesform(){
		global $CFG, $OUTPUT, $DB, $USER;
	
		$cm = $this->coursemodule;
		$context = $this->context;
		$course = $this->course;
	
		$updatepref = optional_param('updatepref', 0, PARAM_BOOL);
		if ($updatepref) {
			$perpage = optional_param('perpage', 10, PARAM_INT);
			$perpage = ($perpage <= 0) ? 10 : $perpage;
			$filter = optional_param('filter', 0, PARAM_INT);
			set_user_preference('publication_perpage', $perpage);
		}
	
	
		/* next we get perpage and quickgrade (allow quick grade) params
		 * from database
		*/
		$perpage    = get_user_preferences('publication_perpage', 10);
		$quickgrade = get_user_preferences('publication_quickgrade', 0);
		$filter = get_user_preferences('publicationfilter', 0);
			
		$page    = optional_param('page', 0, PARAM_INT);
		
		
		$formattrs = array();
		$formattrs['action'] = new moodle_url('/mod/publication/view.php');
		$formattrs['id'] = 'fastg';
		$formattrs['method'] = 'post';
		$formattrs['class'] = 'mform';
		
		$html = '';
		$html .= html_writer::start_tag('form', $formattrs);
		$html .= html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'id',      'value'=> $this->get_coursemodule()->id));
		$html .= html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'page',    'value'=> $page));
		$html .= html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'sesskey', 'value'=> sesskey()));
		echo $html;
		
		echo html_writer::start_tag('fieldset',array('id'=>'id_allfiles', 'class'=>'clearfix','aria-live'=>'polite'));
		$title = (has_capability('mod/publication:approve', $context)) ? get_string('allfiles', 'publication') : get_string('publicfiles', 'publication');
		echo html_writer::tag('legend', $title);
		echo html_writer::start_div('fcontainer clearfix');
		
		// Check to see if groups are being used in this assignment.
	
		// Find out current groups mode.
		$groupmode = groups_get_activity_groupmode($cm);
		$currentgroup = groups_get_activity_group($cm, true);
		
		$html = '';
	
		// Get all ppl that are allowed to submit assignments.
		list($esql, $params) = get_enrolled_sql($context, 'mod/publication:view', $currentgroup);
	
		$showall = false;
		
		if(has_capability('mod/publication:approve', $context) ||
			has_capability('mod/publication:grantextension', $context)){
			$showall = true;
		}

		if ($showall) {
			$sql = 'SELECT u.id FROM {user} u '.
					'LEFT JOIN ('.$esql.') eu ON eu.id=u.id '.
					'WHERE u.deleted = 0 AND eu.id=u.id ';
		} else {		
			$sql = 'SELECT DISTINCT u.id FROM {user} u '.
					'LEFT JOIN ('.$esql.') eu ON eu.id=u.id '.
					'LEFT JOIN {publication_file} files ON (u.id = files.userid) '.
					'WHERE u.deleted = 0 AND eu.id=u.id '.
					'AND files.publication = '. $this->get_instance()->id;
		}
		
		$users = $DB->get_records_sql($sql, $params);
		if (!empty($users)) {
			$users = array_keys($users);
		}
		
		// If groupmembersonly used, remove users who are not in any group.
		if ($users and !empty($CFG->enablegroupmembersonly) and $cm->groupmembersonly) {
			if ($groupingusers = groups_get_grouping_members($cm->groupingid, 'u.id', 'u.id')) {
				$users = array_intersect($users, array_keys($groupingusers));
			}
		}
	
		$tablecolumns = array('selection', 'fullname');
		$tableheaders = array('', get_string('fullnameuser'));
	
		$useridentity = explode(',', $CFG->showuseridentity);
		
		foreach($useridentity as $cur){
			$tablecolumns[] = $cur;
			$tableheaders[] = ($cur == 'phone1') ? get_string('phone') : get_string($cur);
		}
	
		$tableheaders[] = get_string('lastmodified');
		$tablecolumns[] = 'timemodified';
		
		if(has_capability('mod/publication:approve', $context)){
			// not necessary in upload mode, approving by uploading
			// cusomer wants to see it anyways
			// if($this->get_instance()->mode == PUBLICATION_MODE_IMPORT)
			{
				$tablecolumns[] = 'status';
				$tableheaders[] = get_string('status', 'publication');
			}
			
			$tablecolumns[] = 'visibility';
			$tableheaders[] = get_string('visibility', 'publication');
		}
	
	
		require_once($CFG->libdir.'/tablelib.php');
		$table = new flexible_table('mod-publication-allfiles');
	
		$table->define_columns($tablecolumns);
		$table->define_headers($tableheaders);
		$table->define_baseurl($CFG->wwwroot.'/mod/publication/view.php?id='.$cm->id.'&amp;currentgroup='.$currentgroup);
	
		$table->sortable(true, 'lastname'); // Sorted by lastname by default.
		$table->collapsible(false);
		$table->initialbars(true);
		
		$table->column_class('fullname', 'fullname');
		$table->column_class('timemodified', 'timemodified');
	
		$table->set_attribute('cellspacing', '0');
		$table->set_attribute('id', 'attempts');
		$table->set_attribute('class', 'publications');
		$table->set_attribute('width', '100%');
	
		// Start working -- this is necessary as soon as the niceties are over.
		$table->setup();
	
		// Construct the SQL.
		list($where, $params) = $table->get_sql_where();
		if ($where) {
			$where .= ' AND ';
		}

		if ($sort = $table->get_sql_sort()) {
			$sort = ' ORDER BY '.$sort;
		}

		$ufields = user_picture::fields('u');
		$useridentityfields = 'u.'.str_replace(',', ',u.', $CFG->showuseridentity);

		$totalfiles = 0;
		
		if (!empty($users)) {
			$select = 'SELECT '.$ufields.','.$useridentityfields.', username,
                                COUNT(*) filecount,
                                MAX(files.timecreated) timemodified ';
			$sql = 'FROM {user} u '.
					'LEFT JOIN {publication_file} files ON u.id = files.userid
                            AND files.publication = '.$this->get_instance()->id.' '.
	                            'WHERE '.$where.'u.id IN ('.implode(',', $users).') '.
			                    'GROUP BY '.$ufields.','.$useridentityfields.', username ';
			
			$ausers = $DB->get_records_sql($select.$sql.$sort, $params, $table->get_page_start(), $table->get_page_size());
			$table->pagesize($perpage, count($users));
	
			// Offset used to calculate index of student in that particular query, needed for the pop up to know who's next.
			$offset = $page * $perpage;
			$strupdate = get_string('update');
	
			if ($ausers !== false) {
				$endposition = $offset + $perpage;
				$currentposition = 0;
				
				$valid = $OUTPUT->pix_icon('i/valid',
						get_string('student_approved', 'publication'));
				$questionmark = $OUTPUT->pix_icon('questionmark',
						get_string('student_pending', 'publication'),
						'mod_publication');
				$cross_red = $OUTPUT->pix_icon('i/cross_red_big',
						get_string('student_rejected', 'publication'));
				
				foreach ($ausers as $auser) {
					if ($currentposition >= $offset && $currentposition < $endposition) {
						// Calculate user status.
						$selected_user = html_writer::checkbox('selectedeuser['.$auser->id .']', 'selected', true,
								null, array('class'=>'userselection'));
	
						$useridentity = explode(',', $CFG->showuseridentity);
						foreach($useridentity as $cur){
							if (!empty($auser->$cur)) {
								$$cur = html_writer::tag('div', $auser->$cur,
										array('id'=>'u'.$cur.$auser->id));
							} else {
								$$cur = html_writer::tag('div', '-', array('id'=>'u'.$cur.$auser->id));
							}
						}	

						$userlink = '<a href="' . $CFG->wwwroot . '/user/view.php?id=' . $auser->id .
						'&amp;course=' . $course->id . '">' .
						fullname($auser, has_capability('moodle/site:viewfullnames', $this->context)) . '</a>';
						
						$extension = $this->user_extensionduedate($auser->id);
						
						if($extension){
							$userlink .= '<br/>' . get_string('extensionto', 'publication') . ': ' . userdate($extension);
						}
						
						$row = array($selected_user,$userlink);
	
						$useridentity = explode(',', $CFG->showuseridentity);
						foreach($useridentity as $cur) {
							if(true /*!$this->column_is_hidden($cur)*/){
								$row[] = $$cur;
							}else{
								$row[] = "";
							}
						}
						
						$filearea = 'attachment';
						$sid = $auser->id;
						$fs = get_file_storage();
						
						$files = $fs->get_area_files($this->get_context()->id,
								'mod_publication',
								$filearea,
								$sid,
								'timemodified',
								false);
						
						$filetable = new html_table();
						$filetable->attributes = array('class' => 'filetable');
						
						$statustable = new html_table();
						$statustable->attributes = array('class' => 'filetable');
						
						$permissiontable = new html_table();
						$permissiontable->attributes = array('class' => 'filetable');

						$conditions = array();
						$conditions['publication'] = $this->get_instance()->id;
						$conditions['userid'] = $auser->id;
						foreach($files as $file){
							$conditions['fileid'] = $file->get_id();
							$filepermissions = $DB->get_record('publication_file', $conditions);
							
							$showfile = false;
							
							// always show all files to teachers
							if(has_capability('mod/publication:approve', $context)){
								$showfile = true;
							}
							
							
							if($this->get_instance()->mode == PUBLICATION_MODE_UPLOAD){
								// mode upload
								if($this->get_instance()->obtainteacherapproval){
									// need teacher approval
									if($filepermissions->teacherapproval == 1){
										// teacher has approved
										$showfile = true;
									}
								}else{									
									// no need for teacher approval
									if(is_null($filepermissions->teacherapproval) || $filepermissions->teacherapproval == 1){
										// teacher only hasnt rejected
										$showfile = true;
									}
								}
								
							}else{
								
								// mode import
								if(!$this->get_instance()->obtainstudentapproval && $filepermissions->teacherapproval == 1){
									// no need to ask student and teacher has approved	
									$showfile = true;
								}else if($this->get_instance()->obtainstudentapproval &&
										$filepermissions->teacherapproval == 1 &&
										$filepermissions->studentapproval == 1){
									// student and teacher have approved
									$showfile = true;
								}
							}

//							if(($this->get_instance()->mode == PUBLICATION_MODE_IMPORT && (!$this->get_instance()->obtainstudentapproval || $filepermissions->studentapproval))
//							|| ($this->get_instance()->mode == PUBLICATION_MODE_UPLOAD && (!$this->get_instance()->obtainteacherapproval || $filepermissions->teacherapproval))
//							|| has_capability('mod/publication:approve', $context)){
							if($showfile){			
								
								$filerow = array();
								$filerow[] = $OUTPUT->pix_icon(file_file_icon($file), get_mimetype_description($file));
									
								$url = new moodle_url('/mod/publication/view.php',array('id'=>$cm->id,'download'=>$file->get_id()));
								$filerow[] = html_writer::link($url, $file->get_filename());
								if(has_capability('mod/publication:approve', $context)){	
									$checked = $filepermissions->teacherapproval;
									
									if($this->get_instance()->mode == PUBLICATION_MODE_UPLOAD && is_null($checked)){
										// if checked is null set defaults for upload mode
										if($this->get_instance()->obtainteacherapproval){
											$checked = false;
										}else{
											$checked = true;
										}
									}
									
									$permissionrow = array();
									$permissionrow[] = html_writer::checkbox('file', 'value',$checked);
									
									$statusrow = array();
									
									if(is_null($filepermissions->studentapproval)){
										$statusrow[] = $questionmark;
									}else if($filepermissions->studentapproval){
										$statusrow[] = $valid;
									}else{
										$statusrow[] = $cross_red;
									}
									$statustable->data[] = $statusrow;
									$permissiontable->data[] = $permissionrow;
									$totalfiles++;
								}
								$filetable->data[] = $filerow;
							}
						}
						
						$lastmodified = "";
						if(count($filetable->data) > 0){
							$lastmodified = html_writer::table($filetable);
							$lastmodified .= userdate($auser->timemodified);
						}else{
							$lastmodified = "keine Dateien"; //TODO get_string
						}
						
						$row[] = $lastmodified;
						
						if(has_capability('mod/publication:approve', $context)){
							// not necessary in upload mode, approving by uploading
							// cusomer wants to see it anyways
							// if($this->get_instance()->mode == PUBLICATION_MODE_IMPORT)
							{
								if(count($statustable->data) > 0){
									$status = html_writer::table($statustable);
								}else{
									$status = '';
								}
								$row[] = $status;
							}
							if(count($permissiontable->data) > 0){								
								$permissions = html_writer::table($permissiontable);
							}else{
								$permissions = '';
							}
							$row[] = $permissions;
						}

						$table->add_data($row);
					}
					$currentposition++;
				}			
				
				if ($totalfiles > 0) {
					$html .= html_writer::start_tag('div', array('class' => 'mod-publication-download-link'));
					$html .= html_writer::link(new moodle_url('/mod/publication/view.php',
							array('id' => $this->coursemodule->id, 'action' => 'zip')), get_string('downloadall', 'publication'));
					$html .= html_writer::end_tag('div');
				}

				echo $html;
				$html = "";
				
				
				$table->print_html();  // Print the whole table.
				
				$options = array();
				$options['zipusers'] = get_string('zipusers', 'publication');
				//		$options['approveusers'] = get_string('approveusers', 'publication');
				//		$options['rejectusers'] = get_string('rejectusers', 'publication');
				if(has_capability('mod/publication:grantextension', $this->get_context())){
					$options['grantextension'] = get_string('grantextension', 'publication');
				}
				
				if ($totalfiles > 0){
					$html .= html_writer::start_div('withselection');
					$html .= html_writer::span(get_string('withselected', 'publication'));
					$html .= html_writer::select($options, 'action');
					$html .= html_writer::empty_tag('input', array('type'=>'submit', 'name'=>'submit',
							'value'=>get_string('go', 'publication')));
					$html .= html_writer::end_div();
				}
			} else {
				$html .= html_writer::tag('div', get_string('nothingtodisplay', 'publication'),
						array('class'=>'nosubmisson'));
			}
		} else {
			$html .= html_writer::tag('div', get_string('nothingtodisplay', 'publication'),
					array('class'=>'nosubmisson'));
		}
		
/*		
		// select all/none 
		$html .= html_writer::start_tag('div', array('class'=>'checkboxcontroller'));
		$html .= "<script type=\"text/javascript\">
                                        function toggle_userselection() {
                                        var checkboxes = document.getElementsByClassName('userselection');
	
                                        if(checkboxes.length > 0){
                                        checkboxes[0].checked = !checkboxes[0].checked;
	
                                        for(var i = 1; i < checkboxes.length;i++){
                                        checkboxes[i].checked = checkboxes[0].checked;
    }
    }
    }
                                        </script>";
	
		$html .= '<div style="padding-top:14px;margin-left:12px;">';
		$html .= html_writer::tag('a', get_string('select_allnone', 'extserver'),
				array('href'=>'#', 'onClick'=>'toggle_userselection()'));
		$html .= '</div>';
		$html .= html_writer::end_tag('div');
*/
	
		echo $html;
		
		echo html_writer::end_div();
		echo html_writer::end_tag('fieldset');
		echo html_writer::end_tag('form');
		
		// Mini form for setting user preference.
		$html = '';
	
		$formaction = new moodle_url('/mod/publication/view.php', array('id'=>$this->coursemodule->id));
		$mform = new MoodleQuickForm('optionspref', 'post', $formaction, '', array('class'=>'optionspref'));
	
		$mform->addElement('hidden', 'updatepref');
		$mform->setDefault('updatepref', 1);
		
		$mform->addElement('header', 'qgprefs', get_string('optionalsettings', 'publication'));
	
		$mform->addElement('text', 'perpage', get_string('entiresperpage', 'publication'), array('size'=>1));
		$mform->setDefault('perpage', $perpage);
	

		$mform->addElement('submit', 'savepreferences', get_string('savepreferences'));
	
		$mform->display();
	
		return $html;
	}
	
	
	public function download_file($fileid){
		global $DB, $USER;

		$conditions = array();
		$conditions['publication'] = $this->get_instance()->id;
		$conditions['fileid'] = $fileid;
		$record = $DB->get_record('publication_file', $conditions);
		
		$allowed = false;
		
		if($record->userid == $USER->id){
			// owner is always allowed to see this files
			$allowed = true;
		
		}else if(has_capability('mod/publication:approve', $this->get_context())){
			// teachers has to see the files to know if they can allow them
			$allowed = true;
		
		}else if(($this->get_instance()->mode == PUBLICATION_MODE_IMPORT && (!$this->get_instance()->obtainstudentapproval || $filepermissions->studentapproval))
		|| ($this->get_instance()->mode == PUBLICATION_MODE_UPLOAD && (!$this->get_instance()->obtainteacherapproval || $filepermissions->teacherapproval))){
			// verybody is allowed
			$allowed = true;
		}
		
		if($allowed){
			$sid = $record->userid;
			$filearea = 'attachment';
		
			$fs = get_file_storage();
			$file = $fs->get_file_by_id($fileid);
			send_file($file, $file->get_filename(),'default' ,0,false, true, $file->get_mimetype(),false);
			die();
		}else{
			print_error('You are not allowed to see this file'); //TODO ge_string
		}
	}
	
	/**
	 * creates a zip of all uploaded files and sends a zip to the browser
	 */
	public function download_zip($users = array()) {
		global $CFG, $DB;
		require_once($CFG->libdir.'/filelib.php');

		$conditions = array();
		$conditions['publication'] = $this->get_instance()->id;
		
		$customusers = "";
		if(count($users) > 0){
			$customusers = " and userid IN (" . implode($users, ',') . ")";
		}
		
		$uploaders = $DB->get_records_sql("SELECT DISTINCT userid FROM {publication_file} WHERE publication=:pubid" . $customusers,
				array("pubid"=>$this->get_instance()->id));

		$filesforzipping = array();
		$fs = get_file_storage();
		$filearea = 'attachment';
	
		$groupmode = groups_get_activity_groupmode($this->get_coursemodule());
		$groupid = 0;   // All users.
		$groupname = '';
		if ($groupmode) {
			$groupid = groups_get_activity_group($this->get_coursemodule(), true);
			$groupname = groups_get_group_name($groupid).'-';
		}
		$filename = str_replace(' ', '_', clean_filename($this->course->shortname.'-'.
				$this->get_instance()->name.'-'.$groupname.$this->get_instance()->id.'.zip')); // Name of new zip file.
		
		foreach ($uploaders as $uploader) {			
			$a_userid = $uploader->userid; // Get userid.
			
			if ((groups_is_member($groupid, $a_userid)or !$groupmode or !$groupid)) {
				$conditions['userid'] = $uploader->userid;
				$records = $DB->get_records('publication_file', $conditions);
				$filespermissions = array();
				foreach($records as $record){
					$filespermissions[$record->fileid] = $record;
				}
				
				$a_assignid = $a_userid; // Get name of this assignment for use in the file names.
				// Get user firstname/lastname.
				$a_user = $DB->get_record('user', array('id'=>$a_userid), 'id,username,firstname,lastname');
	
				$files = $fs->get_area_files($this->get_context()->id, 'mod_publication', $filearea, $a_userid,
						'timemodified', false);
				foreach ($files as $file) {					
					$filepermissions = $filespermissions[$file->get_id()];
					
					$allowed = false;
					if(has_capability('mod/publication:approve', $this->get_context())){
						// teachers has to see the files to know if they can allow them
						$allowed = true;
					
					}else if(($this->get_instance()->mode == PUBLICATION_MODE_IMPORT && (!$this->get_instance()->obtainstudentapproval || $filepermissions->studentapproval))
					|| ($this->get_instance()->mode == PUBLICATION_MODE_UPLOAD && (!$this->get_instance()->obtainteacherapproval || $filepermissions->teacherapproval))){
						// verybody is allowed
						$allowed = true;
					}
					
					
					if($allowed){
						// Get files new name.
						$fileext = strstr($file->get_filename(), '.');
						$fileoriginal = str_replace($fileext, '', $file->get_filename());
						$fileforzipname =  clean_filename(fullname($a_user) . '_' . $fileoriginal.'_'.$a_userid.$fileext);
						// Save file name to array for zipping.
						$filesforzipping[$fileforzipname] = $file;
					}
				}
			}
		} // End of foreach.
		
		if (empty($filesforzipping)) {
			print_error('nofilestozip', 'publication');
		}
	
		if ($zipfile = $this->pack_files($filesforzipping)) {
			send_temp_file($zipfile, $filename); // Send file and delete after sending.
		}
	}
	
	private function pack_files($filesforzipping) {
		global $CFG;
		// Create path for new zip file.
		$tempzip = tempnam($CFG->dataroot.'/temp/', 'publication_');
		// Zip files.
		$zipper = new zip_packer();
		if ($zipper->archive_to_pathname($filesforzipping, $tempzip)) {
			return $tempzip;
		}
		return false;
	}
	
	/**
	 * Updates files from connected assignment
	 */
	public function importfiles(){
		global $DB;
		
		if($this->instance->mode == PUBLICATION_MODE_IMPORT){
			$assign = $DB->get_record('assign', array('id'=> $this->instance->importfrom));
			$assigncm = $DB->get_record('course_modules', array('course'=>$this->course->id, 'instance'=>$this->instance->importfrom));
					
			$assigncontext = context_module::instance($assigncm->id);
			
			if($assign && $assigncm){		
				if(has_capability('mod/publication:addinstance', $this->context)){
					$records = $DB->get_records('assignsubmission_file',array('assignment'=>$this->get_instance()->importfrom));
									
					foreach($records as $record){
						$userfilesids = array();
						
						// no need to do any of that if user has no files submitted
						if($record->numfiles > 0){
							$fs = get_file_storage();
							$files = $fs->get_area_files($assigncontext->id,
									"assignsubmission_file",
									"submission_files",
									$record->submission,
									"id",
									false);
							$submission = $DB->get_record('assign_submission', array('id'=>$record->submission));
							
							//copy files
							foreach($files as $file){	
								$newfilerecord = new stdClass();
								$newfilerecord->contextid = $this->get_context()->id;
								$newfilerecord->component = 'mod_publication';
								$newfilerecord->filearea = 'attachment';
								$newfilerecord->itemid = $submission->userid;
								
								try{
									$newfile = $fs->create_file_from_storedfile($newfilerecord, $file);
									
									$dataobject = new stdClass();
									$dataobject->publication = $this->get_instance()->id;
									$dataobject->userid = $submission->userid;
									$dataobject->timecreated = time();
									$dataobject->fileid = $newfile->get_id();
									$dataobject->filename = $newfile->get_filename();
									$dataobject->type = PUBLICATION_MODE_IMPORT;
									$newid = $DB->insert_record('publication_file', $dataobject);
									
									array_push($userfilesids, $newid);
									
								}catch (Exception $e){
									// file does allready exist
									$conditions = array();
									$conditions['publication'] = $this->get_instance()->id;
									$conditions['userid'] = $submission->userid;
									$conditions['contenthash'] = $file->get_contenthash();
									
									$oldrecord = $DB->get_record('publication_file', $conditions, 'id');
									array_push($userfilesids, $oldrecord->id);
								}
							}
						}
						
						// remove files and records wich dont exist any more
						$recstodelete = $DB->get_records_sql('SELECT * FROM {publication_file}
									WHERE publication=:publication AND userid=:uid AND NOT id IN(' . implode(",", $userfilesids) . ')',
								array(	'publication'=>$this->get_instance()->id,
										'uid'=>$submission->userid)
						);
							
						foreach($recstodelete as $rectodelete){
							$filetodelete = $fs->get_file_by_id($rectodelete->fileid);
							$filetodelete->delete();
						
							$DB->delete_records('publication_file',array('id'=>$rectodelete->id));
						}
					}

					return true;
				}
			}
		}
		
		return false;
	}
}