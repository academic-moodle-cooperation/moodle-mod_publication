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

require_once($CFG->dirroot . '/mod/publication/locallib.php');

/**
 * A custom renderer class that extends the plugin_renderer_base and is used by the assign module.
 *
 * @package mod_assign
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_publication_renderer extends plugin_renderer_base {

    /**
     * Rendering assignment files
     *
     * @param context $context
     * @param int $userid
     * @param string $filearea
     * @param string $component
     * @return string
     */
    public function assign_files(context $context, $userid, $filearea, $component) {
        return $this->render(new publication_files($context, $userid, $filearea, $component));
    }


    /**
     * Rendering assignment files
     *
     * @param assign_files $tree
     * @return string
     */
    public function render_publication_files(publication_files $tree) {
        $this->htmlid = 'assign_files_tree_'.uniqid();
        $this->page->requires->js_init_call('M.mod_assign.init_tree', array(true, $this->htmlid));
        $html = '<div id="'.$this->htmlid.'">';
        $html .= $this->htmllize_tree($tree, $tree->dir);
        $html .= '</div>';

        if ($tree->portfolioform) {
            $html .= $tree->portfolioform;
        }
        return $html;
    }

     /**
     * Page is done - render the footer.
     *
     * @return void
     */
    public function render_footer() {
        return $this->output->footer();
    }


    /**
     * Internal function - creates htmls structure suitable for YUI tree.
     *
     * @param assign_files $tree
     * @param array $dir
     * @return string
     */
    protected function htmllize_tree(publication_files $tree, $dir) {
        global $CFG;
        $yuiconfig = array();
        $yuiconfig['type'] = 'html';

        if (empty($dir['subdirs']) and empty($dir['files'])) {
            return '';
        }

        $result = '<ul>';
        foreach ($dir['subdirs'] as $subdir) {
            $image = $this->output->pix_icon(file_folder_icon(),
                                             $subdir['dirname'],
                                             'moodle',
                                             array('class'=>'icon'));
            $result .= '<li yuiConfig=\'' . json_encode($yuiconfig) . '\'>' .
                       '<div>' . $image . ' ' . s($subdir['dirname']) . '</div> ' .
                       $this->htmllize_tree($tree, $subdir) .
                       '</li>';
        }

        foreach ($dir['files'] as $file) {
            $filename = $file->get_filename();
            $image = $this->output->pix_icon(file_file_icon($file),
                                             $filename,
                                             'moodle',
                                             array('class'=>'icon'));
            $result .= '<li yuiConfig=\'' . json_encode($yuiconfig) . '\'>' .
                       '<div>' . $image . ' ' .
                                 $file->fileurl . ' ' .
                                 $file->portfoliobutton . '</div>' .
                       '</li>';
        }

        $result .= '</ul>';

        return $result;
    }

    /**
     * Helper method dealing with the fact we can not just fetch the output of flexible_table
     *
     * @param flexible_table $table The table to render
     * @param int $rowsperpage How many assignments to render in a page
     * @param bool $displaylinks - Whether to render links in the table
     *                             (e.g. downloads would not enable this)
     * @return string HTML
     */
    protected function flexible_table(flexible_table $table, $rowsperpage, $displaylinks) {

        $o = '';
        ob_start();
        $table->out($rowsperpage, $displaylinks);
        $o = ob_get_contents();
        ob_end_clean();

        return $o;
    }

    /**
     * Helper method dealing with the fact we can not just fetch the output of moodleforms
     *
     * @param moodleform $mform
     * @return string HTML
     */
    protected function moodleform(moodleform $mform) {

        $o = '';
        ob_start();
        $mform->display();
        $o = ob_get_contents();
        ob_end_clean();

        return $o;
    }

}