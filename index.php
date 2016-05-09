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
 * index.php
 *
 * @package       mod_publication
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Philipp Hager (office@phager.at)
 * @author        Andreas Windbichler
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/mod/publication/locallib.php');

$id = required_param('id', PARAM_INT);   // We need a course!

if (!$course = $DB->get_record('course', array('id' => $id))) {
    print_error('invalidcourseid');
}

require_course_login($course);
$PAGE->set_pagelayout('incourse');

$event = \mod_publication\event\course_module_instance_list_viewed::create(array(
        'context' => context_course::instance($course->id)
));
$event->trigger();

$strmodulenameplural = get_string('modulenameplural', 'publication');
$strmodulname = get_string('modulename', 'publication');
$strsectionname  = get_string('sectionname', 'format_'.$course->format);
$strname = get_string('name');
$strdesc = get_string('description');

$PAGE->set_url('/mod/publication/index.php', array('id' => $course->id));
$PAGE->navbar->add($strmodulenameplural);
$PAGE->set_title($strmodulenameplural);
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();
echo $OUTPUT->heading($strmodulname);

if (!$cms = get_coursemodules_in_course('publication', $course->id, 'cm.idnumber')) {
    notice(get_string('nopublicationsincourse', 'publication'), '../../course/view.php?id=' . $course->id);
    die;
}

$usesections = course_format_uses_sections($course->format);
if ($usesections) {
    $sections = get_fast_modinfo($course->id)->get_section_info_all();
}

$timenow = time();

$table = new html_table();

if ($usesections) {
    $table->head  = array ($strsectionname, $strname, $strdesc);
} else {
    $table->head  = array ($strname, $strdesc);
}

$currentsection = '';

$modinfo = get_fast_modinfo($course);
foreach ($modinfo->instances['publication'] as $cm) {
    if (!$cm->uservisible) {
        continue;
    }

    // Show dimmed if the mod is hidden!
    $class = $cm->visible ? '' : 'dimmed';

    $link = html_writer::tag('a', format_string($cm->name), array('href' => 'view.php?id='.$cm->id, 'class' => $class));

    $printsection = '';
    if ($usesections) {
        if ($cm->sectionnum !== $currentsection) {
            if ($cm->sectionnum) {
                $printsection = get_section_name($course, $sections[$cm->sectionnum]);
            }
            if ($currentsection !== '') {
                $table->data[] = 'hr';
            }
            $currentsection = $cm->sectionnum;
        }
    }

    $publication = new publication($cm, $course);
    $desc = $publication->get_instance()->intro;

    if ($usesections) {
        $table->data[] = array ($printsection, $link, $desc);
    } else {
        $table->data[] = array ($link, $desc);
    }
}

echo html_writer::empty_tag('br');

echo html_writer::table($table);

echo $OUTPUT->footer();
