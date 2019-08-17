// This file is part of mod_grouptool for Moodle - http://moodle.org/
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
 * Javascript to align rows
 *
 * @package   mod_publication
 * @author    Hannes Laimer
 * @copyright 2019 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 /**
  * @module mod_publication/alignrows
  */
define(['jquery'], function($) {

    /**
     * @constructor
     * @alias module:mod_publication/alignrows
     */
    var Alignrows = function() {
        this.cmid = 0;
    };

    var instance = new Alignrows();
    instance.initializer = function() {
        $("#attempts").ready(function () {
            var alltds = $("#attempts > tbody > tr > td > table > tbody > tr > td");
            var maxHeight = Math.max.apply(null, alltds.map(function () {
                return $(this).height();
            }).get());
            alltds.height(maxHeight).css('vertical-align', 'middle');
            $(".permissionstable > tbody > tr > td").removeClass('c0');
        });
    };
    return instance;
});