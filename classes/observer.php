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
        $onlinetexts = $DB->get_records('assignsubmission_onlinetext', array('assignment' => $assignid,
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
                // We look for regular imported files here!
                $conditions['type'] = PUBLICATION_MODE_IMPORT;

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

            if (!empty($onlinetexts)) {
                require_once($CFG->dirroot . '/mod/assign/locallib.php');
                $assignment = new \assign($assigncontext, $assigncm, $assign->get_course());
                $onlinetext = $assignment->get_submission_plugin_by_type('onlinetext');
            }
            // And now the same for online texts!
            foreach ($onlinetexts as $record) {

                // First we fetch the ressource files (embedded files in text!)
                $fsfiles = $fs->get_area_files($assigncontext->id,
                                               'assignsubmission_onlinetext',
                                               ASSIGNSUBMISSION_ONLINETEXT_FILEAREA,
                                               $submission->id,
                                               'timemodified',
                                               false);
                $assignfiles = array();
                foreach ($fsfiles as $file) {
                    $filerecord = new \stdClass();
                    $filerecord->contextid = $context->id;
                    $filerecord->component = 'mod_publication';
                    $filerecord->filearea = 'attachment';
                    $filerecord->itemid = $submission->userid;
                    $filerecord->filepath = '/ressources/';
                    $filerecord->filename = $file->get_filename();
                    $pathnamehash = $fs->get_pathname_hash($filerecord->contextid, $filerecord->component, $filerecord->filearea,
                                                           $filerecord->itemid, $filerecord->filepath, $filerecord->filename);

                    if ($fs->file_exists_by_hash($pathnamehash)) {
                        $otherfile = $fs->get_file_by_hash($pathnamehash);
                        if ($file->get_contenthash() != $otherfile->get_contenthash()) {
                            // We have to update the file!
                            $otherfile->delete();
                            $fs->create_file_from_storedfile($filerecord, $file);
                        }
                    } else {
                        // We have to add the file!
                        $fs->create_file_from_storedfile($filerecord, $file);
                    }
                }

                // Now we delete old ressource-files, which are no longer present!
                $ressources = $fs->get_directory_files($context->id,
                                                       'mod_publication',
                                                       'attachment',
                                                       $submission->userid,
                                                       '/ressources/',
                                                       true,
                                                       false);
                foreach ($ressources as $ressource) {
                    $pathnamehash = $fs->get_pathname_hash($assigncontext->id, 'assignsubmission_onlinetext',
                                                           ASSIGNSUBMISSION_ONLINETEXT_FILEAREA, $submission->id, '/',
                                                           $ressource->get_filename());
                    if (!$fs->file_exists_by_hash($pathnamehash)) {
                        $ressource->delete();
                    }
                }

                /* Here we convert the pluginfile urls to relative urls for the exported html-file
                 * (the ressources have to be included in the download!) */
                $formattedtext = str_replace('@@PLUGINFILE@@/', './ressources/', $record->onlinetext);
                $formattedtext = format_text($formattedtext, $record->onlineformat, array('context' => $assigncontext));

                $head = '<head><meta charset="UTF-8"></head>';
                $submissioncontent = '<!DOCTYPE html><html>' . $head . '<body>'. $formattedtext . '</body></html>';

                $filename = get_string('onlinetextfilename', 'assignsubmission_onlinetext');

                // Does the file exist... let's check it!
                $pathhash = $fs->get_pathname_hash($context->id, 'mod_publication', 'attachment', $submission->userid, '/', $filename);

                $conditions = array('publication' => $curpub->id,
                                    'userid'      => $submission->userid,
                                    'type'        => PUBLICATION_MODE_ONLINETEXT);
                $pubfile = $DB->get_record('publication_file', $conditions, '*', IGNORE_MISSING);

                $createnew = false;
                if ($fs->file_exists_by_hash($pathhash)) {
                    $file = $fs->get_file_by_hash($pathhash);
                    if (empty($formattedtext)) {
                        // The onlinetext was empty, delete the file!
                        $DB->delete_records('publication_file', $conditions);
                        $file->delete();
                    } else if (($file->get_timemodified() < $submission->timemodified)
                            && ($file->get_contenthash() != sha1($submissioncontent))) {
                        /* If the submission has been modified after the file,             *
                         * we check for different content-hashes to see if it was changed! */
                        $createnew = true;
                        if ($file->get_id() == $pubfile->fileid) {
                            // Everything's alright, we can delete the old file!
                            $file->delete();
                        } else {
                            // Something unexcpected happened!
                            throw new \coding_exception('Mismatching fileids (pubfile with id '.$pubfile->fileid.' and stored file '.
                                                       $file->get_id().'!');
                        }
                    }
                } else if (!empty($formattedtext)) {
                    // There exists no such file, so we create one!
                    $createnew = true;
                }

                if ($createnew === true) {
                    // We gotta create a new one!
                    $newfilerecord = new \stdClass();
                    $newfilerecord->contextid = $context->id;
                    $newfilerecord->component = 'mod_publication';
                    $newfilerecord->filearea = 'attachment';
                    $newfilerecord->itemid = $submission->userid;
                    $newfilerecord->filename = $filename;
                    $newfilerecord->filepath = '/';
                    $newfile = $fs->create_file_from_string($newfilerecord, $submissioncontent);
                    if (empty($pubfile)) {
                        $pubfile = new \stdClass();
                        $pubfile->userid = $submission->userid;
                        $pubfile->type = PUBLICATION_MODE_ONLINETEXT;
                        $pubfile->publication = $curpub->id;
                    }
                    // The file has been updated, so we set the new time.
                    $pubfile->timecreated = time();
                    $pubfile->fileid = $newfile->get_id();
                    $pubfile->filename = $filename;
                    $pubfile->contenthash = $newfile->get_contenthash();
                    if (!empty($pubfile->id)) {
                        $DB->update_record('publication_file', $pubfile);
                    } else {
                        $DB->insert_record('publication_file', $pubfile);
                    }
                }
            }
        }

        return true;
    }

}