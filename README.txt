// This file is part of mod_organizer for Moodle - http://moodle.org/
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
// If not, see <http://www.gnu.org/licenses/>.

/**
 * README.txt
 * @version       2016-06-22
 * @package       mod_publication
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Philipp Hager (office@phager.at)
 * @author        Andreas Windbichler
 * @author        Eva Karall (eva.maria.karall@univie.ac.at)
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

# ---------------------------------------------------------------
# FOR Moodle 3.1+
# ---------------------------------------------------------------

Publication module
===============

OVERVIEW
================================================================================
    The Publication module is used to allow students to share files with each other.

    A publication can use two different modes:
    1) Import files from an assignment
    2) Allow students to upload own files

    The publication module also provides the possibility to either
    allow files to be instantly visible to all the other students or
    the files require an approval from a teacher first.
    This gives teachers the possibility to controll which files are shared.

REQUIREMENTS
================================================================================
    Moodle 3.1 or later

INSTALLATION
================================================================================
    To install, extract the contents of the archive to the mod/ folder in the moodle
    root folder, and all of the archive's contents will be properly placed into the
    folder structure. The module and all of its files is located in mod/publication
    folder and require no other files or folders.

    The langfiles can be put into the folder mod/publication/lang normally.
    All languages should be encoded with utf-8.

    After it you have to run the admin-page of moodle (http://your-moodle-site/admin)
    in your browser. You have to loged in as admin before.
    The installation process will be displayed on the screen. All the data necessary
    for a proper install is contained in the help files displayed on screen.

CHANGELOG
================================================================================
v 2016062200
-------------------------
*) Moodle 3.1 update
*) New Feature: Sync Assignment-Submissions automatically!

v 2016051101
-------------------------
*) fixed typo causing warning about undefined variable

v 2016051100
-------------------------
*) PHP 7 compatibility!
*) Reformatted parts of code (code checker issues)
*) Fix files not being restored correctly

v 2016012000
-------------------------
*) removed some unused code
*) updated language strings (fix typos, termini, etc.)
*) check capability publication:upload for submit button
*) fix uninitialized variable corrupting ZIP files with debugging enabled
*) remove unused settings and deprecate unused lang strings
*) replace javascript with AMD modules based on JQuery instead of YUI
*) remove unused cron setting from version.php
*) disable assignments with teamsubmissions enabled in publication until teamsubmissions are supported
*) fix usage of fullname function (don't override fullname format anymore)
