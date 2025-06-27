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
 * @author     ByteSparrow <moodle@bytesparrow.dee>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qbank_genai\local;

#use qbank_genai\local\genai_qformat_gift;
require_once(__DIR__ . '/../../locallib.php');

abstract class generalimporter {

  /**
   * get recently created questions
   * process them ($addidentifier)
   * and return them
   * @param type $courseid
   * @param type $categoryid
   * @param type $addidentifier
   * @return type
   */
  public static function process_recently_imported_questions(int $courseid, int $categoryid, bool $addidentifier): array {
    global $DB;

    $imported_questions = self::get_recent_imported_questions($courseid, $categoryid);
    mtrace("[qbank_genai] got ".count($imported_questions)." questions...\n");
    if (count($imported_questions)) {
      //change question name if $addidentifier is set
      if ($addidentifier) {
        foreach ($imported_questions as &$q) {
          $q->name = get_string('aicreatedtag', 'qbank_genai') . $q->name;
          $q->questiontext = get_string('aicreatedtag', 'qbank_genai') . $q->questiontext;
          // Datenbankeintrag aktualisieren
          $record = new \stdClass();
          $record->id = $q->id;
          $record->name = $q->name;
          $record->questiontext = $q->questiontext;
          $DB->update_record('question', $record);
        }
      }
    }
    return $imported_questions;
  }

  /**
   * get questions that were created within this course and category within the last 10 seconds
   * @global type $DB
   * @param int $courseid
   * @param int $categoryid
   * @return array<StdClass> questions
   */
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
          AND q.parent = 0
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
