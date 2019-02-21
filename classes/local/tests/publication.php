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
 * subclass of mod_publication to publish all testable methods!
 *
 * @package   mod_publication
 * @author    Philipp Hager
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_publication\local\tests;

use stdClass;
use coding_exception;
use dml_exception;
use required_capability_exception;
use Exception;

defined('MOODLE_INTERNAL') || die();

/**
 * Test subclass that makes all the protected methods we want to test public.
 *
 * Please ignore the nasty code in here just catching all kind of exceptions and then throwing them again, it's just to shut up
 * code-checker about "unnecessary method overrides" which we need to make the methods under test publicly available!
 *
 * @package   mod_publication
 * @author    Philipp Hager
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class publication extends \publication {
    /**
     * Additional method to get publication record.
     *
     * @return stdClass Instances database record
     */
    public function get_publication() {
        return $this->instance;
    }

    /**
     * Override method to be available for testing!
     *
     * @param int $agrpid active-group-id to register/queue user to
     * @param int $userid user to register/queue
     * @param bool $previewonly optional don't act, just return a preview
     * @return string status message
     * @throws coding_exception
     * @throws dml_exception
     * @throws required_capability_exception
     */
    public function testable_register_in_agrp($agrpid, $userid=0, $previewonly=false) {
        return parent::register_in_agrp($agrpid, $userid, $previewonly);
    }

    /**
     * Override method to be available for testing!
     *
     * @param int $agrpid active-group-id to unregister/unqueue user from
     * @param int $userid user to unregister/unqueue
     * @param bool $previewonly (optional) don't act, just return a preview
     * @return string $message if everything went right
     * @throws coding_exception
     * @throws dml_exception
     * @throws required_capability_exception
     */
    public function testable_unregister_from_agrp($agrpid, $userid=0, $previewonly=false) {
        return parent::unregister_from_agrp($agrpid, $userid, $previewonly);
    }

    /**
     * Override method to be available for testing!
     *
     * @param int $agrpid ID of the active group
     * @param int $userid ID of user to queue or null (then $USER->id is used)
     * @return bool whether or not user qualifies for a group change
     * @throws Exception
     */
    public function testable_qualifies_for_groupchange($agrpid, $userid) {
        return parent::qualifies_for_groupchange($agrpid, $userid);
    }

    /**
     * Override method to be available for testing!
     *
     * @param int $agrpid ID of the active group
     * @param int $userid (optional) ID of user to queue or null (then $USER->id is used)
     * @param stdClass $message (optional) cached data for the language strings
     * @param int $oldagrpid (optional) ID of former active group
     * @return string status message
     * @throws coding_exception
     * @throws dml_exception
     * @throws exceedgroupqueuelimit
     * @throws exceeduserqueuelimit
     * @throws exceeduserreglimit
     * @throws registration
     * @throws regpresent
     * @throws required_capability_exception
     */
    public function testable_can_change_group($agrpid, $userid=0, $message=null, $oldagrpid = null) {
        return parent::can_change_group($agrpid, $userid, $message, $oldagrpid);
    }

    /**
     * Override method to be available for testing!
     *
     * @param int $agrpid ID of active group to change to
     * @param int $userid (optional) ID of user to change group for or null ($USER->id is used).
     * @param stdClass $message (optional) prepared message object containing username and groupname or null.
     * @param int $oldagrpid (optional) ID of former active group
     * @return string success message
     * @throws coding_exception
     * @throws dml_exception
     * @throws exceedgroupqueuelimit
     * @throws exceeduserqueuelimit
     * @throws exceeduserreglimit
     * @throws registration
     * @throws regpresent
     * @throws required_capability_exception
     */
    public function testable_change_group($agrpid, $userid = null, $message = null, $oldagrpid = null) {
        return parent::change_group($agrpid, $userid, $message, $oldagrpid);
    }

    /**
     * Override method to be available for testing!
     *
     * @param int $agrpid
     * @param int $userid
     * @param stdClass $message
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     * @throws required_capability_exception
     */
    public function testable_add_registration($agrpid, $userid, $message) {
        return parent::add_registration($agrpid, $userid, $message);
    }

    /**
     * Override method to be available for testing!
     *
     * @param int $agrpid
     * @param int $userid
     * @param stdClass $message
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     * @throws required_capability_exception
     */
    public function testable_add_queue_entry($agrpid, $userid, $message) {
        return parent::add_queue_entry($agrpid, $userid, $message);
    }

    /**
     * Get context module.
     *
     * @return \context_module
     */
    public function get_context() {
        return $this->context;
    }
}
