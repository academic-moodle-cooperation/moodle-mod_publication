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
 * Contains form class for approving publication files
 *
 * @package       mod_publication
 * @author        Hannes Laimer
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/publication/locallib.php');

/**
 * Form for displaying and changing approval for publication files
 *
 * @package       mod_publication
 * @author        Hannes Laimer
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_publication_allfiles_form extends moodleform {

    /**
     * Form definition. Abstract method - always override!
     */
    protected function definition() {
        $mform = $this->_form;
        $generatedform = str_replace('<select', '<select onchange=\'this.form.submit()\'', $this->_customdata['form']);
        $mform->addElement('html', $generatedform);
    }
}