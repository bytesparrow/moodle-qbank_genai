<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin administration pages are defined here.
 *
 * @package     qbank_genai
 * @category    admin
 * @copyright   2023 Ruthy Salomon <ruthy.salomon@gmail.com> , Yedidia Klein <yedidia@openapp.co.il>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require $_SERVER ["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR . 'config.php';
require_once($CFG->dirroot . '/question/editlib.php');

defined('MOODLE_INTERNAL') || die();

core_question\local\bank\helper::require_plugin_enabled('qbank_genai');


// courseid als verpflichtenden Parameter vom Typ INTEGER holen
$courseid = required_param('courseid', PARAM_INT);
// Kursdaten abrufen
$course = get_course($courseid);
// Nutzerberechtigungen prüfen (optional, aber empfohlen)
require_login($course);


list($thispageurl, $contexts, $cmid, $cm, $module, $pagevars) = question_edit_setup('import', '/question/bank/genai/story.php');

list($catid, $catcontext) = explode(',', $pagevars['cat']);
if (!$qbankcategory = $DB->get_record("question_categories", ['id' => $catid])) {
    throw new moodle_exception('nocategory', 'question');
}

$categorycontext = context::instance_by_id($qbankcategory->contextid);
$qbankcategory->context = $categorycontext;

// This page can be called without courseid in which case.
// We get the context from the category object.
/* code does not work, since page dies on  = question_edit_setup...
if ($contexts === null) { // Need to get the course from the chosen category.

    $contexts = new core_question\local\bank\question_edit_contexts($categorycontext);
    $thiscontext = $contexts->lowest();
    if ($thiscontext->contextlevel == CONTEXT_COURSE) {
        require_login($thiscontext->instanceid, false);
    } else if ($thiscontext->contextlevel == CONTEXT_MODULE) {
        list($module, $cm) = get_module_from_cmid($thiscontext->instanceid);
        require_login($cm->course, false, $cm);
    }
    $contexts->require_one_edit_tab_cap($edittab);
}*/

$PAGE->set_url($thispageurl);

require_once("$CFG->libdir/formslib.php");
require_once(__DIR__ . '/locallib.php');

// $PAGE->set_context(\context_system::instance());
$PAGE->set_heading(get_string('pluginname', 'qbank_genai'));
$PAGE->set_title(get_string('pluginname', 'qbank_genai'));
$PAGE->set_pagelayout('standard');
$PAGE->requires->js_call_amd('qbank_genai/state');

echo $OUTPUT->header();

// Print horizontal nav if needed.
$renderer = $PAGE->get_renderer('core_question', 'bank');

$qbankaction = new \core_question\output\qbank_action_menu($thispageurl);
echo $renderer->render($qbankaction);

$mform = new \qbank_genai\story_form(null, ['contexts' => $contexts, 'courseid' => $courseid]);

if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot . '/question/edit.php?courseid=' . $courseid);
} else if ($data = $mform->get_data()) {

    // Call the adhoc task.  
    $task = new \qbank_genai\task\questions();
    if ($task) {

        $uniqid = uniqid($USER->id, true);

        $preset = $data->preset;

        // Create the DB entry.
        $dbrecord = new \stdClass();
        // $dbrecord->course = $courseid;
        $dbrecord->numoftries = get_config('qbank_genai', 'numoftries');
        $dbrecord->numofquestions = $data->numofquestions;
        $dbrecord->aiidentifier = empty($data->addidentifier)?false:true;
        $dbrecord->category = $qbankcategory->id;
        $dbrecord->userid = $USER->id;
        $dbrecord->qformat = $data->presetformat;
        $dbrecord->timecreated = time();
        $dbrecord->timemodified = 0;
        $dbrecord->tries = 0;
        $dbrecord->story = $data->story;
        $dbrecord->uniqid = $uniqid;
        $dbrecord->llmresponse = '';
        $dbrecord->success = '';
        $dbrecord->primer = $data->{'primer' . $preset};
        $dbrecord->instructions = $data->{'instructions' . $preset};
        $dbrecord->example = $data->{'example' . $preset};

        $inserted = $DB->insert_record('qbank_genai', $dbrecord);

        if ($inserted == 0) {
            throw new \moodle_exception('There was an error when storing the genai processing data to db.');
        }
        $dbrecord->id = $inserted;


        $task->set_custom_data([
            'genaiid' => $dbrecord->id,
            'uniqid' => $uniqid
        ]);
        \core\task\manager::queue_adhoc_task($task);
        $success = get_string('tasksuccess', 'qbank_genai');
    } else {
        $error = get_string('taskerror', 'qbank_genai');
    }
    // Check if the cron is overdue.
    $lastcron = get_config('tool_task', 'lastcronstart');
    $cronoverdue = ($lastcron < time() - 3600 * 24);

    // Prepare the data for the template.
    $datafortemplate = [
        'wwwroot' => $CFG->wwwroot,
        'uniqid' => $uniqid,
        'userid' => $USER->id,
        'cron' => $cronoverdue,
        'courseid' => $courseid
    ];
    // Load the ready template.
    echo $OUTPUT->render_from_template('qbank_genai/loading', $datafortemplate);
} else {
    $mform->display();
}

echo $OUTPUT->footer();
