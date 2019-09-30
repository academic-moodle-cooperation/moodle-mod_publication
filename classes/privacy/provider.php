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
 * Privacy class for requesting user data.
 *
 * @package    mod_publication
 * @author     Philipp Hager
 * @copyright  2018 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_publication\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\metadata\provider as metadataprovider;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\plugin\provider as pluginprovider;
use core_privacy\local\request\user_preference_provider as preference_provider;
use core_privacy\local\request\writer;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\helper;
use core_privacy\local\request\core_userlist_provider;
use core_privacy\local\request\userlist;
use core_privacy\local\request\approved_userlist;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/publication/locallib.php');

/**
 * Privacy class for requesting user data.
 *
 * @package    mod_publication
 * @author     Philipp Hager
 * @copyright  2018 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements metadataprovider, pluginprovider, preference_provider, core_userlist_provider {
    /**
     * Provides meta data that is stored about a user with mod_publication
     *
     * @param  collection $collection A collection of meta data items to be added to.
     * @return  collection Returns the collection of metadata.
     */
    public static function get_metadata(collection $collection): collection {
        $publicationextduedates = [
                'userid' => 'privacy:metadata:userid',
                'extensionduedate' => 'privacy:metadata:extensionduedate',
        ];
        $publicationfile = [
                'userid' => 'privacy:metadata:userid',
                'timecreated' => 'privacy:metadata:timecreated',
                'fileid' => 'privacy:metadata:fileid',
                'filesourceid' => 'privacy:metadata:fileid',
                'filename' => 'privacy:metadata:filename',
                'contenthash' => 'privacy:metadata:contenthash',
                'type' => 'privacy:metadata:type',
                'teacherapproval' => 'privacy:metadata:teacherapproval',
                'studentapproval' => 'privacy:metadata:studentapproval'
        ];
        $publicationgroupapproval = [
                'fileid' => 'privacy:metadata:fileid',
                'userid' => 'privacy:metadata:userid',
                'approval' => 'privacy:metadata:approval',
                'timemodified' => 'privacy:metadata:timemodified'
        ];

        $collection->add_database_table('publication_extduedates', $publicationextduedates, 'privacy:metadata:extduedates');
        $collection->add_database_table('publication_file', $publicationfile, 'privacy:metadata:files');
        $collection->add_database_table('publication_groupapproval', $publicationgroupapproval, 'privacy:metadata:groupapproval');

        $collection->add_user_preference('publication_perpage', 'privacy:metadata:publicationperpage');

        // Link to subplugins.
        $collection->add_subsystem_link('core_files', [], 'privacy:metadata:publicationfileexplanation');

        return $collection;
    }

    /**
     * Returns all of the contexts that has information relating to the userid.
     *
     * @param  int $userid The user ID.
     * @return contextlist an object with the contexts related to a userid.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        global $DB;

        $params = [
                'modulename' => 'publication',
                'contextlevel' => CONTEXT_MODULE,
                'userid' => $userid,
                'guserid' => $userid,
                'extuserid' => $userid,
                'fuserid' => $userid
        ];

        $enroled = enrol_get_all_users_courses($userid);
        if (!empty($enroled)) {
            $enroled = array_keys($enroled);
        } else {
            $enroled = [-1];
        }
        list($enrolsql, $enrolparams) = $DB->get_in_or_equal($enroled, SQL_PARAMS_NAMED, 'enro');
        $params = $params + $enrolparams;

        /* The where clause is quite interesting here, because we have to differentiate
         * if there was a mod_assign to import from with teamsubmission enabled or not.
         * If we imported from an assign instance with teamsubmissions the userid-fields often contains the group's id.
         * If we uploaded or imported from a none-teamsubmission-assign instance we have the userid fields populated with user's
         * ids.
         * Did I also mention the possibility of imports from teamsubmission-assigns which won't prevent users without groups be
         * counted as special "standard group"? We also consider these here!
         *
         * I know it's not the best design, but when implementing the teamsubmission-imports we had not much time to add another
         * field to reference group-ids.
         * TODO: split {publication_file}.userid to a userid and a groupid field or rename it at least to itemid! */
        $sql = "
   SELECT DISTINCT ctx.id
     FROM {course_modules} cm
     JOIN {modules} m ON cm.module = m.id AND m.name = :modulename
     JOIN {publication} p ON cm.instance = p.id
     JOIN {context} ctx ON cm.id = ctx.instanceid AND ctx.contextlevel = :contextlevel
LEFT JOIN {publication_extduedates} ext ON p.id = ext.publication
LEFT JOIN {publication_file} f ON p.id = f.publication
LEFT JOIN {publication_groupapproval} ga ON f.id = ga.fileid
LEFT JOIN {assign} a ON p.importfrom = a.id
LEFT JOIN {groups} g ON g.courseid = p.course
LEFT JOIN {groups_members} gm ON g.id = gm.groupid AND gm.userid = :guserid
    WHERE ((p.importfrom > 0 AND a.teamsubmission > 0)
           AND ((gm.userid = :userid AND (ext.userid = gm.groupid OR f.userid = gm.groupid))
               OR (gm.userid IS NULL AND f.userid = 0 AND a.preventsubmissionnotingroup = 0 AND g.courseid $enrolsql)))
           OR ((p.importfrom <= 0 OR a.teamsubmission = 0) AND (ext.userid = :extuserid OR f.userid = :fuserid))";
        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param   userlist    $userlist   The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        $params = [
                'modulename' => 'publication',
                'contextid' => $context->id,
                'contextlevel' => CONTEXT_MODULE,
                'upload' => PUBLICATION_MODE_UPLOAD
        ];

        // Get all who uploaded/have files imported!
        // First get all regular uploads.
        $sql = "SELECT f.userid
                  FROM {context} ctx
                  JOIN {course_modules} cm ON cm.id = ctx.instanceid
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modulename
                  JOIN {publication} p ON p.id = cm.instance
                  JOIN {publication_file} f ON p.id = f.publication
                 WHERE ctx.id = :contextid AND ctx.contextlevel = :contextlevel AND p.mode = :upload";
        $userlist->add_from_sql('userid', $sql, $params);

        unset($params['upload']);
        $params['import'] = PUBLICATION_MODE_IMPORT;
        // Second get all imported file's users.
        $sql = "SELECT gm.userid
                  FROM {context} ctx
                  JOIN {course_modules} cm ON cm.id = ctx.instanceid
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modulename
                  JOIN {publication} p ON p.id = cm.instance
                  JOIN {publication_file} f ON p.id = f.publication
             LEFT JOIN {assign} a ON p.importfrom = a.id
             LEFT JOIN {groups} g ON g.courseid = p.course AND f.userid = g.id
             LEFT JOIN {groups_members} gm ON g.id = gm.groupid
                 WHERE ctx.id = :contextid AND ctx.contextlevel = :contextlevel AND p.mode = :import
                       AND (p.importfrom > 0 AND a.teamsubmission > 0)";
        $userlist->add_from_sql('userid', $sql, $params);
        $sql = "SELECT f.userid
                  FROM {context} ctx
                  JOIN {course_modules} cm ON cm.id = ctx.instanceid
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modulename
                  JOIN {publication} p ON p.id = cm.instance
                  JOIN {publication_file} f ON p.id = f.publication
             LEFT JOIN {assign} a ON p.importfrom = a.id
                 WHERE ctx.id = :contextid AND ctx.contextlevel = :contextlevel AND p.mode = :import
                       AND (p.importfrom > 0 AND a.teamsubmission = 0)";
        $userlist->add_from_sql('userid', $sql, $params);
        // TODO: std-Group-Members may be missing here!

        // Get all who got an extension!
        $sql = "SELECT e.userid
                  FROM {context} ctx
                  JOIN {course_modules} cm ON cm.id = ctx.instanceid
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modulename
                  JOIN {publication} p ON p.id = cm.instance
                  JOIN {publication_extduedates} e ON p.id = e.publication
                 WHERE ctx.id = :contextid AND ctx.contextlevel = :contextlevel";
        $userlist->add_from_sql('userid', $sql, $params);

        // Get all who gave (group) approval!
        $sql = "SELECT ga.userid
                  FROM {context} ctx
                  JOIN {course_modules} cm ON cm.id = ctx.instanceid
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modulename
                  JOIN {publication} p ON p.id = cm.instance
                  JOIN {publication_file} f ON p.id = f.publication
                  JOIN {publication_groupapproval} ga ON p.id = ga.fileid
                 WHERE ctx.id = :contextid AND ctx.contextlevel = :contextlevel";
        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param   approved_userlist       $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();

        if ($context->contextlevel == CONTEXT_MODULE) {
            // Apparently we can't trust anything that comes via the context.
            // Go go mega query to find out it we have an checkmark context that matches an existing checkmark.
            $sql = "SELECT p.id
                    FROM {publication} p
                    JOIN {course_modules} cm ON p.id = cm.instance AND p.course = cm.course
                    JOIN {modules} m ON m.id = cm.module AND m.name = :modulename
                    JOIN {context} ctx ON ctx.instanceid = cm.id AND ctx.contextlevel = :contextmodule
                    WHERE ctx.id = :contextid";
            $params = ['modulename' => 'publication', 'contextmodule' => CONTEXT_MODULE, 'contextid' => $context->id];
            $id = $DB->get_field_sql($sql, $params);
            // If we have an id over zero then we can proceed.
            if ($id > 0) {
                $userids = $userlist->get_userids();
                if (count($userids) <= 0) {
                    return;
                }

                $fs = get_file_storage();

                list($usersql, $userparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'usr');

                // Delete users' files, extended due dates and groupapprovals for this publication!
                $DB->delete_records_select('publication_extduedates', "publication = :id AND userid ".$usersql,
                        ['id' => $id] + $userparams);
                $files = $DB->get_records_select('publication_file', "publication = :id AND userid ".$usersql,
                        ['id' => $id] + $userparams);

                if ($files) {
                    $fileids = array_keys($files);
                    foreach ($files as $cur) {
                        $file = $fs->get_file_by_id($cur->fileid);
                        $file->delete();
                    }
                    list($filesql, $fileparams) = $DB->get_in_or_equal($fileids, SQL_PARAMS_NAMED, 'file');
                    $DB->delete_records_select('publication_groupapproval', "(fileid $filesql) AND (userid ".$usersql.")",
                            $fileparams + $userparams);
                    $DB->delete_records_list('publication_file', 'id', $fileids);
                }

            }
        }
    }


    /**
     * Write out the user data filtered by contexts.
     *
     *
     * @param approved_contextlist $contextlist contexts that we are writing data out from.
     * @throws \dml_exception
     * @throws \coding_exception
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $contexts = $contextlist->get_contexts();

        if (empty($contexts)) {
            return;
        }

        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        $sql = "SELECT
                    c.id AS contextid,
                    p.*,
                    cm.id AS cmid
                  FROM {context} c
                  JOIN {course_modules} cm ON cm.id = c.instanceid
                  JOIN {publication} p ON p.id = cm.instance
                 WHERE c.id {$contextsql}";

        // Keep a mapping of publicationid to contextid.
        $mappings = [];

        $publications = $DB->get_records_sql($sql, $contextparams);

        $user = $contextlist->get_user();

        foreach ($publications as $publication) {
            $context = \context_module::instance($publication->cmid);
            $mappings[$publication->id] = $publication->contextid;

            // Check that the context is a module context.
            if ($context->contextlevel != CONTEXT_MODULE) {
                continue;
            }

            $publicationdata = helper::get_context_data($context, $user);
            helper::export_context_files($context, $user);

            $cm = get_coursemodule_from_instance('publication', $publication->id);

            $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
            $publication = new \publication($cm, $course, $context);

            writer::with_context($context)->export_data([], $publicationdata);

            /* We don't differentiate between roles, if we have data about the user, we give it freely ;) - no sensible
             * information here! */

            static::export_user_preferences($user->id);
            static::export_extensions($context, $publication, $user);
            static::export_files($context, $publication, $user, []);
        }
    }

    /**
     * Stores the user preferences related to mod_publication.
     *
     * @param  int $userid The user ID that we want the preferences for.
     * @throws \dml_exception
     * @throws \coding_exception
     */
    public static function export_user_preferences(int $userid) {
        $context = \context_system::instance();
        $value = get_user_preferences('publication_perpage', null, $userid);
        if ($value !== null) {
            writer::with_context($context)->export_user_preference('mod_publication', 'publication_perpage', $value,
                    get_string('privacy:metadata:publicationperpage', 'mod_publication'));
        }
    }

    /**
     * Export overrides for this assignment.
     *
     * @param  \context $context Context
     * @param  \publication $pub The publication object.
     * @param  \stdClass $user The user object.
     * @throws \coding_exception
     */
    public static function export_extensions(\context $context, \publication $pub, \stdClass $user) {
        $ext = $pub->user_extensionduedate($user->id);
        // Overrides returns an array with data in it, but an override with actual data will have the assign ID set.
        if ($ext > 0) {
            $data = (object)[get_string('privacy:extensionduedate', 'mod_publication') => transform::datetime($ext)];
            writer::with_context($context)->export_data([], $data);
        }
    }

    /**
     * Fetches all of the user's files and adds them to the export
     *
     * @param  \context_module $context
     * @param  \publication $pub
     * @param  \stdClass $user
     * @param  array $path Current directory path that we are exporting to.
     * @throws \dml_exception
     * @throws \coding_exception
     */
    protected static function export_files(\context_module $context, \publication $pub, \stdClass $user, array $path) {
        global $DB;

        $groupimports = false;
        $emptygroup = false;
        if (($pub->get_instance()->mode == PUBLICATION_MODE_IMPORT) && ($pub->get_instance()->importfrom > 0)) {
            $assign = $DB->get_record('assign', ['id' => $pub->get_instance()->importfrom],
                    'name, teamsubmission, preventsubmissionnotingroup');
            $groupimports = $assign->teamsubmission;
            if ($groupimports && !$assign->preventsubmissionnotingroup) {
                $groups = groups_get_user_groups($pub->get_instance()->course, $user->id);
                $emptygroup = ($groups === [0 => []]) ? true : false;
            }
        }

        if ($groupimports) {
            // Imported files are saved under group's ID!
            if (!$emptygroup) {
                $rs = $DB->get_recordset_sql("
                    SELECT f.*
                      FROM {publication} p
                      JOIN {publication_file} f ON p.id = f.publication
                      JOIN {groups} g ON g.courseid = p.course
                      JOIN {groups_members} gm ON g.id = gm.groupid AND gm.userid = :userid AND f.userid = gm.groupid
                     WHERE p.id = :publication", [
                        'publication' => $pub->get_instance()->id,
                        'userid' => $user->id
                ]);
            } else {
                $rs = $DB->get_recordset("publication_file", [
                        'publication' => $pub->get_instance()->id,
                        'userid' => 0
                ]);
            }
        } else {
            // Imported and uploaded files are saved with user's ID!
            $rs = $DB->get_recordset_sql("SELECT f.*
                  FROM {publication} p
                  JOIN {publication_file} f ON p.id = f.publication
                 WHERE p.id = :publication AND f.userid = :userid", [
                    'publication' => $pub->get_instance()->id,
                    'userid' => $user->id
            ]);
        }

        foreach ($rs as $cur) {
            $filepath = array_merge($path, [get_string('privacy:path:files', 'mod_publication'), $cur->filename]);
            switch ($cur->type) {
                case PUBLICATION_MODE_ONLINETEXT:
                    static::export_onlinetext($context, $cur, $filepath);
                    break;
                default:
                    static::export_file($context, $cur, $filepath);
                    break;
            }
        }

        if ($groupimports) {
            static::export_groupapprovals($context, $pub, $user, $path);
        }
    }

    /**
     * Exports an uploaded/imported file!
     *
     * @param \context_module $context
     * @param \stdClass $file
     * @param array $path
     * @throws \coding_exception
     */
    protected static function export_file(\context_module $context, \stdClass $file, array $path) {
        // Export file!
        static $fs = null;

        if ($fs === null) {
            $fs = new \file_storage();
        }

        $fsfile = $fs->get_file_by_id($file->fileid);
        static::export_file_metadata($context, $file, $path);
        writer::with_context($context)->export_custom_file($path, $fsfile->get_filename(), $fsfile->get_content());
    }

    /**
     * Adds the metadata of an imported/uploaded file to the export!
     *
     * @param \context_module $context
     * @param \stdClass $file
     * @param array $path
     * @throws \coding_exception
     */
    protected static function export_file_metadata(\context_module $context, \stdClass $file, array $path) {
        // Export file's metadata!
        $export = (object)[
                'timecreated' => transform::datetime($file->timecreated),
                'filename' => $file->filename,
                'contenthash' => $file->contenthash,
                'teacherapproval' => transform::yesno($file->teacherapproval),
                'studentapproval' => transform::yesno($file->studentapproval)
        ];
        switch ($file->type) {
            case PUBLICATION_MODE_IMPORT:
                $export->type = get_string('privacy:type:import', 'publication');
                break;
            case PUBLICATION_MODE_UPLOAD:
                $export->type = get_string('privacy:type:upload', 'publication');
                break;
            case PUBLICATION_MODE_ONLINETEXT:
                $export->type = get_string('privacy:type:onlinetext', 'publication');
                break;
        }

        writer::with_context($context)->export_data($path, (object)$export);
    }

    /**
     * Adds an imported onlinetext and resources to export!
     *
     * @param \context_module $context
     * @param \stdClass $file
     * @param array $path
     * @throws \coding_exception
     */
    protected static function export_onlinetext(\context_module $context, \stdClass $file, array $path) {
        // Export file!
        static $fs = null;

        if ($fs === null) {
            $fs = new \file_storage();
        }

        $fsfile = $fs->get_file_by_id($file->fileid);

        static::export_file_metadata($context, $file, $path);
        writer::with_context($context)->export_custom_file($path, $fsfile->get_filename(), $fsfile->get_content());

        /*
         * Export resources!
         * We won't use writer::with_context($context)->export_area_files() due to us only needing a subdirectory!
         */
        $resources = $fs->get_directory_files($context->id,
                'mod_publication',
                'attachment',
                $fsfile->get_itemid(),
                '/resources/',
                true,
                false);
        if (count($resources) > 0) {
            foreach ($resources as $cur) {
                writer::with_context($context)->export_custom_file(array_merge($path, [
                        get_string('privacy:path:resources', 'mod_publication')
                ]), $cur->get_filename(), $cur->get_content());
            }
        }
    }

    /**
     * Fetches all of the user's group approvals and adds them to the export
     *
     * @param  \context $context
     * @param  \publication $pub
     * @param  \stdClass $user
     * @param  array $path Current directory path that we are exporting to.
     * @throws \dml_exception
     */
    protected static function export_groupapprovals(\context $context, \publication $pub, \stdClass $user, array $path) {
        global $DB;

        // Fetch all approvals!
        $rs = $DB->get_recordset_sql("SELECT ga.id, f.filename, ga.userid, ga.approval, ga.timecreated, ga.timemodified,
                                             f.userid AS groupid
                                        FROM {publication_groupapproval} ga
                                        JOIN {publication_file} f ON ga.fileid = f.id
                                       WHERE ga.userid = :userid AND f.publication = :publication", [
                'userid' => $user->id,
                'publication' => $pub->get_instance()->id
        ]);

        foreach ($rs as $cur) {
            static::export_groupapproval($context, $cur, $path);
        }

        $rs->close();
    }

    /**
     * Formats and then exports the user's approval data.
     *
     * @param  \context $context
     * @param  \stdClass $approval
     * @param  array $path Current directory path that we are exporting to.
     */
    protected static function export_groupapproval(\context $context, \stdClass $approval, array $path) {
        $approvaldata = (object)[
                'filename' => $approval->filename,
                'approval' => transform::yesno($approval->approval),
                'groupid' => $approval->groupid,
                'timecreated' => transform::datetime($approval->timecreated),
                'timemodified' => transform::datetime($approval->timemodified)
        ];

        writer::with_context($context)->export_data($path, $approvaldata);
    }

    /**
     * Delete all use data which matches the specified context.
     *
     * @param \context $context The module context.
     * @throws \dml_exception
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if ($context->contextlevel == CONTEXT_MODULE) {
            $fs = new \file_storage();

            // Apparently we can't trust anything that comes via the context.
            // Go go mega query to find out it we have an assign context that matches an existing assignment.
            $sql = "SELECT p.id
                    FROM {publication} p
                    JOIN {course_modules} cm ON p.id = cm.instance AND p.course = cm.course
                    JOIN {modules} m ON m.id = cm.module AND m.name = :modulename
                    JOIN {context} ctx ON ctx.instanceid = cm.id AND ctx.contextlevel = :contextmodule
                    WHERE ctx.id = :contextid";
            $params = ['modulename' => 'publication', 'contextmodule' => CONTEXT_MODULE, 'contextid' => $context->id];
            $id = $DB->get_field_sql($sql, $params);
            // If we have a count over zero then we can proceed.
            if ($id > 0) {
                // Get all publication files and group approvals to delete them!
                if ($files = $DB->get_records('publication_file', ['publication' => $id])) {
                    $fileids = array_keys($files);

                    // Go through all files and delete files and resources in filespace!
                    foreach ($files as $cur) {
                        $fs->delete_area_files($context->id, 'mod_publication', 'attachment', $cur->userid);
                    }

                    $DB->delete_records_list('publication_groupapproval', 'fileid', $fileids);
                    $DB->delete_records_list('publication_file', 'id', $fileids);
                }

                $DB->delete_records('publication_extduedates', ['publication' => $id]);
            }
        }
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     * @throws \dml_exception
     * @throws \coding_exception
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        $user = $contextlist->get_user();
        $fs = new \file_storage();

        $contextids = $contextlist->get_contextids();

        if (empty($contextids) || $contextids === []) {
            return;
        }

        list($ctxsql, $ctxparams) = $DB->get_in_or_equal($contextids, SQL_PARAMS_NAMED, 'ctx');

        // Apparently we can't trust anything that comes via the context.
        // Go go mega query to find out it we have an assign context that matches an existing assignment.
        $sql = "SELECT ctx.id AS ctxid, p.*
                    FROM {publication} p
                    JOIN {course_modules} cm ON p.id = cm.instance AND p.course = cm.course
                    JOIN {modules} m ON m.id = cm.module AND m.name = :modulename
                    JOIN {context} ctx ON ctx.instanceid = cm.id AND ctx.contextlevel = :contextmodule
                    WHERE ctx.id ".$ctxsql;
        $params = ['modulename' => 'publication', 'contextmodule' => CONTEXT_MODULE];

        if (!$records = $DB->get_records_sql($sql, $params + $ctxparams)) {
            return;
        }

        foreach ($contextlist as $context) {
            if ($context->contextlevel != CONTEXT_MODULE) {
                continue;
            }

            $pub = $records[$context->id];

            $teams = false;
            $emptygroup = false;
            if ($pub->mode === PUBLICATION_MODE_IMPORT) {
                $assign = $DB->get_record('assign', ['id' => $pub->importfrom]);
                $teams = $assign->teamsubmission;
                $usergroups = groups_get_user_groups($pub->course, $user->id);
                $emptygroup = $teams && !$assign->preventsubmissionnotingroup && ($usergroups === [0 => []]);
            }

            if ($emptygroup) {
                $files = $DB->get_records('publication_file', ['publication' => $pub->id, 'userid' => 0]);
            } else if (!$teams) {
                $files = $DB->get_records('publication_file', ['publication' => $pub->id, 'userid' => $user->id]);
            } else {
                $files = [];

                $usergroups = groups_get_all_groups($pub->course, $user->id);
                foreach (array_keys($usergroups) as $grpid) {
                    $files = $files + $DB->get_records('publication_file', ['publication' => $pub->id, 'userid' => $grpid]);
                }
            }

            if ($files) {
                $fileids = array_keys($files);

                // Go through all files and delete files and resources in filespace!
                foreach ($files as $cur) {
                    if (!$teams) {
                        $fs->delete_area_files($context->id, 'mod_publication', 'attachment', $cur->userid);
                    } else {
                        groups_remove_member($cur->userid, $user->id);
                    }
                }

                list($filesql, $fileparams) = $DB->get_in_or_equal($fileids, SQL_PARAMS_NAMED, 'file');
                $DB->delete_records_select('publication_groupapproval', 'userid = :userid AND fileid '.$filesql,
                        ['userid' => $user->id] + $fileparams);
                if (!$teams) {
                    $DB->delete_records_list('publication_file', 'id', $fileids);
                }
            }

            $DB->delete_records('publication_extduedates', ['publication' => $pub->id, 'userid' => $user->id]);
        }
    }
}
