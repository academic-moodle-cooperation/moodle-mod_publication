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

/**
 * Adds a new publication instance
 *
 * @param publication $publication
 * @param mform $mform
 */
function publication_add_instance($publication, $mform=null) {
	global $CFG, $DB;

	$cmid       = $publication->coursemodule;
	$cmidnumber = $publication->cmidnumber;
	$courseid   = $publication->course;

	try {
		$id = $DB->insert_record('publication', $publication);
	} catch (Exception $e) {
		var_dump($e);
	}

	$DB->set_field('course_modules', 'instance', $id, array('id'=>$cmid));

	$record = $DB->get_record('publication', array('id'=>$id));

	$record->course     = $courseid;
	$record->cmidnumber = $cmidnumber;
	$record->cmid       = $cmid;

	return $record->id;
}

/**
 * Return the list if Moodle features this module supports
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, null if doesn't know
 */
function publication_supports($feature) {
	switch($feature) {

		case FEATURE_GROUPS:                  return true;
		case FEATURE_GROUPINGS:               return true;
		case FEATURE_GROUPMEMBERSONLY:        return true;
		case FEATURE_MOD_INTRO:               return true;
		case FEATURE_GRADE_HAS_GRADE:         return false;
		case FEATURE_GRADE_OUTCOMES:          return false;
		case FEATURE_GRADE_HAS_GRADE:         return false;
		case FEATURE_BACKUP_MOODLE2:          return false;
		case FEATURE_SHOW_DESCRIPTION:        return true;
		case FEATURE_IDNUMBER:                return false;

		default: return null;
	}
}

/**
 * updates an existing publication instance
 *
 * @param publication $publication
 * @param mform $mform
 */
function publication_update_instance($publication, $mform=null) {
	global $CFG, $DB;

	$publication->id = $publication->instance;

	$publication->timemodified = time();

	$DB->update_record('publication', $publication);

	return true;
}

/**
 * complete deletes an publication instance
 *
 * @param int $id
 * @return boolean
 */
function publication_delete_instance($id) {
	global $CFG, $DB;

	if (! $publication = $DB->get_record('publication', array('id'=>$id))) {
		return false;
	}

	$result = true;
	if (! $DB->delete_records('publication', array('id'=>$publication->id))) {
		$result = false;
	}
	return $result;
}

/**
 *
 * @param stdClass $coursemodule The coursemodule object (record).
 * @return cached_cm_info An object on information that the courses
 *                        will know about (most noticeably, an icon).
 */
function publication_get_coursemodule_info($coursemodule) {
	global $CFG, $DB;

	$dbparams = array('id'=>$coursemodule->instance);
	$fields = 'id, name, alwaysshowdescription, allowsubmissionsfromdate, intro, introformat';
	if (! $publication = $DB->get_record('publication', $dbparams, $fields)) {
		return false;
	}

	$result = new cached_cm_info();
	$result->name = $publication->name;
	if ($coursemodule->showdescription) {
		if ($publication->alwaysshowdescription || time() > $publication->allowsubmissionsfromdate) {
			// Convert intro to html. Do not filter cached version, filters run at display time.
			$result->content = format_module_intro('publication', $publication, $coursemodule->id, false);
		}
	}
	return $result;
}