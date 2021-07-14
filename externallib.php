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
 * mod_publication external file
 *
 * @package       mod_publication
 * @author        Philipp Hager
 * @author        Andreas Windbichler
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . "/mod/publication/locallib.php");

/**
 * Class mod_publication_external contains external functions used by mod_publication's AJAX
 *
 * @package       mod_publication
 * @author        Philipp Hager
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_publication_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_onlinetextpreview_parameters() {
        // Function get_onlinetextpreview_parameters() always return an external_function_parameters().
        // The external_function_parameters constructor expects an array of external_description.
        return new external_function_parameters(
        // An external_description can be: external_value, external_single_structure or an external_multiple structure!
            [
                'itemid' => new external_value(PARAM_INT, 'Group\'s or user\'s ID'),
                'cmid' => new external_value(PARAM_INT, 'Coursemodule ID')
            ]
        );
    }


    /**
     * The function itself
     *
     * @param int $itemid the itemid (user ID or group ID) whom the onlinetext belongs to!
     * @param int $cmid the course-module ID
     * @return string welcome message
     */
    public static function get_onlinetextpreview($itemid, $cmid) {
        global $DB;

        // Parameters validation!
        $params = self::validate_parameters(self::get_onlinetextpreview_parameters(),
            [
                'itemid' => $itemid,
                'cmid' => $cmid
            ]);
        $cm = get_coursemodule_from_id('publication', $params['cmid'], 0, false, MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/publication:view', $context);
        require_login($course, true, $cm);

        $text = publication::export_onlinetext_for_preview($params['itemid'], $cm->instance, $context->id);

        return format_text($text, FORMAT_HTML);
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function get_onlinetextpreview_returns() {
        return new external_value(PARAM_RAW, 'HTML snippet representing the online text to use in overlay', VALUE_OPTIONAL, '');
    }

    /**
     * Returns description of the get_publications_by_courses parameters
     *
     * @return external_function_parameters
     */
    public static function get_publications_by_courses_parameters() {
        return new external_function_parameters([
            'courseids' => new external_multiple_structure(
                new external_value(PARAM_INT, 'Course id'), 'Array of course ids (all enrolled courses if empty array)', VALUE_DEFAULT, []
            ),
        ]);
    }

    /**
     * Returns description of the get_publications_by_courses result value
     *
     * @return external_single_structure
     */
    public static function get_publications_by_courses_returns() {
        return new external_single_structure([
            'publications' => new external_multiple_structure(self::publication_structure(), 'All publications for the given courses'),
            'warnings' => new external_warnings()
        ]);
    }

    /**
     * Get all publications for the courses with the given ids. If the ids are empty all publications from all
     * user-enrolled courses are returned.
     *
     * @param $courseids array the ids of the courses to get publications for (all user enrolled courses if empty array)
     * @return stdClass
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     */
    public static function get_publications_by_courses($courseids) {

        $params = self::validate_parameters(self::get_publications_by_courses_parameters(), [
            'courseids' => $courseids
        ]);

        $rpublications = [];
        $warnings = [];

        $mycourses = new stdClass();
        if (empty($params['courseids'])) {
            $mycourses = enrol_get_my_courses();
            $params['courseids'] = array_keys($mycourses);
        }

        // Ensure there are courseids to loop through.
        if (!empty($params['courseids'])) {

            list($courses, $warnings) = external_util::validate_courses($params['courseids'], $mycourses);

            // Get the publications in this course, this function checks users visibility permissions.
            // We can avoid then additional validate_context calls.
            $publication_instances = get_all_instances_in_courses("publication", $courses);
            foreach ($publication_instances as $publication_instance) {

                $cm = get_coursemodule_from_id('publication', $publication_instance->coursemodule);
                $publication = new publication($cm);
                $rpublications[] = self::export_publication($publication->get_instance());
            }
        }

        $result = new stdClass();
        $result->publications = $rpublications;
        $result->warnings = $warnings;
        return $result;
    }


    /**
     * Returns description of the get_publication parameters
     *
     * @return external_function_parameters
     */
    public static function get_publication_parameters() {
        return new external_function_parameters([
            'publicationid' => new external_value(PARAM_INT, 'The id of the publication'),
        ]);
    }

    /**
     * Returns description of the get_publication result value
     *
     * @return external_single_structure
     */
    public static function get_publication_returns() {
        return new external_single_structure([
            'publication' => self::publication_structure(),
        ]);
    }

    /**
     * Returns the publication for the given id
     *
     * @param $publicationid
     * @return stdClass
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws required_capability_exception
     * @throws restricted_context_exception
     */
    public static function get_publication($publicationid) {
        global $DB, $USER;

        $params = self::validate_parameters(self::get_publication_parameters(), [
            'publicationid' => $publicationid,
        ]);

        $cm = get_coursemodule_from_instance('publication', $params['publicationid'], 0, false, MUST_EXIST);

        $context = context_module::instance($cm->id);
        require_capability('mod/publication:view', $context);
        self::validate_context($context);

        $publication = new publication($cm);
        $result = new stdClass();

        $result->publication = self::export_publication($publication->get_instance());
        $publication_files = $DB->get_records('publication_file', ['publication' => $publication->get_instance()->id], '', '*');

        $files = [];
        foreach ($publication_files as $index => $publication_file) {
            if ($publication->has_filepermission($publication_file->fileid, $USER->id)) {
                $files[] = $publication_file;
            }
        }

        $result->publication->files = self::export_publication_files($files);

        return $result;
    }

    /**
     * Description of the publication structure in result values
     *
     * @return external_single_structure
     */
    private static function publication_structure() {
        return new external_single_structure(
            [
                'id' => new external_value(PARAM_INT, 'publication id'),
                'course' => new external_value(PARAM_INT, 'course id the publication belongs to'),
                'name' => new external_value(PARAM_TEXT, 'publication name'),
                'intro' => new external_value(PARAM_RAW, 'intro/description of the publication'),
                'introformat' => new external_value(PARAM_INT, 'intro format'),
                'files' => new external_multiple_structure(self::publication_file_structure(), 'files in this publication', VALUE_OPTIONAL),
            ], 'publication information'
        );
    }

    /**
     * Description of the publication file structure in result values
     *
     * @return external_single_structure
     */
    private static function publication_file_structure() {
        return new external_single_structure(
            [
                'id' => new external_value(PARAM_INT, 'file id'),
                'name' => new external_value(PARAM_TEXT, 'file name'),
                'mimetype' => new external_value(PARAM_TEXT, 'file mime type'),
                'download_url' => new external_value(PARAM_RAW, 'download url for this file'),
            ], 'publication file information'
        );
    }

    /**
     * Converts the given publication to match the publication structure for result values
     *
     * @param $publication object       The publication to be exported
     * @return object                   The exported publication (confirms to publication structure)
     */
    private static function export_publication($publication) {
        $result = new stdClass();

        $result->id = $publication->id;
        $result->course = $publication->course;
        $result->name = $publication->name;
        $result->intro = $publication->intro;
        $result->introformat = $publication->introformat;

        return $result;
    }

    /**
     * Converts the given files to match the publication file structure for result values
     *
     * @param $files array              The files to be exported
     * @return array                    The exported publication files (confirms to publication file structure)
     */
    private static function export_publication_files($files) {
        $result_files = [];

        $fs = get_file_storage();

        foreach ($files as $index => $file_record) {
            $result_file = new stdClass();

            $file = $fs->get_file_by_id($file_record->fileid);

            $result_file->id = $file->get_id();
            $result_file->name = $file->get_filename();
            $result_file->mimetype = $file->get_mimetype();
            $result_file->download_url = moodle_url::make_webservice_pluginfile_url(
                $file->get_contextid(), $file->get_component(), $file->get_filearea(),
                $file->get_itemid(), $file->get_filepath(), $file->get_filename(), true
            )->out(false);

            $result_files[] = $result_file;
        }

        return $result_files;
    }

}
