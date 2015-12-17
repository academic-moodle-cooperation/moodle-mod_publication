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
 * mod_publication_grantextension_form.php
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
 * Form for granting extensions
 *
 * @package       mod_publication
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Philipp Hager (office@phager.at)
 * @author        Andreas Windbichler
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_publication_grantextension_form extends moodleform {
    /** @var object publication instance */
    private $instance;

    /**
     * Form definition method
     */
    public function definition() {
        global $CFG, $OUTPUT, $DB, $USER;

        $publication = &$this->_customdata['publication'];
        $this->instance = $publication->get_instance();
        $userids = &$this->_customdata['userids'];

        $mform = $this->_form;

        if ($publication->get_instance()->allowsubmissionsfromdate) {
            $mform->addElement('static', 'fromdate',
                    get_string('allowsubmissionsfromdate', 'publication'),
                    userdate($publication->get_instance()->allowsubmissionsfromdate));
        }

        if ($publication->get_instance()->duedate) {
            $mform->addElement('static', 'duedate',
                    get_string('duedate', 'publication'), userdate($publication->get_instance()->duedate));
            $finaldate = $publication->get_instance()->duedate;
        }

        $mform->addElement('date_time_selector', 'extensionduedate',
                get_string('extensionduedate', 'publication'), array('optional' => true));
        $mform->setDefault('extensionduedate', $finaldate);

        if (count($userids) == 1) {
            $extensionduedate = $publication->user_extensionduedate($userids[0]);
            if ($extensionduedate) {
                $mform->setDefault('extensionduedate', $extensionduedate);
            }
        }

        $mform->addElement('hidden', 'id', $publication->get_coursemodule()->id);
        $mform->setType('id', PARAM_INT);

        foreach ($userids as $idx => $userid) {
            $mform->addElement('hidden', 'userids[' . $idx . ']', $userid);
            $mform->setType('userids[' . $idx . ']', PARAM_INT);
        }

        $this->add_action_buttons(true, get_string('save_changes', 'publication'));
    }

    /**
     * Perform validation on the extension form
     *
     * @param array $data
     * @param array $files
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if ($this->instance->duedate && $data['extensionduedate']) {
            if ($this->instance->duedate > $data['extensionduedate']) {
                $errors['extensionduedate'] = get_string('extensionnotafterduedate', 'publication');
            }
        }
        if ($this->instance->allowsubmissionsfromdate && $data['extensionduedate']) {
            if ($this->instance->allowsubmissionsfromdate > $data['extensionduedate']) {
                $errors['extensionduedate'] = get_string('extensionnotafterfromdate', 'publication');
            }
        }

        return $errors;
    }
}