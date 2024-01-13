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
 * Contains interface and callback methods for mod_publication
 *
 * @package       mod_publication
 * @author        Philipp Hager
 * @author        Andreas Windbichler
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/locallib.php');

/**
 * Adds a new publication instance
 *
 * @param stdClass $publication data (from mod_publication_mod_form)
 * @return int publication ID
 */
function publication_add_instance($publication) {
    global $DB, $OUTPUT;

    $cmid = $publication->coursemodule;
    $courseid = $publication->course;

    $id = 0;
    try {
        $id = $DB->insert_record('publication', $publication);
    } catch (Exception $e) {
        echo $OUTPUT->notification($e->getMessage(), 'error');
    }

    $DB->set_field('course_modules', 'instance', $id, ['id' => $cmid]);

    $record = $DB->get_record('publication', ['id' => $id]);

    $record->course = $courseid;
    $record->cmid = $cmid;

    $course = $DB->get_record('course', ['id' => $record->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_id('publication', $cmid, 0, false, MUST_EXIST);
    $context = context_module::instance($cm->id);
    $instance = new publication($cm, $course, $context);

    $instance->update_calendar_event();

    if ($instance->get_instance()->mode == PUBLICATION_MODE_IMPORT) {
        // Fetch all files right now!
        $instance->importfiles();
    }

    return $record->id;
}

/**
 * Return the list if Moodle features this module supports
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, null if doesn't know
 */
function publication_supports($feature) {
    switch ($feature) {
        case FEATURE_GROUPS:
            return true;
        case FEATURE_GROUPINGS:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_IDNUMBER:
            return true;
        case FEATURE_MOD_PURPOSE:
            return MOD_PURPOSE_COLLABORATION;
        default:
            return null;
    }
}

/**
 * updates an existing publication instance
 *
 * @param stdClass $publication from mod_publication_mod_form
 * @return bool true
 */
function publication_update_instance($publication) {
    global $DB;

    $publication->id = $publication->instance;

    $publication->timemodified = time();

    $DB->update_record('publication', $publication);

    $course = $DB->get_record('course', ['id' => $publication->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('publication', $publication->id, 0, false, MUST_EXIST);
    $context = context_module::instance($cm->id);
    $instance = new publication($cm, $course, $context);

    $instance->update_calendar_event();

    if ($instance->get_instance()->mode == PUBLICATION_MODE_IMPORT) {
        // Fetch all files right now!
        $instance->importfiles();
    }

    return true;
}

/**
 * complete deletes an publication instance
 *
 * @param int $id
 * @return bool
 */
function publication_delete_instance($id) {
    global $DB;

    if (!$publication = $DB->get_record('publication', ['id' => $id])) {
        return false;
    }

    $DB->delete_records('publication_extduedates', ['publication' => $publication->id]);

    $fs = get_file_storage();

    $fs->delete_area_files($publication->id, 'mod_publication', 'attachment');

    $DB->delete_records('publication_file', ['publication' => $publication->id]);

    $DB->delete_records('event', ['modulename' => 'publication', 'instance' => $publication->id]);

    $tableuniqueid = \mod_publication\local\allfilestable\base::get_table_uniqueid($id);
    $DB->delete_records('user_preferences', ['name' => $tableuniqueid]);
    $filteruserpreference = 'mod-publication-perpage-' . $id;
    $DB->delete_records('user_preferences', ['name' => $filteruserpreference]);

    $result = true;
    if (!$DB->delete_records('publication', ['id' => $publication->id])) {
        $result = false;
    }

    return $result;
}

/**
 * Returns info object about the course module
 *
 * @param stdClass $coursemodule The coursemodule object (record).
 * @return bool|cached_cm_info An object on information that the courses will know about (most noticeably, an icon) or false.
 */
function publication_get_coursemodule_info($coursemodule) {
    global $DB;

    $dbparams = ['id' => $coursemodule->instance];
    $fields = 'id, name, alwaysshowdescription, allowsubmissionsfromdate, intro, introformat, completionupload';
    if (!$publication = $DB->get_record('publication', $dbparams, $fields)) {
        return false;
    }

    $result = new cached_cm_info();
    $result->name = $publication->name;
    if ($coursemodule->showdescription) {
        if ($publication->alwaysshowdescription || time() > $publication->allowsubmissionsfromdate) {
            // Convert intro to html. Do not filter cached version, filters run at display time.
            $result->content = format_module_intro('publication', $publication, $coursemodule->id, false);
        }
    }

    // Populate the custom completion rules as key => value pairs, but only if the completion mode is 'automatic'.
    if ($coursemodule->completion == COMPLETION_TRACKING_AUTOMATIC) {
        $result->customdata['customcompletionrules']['completionupload'] = $publication->completionupload;
    }

    return $result;
}

/**
 * Defines which elements mod_publication needs to add to reset form
 *
 * @param MoodleQuickForm $mform The reset course form to extend
 */
function publication_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'publicationheader', get_string('modulenameplural', 'publication'));
    $mform->addElement('checkbox', 'reset_publication_userdata', get_string('reset_userdata', 'publication'));
}

/**
 * Reset the userdata in publication module
 *
 * @param object $data settings object which userdata to reset
 * @return array[] array of associative arrays giving feedback what has been successfully reset
 */
function publication_reset_userdata($data) {
    global $DB;

    if (!$DB->count_records('publication', ['course' => $data->courseid])) {
        return [];
    }

    $componentstr = get_string('modulenameplural', 'publication');
    $status = [];

    if (isset($data->reset_publication_userdata)) {

        $publications = $DB->get_records('publication', ['course' => $data->courseid]);

        foreach ($publications as $publication) {

            $DB->delete_records('publication_extduedates', ['publication' => $publication->id]);

            $filerecords = $DB->get_records('publication_file', ['publication' => $publication->id]);

            $fs = get_file_storage();
            foreach ($filerecords as $filerecord) {
                if ($file = $fs->get_file_by_id($filerecord->fileid)) {
                    $file->delete();
                }
            }

            $DB->delete_records('publication_file', ['publication' => $publication->id]);

            $status[] = [
                    'component' => $componentstr,
                    'item' => $publication->name,
                    'error' => false,
            ];
        }
    }

    return $status;

}


/**
 * extend an assigment navigation settings
 *
 * @param settings_navigation $settings
 * @param navigation_node $navref
 * @return void
 */
function publication_extend_settings_navigation(settings_navigation $settings, navigation_node $navref) {
    global $DB;

    // We want to add these new nodes after the Edit settings node, and before the
    // Locally assigned roles node. Of course, both of those are controlled by capabilities.
    $keys = $navref->get_children_key_list();
    $beforekey = null;
    $i = array_search('modedit', $keys);
    if ($i === false && array_key_exists(0, $keys)) {
        $beforekey = $keys[0];
    } else if (array_key_exists($i + 1, $keys)) {
        $beforekey = $keys[$i + 1];
    }

    $cm = $settings->get_page()->cm;
    if (!$cm) {
        return;
    }

    $context = $cm->context;
    $course = $settings->get_page()->course;

    if (!$course) {
        return;
    }

    if (has_capability('mod/publication:addinstance', $settings->get_page()->cm->context)) {
        $url = new moodle_url('/mod/publication/view.php', ['id' => $settings->get_page()->cm->id, 'allfilespage' => '1']);

        $node = navigation_node::create(get_string('allfiles', 'publication'),
            $url,
            navigation_node::TYPE_SETTING, null, 'mod_publication_allfiles');
        $navref->add_node($node, $beforekey);
    }

}

/**
 * Serves resource files.
 *
 * @param mixed $course course or id of the course
 * @param mixed $cm course module or id of the course module
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - just send the file
 */
function mod_publication_pluginfile($course, $cm, context $context, $filearea, $args, $forcedownload, array $options = []) {
    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_login($course, false, $cm);
    if (!has_capability('mod/publication:view', $context)) {
        return false;
    }

    if ($filearea !== 'attachment') {
        return false;
    }

    $itemid = (int)array_shift($args);

    $relativepath = implode('/', $args);

    $fullpath = "/{$context->id}/mod_publication/$filearea/$itemid/$relativepath";
    $fs = get_file_storage();
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) || $file->is_directory()) {
        return false;
    }

    send_stored_file($file, 0, 0, true, $options);

    // Wont be reached!
    return false;
}

/**
 * Callback for block_myoverview which will decide whether it will be shown in the overview
 *
 * @param event
 * @param factory
 */
function mod_publication_core_calendar_provide_event_action(calendar_event $event, \core_calendar\action_factory $factory) {
    global $CFG, $USER, $DB;
    require_once($CFG->dirroot . '/mod/publication/locallib.php');

    // Get the instance of the publication with the way recommended by the docs.
    $courseinstance = get_fast_modinfo($event->courseid)->instances['publication'][$event->instance];
    $instance = new publication($courseinstance);

    // Only show this instance if it's open
    if ($instance->is_open()) {
        // Also don't show this instance when the user already uploaded one or more files
        $files = $DB->count_records('publication_file', ['publication' => $event->instance, 'userid' => $USER->id]);

        if ($files >= 1) {
            return null;
        }

        return $factory->create_instance(
            get_string('add_uploads', 'publication'), // Name of the action button
            new \moodle_url('/mod/publication/view.php', ['id' => $courseinstance->id]), // URL of the instance
            1, // Count of necessary actions
            true // Whether the user can take action on this folder.
        );
    }
}


/**
 * Callback which returns human-readable strings describing the active completion custom rules for the module instance.
 *
 * @param cm_info|stdClass $cm object with fields ->completion and ->customdata['customcompletionrules']
 * @return array $descriptions the array of descriptions for the custom rules.
 */
function mod_publication_get_completion_active_rule_descriptions($cm) {
    // Values will be present in cm_info, and we assume these are up to date.
    if (empty($cm->customdata['customcompletionrules'])
        || $cm->completion != COMPLETION_TRACKING_AUTOMATIC) {
        return [];
    }

    $descriptions = [];
    foreach ($cm->customdata['customcompletionrules'] as $key => $val) {
        switch ($key) {
            case 'completionupload':
                if (!empty($val)) {
                    $descriptions[] = get_string('completionupload', 'publication');
                }
                break;
            default:
                break;
        }
    }
    return $descriptions;
}

