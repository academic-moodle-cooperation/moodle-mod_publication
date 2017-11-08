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

    /**
     * constructor
     *
     * @param \publication $publication publication object
     */
    public function __construct(\publication $publication) {
        parent::__construct();

        $this->publication = $publication;

        $this->fs = get_file_storage();
    }

    /**
     * Initialize the table (get the files, style table, prepare options used for approval-selects, add files to table, etc.)
     *
     * @return int amount of files in table
     */
    public function init() {
        $files = $this->get_files();

        if (count($files) == 0 && has_capability('mod/publication:upload', $this->publication->get_context())) {
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
        $this->options[2] = get_string('student_approve', 'publication');
        $this->options[1] = get_string('student_reject', 'publication');

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
                'download' => $file->get_id()
        ]);
        $data[] = \html_writer::link($dlurl, $file->get_filename());

        // The specific data will be added in the child-classes!

        return $data;
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
        }

        return $this->files;
    }

    /**
     * Returns if it's possible to change the approval
     *
     * @return bool
     */
    public function changepossible() {
        return $this->changepossible ? true : false;
    }

}
