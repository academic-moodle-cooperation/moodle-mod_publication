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
 * Unit Tests for mod/publication's privacy providers!
 *
 * @package    mod_publication
 * @copyright  2019 Academic Moodle Cooperation https://www.academic-moodle-cooperation.org/
 * @author Philipp Hager <philipp.hager@tuwien.ac.at> strongly based on mod_assign's privacy unit tests!
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_publication\local\tests;

use \mod_publication\privacy\provider;
use context_module;
use stdClass;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/publication/locallib.php');

/**
 * Unit Tests for mod/publication's privacy providers! TODO: finish these unit tests here!
 *
 * @copyright  2019 Academic Moodle Cooperation https://www.academic-moodle-cooperation.org/
 * @author Philipp Hager <philipp.hager@tuwien.ac.at> strongly based on mod_assign's privacy unit tests!
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class privacy_testcase extends base {
    /** @var stdClass */
    private $course1;
    /** @var stdClass */
    private $course2;
    /** @var stdClass */
    private $group11;
    /** @var stdClass */
    private $group12;
    /** @var stdClass */
    private $group21;
    /** @var stdClass */
    private $group22;
    /** @var stdClass */
    private $user1;
    /** @var stdClass */
    private $user2;
    /** @var stdClass */
    private $user3;
    /** @var stdClass */
    private $teacher1;
    /** @var publication */
    private $pubupload;
    /** @var publication */
    private $pubupload2;
    /** @var \testable_assign */
    private $assign;
    /** @var \testable_assign */
    private $assign2;
    /** @var publication */
    private $pubimport;
    /** @var \testable_assign */
    private $teamassign;
    /** @var \testable_assign */
    private $teamassign2;
    /** @var publication */
    private $pubteamimport;
    /** @var publication */
    private $pubteamimport2;

    /**
     * Set up the common parts of the tests!
     *
     * The base test class already contains a setUp-method setting up a course including users and groups.
     *
     * @throws \coding_exception
     */
    public function setUp() {
        parent::setUp();

        $this->resetAfterTest();

        $this->course1 = self::getDataGenerator()->create_course();
        $this->course2 = self::getDataGenerator()->create_course();
        $this->group11 = self::getDataGenerator()->create_group((object)['courseid' => $this->course1->id]);
        $this->group12 = self::getDataGenerator()->create_group((object)['courseid' => $this->course1->id]);
        $this->group21 = self::getDataGenerator()->create_group((object)['courseid' => $this->course2->id]);
        $this->group22 = self::getDataGenerator()->create_group((object)['courseid' => $this->course2->id]);

        $this->user1 = $this->students[0];
        self::getDataGenerator()->enrol_user($this->user1->id, $this->course1->id, 'student');
        self::getDataGenerator()->enrol_user($this->user1->id, $this->course2->id, 'student');
        $this->user2 = $this->students[1];
        self::getDataGenerator()->enrol_user($this->user2->id, $this->course1->id, 'student');
        self::getDataGenerator()->enrol_user($this->user2->id, $this->course2->id, 'student');
        $this->user3 = $this->students[2];
        self::getDataGenerator()->enrol_user($this->user3->id, $this->course1->id, 'student');
        self::getDataGenerator()->enrol_user($this->user3->id, $this->course2->id, 'student');
        // Need a second user as teacher.
        $this->teacher1 = $this->editingteachers[0];
        self::getDataGenerator()->enrol_user($this->teacher1->id, $this->course1->id, 'editingteacher');
        self::getDataGenerator()->enrol_user($this->teacher1->id, $this->course2->id, 'editingteacher');

        // Prepare groups!
        self::getDataGenerator()->create_group_member((object)['userid' => $this->user1->id, 'groupid' => $this->group11->id]);
        self::getDataGenerator()->create_group_member((object)['userid' => $this->user3->id, 'groupid' => $this->group11->id]);
        self::getDataGenerator()->create_group_member((object)['userid' => $this->user1->id, 'groupid' => $this->group21->id]);
        self::getDataGenerator()->create_group_member((object)['userid' => $this->user3->id, 'groupid' => $this->group21->id]);

        self::getDataGenerator()->create_group_member((object)['userid' => $this->user2->id, 'groupid' => $this->group12->id]);
        self::getDataGenerator()->create_group_member((object)['userid' => $this->user2->id, 'groupid' => $this->group22->id]);

        // Create multiple publication instances.
        // Publication with uploads.
        $this->pubupload = $this->create_instance([
                'name' => 'Pub Upload 1',
                'course' => $this->course1
        ]);
        $this->pubupload2 = $this->create_instance([
                'name' => 'Pub Upload 2',
                'course' => $this->course1
        ]);

        // Assign to import from.
        $this->assign = $this->create_assign($this->course1, ['submissiondrafts' => false,
                                                              'assignsubmission_onlinetext_enabled' => true]);
        $this->assign2 = $this->create_assign($this->course1, ['submissiondrafts' => false,
                                                               'assignsubmission_onlinetext_enabled' => true]);

        // Publication with imports.
        $this->pubimport = $this->create_instance([
                'name' => 'Pub Import 1',
                'course' => $this->course1,
                'mode' => PUBLICATION_MODE_IMPORT,
                'importfrom' => $this->assign->get_instance()->id,
        ]);

        // Publication with import from teamsubmission.
        $this->teamassign = $this->create_assign($this->course1, [
                'name' => 'Teamassign 1',
                'teamsubmission' => true,
                'submissiondrafts' => false,
                'assignsubmission_onlinetext_enabled' => true
        ]);
        $this->teamassign2 = $this->create_assign($this->course2, [
                'name' => 'Teamassign 2',
                'teamsubmission' => true,
                'submissiondrafts' => false,
                'assignsubmission_onlinetext_enabled' => true
        ]);
        $this->pubteamimport = $this->create_instance([
                'name' => 'Teamimport 1',
                'course' => $this->course1,
                'mode' => PUBLICATION_MODE_IMPORT,
                'importfrom' => $this->teamassign->get_instance()->id,
                'requireallteammemberssubmit' => false,
                'preventsubmissionnotingroup' => false,
        ]);
        $this->pubteamimport2 = $this->create_instance([
                'name' => 'Teamimport 2',
                'course' => $this->course2,
                'mode' => PUBLICATION_MODE_IMPORT,
                'importfrom' => $this->teamassign2->get_instance()->id,
                'requireallteammemberssubmit' => false,
                'preventsubmissionnotingroup' => false,
        ]);
    }

    /**
     * Test that getting the contexts for a user works.
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function test_get_contexts_for_userid() {
        // The user will be in these contexts.
        $usercontextids = [
            $this->pubimport->get_context()->id,
            $this->pubupload->get_context()->id,
            $this->pubteamimport->get_context()->id,
        ];

        // User 1 submits to assign1 and teamassign1 and uploads in pubupload1!
        $this->add_submission($this->user1, $this->assign, 'Textsubmission in assign1 by user1!', true);
        $this->add_submission($this->user1, $this->teamassign, 'Textsubmission in teamassign1 by user1!', true);
        $this->create_upload($this->user1->id, $this->pubupload->get_instance()->id, 'upload-no-1.txt',
                'THis is the first upload here!');
        // User 3 also submits to general assign & uploads in general publication!
        $this->add_submission($this->user3, $this->assign2, 'Textsubmission for assign2 by user3!', true);
        $this->create_upload($this->user3->id, $this->pubupload2->get_instance()->id, 'upload-no-2.txt',
                'This is another upload in another publication');

        // Then we check, if user 1 appears only in pubimport1, pubupload1 and pubteamimport1!
        $contextlist = provider::get_contexts_for_userid($this->user1->id);

        $this->assertEquals(count($usercontextids), count($contextlist->get_contextids()));
        // There should be no difference between the contexts.
        $this->assertEmpty(array_diff($usercontextids, $contextlist->get_contextids()));

        // User 3 is in a group with user 1 and submits to teamassign2!
        $this->add_submission($this->user3, $this->teamassign2,
                'Another text submission, but this time valid for the whole group!');

        // Now user 1 is also in pubteamimport2!
        $usercontextids[] = $this->pubteamimport2->get_context()->id;

        $contextlist = provider::get_contexts_for_userid($this->user1->id);
        $this->assertEquals(count($usercontextids), count($contextlist->get_contextids()));
        // There should be no difference between the contexts.
        $this->assertEmpty(array_diff($usercontextids, $contextlist->get_contextids()));

        // TODO: test for group approvals and extended due dates!
    }

    /**
     * Test returning a list of user IDs related to a context.
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function test_get_users_in_context() {
        // User 1 submits to assign1 and teamassign1 and uploads in pubupload1!
        $this->add_submission($this->user1, $this->assign, 'Textsubmission in assign1 by user1!', true);
        $this->add_submission($this->user1, $this->teamassign, 'Textsubmission in teamassign1 by user1!', true);
        $this->create_upload($this->user1->id, $this->pubupload->get_instance()->id, 'upload-no-1.txt',
                'This is the first upload here!');

        $uploadcm = get_coursemodule_from_instance('publication', $this->pubupload->get_instance()->id);
        $uploadctx = context_module::instance($uploadcm->id);
        $userlist = new \core_privacy\local\request\userlist($uploadctx, 'publication');
        provider::get_users_in_context($userlist);
        $userids = $userlist->get_userids();
        self::assertTrue(in_array($this->user1->id, $userids));
        self::assertFalse(in_array($this->user2->id, $userids));
        self::assertFalse(in_array($this->user3->id, $userids));

        $upload2cm = get_coursemodule_from_instance('publication', $this->pubupload2->get_instance()->id);
        $upload2ctx = context_module::instance($upload2cm->id);
        $userlist2 = new \core_privacy\local\request\userlist($upload2ctx, 'publication');
        provider::get_users_in_context($userlist2);
        $userids2 = $userlist2->get_userids();
        self::assertFalse(in_array($this->user1->id, $userids2));
        self::assertFalse(in_array($this->user2->id, $userids2));
        self::assertFalse(in_array($this->user3->id, $userids2));

        $importcm = get_coursemodule_from_instance('publication', $this->pubimport->get_instance()->id);
        $importctx = context_module::instance($importcm->id);
        $importuserlist = new \core_privacy\local\request\userlist($importctx, 'publication');
        provider::get_users_in_context($importuserlist);
        $importuserids = $importuserlist->get_userids();
        self::assertTrue(in_array($this->user1->id, $importuserids));
        self::assertFalse(in_array($this->user2->id, $importuserids));
        self::assertFalse(in_array($this->user3->id, $importuserids));

        $teamcm = get_coursemodule_from_instance('publication', $this->pubteamimport->get_instance()->id);
        $teamctx = context_module::instance($teamcm->id);
        $teamuserlist = new \core_privacy\local\request\userlist($teamctx, 'publication');
        provider::get_users_in_context($teamuserlist);
        $teamuserids = $teamuserlist->get_userids();
        self::assertTrue(in_array($this->user1->id, $teamuserids));
        self::assertFalse(in_array($this->user2->id, $teamuserids));
        self::assertTrue(in_array($this->user3->id, $teamuserids));

        // TODO: check for extended due dates and groupapprovals!
    }

    /**
     * Test that a student with multiple submissions and grades is returned with the correct data.
     */
    public function test_export_user_data_student() {
        // Stop here and mark this test as incomplete.
        self::markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * Tests the data returned for a teacher.
     */
    public function test_export_user_data_teacher() {
        // Stop here and mark this test as incomplete.
        self::markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * A test for deleting all user data for a given context.
     */
    public function test_delete_data_for_all_users_in_context() {
        // Stop here and mark this test as incomplete.
        self::markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * A test for deleting all user data for one user.
     */
    public function test_delete_data_for_user() {
        // Stop here and mark this test as incomplete.
        self::markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * A test for deleting all user data for a bunch of users.
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function test_delete_data_for_users() {
        global $DB;

        // User 1 submits to assign1 and teamassign1 and uploads in pubupload1!
        $this->add_submission($this->user1, $this->assign, 'Textsubmission in assign1 by user1!', true);
        $this->add_submission($this->user2, $this->assign, 'Textsubmission in assign1 by user2!', true);
        $this->add_submission($this->user1, $this->teamassign, 'Textsubmission in teamassign1 by user1!', true);
        $this->add_submission($this->user2, $this->teamassign, 'Textsubmission in teamassign1 by user2!', true);
        $this->create_upload($this->user1->id, $this->pubupload->get_instance()->id, 'upload-no-1.txt',
                'This is the first upload here!');
        $this->create_upload($this->user2->id, $this->pubupload->get_instance()->id, 'upload-no-2.txt',
                'This is the second upload here!');

        // Test for the data to be in place!
        self::assertEquals(2, $DB->count_records('publication_file', ['publication' => $this->pubimport->get_instance()->id]));
        self::assertEquals(2, $DB->count_records('publication_file', ['publication' => $this->pubteamimport->get_instance()->id]));
        self::assertEquals(2, $DB->count_records('publication_file', ['publication' => $this->pubupload->get_instance()->id]));

        $userlist = new \core_privacy\local\request\approved_userlist($this->pubimport->get_context(), 'publication',
                [$this->user1->id]);
        provider::delete_data_for_users($userlist);
        self::assertEquals(1, $DB->count_records('publication_file', ['publication' => $this->pubimport->get_instance()->id]));
        self::assertEquals(2, $DB->count_records('publication_file', ['publication' => $this->pubteamimport->get_instance()->id]));
        self::assertEquals(2, $DB->count_records('publication_file', ['publication' => $this->pubupload->get_instance()->id]));
        $userlist = new \core_privacy\local\request\approved_userlist($this->pubupload->get_context(), 'publication',
                [$this->user1->id]);
        provider::delete_data_for_users($userlist);
        self::assertEquals(1, $DB->count_records('publication_file', ['publication' => $this->pubimport->get_instance()->id]));
        self::assertEquals(2, $DB->count_records('publication_file', ['publication' => $this->pubteamimport->get_instance()->id]));
        self::assertEquals(1, $DB->count_records('publication_file', ['publication' => $this->pubupload->get_instance()->id]));

        $userlist = new \core_privacy\local\request\approved_userlist($this->pubteamimport->get_context(), 'publication',
                [$this->user1->id, $this->user2->id, $this->user3->id]);
        provider::delete_data_for_users($userlist);
        $userlist = new \core_privacy\local\request\approved_userlist($this->pubupload->get_context(), 'publication',
                [$this->user1->id, $this->user2->id, $this->user3->id]);
        provider::delete_data_for_users($userlist);
        $userlist = new \core_privacy\local\request\approved_userlist($this->pubimport->get_context(), 'publication',
                [$this->user1->id, $this->user2->id, $this->user3->id]);
        provider::delete_data_for_users($userlist);

        self::assertEquals(0, $DB->count_records('publication_file', ['publication' => $this->pubimport->get_instance()->id]));
        self::assertEquals(2, $DB->count_records('publication_file', ['publication' => $this->pubteamimport->get_instance()->id]));
        self::assertEquals(0, $DB->count_records('publication_file', ['publication' => $this->pubupload->get_instance()->id]));
    }
}
