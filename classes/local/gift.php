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
   * Parse the gift questions.
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
    global $DB, $CFG;
    require_once($CFG->libdir . '/questionlib.php');
    require_once($CFG->dirroot . '/question/format.php');
    require_once($CFG->dirroot . '/question/format/gift/format.php');

    $qformat = new \qformat_gift();
    $allquestionstext = $llmresponse->text;
    $questions = explode("\n\n", $allquestionstext);
      
    //ai hat Mist gemeldet
    if(!strstr($allquestionstext, "::"))
    {
      return false;
    }
    //gezählte Fragen passen nicht zur Anzahl
    if (count($questions) != $numofquestions) {
      return false;
    }

    $createdquestions = []; // Array of objects of created questions.
    foreach ($questions as $question) {

      /*     $singlequestion = explode("\n", $question);

        // Manipulating question text manually for question text field.
        $questiontext = explode('{', $singlequestion[0]);

        $questiontext = trim(preg_replace('/^.*::/', '', $questiontext[0]));

        $qtype = 'match';
        $q = $qformat->readquestion($singlequestion);

        // Check if question is valid.
        if (!$q) {
        return false;
        }
        $q->category = $categoryid;
        $q->createdby = $userid;
        $q->modifiedby = $userid;
        $q->timecreated = time();
        $q->timemodified = time();
        $q->questiontext = ['text' => "<p>" . $questiontext . "</p>"];
        $q->questiontextformat = 1;
        if ($addidentifier == 1) {
        $q->name = "AI-created: " . $q->name; // Adds a "watermark" to the question
        }
        $created = \question_bank::get_qtype($qtype)->save_question($q, $q);
       */
      
    }

      $importsuccess = self::import_gift($allquestionstext, $categoryid, $addidentifier);

      
      //todo: das created muss nach unten, da das jetzt ne andere logik ist
      $importsuccesful = $importsuccess['status'] == 'success';
      $returnvalue = null;
      
      if($importsuccesful)
      {
        $returnvalue = $importsuccess["imported"];
       
      } 
      return $returnvalue;
      
      $update = $DB->get_record('qbank_genai', ['id' => $genaiid]);
      if(!$importsuccesful)
      {
         $update->success = 0;
      }
      else
      {
        $update->success = 1;
         $returnvalue = $importsuccess["imported"];
      }
     //maybe reactivate:
      //would set tries+1 for each question $update->tries = $update->tries + 1;
     
      $update->datemodified = time();
      $DB->update_record('qbank_genai', $update);
      return $returnvalue;
      $createdquestions[] = $returnvalue;

    return $createdquestions;
  }

  /**
   * das geht tatsächlich is aber hänsslich. schön machen.
   * @global type $USER
   * @global type $CFG
   * @global type $DB
   * @param type $courseid
   * @param type $gifttext
   * @param type $categoryid
   * @return type
   * @throws \moodle_exception
   */
  public static function import_gift($gifttext, $categoryid, $addidentifier) {
    global $USER, $CFG, $DB;


    // Temporäre Datei mit GIFT-Inhalt erzeugen
    $tmpfile = \tempnam(\sys_get_temp_dir(), 'gift_');
    \file_put_contents($tmpfile, $gifttext);

    // Importklasse vorbereiten
    /*
      require_once($CFG->dirroot . '/question/format.php');
      require_once($CFG->dirroot . '/question/format/gift/format.php'); */

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

    //weitere settings im constructor
    $genai_gift->setCategory($category);
    $genai_gift->setContexts($contexts->having_one_edit_tab_cap('import'));
    $genai_gift->setCourse($COURSE);
    $genai_gift->setFilename($tmpfile);
    $genai_gift->setRealfilename(basename($tmpfile));
    $genai_gift->setMatchgrades("nearest");
    $genai_gift->setCatfromfile(false);
    $genai_gift->setContextfromfile(false);
    $genai_gift->setStoponerror(false);




    // Import durchführen
    if (!$genai_gift->importprocess()) {
      \unlink($tmpfile);
      return array('status' => 'error');
    }

    $imported_questions = self::get_recent_imported_questions($courseid, $categoryid);
    if (count($imported_questions)) {
      if ($addidentifier) {
        foreach ($imported_questions as &$q) {
          $q->name = 'AI-created: ' . $q->name;

          // Datenbankeintrag aktualisieren
          $record = new \stdClass();
          $record->id = $q->id;
          $record->name = $q->name;
          $DB->update_record('question', $record);
        }
      }
    }
    $return_ar = array('status' => 'success');
    $return_ar["imported"] = $imported_questions;
    
    \unlink($tmpfile);
    return $return_ar;

/*

    return $imported_questions;
    return [
      'status' => 'success',
      'imported' => \count($genai_gift->questions)
    ];
 */
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
