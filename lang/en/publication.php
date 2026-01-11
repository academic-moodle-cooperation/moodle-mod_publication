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
$string['add_uploads'] = 'Add files';
$string['allfiles'] = 'File submissions';
$string['allowedfiletypes'] = 'Accepted file types';
$string['allowedfiletypes_err'] = 'Check input! Invalid file extensions or seperators';
$string['allowedfiletypes_help'] = 'Accepted file types can be restricted by entering a comma-separated list of mimetypes, e.g. \'video/mp4, audio/mp3, image/png, image/jpeg\', or file extensions including a dot, e.g. \'.png, .jpg\'. If the field is left empty, then all file types are allowed.';
$string['allowsubmissionsanddescriptionfromdatesummary'] = 'The assignment details and submission form will be available from <strong>{$a}</strong>';
$string['allowsubmissionsfromdate'] = 'Allow submissions from';
$string['allowsubmissionsfromdate_help'] = 'If this option is enabled, participants cannot submit their file submissions before this date. If the option is disabled, participants can start submitting right away.';
$string['allowsubmissionsfromdatesummary'] = 'This assignment will accept submissions from <strong>{$a}</strong>';
$string['alwaysshowdescription'] = 'Always show description';
$string['alwaysshowdescription_help'] = 'If disabled, the assignment description above will only become visible to students at the "Upload/Approval from" date.';
$string['approval_required'] = 'Decision pending';
$string['approval_timeover'] = 'You can change your consent only during the editing period.';
$string['approvalchange'] = 'Publication status changed';
$string['approvalfromdate'] = 'Approval from';
$string['approvalfromdate_help'] = 'Approval status cannot be changed before this date. This setting is only relevant when Student or Group approval is not automatic.';
$string['approvalsettings'] = 'Publication settings';
$string['approvaltodate'] = 'Approval until';
$string['approvaltodate_help'] = 'Approval status cannot be changed after this date. This setting is only relevant when Student or Group approval is not automatic.';
$string['approvaltodatevalidation'] = 'Approval until date must be after the approval from date.';
$string['approved'] = 'Approved';
$string['approveusers'] = 'Give approval';
$string['assignment'] = 'Assignment';
$string['assignment_help'] = 'Choose the assignment to import files from individual or group submissions.';
$string['assignment_notfound'] = 'The assignment from which files were imported, could no longer be found.';
$string['assignment_notset'] = 'No assignment has been chosen.';
$string['availability'] = 'Editing period (upload or approval)';
$string['availabilityrestriction'] = 'Apply availability restrictions to user list';
$string['availabilityrestriction_admin'] = 'Default setting for availability restrictions to user list';
$string['availabilityrestriction_admin_desc'] = 'Users who cannot access the activity due to availability restrictions will be removed from the list.<br>This only includes conditions which are marked as being applied to user lists. For example, group conditions are included but date conditions are not included.';
$string['availabilityrestriction_help'] = 'Users who cannot access the activity due to availability restrictions will be removed from the list.<br>This only includes conditions which are marked as being applied to user lists. For example, group conditions are included but date conditions are not included.';
$string['choose'] = 'Please choose ...';
$string['completionassignsubmission'] = 'Student or group must make a submission in the linked assignment activity';
$string['completiondetail:assignsubmission'] = 'Make a submission in the linked assignment activity';
$string['completiondetail:upload'] = 'Upload a file';
$string['completionupload'] = 'Student must upload a file';
$string['configautoimport'] = 'If you prefer to have student submissions be automatically imported into student folder instances. This feature can be enabled/disabled for each student folder instance separately.';
$string['configmaxbytes'] = 'Default maximum size for all files in the student folder.';
$string['configmaxfiles'] = 'Default maximum number of attachments allowed per user.';
$string['courseuploadlimit'] = 'Course upload limit';
$string['currentlynotapproved'] = '* Currently not approved or rejected to publication.';
$string['cutoffdate'] = 'Cut-off date';
$string['cutoffdate_help'] = 'If set, the assignment will not accept submissions after this date without an extension.';
$string['cutoffdate_import'] = 'Last approval to';
$string['cutoffdate_upload'] = 'Last upload to';
$string['cutoffdatefromdatevalidation'] = 'Cut-off date must be after the allow submissions from date.';
$string['cutoffdatevalidation'] = 'The cut-off date cannot be earlier than the due date.';
$string['datefrom'] = 'From';
$string['datefrom_import'] = 'Approval from';
$string['datefrom_upload'] = 'Upload from';
$string['dateto'] = 'To';
$string['dateto_import'] = 'Approval to';
$string['dateto_upload'] = 'Upload to';
$string['details'] = 'Details';
$string['downloadall'] = 'Download all file submissions';
$string['duedate'] = 'Due date';
$string['duedate_help'] = 'If this option is enabled, participants cannot submit their file submissions after this date. If the option is disabled, participants can submit forever.';
$string['duedatevalidation'] = 'Due date must be after the allow submissions from date.';
$string['edit_timeover'] = 'Files can be edited only  during the editing period.';
$string['edit_uploads'] = 'Edit/upload files';
$string['email:filechange:footer'] = '</ul><br />Please check if your permission for publication is required.';
$string['email:filechange_import:header'] = 'The following file(s) from Assignment <b>\'{$a->assign}\'</b> was/were imported into <b>\'{$a->publication}\'</b> on {$a->dayupdated} at {$a->timeupdated}:<br /><ul>';
$string['email:filechange_import:subject'] = 'File(s) imported';
$string['email:filechange_upload:header'] = '<b>{$a->username}</b> has uploaded the following file(s) to <b>\'{$a->publication}\'</b> on {$a->dayupdated} at {$a->timeupdated}:<br /><ul>';
$string['email:filechange_upload:subject'] = 'File(s) uploaded';
$string['email:statuschange:filename'] = '<li>\'{$a->filename}\' to \'<b>{$a->apstatus}</b>\'</li>';
$string['email:statuschange:footer'] = '</ul>';
$string['email:statuschange:header'] = 'The publication status of the following file(s) for <b>\'{$a->publication}\'</b> was changed on {$a->dayupdated} at {$a->timeupdated} by <b>{$a->username}</b>:<br /><ul>';
$string['email:statuschange:subject'] = 'Publication status changed';
$string['entiresperpage'] = 'Participants shown per page';
$string['eventoverridecreated'] = 'Publication override created';
$string['eventoverridedeleted'] = 'Publication override deleted';
$string['eventoverrideupdated'] = 'Publication override updated';
$string['eventpublicationapprovalchanged'] = 'Publication file approval changed';
$string['eventpublicationduedateextended'] = 'Publication due-date extended';
$string['eventpublicationfiledeleted'] = 'Publication file delete';
$string['eventpublicationfileimported'] = 'Publication file import';
$string['eventpublicationfileuploaded'] = 'Publication file upload';
$string['exception_file'] = 'You are not allowed to see this file';
$string['extensionduedate'] = 'Extension due date';
$string['extensionnotafterduedate'] = 'Extension date must be after the due date';
$string['extensionnotafterfromdate'] = 'Extension date must be after the allow submissions from date';
$string['extensionto'] = 'Extension to';
$string['filedetails'] = 'Details';
$string['filesofthesetypes'] = 'Files of these types may be added:';
$string['filter'] = 'Filter';
$string['filter:allfiles'] = 'All file submissions';
$string['filter:approvalrequired'] = 'Decision pending';
$string['filter:approved'] = 'Approved file submissions';
$string['filter:nofiles'] = 'No file submission';
$string['filter:nofilter'] = 'No filter';
$string['filter:rejected'] = 'Rejected file submissions';
$string['giveapproval'] = 'Give approval';
$string['go'] = 'Go';
$string['grantextension'] = 'Grant extension';
$string['group_approved'] = 'Approved by all members of the group.';
$string['guideline'] = 'Publication of file submissions';
$string['hidden'] = 'Not published';
$string['importfrom_err'] = 'You have to choose an assignment you want to import file submissions from.';
$string['maxbytes'] = 'Maximum attachment size';
$string['maxbytes_help'] = 'Files uploaded by students may be up to this size.';
$string['maxfiles'] = 'Maximum number of attachments';
$string['maxfiles_help'] = 'Each student will be able to upload up to this number of files for their submission.';
$string['messageprovider:publication_updates'] = 'Publication notifications';
$string['mode'] = 'Mode';
$string['mode_help'] = 'Choose whether students can upload documents here or their submissions of an assignment shall be imported.';
$string['modeimport'] = 'Import files from an assignment activity';
$string['modeupload'] = 'Upload files directly in the current activity';
$string['modulename'] = 'Student folder';
$string['modulename_help'] = '###### Key features
- Allows students to upload files or import them from an assignment activity
- Supports automatic or approval-based publishing of files
- Sends notifications to students and teachers about uploads, changes, and publication status
- Provides flexible settings for file limits, size, types, and submission periods
- Enables bulk download of all published files as a ZIP archive

###### Ways to use it
- Share student-created resources with the entire class
- Collect and publish group work or project files collaboratively
- Organize peer contributions for review or discussion
- Manage controlled publication workflows with student and teacher approvals
- Create a central repository for course-related student submissions';
$string['modulename_summary'] = 'Allows students to upload or import files and share them with peers in a central course space through controlled publishing options.';
$string['modulename_tip'] = '<a href="https://academic-moodle-cooperation.org/anleitungen/studierendenordner-einsetzen/?lng=en" target="_blank">Student Folder: Use the student folder</a>';
$string['modulenameplural'] = 'Student folders';
$string['myfiles'] = 'Own files';
$string['mygroupfiles'] = 'My group\'s files';
$string['name'] = 'Name';
$string['noentries'] = 'No entries';
$string['nofiles'] = 'No files available';
$string['nofilestodisplay'] = 'Currently there are no files available or not yet published.';
$string['nofilestozip'] = 'No files to zip';
$string['nonexistentfiletypes'] = 'The following file types were not recognised: {$a}';
$string['nopublicationsincourse'] = 'There is no student folder instance in this course.';
$string['nothing_to_show_groups'] = 'Nothing to display - no groups available';
$string['nothing_to_show_users'] = 'Nothing to display - no students available';
$string['nothingtodisplay'] = 'No entries to display';
$string['notice'] = '<strong>Notice: </strong>';
$string['notice_changes_possible_in_original'] = 'Changes to existing files are only possible in the original assignment activity.';
$string['notice_files_imported'] = 'Shown files are imported from an assignment activity.';
$string['notice_files_imported_group'] = 'Shown files are from a group submission, imported from an assignment activity.';
$string['notice_group_all_teachernotrequired'] = 'The files will only be published for all students with the approval of <strong>ALL group members.</strong>';
$string['notice_group_all_teacherrequired'] = 'The files will only be published for all students with the approval of <strong>ALL group members and the teacher.</strong> Teachers reserve the right to reject the publication of your files at any time.';
$string['notice_group_one_teachernotrequired'] = 'The files will only be published for all students with the approval of <strong>at LEAST ONE group member.</strong>';
$string['notice_group_one_teacherrequired'] = 'The files will only be published for all students with the approval of <strong>at LEAST ONE group member and the teacher.</strong> Teachers reserve the right to reject the publication of your files at any time.';
$string['notice_import_studentnotrequired_teachernotrequired'] = 'The files will be published (will be made visible to everyone) <strong>automatically.</strong>';
$string['notice_import_studentnotrequired_teacherrequired'] = 'The files will be published (will be made visible for everyone) only <strong>after the approval of teachers.</strong> Teachers reserve the right to reject the publication of your files at any time.';
$string['notice_import_studentrequired_teachernotrequired'] = 'The files will be published (will be made visible for everyone) after <strong>your approval.</strong>';
$string['notice_import_studentrequired_teacherrequired'] = 'The files will be published (will be made visible for everyone) after <strong>your approval and the approval of teachers.</strong> Teachers reserve the right to reject the publication of your files at any time.';
$string['notice_obtainapproval_import_both'] = 'As a teacher, you can reject approval for publication at any time, if a file not meets the defined requirements.';
$string['notice_obtainapproval_import_studentonly'] = 'In the spirit of copyright law, we ask that you request approval to publish files from students in a separate way.<br>As a teacher, you can reject approval for publication at any time, if a file not meets the defined requirements.';
$string['notice_obtainapproval_upload_automatic'] = 'In the spirit of copyright law, we ask that you request approval to publish files from students in a separate way.<br>As a teacher, you can reject approval for publication at any time, if a file does not meet the defined requirements.';
$string['notice_obtainapproval_upload_teacher'] = 'In the spirit of copyright law, we ask that you request approval to publish files from students in a separate way.<br>As a teacher, you can reject approval for publication at any time, if a file does not meet the defined requirements.';
$string['notice_obtainteacherapproval_studentsapproval'] = 'In the spirit of copyright law, we ask that you request approval to publish files from participants in a separate way.';
$string['notice_upload_studentnotrequired_teachernotrequired'] = 'All files you upload here will be published (will be made visible to everyone) <strong>automatically.</strong>';
$string['notice_upload_studentnotrequired_teacherrequired'] = 'All files you upload here will be published (will be made visible for everyone) only <strong>after the approval of teachers.</strong> Teachers reserve the right to reject the publication of your files at any time.';
$string['notice_upload_studentrequired_teachernotrequired'] = 'All files you upload here will be published (will be made visible for everyone) <strong>after your approval.</strong>';
$string['notice_upload_studentrequired_teacherrequired'] = 'All files you upload here will be published (will be made visible for everyone) <strong>after your approval and the approval of teachers.</strong> Teachers reserve the right to reject the publication of your files at any time.';
$string['notifications'] = 'Notifications';
$string['notify:filechange'] = 'Notifications about submitted or imported files';
$string['notify:filechange_admin'] = 'Default notification setting for file changes - submitted or imported';
$string['notify:filechange_help'] = 'Depends on the setting, if enabled, students and/or teachers will receive a notification when students upload or change a file, or when a file is imported or updated from an assignment activity.';
$string['notify:setting:0'] = 'No notifications';
$string['notify:setting:1'] = 'Teachers only';
$string['notify:setting:2'] = 'Students only';
$string['notify:setting:3'] = 'Both teachers and students';
$string['notify:statuschange'] = 'Notifications about publication status changes';
$string['notify:statuschange_admin'] = 'Default notification setting for publication status changes';
$string['notify:statuschange_help'] = ' Depends on the setting, if enabled, students and/or teachers will receive a notification when the publication status of one of the files is changed.';
$string['notifystudents'] = 'Notify students about publication changes';
$string['notifystudents_help'] = 'If enabled, students will receive a notification when the publication status of one of their uploaded files changes.';
$string['notifyteacher'] = 'Notify teachers about submitted files';
$string['notifyteacher_help'] = 'If enabled, teachers will receive a notification when students upload a file.';
$string['obtainapproval_automatic'] = 'Automatic';
$string['obtainapproval_required'] = 'Required';
$string['obtaingroupapproval'] = 'Approval by group';
$string['obtaingroupapproval_admin'] = 'Default group approval setting';
$string['obtaingroupapproval_admin_desc'] = 'This setting determines the default approval setting for group members. This setting becomes relevant <strong>only</strong> when the mode is set to "Import files from an assignment activity" and the assignment has group submission.';
$string['obtaingroupapproval_all'] = 'Required from ALL members';
$string['obtaingroupapproval_help'] = 'This option determines how the publication (visibility) of file submissions by groups takes place: <br /><ul><li><strong>Automatic</strong> - no approval from group members is required. In the spirit of copyright law, we ask that you request approval to publish files from students in a separate way.</li><li><strong>Required from at least ONE member</strong> - at least one group member needs to approve</li><li><strong>Required from ALL members</strong> - all group members need to approve</li></ul>';
$string['obtaingroupapproval_single'] = 'Required from at least ONE member';
$string['obtaingroupapproval_title'] = 'Group approval';
$string['obtainstudentapproval'] = 'Student approval';
$string['obtainstudentapproval_admin'] = 'Default student approval setting';
$string['obtainstudentapproval_admin_desc'] = 'This setting determines the default approval setting for students.';
$string['obtainstudentapproval_help'] = 'This option determines how the publication (visibility) of file submissions by student approval takes place: <br><ul><li><strong>Automatic</strong> - no approval from students is required. In the spirit of copyright law, we ask that you request approval to publish files from students in a separate way.</li><li><strong>Required</strong> - Students need to manually approve the file for publication</li></ul>';
$string['obtainstudentapproval_no'] = 'Automatic';
$string['obtainstudentapproval_yes'] = 'Required';
$string['obtainteacherapproval'] = 'Teacher approval';
$string['obtainteacherapproval_admin'] = 'Default teacher approval setting';
$string['obtainteacherapproval_admin_desc'] = 'This setting determines the default approval setting for teachers.';
$string['obtainteacherapproval_help'] = 'This option determines how the publication (visibility) of file submissions by teachers approval takes place: <br><ul><li><strong>Automatic</strong> - no approval from teachers is required.</li><li><strong>Required</strong> - Teachers need to manually approve the file for publication</li></ul>';
$string['obtainteacherapproval_no'] = 'Automatic';
$string['obtainteacherapproval_yes'] = 'Required';
$string['optionalsettings'] = 'Options';
$string['overdue'] = 'Deadline of editing period passed';
$string['override:add:group'] = 'Add group override';
$string['override:add:user'] = 'Add user override';
$string['override:approval:from'] = 'Approval from {$a->from}';
$string['override:approval:fromto'] = 'Approval from {$a->from} until {$a->to}';
$string['override:approval:to'] = 'Approval until {$a->to}';
$string['override:delete:ask'] = 'Are you sure you want to delete the override for {$a->userorgroup} {$a->fullname}?';
$string['override:delete:success'] = 'Override deleted successfully!';
$string['override:group:choose'] = 'Choose a group';
$string['override:invalidid'] = 'Invalid override ID';
$string['override:nothingtochange'] = 'There are no settings that can be overriden with the current activity settings!';
$string['override:save:success'] = 'Override saved successfully';
$string['override:submission:from'] = 'Allow submissions from {$a->from}';
$string['override:submission:fromto'] = 'Allow submissions from {$a->from} until {$a->to}';
$string['override:submission:to'] = 'Allow submissions until {$a->to}';
$string['override:user:choose'] = 'Choose a user';
$string['overrides:empty'] = 'No overrides';
$string['overview'] = 'Overview';
$string['pending'] = 'Pending';
$string['pluginadministration'] = 'Student folder administration';
$string['pluginname'] = 'Student folder';
$string['privacy:extensionduedate'] = 'Extension due date';
$string['privacy:metadata:approval'] = 'Whether the group member has approved or rejected for publication.';
$string['privacy:metadata:contenthash'] = 'SHA1 hash of the file\'s content, used to determine if the file changed.';
$string['privacy:metadata:extduedates'] = 'Stores information about overridden/extended due dates for mod_publication.';
$string['privacy:metadata:extensionduedate'] = 'The due date effective for students due to the override/extension.';
$string['privacy:metadata:fileid'] = 'Identifier of the file.';
$string['privacy:metadata:filename'] = 'The file\'s name.';
$string['privacy:metadata:files'] = 'Stores information (identifier, whom it belongs, where it came from, hash of content, file name and if approved by teacher and/or student) about the files uploaded/imported into mod_publication.';
$string['privacy:metadata:groupapproval'] = 'Stores information about approval or rejection of files by group members, imported from a group submission.';
$string['privacy:metadata:publicationfileexplanation'] = 'Files and converted onlinetext-submissions for this plugin get stored via Moodle\'s file API.';
$string['privacy:metadata:publicationperpage'] = 'How many entries should be displayed on a single table page!';
$string['privacy:metadata:studentapproval'] = 'Whether the student has approved or rejected the publication of a file.';
$string['privacy:metadata:teacherapproval'] = 'Whether the teacher has approved or rejected the publication of a file.';
$string['privacy:metadata:timecreated'] = 'The time and date the data record was created.';
$string['privacy:metadata:timemodified'] = 'The most recent time and date the data record got updated/modified.';
$string['privacy:metadata:type'] = 'Marks the origin of the file (uploaded by student, imported from assignment submission or converted onlinetext from assignment submission).';
$string['privacy:metadata:userid'] = 'Identifier of the user.';
$string['privacy:path:files'] = 'Files';
$string['privacy:path:resources'] = 'Resources';
$string['privacy:type:import'] = 'Imported file';
$string['privacy:type:onlinetext'] = 'Imported onlinetext';
$string['privacy:type:upload'] = 'Uploaded file';
$string['publication:addinstance'] = 'Add a new student folder';
$string['publication:approve'] = 'Decide if files should be published (visible for all participants)';
$string['publication:grantextension'] = 'Grant extension';
$string['publication:manageoverrides'] = 'Manage overrides';
$string['publication:receiveteachernotification'] = 'Receive notifications for teachers';
$string['publication:upload'] = 'Upload files to a student folder';
$string['publication:view'] = 'View student folder';
$string['publicationstatus'] = 'Publication';
$string['publicationstatus_help'] = 'The status of the publication represents the approval of the teacher and the final publication: <ul><li><i class="fa fa-check text-success fa-fw"></i> File is published and therefore visible for all participants</li><li><i class="fa fa-times text-danger fa-fw"></i> File is not published (approval has not yet been given or has been rejected) and therefore not visible</li></ul>';
$string['publicfiles'] = 'Published files';
$string['published_aftercheck'] = 'Approval from teachers required';
$string['published_immediately'] = 'Approve automatically';
$string['rejected'] = 'Rejected';
$string['rejectusers'] = 'Reject';
$string['reset'] = 'Revert';
$string['reset_userdata'] = 'All data';
$string['resetstudentapproval'] = 'Revert student approval';
$string['save_changes'] = 'Save changes';
$string['saveapproval'] = 'Save changes';
$string['savestudentapprovalwarning'] = 'Are you sure you want to save these changes? The publication status cannot be changed once it is set.';
$string['saveteacherapproval'] = 'Save changes';
$string['search:activity'] = 'Student folder - activity information';
$string['show_details'] = 'Show details';
$string['status'] = 'Status';
$string['status:approved'] = 'Approved';
$string['status:approvednot'] = 'Rejected';
$string['status:approvedrevoke'] = 'Revoked';
$string['student_approve'] = 'Approve';
$string['student_approved'] = 'Approved by student.';
$string['student_approved_automatically'] = 'Approved by student automatically.';
$string['student_pending'] = 'Decision from student is pending.';
$string['student_reject'] = 'Reject';
$string['student_rejected'] = 'Rejected from student.';
$string['studentapproval'] = 'Approval (students)';
$string['studentapproval_help'] = 'In the column "Approval (students)" the feedback of the students is displayed:<br><ul><li><i class="fa fa-question fa-fw text-warning"></i> - Decision pending</li><li><i class="fa fa-check text-success fa-fw"></i> - Approval given</li><li><i class="fa fa-times text-danger fa-fw"></i> - Approval rejected</li></ul>';
$string['submissionsettings'] = 'Submission settings';
$string['teacher_approve'] = 'Approve';
$string['teacher_approved'] = 'Approved by teacher.';
$string['teacher_approved_automatically'] = 'Approved by teacher automatically.';
$string['teacher_pending'] = 'Decision from teacher is pending.';
$string['teacher_reject'] = 'Reject';
$string['teacher_rejected'] = 'Not published (rejected).';
$string['teacherapproval'] = 'Approval';
$string['teacherapproval_help'] = 'Current approval/rejection of files, i.e. whether they are visible to all participants: <br><ul><li><strong>Choose...</strong> - decision pending/no approval given or rejected, these files are not visible.</li><li><strong>Approve</strong> - approval granted, these files are published and therefore visible to all.</li><li><strong>Reject</strong> - no approval given, these files are not published and therefore not visible.</li></ul>';
$string['total'] = 'Total';
$string['updatefiles'] = 'Update files';
$string['updatefileswarning'] = 'Already imported files will be replaced or deleted if the original files in the assignment were refreshed or deleted. The student\'s settings like the approval for publishing remain as they are.';
$string['uploaded'] = 'Uploaded';
$string['uploadnotopen'] = 'File upload is closed!';
$string['visibility'] = 'Published';
$string['visible'] = 'Published';
$string['visibleforstudents'] = 'Published';
$string['visibleforstudents_no'] = 'This file is not published (not visible for students).';
$string['visibleforstudents_yes'] = 'This file is published (visible for students).';
$string['withselected'] = 'With selected...';
$string['zipusers'] = 'Download selected file submissions';
