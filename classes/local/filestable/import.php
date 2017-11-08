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
 * Contains class for files table listing files imported from oneself (and options for approving them)
 *
 * @package       mod_publication
 * @author        Philipp Hager
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_publication\local\filestable;

defined('MOODLE_INTERNAL') || die();

/**
 * Table showing my imported files
 *
 * @package       mod_publication
 * @author        Philipp Hager
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class import extends base {
    /**
     * Add a single file to the table
     *
     * @param \stored_file $file Stored file instance
     * @return string[] Array of table cell contents
     */
    public function add_file(\stored_file $file) {
        // The common columns!
        $data = parent::add_file($file);

        // Now add the specific data to the table!
        $teacherapproval = $this->publication->teacher_approval($file);
        if ($teacherapproval && $this->publication->get_instance()->obtainstudentapproval) {
            $studentapproval = $this->publication->student_approval($file);
            if ($this->publication->is_open() && $studentapproval == 0) {
                $this->changepossible = true;
                $data[] = \html_writer::select($this->options, 'studentapproval[' . $file->get_id() . ']', $studentapproval);
            } else {
                switch ($studentapproval) {
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
            switch ($teacherapproval) {
                case 1:
                    $data[] = get_string('teacher_approved', 'publication');
                    break;
                default:
                    $data[] = get_string('student_pending', 'publication');
            }
        }

        return $data;
    }
}
