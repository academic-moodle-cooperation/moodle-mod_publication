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
namespace mod_publication;

use mod_publication\local\tests\base;
use Exception;
use mod_assign_generator;
use coding_exception;

defined('MOODLE_INTERNAL') || die();

// Make sure the code being tested is accessible.
global $CFG;
require_once($CFG->dirroot . '/mod/publication/locallib.php'); // Include the code to test!
require_once($CFG->dirroot . '/mod/assign/locallib.php'); // For ASSIGN_SUBMISSION_STATUS_SUBMITTED.

/**
 * This class contains the test cases for the formular validation.
 *
 * @package   mod_publication
 * @author    Philipp Hager
 * @copyright 2017 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers    \mod_publication\publication::get_allfilestable
 * @covers    \mod_publication\publication::display_allfilesform
 * @covers    \mod_publication\publication::importfiles
 */
final class allfilestable_test extends base {
    /*
     * The base test class already contains a setUp-method setting up a course including users and groups.
     */

    /**
     * Tests the basic creation of a publication instance with standardized settings!
     */
    public function test_create_instance(): void {
        self::assertNotEmpty($this->create_instance());
    }

    /**
     * Tests if we can create an allfilestable without uploaded files
     *
     * @throws Exception
     */
    public function test_allfilestable_upload(): void {
        // Setup fixture!
        $publication = $this->create_instance([
            'mode' => PUBLICATION_MODE_UPLOAD,
            'obtainteacherapproval' => 0,
            'obtainstudentapproval' => 0,
        ]);

        // Exercise SUT!
        $output = $publication->display_allfilesform();
        self::assertFalse(strpos($output, "Nothing to display"));

        // Teardown fixture!
        $publication = null;
    }

    /**
     * Tests if we can create an allfilestable without imported files
     *
     * @throws coding_exception
     */
    public function test_allfilestable_import(): void {
        // Setup fixture!
        /** @var mod_assign_generator $generator */
        $generator = self::getDataGenerator()->get_plugin_generator('mod_assign');
        $params['course'] = $this->course->id;
        $assign = $generator->create_instance($params);
        $publication = $this->create_instance([
            'mode' => PUBLICATION_MODE_IMPORT,
            'importfrom' => $assign->id,
            'obtainteacherapproval' => 0,
            'obtainstudentapproval' => 0,
        ]);

        // Exercise SUT!
        $output = $publication->display_allfilesform();
        self::assertFalse(strpos($output, "Nothing to display"));

        // Teardown fixture!
        $publication = null;
    }

    /**
     * Tests if we can create an allfilestable without imported group-files
     *
     * @throws coding_exception
     */
    public function test_allfilestable_group(): void {
        // Setup fixture!
        $this->resetAfterTest();
        $this->setAdminUser();
        // Create course and enrols.
        $course = $this->getDataGenerator()->create_course();
        $users = [
            'student1' => $this->getDataGenerator()->create_and_enrol($course, 'student'),
            'student2' => $this->getDataGenerator()->create_and_enrol($course, 'student'),
            'student3' => $this->getDataGenerator()->create_and_enrol($course, 'student'),
            'student4' => $this->getDataGenerator()->create_and_enrol($course, 'student'),
            'student5' => $this->getDataGenerator()->create_and_enrol($course, 'student'),
        ];
        $teacher = $this->getDataGenerator()->create_and_enrol($course, 'teacher');
        $this->course = $course;

        // Generate groups.
        $groups = [];
        $groupmembers = [
            'group1' => ['student1', 'student2'],
            'group2' => ['student3', 'student4'],
            'group3' => ['student5'],
        ];
        foreach ($groupmembers as $groupname => $groupusers) {
            $group = $this->getDataGenerator()->create_group(['courseid' => $course->id, 'name' => $groupname]);
            foreach ($groupusers as $user) {
                groups_add_member($group, $users[$user]);
            }
            $groups[$groupname] = $group;
        }

        $params = [
            'course' => $course,
            'assignsubmission_file_enabled' => 1,
            'assignsubmission_file_maxfiles' => 12,
            'assignsubmission_file_maxsizebytes' => 1024 * 1024,
            'teamsubmission' => 1,
            'preventsubmissionnotingroup' => false,
            'requireallteammemberssubmit' => false,
            'groupmode' => 1,
        ];

        $assign = $this->getDataGenerator()->create_module('assign', $params);
        $cm = get_coursemodule_from_id('assign', $assign->cmid, 0, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);
        $files = [
            "mod/assign/tests/fixtures/submissionsample01.txt",
            "mod/assign/tests/fixtures/submissionsample02.txt",
        ];
        $generator = self::getDataGenerator()->get_plugin_generator('mod_assign');

        $this->setAdminUser();
        foreach ($users as $key => $user) {
            $generator->create_submission([
                'userid' => $user->id,
                'cmid' => $cm->id,
                'file' => implode(',', $files),
            ]);
        }

        // Function assign::save_submission() ignores the status passed by create_submission and writes
        // ASSIGN_SUBMISSION_STATUS_DRAFT because the assign instance has submissiondrafts=1 (the
        // generator's default). Promote two of the three group submissions to "submitted" directly
        // so the import filter (which only takes ASSIGN_SUBMISSION_STATUS_SUBMITTED) has work to do
        // and group3 stays draft to verify drafts are excluded.
        global $DB;
        $DB->set_field('assign_submission', 'status', ASSIGN_SUBMISSION_STATUS_SUBMITTED, [
            'assignment' => $assign->id,
            'groupid'    => $groups['group1']->id,
        ]);
        $DB->set_field('assign_submission', 'status', ASSIGN_SUBMISSION_STATUS_SUBMITTED, [
            'assignment' => $assign->id,
            'groupid'    => $groups['group2']->id,
        ]);

        $this->setAdminUser();
        $publication = $this->create_instance([
            'mode' => PUBLICATION_MODE_IMPORT,
            'importfrom' => $assign->id,
            'obtainteacherapproval' => 0,
            'obtainstudentapproval' => 0,
            'allowsubmissionsfromdate' => 0,
            'duedate' => 0,
            'groupmode' => NOGROUPS,
            'groupapproval' => PUBLICATION_APPROVAL_GROUPAUTOMATIC,
        ]);

        $publication->importfiles();
        $publication->set_allfilespage(true);
        $allfilestable = $publication->get_allfilestable(PUBLICATION_FILTER_NOFILTER);
        ob_start();
        $allfilestable->out(10, true); // Print the whole table.
        $tableoutput = ob_get_contents();
        ob_end_clean();
        $norowsfound = $allfilestable->get_count() == 0;
        $nofilesfound = $allfilestable->get_totalfilescount() == 0;
        self::assertFalse($norowsfound);
        self::assertFalse($nofilesfound);

        // Files from the two submitted group submissions must have been imported.
        self::assertTrue($DB->record_exists('publication_file', [
            'publication' => $publication->get_instance()->id,
            'userid'      => $groups['group1']->id,
            'type'        => PUBLICATION_MODE_IMPORT,
        ]));
        self::assertTrue($DB->record_exists('publication_file', [
            'publication' => $publication->get_instance()->id,
            'userid'      => $groups['group2']->id,
            'type'        => PUBLICATION_MODE_IMPORT,
        ]));
        // Group3's submission is still a draft, so its files must NOT have been imported.
        self::assertFalse($DB->record_exists('publication_file', [
            'publication' => $publication->get_instance()->id,
            'userid'      => $groups['group3']->id,
            'type'        => PUBLICATION_MODE_IMPORT,
        ]));

        // Teardown fixture!
        $publication = null;
    }

    /**
     * The start-page ("Published files") view must show one row per file, while the teacher
     * "File submissions" view keeps grouping files by participant.
     *
     * @covers \mod_publication\local\allfilestable\base::init_sql
     */
    public function test_allfilestable_startpage_one_row_per_file(): void {
        $publication = $this->create_instance([
            'mode' => PUBLICATION_MODE_UPLOAD,
            'obtainteacherapproval' => 0,
            'obtainstudentapproval' => 0,
        ]);
        $pubid = $publication->get_instance()->id;

        // Two files for one participant and one for another => 3 files / 2 participants.
        $this->create_upload($this->students[0]->id, $pubid, 'file1.txt', 'content 1');
        $this->create_upload($this->students[0]->id, $pubid, 'file2.txt', 'content 2');
        $this->create_upload($this->students[1]->id, $pubid, 'file3.txt', 'content 3');

        // Start page: one row per file.
        self::assertFalse($publication->get_allfilespage());
        $startpagetable = $publication->get_allfilestable(PUBLICATION_FILTER_NOFILTER);
        self::assertEquals(3, $startpagetable->get_count());

        ob_start();
        $startpagetable->out(100, true); // Render so the per-row file counters get populated.
        ob_end_clean();
        self::assertEquals(3, $startpagetable->get_totalfilescount());

        // Teacher "File submissions" view: still grouped by participant (2 rows).
        $teachertable = $publication->get_allfilestable(PUBLICATION_FILTER_ALLFILES, true);
        self::assertEquals(2, $teachertable->get_count());

        // Teardown fixture!
        $publication = null;
    }
}
