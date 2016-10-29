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
 * filestable/group.php
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

/**
 * Table showing my group files
 *
 * @package       mod_publication
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Philipp Hager (office@phager.at)
 * @copyright     2016 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class group extends base {

    protected $groupingid = 0;

    public function add_file(\stored_file $file) {
        // The common columns!
        $data = parent::add_file($file);

        // Now add the specific data to the table!
        // TODO: copied from import class, adapt to multiple students being involved!
        $teacherapproval = $this->teacher_approval($file);
        if ($teacherapproval && $this->publication->get_instance()->obtainstudentapproval) {
            $studentapproval = $this->student_approval($file);
            if ($publication->is_open() && $studentapproval == 0) {
                $this->changepossible = true;
                $data[] = \html_writer::select($options, 'studentapproval[' . $file->get_id()  . ']', $studentapproval);
            } else {
                switch($studentapproval) {
                    case 2:
                        $data[] = get_string('student_approved', 'publication');
                        break;
                    case 1:
                        $data[] = get_string('student_rejected', 'publication');
                        break;
                    default:
                        $data[] = get_string('student_pending', 'publication');
                }
            }
        } else {
            switch($teacherapproval) {
                case 1:
                    $data[] = get_string('teacher_approved', 'publication');
                    break;
                default:
                    $data[] = get_string('student_pending', 'publication');
            }
        }

        return $data;
    }

    public function get_files() {
        global $USER, $DB;

        if ($this->files !== null) {
            return $this->files;
        }

        $contextid = $this->publication->get_context()->id;
        $filearea = 'attachment';

        // Get the current users subission group id(s)!
        $groupingid = 0;

        /* OK, assign is a little bit inconsistent with implementation and doc-comments, it states it will return false for user's
         * group if there's no group or multiple groups, instead it uses just the first group it finds for the user!
         * So if assign doesn't behave that exact, we just use all users groups (except there's a groupingid set for submission! */
        $assignid = $this->publication->get_instance()->importfrom;
        $this->groupingid = $DB->get_field('assign', 'teamsubmissiongroupingid', array('id' => $assignid));
        $groups = groups_get_all_groups($this->publication->get_instance()->course, $USER->id, $this->groupingid);
        if (empty($groups)) {
            // Users without group membership get assigned group id 0!
            $groups = array(0);
        }

        foreach ($groups as $group) {
            $itemid = $group->id;

            $files = $this->fs->get_area_files($contextid, 'mod_publication', $filearea, $itemid, 'timemodified', false);

            foreach ($files as $file) {
                if ($file->get_filepath() == '/resources/') {
                    $this->resources[] = $file;
                } else {
                    $this->files[] = $file;
                }
            }
        }

        return $this->files;
    }
}
