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
 * Resets checked checkboxes after ZIP file was loaded!
 *
 * @module        mod_publication/filesform
 * @package
 * @author        Philipp Hager
 * @author        Hannes Laimer
 * @author        Andreas Windbichler
 * @copyright     2020 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
        this.form = $('#fastg');
        this.menuaction = $('#menuaction');
        this.usersel = $('.userselection');
        this.attemptstable = $('table#attempts');
    };

    var instance = new Filesform();

    instance.initializer = function() {
        log.info('Initialize filesform JS!', 'mod_publication');
        instance.form.on('submit', function() {
            if (instance.menuaction.val() === 'zipusers') {
                setTimeout(function() {
                    instance.usersel.prop('checked', false);
                }, 100);
            }
        });
        if (this.attemptstable.length > 0) {
            var $rows = this.attemptstable.children('tbody').children('tr');
            var needsapprovalcount = 0;
            $rows.each(function() {
                var $this = $(this);
                var $checkbox = $this.find('.permissionstable select.custom-select');
                if ($checkbox.length > 0) {
                    $checkbox.each(function() {
                         var $c = $(this);
                         if ($c.val() === '') {
                             if (!$c.hasClass('needs-approval')) {
                                 $c.addClass('needs-approval');
                                 needsapprovalcount++;
                                 $c.after('<span class="needs-approval-asterisk">*</span>');
                             }
                         }
                    });
                }
            });
            if (needsapprovalcount > 0) {
                $('.needsapproval-legend').removeClass('d-none');
            }
        }

    };

    return instance;
});
