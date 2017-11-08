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

$id = required_param('id', PARAM_INT); // Course Module ID.
$itemid = required_param('itemid', PARAM_INT); // Item-ID (group- or user-ID).
$itemname = optional_param('itemname', false, PARAM_TEXT); // Item-Name to save DB access!

$url = new moodle_url('/mod/publication/onlinetextpreview.php', ['id' => $id, 'itemid' => $itemid]);
$cm = get_coursemodule_from_id('publication', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);

require_login($course, true, $cm);
$PAGE->set_url($url);

$context = context_module::instance($cm->id);

require_capability('mod/publication:view', $context);

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('preview') . ' ' . get_string('onlinetextfilename', 'assignsubmission_onlinetext') .
        ($itemname ? ' ' . strtolower(get_string('from')) . ' ' . $itemname : ''));

echo publication::export_onlinetext_for_preview($itemid, $cm->instance, $context->id);

echo $OUTPUT->footer();
