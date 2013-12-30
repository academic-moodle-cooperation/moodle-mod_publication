<?php
// This plugin is for Moodle - http://moodle.org/
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
 *
 * @package mod_publication
 * @author Andreas Windbichler
 * @copyright TSC
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/mod/publication/locallib.php');

$id = required_param('id', PARAM_INT); // Course Module ID

$url = new moodle_url('/mod/publication/view.php', array('id' => $id));
$cm = get_coursemodule_from_id('publication', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

require_login($course, true, $cm);
$PAGE->set_url($url);

$context = context_module::instance($cm->id);

require_capability('mod/publication:view', $context);

$publication = new publication($context, $cm, $course);

add_to_log($course->id, "publication", "view", "view.php?id={$cm->id}", $id, $cm->id);


$pagetitle = strip_tags($course->shortname.': '.format_string($publication->get_instance()->name));

// Print the page header.
$PAGE->set_title($pagetitle);
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();

// Print the main part of the page.
echo $OUTPUT->heading(format_string($publication->get_instance()->name),1);

echo $publication->display_intro();
echo $publication->display_importlink();


$submissionid = $USER->id; //TODO use submission id

$files = new publication_files($context,$submissionid, 'attachment');

echo $OUTPUT->heading(get_string('myfiles','publication'), 3);
echo $PAGE->get_renderer('mod_publication')->render($files);

echo $publication->display_uploadlink();

echo $OUTPUT->footer();