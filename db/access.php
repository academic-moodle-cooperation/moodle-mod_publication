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
 * db/access.php
 *
 * @package       mod_publication
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Philipp Hager (office@phager.at)
 * @author        Andreas Windbichler
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$capabilities = array(
        'mod/publication:view' => array(
                'captype' => 'read',
                'contextlevel' => CONTEXT_MODULE,
                'archetypes' => array(
                        'guest' => CAP_ALLOW,
                        'student' => CAP_ALLOW,
                        'teacher' => CAP_ALLOW,
                        'editingteacher' => CAP_ALLOW,
                        'manager' => CAP_ALLOW
                )
        ),

        'mod/publication:addinstance' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'editingteacher' => CAP_ALLOW,
                        'manager' => CAP_ALLOW
                ),
                'clonepermissionsfrom' => 'moodle/course:manageactivities'
        ),

        'mod/publication:upload' => array(
                'captype' => 'read',
                'contextlevel' => CONTEXT_MODULE,
                'archetypes' => array(
                        'student' => CAP_ALLOW,
                        'teacher' => CAP_ALLOW,
                        'editingteacher' => CAP_ALLOW,
                        'manager' => CAP_ALLOW
                )
        ),

        'mod/publication:approve' => array(
                'captype' => 'read',
                'contextlevel' => CONTEXT_MODULE,
                'archetypes' => array(
                        'teacher' => CAP_ALLOW,
                        'editingteacher' => CAP_ALLOW,
                        'manager' => CAP_ALLOW
                )
        ),

        'mod/publication:grantextension' => array(
                'captype' => 'write',
                'contextlevel' => CONTEXT_MODULE,
                'archetypes' => array(
                        'teacher' => CAP_ALLOW,
                        'editingteacher' => CAP_ALLOW,
                        'manager' => CAP_ALLOW
                )
        ),
);