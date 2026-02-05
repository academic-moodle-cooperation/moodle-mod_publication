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
require_once(__DIR__ . '/overrides_form.php');

$id = required_param('id', PARAM_INT); // Course Module ID.
$overrideid = required_param('overrideid', PARAM_INT);
$confirm = optional_param('confirm', false, PARAM_BOOL);

$url = new moodle_url('/mod/publication/overrides_delete.php', ['id' => $id, 'overrideid' => $overrideid]);
$cm = get_coursemodule_from_id('publication', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);

require_login($course, true, $cm);
$PAGE->set_url($url);

$context = context_module::instance($cm->id);

$backurl = new moodle_url('/mod/publication/overrides.php', ['id' => $id]);
require_capability('mod/publication:manageoverrides', $context);

$publication = new publication($cm, $course, $context);

$override = $publication->override_get($overrideid);

if (!$override) {
    redirect($backurl, 'Invalid override id');
}

if ($confirm) {
    require_sesskey();
    $publication->override_delete($overrideid);
    $eventparams = [
        'context' => $context,
        'other' => [
            'publication' => $publication->get_instance()->id,
        ],
    ];
    $eventparams['objectid'] = $override->id;
    if ($publication->get_mode() == PUBLICATION_MODE_ASSIGN_TEAMSUBMISSION) {
        $eventparams['other']['groupid'] = $override->groupid;
        $event = \mod_publication\event\group_override_deleted::create($eventparams);
    } else {
        $eventparams['relateduserid'] = $override->userid;
        $event = \mod_publication\event\user_override_deleted::create($eventparams);
    }
    $event->trigger();
    redirect($backurl, get_string('override:delete:success', 'mod_publication'));
}

$pagetitle = strip_tags($course->shortname . ': ' . format_string($publication->get_instance()->name));

// Print the page header.
$PAGE->set_pagelayout('admin');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($course->fullname);
$PAGE->add_body_class('limitedwidth');
$activityheader = $PAGE->activityheader;
$activityheader->set_attrs([
    'description' => '',
    'hidecompletion' => true,
    'title' => $activityheader->is_title_allowed() ?
        format_string($publication->get_instance()->name, true, ['context' => $context]) : "",
]);

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('overrides', 'mod_assign'), 2);

$mode = $publication->get_mode();
$confirmstrcontext = new stdClass();
if ($mode == PUBLICATION_MODE_ASSIGN_TEAMSUBMISSION) {
    $confirmstrcontext->userorgroup = get_string('group');
    $group = $DB->get_record('groups', ['id' => $override->groupid]);
    if ($group) {
        $confirmstrcontext->fullname = $group->name;
    } else {
        $confirmstrcontext->fullname = 'N/A';
    }
} else {
    $confirmstrcontext->userorgroup = get_string('user');
    $user = $DB->get_record('user', ['id' => $override->userid]);
    if ($user) {
        $confirmstrcontext->fullname = fullname($user);
    } else {
        $confirmstrcontext->fullname = 'N/A';
    }
}

$confirmstr = get_string('override:delete:ask', 'mod_publication', $confirmstrcontext);
$confirmurl = new moodle_url($url, ['id' => $id, 'overrideid' => $overrideid, 'confirm' => 1, 'sesskey' => sesskey()]);

echo $OUTPUT->confirm($confirmstr, $confirmurl, $backurl);

echo $OUTPUT->footer();
