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

defined('MOODLE_INTERNAL') || die();

/**
 * An assign file class that extends rendererable class and is used by the assign module.
 *
 * @package   mod_assign
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class publication_files implements renderable {
    /** @var context $context */
    public $context;
    /** @var string $context */
    public $dir;
    /** @var MoodleQuickForm $portfolioform */
    public $portfolioform;
    /** @var stdClass $cm course module */
    public $cm;
    /** @var stdClass $course */
    public $course;

    /**
     * The constructor
     *
     * @param context $context
     * @param int $sid
     * @param string $filearea
     * @param string $component
     */
    public function __construct(context $context, $sid, $filearea) {
        global $CFG;
        $this->context = $context;
        list($context, $course, $cm) = get_context_info_array($context->id);
        $this->cm = $cm;
        $this->course = $course;
        $fs = get_file_storage();
        $this->dir = $fs->get_area_tree($this->context->id, 'mod_publication', $filearea, $sid);

        $files = $fs->get_area_files($this->context->id,
                                     'mod_publication',
                                     $filearea,
                                     $sid,
                                     'timemodified',
                                     false);

        if (!empty($CFG->enableportfolios)) {
            require_once($CFG->libdir . '/portfoliolib.php');
            if (count($files) >= 1 &&
                    has_capability('mod/assign:exportownsubmission', $this->context)) {
                $button = new portfolio_add_button();
                $callbackparams = array('cmid' => $this->cm->id,
                                        'sid' => $sid,
                                        'area' => $filearea,
                                        'component' => 'mod_publication');
                $button->set_callback_options('assign_portfolio_caller',
                                              $callbackparams,
                                              'mod_assign');
                $button->reset_formats();
                $this->portfolioform = $button->to_html(PORTFOLIO_ADD_TEXT_LINK);
            }

        }

        // Plagiarism check if it is enabled.
        $output = '';
        if (!empty($CFG->enableplagiarism)) {
            require_once($CFG->libdir . '/plagiarismlib.php');

            // For plagiarism_get_links.
            $assignment = new assign($this->context, null, null);
            foreach ($files as $file) {

                $linkparams = array('userid' => $sid,
                                    'file' => $file,
                                    'cmid' => $this->cm->id,
                                    'course' => $this->course,
                                    'assignment' => $assignment->get_instance());
                $output .= plagiarism_get_links($linkparams);

                $output .= '<br />';
            }
        }

        $this->preprocess($this->dir, $filearea, 'mod_publication');
    }

    /**
     * Preprocessing the file list to add the portfolio links if required.
     *
     * @param array $dir
     * @param string $filearea
     * @param string $component
     * @return void
     */
    public function preprocess($dir, $filearea, $component = 'publication') {
        global $CFG;
        foreach ($dir['subdirs'] as $subdir) {
            $this->preprocess($subdir, $filearea, $component);
        }
        foreach ($dir['files'] as $file) {
            $file->portfoliobutton = '';
            if (!empty($CFG->enableportfolios)) {
                $button = new portfolio_add_button();
                if (has_capability('mod/assign:exportownsubmission', $this->context)) {
                    $portfolioparams = array('cmid' => $this->cm->id, 'fileid' => $file->get_id());
                    $button->set_callback_options('assign_portfolio_caller',
                                                  $portfolioparams,
                                                  'mod_assign');
                    $button->set_format_by_file($file);
                    $file->portfoliobutton = $button->to_html(PORTFOLIO_ADD_ICON_LINK);
                }
            }
            $path = '/' .
                    $this->context->id .
                    '/' .
                    $component .
                    '/' .
                    $filearea .
                    '/' .
                    $file->get_itemid() .
                    $file->get_filepath() .
                    $file->get_filename();
            $url = file_encode_url("$CFG->wwwroot/pluginfile.php", $path, true);
            $filename = $file->get_filename();
            $file->fileurl = html_writer::link($url, $filename);
        }
    }
}