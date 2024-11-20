<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Contains the class for fetching the important dates in mod_assign for a given module instance and a user.
 *
 * @package   mod_publication
 * @copyright 2022 Simeon Naydenov (moniNaydenov@gmail.com)
 * @copyright 2022 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace mod_publication;

use core\activity_dates;

/**
 * Class for fetching the important dates in mod_assign for a given module instance and a user.
 *
 * @copyright 2021 Shamim Rezaie <shamim@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dates extends activity_dates {

    /**
     * Returns a list of important dates in mod_assign
     *
     * @return array
     */
    protected function get_dates(): array {
        global $CFG, $USER;
        require_once($CFG->dirroot . '/mod/publication/locallib.php');

        $course = get_course($this->cm->course);
        $context = \context_module::instance($this->cm->id);

        $publication = new \publication($this->cm, $course, $context);
        $instance = $publication->get_instance();

        $textsuffix = ($instance->mode == PUBLICATION_MODE_IMPORT) ? "_import" : "_upload";
        $dates = [];

        if ($instance->allowsubmissionsfromdate) {
            $dates[] = [
                'label' => get_string('allowsubmissionsfromdate' . $textsuffix, 'publication') . ':',
                'timestamp' => $instance->allowsubmissionsfromdate,
            ];
        }
        if ($instance->duedate) {
            $dates[] = [
                'label' => get_string('duedate' . $textsuffix, 'publication') . ':',
                'timestamp' => $instance->duedate,
            ];
        }

        $extensionduedate = $publication->user_extensionduedate($USER->id);

        if ($extensionduedate) {
            $dates[] = [
                'label' => get_string('extensionto', 'publication') . ':',
                'timestamp' => $extensionduedate,
            ];
        }
        return $dates;
    }
}
