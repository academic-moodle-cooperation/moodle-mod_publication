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
 * Strings for component 'publication', language 'en'
 *
 * @package       mod_publication
 * @author        Philipp Hager
 * @author        Andreas Windbichler
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['modulename'] = 'Student folder';
$string['pluginname'] = 'Student folder';
$string['modulename_help'] = 'The student folder offers the following features:

* Participants can upload documents that are available to other participants immediately or after you have checked the documents and given your consent.
* An assignment can be chosen as a basis for a student folder. The teacher can decide which documents of the assignment are visible for all participants. Teachers can also let the participants decide whether their documents should be visible to others.';

$string['eventpublicationfiledeleted'] = 'Publication file delete';
$string['eventpublicationfileuploaded'] = 'Publication file upload';
$string['eventpublicationfileimported'] = 'Publication file import';
$string['eventpublicationduedateextended'] = 'Publication due-date extended';
$string['eventpublicationapprovalchanged'] = 'Publication file approval changed';

$string['modulenameplural'] = 'Student folders';
$string['pluginadministration'] = 'Student folder administration';
$string['publication:addinstance'] = 'Add a new student folder';
$string['publication:view'] = 'View student folder';
$string['publication:upload'] = 'Upload files to a student folder';
$string['publication:approve'] = 'Decide if files should be visible for every student';
$string['publication:grantextension'] = 'Grant extension';
$string['publication:receiveteachernotification'] = 'Receive notifications for teachers';
$string['search:activity'] = 'Student folder - activity information';

$string['messageprovider:publication_updates'] = 'Publication notifications';

$string['notifications'] = 'Notifications';
$string['notifyteacher'] = 'Notify graders about uploads';
$string['notifystudents'] = 'Notify students about approval changes';
$string['notifyteacher_help'] = 'If enabled, graders (usually teachers) receive a message whenever a student uploads a file. Message methods are configurable.';
$string['notifystudents_help'] = 'If enabled, students recieve a message whenever the approval status of one of their uploads changes. Message methods are configurable.';

$string['uploaded'] = 'Uploaded';
$string['approvalchange'] = 'Approval status changed';

$string['emailteachermail'] = '---------------------------------------------------------------------\n{$a->username} has uploaded \'{$a->filename}\'
for \'{$a->publication}\' on {$a->dayupdated} at {$a->timeupdated}.

It is available here:

    {$a->url}---------------------------------------------------------------------\n';

$string['emailteachermailhtml'] = '{$a->username} has uploaded \'{$a->filename}\'
for <i>\'{$a->publication}\' on {$a->dayupdated} at {$a->timeupdated}</i><br /><br />
It is <a href="{$a->url}">available on the web site</a>.';

$string['emailstudentsmail'] = '{$a->username} has changed the approval status of \'{$a->filename}\'
for \'{$a->publication}\' to {$a->apstatus} on {$a->dayupdated} at {$a->timeupdated}.

It is available here:

    {$a->url}';

$string['emailstudentsmailhtml'] = '{$a->username} has changed the approval status of \'{$a->filename}\'
for <i>\'{$a->publication}\'</i> to <b>{$a->apstatus}</b> on {$a->dayupdated} at {$a->timeupdated}</i><br /><br />
It is <a href="{$a->url}">available on the web site</a>.';

$string['name'] = 'Student folder name';
$string['obtainstudentapproval'] = 'Obtain approval';
$string['saveapproval'] = 'save approval';
$string['configobtainstudentapproval'] = 'Documents are visible after the student´s consent.';
$string['obtainteacherapproval'] = 'Approved by default';
$string['configobtainteacherapproval'] = 'Documents of students are by default visible for all other participants.';
$string['maxfiles'] = 'Maximum number of attachments';
$string['configmaxfiles'] = 'Default maximum number of attachments allowed per user.';
$string['maxbytes'] = 'Maximum attachment size';
$string['configmaxbytes'] = 'Default maximum size for all files in the student folder.';

$string['reset_userdata'] = 'All data';

// Strings from the File  mod_form.
$string['autoimport'] = 'Sync automatically with Assignment';
$string['autoimport_help'] = 'If activated, new submissions will in corresponding Assigment will be imported automatically into student folder module. (Optional) Studentapproval has to be obtained again for the new files.';
$string['configautoimport'] = 'If you prefer to have student submissions be automatically imported into student folder instances. This feature can be enabled/disabled for each student folder instance separately.';
$string['availability'] = 'Timeslot for Upload/Approval';

$string['allowsubmissionsfromdate'] = 'from';
$string['allowsubmissionsfromdateh'] = 'Timeslot for Upload/Approval';
$string['allowsubmissionsfromdateh_help'] = 'You can determine the period of time during which students can upload files or give their approval for file publication. During this time period students can edit their files and can also withdraw their approval for publication.';
$string['allowsubmissionsfromdatesummary'] = 'This assignment will accept submissions from <strong>{$a}</strong>';
$string['allowsubmissionsanddescriptionfromdatesummary'] = 'The assignment details and submission form will be available from <strong>{$a}</strong>';
$string['alwaysshowdescription'] = 'Always show description';
$string['alwaysshowdescription_help'] = 'If disabled, the Assignment Description above will only become visible to students at the "Allow submissions from" date.';

$string['duedate'] = 'to';
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
$string['allowedfiletypes'] = 'Accepted file types';
$string['allowedfiletypes_help'] = 'Accepted file types can be restricted by entering a comma-separated list of mimetypes, e.g. \'video/mp4, audio/mp3, image/png, image/jpeg\', or file extensions including a dot, e.g. \'.png, .jpg\'. If the field is left empty, then all file types are allowed.';
$string['allowedfiletypes_err'] = 'Check input! Invalid file extensions or seperators';
$string['obtainteacherapproval_help'] = 'Decide if files will be made visible immediately upon upload or not: <br><ul><li> yes - all files will be visible to everyone right away</li><li> no - files will be published only after the teacher approved</li></ul>';

$string['currentlynotapproved'] = '* Currently not approved or disapproved.';

$string['teacherapproval_help'] = 'Current decision on files, to be visible to all participants: <br /><ul><li>Choose... - not approved or disapproved yet, the files are not visible.</li><li>Yes - approved, the files are visible.</li><li>No - disapproved, the files are not visible.</li></ul>';
$string['assignment'] = 'Assignment';
$string['assignment_help'] = 'Choose the assignment to import files from. In the moment group-assignments are not supported and therefore not selectable.';
$string['obtainstudentapproval_help'] = 'Decide if students approval will be obtained: <br><ul><li> yes - files will be visible to all only after the student approved. The teacher may select individual students/files to ask for approval.</li><li> no - the student’s approval will not be obtained via Moodle. The file’s visibility is solely the teacher’s desicion.</li></ul>';
$string['choose'] = 'please choose ...';
$string['importfrom_err'] = 'You have to choose an assignment you want to import from.';
$string['nonexistentfiletypes'] = 'The following file types were not recognised: {$a}';

$string['groupapprovalmode'] = 'Groupapproval mode';
$string['groupapprovalmode_help'] = 'Here you decide if approval of all group members or just approval of at least one group member is required for files to be visible. The files will only be visible after approval by all group members or at least one group member.';
$string['groupapprovalmode_all'] = 'ALL members of the group have to approve';
$string['groupapprovalmode_single'] = 'at least ONE member has to approve';

$string['warning_changefromobtainteacherapproval'] = 'After activating this setting, all uploaded files will be visible to other participants. All uploaded will become visible. You can manually make files invisible to certain students.';
$string['warning_changetoobtainteacherapproval'] = 'After deactivating this setting uploaded files will not be visible to other participants automatically. You will have to determine which files are visible. Already visible files will become invisible.';

$string['warning_changefromobtainstudentapproval'] = 'If you perform this change, only you can decide which files are visible to all students. The students are not asked for their approval. All files marked as approved will become visible to all students independent of the students\' decisions.';
$string['warning_changetoobtainstudentapproval'] = 'If you perform this change, the students are asked for their approval for all files marked as visible. Files will only become visible after the students\' approval.';

// Strings from the File  mod_publication_grantextension_form.php.
$string['extensionduedate'] = 'Extension due date';
$string['extensionnotafterduedate'] = 'Extension date must be after the due date';
$string['extensionnotafterfromdate'] = 'Extension date must be after the allow submissions from date';

// Strings from the File  index.php.
$string['nopublicationsincourse'] = 'There are no student folder instances in this course.';

// Strings from the File  view.php.
$string['allowsubmissionsfromdate_upload'] = 'Upload possibility from';
$string['allowsubmissionsfromdate_import'] = 'Approval from';
$string['duedate_upload'] = 'Upload possibility to';
$string['duedate_import'] = 'Approval to';
$string['cutoffdate_upload'] = 'Last upload posssibility to';
$string['cutoffdate_import'] = 'Last approval to';
$string['extensionto'] = 'Extension to';
$string['filedetails'] = 'Details';
$string['assignment_notfound'] = 'The assignment from which files were imported, could no longer be found.';
$string['assignment_notset'] = 'No assignment has been chosen.';
$string['updatefiles'] = 'Update files';
$string['updatefileswarning'] = 'Files from an individual student in the student folder will be updated with his/her submission of the assignment. Already visible files from students will be replaced too, if they are deleted or refreshed - the settings of the student as to the visibility will not be changed.';
$string['myfiles'] = 'Own files';
$string['mygroupfiles'] = 'My group\'s files';
$string['add_uploads'] = 'Add files';
$string['edit_uploads'] = 'edit/upload files';
$string['edit_timeover'] = 'Files can only be edited during the changeperiod.';
$string['approval_timeover'] = 'You can only change your approval during the changeperiod.';
$string['noentries'] = 'No entries';
$string['nofiles'] = 'No files available';
$string['nothing_to_show_users'] = 'nothing to display - no students available';
$string['nothing_to_show_groups'] = 'nothing to display - no group available';
$string['notice'] = 'Notice:';
$string['notice_uploadrequireapproval'] = 'All uploaded files will be made visible only after the teacher’s review';
$string['notice_uploadnoapproval'] = 'All files will be immediately visible to everyone upon upload. The teacher reserves the right to hide published files at any time.';
$string['notice_groupimportrequireallapproval'] = 'Decide whether your files are available for everyone. All group members must give their approval before the file will be visible.';
$string['notice_groupimportrequireoneapproval'] = 'Decide whether your files are available for everyone. A single group member\'s approval is enough for the file to be visible. Please discuss group internally if your file should be visible or not before approving it!';
$string['notice_importrequireapproval'] = 'Decide whether your files are available for everyone.';
$string['notice_importnoapproval'] = 'The following files are visible to all.';
$string['teacher_pending'] = 'confirmation pending';
$string['teacher_approved'] = 'visible (approved)';
$string['teacher_rejected'] = 'declined';
$string['approved'] = 'Approved';
$string['show_details'] = 'Show details';
$string['student_approve'] = 'approve';
$string['student_approved'] = 'approved';
$string['student_pending'] = 'not visible (not approved)';
$string['pending'] = 'Pending';
$string['student_reject'] = 'reject';
$string['student_rejected'] = 'rejected';
$string['rejected'] = 'Rejected';
$string['visible'] = 'visible';
$string['hidden'] = 'hidden';
$string['status:approved'] = 'approved';
$string['status:approvednot'] = 'rejected';

$string['allfiles'] = 'All files';
$string['publicfiles'] = 'Public files';
$string['downloadall'] = 'Download all files as ZIP';
$string['optionalsettings'] = 'Options';
$string['entiresperpage'] = 'Participants shown per page';
$string['nothingtodisplay'] = 'No entries to display';
$string['nofilestozip'] = 'No files to zip';
$string['status'] = 'Status';
$string['studentapproval'] = 'Status'; // Previous 'Student approval'.
$string['studentapproval_help'] = 'The column status represents the students reply of the approval:

* ? - approval pending
* ✓ - approval given
* ✖ - approval declined';
$string['teacherapproval'] = 'Approval';
$string['visibility'] = 'visible for all';
$string['visibleforstudents'] = 'visible to all';
$string['visibleforstudents_yes'] = 'Stundets can see this file';
$string['visibleforstudents_no'] = 'This file is NOT visible to students';
$string['resetstudentapproval'] = 'Reset status'; // Previous 'Reset student approval'.
$string['savestudentapprovalwarning'] = 'Are you sure you want to save these changes? You can not change the status once is it set.';

$string['go'] = 'Go';
$string['withselected'] = 'With selected...';
$string['zipusers'] = "Download as ZIP";
$string['approveusers'] = "visible for all";
$string['rejectusers'] = "invisible for all";
$string['grantextension'] = 'grant extension';
$string['saveteacherapproval'] = 'save approval';
$string['reset'] = 'Revert';

// Strings from the File  upload.php.
$string['filesofthesetypes'] = 'Files of these types may be added:';
$string['guideline'] = 'visible for everybody:';
$string['published_immediately'] = 'yes immediately, without approval by a teacher';
$string['published_aftercheck'] = 'no, only after approval by a teacher';
$string['save_changes'] = 'Save changes';

// Strings for JS...
$string['total'] = 'total';
$string['details'] = 'Details';

// Privacy strings...
$string['privacy:metadata:publicationperpage'] = 'How many entries should be displayed on a single table page!';
$string['privacy:path:files'] = 'files';
$string['privacy:path:resources'] = 'resources';
$string['privacy:type:upload'] = 'uploaded file';
$string['privacy:type:import'] = 'imported file';
$string['privacy:type:onlinetext'] = 'imported onlinetext';
$string['privacy:metadata:groupapproval'] = 'Stores information about group members\' approval or rejection of files imported from group\'s submission.';
$string['privacy:metadata:publicationfileexplanation'] = 'Files and converted onlinetext-submissions for this plugin get stored via Moodle\'s file API.';
$string['privacy:metadata:extduedates'] = 'Stores information about overridden/extended due dates for mod_publication.';
$string['privacy:metadata:files'] = 'Stores information (identifier, whom it belongs, where it came from, hash of content, file name and if approved by teacher and/or student) about the files uploaded/imported into mod_publication.';
$string['privacy:metadata:fileid'] = 'Identifier of the file.';
$string['privacy:metadata:userid'] = 'Identifier of the user.';
$string['privacy:metadata:timecreated'] = 'The time and date the data record was created.';
$string['privacy:metadata:timemodified'] = 'The most recent time and date the data record got updated/modified.';
$string['privacy:metadata:approval'] = 'Whether or not the group member approved or rejected the file.';
$string['privacy:metadata:studentapproval'] = 'Whether or not the student approved or rejected the file.';
$string['privacy:metadata:teacherapproval'] = 'Whether or not the teacher approved or rejected the file.';
$string['privacy:metadata:type'] = 'Marks the origin of the file (uploaded by student, imported from assignment submission or converted onlinetext from assignment submission).';
$string['privacy:metadata:contenthash'] = 'SHA1 hash of the file\'s content, used to determine if the file changed.';
$string['privacy:metadata:filename'] = 'The file\'s name.';
$string['privacy:metadata:extensionduedate'] = 'The due date effective for the user due to it being overridden/extended.';

// Deprecated since Moodle 2.9!
$string['requiremodintro'] = 'Require activity description';
$string['configrequiremodintro'] = 'Disable this option if you do not want to force users to enter description of each activity.';
