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
 * Handles file uploads by students!
 *
 * @package       mod_publication
 * @author        Philipp Hager
 * @author        Andreas Windbichler
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/mod/publication/locallib.php');
require_once($CFG->dirroot . '/mod/publication/upload_form.php');

$cmid = required_param('cmid', PARAM_INT); // Course Module ID.
$id = optional_param('id', 0, PARAM_INT); // EntryID.

if (!$cm = get_coursemodule_from_id('publication', $cmid)) {
    print_error('invalidcoursemodule');
}

if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
    print_error('coursemisconf');
}

require_login($course, false, $cm);

$context = context_module::instance($cm->id);

require_capability('mod/publication:upload', $context);

$publication = new publication($cm, $course, $context);


$url = new moodle_url('/mod/publication/upload.php', array('cmid' => $cm->id));
if (!empty($id)) {
    $url->param('id', $id);
}

$PAGE->set_url($url);

$entry = new stdClass();
$entry->id = $USER->id;

$entry->definition       = '';          // Updated later.
$entry->definitionformat = FORMAT_HTML; // Updated later.

$maxfiles = $publication->get_instance()->maxfiles;
$maxbytes = $publication->get_instance()->maxbytes;

// Patch accepted filetypes.
if (isset($publication->get_instance()->allowedfiletypes) AND !empty($publication->get_instance()->allowedfiletypes)) {
    $acceptedfiletypes = explode(', ', strtolower($publication->get_instance()->allowedfiletypes));
    foreach ($acceptedfiletypes as &$a) {
        $a = '.'.trim($a); // Conversion to fe *.jpg...
    }
} else {
    $acceptedfiletypes = array('*');
}


$definitionoptions = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => $maxfiles,
        'maxbytes' => $maxbytes, 'context' => $context, 'accepted_types' => $acceptedfiletypes);
$attachmentoptions = array('subdirs' => false, 'maxfiles' => $maxfiles,
        'maxbytes' => $maxbytes, 'accepted_types' => $acceptedfiletypes);

$entry = file_prepare_standard_editor($entry, 'definition', $definitionoptions, $context, 'mod_publication', 'entry', $entry->id);
$entry = file_prepare_standard_filemanager($entry, 'attachment', $attachmentoptions,
             $context, 'mod_publication', 'attachment', $entry->id);

$entry->cmid = $cm->id;

// Create a new form object (found in lib.php).
$mform = new mod_publication_upload_form(null, array('current' => $entry, 'cm' => $cm, 'publication' => $publication,
        'definitionoptions' => $definitionoptions, 'attachmentoptions' => $attachmentoptions));


if ($mform->is_cancelled()) {
    redirect(new moodle_url('/mod/publication/view.php', array('id' => $cm->id)));

} else if ($data = $mform->get_data()) {
    // Store updated set of files.

    // Save and relink embedded images and save attachments.
    $entry = file_postupdate_standard_editor($entry, 'definition', $definitionoptions,
            $context, 'mod_publication', 'entry', $entry->id);
    $entry = file_postupdate_standard_filemanager($entry, 'attachment', $attachmentoptions,
            $context, 'mod_publication', 'attachment', $entry->id);

    $filearea = 'attachment';
    $sid = $USER->id;
    $fs = get_file_storage();

    $files = $fs->get_area_files($context->id, 'mod_publication', $filearea, $sid, 'timemodified', false);

    $values = array();
    foreach ($files as $file) {
        $values[] = $file->get_id();
    }

    $rows = $DB->get_records('publication_file', array('publication' => $publication->get_instance()->id, 'userid' => $USER->id));

    // Find new files and store in db.
    foreach ($files as $file) {
        $found = false;

        foreach ($rows as $row) {
            if ($row->fileid == $file->get_id()) {
                $found = true;
            }
        }

        if (!$found) {
            $dataobject = new stdClass();
            $dataobject->publication = $publication->get_instance()->id;
            $dataobject->userid = $USER->id;
            $dataobject->timecreated = $file->get_timecreated();
            $dataobject->fileid = $file->get_id();
            $dataobject->studentapproval = 1; // Upload always means user approves.
            $dataobject->filename = $file->get_filename();
            $dataobject->type = PUBLICATION_MODE_UPLOAD;

            $DB->insert_record('publication_file', $dataobject);
        }
    }

    // Find deleted files and update db.
    foreach ($rows as $idx => $row) {
        $found = false;
        foreach ($files as $file) {
            if ($file->get_id() == $row->fileid) {
                $found = true;
                continue;
            }
        }

        if (!$found) {
            $DB->delete_records('publication_file', array('id' => $row->id));
        }
    }

    redirect(new moodle_url('/mod/publication/view.php', array('id' => $cm->id)));
}

// Load existing files into draft area.

echo $OUTPUT->header();

echo $OUTPUT->heading(format_string($publication->get_instance()->name));

$publication->display_intro();

$mform->display();

echo $OUTPUT->footer();
