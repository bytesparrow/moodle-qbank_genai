// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Code for checking questions generation state.
 *
 * @package
 * @category    admin
 * @copyright   2023 Ruthy Salomon <ruthy.salomon@gmail.com> , Yedidia Klein <yedidia@openapp.co.il>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/str', 'core/templates'], function ($, Ajax, Str, Templates) {
    // Load the state of the questions generation every 20 seconds.
    var intervalId = setInterval(function () {
        checkState(intervalId);
    }, 10000);

    /**
     * Check the state of the questions generation.
     * @param {int} intervalId The interval id.
     * @return {void}
     * @example
     *  checkState(intervalId);
     */
    function checkState(intervalId) {
        if (document.getElementById("qbank_genai_userid") === null)
        {   //nothing to check, value does not exist in DOM
            return;
        }
        var userid = document.getElementById("qbank_genai_userid").textContent.trim();
        var uniqid = document.getElementById("qbank_genai_uniqid").textContent.trim();
        var courseid = document.getElementById("qbank_genai_courseid").textContent.trim();
        var promises = Ajax.call([{
                methodname: 'qbank_genai_check_state',
                args: {
                    userid: userid,
                    uniqid: uniqid
                }
            }]);
        promises[0].then(function (showSuccess) {
            // If Questions are ready, show success message.
            if (showSuccess[0].success != '') {
                if (showSuccess[0].success == "0") { //Error (probably question not created after n tries).
                    var error = showSuccess[0].tries;
                } else {
                    var error = '';
                }
                if (!error)
                {
                    var successmessage = JSON.parse(showSuccess[0].success);
                    if (Object.keys(successmessage).length == 1) {
                        var single = true;
                    } else {
                        var single = false;
                    }
                }


                Templates.render('qbank_genai/success', {success: successmessage,
                    courseid: courseid,
                    wwwroot: M.cfg.wwwroot,
                    error: error,
                    single: single}).then(function (html) {
                    $("#qbank_genai_success").html(html);
                });
                // Stop checking the state while questions are ready.
                clearInterval(intervalId);
            }
            var percent = 0;
            var numoftries = showSuccess[0].numoftries;
            var tries = showSuccess[0].tries;
            // Show info if exists.
            if (showSuccess[0].tries !== null) {
                // If the questions are ready, show 100%.
                if (showSuccess[0].success != '') {
                    percent = 100;
                } else {
                    percent = Math.round((showSuccess[0].tries / numoftries) * 100);
                }
                //"translate" the strings and add the vars
                Str.get_strings([
                {key: 'generationtries', component: 'qbank_genai', param: tries},
                {key: 'numoftries', component: 'qbank_genai', param: numoftries}
                ]).then(function (strings) { //now render the template
                    const [generationtriesStr, numoftriesStr] = strings;
                    Templates.render('qbank_genai/info', {
                        tries: tries,
                        numoftries: numoftries,
                        percent: percent,
                        generationtries_str: generationtriesStr,
                        numoftries_str: numoftriesStr,
                    }
                    ).then(function (html) {
                        $("#qbank_genai_info").html(html);
                    });
                });
            }
        });
    }
});
