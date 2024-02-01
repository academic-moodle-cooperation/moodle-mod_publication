CHANGELOG
=========

4.3.0 (2024-01-10)
-------------------
* Moodle 4.3.0 compatible version
* [BUG] #7813 - fix missing 'Default group'
* [BUG] #7804 - fix instance field name too short
* [BUG] #7808 - fix deprecated user name fields call
* [BUG] #7812 - remove unused plugin settings
* [BUG] #7810 - fix assign submission remove has no effect on stud. folder submission
* [BUG] #7791 - fix table filtering not per instance
* [BUG] #7827 - fix errors when setting up completion
* [BUG] #7805 - fix bug with sending notifications if status has not been changed
* [BUG] #7806 - fix bug with sending notifications when using the go button
* [BUG] #7807 - fix bug with changed status using the go button

4.2.2 (2023-12-05)
-------------------
* [BUG] #7727 - "Nothing to display" shown without initials bar even though filtering by initials - github pull request #75 - t-schroeder
* [BUG] #7472 - fix bug with sending notifications to teachers from course categories
* [BUG] #7707 - fix bug when "Download all file submissions" is used by teachers - not all files are downloaded

4.2.1 (2023-09-20)
-------------------
* [BUG] #7699 - update langstrings

4.2.0 (2023-08-10)
-------------------
* Moodle 4.2.0 compatible version
* [FEATURE] #7307 - add support for activity completion
* [BUG] #7352 - add locallib require - github pull request #70 - matthewhilton
* [UPDATE] #7357 - new navigation structure
* [HOTFIX] #7628 - force download plugin files, add missing require_sesskey() in view.php
* [BUG] #7653 - fix error when participants try to download files
* [BUG] #7654 - fix warning message of publication, shown in assignment if online text is empty

4.1.0 (2022-12-12)
-------------------
* Moodle 4.1.0 compatible version
* [BUG] #7405 - fix fatal error on student approval
* [BUG] #7408 - remove german word in approval notification

4.0.0 (2022-05-04)
-------------------
* Moodle 4.0.0 compatible version
* [FEATURE] Add idnumber support
* [HOTFIX] #7208 - fix sql query when filters applied
* [FEATURE] #7173 - update icon to monologo
* [BUG] #7212 - remove deprecated string from list of deprecated strings - github pull request #65 - hdagheda 

3.11.3 (2022-04-05)
-------------------
* [BUG] #7142 - fix typo in locallib.php
* [BUG] #7149 - fix error when manually updating files - github issue #59
* [FEATURE] #7143 - show availability dates on course page when showactivitydates is set to true
* [BUG] #7119 - fix pagination functionality for individual users and groups
* [FEATURE] #7145 - fix order of implode parameters (php 8) - github pull request #61 - Nick Phillips
* [FEATURE] #7176 - UX improvements for "teacher view" - github issue #44
* [BUG] #7204 - fix required assignment field when "Import" selected

3.11.2 (2021-12-22)
-------------------
* [FEATURE] #6931 - Add events in calendar - github pull request #48 from Henry Brink
* [BUG] #7091 - fix Ambiguous column name 'email' github issue #55 from mrkskwsnck

3.11.1 (2021-09-15)
-------------------
* [FEATURE] #6879 - make files that need approval more visible and add infotext
* [BUG] #6967 - fix wrong default value for db fields in table publication_groupapproval - github issue #52
* [BUG] #6975 - fix wrong referenced table in install.xml - github issue #53
* [FIXED] #6929 - fix language of notifications to match that of the receiver 

3.11.0 (2021-05-25)
-------------------
* Moodle 3.11.0 compatible version
* [FEATURE] #6834 - add one more file status for students - "pending approval"
* [FIXED] #6912 - fix buttons margins - github merge request #49 by Luca Bösch
* [FIXED] #6877 - add indexes for db table publication_file, fields publication and userid - github #47
* [FIXED] #6922 - remove warning that appears sometimes during listing of files of groups
* [FIXED] #6923 - fix bug when sending approval-changed notifications to groups
* [FIXED] #6924 - fix showing deleted assignments (pending deletion) in the dropdown
* [FIXED] #6921 - fix approval status in notification text/log entry

3.10.1 (2021-04-14)
-------------------
* [FIXED] #6838 - fix wrong-restored allowedfiletypes

3.10.0 (2020-11-11)
------------------
* Moodle 3.10.0 compatible version
* [FIXED] GitHub #38 - merge Pull request #39 by Christian Wolters - Omit manager in recieveteachernotification cap
* [FIXED] GitHub #41 - remove hardcoded "not approved"

3.9.0 (2020-06-15)
------------------
* Moodle 3.9.0 compatible version

3.8.1 (2020-02-18)
------------------

* [BUG] #6594 uploaded files are now approved automatically, if specified in the settings
* [BUG] #6380 pagination now fixed to predefined values (all/10/20/50/100)


3.8.0 (2020-01-05)
------------------

* [BUG] #6492 fixed bug which caused fatal error when opening reports / dates
* [FEATURE] #4641 table is now sortable by approval selection
* [FIXED] #6381 Fix typo in receiveteachernotification langstring


3.7.1 (2019-10-04)
------------------

* [FIXED] #6295 Fix typo in receiveteachernotification capability and add langstring for it
* [FIXED] #6333 Images are now shown in the description
* [FIXED] #6250 Fix typo in 'extended' [github pull #32 @germanvaleroelizondo]
* [FIXED] #6209 space added between save approval and revert button
* [FEATURE] #6208 approval select boxes are now left of column instead of center
* [FIXED] #6205 list of files and approval select boxes are now aligned


3.7.0 (2019-08-07)
------------------

* [BUG] #6207 publication layout bug after changing approval fixed
* [BUG] #6206 notifications with groups fixed
* [FEATURE] #6126 added support for report-editdates
* [FEATURE] #4995 notification on file-status change for student
* [FEATURE] #4730 notification on file-upload for teacher
* [FEATURE] #4731 extended logs


3.6.1 (2019-05-15)
------------------

* [FIXED] #6015 remove setting to show id
* [FIXED] #6035 auto-reload on groupfilter-change


3.6.0 (2019-02-01)
------------------

* Moodle 3.6 compatible version
* [FIXED] #5951 remove strong-tags in labels due to flexbox not displaying spaces around them
* [FIXED] #6014 fix help icon related to column status being displayed the column before
* [FEATURE] #5835 context locking now affects publication too (some capabilities were defined as reading instead of writing)!
* [FEATURE] #5758 add new core_userlist_provider methods to privacy provider
* [FEATURE] #6025 privacy API updates (#5758) are now covered by unit tests
* [UPDATE] #5638 update .travis.yml
* [UPDATE] #5952 update accepted file types help text and label
* [UPDATE] #6029 update README.md
* [CHANGED] #5638 strip leading slashes from namespaces and use statements
* [CHANGED] #6025 reorganized unit tests


3.5.0 (2018-07-18)
------------------

* Moodle 3.5 compatible version
* [CHANGED] #5134 removed german lang file from repository
* [FEATURE] #5386 implemented privacy API


3.4.0 (2017-12-13)
------------------

* Moodle 3.4 compatible version
* [CHANGED] #4849 use moodleform's hideIf-Method instead of custom JS
* [CHANGED] #4621 prepend user's respectively group's name to onlinetext-file-downloads
* [CHANGED] #4851 reformatted many lines of code (PHPDoc comments, coding style, properties, variables, etc.)
* [CHANGED] added a label to filemanager for uploading files due to future behat tests using it


3.3.2 (2017-11-16)
------------------

* [FIXED] #4914 error caused for users without capability "mod/publication:upload" by not instantiated files table


3.3.1 (2017-09-04)
------------------

* [FIXED] #4675 integrated fix for granting extension causing fatal errors (thanks @raad https://github.com/raad)
* [FIXED] warning in "grant extension"-form due to no due date being used
* [FIXED] #4686 fixed template JSON not validating and template HTML as well as template coding style
* [FIXED] fixed/added some PHPDoc comments
* [CHANGED] #4685 updated travis.yml to use moodle-plugin-ci version 2 and run behat tests in firefox and chrome


3.3.0 (2017-08-10)
------------------

* Moodle 3.3 compatible version
* [FEATURE] #3926 add preview for imported onlinetext-submissions
* [CHANGED] #4432 updated filetype restrictions to work like mod_assign's implementation
* [CHANGED] #3824 show group approval mode elements only when needed
* [CHANGED] #4432 update filetype restrictions to support either extensions or mime types (see mod_assign update)
* [CHANGED] #3905 improve message output if assign to import from has been deleted
* [CHANGED] #4276 fixed upper/lower-case mix of 'student folder' in english language file
* [FIXED] #4647 missing param for group table if no group's in course
* [FIXED] added missing SVG file for questionmark icon
* [FIXED] exception due to no total files in table
* several other small fixes and improvements


3.2.2 (2016-04-19)
------------------

* [FIXED] #4409 fixed file permission check for one's own files in teamsubmissions for standard group
* [CHANGED] #4276 fix inconsistent plugin naming in english lang strings
* [CHANGED] added local PHPUnit config file


3.2.1 (2016-03-23)
------------------

* [FIXED] #4368 fixed GROUP BY in SQL causing problems with postgres
* [CHANGED] #4292 added missing PHPDoc comments and fixed code checker warnings and unified some duplicated code in a static method
* [CHANGED] fixed .travis.yml to check against MOODLE_32_STABLE


3.2.0 (2016-12-05)
------------------

* Moodle 3.2 compatible version


3.1.1 (2016-12-05)
------------------

* [FEATURE] #3589 Make name and description searchable
* [FEATURE] #3302 add support for importing online-text-submissions (incl. embeded files)
* [FEATURE] #3856 add support for importing team-submissions (incl. online-text- and file-submissions)


3.1.0 (2016-06-22)
------------------

* Moodle 3.1 compatible version
* [FEATURE] #3237 Sync Assignment-Submissions automatically


3.0.1 (2016-06-30)
------------------

* [FIXED] #3315 Typo causing warning about undefined variable


3.0.0 (2016-05-11)
------------------

* Moodle 3.0 compatible version
* [CHANGED] PHP 7 compatibility
* [CHANGED] #3134 Reformatted parts of code (code checker issues)
* [FIXED] #3171 Fix files not being restored correctly


2.9.2 (2016-05-12)
------------------

* [FIXED] #3171 Problems with restored files not being shown


2.9.1 (2016-03-04)
------------------

* [FIXED] #3107 German lang strings


2.9.0 (2016-01-20)
------------------

* Moodle 2.9 compatible version
* [CHANGED] #2495 Replace javascript with AMD modules based on JQuery instead of YUI
* [FIXED] Language strings (fix typos, termini, etc.)
* [FIXED] #2737 Capability publication:upload for submit button
* [FIXED] #2777 Uninitialized variable corrupting ZIP files with debugging enabled
* [FIXED] #2886 Disable assignments with teamsubmissions enabled in publication until
  team submissions are supported
* [FIXED] #2875 Usage of fullname function (don't override fullname format anymore)
* [REMOVED] #2495 Unused settings and deprecate unused lang strings
* [REMOVED] Unused cron setting from version.php
* [REMOVED] Unused code


2.8.0 (2015-06-24)
------------------

* Moodle 2.8 compatible version


2.7 (2014-11-30)
----------------

* First release for Moodle 2.7
