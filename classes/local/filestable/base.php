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
 * Base class for tables showing files related to me (uploaded by me, imported from me or my group and options to approve them)
 *
 * @package       mod_publication
 * @author        Philipp Hager
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_publication\local\filestable;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/publication/locallib.php');
require_once($CFG->libdir . '/tablelib.php');

/**
 * Base class for tables showing my files or group files (upload or import)
 *
 * @package       mod_publication
 * @author        Philipp Hager
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class base extends \html_table {
    /** @var \publication publication object */
    protected $publication = null;
    /** @var \file_storage file storage object */
    protected $fs = null;
    /** @var \stored_file[] array of stored_file objects */
    protected $files = null;
    /** @var \stored_file[] array of stored_file objects used in onlinetexts */
    protected $resources = null;
    /** @var bool whether or not changes of approval are still possible */
    protected $changepossible = false;
    /** @var string[] select options */
    protected $options = [];
    protected $valid = '';
    protected $questionmark = '';
    protected $invalid = '';
    public $lastmodified = 0;

    /**
     * constructor
     *
     * @param \publication $publication publication object
     */
    public function __construct(\publication $publication) {
        global $OUTPUT;

        parent::__construct();

        $this->publication = $publication;

        $this->fs = get_file_storage();

        $this->valid = \mod_publication\local\allfilestable\base::approval_icon('check', 'text-success', false);
        $this->questionmark = \mod_publication\local\allfilestable\base::approval_icon('question', 'text-warning', false);
        $this->invalid = \mod_publication\local\allfilestable\base::approval_icon('times', 'text-danger', false);
    }

    /**
     * Initialize the table (get the files, style table, prepare options used for approval-selects, add files to table, etc.)
     *
     * @return int amount of files in table
     */
    public function init() {
        $files = $this->get_files();

        if ((!$files || count($files) == 0) && has_capability('mod/publication:upload', $this->publication->get_context())) {
            return 0;
        }

        if (!isset($this->attributes)) {
            $this->attributes = ['class' => 'coloredrows'];
        } else if (!isset($this->attributes['class'])) {
            $this->attributes['class'] = 'coloredrows';
        } else {
            $this->attributes['class'] .= ' coloredrows';
        }

        $this->options = [];
        $this->options[1] = get_string('student_approve', 'publication');
        $this->options[2] = get_string('student_reject', 'publication');

        if (empty($files) || count($files) == 0) {
            return 0;
        }

        foreach ($files as $file) {
            $this->data[] = $this->add_file($file);
        }

        return count($this->data);
    }

    /**
     * Add a single file to the table
     *
     * @param \stored_file $file Stored file instance
     * @return string[] Array of table cell contents
     */
    public function add_file(\stored_file $file) {
        global $OUTPUT;

        $data = [];
        $data[] = $OUTPUT->pix_icon(file_file_icon($file), get_mimetype_description($file));

        $dlurl = new \moodle_url('/mod/publication/view.php', [
                'id' => $this->publication->get_coursemodule()->id,
                'download' => $file->get_id(),
        ]);
        $data[] = \html_writer::link($dlurl, $file->get_filename());

        $data[] = $this->get_approval_status_for_file($file);

        // The specific data will be added in the child-classes!

        return $data;
    }

    public function get_approval_status_for_file($file) {
        global $OUTPUT;
        $templatecontext = new \stdClass;
        // Now add the specific data to the table!
        $teacherapproval = $this->publication->teacher_approval($file);
        $studentapproval = $this->publication->student_approval($file);

        $obtainteacherapproval = $this->publication->get_instance()->obtainteacherapproval;
        $obtainstudentapproval = $this->publication->get_instance()->obtainstudentapproval;

        $studentapproved = false;
        $studentdenied = false;
        $studentpending = false;
        $hint = '';
        if ($obtainstudentapproval == 1) {
            if ($studentapproval == 1) {
                $studentapproved = true;
                $hint = get_string('student_approved', 'publication');
            } else if ($studentapproval == 2) {
                $studentdenied = true;
                $hint = get_string('student_rejected', 'publication');
            } else {
                if ($this->publication->is_approval_open()) {
                    $this->changepossible = true;
                    return \html_writer::select($this->options, 'studentapproval[' . $file->get_id() . ']', $studentapproval);
                }
                $studentpending = true;
                $hint = get_string('student_pending', 'publication');
            }
        } else {
            $studentapproved = true;
            $hint = get_string('student_approved_automatically', 'publication');
        }

        $hint .= ' ';

        $teacherapproved = false;
        $teacherdenied = false;
        $teacherpending = false;

        if ($obtainteacherapproval == 1) {
            if ($teacherapproval == 1) {
                $teacherapproved = true;
                $hint .= get_string('teacher_approved', 'publication');
            } else if ($teacherapproval == 2) {
                $teacherdenied = true;
                $hint .= get_string('teacher_rejected', 'publication');
            } else {
                $teacherpending = true;
                $hint .= get_string('teacher_pending', 'publication');
            }
        } else {
            $teacherapproved = true;
            $hint .= get_string('teacher_approved_automatically', 'publication');
        }


        if ($studentapproved && $teacherapproved) {
            $templatecontext->icon = $this->valid;
        } else if ($studentdenied || $teacherdenied) {
            $templatecontext->icon = $this->invalid;
        } else {
            $templatecontext->icon = $this->questionmark;
        }
        $templatecontext->hint = $hint;
        return $OUTPUT->render_from_template('mod_publication/approval_icon', $templatecontext);
/*
        if ($teacherapproval && $this->publication->get_instance()->obtainstudentapproval) {
            $studentapproval = $this->publication->student_approval($file);
            if ($this->publication->is_open() && $studentapproval == 0) {
                $this->changepossible = true;
                $data[] = \html_writer::select($this->options, 'studentapproval[' . $file->get_id() . ']', $studentapproval);
                $templatecontext = false;
            } else {
                switch ($studentapproval) {
                    case 2:
                        $templatecontext->icon = $this->valid;
                        $templatecontext->hint = get_string('student_approved', 'publication');
                        break;
                    case 1:
                        $templatecontext->icon = $this->invalid;
                        $templatecontext->hint = get_string('student_rejected', 'publication');
                        break;
                    default:
                        $templatecontext->icon = $this->questionmark;
                        $templatecontext->hint = get_string('student_pending', 'publication');
                }
            }
        } else {
            switch ($teacherapproval) {
                case 1:
                    $templatecontext->icon = $this->valid;
                    $templatecontext->hint = get_string('teacher_approved', 'publication');
                    break;
                case 3:
                    $templatecontext->icon = $this->questionmark;
                    $templatecontext->hint = get_string('hidden', 'publication') . ' (' . get_string('teacher_pending', 'publication') . ')';
                    break;
                default:
                    $templatecontext->icon = $this->questionmark;
                    $templatecontext->hint = get_string('student_pending', 'publication');
            }
        }

        if ($templatecontext) {
            return $OUTPUT->render_from_template('mod_publication/approval_icon', $templatecontext);
        }
        return '';*/
    }

    /**
     * Get all files, in which the current user is involved
     *
     * @return \stored_file[] array of stored_files indexed by pathanmehash
     */
    public function get_files() {
        global $USER;

        if ($this->files !== null) {
            return $this->files;
        }

        $contextid = $this->publication->get_context()->id;
        $filearea = 'attachment';
        // User ID for regular instances, group id for assignments with teamsubmission!
        $itemid = $USER->id;

        $files = $this->fs->get_area_files($contextid, 'mod_publication', $filearea, $itemid, 'timemodified', false);

        foreach ($files as $file) {
            if ($file->get_filepath() == '/resources/') {
                $this->resources[] = $file;
            } else {
                $this->files[] = $file;
            }
            if ($this->lastmodified < $file->get_timemodified()) {
                $this->lastmodified = $file->get_timemodified();
            }
        }

        return $this->files;
    }

    /**
     * Returns if it's possible to change the approval
     *
     * @return bool
     */
    public function changepossible() {
        $result = ($this->changepossible ? true : false) && has_capability('mod/publication:upload',
                        $this->publication->get_context());
        return $result;
    }

}
