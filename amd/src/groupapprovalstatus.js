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
 * groupapprovalstatus.js
 *
 * @package       mod_publication
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Philipp Hager
 * @copyright     2016 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 /**
  * @module mod_publication/groupapprovalstatus
  */
define(['jquery', 'core/yui', 'core/str', 'core/templates', 'core/log'], function($, Y, str, templates, log) {

    /**
     * @constructor
     * @alias module:mod_publication/groupapprovalstatus
     */
    var Groupapprovalstatus = function() {
        this.id = '';

        this.contextid = 0;

        this.panel = null;
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

        str.get_string('filedetails', 'mod_publication').done(function(s) {
            log.info('Done loading strings...', 'mod_publication');

            // Get group id!
            /*
             * In the next stable moodle version we will use a moodle AMD module
             * allowing us to call the popup without YUI in a standard way.
             *
             * Until then we're wrapping some YUI and start transitioning
             * Moodle-style (see also lib/amd/notification.js)
             */
            // Here we are wrapping YUI. This allows us to start transitioning, but
            // wait for a good alternative without having inconsistent dialogues.

            $( ".path-mod-publication .statustable .approvaldetails *").click(function(e) {
                e.stopPropagation();
                var element = $( e.target );

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
                    stat = { approved: false, rejected: false, pending: false };
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
                    if (!instance.panel) {
                        Y.use('moodle-core-info', function () {
                            instance.panel = new M.core.notification.info({});
                            // Here eventually I have my compiled template, and any javascript that it generated.
                            instance.panel.setStdModContent('header', filename);
                            instance.panel.setStdModContent('body', source);
                            instance.panel.show();
                        });
                    } else {
                        // Here eventually I have my compiled template, and any javascript that it generated.
                        instance.panel.setStdModContent('header', filename);
                        instance.panel.setStdModContent('body', source);
                        instance.panel.show();
                    }

                }).fail(function(ex) {
                    if (!instance.panel) {
                        Y.use('moodle-core-info', function () {
                            instance.panel = new M.core.notification.info({});
                            // Deal with this exception (I recommend core/notify exception function for this).
                            instance.panel.setStdModContent('body', ex.message);
                            instance.panel.show();
                        });
                    } else {
                        // Deal with this exception (I recommend core/notify exception function for this).
                        instance.panel.setStdModContent('body', ex.message);
                        instance.panel.show();
                    }
                });
            });
            // Everything is prepared, fade the symbols in!
            $( ".path-mod-publication .statustable .approvaldetails").fadeIn('slow');
        }).fail(function(ex) {
            log.error("Error getting strings: " + ex, "mod_publication");
        });
    };

    return instance;
});