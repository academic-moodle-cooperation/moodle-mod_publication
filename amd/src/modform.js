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
 * Mod form supplement.
 *
 * @module    mod_publication/modform
 * @package
 * @author    Simeon Naydenov (moninaydenov@gmail.com)
 * @copyright 2024 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery'], function($) {
    const FITEM_APPROVALFROM = '#fitem_id_approvalfromdate';
    const FITEM_APPROVALTO = '#fitem_id_approvaltodate';
    const FITEM_OBTAINSTUDENTAPPROVAL = '#fitem_id_obtainstudentapproval';
    const FITEM_OBTAINGROUPAPPROVAL = '#fitem_id_obtaingroupapproval';
    const SELECT_OBTAINSTUDENTAPPROVAL = '#id_obtainstudentapproval';
    const SELECT_OBTAINGROUPAPPROVAL = '#id_obtaingroupapproval';
    const GROUP_AUTOMATIC = '-1';
    const STUDENT_AUTOMATIC = '0';

    const $approvalFromDate = $(FITEM_APPROVALFROM);
    const $approvalToDate = $(FITEM_APPROVALTO);
    const $obtainStudentApproval = $(FITEM_OBTAINSTUDENTAPPROVAL);
    const $obtainGroupApproval = $(FITEM_OBTAINGROUPAPPROVAL);
    const $selectObtainStudentApproval = $(SELECT_OBTAINSTUDENTAPPROVAL);
    const $selectObtainGroupApproval = $(SELECT_OBTAINGROUPAPPROVAL);

    const changeObtainStudentApproval = function() {
        let isAutomatic = false;
        if ($obtainStudentApproval.attr('hidden') !== 'hidden') {
            isAutomatic = $selectObtainStudentApproval.val() === STUDENT_AUTOMATIC;
        } else if ($obtainGroupApproval.attr('hidden') !== 'hidden') {
            isAutomatic = $selectObtainGroupApproval.val() === GROUP_AUTOMATIC;
        }
        if (isAutomatic) {
            $approvalFromDate.attr('hidden', 'hidden').css('display', 'none');
            $approvalToDate.attr('hidden', 'hidden').css('display', 'none');
        } else {
            $approvalFromDate.removeAttr('hidden').css('display', 'flex');
            $approvalToDate.removeAttr('hidden').css('display', 'flex');
        }

    };
    $selectObtainStudentApproval.on('change', changeObtainStudentApproval);
    $selectObtainGroupApproval.on('change', changeObtainStudentApproval);
    changeObtainStudentApproval();
});