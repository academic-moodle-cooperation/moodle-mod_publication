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
 * Admin-Settings for mod publication
 * 
 * @package       mod_publication
 * @author        Windbichler Andras
 * @copyright     TSC
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configcheckbox('publication/requiremodintro',
    		get_string('requiremodintro', 'publication'), get_string('configrequiremodintro', 'publication'), 1));
    
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
}
