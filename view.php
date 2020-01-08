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
 * Displays a single mod_publication instance
 *
 * @package       mod_publication
 * @author        Philipp Hager
 * @author        Andreas Windbichler
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/mod/publication/locallib.php');
require_once($CFG->dirroot . '/mod/publication/mod_publication_files_form.php');
require_once($CFG->dirroot . '/mod/publication/mod_publication_allfiles_form.php');

$id = required_param('id', PARAM_INT); // Course Module ID.

$url = new moodle_url('/mod/publication/view.php', ['id' => $id]);
$cm = get_coursemodule_from_id('publication', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);

require_login($course, true, $cm);
$PAGE->set_url($url);

$context = context_module::instance($cm->id);

require_capability('mod/publication:view', $context);

$publication = new publication($cm, $course, $context);

$event = \mod_publication\event\course_module_viewed::create([
    'objectid' => $PAGE->cm->instance,
    'context' => $PAGE->context,
]);
$event->add_record_snapshot('course', $PAGE->course);
$event->trigger();

$pagetitle = strip_tags($course->shortname . ': ' . format_string($publication->get_instance()->name));
$action = optional_param('action', 'view', PARAM_ALPHA);
$savevisibility = optional_param('savevisibility', false, PARAM_RAW);

$download = optional_param('download', 0, PARAM_INT);
if ($download > 0) {
    $publication->download_file($download);
}

if ($savevisibility) {
    require_capability('mod/publication:approve', $context);

    $files = optional_param_array('files', [], PARAM_INT);

    $params = [];

    $params['pubid'] = $publication->get_instance()->id;

    foreach ($files as $fileid => $val) {
        $x = $DB->get_record('publication_file', array('fileid' => $fileid), $fields = "fileid,userid,teacherapproval,filename");

        $oldval = $x->teacherapproval;

        if ($val != $oldval) {
            $newstatus = ($val == 2 || ($val == 3 && !$publication->get_instance()->obtainteacherapproval)) ? '' : 'not';
            $logstatus = $newstatus;
            $user = $DB->get_record('user', array('id' => $x->userid));
            $group = false;

            if (($publication->get_instance()->mode == PUBLICATION_MODE_IMPORT)
                && $DB->get_field('assign', 'teamsubmission', ['id' => $publication->get_instance()->importfrom])) {
                $logstatus = $newstatus." (Teacher) ";
                $group = $x->userid;
            }

            $dataforlog = new stdClass();
            $dataforlog->publication = $params['pubid'];
            $dataforlog->approval = $logstatus." approved";
            $dataforlog->userid = $USER->id;
            $dataforlog->reluser = $user->id;
            $dataforlog->fileid = $fileid;

            try {
                \mod_publication\event\publication_approval_changed::approval_changed($cm, $dataforlog)->trigger();
            } catch (coding_exception $e) {
                throw new Exception("Coding exception while sending notification: " . $e->getMessage());
            }

            $DB->set_field('publication_file', 'teacherapproval', $val, ['fileid' => $fileid]);

            if ($publication->get_instance()->notifystudents) {
                $strsubmitted = get_string('approvalchange', 'publication');

                if ($group) {
                    $select = 'groupid = :id';
                    $params = array('id' => $group);
                    $usersingroup = $DB->get_records_select('groups_members', $select, $params, '', 'userid');
                    foreach ($usersingroup as $u) {
                        $user = $DB->get_record('user', array('id' => $u->userid));
                        $publication::send_student_notification_approval_changed($cm, $u, $USER, $newstatus, $x, $id, $publication);
                    }
                } else {
                    $publication::send_student_notification_approval_changed($cm, $user, $USER, $newstatus, $x, $id, $publication);
                }
            }
        }
    }

} else if ($action == "zip") {
    $publication->download_zip(true);
} else if ($action == "zipusers") {
    $users = optional_param_array('selectedeuser', false, PARAM_INT);
    if (!$users) {
        // No users selected.
        header("Location: view.php?id=" . $id);
        die();
    }
    $users = array_keys($users);
    $publication->download_zip($users);

} else if ($action == "import") {
    require_capability('mod/publication:approve', $context);
    require_sesskey();

    if (!isset($_POST['confirm'])) {
        $message = get_string('updatefileswarning', 'publication');

        echo $OUTPUT->header();
        echo $OUTPUT->heading(format_string($publication->get_instance()->name), 1);
        echo $OUTPUT->confirm($message, 'view.php?id=' . $id . '&action=import&confirm=1', 'view.php?id=' . $id);
        echo $OUTPUT->footer();
        exit;
    }

    $publication->importfiles();
} else if ($action == "grantextension") {
    require_capability('mod/publication:grantextension', $context);

    $users = optional_param_array('selectedeuser', [], PARAM_INT);
    $users = array_keys($users);

    if (count($users) > 0) {
        $url = new moodle_url('/mod/publication/grantextension.php', ['id' => $cm->id]);
        foreach ($users as $idx => $u) {
            $url->param('userids[' . $idx . ']', $u);
        }

        redirect($url);
        die();
    }
} else if ($action == "approveusers" || $action == "rejectusers") {
    require_capability('mod/publication:approve', $context);

    $users = optional_param_array('selectedeuser', [], PARAM_INT);
    $users = array_keys($users);

    if (count($users) > 0) {

        list($usersql, $params) = $DB->get_in_or_equal($users, SQL_PARAMS_NAMED, 'user');
        $approval = ($action == "approveusers") ? 1 : 0;
        $params['pubid'] = $publication->get_instance()->id;
        $select = ' publication=:pubid AND userid ' . $usersql;
        $DB->set_field_select('publication_file', 'teacherapproval', $approval, $select, $params);
    }
} else if ($action == "resetstudentapproval") {
    require_capability('mod/publication:approve', $context);

    $users = optional_param_array('selectedeuser', [], PARAM_INT);
    $users = array_keys($users);

    if (count($users) > 0) {

        list($usersql, $params) = $DB->get_in_or_equal($users, SQL_PARAMS_NAMED, 'user');
        $select = ' publication=:pubid AND userid ' . $usersql;
        $params['pubid'] = $publication->get_instance()->id;

        $DB->set_field_select('publication_file', 'studentapproval', null, $select, $params);

        if (($publication->get_instance()->mode == PUBLICATION_MODE_IMPORT)
            && $DB->get_field('assign', 'teamsubmission', ['id' => $publication->get_instance()->importfrom])) {
            $fileids = $DB->get_fieldset_select('publication_file', 'id', $select, $params);
            if (count($fileids) == 0) {
                $fileids = [-1];
            }

            $groups = $users;
            $users = [];
            foreach ($groups as $cur) {
                $members = $publication->get_submissionmembers($cur);
                $users = array_merge($users, array_keys($members));
            }
            if (count($users) > 0) { // Attention, now we have real users! Above they may be groups!
                list($usersql, $userparams) = $DB->get_in_or_equal($users, SQL_PARAMS_NAMED, 'user');
                list($filesql, $fileparams) = $DB->get_in_or_equal($fileids, SQL_PARAMS_NAMED, 'file');
                $select = ' fileid ' . $filesql . ' AND userid ' . $usersql;
                $params = $fileparams + $userparams;
                $DB->set_field_select('publication_groupapproval', 'approval', null, $select, $params);
            }
        }
    }
}

$submissionid = $USER->id;

$filesform = new mod_publication_files_form(null,
    ['publication' => $publication, 'sid' => $submissionid, 'filearea' => 'attachment']);

if ($data = $filesform->get_data() && $publication->is_open()) {
    $datasubmitted = $filesform->get_submitted_data();

    if (isset($datasubmitted->gotoupload)) {
        redirect(new moodle_url('/mod/publication/upload.php',
            ['id' => $publication->get_instance()->id, 'cmid' => $cm->id]));
    }

    $studentapproval = optional_param_array('studentapproval', [], PARAM_INT);

    $conditions = [];
    $conditions['publication'] = $publication->get_instance()->id;
    $conditions['userid'] = $USER->id;

    $pubfileids = $DB->get_records_menu('publication_file', ['publication' => $publication->get_instance()->id],
        'id ASC', 'fileid, id');

    // Update records.
    foreach ($studentapproval as $idx => $approval) {
        $conditions['fileid'] = $idx;

        $approval = ($approval >= 1) ? $approval - 1 : null;

        $dataforlog->approval = $approval == 1 ? 'approved' : 'rejected';

        if (($publication->get_instance()->mode == PUBLICATION_MODE_IMPORT)
            && $DB->get_field('assign', 'teamsubmission', ['id' => $publication->get_instance()->importfrom])) {
            /* We have to deal with group approval! The method sets group approval for the specified user
             * and returns current cumulated group approval (and it also sets it in publication_file table)! */
            $stats = $publication->set_group_approval($approval, $pubfileids[$idx], $USER->id);

            $dataforlog->approval = '(Students '.$stats['approving'].' out of '.$stats['needed'].') '.$dataforlog->approval;
        } else {
            $DB->set_field('publication_file', 'studentapproval', $approval, $conditions);
        }

        $dataforlog->publication = $conditions['publication'];
        $dataforlog->userid = $USER->id;
        $dataforlog->reluser = $USER->id;
        $dataforlog->fileid = $idx;

        \mod_publication\event\publication_approval_changed::approval_changed($cm, $dataforlog)->trigger();
    }
}

$filesform = new mod_publication_files_form(null,
    ['publication' => $publication, 'sid' => $submissionid, 'filearea' => 'attachment']);

// Print the page header.
$PAGE->set_title($pagetitle);
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();

// Print the main part of the page.
echo $OUTPUT->heading(format_string($publication->get_instance()->name), 1);

$publication->display_intro();
$publication->display_availability();
$publication->display_importlink();

$filesform->display();

$publication->display_allfilesform();

echo $OUTPUT->footer();
