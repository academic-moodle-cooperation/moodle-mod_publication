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
 * locallib.php
 *
 * @package       mod_publication
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Andreas Windbichler
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
		global $USER, $OUTPUT;

		// display availability dates
		$textsuffix = ($this->instance->mode == PUBLICATION_MODE_IMPORT) ? "_import" : "_upload";
		
		echo $OUTPUT->box_start('generalbox boxaligncenter', 'dates');
		echo '<table>';
		if ($this->instance->allowsubmissionsfromdate) {
			echo '<tr><td class="c0">'.get_string('allowsubmissionsfromdate' . $textsuffix,'publication').':</td>';
			echo '    <td class="c1">'.userdate($this->instance->allowsubmissionsfromdate).'</td></tr>';
		}
		if ($this->instance->duedate) {
			echo '<tr><td class="c0">'.get_string('duedate' . $textsuffix,'publication').':</td>';
			echo '    <td class="c1">'.userdate($this->instance->duedate).'</td></tr>';
		}
		
		if ($this->instance->cutoffdate) {
			echo '<tr><td class="c0">'.get_string('cutoffdate' . $textsuffix,'publication').':</td>';
			echo '    <td class="c1">'.userdate($this->instance->cutoffdate).'</td></tr>';
		}
		
		$extensionduedate = $extensionduedate = $this->user_extensionduedate($USER->id);
		
		if($extensionduedate){			
			echo '<tr><td class="c0">'.get_string('extensionto','publication').':</td>';
			echo '    <td class="c1">'.userdate($extensionduedate).'</td></tr>';
		}
		
		echo '</table>';

		echo $OUTPUT->box_end();
	}

	/**
	 * If the mode is set to import then the link to the corresponding
	 * assignment will be displayed
	 */
	public function display_importlink(){
		global $DB, $OUTPUT;
		
		if($this->instance->mode == PUBLICATION_MODE_IMPORT){
			echo html_writer::start_div('assignurl');
			
			if($this->get_instance()->importfrom == -1){
				echo get_string('assignment_notset', 'publication');
			}else{
				$assign = $DB->get_record('assign', array('id'=> $this->instance->importfrom));
				
				$assign_module_id = $DB->get_field('modules', 'id', array('name'=>'assign'));
				
				$assigncm = $DB->get_record('course_modules',
						array('course'=>$assign->course, 'module'=>$assign_module_id, 'instance'=>$assign->id));
				
				if($assign && $assigncm){
					$assignurl = new moodle_url('/mod/assign/view.php', array('id' => $assigncm->id));
					echo get_string('assignment','publication') . ': ' . html_writer::link($assignurl, $assign->name);
					
					if(has_capability('mod/publication:addinstance', $this->context)){
						$url = new moodle_url('/mod/publication/view.php',
								array('id'=>$this->coursemodule->id, 'sesskey'=>sesskey(), 'action'=>'import'));
						$label = get_string('updatefiles','publication');
						
						echo $OUTPUT->single_button($url, $label);
					}
					
				}else{
					echo get_string('assignment_notfound', 'publication');
				}
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

		echo html_writer::start_tag('div',array('id'=>'id_allfiles', 'class'=>'clearfix','aria-live'=>'polite'));
		$title = (has_capability('mod/publication:approve', $context)) ? get_string('allfiles', 'publication') : get_string('publicfiles', 'publication');
		echo html_writer::tag('div', $title, array('class' => 'legend'));
		echo html_writer::start_div('fcontainer clearfix');
		
		// Check to see if groups are being used in this assignment.
	
		// Find out current groups mode.
		$groupmode = groups_get_activity_groupmode($cm);
		$currentgroup = groups_get_activity_group($cm, true);

		echo groups_print_activity_menu($cm, $CFG->wwwroot . '/mod/publication/view.php?id=' . $cm->id, true);	
		
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
			$sql = 'SELECT u.id FROM {user} u '.
					'LEFT JOIN ('.$esql.') eu ON eu.id=u.id '.
					'LEFT JOIN {publication_file} files ON (u.id = files.userid) '. 
					'WHERE u.deleted = 0 AND eu.id=u.id '.
					'AND files.publication = '. $this->get_instance()->id . ' ';
					
			if($this->get_instance()->mode == PUBLICATION_MODE_UPLOAD){
				// mode upload
				if($this->get_instance()->obtainteacherapproval){
					// need teacher approval
					
					$where = 'files.teacherapproval = 1';
				}else{
					// no need for teacher approval
					// teacher only hasnt rejected
					$where = '(files.teacherapproval = 1 OR files.teacherapproval IS NULL)';
				}
			}else{	
				// mode import
				if(!$this->get_instance()->obtainstudentapproval){
					// no need to ask student and teacher has approved
					$where = 'files.teacherapproval = 1';
				}else{
					// student and teacher have approved					
					$where ='files.teacherapproval = 1 AND files.studentapproval = 1';
				}
			}
			
			$sql .= 'AND ' . $where . ' ';
			$sql .= 'GROUP BY u.id';
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
		
		$selectallnone = html_writer::checkbox('selectallnone', false,false,'',
				array('id'=>'selectallnone',
						'onClick'=>'toggle_userselection()'
				));
	
		$tablecolumns = array('selection', 'fullname');
		$tableheaders = array($selectallnone, get_string('fullnameuser'));
	
		$useridentity = $CFG->showuseridentity != '' ? explode(',', $CFG->showuseridentity) : array();

		foreach($useridentity as $cur){
			if(!(get_config('publication', 'hideidnumberfromstudents') && $cur == "idnumber" && !has_capability('mod/publication:approve', $context))
				&& !($cur != "idnumber" && !has_capability('mod/publication:approve', $context))){
				$tablecolumns[] = $cur;
				$tableheaders[] = ($cur == 'phone1') ? get_string('phone') : get_string($cur);
			}
		}
	
		$tableheaders[] = get_string('lastmodified');
		$tablecolumns[] = 'timemodified';
		
		if(has_capability('mod/publication:approve', $context)){
			// not necessary in upload mode without studentapproval
			if($this->get_instance()->mode == PUBLICATION_MODE_IMPORT &&
				$this->get_instance()->obtainstudentapproval){
				$tablecolumns[] = 'studentapproval';
				$tableheaders[] = get_string('studentapproval', 'publication') .' '.
					$OUTPUT->help_icon('studentapproval','publication');
			}
			
			$tablecolumns[] = 'teacherapproval';
			if($this->get_instance()->mode == PUBLICATION_MODE_IMPORT && $this->get_instance()->obtainstudentapproval){
				$tableheaders[] = get_string('obtainstudentapproval', 'publication');
			}else{
				$tableheaders[] = get_string('teacherapproval', 'publication');					
			}
			
			$tablecolumns[] = 'visibleforstudents';
			$tableheaders[] = get_string('visibleforstudents', 'publication');			
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

		$table->no_sorting('studentapproval');
		$table->no_sorting('selection');
		$table->no_sorting('teacherapproval');
		$table->no_sorting('visibleforstudents');
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
		$useridentityfields =  $CFG->showuseridentity != '' ? 'u.'.str_replace(',', ',u.', $CFG->showuseridentity) . ', ' : '';
		$totalfiles = 0;
		
		if (!empty($users)) {
			$select = 'SELECT '.$ufields.','.$useridentityfields.' username,
                                COUNT(*) filecount,
								SUM(files.studentapproval) as status,
                                MAX(files.timecreated) timemodified ';
			$sql = 'FROM {user} u '.
					'LEFT JOIN {publication_file} files ON u.id = files.userid
                            AND files.publication = '.$this->get_instance()->id.' '.
	                            'WHERE '.$where.'u.id IN ('.implode(',', $users).') '.
			                    'GROUP BY '.$ufields.','.$useridentityfields.' username ';
			
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
				$invalid = $OUTPUT->pix_icon('i/invalid',
						get_string('student_rejected', 'publication'));
				
				$visibleforstundets_yes = $OUTPUT->pix_icon('i/valid',
						get_string('visibleforstudents_yes', 'publication'));

				$visibleforstundets_no = $OUTPUT->pix_icon('i/invalid',
						get_string('visibleforstudents_no', 'publication'));
				
				foreach ($ausers as $auser) {
					if ($currentposition >= $offset && $currentposition < $endposition) {
						// Calculate user status.
						$selected_user = html_writer::checkbox('selectedeuser['.$auser->id .']', 'selected', false,
								null, array('class'=>'userselection'));
	
						$useridentity = $CFG->showuseridentity != '' ? explode(',', $CFG->showuseridentity) : array();
						foreach($useridentity as $cur){
							if(!(get_config('publication', 'hideidnumberfromstudents') && $cur == "idnumber" && !has_capability('mod/publication:approve', $context))){	
								if (!empty($auser->$cur)) {
									$$cur = html_writer::tag('div', $auser->$cur,
											array('id'=>'u'.$cur.$auser->id));
								} else {
									$$cur = html_writer::tag('div', '-', array('id'=>'u'.$cur.$auser->id));
								}
							}
						}	

						$userlink = '<a href="' . $CFG->wwwroot . '/user/view.php?id=' . $auser->id .
						'&amp;course=' . $course->id . '">' .
						fullname($auser, has_capability('moodle/site:viewfullnames', $this->context)) . '</a>';
						
						$extension = $this->user_extensionduedate($auser->id);
						
						if($extension){
							if(has_capability('mod/publication:grantextension', $context) ||
								has_capability('mod/publication:approve', $context)){
								$userlink .= '<br/>' . get_string('extensionto', 'publication') . ': ' . userdate($extension);
							}
						}
						
						$row = array($selected_user,$userlink);
	
						$useridentity = $CFG->showuseridentity != '' ? explode(',', $CFG->showuseridentity) : array();
						foreach($useridentity as $cur) {

							if(!(get_config('publication', 'hideidnumberfromstudents') && $cur == "idnumber" && !has_capability('mod/publication:approve', $context))
								&& !($cur != "idnumber" && !has_capability('mod/publication:approve', $context))){
								if(true /*!$this->column_is_hidden($cur)*/){
									$row[] = $$cur;
								}else{
									$row[] = "";
								}
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

/*------------------ start ----------------------------*/
						
						$filetable = new html_table();
						$filetable->attributes = array('class' => 'filetable');
						
						$statustable = new html_table();
						$statustable->attributes = array('class' => 'statustable');
						
						$permissiontable = new html_table();
						$permissiontable->attributes = array('class' => 'permissionstable');
						
						$visibleforuserstable = new html_table();
						$visibleforuserstable->attributes = array('class' => 'statustable');
						
						$conditions = array();
						$conditions['publication'] = $this->get_instance()->id;
						$conditions['userid'] = $auser->id;
						foreach($files as $file){
							$conditions['fileid'] = $file->get_id();
							$filepermissions = $DB->get_record('publication_file', $conditions);
							
							$showfile = false;
							
							if(has_capability('mod/publication:approve', $context)){
								$showfile = true;
							}else if($this->has_filepermission($file->get_id())){
								$showfile = true;
							}
							
							if($this->has_filepermission($file->get_id())){
								$visibleforuserstable->data[]= array($visibleforstundets_yes);
							}else{
								$visibleforuserstable->data[]= array($visibleforstundets_no);
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

									if(is_null($checked)){
										$checked = "";
									}else{
										$checked = $checked + 1;
									}
																
									$permissionrow = array();
																	
									$options = array();
									$options['2'] = get_string('yes');
									$options['1'] = get_string('no');
									
									$permissionrow[] = html_writer::select($options, 'files[' . $file->get_id() . ']',$checked);									
									
									$statusrow = array();
									
									if(is_null($filepermissions->studentapproval)){
										$statusrow[] = $questionmark;
									}else if($filepermissions->studentapproval){
										$statusrow[] = $valid;
									}else{
										$statusrow[] = $invalid;
									}
									$statustable->data[] = $statusrow;
									$permissiontable->data[] = $permissionrow;
								}
								$filetable->data[] = $filerow;
								$totalfiles++;
							}
						}
						
						$lastmodified = "";
						if(count($filetable->data) > 0){
							$lastmodified = html_writer::table($filetable);
							$lastmodified .= html_writer::span(userdate($auser->timemodified),"timemodified");
						}else{
							$lastmodified = get_string('nofiles', 'publication');
						}
					
						$row[] = $lastmodified;
						
						if(has_capability('mod/publication:approve', $context)){
							// not necessary in upload mode without studentapproval
							if($this->get_instance()->mode == PUBLICATION_MODE_IMPORT &&
								$this->get_instance()->obtainstudentapproval){
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
							
							$row[] = html_writer::table($visibleforuserstable);
						}

						$table->add_data($row);
					}
					$currentposition++;
				}			
				
				if (/*$totalfiles > 0*/true) { // always display download option
					$html .= html_writer::start_tag('div', array('class' => 'mod-publication-download-link'));
					$html .= html_writer::link(new moodle_url('/mod/publication/view.php',
							array('id' => $this->coursemodule->id, 'action' => 'zip')), get_string('downloadall', 'publication'));
					$html .= html_writer::end_tag('div');
				}

				echo $html;
				$html = "";
				
				
				$table->print_html();  // Print the whole table.
				
				$options = array();
				if (/*$totalfiles > 0*/true) { // always display download option
					$options['zipusers'] = get_string('zipusers', 'publication');
				
				}

				if($totalfiles > 0){
					if(has_capability('mod/publication:approve', $context)){
						$options['approveusers'] = get_string('approveusers', 'publication');
						$options['rejectusers'] = get_string('rejectusers', 'publication');
						
						if($this->get_instance()->mode == PUBLICATION_MODE_IMPORT && $this->get_instance()->obtainstudentapproval){
							$options['resetstudentapproval'] = get_string('resetstudentapproval', 'publication');
						}
					}
				}
				if(has_capability('mod/publication:grantextension', $this->get_context())){
					$options['grantextension'] = get_string('grantextension', 'publication');
				}
				
				if (count($options) > 0){
					if(has_capability('mod/publication:approve', $context)){
						$html .= html_writer::empty_tag('input', array('type'=>'reset', 'name'=>'resetvisibility',
								'value'=>get_string('reset', 'publication'),
								'class'=>'visibilitysaver'
						));
						
						if($this->get_instance()->mode == PUBLICATION_MODE_IMPORT && $this->get_instance()->obtainstudentapproval){
							$html .= html_writer::empty_tag('input', array('type'=>'submit', 'name'=>'savevisibility',
									'value'=>get_string('saveapproval', 'publication'),
									'class'=>'visibilitysaver'
							));
						}else{
							$html .= html_writer::empty_tag('input', array('type'=>'submit', 'name'=>'savevisibility',
									'value'=>get_string('saveteacherapproval', 'publication'),
									'class'=>'visibilitysaver'
							));
						}
					}
					
					$html .= html_writer::start_div('withselection');
					$html .= html_writer::span(get_string('withselected', 'publication'));
					$html .= html_writer::select($options, 'action');
					$html .= html_writer::empty_tag('input', array('type'=>'submit', 'name'=>'submitgo',
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
		
		
		// select all/none 
		$html .= html_writer::start_tag('div', array('class'=>'checkboxcontroller'));
		$html .= "<script type=\"text/javascript\">
                                        function toggle_userselection() {
                                        var checkboxes = document.getElementsByClassName('userselection');
										var sel = document.getElementById('selectallnone');
				
                                        if(checkboxes.length > 0){
                                        checkboxes[0].checked = sel.checked;
	
                                        for(var i = 1; i < checkboxes.length;i++){
                                        checkboxes[i].checked = checkboxes[0].checked;
    }
    }
    }
                                        </script>";
	/*
		$html .= '<div style="padding-top:14px;margin-left:12px;">';
		$html .= html_writer::tag('a', get_string('select_allnone', 'extserver'),
				array('href'=>'#', 'onClick'=>'toggle_userselection()'));
		$html .= '</div>';
		$html .= html_writer::end_tag('div');
*/
	
		$html .= html_writer::end_div();
		$html .= html_writer::end_div();
		$html .= html_writer::end_div();
		
		echo $html;
		
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
	
	/**
	 * Returns if a user has the permission to view a file
	 * @param unknown $fileid
	 * @param number $userid use for custom user, if 0 then if public visible
	 * @return boolean
	 */
	function has_filepermission($fileid, $userid=0){
		global $DB;
		
		$conditions = array();
		$conditions['publication'] = $this->get_instance()->id;
		$conditions['fileid'] = $fileid;
	
		$filepermissions = $DB->get_record('publication_file', $conditions);
	
		$haspermission = false;
	
		if($filepermissions){
				
			if($userid != 0){
				if($filepermissions->userid == $userid){
					// everyone is allowed to view their own files
					$haspermission = true;
				}
			}
	
			if($this->get_instance()->mode == PUBLICATION_MODE_UPLOAD){
				// mode upload
				if($this->get_instance()->obtainteacherapproval){
					// need teacher approval
					if($filepermissions->teacherapproval == 1){
						// teacher has approved
						$haspermission = true;
					}
				}else{
					// no need for teacher approval
					if(is_null($filepermissions->teacherapproval) || $filepermissions->teacherapproval == 1){
						// teacher only hasnt rejected
						$haspermission = true;
					}
				}
					
			}else{
					
				// mode import
				if(!$this->get_instance()->obtainstudentapproval && $filepermissions->teacherapproval == 1){
					// no need to ask student and teacher has approved
					$haspermission = true;
				}else if($this->get_instance()->obtainstudentapproval &&
				$filepermissions->teacherapproval == 1 &&
				$filepermissions->studentapproval == 1){
					// student and teacher have approved
					$haspermission = true;
				}
			}
		}
	
		return $haspermission;
	}
	
	public function download_file($fileid){
		global $DB, $USER;

		$conditions = array();
		$conditions['publication'] = $this->get_instance()->id;
		$conditions['fileid'] = $fileid;
		$record = $DB->get_record('publication_file', $conditions);
		
		$allowed = false;
		
		if(has_capability('mod/publication:approve', $this->get_context())){
			// teachers has to see the files to know if they can allow them
			$allowed = true;
		}else if($this->has_filepermission($fileid,$USER->id)){
			// file is publicly viewable or is owned by the user
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
	 * Creates a zip of all uploaded files and sends a zip to the browser
	 * @param unknown $users false => empty zip, true all users, array files from users in array
	 */
	public function download_zip($users = array()) {
		global $CFG, $DB;
		require_once($CFG->libdir.'/filelib.php');

		$context = $this->get_context();
		$cm = $this->get_coursemodule();
		
		$conditions = array();
		$conditions['publication'] = $this->get_instance()->id;
			
		$filesforzipping = array();
		$fs = get_file_storage();
		$filearea = 'attachment';

		// Find out current groups mode.
		$groupmode = groups_get_activity_groupmode($this->get_coursemodule());
		$currentgroup = groups_get_activity_group($this->get_coursemodule(), true);
		
		// get group name for filename
		$groupname = '';

		
		// Get all ppl that are allowed to submit assignments.
		list($esql, $params) = get_enrolled_sql($context, 'mod/publication:view', $currentgroup);
		
		$showall = false;
		
		if(has_capability('mod/publication:approve', $context) ||
		has_capability('mod/publication:grantextension', $context)){
			$showall = true;
		}
		
		if(is_array($users) && count($users) > 0){
			$customusers = " and u.id IN (" . implode($users, ',') . ") ";
				
		}else if($users == false){
			$customusers = " AND 1=2 ";
		}
		
		if ($showall) {
			$sql = 'SELECT u.id FROM {user} u '.
					'LEFT JOIN ('.$esql.') eu ON eu.id=u.id '.
					'WHERE u.deleted = 0 AND eu.id=u.id '. $customusers;
		} else {
			$sql = 'SELECT u.id FROM {user} u '.
					'LEFT JOIN ('.$esql.') eu ON eu.id=u.id '.
					'LEFT JOIN {publication_file} files ON (u.id = files.userid) '.
					'WHERE u.deleted = 0 AND eu.id=u.id '. $customusers . 
					'AND files.publication = '. $this->get_instance()->id . ' ';
				
			if($this->get_instance()->mode == PUBLICATION_MODE_UPLOAD){
				// mode upload
				if($this->get_instance()->obtainteacherapproval){
					// need teacher approval
						
					$where = 'files.teacherapproval = 1';
				}else{
					// no need for teacher approval
					// teacher only hasnt rejected
					$where = '(files.teacherapproval = 1 OR files.teacherapproval IS NULL)';
				}
			}else{
				// mode import
				if(!$this->get_instance()->obtainstudentapproval){
					// no need to ask student and teacher has approved
					$where = 'files.teacherapproval = 1';
				}else{
					// student and teacher have approved
					$where ='files.teacherapproval = 1 AND files.studentapproval = 1';
				}
			}
				
			$sql .= 'AND ' . $where . ' ';
			$sql .= 'GROUP BY u.id';
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
		
		
			
		$filename = str_replace(' ', '_', clean_filename($this->course->shortname.'-'.
				$this->get_instance()->name.'-'.$groupname.$this->get_instance()->id.'.zip')); // Name of new zip file.

		$userfields = get_all_user_name_fields();
		$userfields['id'] = 'id';
		$userfields['username'] ='username';
		$userfields = implode(', ', $userfields);
		
		$viewfullnames = has_capability('moodle/site:viewfullnames', $this->context);

		// get all files from each user
		foreach ($users as $uploader) {
			$a_userid = $uploader;
			
			$conditions['userid'] = $uploader;
			$records = $DB->get_records('publication_file', $conditions);
				
				
			$a_assignid = $a_userid; // Get name of this assignment for use in the file names.
			// Get user firstname/lastname.
			$a_user = $DB->get_record('user', array('id'=>$a_userid), $userfields);
				
				
			foreach($records as $record){
					
				if(has_capability('mod/publication:approve', $this->get_context()) ||
					$this->has_filepermission($record->fileid)){
					// is teacher or file is public
					
					$file = $fs->get_file_by_id($record->fileid);
						
					// Get files new name.
					$fileext = strstr($file->get_filename(), '.');
					$fileoriginal = str_replace($fileext, '', $file->get_filename());
					$fileforzipname =  clean_filename(fullname($a_user,$viewfullnames) . '_' . $fileoriginal.'_'.$a_userid.$fileext);
					// Save file name to array for zipping.
					$filesforzipping[$fileforzipname] = $file;
				}
			}
		} // End of foreach.
		
		/*
		if (empty($filesforzipping)) {
			print_error('nofilestozip', 'publication');
		}
		*/
	
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
			$assign_module_id = $DB->get_field('modules', 'id', array('name'=>'assign'));
			$assigncm = $DB->get_record('course_modules',
					array('course'=>$assign->course, 'module'=>$assign_module_id, 'instance'=>$assign->id));
					
			$assigncontext = context_module::instance($assigncm->id);
			
			if($assign && $assigncm){		
				if(has_capability('mod/publication:addinstance', $this->context)){
					$records = $DB->get_records('assignsubmission_file',array('assignment'=>$this->get_instance()->importfrom));
									
					foreach($records as $record){
						
						$fs = get_file_storage();
						$files = $fs->get_area_files($assigncontext->id,
								"assignsubmission_file",
								"submission_files",
								$record->submission,
								"id",
								false);
						
						$submission = $DB->get_record('assign_submission', array('id'=>$record->submission));
						
						$assignfileids = array();
						
						$assignfiles = array();
						
						foreach($files as $file){
							$assignfiles[$file->get_id()] = $file;
							$assignfileids[$file->get_id()] = $file->get_id();
						}
						
						$conditions = array();
						$conditions['publication'] = $this->get_instance()->id;
						$conditions['userid'] = $submission->userid;
						
						$oldpubfiles = $DB->get_records('publication_file',$conditions);						
						
						foreach($oldpubfiles as $oldpubfile){
							
							if(in_array($oldpubfile->filesourceid,$assignfileids)){
								// file was in assign and is still there
								unset($assignfileids[$oldpubfile->filesourceid]);
								
							}else{
								// file has been removed from assign
								// remove from publication (file and db entry)
								
								$file = $fs->get_file_by_id($oldpubfile->fileid);
								$file->delete();
								
								$conditions['id'] = $oldpubfile->id;
								
								$DB->delete_records('publication_file',$conditions);
							}
						}
						
						// add new files to publication
						foreach($assignfileids as $assignfileid){							
							$newfilerecord = new stdClass();
							$newfilerecord->contextid = $this->get_context()->id;
							$newfilerecord->component = 'mod_publication';
							$newfilerecord->filearea = 'attachment';
							$newfilerecord->itemid = $submission->userid;
							
							try{
								$newfile = $fs->create_file_from_storedfile($newfilerecord, $assignfiles[$assignfileid]);
								
								$dataobject = new stdClass();
								$dataobject->publication = $this->get_instance()->id;
								$dataobject->userid = $submission->userid;
								$dataobject->timecreated = time();
								$dataobject->fileid = $newfile->get_id();
								$dataobject->filesourceid = $assignfileid;
								$dataobject->filename = $newfile->get_filename();
								$dataobject->contenthash = "666";
								$dataobject->type = PUBLICATION_MODE_IMPORT;
								
								$newid = $DB->insert_record('publication_file', $dataobject);
								
								
							}catch(Exception $e){
								// file could not be copied, maybe it does allready exist
								// should not happen
							}
						}
					}

					return true;
				}
			}
		}
		
		return false;
	}
}
