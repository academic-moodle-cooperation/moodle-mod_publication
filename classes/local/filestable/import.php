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
 * filestable/import.php
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
 * Table showing my imported files
 *
 * @package       mod_publication
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Philipp Hager (office@phager.at)
 * @copyright     2016 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class import extends base {
    public function add_file(\stored_file $file) {
        // The common columns!
        $data = parent::add_file($file);

        // Now add the specific data to the table!
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
}
