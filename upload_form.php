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
 * File containing upload form class.
 *
 * @package       mod_publication
 * @author        Philipp Hager
 * @author        Andreas Windbichler
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->libdir . '/formslib.php'); // Putting this is as a safety as i got a class not found error.

/**
 * Form to upload files for mod_publication
 *
 * @package       mod_publication
 * @author        Philipp Hager
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

        $currententry = $this->_customdata['current'];
        $publication = $this->_customdata['publication'];
        $attachmentoptions = $this->_customdata['attachmentoptions'];

        if ($publication->get_instance()->obtainteacherapproval) {
            $text = get_string('published_aftercheck', 'publication');
        } else {
            $text = get_string('published_immediately', 'publication');
        }

        $mform->addElement('header', 'myfiles', get_string('myfiles', 'publication'));

        $mform->addElement('static', 'guideline', get_string('guideline', 'publication'), $text);

        $mform->addElement('filemanager', 'attachment_filemanager', get_string('myfiles', 'publication'), null, $attachmentoptions);

        // Add notice of allowed file types if they're restricted!
        if (!empty($attachmentoptions['accepted_types']) && $attachmentoptions['accepted_types'] !== '*') {
            $text = html_writer::tag('p', get_string('filesofthesetypes', 'publication'));
            $text .= html_writer::start_tag('ul');

            $typesets = $publication->get_configured_typesets();
            foreach ($typesets as $type) {
                $a = new stdClass();
                $extensions = file_get_typegroup('extension', $type);
                $typetext = html_writer::tag('li', $type);
                // Only bother checking if it's a mimetype or group if it has extensions in the group.
                if (!empty($extensions)) {
                    if (strpos($type, '/') !== false) {
                        $a->name = get_mimetype_description($type);
                        $a->extlist = implode(' ', $extensions);
                        $typetext = html_writer::tag('li', $a->name . ' &mdash; ' . $a->extlist);
                    } else if (get_string_manager()->string_exists("group:$type", 'mimetypes')) {
                        $a->name = get_string("group:$type", 'mimetypes');
                        $a->extlist = implode(' ', $extensions);
                        $typetext = html_writer::tag('li', $a->name . ' &mdash; ' . $a->extlist);
                    }
                }
                $text .= $typetext;
            }

            $text .= html_writer::end_tag('ul');
            $mform->addElement('static', '', '', $text);
        }

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
