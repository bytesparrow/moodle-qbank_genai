<?php

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
 * Class to handle xml format.
 *
 * @package    qbank_genai
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qbank_genai\local;

/**
 * Class to handle xml format.
 *
 * @package    qbank_genai
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class xml {

  /**
   * Parse the xml questions.
   *
   * @param int $categoryid
   * @param object $llmresponse
   * @param int $numofquestions
   * @param int $userid
   * @param int $genaiid
   * @param bool $addidentifier
   * @return false|object[]
   */
  public static function parse_questions(
    int $categoryid,
    object $llmresponse,
    int $numofquestions,
    int $userid,
    bool $addidentifier,
    int $genaiid
  ) {
    #return;
    global $CFG, $DB;

    // Work out if this is an uploaded file.
    // Or one from the filesarea.

    $fileformat = 'xml';
    $filedir = make_request_directory();
    $realfilename = uniqid() . "." . $fileformat;
    $importfile = $filedir . '/' . $realfilename;
    $filecreated = file_put_contents($importfile, $llmresponse->text);

    if(!$filecreated)
    {
      return array('status' => 'error', "message" => "Could not create temp file ".$importfile);
    }
    // $realfilename = $importform->get_new_filename('newfile');
    // $importfile = make_request_directory() . "/{$realfilename}";
    // if (!$result = $importform->save_file('newfile', $importfile, true)) {
    //     throw new moodle_exception('uploadproblem');
    // }

    $formatfile = $CFG->dirroot . '/question/format/xml/format.php';
    if (!is_readable($formatfile)) {
      throw new \moodle_exception('formatnotfound', 'question', '', $fileformat);
    }
    require_once($formatfile);


    $classname = 'qformat_xml';
    $qformat = new $classname();

    $category = $DB->get_record("question_categories", ['id' => $categoryid]);
    $categorycontext = \context::instance_by_id($category->contextid);
    $category->context = $categorycontext;

    $contexts = new \core_question\local\bank\question_edit_contexts($categorycontext);

    $thiscontext = $contexts->lowest();
    if ($thiscontext->contextlevel == \CONTEXT_COURSE) {
      \require_login($thiscontext->instanceid, false);
    }
    else {
      throw new \moodle_exception('Context is not a course: ' . $thiscontext->instanceid);
    }
    $courseid = $thiscontext->instanceid;

    // Kontext ermitteln
    $context = \context_course::instance($courseid);
    // Zugriffsprüfung
    \require_capability('moodle/question:add', $context);
    $COURSE = $DB->get_record("course", ['id' => $courseid]);


    // Load data into class.
    $qformat->setCategory($category);
    $qformat->setContexts($contexts->having_one_edit_tab_cap('import'));

    $qformat->setFilename($importfile);
    $qformat->setRealfilename($realfilename);
    $qformat->setCourse($COURSE);
    $qformat->setMatchgrades("nearest");
    $qformat->setCatfromfile(false);
    $qformat->setContextfromfile(false);
    $qformat->setStoponerror(false);
    #$qformat->set_display_progress(true);
    mtrace("[qbank_genai] starting import...\n");
    ob_start();
    $importsuccess = $qformat->importprocess();
    $message = ob_get_clean();

    $cleanedmessage = str_replace(array('Systemnachricht schließen', '&times;'), '', strip_tags($message));

    mtrace("[qbank_genai] cleanedmessage is: $cleanedmessage...\n");
    // Import durchführen
    if (!$importsuccess) {
         \unlink($tmpfile);
      return array('status' => 'error', "message" => $cleanedmessage);
    }
    
    \unlink($tmpfile);
    $return_ar = array('status' => 'success', "message" => $cleanedmessage);
    $dummysucces = new \stdClass();
    $dummysucces->name = "DUMMY";
    $dummysucces->questiontext = "DUMMYquestion";
    $dummysucces->id = 3;

    $return_ar["imported"] = array($dummysucces);
    return $return_ar;

    // Log the import into this category.
    $eventparams = [
      'contextid' => $qformat->category->contextid,
      'other' => ['format' => $fileformat, 'categoryid' => $qformat->category->id],
    ];

    // --- End Adaption.

    $event = \core\event\questions_imported::create($eventparams);
    $event->trigger();
  }

}
