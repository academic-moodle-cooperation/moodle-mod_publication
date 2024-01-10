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
$string['modulename_help'] = 'The student folder offers the following features:<br><ul><li>Students can upload files, which will be published automatically or after teachers reviewed and approved them for publishing.</li><li>An assignment can be chosen as the basis for a student folder. A teacher can decide which files of the assignment are published (visible to all students). Teacher can additionally let students decide for themselves if their files should be visible to all.</li></ul>';

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
$string['publication:approve'] = 'Decide if files should be published (visible for all participants)';
$string['publication:grantextension'] = 'Grant extension';
$string['publication:receiveteachernotification'] = 'Receive notifications for teachers';
$string['search:activity'] = 'Student folder - activity information';

$string['messageprovider:publication_updates'] = 'Publication notifications';

$string['notifications'] = 'Notifications';
$string['notifyteacher'] = 'Notify teachers about submitted files';
$string['notifystudents'] = 'Notify students about publication changes';
$string['notifyteacher_help'] = 'If enabled, teachers will receive a notification when students upload a file.';
$string['notifystudents_help'] = 'If enabled, students will receive a notification when the publication status of one of their uploaded files changes.';

$string['uploaded'] = 'Uploaded';
$string['approvalchange'] = 'Publication status changed';

$string['emailteachermail'] = '---------------------------------------------------------------------\n{$a->username} has uploaded \'{$a->filename}\'
for \'{$a->publication}\' on {$a->dayupdated} at {$a->timeupdated}.

It is available here:

    {$a->url}---------------------------------------------------------------------\n';

$string['emailteachermailhtml'] = '{$a->username} has uploaded \'{$a->filename}\'
for <i>\'{$a->publication}\' on {$a->dayupdated} at {$a->timeupdated}</i><br /><br />
It is <a href="{$a->url}">available on the web site</a>.';

$string['emailstudentsmail'] = '{$a->username} has changed the publication status of \'{$a->filename}\' for \'{$a->publication}\' to {$a->apstatus} on {$a->dayupdated} at {$a->timeupdated}. It is available here: {$a->url}';

$string['emailstudentsmailhtml'] = '{$a->username} has changed the publication status of \'{$a->filename}\' for <i>\'{$a->publication}\'</i> to <b>{$a->apstatus}</b> on {$a->dayupdated} at {$a->timeupdated}</i><br /><br /> It is <a href="{$a->url}">available on the web site</a>.';

$string['name'] = 'Student folder name';
$string['obtainstudentapproval'] = 'Publication of file submissions';
$string['saveapproval'] = 'Save changes';
$string['obtainteacherapproval'] = 'Publication of file submissions';
$string['obtainteacherapproval_help'] = 'Decide if files will be made visible immediately upon upload or not: <br><ul><li> Yes - all files will be visible to everyone right away</li><li> No - files will be published only after the teacher approved</li></ul>';
$string['obtainteacherapproval_yes'] = 'Approve automatically';
$string['obtainteacherapproval_no'] = 'Approval from teachers required';
$string['obtainstudentapproval_teacher'] = 'Approval from teachers required';
$string['obtainstudentapproval_participant'] = 'Approval from students and teachers required';
$string['obtainstudentapproval_yes'] = 'Approval from students and teachers required';
$string['maxfiles'] = 'Maximum number of attachments';
$string['configmaxfiles'] = 'Default maximum number of attachments allowed per user.';
$string['maxbytes'] = 'Maximum attachment size';
$string['configmaxbytes'] = 'Default maximum size for all files in the student folder.';

$string['reset_userdata'] = 'All data';

// Strings from the File  mod_form.
$string['configautoimport'] = 'If you prefer to have student submissions be automatically imported into student folder instances. This feature can be enabled/disabled for each student folder instance separately.';
$string['availability'] = 'Editing period (upload or approval)';

$string['allowsubmissionsfromdate'] = 'From';
$string['allowsubmissionsfromdate_help'] = 'If this option is enabled, participants cannot submit their file submissions before this date. If the option is disabled, participants can start submitting right away.';
$string['allowsubmissionsfromdateh'] = 'Editing period (upload or approval)';
$string['allowsubmissionsfromdateh_help'] = 'In the specified period of time, participants can upload files or give or reject approval for the publication/visibility of files (depending on the mode).';
$string['allowsubmissionsfromdatesummary'] = 'This assignment will accept submissions from <strong>{$a}</strong>';
$string['allowsubmissionsanddescriptionfromdatesummary'] = 'The assignment details and submission form will be available from <strong>{$a}</strong>';
$string['alwaysshowdescription'] = 'Always show description';
$string['alwaysshowdescription_help'] = 'If disabled, the assignment description above will only become visible to students at the "Upload/Approval from" date.';

$string['duedate'] = 'To';
$string['duedate_help'] = 'This is when the assignment is due. Submissions will still be allowed after this date but any assignments submitted after this date are marked as late. To prevent submissions after a certain date - set the assignment cut off date.';
$string['duedatevalidation'] = 'Due date must be after the allow submissions from date.';

$string['cutoffdate'] = 'Cut-off date';
$string['cutoffdate_help'] = 'If set, the assignment will not accept submissions after this date without an extension.';
$string['cutoffdatevalidation'] = 'The cut-off date cannot be earlier than the due date.';
$string['cutoffdatefromdatevalidation'] = 'Cut-off date must be after the allow submissions from date.';

$string['mode'] = 'Mode';
$string['mode_help'] = 'Choose whether students can upload documents here or their submissions of an assignment shall be imported.';
$string['modeupload'] = 'Upload files directly in the current activity';
$string['modeimport'] = 'Import files from an assignment activity';

$string['courseuploadlimit'] = 'Course upload limit';
$string['allowedfiletypes'] = 'Accepted file types';
$string['allowedfiletypes_help'] = 'Accepted file types can be restricted by entering a comma-separated list of mimetypes, e.g. \'video/mp4, audio/mp3, image/png, image/jpeg\', or file extensions including a dot, e.g. \'.png, .jpg\'. If the field is left empty, then all file types are allowed.';
$string['allowedfiletypes_err'] = 'Check input! Invalid file extensions or seperators';

$string['currentlynotapproved'] = '* Currently not approved or rejected to publication.';

$string['teacherapproval_help'] = 'Current approval/rejection of files, i.e. whether they are visible to all participants: <br><ul><li><strong>Choose...</strong> - decision pending/no approval given or rejected, these files are not visible.</li><li><strong>Approve</strong> - approval granted, these files are published and therefore visible to all.</li><li><strong>Reject</strong> - no approval given, these files are not published and therefore not visible.</li></ul>';
$string['assignment'] = 'Assignment';
$string['assignment_help'] = 'Choose the assignment to import files from individual or group submissions.';
$string['obtainstudentapproval_help'] = 'This option determines how the publication (visibility) of file submissions takes place: <br><ul><li><strong>Approval from teachers required</strong> - File submissions will be reviewed and published by teachers</li><li><strong>Approval from students and teachers required</strong> - Students and teachers must agree to the publication of entries, otherwise they will not be visible</li></ul>';
$string['choose'] = 'Please choose ...';
$string['importfrom_err'] = 'You have to choose an assignment you want to import file submissions from.';
$string['nonexistentfiletypes'] = 'The following file types were not recognised: {$a}';

$string['groupapprovalmode'] = 'Approval by group';
$string['groupapprovalmode_help'] = 'Here you decide if the approvals of all group members or just the approval of at least one group member is required for files to be published.';
$string['groupapprovalmode_all'] = 'required from ALL members';
$string['groupapprovalmode_single'] = 'required from at least ONE member';
$string['groupapprovalmode_title'] = 'Group approval';

$string['warning_changefromobtainteacherapproval'] = 'After activating this setting, all uploaded files will be visible to other participants. All uploaded will become visible. You can manually make files invisible to certain students.';
$string['warning_changetoobtainteacherapproval'] = 'After deactivating this setting uploaded files will not be visible to other participants automatically. You will have to determine which files are visible. Already visible files will become invisible.';

$string['warning_changefromobtainstudentapproval'] = 'If you perform this change, only you can decide which files are visible to all students. The students are not asked for their approval. All files marked as approved will become visible to all students independent of the students\' decisions.';
$string['warning_changetoobtainstudentapproval'] = 'If you perform this change, the students are asked for their approval for all files marked as visible. Files will only become visible after the students\' approval.';

$string['completionupload'] = 'Student must upload a file';
$string['completiondetail:upload'] = 'Upload a file';

// Strings from the File  mod_publication_grantextension_form.php.
$string['extensionduedate'] = 'Extension due date';
$string['extensionnotafterduedate'] = 'Extension date must be after the due date';
$string['extensionnotafterfromdate'] = 'Extension date must be after the allow submissions from date';

// Strings from the File  index.php.
$string['nopublicationsincourse'] = 'There is no student folder instance in this course.';

// Strings from the File  view.php.
$string['allowsubmissionsfromdate_upload'] = 'Upload from';
$string['allowsubmissionsfromdate_import'] = 'Approval from';
$string['duedate_upload'] = 'Upload to';
$string['duedate_import'] = 'Approval to';
$string['cutoffdate_upload'] = 'Last upload to';
$string['cutoffdate_import'] = 'Last approval to';
$string['extensionto'] = 'Extension to';
$string['filedetails'] = 'Details';
$string['assignment_notfound'] = 'The assignment from which files were imported, could no longer be found.';
$string['assignment_notset'] = 'No assignment has been chosen.';
$string['updatefiles'] = 'Update files';
$string['updatefileswarning'] = 'Already imported files will be replaced or deleted if the original files in the assignment were refreshed or deleted. The student\'s settings like the approval for publishing remain as they are.';
$string['myfiles'] = 'Own files';
$string['mygroupfiles'] = 'My group\'s files';
$string['add_uploads'] = 'Add files';
$string['edit_uploads'] = 'Edit/upload files';
$string['edit_timeover'] = 'Files can be edited only  during the editing period.';
$string['approval_timeover'] = 'You can change your consent only during the editing period.';
$string['noentries'] = 'No entries';
$string['nofiles'] = 'No files available';
$string['nothing_to_show_users'] = 'Nothing to display - no students available';
$string['nothing_to_show_groups'] = 'Nothing to display - no groups available';
$string['notice'] = '<strong>Notice: </strong>';
$string['notice_uploadrequireapproval'] = 'All files you upload here will be published (will be made visible to everyone) only after the approval of teachers. Teachers reserve the right to reject the publication of your files at any time.';
$string['notice_uploadnoapproval'] = 'All files you upload here will be published immediately (will be made visible to everyone). Teachers reserve the right to reject the publication of your files.';
$string['notice_groupimportrequireallapproval'] = 'Shown files are from a group submission, imported from an assignment activity. The files will only be published for all students with the approval of ALL group members and teachers. Please clarify the publication within the group before.<br>
Changes to existing files are only possible in the origin assignment activity.';
$string['notice_groupimportrequireoneapproval'] = 'Shown files are from a group submission, imported from an assignment activity. The files will only be published for all students with the approval of at least ONE group member and teachers. Please clarify the publication within the group before.<br>
Changes to existing files are only possible in the origin assignment activity.';
$string['notice_importrequireapproval'] = 'All files will be published only after approval from you and teachers.<br>Changes to existing files are possible only in the origin assignment activity.';
$string['notice_importnoapproval'] = 'All files you upload here will be published only after the approval of teachers.';
// approval lang strings
$string['notice_obtainteacherapproval_studentsapproval'] = 'In the spirit of copyright law, we ask that you request approval to publish files from participants in a separate way.';

$string['notice_obtainapproval_import_both'] = 'As a teacher, you can reject approval for publication at any time, if a file not meets the defined requirements.';
$string['notice_obtainapproval_import_studentonly'] = 'In the spirit of copyright law, we ask that you request approval to publish files from students in a separate way.<br>
As a teacher, you can reject approval for publication at any time, if a file not meets the defined requirements.';
$string['notice_obtainapproval_upload_teacher'] = 'In the spirit of copyright law, we ask that you request approval to publish files from students in a separate way.<br>
As a teacher, you can reject approval for publication at any time, if a file not meets the defined requirements.';
$string['notice_obtainapproval_upload_automatic'] = 'In the spirit of copyright law, we ask that you request approval to publish files from students in a separate way.<br>
As a teacher, you can reject approval for publication at any time, if a file not meets the defined requirements.';

$string['teacher_pending'] = 'Decision pending';
$string['teacher_approved'] = 'Published';
$string['teacher_rejected'] = 'Not published (rejected)';
$string['teacher_approve'] = 'Approve';
$string['teacher_reject'] = 'Reject';
$string['approved'] = 'Approved';
$string['show_details'] = 'Show details';
$string['student_approve'] = 'Approve';
$string['student_approved'] = 'Approved';
$string['student_pending'] = 'Decision pending';
$string['pending'] = 'Pending';
$string['student_reject'] = 'Reject';
$string['student_rejected'] = 'Rejected';
$string['rejected'] = 'Rejected';
$string['visible'] = 'Published';
$string['hidden'] = 'Not published';
$string['status:approved'] = 'Approved';
$string['status:approvednot'] = 'Rejected';
$string['status:approvedrevoke'] = 'Revoked';
$string['giveapproval'] = 'Give approval';
$string['overdue'] = 'Deadline of editing period passed';
$string['approval_required'] = 'Decision pending';
$string['publicationstatus'] = 'Publication';
$string['publicationstatus_help'] = 'The status of the publication represents the approval of the teacher and the final publication: <ul><li><i class="fa fa-check text-success fa-fw"></i> File is published and therefore visible for all participants</li><li><i class="fa fa-times text-danger fa-fw"></i> File is not published (approval has not yet been given or has been rejected) and therefore not visible</li></ul>';

$string['allfiles'] = 'File submissions';
$string['publicfiles'] = 'Published files';
$string['downloadall'] = 'Download all file submissions';
$string['optionalsettings'] = 'Options';
$string['entiresperpage'] = 'Participants shown per page';
$string['nothingtodisplay'] = 'No entries to display';
$string['nofilestodisplay'] = 'Currently there are no files available or not yet published.';
$string['nofilestozip'] = 'No files to zip';
$string['status'] = 'Status';
$string['studentapproval'] = 'Approval (students)';
$string['studentapproval_help'] = 'In the column "Approval (students)" the feedback of the students is displayed:<br><ul><li><i class="fa fa-question fa-fw text-warning"></i> - Decision pending</li><li><i class="fa fa-check text-success fa-fw"></i> - Approval given</li><li><i class="fa fa-times text-danger fa-fw"></i> - Approval rejected</li></ul>';
$string['teacherapproval'] = 'Approval';
$string['visibility'] = 'Published';
$string['visibleforstudents'] = 'Published';
$string['visibleforstudents_yes'] = 'This file is published (visible for students).';
$string['visibleforstudents_no'] = 'This file is not published (not visible for students).';
$string['resetstudentapproval'] = 'Revert approval';
$string['savestudentapprovalwarning'] = 'Are you sure you want to save these changes? The publication status cannot be changed once it is set.';

$string['go'] = 'Go';
$string['withselected'] = 'With selected...';
$string['zipusers'] = 'Download selected file submissions';
$string['approveusers'] = 'Give approval';
$string['rejectusers'] = 'Reject';
$string['grantextension'] = 'Grant extension';
$string['saveteacherapproval'] = 'Save changes';
$string['reset'] = 'Revert';

// Strings from the File upload.php.
$string['filesofthesetypes'] = 'Files of these types may be added:';
$string['guideline'] = 'Publication of file submissions';
$string['published_immediately'] = 'Approve automatically';
$string['published_aftercheck'] = 'Approval from teachers required';
$string['save_changes'] = 'Save changes';

$string['overview'] = 'Overview';

// Strings for JS...
$string['total'] = 'Total';
$string['details'] = 'Details';

// Privacy strings...
$string['privacy:metadata:publicationperpage'] = 'How many entries should be displayed on a single table page!';
$string['privacy:path:files'] = 'Files';
$string['privacy:path:resources'] = 'Resources';
$string['privacy:type:upload'] = 'Uploaded file';
$string['privacy:type:import'] = 'Imported file';
$string['privacy:type:onlinetext'] = 'Imported onlinetext';
$string['privacy:metadata:groupapproval'] = 'Stores information about approval or rejection of files by group members, imported from a group submission.';
$string['privacy:metadata:publicationfileexplanation'] = 'Files and converted onlinetext-submissions for this plugin get stored via Moodle\'s file API.';
$string['privacy:metadata:extduedates'] = 'Stores information about overridden/extended due dates for mod_publication.';
$string['privacy:metadata:files'] = 'Stores information (identifier, whom it belongs, where it came from, hash of content, file name and if approved by teacher and/or student) about the files uploaded/imported into mod_publication.';
$string['privacy:metadata:fileid'] = 'Identifier of the file.';
$string['privacy:metadata:userid'] = 'Identifier of the user.';
$string['privacy:metadata:timecreated'] = 'The time and date the data record was created.';
$string['privacy:metadata:timemodified'] = 'The most recent time and date the data record got updated/modified.';
$string['privacy:metadata:approval'] = 'Whether the group member has approved or rejected for publication.';
$string['privacy:metadata:studentapproval'] = 'Whether the student has approved or rejected the publication of a file.';
$string['privacy:metadata:teacherapproval'] = 'Whether the teacher has approved or rejected the publication of a file.';
$string['privacy:metadata:type'] = 'Marks the origin of the file (uploaded by student, imported from assignment submission or converted onlinetext from assignment submission).';
$string['privacy:metadata:contenthash'] = 'SHA1 hash of the file\'s content, used to determine if the file changed.';
$string['privacy:metadata:filename'] = 'The file\'s name.';
$string['privacy:metadata:extensionduedate'] = 'The due date effective for students due to the override/extension.';

// filters
$string['filter'] = 'Filter';
$string['filter:nofilter'] = 'No filter';
$string['filter:allfiles'] = 'All file submissions';
$string['filter:approved'] = 'Approved file submissions';
$string['filter:rejected'] = 'Rejected file submissions';
$string['filter:approvalrequired'] = 'Decision pending';
$string['filter:nofiles'] = 'No file submission';



// Deprecated since Moodle 2.9!
$string['requiremodintro'] = 'Require activity description';
$string['configrequiremodintro'] = 'Disable this option if you do not want to force users to enter description of each activity.';
$string['configobtainstudentapproval'] = 'Files will only be published (made visible to all) after student\'s and teacher\'s approval.';
$string['configobtainteacherapproval'] = 'Documents of students are by default visible for all other participants.';


