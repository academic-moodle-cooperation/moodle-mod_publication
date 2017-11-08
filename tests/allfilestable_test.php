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
 * Unit tests for mod_publication's allfilestable classes.
 *
 * @package   mod_publication
 * @author    Philipp Hager
 * @copyright 2017 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from a Moodle page!
}

// Make sure the code being tested is accessible.
global $CFG;
require_once($CFG->dirroot . '/mod/publication/locallib.php'); // Include the code to test!

/**
 * This class contains the test cases for the formular validation.
 *
 * @package   mod_publication
 * @author    Philipp Hager
 * @copyright 2017 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class publication_allfilestable_test extends advanced_testcase {
    /** Default number of students to create */
    const DEFAULT_STUDENT_COUNT = 50;
    /** Default number of teachers to create */
    const DEFAULT_TEACHER_COUNT = 6;
    /** Default number of editing teachers to create */
    const DEFAULT_EDITING_TEACHER_COUNT = 6;
    /** Number of timestamps to create */
    const DEFAULT_TIMESTAMP_COUNT = 6;
    /** Number of groups to create */
    const GROUP_COUNT = 6;

    /** @var stdClass $course New course created to hold the assignments */
    protected $course = null;

    /** @var array $teachers List of DEFAULT_TEACHER_COUNT teachers in the course */
    protected $teachers = null;

    /** @var array $editingteachers List of DEFAULT_EDITING_TEACHER_COUNT editing teachers in the course */
    protected $editingteachers = null;

    /** @var array $students List of DEFAULT_STUDENT_COUNT students in the course */
    protected $students = null;

    /** @var array $groups List of 10 groups in the course */
    protected $groups = null;

    /** @var array $timestamps List of 10 different timestamps */
    protected $timestamps = null;

    /**
     * Setup function - we will create a course and add an tmt instance to it.
     */
    protected function setUp() {
        global $DB;

        $this->resetAfterTest(true);

        $this->course = self::getDataGenerator()->create_course();
        $this->teachers = [];
        for ($i = 0; $i < self::DEFAULT_TEACHER_COUNT; $i++) {
            array_push($this->teachers, self::getDataGenerator()->create_user());
        }

        $this->editingteachers = [];
        for ($i = 0; $i < self::DEFAULT_EDITING_TEACHER_COUNT; $i++) {
            array_push($this->editingteachers, self::getDataGenerator()->create_user());
        }

        $this->students = [];
        for ($i = 0; $i < self::DEFAULT_STUDENT_COUNT; $i++) {
            array_push($this->students, self::getDataGenerator()->create_user());
        }

        $this->groups = [];
        for ($i = 0; $i < self::GROUP_COUNT; $i++) {
            array_push($this->groups, self::getDataGenerator()->create_group(['courseid' => $this->course->id]));
        }

        $this->timestamps = [];
        for ($i = 0; $i < self::DEFAULT_TIMESTAMP_COUNT; $i++) {
            $hour = rand(0, 23);
            $minute = rand(0, 60);
            $second = rand(0, 60);
            $month = rand(1, 12);
            $day = rand(0, 31);
            $year = rand(1980, date('Y'));
            array_push($this->timestamps, mktime($hour, $minute, $second, $month, $day, $year));
        }

        $teacherrole = $DB->get_record('role', ['shortname' => 'teacher']);
        foreach ($this->teachers as $i => $teacher) {
            self::getDataGenerator()->enrol_user($teacher->id,
                    $this->course->id,
                    $teacherrole->id);
            groups_add_member($this->groups[$i % self::GROUP_COUNT], $teacher);
        }

        $editingteacherrole = $DB->get_record('role', ['shortname' => 'editingteacher']);
        foreach ($this->editingteachers as $i => $editingteacher) {
            self::getDataGenerator()->enrol_user($editingteacher->id,
                    $this->course->id,
                    $editingteacherrole->id);
            groups_add_member($this->groups[$i % self::GROUP_COUNT], $editingteacher);
        }

        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        foreach ($this->students as $i => $student) {
            self::getDataGenerator()->enrol_user($student->id,
                    $this->course->id,
                    $studentrole->id);
            groups_add_member($this->groups[$i % self::GROUP_COUNT], $student);
        }

        // Make sure to run these tests as (editing)teacher, due to students getting no table if no files are present!
        self::setUser($this->editingteachers[0]);
    }

    /**
     * Convenience function to create a testable instance of a publication instance.
     *
     * @param array $params Array of parameters to pass to the generator
     * @return testable_publication Testable wrapper around the publication class.
     */
    protected function create_instance($params = []) {
        $generator = self::getDataGenerator()->get_plugin_generator('mod_publication');
        $params['course'] = $this->course->id;
        $instance = $generator->create_instance($params);
        $cm = get_coursemodule_from_instance('publication', $instance->id);
        $context = context_module::instance($cm->id);

        return new testable_publication($cm, $this->course, $context);
    }

    /**
     * Tests the basic creation of a publication instance with standardized settings!
     */
    public function test_create_instance() {
        self::assertNotEmpty($this->create_instance());
    }

    /**
     * Tests if we can create an allfilestable without uploaded files
     */
    public function test_allfilestable_upload() {
        // Setup fixture!
        $publication = $this->create_instance([
                'mode' => PUBLICATION_MODE_UPLOAD,
                'obtainteacherapproval' => 0,
                'obtainstudentapproval' => 0
        ]);

        // Exercise SUT!
        try {
            ob_start();
            $publication->display_allfilesform();
            $output = ob_get_contents();
            ob_end_clean();
            self::assertFalse(strpos($output, "Nothing to display"));
        } catch (Exception $e) {
            throw $e;
        }

        // Teardown fixture!
        $publication = null;
    }

    /**
     * Tests if we can create an allfilestable without imported files
     */
    public function test_allfilestable_import() {
        // Setup fixture!
        $generator = self::getDataGenerator()->get_plugin_generator('mod_assign');
        $params['course'] = $this->course->id;
        $assign = $generator->create_instance($params);
        $publication = $this->create_instance([
                'mode' => PUBLICATION_MODE_IMPORT,
                'importfrom' => $assign->id,
                'obtainteacherapproval' => 0,
                'obtainstudentapproval' => 0
        ]);

        // Exercise SUT!
        try {
            ob_start();
            $publication->display_allfilesform();
            $output = ob_get_contents();
            ob_end_clean();
            self::assertFalse(strpos($output, "Nothing to display"));
        } catch (Exception $e) {
            throw $e;
        }

        // Teardown fixture!
        $publication = null;
    }

    /**
     * Tests if we can create an allfilestable without imported group-files
     */
    public function test_allfilestable_group() {
        // Setup fixture!
        $generator = self::getDataGenerator()->get_plugin_generator('mod_assign');
        $params['course'] = $this->course->id;
        $params['teamsubmission'] = 1;
        $params['preventsubmissionnotingroup'] = 0;
        $assign = $generator->create_instance($params);
        $publication = $this->create_instance([
                'mode' => PUBLICATION_MODE_IMPORT,
                'importfrom' => $assign->id
        ]);

        // Exercise SUT!
        try {
            ob_start();
            $publication->display_allfilesform();
            $output = ob_get_contents();
            ob_end_clean();
            self::assertFalse(strpos($output, "Nothing to display"));
        } catch (Exception $e) {
            throw $e;
        }

        // Teardown fixture!
        $publication = null;
    }

}

/**
 * Test subclass that makes all the protected methods we want to test public.
 *
 * @package   mod_publication
 * @author    Philipp Hager
 * @copyright 2017 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class testable_publication extends publication {
}
