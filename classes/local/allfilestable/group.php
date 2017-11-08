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
 * Contains class for files table listing all files for imported teamsubmissions
 *
 * @package       mod_publication
 * @author        Philipp Hager
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_publication\local\allfilestable;

defined('MOODLE_INTERNAL') || die();

/**
 * Table showing my group files
 *
 * @package       mod_publication
 * @author        Philipp Hager
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class group extends base {
    /** @var int grouping id for assign's team submissions */
    protected $groupingid = 0;
    /** @var bool if a group membership is required by assign's team submission */
    protected $requiregroup = 0;
    /** @var \stdClass course module object of assign to import from */
    protected $assigncm = null;
    /** @var \context_module context instance of assign to import from */
    protected $assigncontext = null;

    /**
     * Sets the predefined SQL for this table
     */
    protected function init_sql() {
        global $DB;

        $params = [];

        $fields = "g.id, g.name AS groupname, NULL AS groupmembers, COUNT(*) AS filecount,
                   SUM(files.studentapproval) AS studentapproval, NULL AS teacherapproval, MAX(files.timecreated) AS timemodified ";

        $groups = $this->publication->get_groups($this->groupingid);
        if (count($groups) > 0) {
            list($sqlgroupids, $groupparams) = $DB->get_in_or_equal($groups, SQL_PARAMS_NAMED, 'group');
            $params = $params + $groupparams + ['publication' => $this->cm->instance];
        } else {
            $sqlgroupids = " = :group ";
            $params = $params + ['group' => -1, 'publication' => $this->cm->instance];
        }

        if ($this->requiregroup || !count($this->publication->get_submissionmembers(0))) {
            $grouptable = '{groups} g ';
        } else {
            // If no group is required by assign to submit, we have to include all users without group as group 0 - standard group!
            $grouptable = " ( SELECT 0 AS id, :stdname AS name
                           UNION ALL
                              SELECT {groups}.id, {groups}.name AS name
                                FROM {groups}) AS g ";
            $params['stdname'] = get_string('defaultteam', 'assign');
        }

        $from = $grouptable . " LEFT JOIN {publication_file} files ON g.id = files.userid AND files.publication = :publication ";

        $where = "g.id " . $sqlgroupids;
        $groupby = " g.id, groupname, groupmembers, teacherapproval ";

        $this->set_sql($fields, $from, $where, $params, $groupby);
        $this->set_count_sql("SELECT COUNT(g.id) FROM " . $from . " WHERE " . $where, $params);

    }

    /**
     * constructor
     *
     * @param string $uniqueid a string identifying this table.Used as a key in session  vars.
     *                         It gets set automatically with the helper methods!
     * @param \publication $publication publication object
     */
    public function __construct($uniqueid, \publication $publication) {
        global $DB, $PAGE;

        $assignid = $publication->get_instance()->importfrom;
        $this->groupingid = $DB->get_field('assign', 'teamsubmissiongroupingid', ['id' => $assignid]);
        $this->requiregroup = $publication->requiregroup();
        $this->assigncm = get_coursemodule_from_instance('assign', $assignid, $publication->get_instance()->course);
        $this->assigncontext = \context_module::instance($this->assigncm->id);

        parent::__construct($uniqueid, $publication);

        $this->sortable(true, 'groupname'); // Sorted by group by default.
        $this->no_sorting('groupmembers');

        // Init JS!
        $params = new \stdClass();
        $params->id = $uniqueid;
        switch ($publication->get_instance()->groupapproval) {
            case PUBLICATION_APPROVAL_ALL;
                $params->mode = get_string('groupapprovalmode_all', 'mod_publication');
                break;
            case PUBLICATION_APPROVAL_SINGLE:
                $params->mode = get_string('groupapprovalmode_single', 'mod_publication');
                break;
        }

        $PAGE->requires->js_call_amd('mod_publication/groupapprovalstatus', 'initializer', [$params]);

        $params = new \stdClass();
        $cm = get_coursemodule_from_instance('publication', $publication->get_instance()->id);
        $params->cmid = $cm->id;
        $PAGE->requires->js_call_amd('mod_publication/onlinetextpreview', 'initializer', [$params]);
    }

    /**
     * This function is not part of the public api.
     */
    public function print_nothing_to_display() {
        global $OUTPUT;

        // Render button to allow user to reset table preferences.
        echo $this->render_reset_button();

        $this->print_initials_bar();

        echo $OUTPUT->box(get_string('nothing_to_show_groups', 'publication'), 'font-italic');
    }

    /**
     * Return all columns, column-headers and helpicons for this table
     *
     * @return array Array with column names, column headers and help icons
     */
    protected function get_columns() {
        $selectallnone = \html_writer::checkbox('selectallnone', false, false, '', [
                'id' => 'selectallnone',
                'onClick' => 'toggle_userselection()'
        ]);

        $columns = ['selection', 'groupname', 'groupmembers', 'timemodified'];
        $headers = [$selectallnone, get_string('group'), get_string('groupmembers'), get_string('lastmodified')];
        $helpicons = [null, null, null, null];

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

        // Import and upload tables will enhance this list! Import from teamassignments will overwrite it!
        return [$columns, $headers, $helpicons];
    }

    /**
     * Display members of the group
     *
     * @param object $values Contains object with all the values of record.
     * @return string Return groups members.
     */
    public function col_groupmembers($values) {
        $cell = '';

        $groupmembers = $this->publication->get_submissionmembers($values->id);

        if (!count($groupmembers)) {
            return $cell;
        }

        foreach ($groupmembers as $cur) {
            $cell .= \html_writer::tag('div', parent::col_fullname($cur));
        }

        return $cell;
    }

    /**
     * Method wraps string with span-element including data attributes containing detailed group approval data!
     *
     * @param string $symbol string/html-snippet to wrap element around
     * @param \stored_file $file file to fetch details for
     */
    protected function add_details_tooltip(&$symbol, \stored_file $file) {
        global $DB, $OUTPUT;

        $pubfileid = $DB->get_field('publication_file', 'id', [
                'publication' => $this->publication->get_instance()->id,
                'fileid' => $file->get_id()
        ]);
        list(, $approvaldetails) = $this->publication->group_approval($pubfileid);

        $approved = [];
        $rejected = [];
        $pending = [];
        foreach ($approvaldetails as $cur) {
            if (empty($cur->approvaltime)) {
                $cur->approvaltime = '-';
            } else {
                $cur->approvaltime = userdate($cur->approvaltime, get_string('strftimedatetime'));
            }
            if ($cur->approval === null) {
                $pending[] = ['name' => fullname($cur), 'time' => '-'];;
            } else if ($cur->approval == 0) {
                $rejected[] = ['name' => fullname($cur), 'time' => $cur->approvaltime];
            } else if ($cur->approval == 1) {
                $approved[] = ['name' => fullname($cur), 'time' => $cur->approvaltime];
            }
        }

        $status = new \stdClass();
        $status->approved = false;
        $status->rejected = false;
        $status->pending = false;
        switch ($this->publication->student_approval($file)) {
            case 2:
                $status->approved = true;
                break;
            case 1:
                $status->rejected = true;
                break;
            default:
                $status->pending = true;
        }

        $detailsattr = [
                'class' => 'approvaldetails',
                'data-pending' => json_encode($pending),
                'data-approved' => json_encode($approved),
                'data-rejected' => json_encode($rejected),
                'data-filename' => $file->get_filename(),
                'data-status' => json_encode($status)
        ];

        $symbol = $symbol . \html_writer::tag('span', $OUTPUT->pix_icon('i/preview', get_string('show_details', 'publication')),
                        $detailsattr);

    }

    /**
     * Caches and returns itemnames for given itemids
     *
     * @param int $itemid
     * @return string Itemname
     */
    protected function get_itemname($itemid) {
        if (!array_key_exists($itemid, $this->itemnames)) {
            $this->itemnames[$itemid] = groups_get_group_name($itemid);
        }

        return $this->itemnames[$itemid];
    }
}
