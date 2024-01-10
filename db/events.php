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
 * By mod_publication observed events
 *
 * @package       mod_publication
 * @author        Philipp Hager
 * @author        Andreas Windbichler
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$observers = [
        [
                'eventname' => 'mod_assign\event\assessable_submitted',
                'callback' => 'mod_publication\observer::import_assessable',
                'includefile' => '/mod/publication/classes/observer.php',
                'priority' => 0,
                'internal' => true,
        ],
        [
                'eventname' => 'mod_assign\event\submission_removed',
                'callback' => 'mod_publication\observer::import_assessable',
                'includefile' => '/mod/publication/classes/observer.php',
                'priority' => 0,
                'internal' => true,
        ],

];
