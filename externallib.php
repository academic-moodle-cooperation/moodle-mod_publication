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
 * mod_publication external file
 *
 * @package       mod_publication
 * @author        Philipp Hager
 * @author        Andreas Windbichler
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . "/mod/publication/locallib.php");

/**
 * Class mod_publication_external contains external functions used by mod_publication's AJAX
 *
 * @package       mod_publication
 * @author        Philipp Hager
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_publication_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_onlinetextpreview_parameters() {
        // Function get_onlinetextpreview_parameters() always return an external_function_parameters().
        // The external_function_parameters constructor expects an array of external_description.
        return new external_function_parameters(
        // An external_description can be: external_value, external_single_structure or an external_multiple structure!
                [
                        'itemid' => new external_value(PARAM_INT, 'Group\'s or user\'s ID'),
                        'cmid' => new external_value(PARAM_INT, 'Coursemodule ID')
                ]
        );
    }


    /**
     * The function itself
     *
     * @param int $itemid the itemid (user ID or group ID) whom the onlinetext belongs to!
     * @param int $cmid the course-module ID
     * @return string welcome message
     */
    public static function get_onlinetextpreview($itemid, $cmid) {
        global $DB;

        // Parameters validation!
        $params = self::validate_parameters(self::get_onlinetextpreview_parameters(),
                [
                        'itemid' => $itemid,
                        'cmid' => $cmid
                ]);
        $cm = get_coursemodule_from_id('publication', $params['cmid'], 0, false, MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/publication:view', $context);
        require_login($course, true, $cm);

        $text = publication::export_onlinetext_for_preview($params['itemid'], $cm->instance, $context->id);

        return format_text($text, FORMAT_HTML);
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function get_onlinetextpreview_returns() {
        return new external_value(PARAM_RAW, 'HTML snippet representing the online text to use in overlay', VALUE_OPTIONAL, '');
    }
}
