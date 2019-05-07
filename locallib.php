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
 * Contains much of the logic needed for mod_publication
 *
 * @package       mod_publication
 * @author        Philipp Hager
 * @author        Andreas Windbichler
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define('PUBLICATION_MODE_UPLOAD', 0);
define('PUBLICATION_MODE_IMPORT', 1);
// Used in DB to mark online-text-files!
define('PUBLICATION_MODE_ONLINETEXT', 2);

define('PUBLICATION_APPROVAL_ALL', 0);
define('PUBLICATION_APPROVAL_SINGLE', 1);
require_once($CFG->dirroot . '/mod/publication/mod_publication_allfiles_form.php');

/**
 * publication class contains much logic used in mod_publication
 *
 * @package       mod_publication
 * @author        Philipp Hager
 * @author        Andreas Windbichler
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class publication {
    // TODO replace $instance with proper properties + PHPDoc comments?!?
    /** @var object instance */
    protected $instance;
    /** @var object context */
    protected $context;
    /** @var object course */
    protected $course;
    /** @var object coursemodule */
    protected $coursemodule;
    /** @var bool requiregroup if mode = import and group membership is required for submission in assign to import from */
    protected $requiregroup = 0;

    /**
     * Constructor
     *
     * @param object $cm course module object
     * @param object $course (optional) course object
     * @param context_module $context (optional) Course Module Context
     */
    public function __construct($cm, $course = null, $context = null) {
        global $DB;

        $this->coursemodule = $cm;

        if ($course != null) {
            $this->course = $course;
        } else {
            $this->course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
        }

        if ($context != null) {
            $this->context = $context;
        } else {
            $this->context = context_module::instance($cm->id);
        }

        $this->instance = $DB->get_record("publication", ["id" => $cm->instance]);

        $this->instance->obtainteacherapproval = !$this->instance->obtainteacherapproval;

        if ($this->instance->mode == PUBLICATION_MODE_IMPORT) {
            $cond = ['id' => $this->instance->importfrom];
            $this->requiregroup = $DB->get_field('assign', 'preventsubmissionnotingroup', $cond);
        }
    }

    /**
     * Whether or not to show intro text right now
     *
     * @return bool
     */
    public function show_intro() {
        if ($this->get_instance()->alwaysshowdescription ||
                time() > $this->get_instance()->allowsubmissionfromdate) {
            return true;
        }

        return false;
    }

    /**
     * Display the intro text if available
     */
    public function display_intro() {
        global $OUTPUT;

        if ($this->show_intro()) {
            if ($this->instance->intro) {
                echo $OUTPUT->box_start('generalbox boxaligncenter', 'intro');
                echo $this->instance->intro;
                echo $OUTPUT->box_end();
            }
        } else {
            if ($this->alwaysshowdescription) {
                $message = get_string('allowsubmissionsfromdatesummary',
                        'publication', userdate($this->instance->allowsubmissionsfromdate));
            } else {
                $message = get_string('allowsubmissionsanddescriptionfromdatesummary',
                        'publication', userdate($this->instance->allowsubmissionsfromdate));
            }
            echo html_writer::div($message, '', ['id' => 'intro']);
        }
    }

    /**
     * Display dates which limit submission timespan
     */
    public function display_availability() {
        global $USER, $OUTPUT;

        // Display availability dates.
        $textsuffix = ($this->instance->mode == PUBLICATION_MODE_IMPORT) ? "_import" : "_upload";

        echo $OUTPUT->box_start('generalbox boxaligncenter', 'dates');
        echo '<table>';
        if ($this->instance->allowsubmissionsfromdate) {
            echo '<tr><td class="c0">' . get_string('allowsubmissionsfromdate' . $textsuffix, 'publication') . ':</td>';
            echo '    <td class="c1">' . userdate($this->instance->allowsubmissionsfromdate) . '</td></tr>';
        }
        if ($this->instance->duedate) {
            echo '<tr><td class="c0">' . get_string('duedate' . $textsuffix, 'publication') . ':</td>';
            echo '    <td class="c1">' . userdate($this->instance->duedate) . '</td></tr>';
        }

        $extensionduedate = $this->user_extensionduedate($USER->id);

        if ($extensionduedate) {
            echo '<tr><td class="c0">' . get_string('extensionto', 'publication') . ':</td>';
            echo '    <td class="c1">' . userdate($extensionduedate) . '</td></tr>';
        }

        echo '</table>';

        echo $OUTPUT->box_end();
    }

    /**
     * If the mode is set to import then the link to the corresponding
     * assignment will be displayed
     */
    public function display_importlink() {
        global $DB, $OUTPUT;

        if ($this->instance->mode == PUBLICATION_MODE_IMPORT) {
            echo html_writer::start_div('assignurl');

            if ($this->get_instance()->importfrom == -1) {
                echo get_string('assignment_notset', 'publication');
            } else {
                $assign = $DB->get_record('assign', ['id' => $this->instance->importfrom]);

                $assignmoduleid = $DB->get_field('modules', 'id', ['name' => 'assign']);

                if ($assign) {
                    $assigncm = $DB->get_record('course_modules', [
                            'course' => $assign->course,
                            'module' => $assignmoduleid,
                            'instance' => $assign->id
                    ]);
                } else {
                    $assigncm = false;
                }
                if ($assign && $assigncm) {
                    $assignurl = new moodle_url('/mod/assign/view.php', ['id' => $assigncm->id]);
                    echo get_string('assignment', 'publication') . ': ' . html_writer::link($assignurl, $assign->name);

                    if (has_capability('mod/publication:addinstance', $this->context)) {
                        $url = new moodle_url('/mod/publication/view.php',
                                ['id' => $this->coursemodule->id, 'sesskey' => sesskey(), 'action' => 'import']);
                        $label = get_string('updatefiles', 'publication');

                        echo $OUTPUT->single_button($url, $label);
                    }

                } else {
                    echo $OUTPUT->notification(get_string('assignment_notfound', 'publication'), 'warning');
                }
            }
            echo html_writer::end_div();
        }

    }

    /**
     * Display Link to upload form if submission date is open
     * and the user has the capability to upload files
     *
     * @return string HTML snippet with upload link (single button or plain text if not allowed)
     */
    public function display_uploadlink() {
        global $OUTPUT;

        if ($this->instance->mode == PUBLICATION_MODE_UPLOAD) {
            if (has_capability('mod/publication:upload', $this->context)) {
                if ($this->is_open()) {
                    $url = new moodle_url('/mod/publication/upload.php',
                            ['id' => $this->instance->id, 'cmid' => $this->coursemodule->id]);
                    $label = get_string('edit_uploads', 'publication');
                    $editbutton = $OUTPUT->single_button($url, $label);

                    return $editbutton;
                } else {
                    return get_string('edit_timeover', 'publication');
                }
            } else {
                return get_string('edit_notcapable', 'publication');
            }
        }
    }

    /**
     * Get the extension due date (if set)
     *
     * @param int $uid User ID to fetch extension due date for
     * @return int extension due date if set or 0
     */
    public function user_extensionduedate($uid) {
        global $DB;

        $extensionduedate = $DB->get_field('publication_extduedates', 'extensionduedate', [
                'publication' => $this->get_instance()->id,
                'userid' => $uid
        ]);

        if (!$extensionduedate) {
            return 0;
        }

        return $extensionduedate;
    }

    /**
     * Check if submission is currently allowed due to allowsubmissionsfromdae and duedate
     *
     * @return bool
     */
    public function is_open() {
        global $USER;

        if (!has_capability('mod/publication:upload', $this->get_context())) {
            return false;
        }

        $now = time();

        $from = $this->get_instance()->allowsubmissionsfromdate;
        $due = $this->get_instance()->duedate;

        $extensionduedate = $this->user_extensionduedate($USER->id);

        if ($extensionduedate) {
            $due = $extensionduedate;
        }

        if (($from == 0 || $from < $now) &&
                ($due == 0 || $due > $now)) {
            return true;
        }

        return false;
    }

    /**
     * Instance getter
     *
     * @return object instance object
     */
    public function get_instance() {
        return $this->instance;
    }

    /**
     * Context getter
     *
     * @return \context_module context object
     */
    public function get_context() {
        return $this->context;
    }

    /**
     * Coursemodule getter
     *
     * @return object coursemodule object
     */
    public function get_coursemodule() {
        return $this->coursemodule;
    }

    /**
     * Whether or not the assign to import from requires group membership for submissions!
     *
     * @return bool true if group membership is required, false if not or type = upload
     */
    public function requiregroup() {
        return $this->requiregroup;
    }

    /**
     * Get's all groups (optionaly filtered by groupingid or group-IDs in selgroups-array)
     *
     * @param int $groupingid (optional) Grouping-ID to filter groups for or 0
     * @param int[] $selgroups (optional) selected group's IDs to filter for or empty array()
     * @return int[] array of group's IDs
     */
    public function get_groups($groupingid = 0, $selgroups = []) {
        $groups = groups_get_all_groups($this->get_instance()->course, 0, $groupingid);
        $groups = array_keys($groups);

        if (empty($groupingid) && !$this->requiregroup()) {
            $groups[] = 0;
        }

        if (is_array($selgroups) && count($selgroups) > 0) {
            $groups = array_intersect($groups, $selgroups);
        }

        return $groups;
    }

    /**
     * Get userids to fetch files for, when displaying all submitted files or downloading them as ZIP
     *
     * @param int[] $users (optional) user ids for which the returned user ids have to filter
     * @return int[] array of userids
     */
    public function get_users($users = []) {
        global $DB;

        $customusers = '';

        if (is_array($users) && count($users) > 0) {
            $customusers = " and u.id IN (" . implode($users, ', ') . ") ";
        } else if ($users === false) {
            return [];
        }

        // Find out current groups mode.
        $currentgroup = groups_get_activity_group($this->get_coursemodule(), true);

        // Get all ppl that are allowed to submit assignments.
        list($esql, $params) = get_enrolled_sql($this->context, 'mod/publication:view', $currentgroup);

        if (has_capability('mod/publication:approve', $this->context)
                || has_capability('mod/publication:grantextension', $this->context)) {
            // We can skip the approval-checks for teachers!
            $sql = 'SELECT u.id FROM {user} u ' .
                    'LEFT JOIN (' . $esql . ') eu ON eu.id=u.id ' .
                    'WHERE u.deleted = 0 AND eu.id=u.id ' . $customusers;
        } else {
            $sql = 'SELECT u.id FROM {user} u ' .
                    'LEFT JOIN (' . $esql . ') eu ON eu.id=u.id ' .
                    'LEFT JOIN {publication_file} files ON (u.id = files.userid) ' .
                    'WHERE u.deleted = 0 AND eu.id=u.id ' . $customusers .
                    'AND files.publication = ' . $this->get_instance()->id . ' ';

            if ($this->get_instance()->mode == PUBLICATION_MODE_UPLOAD) {
                // Mode upload.
                if ($this->get_instance()->obtainteacherapproval) {
                    // Need teacher approval.

                    $where = 'files.teacherapproval = 1';
                } else {
                    // No need for teacher approval.
                    // Teacher only hasnt rejected.
                    $where = '(files.teacherapproval = 1 OR files.teacherapproval IS NULL)';
                }
            } else {
                // Mode import.
                if (!$this->get_instance()->obtainstudentapproval) {
                    // No need to ask student and teacher has approved.
                    $where = 'files.teacherapproval = 1';
                } else {
                    // Student and teacher have approved.
                    $where = 'files.teacherapproval = 1 AND files.studentapproval = 1';
                }
            }

            $sql .= 'AND ' . $where . ' ';
            $sql .= 'GROUP BY u.id';
        }

        $users = $DB->get_fieldset_sql($sql, $params);

        if (empty($users)) {
            return [-1];
        }

        return $users;
    }

    /**
     * Display form with table containing all files
     *
     * TODO: for Moodle 3.6 we should replace old form classes with a nice bootstrap based form layout!
     */
    public function display_allfilesform() {
        global $CFG, $DB;

        $cm = $this->coursemodule;
        $context = $this->context;

        $updatepref = optional_param('updatepref', 0, PARAM_BOOL);
        if ($updatepref) {
            $perpage = optional_param('perpage', 10, PARAM_INT);
            $perpage = ($perpage <= 0) ? 10 : $perpage;
            $filter = optional_param('filter', 0, PARAM_INT);
            set_user_preference('publication_perpage', $perpage);
        }

        // Next we get perpage param from database!
        $perpage = get_user_preferences('publication_perpage', 10);
        $filter = get_user_preferences('publicationfilter', 0);

        $page = optional_param('page', 0, PARAM_INT);

        $formattrs = [];
        $formattrs['action'] = new moodle_url('/mod/publication/view.php');
        $formattrs['id'] = 'fastg';
        $formattrs['method'] = 'post';
        $formattrs['class'] = 'mform';

        echo html_writer::start_tag('form', $formattrs) .
                html_writer::empty_tag('input', [
                        'type' => 'hidden',
                        'name' => 'id',
                        'value' => $this->get_coursemodule()->id
                ]) .
                html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'page', 'value' => $page]) .
                html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);

        echo html_writer::start_tag('div', ['id' => 'id_allfiles', 'class' => 'clearfix', 'aria-live' => 'polite']);
        $allfiles = get_string('allfiles', 'publication');
        $publicfiles = get_string('publicfiles', 'publication');
        $title = (has_capability('mod/publication:approve', $context)) ? $allfiles : $publicfiles;
        echo html_writer::tag('div', $title, ['class' => 'legend']);
        echo html_writer::start_div('fcontainer clearfix');

        $f = groups_print_activity_menu($cm, $CFG->wwwroot . '/mod/publication/view.php?id=' . $cm->id, true);
        $mf = new mod_publication_allfiles_form(null, array('form' => $f));
        $mf->display();

        if ($this->get_instance()->mode == PUBLICATION_MODE_UPLOAD) {
            $table = new \mod_publication\local\allfilestable\upload('mod-publication-allfiles', $this);
        } else {
            if ($DB->get_field('assign', 'teamsubmission', ['id' => $this->get_instance()->importfrom])) {
                $table = new \mod_publication\local\allfilestable\group('mod-publication-allgroupfiles', $this);
            } else {
                $table = new \mod_publication\local\allfilestable\import('mod-publication-allfiles', $this);
            }
        }

        $link = html_writer::link(new moodle_url('/mod/publication/view.php', [
                'id' => $this->coursemodule->id,
                'action' => 'zip'
        ]),
                get_string('downloadall', 'publication'));
        echo html_writer::tag('div', $link, ['class' => 'mod-publication-download-link']);

        $table->out($perpage, true); // Print the whole table.

        $options = [];
        $options['zipusers'] = get_string('zipusers', 'publication');

        if (has_capability('mod/publication:approve', $context) && $table->totalfiles() > 0) {
            $options['approveusers'] = get_string('approveusers', 'publication');
            $options['rejectusers'] = get_string('rejectusers', 'publication');

            if ($this->get_instance()->mode == PUBLICATION_MODE_IMPORT &&
                    $this->get_instance()->obtainstudentapproval) {
                $options['resetstudentapproval'] = get_string('resetstudentapproval', 'publication');
            }
        }
        if (has_capability('mod/publication:grantextension', $this->get_context())) {
            $options['grantextension'] = get_string('grantextension', 'publication');
        }

        if (count($options) > 0) {
            echo html_writer::start_div('form-row');
            if (has_capability('mod/publication:approve', $context)) {
                $buttons = html_writer::empty_tag('input', [
                        'type' => 'reset',
                        'name' => 'resetvisibility',
                        'value' => get_string('reset', 'publication'),
                        'class' => 'visibilitysaver btn btn-secondary'
                ]);

                if ($this->get_instance()->mode == PUBLICATION_MODE_IMPORT &&
                        $this->get_instance()->obtainstudentapproval) {
                    $buttons .= html_writer::empty_tag('input', [
                            'type' => 'submit',
                            'name' => 'savevisibility',
                            'value' => get_string('saveapproval', 'publication'),
                            'class' => 'visibilitysaver btn btn-primary m-x-1'
                    ]);
                } else {
                    $buttons .= html_writer::empty_tag('input', [
                            'type' => 'submit',
                            'name' => 'savevisibility',
                            'value' => get_string('saveteacherapproval', 'publication'),
                            'class' => 'visibilitysaver btn btn-primary'
                    ]);
                }
            } else {
                $buttons = '';
            }

            echo html_writer::start_div('withselection col-7').
                 html_writer::span(get_string('withselected', 'publication')).
                 html_writer::select($options, 'action').
                 html_writer::empty_tag('input', [
                    'type' => 'submit',
                    'name' => 'submitgo',
                    'value' => get_string('go', 'publication'),
                    'class' => 'btn btn-primary'
                 ]).html_writer::end_div().
                 html_writer::div($buttons, 'col');

        }

        // Select all/none.
        echo html_writer::start_tag('div', ['class' => 'checkboxcontroller']) . "
            <script type=\"text/javascript\">
                function toggle_userselection() {
                    var checkboxes = document.getElementsByClassName('userselection');
                    var sel = document.getElementById('selectallnone');

                    if (checkboxes.length > 0) {
                        checkboxes[0].checked = sel.checked;

                        for(var i = 1; i < checkboxes.length;i++) {
                            checkboxes[i].checked = checkboxes[0].checked;
                        }
                    }
                }
            </script>" .
                html_writer::end_div() .
                html_writer::end_div() .
                html_writer::end_div() .
                html_writer::end_tag('form');

        // Mini form for setting user preference.
        $formaction = new moodle_url('/mod/publication/view.php', ['id' => $this->coursemodule->id]);
        $mform = new MoodleQuickForm('optionspref', 'post', $formaction, '', ['class' => 'optionspref']);

        $mform->addElement('hidden', 'updatepref');
        $mform->setDefault('updatepref', 1);

        $mform->addElement('header', 'qgprefs', get_string('optionalsettings', 'publication'));

        $mform->addElement('text', 'perpage', get_string('entiresperpage', 'publication'), ['size' => 1]);
        $mform->setDefault('perpage', $perpage);

        $mform->addElement('submit', 'savepreferences', get_string('savepreferences'));

        $mform->display();
    }

    /**
     * Returns if a user has the permission to view a file
     *
     * @param unknown $fileid
     * @param number $userid use for custom user, if 0 then if public visible
     * @return boolean
     */
    public function has_filepermission($fileid, $userid = 0) {
        global $DB;

        $conditions = [];
        $conditions['publication'] = $this->get_instance()->id;
        $conditions['fileid'] = $fileid;

        $filepermissions = $DB->get_record('publication_file', $conditions);

        $haspermission = false;

        if ($filepermissions) {
            if ($userid != 0) {
                if ($this->get_instance()->mode == PUBLICATION_MODE_UPLOAD && $filepermissions->userid == $userid) {
                    // Everyone is allowed to view their own files.
                    $haspermission = true;
                } else if ($this->get_instance()->mode == PUBLICATION_MODE_IMPORT) {
                    // If it's a team-submission, we have to check for the group membership!
                    $teamsubmission = $DB->get_field('assign', 'teamsubmission', ['id' => $this->get_instance()->importfrom]);
                    if (!empty($teamsubmission)) {
                        $groupmembers = $this->get_submissionmembers($filepermissions->userid);
                        if (array_key_exists($userid, $groupmembers)) {
                            $haspermission = true;
                        }
                    } else if ($filepermissions->userid == $userid) {
                        // Everyone is allowed to view their own files.
                        $haspermission = true;
                    }
                }
            }

            if ($this->get_instance()->mode == PUBLICATION_MODE_UPLOAD) {
                // Mode upload.
                if ($this->get_instance()->obtainteacherapproval) {
                    // Need teacher approval.
                    if ($filepermissions->teacherapproval == 1) {
                        // Teacher has approved.
                        $haspermission = true;
                    }
                } else {
                    // No need for teacher approval.
                    if (is_null($filepermissions->teacherapproval) || $filepermissions->teacherapproval == 1) {
                        // Teacher only hasnt rejected.
                        $haspermission = true;
                    }
                }
            } else {
                // Mode import.
                if (!$this->get_instance()->obtainstudentapproval && $filepermissions->teacherapproval == 1) {
                    // No need to ask student and teacher has approved.
                    $haspermission = true;
                } else if ($this->get_instance()->obtainstudentapproval &&
                        $filepermissions->teacherapproval == 1 && $filepermissions->studentapproval == 1) {
                    // Student and teacher have approved.
                    $haspermission = true;
                }
            }
        }

        return $haspermission;
    }

    /**
     * Sets group approval for the specified user and returns current cumulated group approval!
     *
     * @param null|int $approval 0 if rejected, 1 if approved and 'null' if not set!
     * @param int $pubfileid ID of publication file entry in DB
     * @param int $userid ID of user to set approval/rejection for
     * @return null|int cumulated approval for specified file
     */
    public function set_group_approval($approval, $pubfileid, $userid) {
        global $DB;

        // Normalize approval value!
        if ($approval !== null) {
            $approval = empty($approval) ? 0 : 1;
        }

        $record = $DB->get_record('publication_groupapproval', ['fileid' => $pubfileid, 'userid' => $userid]);
        $filerec = $DB->get_record('publication_file', ['id' => $pubfileid]);
        if (!empty($record)) {
            if ($record->approval === $approval) {
                // Nothing changed, return!
                return $filerec->studentapproval;
            }
            $record->approval = $approval;
            $record->timemodified = time();
            $DB->update_record('publication_groupapproval', $record);
        } else {
            $record = new stdClass();
            $record->fileid = $pubfileid;
            $record->userid = $userid;
            $record->approval = $approval;
            $record->timecreated = time();
            $record->timemodified = $record->timecreated;
            $record->id = $DB->insert_record('publication_groupapproval', $record);
        }

        // Calculate new cumulated studentapproval for caching in file table!

        // Get group members!
        $groupmembers = $this->get_submissionmembers($filerec->userid);
        if (!empty($groupmembers)) {
            list($usersql, $userparams) = $DB->get_in_or_equal(array_keys($groupmembers), SQL_PARAMS_NAMED, 'user');
            $select = "fileid = :fileid AND approval = :approval AND userid " . $usersql;
            $params = ['fileid' => $pubfileid, 'approval' => 0] + $userparams;
            if ($DB->record_exists_select('publication_groupapproval', $select, $params)) {
                // If anyone rejected it's rejected, no matter what!
                $approval = 0;
            } else {
                if ($this->get_instance()->groupapproval == PUBLICATION_APPROVAL_SINGLE) {
                    // If only one has to approve, we check for that!
                    $params['approval'] = 1;
                    if ($DB->record_exists_select('publication_groupapproval', $select, $params)) {
                        $approval = 1;
                    } else {
                        $approval = 0;
                    }
                } else {
                    // All group members have to approve!
                    $select = "fileid = :fileid AND approval IS NULL AND userid " . $usersql;
                    $params = ['fileid' => $pubfileid] + $userparams;
                    $approving = $DB->count_records_sql("SELECT count(DISTINCT userid)
                                                           FROM {publication_groupapproval}
                                                          WHERE fileid = :fileid AND approval = 1 AND userid " . $usersql, $params);
                    if ($approving < count($userparams)) {
                        // Rejected if not every group member has approved the file!
                        $approval = null;
                    } else {
                        $approval = 1;
                    }
                }
            }
        } else {
            // Group without members, so no one could approve! (Should never happen, never ever!)
            $approval = 0;
        }

        // Update approval value and return it!
        $filerec->studentapproval = $approval;
        $DB->update_record('publication_file', $filerec);

        return $approval;
    }

    /**
     * Determine and return the teacher's approval status for the given file!
     *
     * @param stored_file $file file to determine approval status for
     * @return int|null teacher's approval status (null pending, 1 approved, all other rejected)
     */
    public function teacher_approval(\stored_file $file) {
        global $DB;

        if (empty($conditions)) {
            static $conditions = [];
            $conditions['publication'] = $this->get_instance()->id;
        }
        $conditions['fileid'] = $file->get_id();

        $teacherapproval = $DB->get_field('publication_file', 'teacherapproval', $conditions);

        return $teacherapproval;
    }

    /**
     * Determine and return the student's approval status for the given file!
     *
     * @param stored_file $file file to determine approval status for
     * @return int|null student's approval status (null/0 = pending, 1 = rejected, 2 = approved)
     */
    public function student_approval(\stored_file $file) {
        global $DB;

        if (empty($conditions)) {
            static $conditions = [];
            $conditions['publication'] = $this->get_instance()->id;
        }
        $conditions['fileid'] = $file->get_id();

        $studentapproval = $DB->get_field('publication_file', 'studentapproval', $conditions);

        $studentapproval = (!is_null($studentapproval)) ? $studentapproval + 1 : null;

        return $studentapproval;
    }

    /**
     * Gets the group members for the specified group. Or users without membership if groupid is 0!
     *
     * @param int $groupid
     * @return stdClass[] Group member's user records.
     */
    public function get_submissionmembers($groupid) {
        global $DB;

        if (($this->get_instance()->mode != PUBLICATION_MODE_IMPORT)
                || !$DB->get_field('assign', 'teamsubmission', ['id' => $this->get_instance()->importfrom])) {
            throw new coding_exception('Cannot be called if files get uploaded or teamsubmission is deactivated!');
        }

        if (!empty($groupid)) {
            $groupmembers = groups_get_members($groupid);
        } else if (!$DB->get_field('assign', 'preventsubmissionnotingroup', ['id' => $this->get_instance()->importfrom])) {
            // If groupid == 0, we get all users without group!
            $groupmembers = [];
            $assigncm = get_coursemodule_from_instance('assign', $this->instance->importfrom);
            $context = context_module::instance($assigncm->id);
            $users = get_enrolled_users($context, "mod/assign:submit", 0);
            if (!empty($users)) {
                foreach ($users as $user) {
                    $ugrps = groups_get_user_groups($this->instance->course, $user->id);
                    if (!count($ugrps[0])) {
                        $groupmembers[$user->id] = $user;
                    }
                }
            }
        } else {
            $groupmembers = [];
        }

        return $groupmembers;
    }

    /**
     * Gets group approval for the specified file!
     *
     * @param int $pubfileid ID of publication file entry in DB
     * @return array cumulated approval for specified file and array with approval details
     */
    public function group_approval($pubfileid) {
        global $DB;

        if (($this->get_instance()->mode != PUBLICATION_MODE_IMPORT)
                || !$DB->get_field('assign', 'teamsubmission', ['id' => $this->get_instance()->importfrom])) {
            throw new coding_exception('Cannot be called if files get uploaded or teamsubmission is deactivated!');
        }

        $filerec = $DB->get_record('publication_file', ['id' => $pubfileid]);

        // Get group members!
        $groupmembers = $this->get_submissionmembers($filerec->userid);

        if (!empty($groupmembers)) {
            list($usersql, $userparams) = $DB->get_in_or_equal(array_keys($groupmembers), SQL_PARAMS_NAMED, 'user');
            $sql = "SELECT u.*, ga.approval, ga.timemodified AS approvaltime
                      FROM {user} u
                 LEFT JOIN {publication_groupapproval} ga ON u.id = ga.userid AND ga.fileid = :fileid
                     WHERE u.id " . $usersql;
            $params = ['fileid' => $filerec->id] + $userparams;
            $groupdata = $DB->get_records_sql($sql, $params);
        } else {
            $groupdata = [];
        }

        return [$filerec->studentapproval, $groupdata];
    }

    /**
     * Download a single file, returns file content and terminated script.
     *
     * @param int $fileid ID of the submitted file in filespace
     */
    public function download_file($fileid) {
        global $DB, $USER;

        $conditions = [];
        $conditions['publication'] = $this->get_instance()->id;
        $conditions['fileid'] = $fileid;
        $record = $DB->get_record('publication_file', $conditions);

        $allowed = false;

        if (has_capability('mod/publication:approve', $this->get_context())) {
            // Teachers has to see the files to know if they can allow them.
            $allowed = true;
        } else if ($this->has_filepermission($fileid, $USER->id)) {
            // File is publicly viewable or is owned by the user.
            $allowed = true;
        }

        if ($allowed) {
            $fs = get_file_storage();
            $file = $fs->get_file_by_id($fileid);
            $itemid = $file->get_itemid();
            if ($record->type == PUBLICATION_MODE_ONLINETEXT) {
                global $CFG;

                if ($this->get_instance()->importfrom == -1) {
                    $teamsubmission = false;
                } else {
                    $teamsubmission = $DB->get_field('assign', 'teamsubmission', ['id' => $this->get_instance()->importfrom]);
                }
                if (!$teamsubmission) {
                    // Get user firstname/lastname.
                    $auser = $DB->get_record('user', ['id' => $itemid], get_all_user_name_fields(true));
                    $itemname = str_replace(' ', '_', fullname($auser)).'_';
                } else {
                    if (empty($itemid)) {
                        $itemname = get_string('defaultteam', 'assign').'_';
                    } else {
                        $itemname = $DB->get_field('groups', 'name', ['id' => $itemid]).'_';
                    }
                }

                // Create path for new zip file.
                $zipfile = tempnam($CFG->dataroot . '/temp/', 'publication_');
                // Zip files.
                $filename = $itemname.$file->get_filename();
                $zipname = str_replace('.html', '.zip', $filename);
                $zipper = new zip_packer();
                $filesforzipping = [];
                $this->add_onlinetext_to_zipfiles($filesforzipping, $file, '', $filename, $fs);
                if (count($filesforzipping) == 1) {
                    // We can send the file directly, if it has no resources!
                    send_file($file, $filename, 'default', 0, false, true, $file->get_mimetype(), false);
                } else if ($zipper->archive_to_pathname($filesforzipping, $zipfile)) {
                    send_temp_file($zipfile, $zipname); // Send file and delete after sending.
                }
            } else {
                send_file($file, $file->get_filename(), 'default', 0, false, true, $file->get_mimetype(), false);
            }
            die();
        } else {
            print_error('You are not allowed to see this file'); // TODO ge_string().
        }
    }

    /**
     * Creates a zip of all uploaded files and sends a zip to the browser
     *
     * @param unknown $uploaders false => empty zip, true all users, array files from uploaders (users/groups) in array
     */
    public function download_zip($uploaders = []) {
        global $CFG, $DB, $USER;
        require_once($CFG->libdir . '/filelib.php');

        $cm = $this->get_coursemodule();

        $canapprove = has_capability('mod/publication:approve', $this->get_context());
        if ($this->get_instance()->importfrom == -1) {
            $teamsubmission = false;
        } else {
            $teamsubmission = $DB->get_field('assign', 'teamsubmission', ['id' => $this->get_instance()->importfrom]);
        }

        $conditions = [];
        $conditions['publication'] = $this->get_instance()->id;

        $filesforzipping = [];
        $fs = get_file_storage();

        // Get group name for filename.
        $groupname = '';
        $currentgroup = groups_get_activity_group($cm, true);
        if (!empty($currentgroup)) {
            $groupname = $DB->get_field('groups', 'name', ['id' => $currentgroup]) . '-';
        }

        if (!$teamsubmission) {
            $uploaders = $this->get_users($uploaders);
        } else {
            $uploaders = $this->get_groups(0, $uploaders);
        }

        $filename = str_replace(' ', '_', clean_filename($this->course->shortname . '-' .
                $this->get_instance()->name . '-' . $groupname . $this->get_instance()->id . '.zip')); // Name of new zip file.

        $userfields = get_all_user_name_fields();
        $userfields['id'] = 'id';
        $userfields['username'] = 'username';
        $userfields = implode(', ', $userfields);

        // Get all files from each user/group.
        foreach ($uploaders as $uploader) {
            $conditions['userid'] = $uploader;
            $records = $DB->get_records('publication_file', $conditions);

            if (!$teamsubmission) {
                // Get user firstname/lastname.
                $auser = $DB->get_record('user', ['id' => $uploader], $userfields);
                $itemname = fullname($auser);
                $itemunique = $uploader;
            } else {
                if (empty($uploader)) {
                    $itemname = get_string('defaultteam', 'assign');
                } else {
                    $itemname = $DB->get_field('groups', 'name', ['id' => $uploader]);
                }
                $itemunique = '';
            }

            foreach ($records as $record) {
                if ($canapprove || $this->has_filepermission($record->fileid, $USER->id)) {
                    // Is teacher or file is public.

                    $file = $fs->get_file_by_id($record->fileid);

                    // Get files new name.
                    $fileext = strstr($file->get_filename(), '.');
                    $fileoriginal = str_replace($fileext, '', $file->get_filename());
                    $fileforzipname = clean_filename($itemname . '_' . $fileoriginal . '_' . $itemunique . $fileext);
                    if (key_exists($fileforzipname, $filesforzipping)) {
                        throw new coding_exception('Can\'t overwrite ' . $fileforzipname . '!');
                    }
                    if ($record->type == PUBLICATION_MODE_ONLINETEXT) {
                        $this->add_onlinetext_to_zipfiles($filesforzipping, $file, $itemname, $fileforzipname, $fs, $itemunique);
                    } else {
                        // Save file name to array for zipping.
                        $filesforzipping[$fileforzipname] = $file;
                    }
                }
            }
        } // End of foreach.

        if ($zipfile = $this->pack_files($filesforzipping)) {
            send_temp_file($zipfile, $filename); // Send file and delete after sending.
        }
    }

    /**
     * Pack files in ZIP
     *
     * @param object[] $filesforzipping Files for zipping
     * @return object zipped files
     */
    private function pack_files($filesforzipping) {
        global $CFG;
        // Create path for new zip file.
        $tempzip = tempnam($CFG->dataroot . '/temp/', 'publication_');
        // Zip files.
        $zipper = new zip_packer();
        if ($zipper->archive_to_pathname($filesforzipping, $tempzip)) {
            return $tempzip;
        }

        return false;
    }

    /**
     * Adds onlinetext-file to zipping-files including all ressources!
     *
     * @param stored_file[] $filesforzipping array of stored files indexed by filename
     * @param stored_file $file onlinetext-file to add to ZIP
     * @param string $itemname User or group's name to use for filename
     * @param string $fileforzipname Filename to use for the file being added
     * @param file_storage $fs used to get the ressource files for the online-text-file
     * @param string $itemunique user-ID of the uploading user or empty for teamsubmissions
     */
    protected function add_onlinetext_to_zipfiles(array &$filesforzipping, stored_file $file, $itemname, $fileforzipname,
                                                  $fs = null, $itemunique = '') {

        if (empty($fs)) {
            $fs = get_file_storage();
        }

        // First we get all ressources!
        $resources = $fs->get_directory_files($this->get_context()->id,
                'mod_publication',
                'attachment',
                $file->get_itemid(),
                '/resources/',
                true,
                false);
        if (count($resources) > 0) {
            // If it's an online-Text with resources, we have to add altered content and all the ressources for it!
            $content = $file->get_content();
            // We grabbed the resources already above!
            // Then we change every occurence of the ressource-name from ./resourcename to ./ITEMNAME/resourcename!
            $folder = clean_filename((!empty($itemname) ? $itemname . '_' : '') .
                    (($itemunique != '') ? $itemunique . '_' : '') .
                    'resources');
            foreach ($resources as $resource) {
                $search = './resources/' . $resource->get_filename();
                $replace = $folder . '/' . $resource->get_filename();
                $content = str_replace($search, './' . $replace, $content);
                $filesforzipping[$replace] = $resource;
            }
            /* We add the altered filecontent instead of the stored one        *
             * (needs an array to differentiate between content and filepath)! */
            $filesforzipping[$fileforzipname] = [$content];
        } else {
            $filesforzipping[$fileforzipname] = $file;
        }
    }

    /**
     * Updates files from connected assignment
     */
    public function importfiles() {
        global $DB;

        if ($this->instance->mode == PUBLICATION_MODE_IMPORT) {
            $assign = $DB->get_record('assign', ['id' => $this->instance->importfrom]);
            $assignmoduleid = $DB->get_field('modules', 'id', ['name' => 'assign']);
            $assigncm = $DB->get_record('course_modules', [
                    'course' => $assign->course,
                    'module' => $assignmoduleid,
                    'instance' => $assign->id
            ]);

            $assigncontext = context_module::instance($assigncm->id);

            if ($assigncm && has_capability('mod/publication:addinstance', $this->context)) {
                $this->import_assign_files($assigncm, $assigncontext);
                $this->import_assign_onlinetexts($assigncm, $assigncontext);

                return true;
            }
        }

        return false;
    }

    /**
     * Import assignment's submission files!
     *
     * @param object $assigncm Assignment coursemodule object
     * @param object $assigncontext Assignment context object
     */
    protected function import_assign_files($assigncm, $assigncontext) {
        global $DB, $CFG, $OUTPUT;

        $records = $DB->get_records('assignsubmission_file', ['assignment' => $this->get_instance()->importfrom]);

        $fs = get_file_storage();

        require_once($CFG->dirroot . '/mod/assign/locallib.php');
        $assigncourse = $DB->get_record('course', ['id' => $assigncm->course]);
        $assignment = new assign($assigncontext, $assigncm, $assigncourse);

        foreach ($records as $record) {

            $files = $fs->get_area_files($assigncontext->id,
                    "assignsubmission_file",
                    "submission_files",
                    $record->submission,
                    "id",
                    false);
            $submission = $DB->get_record('assign_submission', ['id' => $record->submission]);

            $assignfileids = [];

            $assignfiles = [];

            foreach ($files as $file) {
                $assignfiles[$file->get_id()] = $file;
                $assignfileids[$file->get_id()] = $file->get_id();
            }

            $conditions = [];
            $conditions['publication'] = $this->get_instance()->id;
            if (empty($assignment->get_instance()->teamsubmission)) {
                $conditions['userid'] = $submission->userid;
            } else {
                $conditions['userid'] = $submission->groupid;
            }
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
                    if ($file = $fs->get_file_by_id($oldpubfile->fileid)) {
                        $file->delete();
                    }

                    $conditions['id'] = $oldpubfile->id;

                    $DB->delete_records('publication_file', $conditions);
                }
            }

            // Add new files to publication.
            foreach ($assignfileids as $assignfileid) {
                $newfilerecord = new stdClass();
                $newfilerecord->contextid = $this->get_context()->id;
                $newfilerecord->component = 'mod_publication';
                $newfilerecord->filearea = 'attachment';
                if (empty($assignment->get_instance()->teamsubmission)) {
                    $newfilerecord->itemid = $submission->userid;
                } else {
                    $newfilerecord->itemid = $submission->groupid;
                }

                try {
                    $newfile = $fs->create_file_from_storedfile($newfilerecord, $assignfiles[$assignfileid]);

                    $dataobject = new stdClass();
                    $dataobject->publication = $this->get_instance()->id;
                    if (empty($assignment->get_instance()->teamsubmission)) {
                        $dataobject->userid = $submission->userid;
                    } else {
                        $dataobject->userid = $submission->groupid;
                    }
                    $dataobject->timecreated = time();
                    $dataobject->fileid = $newfile->get_id();
                    $dataobject->filesourceid = $assignfileid;
                    $dataobject->filename = $newfile->get_filename();
                    $dataobject->contenthash = "666";
                    $dataobject->type = PUBLICATION_MODE_IMPORT;

                    $DB->insert_record('publication_file', $dataobject);
                } catch (Exception $e) {
                    // File could not be copied, maybe it does already exist.
                    // Should not happen.
                    echo $OUTPUT->box($OUTPUT->notification($e->getMessage(), 'notifyproblem'), 'generalbox');
                }
            }
        }

    }

    /**
     * Import assignment's onlinetext submissions!
     *
     * @param object $assigncm Assignment coursemodule object
     * @param object $assigncontext Assignment context object
     */
    protected function import_assign_onlinetexts($assigncm, $assigncontext) {
        if ($this->get_instance()->mode != PUBLICATION_MODE_IMPORT) {
            return;
        }

        self::update_assign_onlinetext($assigncm, $assigncontext, $this->get_instance()->id, $this->get_context()->id);
    }

    /**
     * Updates the online-submission(s) of a single assignment used for manual import and autoimport via event observer
     *
     * @param stdClass $assigncm Assign's coursemodule object
     * @param stdClass $assigncontext Assign's context object
     * @param int $publicationid Publication's instance ID
     * @param int $contextid Publication's context ID
     * @param int $submissionid (optional) If set, only process this submission, else process all submissions
     */
    public static function update_assign_onlinetext($assigncm, $assigncontext, $publicationid, $contextid, $submissionid = 0) {
        global $DB, $CFG;

        $fs = get_file_storage();

        require_once($CFG->dirroot . '/mod/assign/locallib.php');
        $assigncourse = $DB->get_record('course', ['id' => $assigncm->course]);
        $assignment = new assign($assigncontext, $assigncm, $assigncourse);
        $teamsubmission = $assignment->get_instance()->teamsubmission;

        if (!empty($submissionid)) {
            $records = $DB->get_records('assignsubmission_onlinetext', [
                    'assignment' => $assigncm->instance,
                    'submission' => $submissionid
            ]);
        } else {
            $records = $DB->get_records('assignsubmission_onlinetext', ['assignment' => $assigncm->instance]);
        }

        foreach ($records as $record) {
            $submission = $DB->get_record('assign_submission', ['id' => $record->submission]);
            $itemid = empty($teamsubmission) ? $submission->userid : $submission->groupid;

            // First we fetch the resource files (embedded files in text!)
            $fsfiles = $fs->get_area_files($assigncontext->id,
                    'assignsubmission_onlinetext',
                    ASSIGNSUBMISSION_ONLINETEXT_FILEAREA,
                    $submission->id,
                    'timemodified',
                    false);
            foreach ($fsfiles as $file) {
                $filerecord = new \stdClass();
                $filerecord->contextid = $contextid;
                $filerecord->component = 'mod_publication';
                $filerecord->filearea = 'attachment';
                $filerecord->itemid = $itemid;
                $filerecord->filepath = '/resources/';
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

            // Now we delete old resource-files, which are no longer present!
            $resources = $fs->get_directory_files($contextid,
                    'mod_publication',
                    'attachment',
                    $itemid,
                    '/resources/',
                    true,
                    false);
            foreach ($resources as $resource) {
                $pathnamehash = $fs->get_pathname_hash($assignment->get_context()->id, 'assignsubmission_onlinetext',
                        ASSIGNSUBMISSION_ONLINETEXT_FILEAREA, $submission->id, '/',
                        $resource->get_filename());
                if (!$fs->file_exists_by_hash($pathnamehash)) {
                    $resource->delete();
                }
            }

            /* Here we convert the pluginfile urls to relative urls for the exported html-file
             * (the resources have to be included in the download!) */
            $formattedtext = str_replace('@@PLUGINFILE@@/', './resources/', $record->onlinetext);
            $formattedtext = format_text($formattedtext, $record->onlineformat, ['context' => $assigncontext]);

            $head = '<head><meta charset="UTF-8"></head>';
            $submissioncontent = '<!DOCTYPE html><html>' . $head . '<body>' . $formattedtext . '</body></html>';

            $filename = get_string('onlinetextfilename', 'assignsubmission_onlinetext');

            // Does the file exist... let's check it!
            $pathhash = $fs->get_pathname_hash($contextid, 'mod_publication', 'attachment', $itemid, '/', $filename);

            $conditions = [
                    'publication' => $publicationid,
                    'userid' => $itemid,
                    'type' => PUBLICATION_MODE_ONLINETEXT
            ];
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
                    if (empty($pubfile) || ($file->get_id() == $pubfile->fileid)) {
                        // Everything's alright, we can delete the old file!
                        $file->delete();
                    } else {
                        // Something unexcpected happened!
                        throw new coding_exception('Mismatching fileids (pubfile with id ' . $pubfile->fileid .
                                ' and stored file ' .
                                $file->get_id() . '!');
                    }
                }
            } else if (!empty($formattedtext)) {
                // There exists no such file, so we create one!
                $createnew = true;
            }

            if ($createnew === true) {
                // We gotta create a new one!
                $newfilerecord = new stdClass();
                $newfilerecord->contextid = $contextid;
                $newfilerecord->component = 'mod_publication';
                $newfilerecord->filearea = 'attachment';
                $newfilerecord->itemid = $itemid;
                $newfilerecord->filename = $filename;
                $newfilerecord->filepath = '/';
                $newfile = $fs->create_file_from_string($newfilerecord, $submissioncontent);
                if (!$pubfile) {
                    $pubfile = new stdClass();
                    $pubfile->userid = $itemid;
                    $pubfile->type = PUBLICATION_MODE_ONLINETEXT;
                    $pubfile->publication = $publicationid;
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

    /**
     * Format file content of imported onlinetexts to be rendered as preview.
     *
     * @param int $itemid User's or group's ID
     * @param int $publicationid Publication instance's database ID
     * @param int $contextid Publication instance's context ID
     * @return string formatted HTML snippet ready to be output
     */
    public static function export_onlinetext_for_preview($itemid, $publicationid, $contextid) {
        global $DB;

        // Get file data/record!
        $conditions = [
                'publication' => $publicationid,
                'userid' => $itemid,
                'type' => PUBLICATION_MODE_ONLINETEXT
        ];
        if (!$pubfile = $DB->get_record('publication_file', $conditions, '*')) {
            return '';
        }

        $fs = get_file_storage();
        $file = $fs->get_file_by_id($pubfile->fileid);
        $content = $file->get_content();

        // Correct ressources filepaths for onine-view!
        $resources = $fs->get_directory_files($contextid,
                'mod_publication',
                'attachment',
                $itemid,
                '/resources/',
                true,
                false);
        foreach ($resources as $resource) {
            // TODO watch the encoding of the file's names, in the event of core changing it, we have to change too!
            $filename = rawurlencode($resource->get_filename());
            $search = './resources/' . $filename;
            $replace = '@@PLUGINFILE@@/resources/' . $filename;
            $content = str_replace($search, $replace, $content);
        }
        $content = file_rewrite_pluginfile_urls($content, 'pluginfile.php', $contextid, 'mod_publication', 'attachment',
                $itemid, ['forcehttps' => true]);

        // Get only the body part!
        $start = strpos($content, '<body>');
        $length = strrpos($content, '</body>') - strpos($content, '<body>');
        if ($start !== false && $length > 0) {
            $content = substr($content, $start, $length);
        } else {
            $content = '';
        }

        return $content;

    }

    // Allowed file-types have been changed in Moodle 3.3 (and form element will probably change in Moodle 3.4 again)!

    /**
     * Get the type sets configured for this publication.
     * Adapted from assignsubmission_file!
     *
     * @return array('groupname', 'mime/type', ...)
     */
    public function get_configured_typesets() {
        $typeslist = (string)$this->instance->allowedfiletypes;

        $sets = self::get_typesets($typeslist);

        return $sets;
    }

    /**
     * Get the type sets passed.
     * Adapted from assignsubmission_file!
     *
     * @param string $types The space , ; separated list of types
     * @return array('groupname', 'mime/type', ...)
     */
    public static function get_typesets($types) {
        $sets = [];
        if (!empty($types)) {
            $sets = preg_split('/[\s,;:"\']+/', $types, null, PREG_SPLIT_NO_EMPTY);
        }

        return $sets;
    }

    /**
     * Return the accepted types list for the file manager component.
     * Adapted from assignsubmission_file!
     *
     * @return array|string
     */
    public function get_accepted_types() {
        $acceptedtypes = $this->get_configured_typesets();

        if (!empty($acceptedtypes)) {
            return $acceptedtypes;
        }

        return '*';
    }

    /**
     * List the nonexistent file types that need to be removed.
     * Adapted from assignsubmission_file!
     *
     * @param string $types space , or ; separated types
     * @return array A list of the nonexistent file types.
     */
    public static function get_nonexistent_file_types($types) {
        $nonexistent = [];
        foreach (self::get_typesets($types) as $type) {
            // If there's no extensions under that group, it doesn't exist.
            $extensions = file_get_typegroup('extension', [$type]);
            if (empty($extensions)) {
                $nonexistent[$type] = true;
            }
        }

        return array_keys($nonexistent);
    }
}
