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
 * Contains class for files table listing files imported from one's group(s) (and options for approving them)
 *
 * @package       mod_publication
 * @author        Philipp Hager
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_publication\local\filestable;

/**
 * Table showing my group files
 *
 * @package       mod_publication
 * @author        Philipp Hager
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class group extends base {
    /** @var int $groupingid saves the team-assignments submission grouping id */
    protected $groupingid = 0;

    /**
     * Get the approval status for a file
     *
     * @param \stored_file $file file to get the approval status for
     * @return string HTML for the approval status with icon
     */
    public function get_approval_status_for_file($file) {
        global $OUTPUT, $DB, $USER;

        $pubfileid = $DB->get_field('publication_file', 'id', [
            'publication' => $this->publication->get_instance()->id,
            'fileid' => $file->get_id(),
        ]);
        $templatecontext = new \stdClass();
        // Now add the specific data to the table!
        $teacherapproval = $this->publication->teacher_approval($file);
        [$studentapproval, $approvaldetails] = $this->publication->group_approval($pubfileid);

        $obtainteacherapproval = $this->publication->get_instance()->obtainteacherapproval;
        $obtainstudentapproval = $this->publication->get_instance()->obtainstudentapproval;

        $studentapproved = false;
        $studentdenied = false;
        $studentpending = false;
        $hint = '';

        if ($obtainstudentapproval == 1) {
            $pendingstudents = [];
            $rejectedstudents = [];
            $approvedstudents = [];

            foreach ($approvaldetails as $cur) {
                if ($cur->approval == null) {
                    $pendingstudents[] = fullname($cur);
                } else if ($cur->approval == 0) {
                    $rejectedstudents[] = fullname($cur);
                } else {
                    $approvedstudents[] = fullname($cur);
                }
            }
            $rejected = get_string('rejected', 'publication') . ': ' . implode(', ', $rejectedstudents) . '. ';
            $pending = get_string('pending', 'publication') . ': ' . implode(', ', $pendingstudents) . '. ';
            $approved = get_string('approved', 'publication') . ': ' . implode(', ', $approvedstudents) . '. ';

            if ($studentapproval == 1) {
                $studentapproved = true;
                if ($this->publication->get_instance()->groupapproval == PUBLICATION_APPROVAL_SINGLE) {
                    $hint = $approved;
                } else {
                    $hint = get_string('group_approved', 'publication');
                }
            } else if ($studentapproval == 2 || !empty($rejectedstudents)) {
                $studentdenied = true;
                $hint = $rejected;
            } else {
                $hint = $pending;
            }
            $currentstudentfound = false;
            $currentstudentpending = false;
            foreach ($approvaldetails as $cur) {
                if ($cur->userid == $USER->id) {
                    $currentstudentfound = true;
                    if ($cur->approval === null) {
                        $currentstudentpending = true;
                    }
                }
            }

            if (!$currentstudentfound || $currentstudentpending) {
                if (empty($rejectedstudents)) {
                    if ($this->publication->is_approval_open()) {
                        $this->changepossible = true;
                        return \html_writer::select(
                            $this->options,
                            'studentapproval[' . $file->get_id() . ']',
                            '0',
                            ['' => 'choosedots'],
                            ['class' => 'd-inline-block']
                        );
                    }
                }
            }
        } else {
            $studentapproved = true;
            $hint = get_string('student_approved_automatically', 'publication');
        }

        $hint .= ' ';

        $teacherapproved = false;
        $teacherdenied = false;
        $teacherpending = false;

        if ($obtainteacherapproval == 1) {
            if ($teacherapproval == 1) {
                $teacherapproved = true;
                $hint .= get_string('teacher_approved', 'publication');
            } else if ($teacherapproval == 2) {
                $teacherdenied = true;
                $hint .= get_string('teacher_rejected', 'publication');
            } else {
                $teacherpending = true;
                $hint .= get_string('teacher_pending', 'publication');
            }
        } else {
            $teacherapproved = true;
            $hint .= get_string('teacher_approved_automatically', 'publication');
        }

        if ($studentapproved && $teacherapproved) {
            $templatecontext->icon = $this->valid;
        } else if ($studentdenied || $teacherdenied) {
            $templatecontext->icon = $this->invalid;
        } else {
            $templatecontext->icon = $this->questionmark;
        }
        $templatecontext->hint = $hint;
        return $OUTPUT->render_from_template('mod_publication/approval_icon', $templatecontext);
    }

    /**
     * Get all files, in which the current user's groups are involved
     *
     * @return \stored_file[] array of stored_files indexed by pathanmehash
     */
    public function get_files() {
        global $USER, $DB;

        if ($this->files !== null) {
            return $this->files;
        }

        $contextid = $this->publication->get_context()->id;
        $filearea = 'attachment';

        /* OK, assign is a little bit inconsistent with implementation and doc-comments, it states it will return false for user's
         * group if there's no group or multiple groups, instead it uses just the first group it finds for the user!
         * So if assign doesn't behave that exact, we just use all users groups (except there's a groupingid set for submission! */
        $assignid = $this->publication->get_instance()->importfrom;
        $this->groupingid = $DB->get_field('assign', 'teamsubmissiongroupingid', ['id' => $assignid]);
        $groups = groups_get_all_groups($this->publication->get_instance()->course, $USER->id, $this->groupingid);
        if (empty($groups)) {
            // Users without group membership get assigned group id 0!
            $groups = [];
            $groups[0] = new \stdClass();
            $groups[0]->id = 0;
        }

        foreach ($groups as $group) {
            $itemid = $group->id;

            $files = $this->fs->get_area_files($contextid, 'mod_publication', $filearea, $itemid, 'timemodified', false);

            foreach ($files as $file) {
                if ($file->get_filepath() == '/resources/') {
                    $this->resources[] = $file;
                } else {
                    $this->files[] = $file;
                }
                if ($this->lastmodified < $file->get_timemodified()) {
                    $this->lastmodified = $file->get_timemodified();
                }
            }
        }

        return $this->files;
    }
}
