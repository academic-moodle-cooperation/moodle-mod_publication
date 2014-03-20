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
 *
 * @package mod_publication
 * @author Andreas Windbichler
 * @copyright TSC
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/publication/backup/moodle2/backup_publication_stepslib.php');


class backup_publication_activity_task extends backup_activity_task {

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity.
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        $this->add_step(new backup_publication_activity_structure_step('publication_structure', 'publication.xml'));
    }

    /**
     * Code the transformations to perform in the activity in
     * order to get transportable (encoded) links
     * @param string $content
     * @return string
     */
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, "/");

        $search="/(".$base."\/mod\/publication\/index.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@PUBLICATIONINDEX*$2@$', $content);

        $search="/(".$base."\/mod\/publication\/view.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@PUBLICATIONVIEWBYID*$2@$', $content);

        return $content;
    }

}

