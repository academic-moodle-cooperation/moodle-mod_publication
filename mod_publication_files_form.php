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

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/publication/locallib.php');

/**
 * Form for displaying and changing approval for publication files
 *
 * @package       mod_publication
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
        global $DB, $PAGE;

        $publication = &$this->_customdata['publication'];

        $mform = $this->_form;
        if (has_capability('mod/publication:upload', $publication->get_context())) {

            if ($publication->get_instance()->mode == PUBLICATION_MODE_UPLOAD) {
                $table = new \mod_publication\local\filestable\upload($publication);
                $headertext = get_string('myfiles', 'publication');
                if ($publication->get_instance()->obtainteacherapproval) {
                    $notice = get_string('notice_uploadrequireapproval', 'publication');
                } else {
                    $notice = get_string('notice_uploadnoapproval', 'publication');
                }
            } else if ($DB->get_field('assign', 'teamsubmission', array('id' => $publication->get_instance()->importfrom))) {
                $table = new \mod_publication\local\filestable\group($publication);
                $headertext = get_string('mygroupfiles', 'publication');
                if ($publication->get_instance()->obtainstudentapproval) {
                    if ($publication->get_instance()->groupapproval == PUBLICATION_APPROVAL_ALL) {
                        $notice = get_string('notice_groupimportrequireallapproval', 'publication');
                    } else {
                        $notice = get_string('notice_groupimportrequireoneapproval', 'publication');
                    }
                } else {
                    $notice = get_string('notice_importnoapproval', 'publication');
                }
            } else {
                $table = new \mod_publication\local\filestable\import($publication);
                $headertext = get_string('myfiles', 'publication');
                if ($publication->get_instance()->obtainstudentapproval) {
                    $notice = get_string('notice_importrequireapproval', 'publication');
                } else {
                    $notice = get_string('notice_importnoapproval', 'publication');
                }
            }

            $mform->addElement('header', 'myfiles', $headertext);
            $mform->setExpanded('myfiles');

            $PAGE->requires->js_call_amd('mod_publication/filesform', 'initializer', array());

            $noticehtml = html_writer::start_tag('div', array('class' => 'notice'));
            $noticehtml .= get_string('notice', 'publication') . ' ' . $notice;
            $noticehtml .= html_writer::end_tag('div');

            $mform->addElement('html', $noticehtml);
        }

        // Now we do all the table work and return 0 if there's no files to show!
        if ($table->init()) {
            $mform->addElement('html', \html_writer::table($table));
        } else {
            $mform->addElement('static', 'nofiles', '', get_string('nofiles', 'publication'));
        }

        // Display submit buttons if necessary.
        if (!empty($table) && $table->changepossible()) {
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

                if (empty($table)) { // This means, there are no files shown!
                    $label = get_string('add_uploads', 'publication');
                } else {
                    $label = get_string('edit_uploads', 'publication');
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
