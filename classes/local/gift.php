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
 * Class to handle gift format.
 *
 * @package    qbank_genai
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qbank_genai\local;

#use qbank_genai\local\genai_qformat_gift;
require_once(__DIR__ . '/../../locallib.php');

/**
 * Class to handle gift format.
 *
 * @package    qbank_genai
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gift {
  /**
   * 
   *
   * @param int $categoryid
   * @param object $llmresponse
   * @param int $numofquestions
   * @param int $userid
   * @param int $genaiid
   * @param bool $addidentifier
   * @return false|object[]
   */

  /**
   * Parse the gift questions.
   * @global \qbank_genai\local\type $DB
   * @global \qbank_genai\local\type $CFG
   * @param int $categoryid
   * @param object $llmresponse
   * @param int $numofquestions
   * @param int $userid
   * @param bool $addidentifier
   * @param int $genaiid
   * @return bool|array 'status' => 'success', "message" => "somemessage, "imported" => array<StdClass> questions
   */
  public static function parse_questions(
    int $categoryid,
    object $llmresponse,
    int $numofquestions,
    int $userid,
    bool $addidentifier,
    int $genaiid
  ) {
    global $DB, $CFG;
    require_once($CFG->libdir . '/questionlib.php');
    require_once($CFG->dirroot . '/question/format.php');
    require_once($CFG->dirroot . '/question/format/gift/format.php');

    $qformat = new \qformat_gift();
    $allquestionstext = $llmresponse->text;
    $questions = explode("\n\n", $allquestionstext);

    //the llm sent garbage
    if (!strstr($allquestionstext, "::")) {
      //do a retry
      return false;
    }
    //count of questions doesn't meet requirement
    if (count($questions) != $numofquestions) {
      //do a retry
      return false;
    }

    $importresult = self::import_gift($allquestionstext, $categoryid, $addidentifier);

    return $importresult;
  }

  /**
   * new version of import
   * uses moodle's import feature
   * $courseid is calculated from $categoryid
   * @global type $USER
   * @global type $CFG
   * @global type $DB
   * @param type $gifttext
   * @param type $categoryid
   * @param type $addidentifier
   * @return array 'status' => 'success', "message" => "somemessage, "imported" => array<StdClass> questions
   * @throws \moodle_exception
   */
  public static function import_gift($gifttext, $categoryid, $addidentifier) {
    global $USER, $CFG, $DB;


    // Temporäre Datei mit GIFT-Inhalt erzeugen
    $tmpfile = \tempnam(\sys_get_temp_dir(), 'gift_');
    \file_put_contents($tmpfile, $gifttext);

    $genai_gift = new genai_qformat_gift(); // beachte die abgeleitete Klasse
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

    $category->context = $categorycontext;

    $genai_gift->setCategory($category);
    $genai_gift->setContexts($contexts->having_one_edit_tab_cap('import'));
    $genai_gift->setCourse($COURSE);
    $genai_gift->setFilename($tmpfile);
    $genai_gift->setRealfilename(basename($tmpfile));
    $genai_gift->setMatchgrades("nearest");
    $genai_gift->setCatfromfile(false);
    $genai_gift->setContextfromfile(false);
    $genai_gift->setStoponerror(false);


    ob_start();
    $importsuccess = $genai_gift->importprocess();
    $message = ob_get_clean();

    $cleanedmessage = str_replace(array('Systemnachricht schließen', '&times;'), '', strip_tags($message));

    // Import durchführen
    if (!$importsuccess) {
      \unlink($tmpfile);
      return array('status' => 'error', "message" => $cleanedmessage);
    }

    //that's else: success
    $imported_questions = self::get_recent_imported_questions($courseid, $categoryid);

    if (count($imported_questions)) {
      //change question name if $addidentifier is set
      if ($addidentifier) {
        foreach ($imported_questions as &$q) {
          $q->name = get_string('aicreatedtag', 'qbank_genai') . $q->name;
          $q->questiontext = get_string('aicreatedtag', 'qbank_genai').$q->questiontext;
          // Datenbankeintrag aktualisieren
          $record = new \stdClass();
          $record->id = $q->id;
          $record->name = $q->name;
          $record->questiontext = $q->questiontext;
          $DB->update_record('question', $record);
        }
      }
    }
    $return_ar = array('status' => 'success', "message" => $cleanedmessage);
    $return_ar["imported"] = $imported_questions;

    \unlink($tmpfile);
    return $return_ar;
  }

  public static function get_recent_imported_questions(int $courseid, int $categoryid): array {
    global $DB;

    $since = time() - 10;

    $sql = "
        SELECT q.id, q.name, q.qtype, q.questiontext, q.timecreated, q.timemodified
        FROM {question} q
        JOIN {question_versions} v ON v.questionid = q.id
        JOIN {question_bank_entries} e ON e.id = v.questionbankentryid
        JOIN {question_categories} c ON c.id = e.questioncategoryid
        WHERE c.id = :catid
          AND c.contextid = :contextid
          AND q.timemodified >= :since
        ORDER BY q.timemodified DESC
    ";

    // Kontext-ID für den Kurs (in Moodle 4.5 zwingend relevant)
    $context = \context_course::instance($courseid);

    $params = [
      'catid' => $categoryid,
      'contextid' => $context->id,
      'since' => $since,
    ];

    $records = $DB->get_records_sql($sql, $params);

    // Rückgabe als Array von StdClass-Objekten
    $result = [];
    foreach ($records as $q) {
      $obj = new \StdClass();
      $obj->id = $q->id;
      $obj->name = $q->name;
      $obj->qtype = $q->qtype;
      $obj->questiontext = $q->questiontext;
      $obj->created = userdate($q->timecreated);
      $obj->modified = userdate($q->timemodified);
      $result[] = $obj;
    }

    return $result;
  }

}
