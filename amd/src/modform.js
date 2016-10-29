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
 * mod_form.js
 *
 * @package     mod_publication
 * @author      Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author      Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author      Philipp Hager
 * @copyright   2015 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 /**
  * @module mod_publication/modform
  */
define(['jquery', 'core/log'], function($, log) {

    /**
     * @constructor
     * @alias module:mod_publication/modform
     */
    var Modform = function() {
        var importsel = ".path-mod-publication #fitem_id_importfrom, .path-mod-publication #fitem_id_obtainstudentapproval, ";
        importsel += ".path-mod-publication #fitem_id_autoimport, .path-mod-publication #fgroup_id_groupapprovalarray";
        this.importelements = $(importsel);

        var uploadsel = ".path-mod-publication #fitem_id_maxfiles, .path-mod-publication #fitem_id_maxbytes, ";
        uploadsel += ".path-mod-publication #fitem_id_allowedfiletypes, .path-mod-publication #fitem_id_obtainteacherapproval";
        this.uploadelements = $(uploadsel);
        // More than 1 input (selection of radio buttons)!
        this.mode = $(".path-mod-publication #fgroup_id_modegrp input[name=mode]");
    };

    Modform.prototype.toggle_available_options = function(e) {
        if (e.stopPropagation !== undefined) {
            e.stopPropagation();
        }
        var mode = parseInt($(".path-mod-publication #fgroup_id_modegrp input[name=mode]:checked").val());

        if (mode === 0) { // Uploads by students!
            e.data.importelements.fadeOut(600).promise().done(function() {
                e.data.importelements.prop("disabled", true);
                e.data.uploadelements.fadeIn(600).promise().done(function() {
                    e.data.uploadelements.prop("disabled", false);
                });
            });
        } else if (mode === 1) { // Files from assignment!
            e.data.uploadelements.fadeOut(600).promise().done(function() {
                e.data.uploadelements.prop("disabled", true);
                e.data.importelements.fadeIn(600).promise().done(function() {
                    e.data.importelements.prop("disabled", false);
                });
            });
        } else {
            log.error("Incorrect comparison of mode (Type: " + typeof(mode) + "; Value: " + mode + ")", "publication");
        }
    };

    var instance = new Modform();

    instance.initializer = function() {

        instance.mode.change(instance, instance.toggle_available_options);

        log.info("Toggle available options once to begin!");
        instance.toggle_available_options({data: instance});
    };

    return instance;
});