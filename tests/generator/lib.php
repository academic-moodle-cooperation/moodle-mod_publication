<?php
// This file is part of mod_publication for Moodle - http://moodle.org/
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
 * Generator file for mod_publication's PHPUnit tests
 *
 * @package   mod_publication
 * @category  test
 * @author    Philipp Hager
 * @copyright 2017 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * publication module data generator class
 *
 * @package   mod_publication
 * @category  test
 * @author    Philipp Hager
 * @copyright 2017 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_publication_generator extends testing_module_generator {

    /**
     * Generator method creating a mod_publication instance.
     *
     * @param array|stdClass $record (optional) Named array containing instance settings
     * @param array $options (optional) general options for course module. Can be merged into $record
     * @return stdClass record from module-defined table with additional field cmid (corresponding id in course_modules table)
     */
    public function create_instance($record = null, array $options = null) {
        $record = (object)(array)$record;

        $timecreated = time();

        $defaultsettings = array(
            'name' => 'publication',
            'intro' => 'Introtext',
            'introformat' => 1,
            'alwaysshowdescription' => 1,
            'timecreated' => $timecreated,
            'timemodified' => $timecreated,
            'duedate' => $timecreated + 604800, // 1 week later!
            'allowsubmissionsfromdate' => $timecreated,
            'cutoffdate' => 0,
            'mode' => 0, // Equals PUBLICATION_MODE_UPLOAD!
            'importfrom' => -1,
            'autoimport' => 1,
            'obtainstudentapproval' => 1,
            'groupapproval' => 0, // Equals PUBLICATION_APPROVAL_ALL!
            'maxfiles' => 5,
            'maxbytes' => 2,
            'allowedfiletypes' => '',
            'obtainteacherapproval' => 1,
            'groupmode' => SEPARATEGROUPS,
        );

        foreach ($defaultsettings as $name => $value) {
            if (!isset($record->{$name})) {
                $record->{$name} = $value;
            }
        }

        return parent::create_instance($record, (array)$options);
    }
}
