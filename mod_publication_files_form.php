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
 * mod_publication_files_form.php
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

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/publication/locallib.php');

/**
 * Form for displaying and changing approval for publication files
 *
 * @package       mod_publication
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Philipp Hager (office@phager.at)
 * @author        Andreas Windbichler
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_publication_files_form extends moodleform {
    /**
     * Form definition method_exists
     */
    public function definition() {
        global $CFG, $OUTPUT, $DB, $USER, $PAGE;

        $publication = &$this->_customdata['publication'];
        $sid = &$this->_customdata['sid'];
        $filearea = &$this->_customdata['filearea'];

        $mform = $this->_form;
        if (has_capability('mod/publication:upload', $publication->get_context())) {
            $mform->addElement('header', 'myfiles', get_string('myfiles', 'publication'));
            $mform->setExpanded('myfiles');

            $PAGE->requires->js_call_amd('mod_publication/filesform', 'initializer', array());

            if ($publication->get_instance()->mode == PUBLICATION_MODE_UPLOAD) {
                if ($publication->get_instance()->obtainteacherapproval) {
                    $notice = get_string('notice_uploadrequireapproval', 'publication');
                } else {
                    $notice = get_string('notice_uploadnoapproval', 'publication');
                }
            } else {
                if ($publication->get_instance()->obtainstudentapproval) {
                    $notice = get_string('notice_importrequireapproval', 'publication');
                } else {
                    $notice = get_string('notice_importnoapproval', 'publication');
                }
            }

            $noticehtml = html_writer::start_tag('div', array('class' => 'notice'));
            $noticehtml .= get_string('notice', 'publication') . ' ' . $notice;
            $noticehtml .= html_writer::end_tag('div');

            $mform->addElement('html', $noticehtml);
        }

        require_once($CFG->libdir.'/tablelib.php');
        $table = new html_table();

        $tablecolumns = array();
        $tableheaders = array();

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

        $options = array();
        $options[2] = get_string('student_approve', 'publication');
        $options[1] = get_string('student_reject', 'publication');

        $conditions = array();
        $conditions['publication'] = $publication->get_instance()->id;
        $conditions['userid'] = $USER->id;

        $changepossible = false;

        $valid = $OUTPUT->pix_icon('i/valid',
                get_string('student_approved', 'publication'));
        $questionmark = $OUTPUT->pix_icon('questionmark',
                get_string('student_pending', 'publication'),
                'mod_publication');
        $invalid = $OUTPUT->pix_icon('i/invalid',
                get_string('student_rejected', 'publication'));

        foreach ($files as $file) {
            $conditions['fileid'] = $file->get_id();
            $studentapproval = $DB->get_field('publication_file', 'studentapproval', $conditions);
            $teacherapproval = $DB->get_field('publication_file', 'teacherapproval', $conditions);

            $studentapproval = (!is_null($studentapproval)) ? $studentapproval + 1 : null;

            $data = array();
            $data[] = $OUTPUT->pix_icon(file_file_icon($file), get_mimetype_description($file));

            $dlurl = new moodle_url('/mod/publication/view.php',
                    array('id' => $publication->get_coursemodule()->id, 'download' => $file->get_id()));
            $data[] = html_writer::link($dlurl, $file->get_filename());

            if ($publication->get_instance()->mode == PUBLICATION_MODE_IMPORT) {
                if ($teacherapproval && $publication->get_instance()->obtainstudentapproval) {
                    if ($publication->is_open() && $studentapproval == 0) {
                        $changepossible = true;
                        $data[] = html_writer::select($options, 'studentapproval[' . $file->get_id()  . ']', $studentapproval);
                    } else {
                        switch($studentapproval) {
                            case 2:
                                $data[] = get_string('student_approved', 'publication');
                                break;
                            case 1:
                                $data[] = get_string('student_rejected', 'publication');
                                break;
                            default:
                                $data[] = get_string('student_pending', 'publication');
                        }
                    }
                } else {
                    switch($teacherapproval) {
                        case 1:
                            $data[] = get_string('teacher_approved', 'publication');
                            break;
                        default:
                            $data[] = get_string('student_pending', 'publication');
                    }
                }
            }

            if ($publication->get_instance()->mode == PUBLICATION_MODE_UPLOAD) {

                if ($publication->get_instance()->obtainteacherapproval) {
                    // Teacher has to approve: show all status.
                    if (is_null($teacherapproval)) {
                        $data[] = get_string('hidden', 'publication') .' ('. get_string('teacher_pending', 'publication') . ')';
                    } else if ($teacherapproval == 1) {
                        $data[] = get_string('visible', 'publication');
                    } else {
                        $data[] = get_string('hidden', 'publication') .' ('. get_string('teacher_rejected', 'publication') . ')';
                    }
                } else {
                    // Teacher doenst have to approve: only show when rejected.
                    if (is_null($teacherapproval)) {
                        $data[] = get_string('visible', 'publication');
                    } else if ($teacherapproval == 1) {
                        $data[] = get_string('visible', 'publication');
                    } else {
                        $data[] = get_string('hidden', 'publication') .' ('. get_string('teacher_rejected', 'publication') .')';
                    }
                }
            }

            $table->data[] = $data;
        }

        if (count($files) == 0 && has_capability('mod/publication:upload', $publication->get_context())) {
            $mform->addElement('static', 'nofiles', '', get_string('nofiles', 'publication'));
        }

        $tablehtml = html_writer::table($table);

        $mform->addElement('html', $tablehtml);

        // Display submit buttons if necessary.
        if ($changepossible) {
            if ($publication->is_open()) {
                $buttonarray = array();

                $onclick = 'return confirm("' . get_string('savestudentapprovalwarning', 'publication') . '")';

                $buttonarray[] = &$mform->createElement('submit', 'submitbutton',
                        get_string('savechanges'), array('onClick' => $onclick));
                $buttonarray[] = &$mform->createElement('reset', 'resetbutton', get_string('revert'));

                $mform->addGroup($buttonarray, 'submitgrp', '', array(' '), false);
            } else {
                $mform->addElement('static', 'approvaltimeover', '', get_string('approval_timeover', 'publication'));
            }
        }

        if ($publication->get_instance()->mode == PUBLICATION_MODE_UPLOAD
            && has_capability('mod/publication:upload', $publication->get_context())) {
            if ($publication->is_open()) {
                $buttonarray = array();

                $label = get_string('edit_uploads', 'publication');

                if (count($files) == 0) {
                    $label = get_string('add_uploads', 'publication');
                }

                $buttonarray[] = &$mform->createElement('submit', 'gotoupload', $label);
                $mform->addGroup($buttonarray, 'uploadgrp', '', array(' '), false);
            } else {
                $mform->addElement('static', 'edittimeover', '', get_string('edit_timeover', 'publication'));
            }
        }

        $mform->addElement('hidden', 'id', $publication->get_coursemodule()->id);
        $mform->setType('id', PARAM_INT);
    }
}
