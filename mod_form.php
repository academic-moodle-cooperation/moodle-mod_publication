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
        global $DB, $CFG, $COURSE, $PAGE;

        $mform = $this->_form;
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Name.
        $mform->addElement('text', 'name', get_string('name', 'publication'), ['size' => '64']);
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');

        // Adding the standard "intro" and "introformat" fields!
        $this->standard_intro_elements();

        // Publication specific elements.
        $mform->addElement('header', 'submissionsettings', get_string('submissionsettings', 'publication'));
        $mform->setExpanded('submissionsettings');

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
        $teamassigns = [];
        foreach ($assigninstances as $assigninstance) {
            $cm = $DB->get_record('course_modules', ['module' => $module->id, 'instance' => $assigninstance->id]);
            if ($cm->deletioninprogress == 1) {
                continue;
            }
            if (!$assigninstance->teamsubmission) {
                $notteamassigns[] = $assigninstance->id;
            } else {
                $teamassigns[] = $assigninstance->id;
            }
            $attributes = ['data-teamsubmission' => $assigninstance->teamsubmission];
            $select->addOption($assigninstance->name, $assigninstance->id, $attributes);
        }
        $mform->addElement($select);
        $mform->addHelpButton('importfrom', 'assignment', 'publication');
        $mform->hideIf('importfrom', 'mode', 'neq', PUBLICATION_MODE_IMPORT);

        // Publication mode upload specific elements.
        $maxfiles = [];
        for ($i = 1; $i <= 100 || $i <= get_config('publication', 'maxfiles'); $i++) {
            $maxfiles[$i] = $i;
        }

        $mform->addElement('select', 'maxfiles', get_string('maxfiles', 'publication'), $maxfiles);
        $mform->setDefault('maxfiles', get_config('publication', 'maxfiles'));
        $mform->addHelpButton('maxfiles', 'maxfiles', 'publication');
        $mform->hideIf('maxfiles', 'mode', 'neq', PUBLICATION_MODE_UPLOAD);

        $choices = get_max_upload_sizes($CFG->maxbytes, $COURSE->maxbytes);
        $choices[0] = get_string('courseuploadlimit', 'publication') . ' (' . display_size($COURSE->maxbytes) . ')';
        $mform->addElement('select', 'maxbytes', get_string('maxbytes', 'publication'), $choices);
        $mform->setDefault('maxbytes', get_config('publication', 'maxbytes'));
        $mform->addHelpButton('maxbytes', 'maxbytes', 'publication');
        $mform->hideIf('maxbytes', 'mode', 'neq', PUBLICATION_MODE_UPLOAD);

        $mform->addElement('filetypes', 'allowedfiletypes', get_string('allowedfiletypes', 'publication'));
        $mform->addHelpButton('allowedfiletypes', 'allowedfiletypes', 'publication');
        $mform->hideIf('allowedfiletypes', 'mode', 'neq', PUBLICATION_MODE_UPLOAD);


        $name = get_string('allowsubmissionsfromdate', 'publication');
        $options = ['optional' => true];
        $mform->addElement('date_time_selector', 'allowsubmissionsfromdate', $name, $options);
        $mform->addHelpButton('allowsubmissionsfromdate', 'allowsubmissionsfromdate', 'publication');
        $mform->setDefault('allowsubmissionsfromdate', time());
        $mform->hideIf('allowsubmissionsfromdate', 'mode', 'neq', PUBLICATION_MODE_UPLOAD);

        $name = get_string('duedate', 'publication');
        $mform->addElement('date_time_selector', 'duedate', $name, ['optional' => true]);
        $mform->addHelpButton('duedate', 'duedate', 'publication');
        $mform->setDefault('duedate', time() + 7 * 24 * 3600);
        $mform->hideIf('duedate', 'mode', 'neq', PUBLICATION_MODE_UPLOAD);



        $mform->addElement('hidden', 'cutoffdate', false);
        $mform->setType('cutoffdate', PARAM_BOOL);

        // Approval settings start.
        $mform->addElement('header', 'approvalsettings', get_string('approvalsettings', 'publication'));
        $mform->setExpanded('approvalsettings', true);


        // Teacher approval.
        $attributes = [];
        $options = [
            '0' => get_string('obtainapproval_automatic', 'publication'),
            '1' => get_string('obtainapproval_required', 'publication'),
        ];

        $mform->addElement('select', 'obtainteacherapproval',
            get_string('obtainteacherapproval', 'publication'), $options, $attributes);
        $mform->setDefault('obtainteacherapproval', get_config('publication', 'obtainteacherapproval'));
        $mform->addHelpButton('obtainteacherapproval', 'obtainteacherapproval', 'publication');



        // Student approval.
        $attributes = [];
        $options = [
            '0' => get_string('obtainapproval_automatic', 'publication'),
            '1' => get_string('obtainapproval_required', 'publication'),
        ];

        $mform->addElement('select', 'obtainstudentapproval', get_string('obtainstudentapproval', 'publication'), $options, $attributes);
        $mform->setDefault('obtainstudentapproval', get_config('publication', 'obtainstudentapproval'));
        $mform->addHelpButton('obtainstudentapproval', 'obtainstudentapproval', 'publication');
        $mform->hideIf('obtainstudentapproval', 'importfrom', 'in', $teamassigns);


        // Group approval.
        $attributes = [];
        $options = [
            PUBLICATION_APPROVAL_GROUPAUTOMATIC => get_string('obtainapproval_automatic', 'publication'),
            PUBLICATION_APPROVAL_SINGLE => get_string('obtaingroupapproval_single', 'publication'),
            PUBLICATION_APPROVAL_ALL => get_string('obtaingroupapproval_all', 'publication'),
        ];

        $mform->addElement('select', 'obtaingroupapproval', get_string('obtaingroupapproval', 'publication'), $options, $attributes);
        $mform->setDefault('obtaingroupapproval',  get_config('publication', 'obtaingroupapproval'));
        $mform->addHelpButton('obtaingroupapproval', 'obtaingroupapproval', 'publication');
        $mform->hideIf('obtaingroupapproval', 'importfrom', 'in', $notteamassigns);

        /*foreach ($notteamassigns as $cur) {
            $mform->hideIf('obtaingroupapproval', 'importfrom', 'eq', $cur);
        }*/


       // $mform->hideIf('obtainstudentapproval', 'mode', 'neq', PUBLICATION_MODE_IMPORT);


       /* $radioarray = [];
        $radioarray[] = $mform->createElement('radio', 'groupapproval', '', get_string('obtaingroupapproval_all', 'publication'),
            PUBLICATION_APPROVAL_ALL, $attributes);
        $radioarray[] = $mform->createElement('radio', 'groupapproval', '', get_string('obtaingroupapproval_single', 'publication'),
            PUBLICATION_APPROVAL_SINGLE, $attributes);
        $mform->addGroup($radioarray, 'groupapprovalarray', get_string('obtaingroupapproval', 'publication'),
            [html_writer::empty_tag('br')], false);
        $mform->addHelpButton('groupapprovalarray', 'obtaingroupapproval', 'publication');
        $mform->setDefault('groupapproval', PUBLICATION_APPROVAL_ALL);
        $mform->hideIf('groupapprovalarray', 'mode', 'neq', PUBLICATION_MODE_IMPORT);
        $mform->hideIf('groupapprovalarray', 'obtainstudentapproval', 'eq', 0);
        foreach ($notteamassigns as $cur) {
            $mform->hideIf('groupapprovalarray', 'importfrom', 'eq', $cur);
        }


      //  $mform->hideIf('obtainteacherapproval', 'mode', 'neq', PUBLICATION_MODE_UPLOAD);

     /*   $infostrings = [
            'notice_obtainapproval_import_both',
            'notice_obtainapproval_import_studentonly',
            'notice_obtainapproval_upload_teacher',
            'notice_obtainapproval_upload_automatic',
        ];
        foreach ($infostrings as $infostring) {
            $infohtml = html_writer::start_tag('div', ['class' => 'alert alert-info']);
            $infohtml .= get_string($infostring, 'publication');
            $infohtml .= html_writer::end_tag('div');
            $infogroupimport =& $mform->createElement('static', $infostring, '', $infohtml);
            $mform->addGroup([$infogroupimport], $infostring . '_group', '', ' ', false);
        }
        $mform->hideIf('notice_obtainapproval_import_both_group', 'mode', 'neq', PUBLICATION_MODE_IMPORT);
        $mform->hideIf('notice_obtainapproval_import_both_group', 'obtainstudentapproval', 'neq', '1');
        $mform->hideIf('notice_obtainapproval_import_studentonly_group', 'mode', 'neq', PUBLICATION_MODE_IMPORT);
        $mform->hideIf('notice_obtainapproval_import_studentonly_group', 'obtainstudentapproval', 'neq', '0');

        $mform->hideIf('notice_obtainapproval_upload_teacher_group', 'obtainteacherapproval', 'neq', '0');
        $mform->hideIf('notice_obtainapproval_upload_teacher_group', 'mode', 'neq', PUBLICATION_MODE_UPLOAD);
        $mform->hideIf('notice_obtainapproval_upload_automatic_group', 'obtainteacherapproval', 'neq', '1');
        $mform->hideIf('notice_obtainapproval_upload_automatic_group', 'mode', 'neq', PUBLICATION_MODE_UPLOAD);*/



        $mform->addElement('date_time_selector', 'approvalfromdate', get_string('approvalfromdate', 'publication'), ['optional' => true]);
        $mform->addHelpButton('approvalfromdate', 'approvalfromdate', 'publication');
        $mform->setDefault('approvalfromdate', time());

        $mform->addElement('date_time_selector', 'approvaltodate', get_string('approvaltodate', 'publication'), ['optional' => true]);
        $mform->addHelpButton('approvaltodate', 'approvaltodate', 'publication');
        $mform->setDefault('approvaltodate', time() + 7 * 24 * 3600);
        // Approval code end.

        $mform->addElement('hidden', 'alwaysshowdescription', true);
        $mform->setType('alwaysshowdescription', PARAM_BOOL);

        $mform->addElement('header', 'notifications', get_string('notifications', 'publication'));

        $options = [
            PUBLICATION_NOTIFY_NONE => get_string('notify:setting:0', 'publication'),
            PUBLICATION_NOTIFY_TEACHER => get_string('notify:setting:1', 'publication'),
            PUBLICATION_NOTIFY_STUDENT => get_string('notify:setting:2', 'publication'),
            PUBLICATION_NOTIFY_ALL => get_string('notify:setting:3', 'publication'),
        ];

        $mform->addElement('select', 'notifystatuschange', get_string('notify:statuschange', 'publication'), $options);
        $mform->addHelpButton('notifystatuschange', 'notify:statuschange', 'publication');
        $mform->setDefault('notifystatuschange', get_config('publication', 'notifystatuschange'));

        $mform->addElement('select', 'notifyfilechange', get_string('notify:filechange', 'publication'), $options);
        $mform->addHelpButton('notifyfilechange', 'notify:filechange', 'publication');
        $mform->setDefault('notifyfilechange', get_config('publication', 'notifyfilechange'));


/*
        $name = get_string('notifyteacher', 'publication');
        $mform->addElement('selectyesno', 'notifyteacher', $name);
        $mform->addHelpButton('notifyteacher', 'notifyteacher', 'publication');
        $mform->setDefault('notifyteacher', 0);

        $name = get_string('notifystudents', 'publication');
        $mform->addElement('selectyesno', 'notifystudents', $name);
        $mform->addHelpButton('notifystudents', 'notifystudents', 'publication');
        $mform->setDefault('notifystudents', 0);*/
        // Standard coursemodule elements.
        $this->standard_coursemodule_elements();

        // Buttons.
        $this->add_action_buttons();
        $PAGE->requires->js_call_amd('mod_publication/modform');
    }


    /**
     * Add any custom completion rules to the form.
     *
     * @return array Contains the names of the added form elements
     */
    public function add_completion_rules() {
        $mform =& $this->_form;

        $suffix = $this->get_suffix();
        $completionuploadlabel = 'completionupload' . $suffix;

        $mform->addElement('advcheckbox', $completionuploadlabel, '', get_string('completionupload', 'publication'));
        // Enable this completion rule by default.
        $mform->setDefault($completionuploadlabel, 1);
        $mform->hideIf($completionuploadlabel, 'mode', 'neq', PUBLICATION_MODE_UPLOAD);
        return [$completionuploadlabel];
    }


    public function completion_rule_enabled($data) {
        $suffix = $this->get_suffix();
        $completionuploadlabel = 'completionupload' . $suffix;
        if ($data['mode'] == PUBLICATION_MODE_UPLOAD && !empty($data[$completionuploadlabel])) {
            return true;
        }
        return false;
    }

    public function data_postprocessing($data) {
        global $DB;
        parent::data_postprocessing($data);
        $suffix = $this->get_suffix();
        $completionuploadlabel = 'completionupload' . $suffix;
        if (!isset($data->mode) || $data->mode != PUBLICATION_MODE_UPLOAD) {
            $data->{$completionuploadlabel} = 0;
        }

        $data->groupapproval = 0;
        if ($data->mode == PUBLICATION_MODE_IMPORT && $data->importfrom != -1) {
            $assigninstance = $DB->get_record('assign', ['id' => $data->importfrom], '*', MUST_EXIST);
            if ($assigninstance->teamsubmission) {
                if ($data->obtaingroupapproval == PUBLICATION_APPROVAL_GROUPAUTOMATIC) {
                    $data->groupapproval = 0;
                    $data->obtainstudentapproval = 0;
                } else {
                    $data->obtainstudentapproval = 1;
                    $data->groupapproval = $data->obtaingroupapproval;
                }
            }
        }

    }

    public function data_preprocessing(&$default_values) {
        global $DB;
        parent::data_preprocessing($default_values); // TODO: Change the autogenerated stub

        if (isset($default_values['mode']) && $default_values['mode'] == PUBLICATION_MODE_IMPORT) {
            $assign = $DB->get_record('assign', ['id' => $default_values['importfrom']]);
            if ($assign && $assign->teamsubmission) {
                if ($default_values['obtainstudentapproval'] == 0) {
                    $default_values['obtaingroupapproval'] = PUBLICATION_APPROVAL_GROUPAUTOMATIC;
                } else {
                    $default_values['obtaingroupapproval'] = $default_values['groupapproval'];
                }
            }
        }
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
            if ($data['importfrom'] == '0' || $data['importfrom'] == '-1') {
                $errors['importfrom'] = get_string('importfrom_err', 'publication');
            }
        }

        return $errors;
    }
}
