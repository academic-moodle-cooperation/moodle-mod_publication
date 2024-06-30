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
 * Settings definitions for mod_publication
 *
 * @package       mod_publication
 * @author        Philipp Hager
 * @author        Andreas Windbichler
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

global $CFG;

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_configtext('publication/maxfiles', get_string('maxfiles', 'publication'),
            get_string('configmaxfiles', 'publication'), 5, PARAM_INT));


    $options = [
        '0' => get_string('obtainapproval_automatic', 'publication'),
        '1' => get_string('obtainapproval_required', 'publication'),
    ];

    $settings->add(new admin_setting_configselect('publication/obtainteacherapproval', get_string('obtainteacherapproval_admin', 'publication'),
            get_string('obtainteacherapproval_admin_desc', 'publication'), 0, $options));

    $settings->add(new admin_setting_configselect('publication/obtainstudentapproval', get_string('obtainstudentapproval_admin', 'publication'),
            get_string('obtainstudentapproval_admin_desc', 'publication'), 0, $options));

    $options = [
        PUBLICATION_APPROVAL_GROUPAUTOMATIC => get_string('obtainapproval_automatic', 'publication'),
        PUBLICATION_APPROVAL_SINGLE => get_string('obtaingroupapproval_single', 'publication'),
        PUBLICATION_APPROVAL_ALL => get_string('obtaingroupapproval_all', 'publication'),
    ];

    $settings->add(new admin_setting_configselect('publication/obtaingroupapproval', get_string('obtaingroupapproval_admin', 'publication'),
        get_string('obtaingroupapproval_admin_desc', 'publication'), 0, $options));

    if (isset($CFG->maxbytes)) {
        $settings->add(new admin_setting_configselect('publication/maxbytes', get_string('maxbytes', 'publication'),
                get_string('configmaxbytes', 'publication'), 5242880, get_max_upload_sizes($CFG->maxbytes)));
    }
}
