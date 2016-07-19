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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * lib.php
 *
 * @package       mod_publication
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Philipp Hager (office@phager.at)
 * @author        Andreas Windbichler
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Adds a new publication instance
 *
 * @param publication $publication
 * @param mform $mform
 */
function publication_add_instance($publication, $mform = null) {
    global $DB, $OUTPUT;

    $cmid       = $publication->coursemodule;
    $courseid   = $publication->course;

    try {
        $id = $DB->insert_record('publication', $publication);
    } catch (Exception $e) {
        echo $OUTPUT->notification($e->message, 'error');
    }

    $DB->set_field('course_modules', 'instance', $id, array('id' => $cmid));

    $record = $DB->get_record('publication', array('id' => $id));

    $record->course     = $courseid;
    $record->cmid       = $cmid;

    $course = $DB->get_record('course', array('id' => $record->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_id('publication', $cmid, 0, false, MUST_EXIST);
    $context = context_module::instance($cm->id);
    $instance = new publication($cm, $course, $context);

    if ($instance->get_instance()->mode == PUBLICATION_MODE_IMPORT
            && !empty($instance->get_instance()->autoimport)) {
        // Fetch all files right now!
        $instance->importfiles();
    }

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
        case FEATURE_GROUPS:
            return true;
        case FEATURE_GROUPINGS:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_IDNUMBER:
            return false;

        default:
            return null;
    }
}

/**
 * updates an existing publication instance
 *
 * @param publication $publication
 * @param mform $mform
 */
function publication_update_instance($publication, $mform = null) {
    global $DB;

    $publication->id = $publication->instance;

    $publication->timemodified = time();

    $DB->update_record('publication', $publication);

    $course = $DB->get_record('course', array('id' => $publication->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('publication', $publication->id, 0, false, MUST_EXIST);
    $context = context_module::instance($cm->id);
    $instance = new publication($cm, $course, $context);

    if ($instance->get_instance()->mode == PUBLICATION_MODE_IMPORT
            && !empty($instance->get_instance()->autoimport)) {
        // Fetch all files right now!
        $instance->importfiles();
    }

    return true;
}

/**
 * complete deletes an publication instance
 *
 * @param int $id
 * @return boolean
 */
function publication_delete_instance($id) {
    global $DB;

    if (! $publication = $DB->get_record('publication', array('id' => $id))) {
        return false;
    }

    $DB->delete_records('publication_extduedates', array('publication' => $publication->id));

    $fs = get_file_storage();

    $fs->delete_area_files($publication->id, 'mod_publication', 'attachment');

    $DB->delete_records('publication_file', array('publication' => $publication->id));

    $result = true;
    if (! $DB->delete_records('publication', array('id' => $publication->id))) {
        $result = false;
    }
    return $result;
}

/**
 * Returns info object about the course module
 *
 * @param stdClass $coursemodule The coursemodule object (record).
 * @return cached_cm_info An object on information that the courses
 *                        will know about (most noticeably, an icon).
 */
function publication_get_coursemodule_info($coursemodule) {
    global $DB;

    $dbparams = array('id' => $coursemodule->instance);
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

/**
 * Defines which elements mod_publication needs to add to reset form
 *
 * @param moodleform $mform The reset course form to extend
 */
function publication_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'publicationheader', get_string('modulenameplural', 'publication'));
    $mform->addElement('checkbox', 'reset_publication_userdata', get_string('reset_userdata', 'publication'));
}

/**
 * Reset the userdata in publication module
 *
 * @param object $data settings object which userdata to reset
 * @return array[] array of associative arrays giving feedback what has been successfully reset
 */
function publication_reset_userdata($data) {
    global $DB;

    if (!$DB->count_records('publication', array('course' => $data->courseid))) {
        return array();
    }

    $componentstr = get_string('modulenameplural', 'publication');
    $status = array();

    if (isset($data->reset_publication_userdata)) {

        $publications = $DB->get_records('publication', array('course' => $data->courseid));

        foreach ($publications as $publication) {

            $DB->delete_records('publication_extduedates', array('publication' => $publication->id));

            $filerecords = $DB->get_records('publication_file', array('publication' => $publication->id));

            $fs = get_file_storage();
            foreach ($filerecords as $filerecord) {
                $file = $fs->get_file_by_id($filerecord->fileid);
                $file->delete();
            }

            $DB->delete_records('publication_file', array('publication' => $publication->id));

            $status[] = array('component' => $componentstr, 'item' => $publication->name,
                    'error' => false);
        }
    }

    return $status;

}