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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/publication/locallib.php');

/**
 * Form for displaying and changing approval for publication files
 *
 * @package mod_publication
 * @author Andreas Windbichler
 * @copyright TSC
*/
class mod_publication_allfiles_form extends moodleform {

	public function definition(){
		global $CFG, $OUTPUT, $DB, $USER;

		$publication = &$this->_customdata['publication'];
		$filearea = &$this->_customdata['filearea'];
		$cm = $publication->get_coursemodule();

		$mform = $this->_form;
		$mform->addElement('header', 'allfiles', get_string('allfiles', 'publication'));
		$mform->setExpanded('allfiles');


		$html = html_writer::start_div('initialbar firstinitial');
		$html .= "Vorname: ";
		$links = "";
		for($c = 'A'; $c <= 'Z'; $c = chr(ord($c)+1)){
			$url = null;
			$html .= html_writer::link($url, $c) . "\n";
		}
		$html .= html_writer::end_div();
		$mform->addElement('html',$html);

		$html = html_writer::start_div('initialbar firstinitial');
		$html .= "Nachname: ";
		$links = "";
		for($c = 'A'; $c <= 'Z'; $c = chr(ord($c)+1)){
			$url = null;
			$html .= html_writer::link($url, $c) . "\n";
		}
		$html .= html_writer::end_div();

		$mform->addElement('html',$html);

		require_once($CFG->libdir.'/tablelib.php');
		$table = new flexible_table('mod-publication-submissions');

		$tablecolumns = array();
		$tableheaders = array();

		$tablecolumns[] = "selection";
		$tableheaders[] = "";

		$tablecolumns[] = "fullname";
		$tableheaders[] = get_string('fullnameuser');

		$useridentity = explode(',', $CFG->showuseridentity);
		foreach($useridentity as $cur){
			$tablecolumns[] = $cur;
			$tableheaders[] = ($cur == 'phone1') ? get_string('phone') : get_string($cur);
		}

		$tablecolumns[] = "lastedit";
		$tableheaders[] = "zuletzt geändert";

		$tablecolumns[] = "status";
		$tableheaders[] = "status";

		$tablecolumns[] = "visibility";
		$tableheaders[] = "für alle sichtbar";
			
		$table->define_columns($tablecolumns);
		$table->define_headers($tableheaders);

		$table->sortable(true, 'fullname');


		$table->setup();

		// Get all ppl that can submit assignments.

		$context = $publication->get_context();

		$currentgroup = groups_get_activity_group($cm);
		$users = get_enrolled_users($context, 'mod/publication:view', $currentgroup, 'u.id');
		if ($users) {
			$users = array_keys($users);
			// If groupmembersonly used, remove users who are not in any group.
			if (!empty($CFG->enablegroupmembersonly) and $cm->groupmembersonly) {
				if ($groupingusers = groups_get_grouping_members($cm->groupingid, 'u.id', 'u.id')) {
					$users = array_intersect($users, array_keys($groupingusers));
				}
			}
		}

		// Get all ppl that are allowed to submit assignments.
		list($esql, $params) = get_enrolled_sql($context, 'mod/publication:view', $currentgroup);

		var_dump($users);


		$tablecolumns[] = 'id';

		$fs = get_file_storage();
		$this->dir = $fs->get_area_tree($publication->get_context()->id, 'mod_publication', $filearea, $sid);

		$files = $fs->get_area_files($publication->get_context()->id,
				'mod_publication',
				$filearea,
				$sid,
				'timemodified',
				false);

		if (!isset($table->attributes)) {
			$table->attributes = array('class' => 'coloredrows');
		} else if (!isset($table->attributes['class'])) {
			$table->attributes['class'] = 'coloredrows';
		} else {
			$table->attributes['class'] .= ' coloredrows';
		}

		$conditions = null; //TODO make conditions according to selections
		$sort = ''; //TODO implement order

		$records = $DB->get_records('publication_file',$conditions,$sort);

		if ($sort = $table->get_sql_sort()) {
			$sort = ' ORDER BY '.$sort;
		}

		foreach($records as $record){
			$row = array();
			$row[] = "";
			$row[] = "Full Username"; //TODO replace by username
			$row[] = "USERID"; //TODO replace by userid
			$row[] = $record->filename;
				
			$useridentityfields = 'u.'.str_replace(',', ',u.', $CFG->showuseridentity);
				
			if (!empty($users)) {
				$select = 'SELECT '.$ufields.','.$useridentityfields.', username,
						s.id AS submissionid, s.grade, s.submissioncomment,
						s.timemodified, s.timemarked ';
				$sql = 'FROM {user} u '.
						'LEFT JOIN {extserver_submissions} s ON u.id = s.userid
								AND s.assignment = '.$this->assignment->id.' '.
								'WHERE '.$where.'u.id IN ('.implode(',', $users).') ';

			}
				
			$ausers = $DB->get_records_sql($select.$sql.$sort, $params, $table->get_page_start(), $table->get_page_size());
			$table->pagesize($perpage, count($users));
				
				
			$table->add_data($row);
		}

		$table->print_html();
	}
}