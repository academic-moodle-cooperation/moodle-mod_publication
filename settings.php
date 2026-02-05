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

$settings = new admin_settingpage('modpublication', get_string('pluginname', 'publication'));

if ($ADMIN->fulltree) {
    require_once(__DIR__ . '/locallib.php');

    $name = new lang_string('maxfiles', 'publication');
    $description = new lang_string('configmaxfiles', 'publication');
    $setting = new admin_setting_configtext('publication/maxfiles', $name, $description, 5, PARAM_INT);
    $settings->add($setting);

    $options = [
        '0' => get_string('obtainapproval_automatic', 'publication'),
        '1' => get_string('obtainapproval_required', 'publication'),
    ];

    $name = new lang_string('obtainteacherapproval_admin', 'publication');
    $description = new lang_string('obtainteacherapproval_admin_desc', 'publication');
    $setting = new admin_setting_configselect('publication/obtainteacherapproval', $name, $description, 0, $options);
    $settings->add($setting);

    $name = new lang_string('obtainstudentapproval_admin', 'publication');
    $description = new lang_string('obtainstudentapproval_admin_desc', 'publication');
    $setting = new admin_setting_configselect('publication/obtainstudentapproval', $name, $description, 0, $options);
    $settings->add($setting);

    $options = [
        PUBLICATION_APPROVAL_GROUPAUTOMATIC => get_string('obtainapproval_automatic', 'publication'),
        PUBLICATION_APPROVAL_SINGLE => get_string('obtaingroupapproval_single', 'publication'),
        PUBLICATION_APPROVAL_ALL => get_string('obtaingroupapproval_all', 'publication'),
    ];

    $name = new lang_string('obtaingroupapproval_admin', 'publication');
    $description = new lang_string('obtaingroupapproval_admin_desc', 'publication');
    $setting = new admin_setting_configselect('publication/obtaingroupapproval', $name, $description, 0, $options);
    $settings->add($setting);

    $options = [
        PUBLICATION_NOTIFY_NONE => get_string('notify:setting:0', 'publication'),
        PUBLICATION_NOTIFY_TEACHER => get_string('notify:setting:1', 'publication'),
        PUBLICATION_NOTIFY_STUDENT => get_string('notify:setting:2', 'publication'),
        PUBLICATION_NOTIFY_ALL => get_string('notify:setting:3', 'publication'),
    ];

    $name = new lang_string('notify:filechange_admin', 'publication');
    $description = new lang_string('notify:filechange_help', 'publication');
    $setting = new admin_setting_configselect(
        'publication/notifyfilechange',
        $name,
        $description,
        PUBLICATION_NOTIFY_STUDENT,
        $options
    );
    $settings->add($setting);

    $name = new lang_string('notify:statuschange_admin', 'publication');
    $description = new lang_string('notify:statuschange_help', 'publication');
    $setting = new admin_setting_configselect(
        'publication/notifystatuschange',
        $name,
        $description,
        PUBLICATION_NOTIFY_ALL,
        $options
    );
    $settings->add($setting);


    if (isset($CFG->maxbytes)) {
        $name = new lang_string('maxbytes', 'publication');
        $description = new lang_string('configmaxbytes', 'publication');
        $setting = new admin_setting_configselect(
            'publication/maxbytes',
            $name,
            $description,
            5242880,
            get_max_upload_sizes($CFG->maxbytes)
        );
        $settings->add($setting);
    }

    $options = [
        0 => get_string('no'),
        1 => get_string('yes'),
    ];

    $name = new lang_string('availabilityrestriction_admin', 'publication');
    $description = new lang_string('availabilityrestriction_admin_desc', 'publication');
    $setting = new admin_setting_configselect('publication/availabilityrestriction', $name, $description, 1, $options);
    $settings->add($setting);

    $name = new lang_string('allowsubmissionsfromdate', 'publication');
    $description = new lang_string('allowsubmissionsfromdate_help', 'publication');
    $setting = new admin_setting_configduration(
        'publication/allowsubmissionsfromdate',
        $name,
        $description,
        0
    );
    $setting->set_enabled_flag_options(admin_setting_flag::ENABLED, true);
    $settings->add($setting);

    $name = new lang_string('duedate', 'publication');
    $description = new lang_string('duedate_help', 'publication');
    $setting = new admin_setting_configduration(
        'publication/duedate',
        $name,
        $description,
        604800
    );
    $setting->set_enabled_flag_options(admin_setting_flag::ENABLED, true);
    $settings->add($setting);

    $name = new lang_string('approvalfromdate', 'publication');
    $description = new lang_string('approvalfromdate_help', 'publication');
    $setting = new admin_setting_configduration(
        'publication/approvalfromdate',
        $name,
        $description,
        0
    );
    $setting->set_enabled_flag_options(admin_setting_flag::ENABLED, true);
    $settings->add($setting);

    $name = new lang_string('approvaltodate', 'publication');
    $description = new lang_string('approvaltodate_help', 'publication');
    $setting = new admin_setting_configduration(
        'publication/approvaltodate',
        $name,
        $description,
        604800
    );
    $setting->set_enabled_flag_options(admin_setting_flag::ENABLED, true);
    $settings->add($setting);
}
