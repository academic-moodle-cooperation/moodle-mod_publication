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
 * Base class for classes listing all files imported or uploaded
 *
 * @package       mod_publication
 * @author        Philipp Hager
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_publication\local\allfilestable;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/publication/locallib.php');
require_once($CFG->libdir . '/tablelib.php');

/**
 * Base class for tables showing all (public) files (upload or import)
 *
 * @package       mod_publication
 * @author        Philipp Hager
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class base extends \table_sql {
    /** @var \publication publication object */
    protected $publication = null;
    /** @var \context_module context instance object */
    protected $context;
    /** @var \stdClass coursemodule object */
    protected $cm = null;
    /** @var \file_storage file storage */
    protected $fs = null;
    /** @var \stored_file[] files */
    protected $files = null;
    /** @var \stored_file[] resource-files */
    protected $resources = null;
    /** @var int current itemid for files array */
    protected $itemid = null;
    /** @var int amount of files in table, get's counted during formating of the rows! */
    protected $totalfiles = null;
    /** @var string[] of cached itemnames */
    protected $itemnames = [];

    /** @var int activity's groupmode */
    protected $groupmode = 0;
    /** @var int current group if group mode is active */
    protected $currentgroup = 0;
    /** @var string valid pix-icon */
    protected $valid = '';
    /** @var string questionmark pix-icon */
    protected $questionmark = '';
    /** @var string invalid pix-icon */
    protected $invalid = '';
    /** @var string student visible pix-icon */
    protected $studvisibleyes = '';
    /** @var string student not visible pix-icon */
    protected $studvisibleno = '';
    /** @var string[] select box options */
    protected $options = [];
    /** @var int[] $users */
    protected $users = [];

    /**
     * constructor
     *
     * @param string $uniqueid a string identifying this table.Used as a key in session  vars.
     *                         It gets set automatically with the helper methods!
     * @param \publication $publication publication object
     */
    public function __construct($uniqueid, \publication $publication) {
        global $CFG, $OUTPUT;

        parent::__construct($uniqueid);

        $this->fs = get_file_storage();
        $this->publication = $publication;
        $this->cm = get_coursemodule_from_instance('publication', $publication->get_instance()->id, 0, false, MUST_EXIST);
        $this->context = \context_module::instance($this->cm->id);
        $this->groupmode = groups_get_activity_groupmode($this->cm);
        $this->currentgroup = groups_get_activity_group($this->cm, true);

        list($columns, $headers, $helpicons) = $this->get_columns();
        $this->define_columns($columns);
        $this->define_headers($headers);
        $this->define_help_for_headers($helpicons);

        $this->define_baseurl($CFG->wwwroot . '/mod/publication/view.php?id=' . $this->cm->id . '&amp;currentgroup=' .
                $this->currentgroup);

        $this->sortable(true, 'lastname'); // Sorted by lastname by default.
        $this->collapsible(true);
        $this->initialbars(true);

        $this->column_suppress('picture');
        $this->column_suppress('fullname');
        $this->column_suppress('group');

        $this->column_class('fullname', 'fullname');
        $this->column_class('timemodified', 'timemodified');

        $this->set_attribute('cellspacing', '0');
        $this->set_attribute('id', 'attempts');
        $this->set_attribute('class', 'publications');
        $this->set_attribute('width', '100%');

        $this->no_sorting('studentapproval');
        $this->no_sorting('selection');

        $this->no_sorting('visibleforstudents');

        $this->init_sql();

        // Save status of table(s) persistent as user preference!
        $this->is_persistent(true);

        $this->valid = $OUTPUT->pix_icon('i/valid', get_string('student_approved', 'publication'));
        $this->questionmark = $OUTPUT->pix_icon('questionmark', get_string('student_pending', 'publication'), 'mod_publication');
        $this->invalid = $OUTPUT->pix_icon('i/invalid', get_string('student_rejected', 'publication'));

        $this->studvisibleyes = $OUTPUT->pix_icon('i/valid', get_string('visibleforstudents_yes', 'publication'));
        $this->studvisibleno = $OUTPUT->pix_icon('i/invalid', get_string('visibleforstudents_no', 'publication'));

        $this->options = [
                1 => get_string('yes'),
                2 => get_string('no')
        ];
    }

    /**
     * This function is not part of the public api.
     */
    public function print_nothing_to_display() {
        global $OUTPUT;

        // Render button to allow user to reset table preferences.
        echo $this->render_reset_button();

        $this->print_initials_bar();

        echo $OUTPUT->box(get_string('nothing_to_show_users', 'publication'), 'font-italic');
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

        $columns = ['selection', 'picture', 'fullname'];
        $headers = [$selectallnone, '', get_string('fullnameuser')];
        $helpicons = [null, null, null];

        $useridentity = get_extra_user_fields($this->context);
        foreach ($useridentity as $cur) {
            if (has_capability('mod/publication:approve', $this->context)) {
                $columns[] = $cur;
                $headers[] = ($cur == 'phone1') ? get_string('phone') : get_string($cur);
                $helpicons[] = null;
            }
        }

        $columns[] = 'timemodified';
        $headers[] = get_string('lastmodified');
        $helpicons[] = null;

        // Import and upload tables will enhance this list! Import from teamassignments will overwrite it!
        return [$columns, $headers, $helpicons];
    }

    /**
     * Setter for users property
     *
     * @param int[] $users
     */
    protected function set_users($users) {
        $this->users = $users;
    }

    /**
     * Sets the predefined SQL for this table
     */
    protected function init_sql() {
        global $DB;

        $params = [];
        $ufields = \user_picture::fields('u');
        $useridentityfields = get_extra_user_fields_sql($this->context, 'u');

        $fields = $ufields . ' ' . $useridentityfields . ', u.username,
                                COUNT(*) filecount,
                                SUM(files.studentapproval) AS studentapproval,
                                SUM(files.teacherapproval) AS teacherapproval,
                                MAX(files.timecreated) AS timemodified ';

        // Also filters out users according to set activitygroupmode & current activitygroup!
        $users = $this->publication->get_users();
        list($sqluserids, $userparams) = $DB->get_in_or_equal($users, SQL_PARAMS_NAMED, 'user');
        $params = $params + $userparams + ['publication' => $this->cm->instance];

        $from = '{user} u ' .
                'LEFT JOIN {publication_file} files ON u.id = files.userid AND files.publication = :publication ';

        $where = "u.id " . $sqluserids;
        $groupby = $ufields . ' ' . $useridentityfields . ', u.username ';

        $this->set_sql($fields, $from, $where, $params, $groupby);
        $this->set_count_sql("SELECT COUNT(u.id) FROM " . $from . " WHERE " . $where, $params);

    }

    /**
     * Set the sql to query the db. Query will be :
     *      SELECT $fields FROM $from WHERE $where
     * Of course you can use sub-queries, JOINS etc. by putting them in the
     * appropriate clause of the query.
     *
     * @param string $fields fields to fetch (SQL snippet)
     * @param string $from from where to fetch (SQL snippet)
     * @param string $where where conditions for SQL query (SQL snippet)
     * @param array $params (optional) params for query
     * @param string $groupby (optional) groupby clause (SQL snippet)
     */
    public function set_sql($fields, $from, $where, array $params = null, $groupby = '') {
        parent::set_sql($fields, $from, $where, $params);
        $this->sql->groupby = $groupby;
    }

    /**
     * Query the db. Store results in the table object for use by build_table. We had to override, due to group by clause!
     *
     * @param int $pagesize size of page for paginated displayed table.
     * @param bool $useinitialsbar do you want to use the initials bar. Bar
     * will only be used if there is a fullname column defined for the table.
     */
    public function query_db($pagesize, $useinitialsbar = true) {
        global $DB;
        if (!$this->is_downloading()) {
            if ($this->countsql === null) {
                $this->countsql = 'SELECT COUNT(1) FROM ' . $this->sql->from . ' WHERE ' . $this->sql->where;
                $this->countparams = $this->sql->params;
            }
            $grandtotal = $DB->count_records_sql($this->countsql, $this->countparams);
            if ($useinitialsbar && !$this->is_downloading()) {
                $this->initialbars($grandtotal > $pagesize);
            }

            list($wsql, $wparams) = $this->get_sql_where();
            if ($wsql) {
                $this->countsql .= ' AND ' . $wsql;
                $this->countparams = array_merge($this->countparams, $wparams);

                $this->sql->where .= ' AND ' . $wsql;
                $this->sql->params = array_merge($this->sql->params, $wparams);

                $total = $DB->count_records_sql($this->countsql, $this->countparams);
            } else {
                $total = $grandtotal;
            }

            $this->pagesize($pagesize, $total);
        }

        // Fetch the attempts!
        $sort = $this->get_sql_sort();
        if ($sort) {
            $sort = "ORDER BY $sort";
        }
        $sql = "SELECT {$this->sql->fields}
                  FROM {$this->sql->from}
                 WHERE {$this->sql->where}
               " . ($this->sql->groupby ? "GROUP BY {$this->sql->groupby}" : "") . "
               {$sort}";
        if (!$this->is_downloading()) {
            $this->rawdata = $DB->get_records_sql($sql, $this->sql->params, $this->get_page_start(), $this->get_page_size());
        } else {
            $this->rawdata = $DB->get_records_sql($sql, $this->sql->params);
        }
    }

    /**
     * Returns all files to be displayed for this itemid (=userid or groupid)
     *
     * @param int $itemid User or group ID to fetch files for
     * @return array Array with itemid, files-array and resources-array as items
     */
    public function get_files($itemid) {
        if (($itemid === $this->itemid) && (($this->files !== null) || ($this->resources !== null))) {
            // We cache just the current files, to use less memory!
            return [$this->itemid, $this->files, $this->resources];
        }

        $contextid = $this->publication->get_context()->id;
        $filearea = 'attachment';

        $this->itemid = $itemid;
        $this->files = [];
        $this->resources = [];

        $files = $this->fs->get_area_files($contextid, 'mod_publication', $filearea, $this->itemid, 'timemodified', false);

        foreach ($files as $file) {
            if ($file->get_filepath() == '/resources/') {
                $this->resources[] = $file;
            } else {
                $this->files[] = $file;
            }
        }

        return [$this->itemid, $this->files, $this->resources];
    }

    /**
     * Returns the amount of files displayed in this table!
     */
    public function totalfiles() {
        if ($this->totalfiles !== null) {
            return $this->totalfiles;
        } else {
            return 0;
        }
    }

    /**
     * Method wraps string with span-element including data attributes containing detailed group approval data!
     * Is implemented/overwritten where needed!
     *
     * @param string $symbol string/html-snippet to wrap element around
     * @param \stored_file $file file to fetch details for
     */
    protected function add_details_tooltip(&$symbol, \stored_file $file) {
        // This method does nothing here!
    }

    /**
     * Method returns online-text-preview where it's needed!
     * Is implemented/overwritten where needed!
     *
     * @param int $itemid user ID or group ID
     * @param int $fileid file ID
     * @return string link to onlinetext-preview
     */
    protected function add_onlinetext_preview($itemid, $fileid) {
        global $DB, $OUTPUT;
        // This method does nothing here!
        // Get file data/record!
        $conditions = [
                'publication' => $this->cm->instance,
                'userid' => $itemid,
                'fileid' => $fileid,
                'type' => PUBLICATION_MODE_ONLINETEXT
        ];
        if (!$DB->record_exists('publication_file', $conditions)) {
            return '';
        }

        $itemname = $this->get_itemname($itemid);

        $url = new \moodle_url('/mod/publication/onlinepreview.php', [
                'id' => $this->cm->id,
                'itemid' => $itemid,
                'itemname' => $itemname
        ]);

        $detailsattr = [
                'class' => 'onlinetextpreview',
                'data-itemid' => $itemid,
                'data-itemname' => $itemname
        ];
        $symbol = \html_writer::tag('span', $OUTPUT->pix_icon('i/preview', get_string('preview')), $detailsattr);

        return \html_writer::link($url, $symbol, ['target' => '_blank']);
    }

    /**
     * Caches and returns itemnames for given itemids
     *
     * @param int $itemid
     * @return string Itemname
     */
    protected function get_itemname($itemid) {
        global $DB;

        if (!array_key_exists($itemid, $this->itemnames)) {
            $user = $DB->get_record('user', ['id' => $itemid]);
            $this->itemnames[$itemid] = fullname($user);
        }

        return $this->itemnames[$itemid];
    }

    /***************************************************************
     *** COLUMN OUTPUT METHODS *************************************
     **************************************************************/

    /**
     * This function is called for each data row to allow processing of the
     * XXX value.
     *
     * @param object $values Contains object with all the values of record.
     * @return string Return XXX.
     */
    public function col_selection($values) {
        // If the data is being downloaded than we don't want to show HTML.
        if ($this->is_downloading()) {
            return '';
        } else {
            return \html_writer::checkbox('selectedeuser[' . $values->id . ']', 'selected', false, null,
                    ['class' => 'userselection']);
        }
    }

    /**
     * This function is called for each data row to allow processing of the
     * user's name with link and optional extension date.
     *
     * @param object $values Contains object with all the values of record.
     * @return string Return user fullname.
     */
    public function col_fullname($values) {
        // Saves DB access in \mod_publication\local\allfilestable::get_itemname()!
        if (!array_key_exists($values->id, $this->itemnames)) {
            $this->itemnames[$values->id] = fullname($values);
        }

        $extension = $this->publication->user_extensionduedate($values->id);
        if ($extension) {
            if (has_capability('mod/publication:grantextension', $this->context) ||
                    has_capability('mod/publication:approve', $this->context)) {
                $extensiontxt = \html_writer::empty_tag('br') . "\n" .
                        get_string('extensionto', 'publication') . ': ' . userdate($extension);
            } else {
                $extensiontxt = '';
            }
        } else {
            $extensiontxt = '';
        }

        if ($this->is_downloading()) {
            return strip_tags(parent::col_fullname($values) . $extensiontxt);
        } else {
            return parent::col_fullname($values) . $extensiontxt;
        }
    }


    /**
     * This function is called for each data row to allow processing of the
     * group. Also caches group name in itemnames for onlinetext-preview!
     *
     * @param object $values Contains object with all the values of record.
     * @return string Return group's name.
     */
    public function col_groupname($values) {
        // Saves DB access in \mod_publication\local\allfilestable::get_itemname()!
        if (!array_key_exists($values->id, $this->itemnames)) {
            $this->itemnames[$values->id] = $values->groupname;
        }

        return $values->groupname;
    }

    /**
     * This function is called for each data row to allow processing of the
     * user picture.
     *
     * @param object $values Contains object with all the values of record.
     * @return string Return user picture markup.
     */
    public function col_picture($values) {
        global $OUTPUT;
        // If the data is being downloaded than we don't want to show HTML.
        if ($this->is_downloading()) {
            return '';
        } else {
            return $OUTPUT->user_picture($values);
        }
    }

    /**
     * This function is called for each data row to allow processing of the
     * user's groups.
     *
     * @param object $values Contains object with all the values of record.
     * @return string Return user groups.
     */
    public function col_groups($values) {
        $groups = groups_get_all_groups($this->publication->get_instance()->course, $values->id, 0, 'g.name');
        if (!empty($groups)) {
            $values->groups = '';
            foreach ($groups as $group) {
                if ($values->groups != '') {
                    $values->groups .= ', ';
                }
                $values->groups .= $group->name;
            }
            if ($this->is_downloading()) {
                return $values->groups;
            } else {
                return \html_writer::tag('div', $values->groups, ['id' => 'gr' . $values->id]);
            }
        } else if ($this->is_downloading()) {
            return '';
        } else {
            return \html_writer::tag('div', '-', ['id' => 'gr' . $values->id]);
        }
    }

    /**
     * This function is called for each data row to allow processing of the
     * user's submission time.
     *
     * @param object $values Contains object with all the values of record.
     * @return string Return user time of submission.
     */
    public function col_timemodified($values) {
        global $OUTPUT;

        list(, $files, ) = $this->get_files($values->id);

        $filetable = new \html_table();
        $filetable->attributes = ['class' => 'filetable'];

        foreach ($files as $file) {
            if (has_capability('mod/publication:approve', $this->context)
                    || $this->publication->has_filepermission($file->get_id())) {
                $filerow = [];
                $filerow[] = $OUTPUT->pix_icon(file_file_icon($file), get_mimetype_description($file));

                $url = new \moodle_url('/mod/publication/view.php', ['id' => $this->cm->id, 'download' => $file->get_id()]);
                $filerow[] = \html_writer::link($url, $file->get_filename()) .
                        $this->add_onlinetext_preview($values->id, $file->get_id());

                $filetable->data[] = $filerow;
            }
        }

        if ($this->totalfiles === null) {
            $this->totalfiles = 0;
        }
        if (count($filetable->data) > 0) {
            $lastmodified = \html_writer::table($filetable);
            $lastmodified .= \html_writer::span(userdate($values->timemodified), "timemodified");
            $this->totalfiles += count($filetable->data);
        } else {
            $lastmodified = get_string('nofiles', 'publication');
        }

        // TODO: download without tags?
        return $lastmodified;
    }

    /**
     * This function is called for each data row to allow processing of the
     * file status.
     *
     * @param object $values Contains object with all the values of record.
     * @return string Return user time of submission.
     */
    public function col_studentapproval($values) {
        list(, $files, ) = $this->get_files($values->id);

        $table = new \html_table();
        $table->attributes = ['class' => 'statustable'];

        foreach ($files as $file) {
            if (has_capability('mod/publication:approve', $this->context)
                    || $this->publication->has_filepermission($file->get_id())) {
                switch ($this->publication->student_approval($file)) {
                    case 2:
                        $symbol = $this->valid;
                        break;
                    case 1:
                        $symbol = $this->invalid;
                        break;
                    default:
                        $symbol = $this->questionmark;
                }
                $this->add_details_tooltip($symbol, $file);
                $table->data[] = [$symbol];
            }
        }

        if (count($table->data) > 0) {
            return \html_writer::table($table);
        } else {
            return '';
        }
    }

    /**
     * This function is called for each data row to allow processing of the
     * file permission.
     *
     * @param object $values Contains object with all the values of record.
     * @return string Return user time of submission.
     */
    public function col_teacherapproval($values) {

        list(, $files, ) = $this->get_files($values->id);

        $table = new \html_table();
        $table->attributes = ['class' => 'permissionstable'];

        foreach ($files as $file) {
            if ($this->publication->has_filepermission($file->get_id())
                    || has_capability('mod/publication:approve', $this->context)) {

                $checked = $this->publication->teacher_approval($file);
                // Null if none found, DB-entry otherwise!
                // TODO change that conversions and queue the real values! Everywhere!
                $checked = ($checked === false || $checked === null) ? "" : $checked;

                $sel = \html_writer::select($this->options, 'files[' . $file->get_id() . ']', (string)$checked);
                $table->data[] = [$sel];
            }
        }

        if (count($table->data) > 0) {
            return \html_writer::table($table);
        } else {
            return '';
        }
    }

    /**
     * This function is called for each data row to allow processing of the
     * file visibility.
     *
     * @param object $values Contains object with all the values of record.
     * @return string Return user time of submission.
     */
    public function col_visibleforstudents($values) {
        list(, $files, ) = $this->get_files($values->id);

        $table = new \html_table();
        $table->attributes = ['class' => 'statustable'];

        foreach ($files as $file) {
            if ($this->publication->has_filepermission($file->get_id())) {
                $table->data[] = [$this->studvisibleyes];
            } else {
                $table->data[] = [$this->studvisibleno];
            }
        }

        // TODO: download without tags?
        if (count($table->data) > 0) {
            return \html_writer::table($table);
        } else {
            return '';
        }
    }

    /**
     * This function is called for each data row to allow processing of
     * columns which do not have a *_cols function.
     *
     * @param string $colname Name of current column
     * @param object $values Values of the current row
     * @return string return processed value.
     */
    public function other_cols($colname, $values) {
        // Process user identity fields!
        $useridentity = get_extra_user_fields($this->context);
        if ($colname === 'phone') {
            $colname = 'phone1';
        }
        if (in_array($colname, $useridentity)) {
            if (!empty($values->$colname)) {
                if ($this->is_downloading()) {
                    return $values->$colname;
                } else {
                    return \html_writer::tag('div', $values->$colname, ['id' => 'u' . $colname . $values->id]);
                }
            } else {
                if ($this->is_downloading()) {
                    return '-';
                } else {
                    return \html_writer::tag('div', '-', ['id' => 'u' . $colname . $values->id]);
                }
            }
        }

        return $values->$colname;
    }
}
