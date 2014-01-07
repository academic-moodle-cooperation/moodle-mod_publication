<?php
// This plugin is for Moodle - http://moodle.org/
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
 * Strings for component 'mod_publication', language 'en'
 * 
 * @package mod_publication
 * @author Andreas Windbichler
 * @copyright TSC
 */

$string['modulename'] = 'Student folder';
$string['pluginname'] = 'Student folder';
$string['modulename_help'] = 'The student folder offers the following features:

* Participants can upload documents that are available to other participants immediately or after you have checked the documents and given your consent.
* An assignment can be chosen as a basis for a student folder. The teacher can decide which documents of the assignment are visible for all participants. Teachers can also let the participants decide whether their documents should be visible to others.';
$string['modulenameplural'] = 'Student folders';
$string['pluginadministration'] = 'Student folder administration';
$string['publication:addinstance'] = 'Add a new student folder';
$string['publication:view'] = 'View student folder';
$string['publication:upload'] = 'Upload files to a student folder';
$string['publication:approve'] = 'Decide if files should be visible for every student';

$string['requiremodintro'] = 'Require activity description';
$string['configrequiremodintro'] = 'Disable this option if you do not want to force users to enter description of each activity.';
$string['obtainstudentapproval'] = 'Obtain approval';
$string['configobtainstudentapproval'] = 'Documents are visible after the student´s consent.';
$string['obtainteacherapproval'] = 'Approved by default';
$string['configobtainteacherapproval'] = 'Documents of students are by default visible for all other participants.';
$string['maxfiles'] = 'Maximum number of attachments';
$string['configmaxfiles'] = 'Default maximum number of attachments allowed per user.';
$string['maxbytes'] = 'Maximum attachment size';
$string['configmaxbytes'] = 'Default maximum size for all files in the studentfolder.';

// mod_form
$string['availability'] = 'Availability';

$string['allowsubmissionsfromdate'] = 'Allow submissions from';
$string['allowsubmissionsfromdate_help'] = 'If enabled, students will not be able to submit before this date. If disabled, students will be able to start submitting right away.';
$string['allowsubmissionsfromdatesummary'] = 'This assignment will accept submissions from <strong>{$a}</strong>';
$string['allowsubmissionsanddescriptionfromdatesummary'] = 'The assignment details and submission form will be available from <strong>{$a}</strong>';
$string['alwaysshowdescription'] = 'Always show description';
$string['alwaysshowdescription_help'] = 'If disabled, the Assignment Description above will only become visible to students at the "Allow submissions from" date.';

$string['duedate'] = 'Due date';
$string['duedate_help'] = 'This is when the assignment is due. Submissions will still be allowed after this date but any assignments submitted after this date are marked as late. To prevent submissions after a certain date - set the assignment cut off date.';
$string['duedatevalidation'] = 'Due date must be after the allow submissions from date.';

$string['cutoffdate'] = 'Cut-off date';
$string['cutoffdate_help'] = 'If set, the assignment will not accept submissions after this date without an extension.';
$string['cutoffdatevalidation'] = 'The cut-off date cannot be earlier than the due date.';
$string['cutoffdatefromdatevalidation'] = 'Cut-off date must be after the allow submissions from date.';


$string['mode'] = 'Mode';
$string['mode_help'] = 'Choose whether students can upload documents in the folder or documents of an assignment are the source of it.';
$string['modeupload'] = 'students can upload documents';
$string['modeimport'] = 'take documents from an assignment';

$string['courseuploadlimit'] = 'Course upload limit';
$string['allowedfiletypes'] = 'Allowed filetypes (,)';
$string['allowedfiletypes_help'] = 'Set the allowed filetypes for uploading assignments, separated by a comma (,). e.g.: txt, jpg.
If any file type is possible, leave the field empty. The filter is not case sensitive, so PDF equals pdf.';
$string['allowedfiletypes_err'] = 'Check input! Invalid file extensions or seperators';
$string['obtainteacherapproval_help'] = 'If set to no, entries require approving by a teacher before they are viewable by everyone.';
$string['assignment'] = 'Assignment';
$string['obtainstudentapproval_help'] = 'You can decide whether students can publish their assignment submissions on their own or not. The list of students asked for their approval can be chosen. The submissions are only visible after students have given their approval';
$string['choose'] = 'please choose ...';
$string['importfrom_err'] = 'You have to choose an assignment you want to import from.';

$string['warning_changefromobtainteacherapproval'] = 'After activating this setting, all uploaded files will be visible to other participants. All uploaded will become visible. You can manually make files invisible to certain students.';
$string['warning_changetoobtainteacherapproval'] = 'After deactivating this setting uploaded files will not be visible to other participants automatically. You will have to determine which files are visible. Already visible files will become invisible.';

// index.php
$string['nopublicationsincourse'] = 'There are no publication instances in this course.';

// view.php
$string['allowsubmissionsfromdate_upload'] = 'Upload possibility from';
$string['allowsubmissionsfromdate_import'] = 'Approval from';
$string['duedate_upload'] = 'Upload possibility to';
$string['duedate_import'] = 'Approval to';
$string['cutoffdate_upload'] = 'Last upload posssibility to';
$string['cutoffdate_import'] = 'Last approval to';
$string['assignment_notfound'] = 'The assignment used to import from, couldnt be found.';
$string['updatefiles'] = 'Update files';
$string['updatefileswarning'] = 'Files from an individual student in the student folder will be updated with his/her submission of the assignment. Already visible files from students will be replaced too, if they are deleted or refreshed - the settings of the student as to the visibility will not be changed.';
$string['myfiles'] = 'Own files';
$string['edit_uploads'] = 'Edit files';
$string['edit_timeover'] = 'Files can only be edited during the changeperiod.';
$string['approval_timeover'] = 'You can only change your approval during the changeperiod.';
$string['nofiles'] = 'No files available';
$string['notice'] = 'Notice:';
$string['notice_requireapproval'] = 'Entries require approving by a teacher before they are viewable by everyone.';
$string['notice_noapproval'] = 'Files are immediately visible for everyone. The teacher has the right to overrule your setting.';
$string['teacher_pending'] = 'confirmation pending';
$string['teacher_approved'] = 'approved';
$string['teacher_rejected'] = 'rejected';
$string['teacher_blocked'] = 'rejected';
$string['student_approve'] = 'approve';
$string['student_approved'] = 'approved';
$string['student_reject'] = 'reject';
$string['student_rejected'] = 'rejected';

$string['allfiles'] = 'All files';
$string['publicfiles'] = 'Public files';
$string['downloadall'] = 'Download all files as ZIP';
$string['optionalsettings'] = 'Options';
$string['entiresperpage'] = 'Participants shown per page';
$string['nothingtodisplay'] = 'No entries to display';
$string['nofilestozip'] = 'No files to zip';

$string['go'] = 'Go';
$string['withselected'] = 'With selected...';
$string['zipusers'] = "Download as ZIP";
$string['approveusers'] = "für alle sichtbar";
$string['rejectusers'] = "für alle unsichtbar";

// upload.php
$string['guideline'] = 'Guideline:';
$string['published_emediately'] = 'visible for everybody: yes emediately, without approval by a teacher';
$string['published_aftercheck'] = 'visible for everybody: no, only after approval by a teacher';
$string['save_changes'] = 'Save changes';