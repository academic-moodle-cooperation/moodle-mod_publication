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
 * filestable/base.php
 *
 * @package       mod_publication
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Philipp Hager (office@phager.at)
 * @copyright     2016 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_publication\local\filestable;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/publication/locallib.php');
require_once($CFG->libdir.'/tablelib.php');

/**
 * Base class for tables showing my files or group files (upload or import)
 *
 * @package       mod_publication
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Philipp Hager (office@phager.at)
 * @copyright     2016 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class base extends \html_table {

    protected $publication = null;

    protected $fs = null;

    protected $files = null;
    protected $resources = null;

    protected $changepossible = false;

    public function __construct(\publication $publication) {

        $this->publication = $publication;

        $this->fs = get_file_storage();
    }

    public function init() {
        $files = $this->get_files();

        if (count($files) == 0 && has_capability('mod/publication:upload', $this->publication->get_context())) {
            return 0;
        }

        if (!isset($this->attributes)) {
            $this->attributes = array('class' => 'coloredrows');
        } else if (!isset($this->attributes['class'])) {
            $this->attributes['class'] = 'coloredrows';
        } else {
            $this->attributes['class'] .= ' coloredrows';
        }

        $this->options = array();
        $this->options[2] = get_string('student_approve', 'publication');
        $this->options[1] = get_string('student_reject', 'publication');

        foreach ($files as $file) {
            $this->data[] = $this->add_file($file);
        }

        return count($this->data);
    }

    public function add_file(\stored_file $file) {
        global $OUTPUT;

        $data = array();
        $data[] = $OUTPUT->pix_icon(file_file_icon($file), get_mimetype_description($file));

        $dlurl = new \moodle_url('/mod/publication/view.php', array('id'       => $this->publication->get_coursemodule()->id,
                                                                    'download' => $file->get_id()));
        $data[] = \html_writer::link($dlurl, $file->get_filename());

        // The specific data will be added in the child-classes!

        return $data;
    }

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

    public function changepossible() {
        return $this->changepossible ? true : false;
    }

}