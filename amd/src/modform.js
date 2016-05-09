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
        this.importfrom = $(".path-mod-publication #fitem_id_importfrom");
        this.optainstudentapproval = $(".path-mod-publication #fitem_id_obtainstudentapproval");

        this.maxfiles = $(".path-mod-publication #fitem_id_maxfiles");
        this.maxbytes = $(".path-mod-publication #fitem_id_maxbytes");
        this.allowedfiletypes = $(".path-mod-publication #fitem_id_allowedfiletypes");
        this.optainteacherapproval = $(".path-mod-publication #fitem_id_obtainteacherapproval");
        // More than 1 input (selection of radio buttons)!
        this.mode = $(".path-mod-publication #fgroup_id_modegrp input[name=mode]");
    };

    Modform.prototype.toggle_available_options = function(e) {
        var mode = parseInt($(".path-mod-publication #fgroup_id_modegrp input[name=mode]:checked").val());

        if (mode === 0) { // Uploads by students!
            e.data.importfrom.fadeOut(600);
            e.data.optainstudentapproval.fadeOut(600);

            // We make sure they appear after the fadeout!
            window.setTimeout(function() {
                e.data.maxfiles.fadeIn(600);
                e.data.maxbytes.fadeIn(600);
                e.data.allowedfiletypes.fadeIn(600);
                e.data.optainteacherapproval.fadeIn(600);
            }, 600);
        } else if (mode === 1) { // Files from assignment!
            e.data.maxfiles.fadeOut(600);
            e.data.maxbytes.fadeOut(600);
            e.data.allowedfiletypes.fadeOut(600);
            e.data.optainteacherapproval.fadeOut(600);

            // We make sure they appear after the fadeout!
            window.setTimeout(function() {
                e.data.importfrom.fadeIn(600);
                e.data.optainstudentapproval.fadeIn(600);
            }, 600);
        } else {
            log.error("Incorrect comparison of mode (Type: " + typeof(mode) + "; Value: " + mode + ")", "publication");
        }
    };

    var instance = new Modform();

    instance.initializer = function() {

        instance.mode.change(instance, instance.toggle_available_options);

        instance.toggle_available_options({data: instance});
    };

    return instance;
});