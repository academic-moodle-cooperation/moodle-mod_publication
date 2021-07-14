<?php

use mod_publication\local\tests\base;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/mod/publication/externallib.php');

/**
 * External mod publication functions unit tests
 */
class mod_publication_external_testcase extends base {
    
    /**
     * Test if the user only gets publication for enrolled courses
     */
    public function test_get_publications_by_courses() {
        global $CFG, $DB, $USER;

        $this->resetAfterTest(true);

        $user = $this->getDataGenerator()->create_user();

        $course1 = $this->getDataGenerator()->create_course([
            'fullname' => 'PHPUnitTestCourse1',
            'summary' => 'Test course for automated php unit tests',
            'summaryformat' => FORMAT_HTML
        ]);

        $this->getDataGenerator()->enrol_user($user->id, $course1->id);

        $course2 = $this->getDataGenerator()->create_course([
            'fullname' => 'PHPUnitTestCourse2',
            'summary' => 'Test course for automated php unit tests',
            'summaryformat' => FORMAT_HTML
        ]);

        $this->getDataGenerator()->enrol_user($user->id, $course2->id);

        $course3 = $this->getDataGenerator()->create_course([
            'fullname' => 'PHPUnitTestCourse3',
            'summary' => 'Test course for automated php unit tests',
            'summaryformat' => FORMAT_HTML
        ]);

        $publication1 = self::getDataGenerator()->create_module('publication', [
            'course' => $course1->id,
            'name' => 'Publication Module 1',
            'intro' => 'Publication module for automated php unit tests',
            'introformat' => FORMAT_HTML,
        ]);

        $publication2 = self::getDataGenerator()->create_module('publication', [
            'course' => $course2->id,
            'name' => 'Publication Module 2',
            'intro' => 'Publication module for automated php unit tests',
            'introformat' => FORMAT_HTML,
        ]);

        $publication3 = self::getDataGenerator()->create_module('publication', [
            'course' => $course3->id,
            'name' => 'Publication Module 3',
            'intro' => 'Publication module for automated php unit tests',
            'introformat' => FORMAT_HTML,
        ]);

        $this->setUser($user);

        $result = mod_publication_external::get_publications_by_courses([]);

        // user is enrolled only in course1 and course2, so the third publication module in course3 should not be included
        $this->assertEquals(2, count($result->publications));
    }


    /**
     * Test if the user gets a valid publication from the endpoint
     */
    public function test_get_publication() {
        global $CFG, $DB, $USER;

        $this->resetAfterTest(true);

        $user = $this->getDataGenerator()->create_user();

        $course = $this->getDataGenerator()->create_course([
            'fullname' => 'PHPUnitTestCourse',
            'summary' => 'Test course for automated php unit tests',
            'summaryformat' => FORMAT_HTML
        ]);

        $this->getDataGenerator()->enrol_user($user->id, $course->id);

        $publication = self::getDataGenerator()->create_module('publication', [
            'course' => $course->id,
            'name' => 'Publication Module',
            'intro' => 'Publication module for automated php unit tests',
            'introformat' => FORMAT_HTML,
        ]);

        $this->setUser($user);

        $result = mod_publication_external::get_publication($publication->id);

        // publication name should be equal to 'Publication Module'
        $this->assertEquals('Publication Module', $result->publication->name);

        // Course id in publication should be equal to the id of the course
        $this->assertEquals($course->id, $result->publication->course);
    }


    /**
     * Test if the user gets an exception when the publication is hidden in the course
     */
    public function test_get_publication_hidden() {
        global $CFG, $DB, $USER;

        $this->resetAfterTest(true);

        $user = $this->getDataGenerator()->create_user();

        $course = $this->getDataGenerator()->create_course([
            'fullname' => 'PHPUnitTestCourse',
            'summary' => 'Test course for automated php unit tests',
            'summaryformat' => FORMAT_HTML
        ]);

        $this->getDataGenerator()->enrol_user($user->id, $course->id);

        $publication = self::getDataGenerator()->create_module('publication', [
            'course' => $course->id,
            'name' => 'Hidden Publication Module',
            'intro' => 'Publication module for automated php unit tests',
            'introformat' => FORMAT_HTML,
            'visible' => 0,
        ]);

        $this->setUser($user);

        // Test should throw require_login_exception
        $this->expectException(require_login_exception::class);

        $result = mod_publication_external::get_publication($publication->id);

    }

    /**
     * Test if the user gets some files from the publication
     */
    public function test_get_publication_files() {
        global $CFG, $DB, $USER;

        $this->resetAfterTest(true);

        $user = $this->getDataGenerator()->create_user();

        $course = $this->getDataGenerator()->create_course([
            'fullname' => 'PHPUnitTestCourse',
            'summary' => 'Test course for automated php unit tests',
            'summaryformat' => FORMAT_HTML
        ]);

        $this->getDataGenerator()->enrol_user($user->id, $course->id);

        $publication = self::getDataGenerator()->create_module('publication', [
            'course' => $course->id,
            'name' => 'Publication Module',
            'intro' => 'Publication module for automated php unit tests',
            'introformat' => FORMAT_HTML,
        ]);

        $this->setUser($user);

        $this->create_upload($USER->id, $publication->id, 'test1.txt', 'Test 1');

        $result = mod_publication_external::get_publication($publication->id);

        $this->assertEquals($result->publication->files[0]->name, 'test1.txt');

    }
}