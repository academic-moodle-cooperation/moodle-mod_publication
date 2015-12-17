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
 * upload_form.php
 *
 * @package       mod_publication
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Philipp Hager (office@phager.at)
 * @author        Andreas Windbichler
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir.'/formslib.php'); // Putting this is as a safety as i got a class not found error.

/**
 * Form to upload files for mod_publication
 *
 * @package       mod_publication
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Philipp Hager (office@phager.at)
 * @author        Andreas Windbichler
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_publication_upload_form extends moodleform {

    /**
     * Definition of file upload format
     */
    public function definition() {
        $mform = $this->_form;

        $currententry       = $this->_customdata['current'];
        $publication        = $this->_customdata['publication'];
        $cm                = $this->_customdata['cm'];
        $definitionoptions = $this->_customdata['definitionoptions'];
        $attachmentoptions = $this->_customdata['attachmentoptions'];

        $context  = context_module::instance($cm->id);
        // Prepare format_string/text options.
        $fmtoptions = array(
                'context' => $context);

        if ($publication->get_instance()->obtainteacherapproval) {
            $text = get_string('published_aftercheck', 'publication');
        } else {
            $text = get_string('published_immediately', 'publication');
        }

        $mform->addElement('header', 'myfiles', get_string('myfiles', 'publication'));

        $mform->addElement('static', 'guideline', get_string('guideline', 'publication'), $text);

        $mform->addElement('filemanager', 'attachment_filemanager', '', null, $attachmentoptions);

        // Hidden params.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);

        // Buttons.
        $this->add_action_buttons(true, get_string('save_changes', 'publication'));
        $this->set_data($currententry);
    }
}
