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
 * settings.php
 *
 * @package       mod_publication
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Philipp Hager (office@phager.at)
 * @author        Andreas Windbichler
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_configcheckbox('publication/obtainstudentapproval',
            get_string('obtainstudentapproval', 'publication'), get_string('configobtainstudentapproval', 'publication'), 1));

    $settings->add(new admin_setting_configcheckbox('publication/obtainteacherapproval',
            get_string('obtainteacherapproval', 'publication'), get_string('configobtainteacherapproval', 'publication'), 1));

    $settings->add(new admin_setting_configtext('publication/maxfiles', get_string('maxfiles', 'publication'),
            get_string('configmaxfiles', 'publication'), 5, PARAM_INT));

    if (isset($CFG->maxbytes)) {
        $settings->add(new admin_setting_configselect('publication/maxbytes', get_string('maxbytes', 'publication'),
                get_string('configmaxbytes', 'publication'), 5242880, get_max_upload_sizes($CFG->maxbytes)));
    }

    $settings->add(new admin_setting_configcheckbox('publication/hideidnumberfromstudents',
            get_string('hideidnumberfromstudents', 'publication'), get_string('hideidnumberfromstudents_desc', 'publication'), 1));
}
