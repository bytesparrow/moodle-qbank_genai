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
 * Adhoc task for questions generation.
 *
 * @package     qbank_genai
 * @category    admin
 * @copyright   2023 Ruthy Salomon <ruthy.salomon@gmail.com> , Yedidia Klein <yedidia@openapp.co.il>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qbank_genai\task;

defined('MOODLE_INTERNAL') || die();

//important line! Random "class not found" errors without it!
require_once($CFG->dirroot . '/question/engine/bank.php');

/**
 * The question generator adhoc task.
 *
 * @package     qbank_genai
 * @category    admin
 */
class questions extends \core\task\adhoc_task {

  /** @var string identifier of gift qformat */
  const PARAM_GENAI_GIFT = 'gift';

  /** @var string identifier of xml qformat */
  const PARAM_GENAI_XML = 'moodlexml';

  /**
   * Execute the task.
   *
   * @return void
   */
  public function execute() {
    global $DB;
    // Read numoftries from settings.
    $numoftries = get_config('qbank_genai', 'numoftries');

    // Get the data from the task.
    $data = $this->get_custom_data();

    $genaiid = $data->genaiid;
    mtrace($genaiid);
    $dbrecord = $DB->get_record('qbank_genai', ['id' => $genaiid]);

    // If there is no record any more, we can drop this process silently. But normally this should not happen.
    if (empty($dbrecord)) {
      mtrace("There is no related db record.");
      return true;
    }

    // Create questions.
    $parsingresult = false;
    $i = 1;
    $update = new \stdClass();

    mtrace("[qbank_genai] Creating Questions with AI...\n");
    mtrace("[qbank_genai] Try $i of $numoftries...\n");

    while (!$parsingresult && $i <= $numoftries) {



      // Get questions from AI API.
      $questions = \qbank_genai_get_questions($dbrecord);
      
// now update DB on tries.
      $update->id = $genaiid;
      $update->tries = $i;
      $update->datemodified = time();

      $DB->update_record('qbank_genai', $update);

      $update->llmresponse = $questions->text;
      $update->datemodified = time();
      $DB->update_record('qbank_genai', $update);


      switch ($dbrecord->qformat) {
        case "gift":
          $parsingresult = \qbank_genai\local\gift::parse_questions(
              $dbrecord->category,
              $questions,
              $dbrecord->numofquestions,
              $dbrecord->userid,
              $dbrecord->aiidentifier,
              $dbrecord->id
          );
          break;

        case "moodlexml":
          $parsingresult = \qbank_genai\local\xml::parse_questions(
              $dbrecord->category,
              $questions,
              $dbrecord->numofquestions,
              $dbrecord->userid,
              $dbrecord->aiidentifier,
              $dbrecord->id
          );
          break;
        default:
          $parsingresult = array("status" => "error", "message" => "the format $dbrecord->qformat is not supported");
          break;
      }
      $i++;
    }
    $parsingsuccess = $parsingresult["status"] == "success";
    if ($parsingsuccess) {
      $dbquestions = array();
      foreach ($parsingresult["imported"] as $pquestion) {
        $dbquestions[] = array("id" => $pquestion->id,
          "questiontext" => strip_tags($pquestion->name . ": " . $pquestion->questiontext));
      }
      $update = new \stdClass();
      $update->id = $genaiid;
      $update->success = 1;
      $update->createdquestions = json_encode($dbquestions);
      $DB->update_record('qbank_genai', $update);
    }
    else {
      // Insert error info to DB.
      $update = new \stdClass();
      $update->id = $genaiid;
      $update->tries = $i - 1;
      $update->timemodified = time();
      $update->success = '0';
      $DB->update_record('qbank_genai', $update);

      // Print error message.
      // It will be shown on cron/adhoc output (file/whatever).
      echo '[qbank_genai adhoc_task]' . $parsingresult["message"];
    }
  }

}
