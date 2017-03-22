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
 * Contains class for files table listing all files in import mode
 *
 * @package       mod_publication
 * @author        Philipp Hager
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_publication\local\allfilestable;

defined('MOODLE_INTERNAL') || die();

/**
 * Table showing all imported files
 *
 * @package       mod_publication
 * @author        Philipp Hager
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class import extends base {
    /**
     * Return all columns, column-headers and helpicons for this table
     *
     * @return array Array with column names, column headers and help icons
     */
    public function get_columns() {
        list($columns, $headers, $helpicons) = parent::get_columns();

        if (has_capability('mod/publication:approve', $this->context)) {
            if ($this->publication->get_instance()->obtainstudentapproval) {
                $columns[] = 'studentapproval';
                $headers[] = get_string('studentapproval', 'publication');
                $helpicons[] = new \help_icon('studentapproval', 'publication');
            }
            $columns[] = 'teacherapproval';
            if ($this->publication->get_instance()->obtainstudentapproval) {
                $headers[] = get_string('obtainstudentapproval', 'publication');
            } else {
                $headers[] = get_string('teacherapproval', 'publication');
            }
            $helpicons[] = null;

            $columns[] = 'visibleforstudents';
            $headers[] = get_string('visibleforstudents', 'publication');
            $helpicons[] = null;
        }

        return array($columns, $headers, $helpicons);
    }
}
