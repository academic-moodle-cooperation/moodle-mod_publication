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
 * JS showing detailed infos about user's approval status for group approvals in a modal window
 *
 * @package       mod_publication
 * @author        Philipp Hager
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module mod_publication/groupapprovalstatus
 */
define(['jquery', 'core/modal_factory', 'core/str', 'core/templates', 'core/log'], function($, ModalFactory, str, templates, log) {

    /**
     * @constructor
     * @alias module:mod_publication/groupapprovalstatus
     */
    var Groupapprovalstatus = function() {
        this.id = '';
    };

    var instance = new Groupapprovalstatus();

    /**
     * Initialises the JavaScript for publication's group approval status tooltips
     *
     *
     * @param {Object} config The configuration
     */
    instance.initializer = function(config) {
        instance.id = config.id;
        instance.mode = config.mode;

        log.info('Initialize groupapprovalstatus JS!', 'mod_publication');

        // Prepare modal object!
        if (!instance.modal) {
            instance.modalpromise = ModalFactory.create({
                type: ModalFactory.types.DEFAULT,
                body: '...'
            });
        }

        str.get_string('filedetails', 'mod_publication').done(function(s) {
            log.info('Done loading strings...', 'mod_publication');
            instance.modalpromise.done(function(modal) {
                log.info('Done preparing modal', 'mod_publication');
                instance.modal = modal;
                $('.path-mod-publication .statustable .approvaldetails *').click(function(e) {
                    e.stopPropagation();
                    var element = $(e.target);

                    var dataelement = element.parent();

                    var approved;
                    try {
                        approved = dataelement.data('approved');
                    } catch (ex) {
                        approved = [];
                    }

                    var rejected;
                    try {
                        rejected = dataelement.data('rejected');
                    } catch (ex) {
                        rejected = [];
                    }

                    var pending;
                    try {
                        pending = dataelement.data('pending');
                    } catch (ex) {
                        pending = [];
                    }

                    var filename;
                    try {
                        filename = s + ' ' + dataelement.data('filename');
                    } catch (ex) {
                        filename = s;
                    }

                    var stat;
                    try {
                        stat = dataelement.data('status');
                    } catch (ex) {
                        stat = {
                            approved: false,
                            rejected: false,
                            pending: false
                        };
                    }

                    var context = {
                        id: instance.id,
                        mode: instance.mode,
                        status: stat,
                        approved: approved,
                        rejected: rejected,
                        pending: pending
                    };

                    // This will call the function to load and render our template.
                    var promise = templates.render('mod_publication/approvaltooltip', context);

                    // How we deal with promise objects is by adding callbacks.
                    promise.done(function(source) {
                        // Here eventually I have my compiled template, and any javascript that it generated.
                        instance.modal.setTitle(filename);
                        instance.modal.setBody(source);
                        instance.modal.show();
                    }).fail(function(ex) {
                        // Deal with this exception (I recommend core/notify exception function for this).
                        instance.modal.setBody(ex.message);
                        instance.modal.show();
                    });
                });
                // Everything is prepared, fade the symbols in!
                $('.path-mod-publication .statustable .approvaldetails').fadeIn('slow');
            });
        }).fail(function(ex) {
            log.error('Error getting strings: ' + ex, 'mod_publication');
        });
    };

    return instance;
});
