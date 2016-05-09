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
 * filesform.js
 *
 * @package     mod_publication
 * @author      Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author      Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author      Philipp Hager
 * @copyright   2015 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 /**
  * @module mod_publication/filesform
  */
define(['jquery', 'core/log'], function($, log) {

    /**
     * @constructor
     * @alias module:mod_publication/modform
     */
    var Filesform = function() {
        this.form = $("#fastg");
        this.menuaction = $("#menuaction");
        this.usersel = $(".userselection");
    };

    var instance = new Filesform();

    instance.initializer = function() {
        log.info("Initialize filesform JS!", "mod_publication");
        instance.form.on('submit', function() {
            if (instance.menuaction.val() === 'zipusers') {
                setTimeout(function() {
                    instance.usersel.prop('checked', false);
                }, 100);
            }
        });
    };

    return instance;
});