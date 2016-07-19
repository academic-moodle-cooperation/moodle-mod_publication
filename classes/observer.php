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
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Philipp Hager
 * @copyright     2016 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_publication;

defined('MOODLE_INTERNAL') || die;

/**
 * mod_grouptool\observer handles events due to changes in moodle core which affect grouptool
 *
 * @package       mod_grouptool
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Philipp Hager
 * @since         Moodle 2.8+
 * @copyright     2015 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer {
    /**
     * \mod_assign\event\assessable_submitted
     *
     * @param \mod_assign\event\assessable_submitted $e Event object containing useful data
     * @return bool true if success
     */
    public static function import_assessable(\mod_assign\event\assessable_submitted $e) {
        global $DB, $CFG, $OUTPUT;

        // Keep other page calls slimed down!
        require_once($CFG->dirroot .'/mod/publication/locallib.php');

        // We have the submission ID, so first we fetch the corresponding submission, assign, etc.!
        $assign = $e->get_assign();
        $assignid = $assign->get_course_module()->instance;
        $submission = $DB->get_record($e->objecttable, array('id' => $e->objectid));
        $assignmoduleid = $DB->get_field('modules', 'id', array('name' => 'assign'));
        $assigncm = $DB->get_record('course_modules', array('course'   => $assign->get_course()->id,
                                                            'module'   => $assignmoduleid,
                                                            'instance' => $assignid));
        $assigncontext = \context_module::instance($assigncm->id);

        $sql = "SELECT pub.id, pub.course
                  FROM {publication} pub
                 WHERE (pub.mode = ?) AND (pub.importfrom = ?) AND (pub.autoimport = 1)";
        $params = array(\PUBLICATION_MODE_IMPORT, $assignid);
        if (! $publications = $DB->get_records_sql($sql, $params)) {
            return true;
        }

        $subfilerecords = $DB->get_records('assignsubmission_file', array('assignment' => $assignid,
                                                                          'submission' => $submission->id));
        $fs = get_file_storage();

        $assignfileids = array();
        $assignfiles = array();

        foreach ($publications as $curpub) {
            $cm = get_coursemodule_from_instance('publication', $curpub->id, 0, false, MUST_EXIST);
            $context = \context_module::instance($cm->id);
            foreach ($subfilerecords as $record) {
                if ($assignfileids == array()) {
                    $files = $fs->get_area_files($assigncontext->id,
                                                 "assignsubmission_file",
                                                 "submission_files",
                                                 $record->submission,
                                                 "id",
                                                 false);

                    foreach ($files as $file) {
                        $assignfiles[$file->get_id()] = $file;
                        $assignfileids[$file->get_id()] = $file->get_id();
                    }
                }

                $conditions = array();
                $conditions['publication'] = $curpub->id;
                $conditions['userid'] = $submission->userid;

                $oldpubfiles = $DB->get_records('publication_file', $conditions);

                foreach ($oldpubfiles as $oldpubfile) {

                    if (in_array($oldpubfile->filesourceid, $assignfileids)) {
                        // File was in assign and is still there.
                        unset($assignfileids[$oldpubfile->filesourceid]);

                    } else {
                        // File has been removed from assign.
                        // Remove from publication (file and db entry).
                        $file = $fs->get_file_by_id($oldpubfile->fileid);
                        $file->delete();

                        $conditions['id'] = $oldpubfile->id;

                        $DB->delete_records('publication_file', $conditions);
                    }
                }

                // Add new files to publication.
                foreach ($assignfileids as $assignfileid) {
                    $newfilerecord = new \stdClass();
                    $newfilerecord->contextid = $context->id;
                    $newfilerecord->component = 'mod_publication';
                    $newfilerecord->filearea = 'attachment';
                    $newfilerecord->itemid = $submission->userid;

                    try {
                        if ($fs->file_exists($newfilerecord->contextid,
                                             $newfilerecord->component,
                                             $newfilerecord->filearea,
                                             $newfilerecord->itemid,
                                             $assignfiles[$assignfileid]->get_filepath(),
                                             $assignfiles[$assignfileid]->get_filename())) {
                            \core\notification::info($OUTPUT->box('File existed, skipped creation!', 'generalbox'));
                            $newfile = $fs->get_file($newfilerecord->contextid,
                                                     $newfilerecord->component,
                                                     $newfilerecord->filearea,
                                                     $newfilerecord->itemid,
                                                     $assignfiles[$assignfileid]->get_filepath(),
                                                     $assignfiles[$assignfileid]->get_filename());
                        } else {
                            $newfile = $fs->create_file_from_storedfile($newfilerecord, $assignfiles[$assignfileid]);
                        }

                        $dataobject = new \stdClass();
                        $dataobject->publication = $curpub->id;
                        $dataobject->userid = $submission->userid;
                        $dataobject->timecreated = time();
                        $dataobject->fileid = $newfile->get_id();
                        $dataobject->filesourceid = $assignfileid;
                        $dataobject->filename = $newfile->get_filename();
                        $dataobject->contenthash = "666";
                        $dataobject->type = \PUBLICATION_MODE_IMPORT;

                        $DB->insert_record('publication_file', $dataobject);
                    } catch (Exception $e) {
                        // File could not be copied, maybe it does allready exist.
                        // Should not happen.
                        \core\notification::error($OUTPUT->box($e->message, 'generalbox'));
                    }
                }
            }
        }

        return true;
    }

}