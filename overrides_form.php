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
 * Settings form for overrides in the publication module.
 *
 * @package    mod_publication
 * @author     Simeon Naydenov
 * @copyright  2024 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Form for editing overrides in the publication module.
 *
 * This form allows teachers to set user or group-specific overrides for submission and approval dates
 * in the mod_publication activity.
 *
 * @package    mod_publication
 * @author     Simeon Naydenov
 * @copyright  2024 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class publication_overrides_form extends moodleform {
    /**
     * @var publication The publication instance used to retrieve context and settings.
     */
    private $_publication;

    /**
     * Defines the form elements for the overrides form.
     *
     * Adds fields for selecting a user or group, and for setting override dates for submissions and approvals.
     * Fields are shown or hidden depending on the publication mode and settings.
     *
     * @return void
     */
    public function definition() {
        global $DB;
        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'overrideid');
        $mform->setType('overrideid', PARAM_INT);

        $this->_publication = $this->_customdata['publication'];
        $mode = $this->_publication->get_mode();
        if ($mode == PUBLICATION_MODE_ASSIGN_TEAMSUBMISSION) {
            $groupids = $this->_publication->get_groups();
            $groups = $DB->get_records_list('groups', 'id', $groupids);

            $mform->addElement('hidden', 'userid');
            $mform->setType('userid', PARAM_INT);
            $mform->setDefault('userid', 0);
            $groupsclean = [];
            foreach ($groups as $group) {
                $groupsclean[$group->id] = $group->name;
            }
            $options = [
                'multiple' => false,
                'noselectionstring' => get_string('override:group:choose', 'publication'),
            ];
            $mform->addElement('autocomplete', 'groupid', get_string('group'), $groupsclean, $options);
            $mform->addRule('groupid', null, 'required', null, 'client');
        } else {
            $userids = $this->_publication->get_users([], true);
            $users = $DB->get_records_list('user', 'id', $userids);

            $mform->addElement('hidden', 'groupid');
            $mform->setType('groupid', PARAM_INT);
            $mform->setDefault('groupid', 0);

            $usersclean = [];
            foreach ($users as $user) {
                if ($user->deleted == 1 || $user->suspended == 1) {
                    continue;
                }
                $usersclean[$user->id] = fullname($user);
            }
            $options = [
                'multiple' => false,
                'noselectionstring' => get_string('override:user:choose', 'publication'),
            ];
            $mform->addElement('autocomplete', 'userid', get_string('user'), $usersclean, $options);
            $mform->addRule('userid', null, 'required', null, 'client');
        }

        $itemsadded = false;
        if ($mode == PUBLICATION_MODE_FILEUPLOAD) {
            $mform->addElement('header', 'submissionsettings', get_string('submissionsettings', 'publication'));
            $mform->setExpanded('submissionsettings');

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
            $itemsadded = true;
        }

        if ($this->_publication->get_instance()->obtainstudentapproval == 1) {
            $mform->addElement('header', 'approvalsettings', get_string('approvalsettings', 'publication'));
            $mform->setExpanded('approvalsettings', true);

            $mform->addElement(
                'date_time_selector',
                'approvalfromdate',
                get_string('approvalfromdate', 'publication'),
                ['optional' => true]
            );
            $mform->addHelpButton('approvalfromdate', 'approvalfromdate', 'publication');
            $mform->setDefault('approvalfromdate', time());

            $mform->addElement(
                'date_time_selector',
                'approvaltodate',
                get_string('approvaltodate', 'publication'),
                ['optional' => true]
            );
            $mform->addHelpButton('approvaltodate', 'approvaltodate', 'publication');
            $mform->setDefault('approvaltodate', time() + 7 * 24 * 3600);
            $itemsadded = true;
        }

        if (!$itemsadded) {
            $mform->addElement('html', '<div class="alert alert-info">' .
                get_string('override:nothingtochange', 'mod_publication') . '</div>');
        }
        $this->add_action_buttons(true);
    }
}
