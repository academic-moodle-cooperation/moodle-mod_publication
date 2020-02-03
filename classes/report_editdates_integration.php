<?php
// This file is part of Moodle - http://moodle.org/
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
 * Contains class for report-editdates support
 *
 * @package       mod_publication
 * @author        Hannes Laimer
 * @author        Andreas Krieger
 * @copyright     2019 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Class needed for report-editdates support
 *
 * @package       mod_publication
 * @author        Hannes Laimer
 * @author        Andreas Krieger
 * @copyright     2019 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_publication_report_editdates_integration
extends report_editdates_mod_date_extractor {

    /**
     * mod_publication_report_editdates_integration constructor.
     * @param object $course the course
     */
    public function __construct($course) {
        parent::__construct($course, 'publication');
        parent::load_data();
    }

    /**
     * Returns the duedates for a specific publication
     * @param cm_info $cm
     * @return array
     * @throws coding_exception
     */
    public function get_settings(cm_info $cm) {
        $publication = $this->mods[$cm->instance];

        return array(
                'allowsubmissionsfromdate' => new report_editdates_date_setting(
                        get_string('allowsubmissionsfromdate', 'publication'),
                        $publication->allowsubmissionsfromdate,
                        self::DATETIME, true, 5),
                'duedate' => new report_editdates_date_setting(
                        get_string('duedate', 'publication'),
                        $publication->duedate,
                        self::DATETIME, true, 5),
                );
    }

    /**
     * Validates dates
     * @param cm_info $cm
     * @param array $dates
     * @return array
     * @throws coding_exception
     */
    public function validate_dates(cm_info $cm, array $dates) {
        $errors = array();
        if ($dates['allowsubmissionsfromdate'] && $dates['duedate']
                && $dates['duedate'] < $dates['allowsubmissionsfromdate']) {
            $errors['duedate'] = get_string('duedatevalidation', 'publication');
        }

        return $errors;
    }

    /**
     * Saves the dates
     * @param cm_info $cm
     * @param array $dates
     * @throws dml_exception
     */
    public function save_dates(cm_info $cm, array $dates) {
        global $DB;

        $update = new stdClass();
        $update->id = $cm->instance;
        $update->duedate = $dates['duedate'];
        $update->allowsubmissionsfromdate = $dates['allowsubmissionsfromdate'];

        $DB->update_record('publication', $update);
    }
}
