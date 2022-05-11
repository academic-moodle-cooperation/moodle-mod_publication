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
 * Instance settings form.
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
 * Form for creating and editing mod_publication instances
 *
 * @package       mod_publication
 * @author        Philipp Hager
 * @author        Andreas Windbichler
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_publication_mod_form extends moodleform_mod {

    /**
     * Define this form - called by the parent constructor
     */
    public function definition() {
        global $DB, $CFG, $COURSE;

        $mform = $this->_form;
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Name.
        $mform->addElement('text', 'name', get_string('name', 'publication'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');

        // Adding the standard "intro" and "introformat" fields!
        $this->standard_intro_elements();

        // Publication specific elements.
        $mform->addElement('header', 'publication', get_string('modulename', 'publication'));
        $mform->setExpanded('publication');

        if (isset($this->current->id) && $this->current->id != "") {
            $filecount = $DB->count_records('publication_file', ['publication' => $this->current->id]);
        } else {
            $filecount = 0;
        }

        $disabled = [];
        if ($filecount > 0) {
            $disabled['disabled'] = 'disabled';
        }

        $modearray = [];
        $modearray[] =& $mform->createElement('radio', 'mode', '', get_string('modeupload', 'publication'),
                PUBLICATION_MODE_UPLOAD, $disabled);
        $modearray[] =& $mform->createElement('radio', 'mode', '', get_string('modeimport', 'publication'),
                PUBLICATION_MODE_IMPORT, $disabled);
        $mform->addGroup($modearray, 'modegrp', get_string('mode', 'publication'), [' '], false);
        $mform->addHelpButton('modegrp', 'mode', 'publication');
        if ($filecount === 0) {
            $mform->addRule('modegrp', null, 'required', null, 'client');
        }

        // Publication mode import specific elements.
        $choices = [];
        $choices[-1] = get_string('choose', 'publication');
        $assigninstances = $DB->get_records('assign', ['course' => $COURSE->id]);
        $module = $DB->get_record('modules', ['name' => 'assign']);
        $select = $mform->createElement('select', 'importfrom', get_string('assignment', 'publication'), $choices, $disabled);
        $notteamassigns = [-1];
        foreach ($assigninstances as $assigninstance) {
            $cm = $DB->get_record('course_modules', ['module' => $module->id, 'instance' => $assigninstance->id]);
            if ($cm->deletioninprogress == 1) {
                continue;
            }
            if (!$assigninstance->teamsubmission) {
                $notteamassigns[] = $assigninstance->id;
            }
            $attributes = ['data-teamsubmission' => $assigninstance->teamsubmission];
            $select->addOption($assigninstance->name, $assigninstance->id, $attributes);
        }
        $mform->addElement($select);
        $mform->addHelpButton('importfrom', 'assignment', 'publication');
        $mform->hideIf('importfrom', 'mode', 'neq', PUBLICATION_MODE_IMPORT);

        $mform->addElement('advcheckbox', 'autoimport', get_string('autoimport', 'publication'));
        $mform->setDefault('autoimport', get_config('publication', 'autoimport'));
        $mform->addHelpButton('autoimport', 'autoimport', 'publication');
        $mform->hideIf('autoimport', 'mode', 'neq', PUBLICATION_MODE_IMPORT);

        $attributes = [];
        if (isset($this->current->id) && isset($this->current->obtainstudentapproval)) {
            if ($this->current->obtainstudentapproval) {
                $message = get_string('warning_changefromobtainstudentapproval', 'publication');
                $showwhen = "0";
            } else {
                $message = get_string('warning_changetoobtainstudentapproval', 'publication');
                $showwhen = "1";
            }

            $message = trim(preg_replace('/\s+/', ' ', $message));
            $message = str_replace('\'', '\\\'', $message);
            $attributes['onChange'] = "if (this.value==" . $showwhen . ") {alert('" . $message . "')}";
        }

        $mform->addElement('selectyesno', 'obtainstudentapproval', get_string('obtainstudentapproval', 'publication'), $attributes);
        $mform->setDefault('obtainstudentapproval', get_config('publication', 'obtainstudentapproval'));
        $mform->addHelpButton('obtainstudentapproval', 'obtainstudentapproval', 'publication');
        $mform->hideIf('obtainstudentapproval', 'mode', 'neq', PUBLICATION_MODE_IMPORT);

        $radioarray = [];
        $radioarray[] = $mform->createElement('radio', 'groupapproval', '', get_string('groupapprovalmode_all', 'publication'),
                PUBLICATION_APPROVAL_ALL, $attributes);
        $radioarray[] = $mform->createElement('radio', 'groupapproval', '', get_string('groupapprovalmode_single', 'publication'),
                PUBLICATION_APPROVAL_SINGLE, $attributes);
        $mform->addGroup($radioarray, 'groupapprovalarray', get_string('groupapprovalmode', 'publication'),
                [html_writer::empty_tag('br')], false);
        $mform->addHelpButton('groupapprovalarray', 'groupapprovalmode', 'publication');
        $mform->setDefault('groupapproval', PUBLICATION_APPROVAL_ALL);
        $mform->hideIf('groupapprovalarray', 'mode', 'neq', PUBLICATION_MODE_IMPORT);
        foreach ($notteamassigns as $cur) {
            $mform->hideIf('groupapprovalarray', 'importfrom', 'eq', $cur);
        }

        // Publication mode upload specific elements.
        $maxfiles = [];
        for ($i = 1; $i <= 100 || $i <= get_config('publication', 'maxfiles'); $i++) {
            $maxfiles[$i] = $i;
        }

        $mform->addElement('select', 'maxfiles', get_string('maxfiles', 'publication'), $maxfiles);
        $mform->setDefault('maxfiles', get_config('publication', 'maxfiles'));
        $mform->hideIf('maxfiles', 'mode', 'neq', PUBLICATION_MODE_UPLOAD);

        $choices = get_max_upload_sizes($CFG->maxbytes, $COURSE->maxbytes);
        $choices[0] = get_string('courseuploadlimit', 'publication') . ' (' . display_size($COURSE->maxbytes) . ')';
        $mform->addElement('select', 'maxbytes', get_string('maxbytes', 'publication'), $choices);
        $mform->setDefault('maxbytes', get_config('publication', 'maxbytes'));
        $mform->hideIf('maxbytes', 'mode', 'neq', PUBLICATION_MODE_UPLOAD);

        $mform->addElement('filetypes', 'allowedfiletypes', get_string('allowedfiletypes', 'publication'));
        $mform->addHelpButton('allowedfiletypes', 'allowedfiletypes', 'publication');
        $mform->hideIf('allowedfiletypes', 'mode', 'neq', PUBLICATION_MODE_UPLOAD);

        $attributes = [];
        if (isset($this->current->id) && isset($this->current->obtainteacherapproval)) {
            if (!$this->current->obtainteacherapproval) {
                $message = get_string('warning_changefromobtainteacherapproval', 'publication');
                $showwhen = "1";
            } else {
                $message = get_string('warning_changetoobtainteacherapproval', 'publication');
                $showwhen = "0";
            }

            $message = trim(preg_replace('/\s+/', ' ', $message));
            $attributes['onChange'] = "if (this.value==" . $showwhen . ") {alert('" . $message . "')}";
        }

        $mform->addElement('selectyesno', 'obtainteacherapproval',
                get_string('obtainteacherapproval', 'publication'), $attributes);
        $mform->setDefault('obtainteacherapproval', get_config('publication', 'obtainteacherapproval'));
        $mform->addHelpButton('obtainteacherapproval', 'obtainteacherapproval', 'publication');
        $mform->hideIf('obtainteacherapproval', 'mode', 'neq', PUBLICATION_MODE_UPLOAD);

        // Availability.
        $mform->addElement('header', 'availability', get_string('availability', 'publication'));
        $mform->setExpanded('availability', true);

        $name = get_string('allowsubmissionsfromdate', 'publication');
        $options = ['optional' => true];
        $mform->addElement('date_time_selector', 'allowsubmissionsfromdate', $name, $options);
        $mform->addHelpButton('allowsubmissionsfromdate', 'allowsubmissionsfromdateh', 'publication');
        $mform->setDefault('allowsubmissionsfromdate', time());

        $name = get_string('duedate', 'publication');
        $mform->addElement('date_time_selector', 'duedate', $name, ['optional' => true]);

        $mform->setDefault('duedate', time() + 7 * 24 * 3600);

        $mform->addElement('hidden', 'cutoffdate', false);
        $mform->setType('cutoffdate', PARAM_BOOL);

        $mform->addElement('hidden', 'alwaysshowdescription', true);
        $mform->setType('alwaysshowdescription', PARAM_BOOL);

        $mform->addElement('header', 'notifications', get_string('notifications', 'publication'));

        $name = get_string('notifyteacher', 'publication');
        $mform->addElement('selectyesno', 'notifyteacher', $name);
        $mform->addHelpButton('notifyteacher', 'notifyteacher', 'publication');
        $mform->setDefault('notifyteacher', 0);

        $name = get_string('notifystudents', 'publication');
        $mform->addElement('selectyesno', 'notifystudents', $name);
        $mform->addHelpButton('notifystudents', 'notifystudents', 'publication');
        $mform->setDefault('notifystudents', 0);
        // Standard coursemodule elements.
        $this->standard_coursemodule_elements();

        // Buttons.
        $this->add_action_buttons();
    }

    /**
     * Perform minimal validation on the settings form
     *
     * @param array $data
     * @param array $files
     * @return string[] errors
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if ($data['allowsubmissionsfromdate'] && $data['duedate']) {
            if ($data['allowsubmissionsfromdate'] > $data['duedate']) {
                $errors['duedate'] = get_string('duedatevalidation', 'publication');
            }
        }
        if ($data['duedate'] && $data['cutoffdate']) {
            if ($data['duedate'] > $data['cutoffdate']) {
                $errors['cutoffdate'] = get_string('cutoffdatevalidation', 'publication');
            }
        }
        if ($data['allowsubmissionsfromdate'] && $data['cutoffdate']) {
            if ($data['allowsubmissionsfromdate'] > $data['cutoffdate']) {
                $errors['cutoffdate'] = get_string('cutoffdatefromdatevalidation', 'publication');
            }
        }

        if ($data['mode'] == PUBLICATION_MODE_IMPORT) {
            if ($data['importfrom'] == "0" || $data['importfrom'] == '-1') {
                $errors['importfrom'] = get_string('importfrom_err', 'publication');
            }
        }

        return $errors;
    }
}
