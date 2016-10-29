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
 * JS enhancing usability of mod_publication's mod_form
 *
 * @package       mod_publication
 * @author        Philipp Hager
 * @author        Andreas Windbichler
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
        var importsel = ".path-mod-publication [name=importfrom], .path-mod-publication [name=obtainstudentapproval], ";
        importsel += ".path-mod-publication [name=autoimport], .path-mod-publication [name=groupapproval]";
        this.importelements = $(importsel).parents(".fitem");

        this.importfrom = $(".path-mod-publication [name=importfrom]");

        var uploadsel = ".path-mod-publication [name=maxfiles], .path-mod-publication [name=maxbytes], ";
        uploadsel += ".path-mod-publication [name=allowedfiletypes], .path-mod-publication [name=obtainteacherapproval]";
        this.uploadelements = $(uploadsel).parents(".fitem");

        // More than 1 input (selection of radio buttons)!
        this.mode = $(".path-mod-publication input[name=mode]");

        this.getapproval = $(".path-mod-publication [name=obtainstudentapproval]");
    };

    Modform.prototype.toggle_available_options = function(e) {
        if (e.stopPropagation !== undefined) {
            e.stopPropagation();
        }
        var mode = parseInt($(".path-mod-publication input[name=mode]:checked").val());

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
            e.data.importelements.fadeOut(600).promise().done(function() {
                e.data.importelements.prop("disabled", true);
            });
            e.data.uploadelements.fadeOut(600).promise().done(function() {
                e.data.uploadelements.prop("disabled", true);
            });
            log.error("Incorrect comparison of mode (Type: " + typeof(mode) + "; Value: " + mode + ")", "publication");
        }

        e.data.toogle_groupapproval_disabled(e);
    };

    Modform.prototype.toogle_groupapproval_disabled = function(e) {
        if (e.stopPropagation !== undefined) {
            e.stopPropagation();
        }

        var mode = parseInt($(".path-mod-publication input[name=mode]:checked").val());
        var importfrom = e.data.importfrom.find(":checked");
        var teamsubmission = 0;
        if (importfrom) {
            teamsubmission = importfrom.data("teamsubmission");
        }

        if ((mode === 0) || (teamsubmission !== 1) || (parseInt(e.data.getapproval.val()) === 0)) {
            // Disabled if mode=upload or no teamsubmission-assignment is selected!
            $(".path-mod-publication [name=groupapproval]").prop("disabled", true);
        } else {
            $(".path-mod-publication [name=groupapproval]").prop("disabled", false);
        }
    };

    var instance = new Modform();

    instance.initializer = function() {

        instance.mode.change(instance, instance.toggle_available_options);
        instance.importfrom.change(instance, instance.toogle_groupapproval_disabled);
        instance.getapproval.change(instance, instance.toogle_groupapproval_disabled);

        log.info("Toggle available options once to begin!");
        instance.toggle_available_options({data: instance});
    };

    return instance;
});