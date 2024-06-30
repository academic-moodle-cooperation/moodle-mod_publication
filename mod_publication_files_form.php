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
 * Contains form class for approving publication files
 *
 * @package       mod_publication
 * @author        Philipp Hager
 * @author        Andreas Windbichler
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/publication/locallib.php');

/**
 * Form for displaying and changing approval for publication files
 *
 * @package       mod_publication
 * @author        Hannes Laiemr
 * @author        Philipp Hager
 * @author        Andreas Windbichler
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_publication_files_form extends moodleform {
    /**
     * Form definition method_exists
     */
    public function definition() {
        global $DB, $PAGE, $OUTPUT;

        $publication = &$this->_customdata['publication'];

        $mform = $this->_form;

        $mode = $publication->get_mode();

        $publicationinstance = $publication->get_instance();

        $noticestudentstringid = '';
        $noticeteacherid = '';
        $noticemode = '';

        if ($mode == PUBLICATION_MODE_FILEUPLOAD) {
            $noticemode = 'upload';
        } else {
            $noticemode = 'import';
        }

        if ($publicationinstance->obtainstudentapproval) {
            if ($mode == PUBLICATION_MODE_ASSIGN_TEAMSUBMISSION) {
                if ($publicationinstance->groupapproval == PUBLICATION_APPROVAL_ALL) {
                    $noticestudentstringid = 'all';
                } else {
                    $noticestudentstringid = 'one';
                }
                $noticemode = 'group';
            } else {
                $noticestudentstringid = 'studentrequired';
            }
        } else {
            $noticestudentstringid = 'studentnotrequired';
        }

        if ($publicationinstance->obtainteacherapproval) {
            $noticeteacherid = 'teacherrequired';
        } else {
            $noticeteacherid = 'teachernotrequired';
        }

        $stringid = 'notice_' . $noticemode . '_' . $noticestudentstringid . '_' . $noticeteacherid;

        if ($mode == PUBLICATION_MODE_ASSIGN_TEAMSUBMISSION) {
            $headertext = get_string('mygroupfiles', 'publication');
        } else {
            $headertext = get_string('myfiles', 'publication');
        }
        $notice = get_string($stringid, 'publication');

        if ($mode == PUBLICATION_MODE_ASSIGN_TEAMSUBMISSION) {
            $notice = get_string('notice_files_imported_group', 'publication') . ' ' . $notice;
        } else if ($mode == PUBLICATION_MODE_ASSIGN_IMPORT) {
            $notice = get_string('notice_files_imported', 'publication') . ' ' . $notice;
        }

        if ($mode != PUBLICATION_MODE_FILEUPLOAD) {
            $notice .= '<br />' . get_string('notice_changes_possible_in_original', 'publication');
        }

        $table = $publication->get_filestable();

        $mform->addElement('header', 'myfiles', $headertext);
        $mform->setExpanded('myfiles');

        $PAGE->requires->js_call_amd('mod_publication/filesform', 'initializer', []);
        $PAGE->requires->js_call_amd('mod_publication/alignrows', 'initializer', []);

        $noticehtml = html_writer::start_tag('div', ['class' => 'alert alert-info']);
        $noticehtml .= get_string('notice', 'publication') . ' ' . $notice;
        $noticehtml .= html_writer::end_tag('div');

        $mform->addElement('html', $noticehtml);

        // Now we do all the table work and return 0 if there's no files to show!
        $table->init();

        $mode = $publication->get_mode();
        $timeremaining = false;
        $publicationinstance = $publication->get_instance();
        if ($publicationinstance->duedate > 0) {
            $timeremainingdiff = $publicationinstance->duedate - time();
            if ($timeremainingdiff > 0) {
                $timeremaining = format_time($publicationinstance->duedate - time());
            } else {
                $timeremaining = get_string('overdue', 'publication');
            }
        }

        $approvalfromdate = $publicationinstance->approvalfromdate > 0 ? userdate($publicationinstance->approvalfromdate) : false;
        $approvaltodate = $publicationinstance->approvaltodate > 0 ? userdate($publicationinstance->approvaltodate) : false;
        $tablecontext = [
            'myfiles' => $table->data,
            'hasmyfiles' => !empty($table->data),
            'timeremaining' => $timeremaining,
            'lastmodified' => userdate($table->lastmodified),
            'approvalfromdate' => $approvalfromdate,
            'approvaltodate' => $approvaltodate,
            'assign' => $publication->get_importlink(),
            'myfilestitle' => $mode == PUBLICATION_MODE_ASSIGN_TEAMSUBMISSION ? get_string('mygroupfiles', 'publication') : get_string('myfiles', 'publication'),
        ];
        $myfilestable = $OUTPUT->render_from_template('mod_publication/myfiles', $tablecontext);
        $myfilestable = '<table class="table table-striped w-100">' . $myfilestable . '</table>';
        $mform->addElement('html', $myfilestable);

        // Display submit buttons if necessary.
        if (!empty($table) && $table->changepossible()) {
            if ($publication->is_open()) {
                $buttonarray = [];

                $onclick = 'return confirm("' . get_string('savestudentapprovalwarning', 'publication') . '")';

                $buttonarray[] = &$mform->createElement('submit', 'submitbutton',
                    get_string('savechanges'), ['onClick' => $onclick]);
                $buttonarray[] = &$mform->createElement('reset', 'resetbutton', get_string('revert'),
                    ['class' => 'btn btn-secondary']);

                $mform->addGroup($buttonarray, 'submitgrp', '', [' '], false);
            } else {
                $mform->addElement('static', 'approvaltimeover', '', get_string('approval_timeover', 'publication'));
            }
        }

        if ($publication->get_instance()->mode == PUBLICATION_MODE_UPLOAD
            && has_capability('mod/publication:upload', $publication->get_context())) {
            if ($publication->is_open()) {
                $buttonarray = [];

                if (empty($table)) { // This means, there are no files shown!
                    $label = get_string('add_uploads', 'publication');
                } else {
                    $label = get_string('edit_uploads', 'publication');
                }

                $buttonarray[] = &$mform->createElement('submit', 'gotoupload', $label);
                $mform->addGroup($buttonarray, 'uploadgrp', '', [' '], false);
            } else if (has_capability('mod/publication:upload', $publication->get_context())) {
                $mform->addElement('static', 'edittimeover', '', get_string('edit_timeover', 'publication'));
            }
        }

        $mform->addElement('hidden', 'id', $publication->get_coursemodule()->id);
        $mform->setType('id', PARAM_INT);

        $mform->disable_form_change_checker();
    }
}
