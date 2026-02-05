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
 * observer.php
 *
 * @package       mod_publication
 * @author        Philipp Hager
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_publication;

use publication;

/**
 * mod_grouptool\observer handles events due to changes in moodle core which affect grouptool
 *
 * @package       mod_publication
 * @author        Philipp Hager
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer {
    /**
     * Event observer for the 'course_module_created' event.
     *
     * This method is triggered when any course module is created. If the module is
     * of type 'publication', it initializes the publication instance and, if its mode
     * is set to PUBLICATION_MODE_IMPORT, automatically imports files. It also sends
     * any pending notifications related to the publication module.
     *
     * @param \core\event\base $event The event data for the created course module.
     */
    public static function course_module_created(\core\event\base $event) {
        global $DB;
        $eventdata = $event->get_data();
        if (
            isset($eventdata['other']) &&
            isset($eventdata['other']['modulename']) && $eventdata['other']['modulename'] == 'publication'
        ) {
            $cm = get_coursemodule_from_instance('publication', $eventdata['other']['instanceid'], 0, false, MUST_EXIST);
            $publication = new publication($cm);
            if ($publication->get_instance()->mode == PUBLICATION_MODE_IMPORT) {
                $publication->importfiles();
            }
            publication::send_all_pending_notifications();
        }
    }

    /**
     * Event observer for \mod_assign\event\assessable_submitted
     *
     * @param \mod_assign\event\base $e Event object containing useful data
     * @return bool true if success
     */
    public static function import_assessable(\mod_assign\event\base $e) {
        global $DB, $CFG, $OUTPUT;

        // Keep other page calls slimmed down!
        require_once($CFG->dirroot . '/mod/publication/locallib.php');

        // We have the submission ID, so first we fetch the corresponding submission, assign, etc.!
        $assign = $e->get_assign();
        $assignid = $assign->get_course_module()->instance;
        $submission = $DB->get_record($e->objecttable, ['id' => $e->objectid]);

        if (!empty($assign->get_instance()->teamsubmission) && !empty($submission->userid)) {
            /* If the userid is set, we can skip here... the files and texts are in the submission with groupid set
               or groupid 0 for users without group! */
            return true;
        }

        $assignmoduleid = $DB->get_field('modules', 'id', ['name' => 'assign']);
        $assigncm = $DB->get_record('course_modules', [
                'course' => $assign->get_course()->id,
                'module' => $assignmoduleid,
                'instance' => $assignid,
        ]);
        $assigncontext = \context_module::instance($assigncm->id);

        $sql = "SELECT pub.*
                  FROM {publication} pub
                 WHERE (pub.mode = ?) AND (pub.importfrom = ?)";
        $params = [\PUBLICATION_MODE_IMPORT, $assignid];
        if (!$publications = $DB->get_records_sql($sql, $params)) {
            return true;
        }

        foreach ($publications as $pub) {
            $cm = get_coursemodule_from_instance('publication', $pub->id);
            if (!$cm) {
                continue;
            }
            $publication = new publication($cm);
            $publication->importfiles();
        }

        publication::send_all_pending_notifications();
        return true;
    }
}
