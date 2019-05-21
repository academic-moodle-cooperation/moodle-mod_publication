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
 * Contains event class for a single mod_publication being viewed
 *
 * @package       mod_publication
 * @author        Hannes Laimer
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_publication\event;
defined('MOODLE_INTERNAL') || die();

/**
 * A file was deleted for this event
 *
 * @package       mod_publication
 * @author        Hannes Laimer
 * @copyright     2019 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class publication_file_imported extends \core\event\base {
    /**
     * Init event objecttable
     */
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['objecttable'] = 'publication_file';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    /**
     * Logs that a file was imported
     * @param \stdClass $cm
     * @param object $do
     * @return \core\event\base
     * @throws \coding_exception
     */
    public static function file_added(\stdClass $cm, $do) {
        // Trigger overview event.
        $event = self::create(array(
            'objectid'      => (int)$do->publication,
            'context'       => \context_module::instance($cm->id),
            'relateduserid' => $do->userid,
            'other'         => (Array)$do
        ));
        return $event;
    }
    // You might need to override get_url() and get_legacy_log_data() if view mode needs to be stored as well.
    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The ".$this->data['other']['typ']." with id '".$this->data['other']['itemid'].
            "' added a file with id '".$this->data['other']['fileid'].
            "' which was imported to publication with id '".$this->data['other']['publication']."'";
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventpublicationfileimported', 'publication');
    }

    /**
     * Get URL related to the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
        $moduleid = get_coursemodule_from_instance('publication', $this->data['other']['publication'])->id;
        return new \moodle_url("/mod/publication/view.php", array('id'  => $moduleid));
    }

    /**
     * Return the legacy event log data.
     *
     * @return array|null
     */
    protected function get_legacy_logdata() {
        return array($this->courseid, 'publication', 'file imported '.$this->data['other']['typ'], $this->get_url(),
            $this->data['other']['publication'], $this->contextinstanceid);
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();
        // Make sure this class is never used without proper object details.
        if (empty($this->objectid) || empty($this->objecttable)) {
            throw new \coding_exception('The registration_created event must define objectid and object table.');
        }
        // Make sure the context level is set to module.
        if ($this->contextlevel != CONTEXT_MODULE) {
            throw new \coding_exception('Context level must be CONTEXT_MODULE.');
        }
    }
}