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

require_once(__DIR__ . '/../../locallib.php');

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
    $parsedquestions = false;
    $i = 1;
    $error = ''; // Error message.
    $update = new \stdClass();

    mtrace("[qbank_genai] Creating Questions with AI...\n");
    mtrace("[qbank_genai] Try $i of $numoftries...\n");

    while (!$parsedquestions && $i <= $numoftries) {



      // Get questions from AI API.
       $questions = \qbank_genai_get_questions($dbrecord);
      #//debug

     /*  $questions = new \stdClass();
      $questions->text = '::Adaptives Unterrichten und Lernerfolg:: Was ist laut der Studie von Beck et al. aus dem Jahr 2008 ein wesentlicher Faktor für verbesserte Lernergebnisse der Schüler:innen? { =Die adaptive Lehrkompetenz der Lehrkraft.#Richtig. Die Studie legt nahe, dass Lehrer:innen, die ihre Unterrichtsmethoden an die individuellen Bedürfnisse und Fähigkeiten der Schüler:innen anpassen, nachweislich bessere Lernergebnisse erzielen. ~Die Verwendung von offenen Unterrichtsmethoden.#Nicht ganz richtig. Offene Unterrichtsmethoden sind Teil der adaptiven Lehrkompetenz, aber nicht der einzige Faktor für verbesserte Lernergebnisse.  ~Ein hoher Grad an Individualisierung.#Teilweise richtig. Die Individualisierung ist ein wichtiger Aspekt der adaptiven Lehrkompetenz, aber sie ist nicht der einzige Faktor für verbesserte Lernergebnisse. ~Die Anwendung von differenzierten Unterrichtsangeboten.#Teilweise richtig. Differenzierte Unterrichtsangebote sind ein Teil der adaptiven Lehrkompetenz, aber sie sind nicht der einzige Faktor für verbesserte Lernergebnisse. }';
               $questions->text = '::Adaptives Unterrichten – Zuordnungsfrage::
        [html]Ordnen Sie die folgenden Aussagen zu "Adaptives Unterrichten" den passenden Definitionen oder Beschreibungen zu. Jede Aussage passt genau zu einer Definition oder Beschreibung.{
        =[moodle]Anpassung der Lernangebote an individuelle Lernvoraussetzungen -> Prinzip des adaptiven Unterrichtens
        =[moodle]Abwechslung zwischen gemeinsamen, individualisierenden, differenzierenden, offenen Unterrichtsangeboten -> Methode zur Umsetzung des adaptiven Unterrichtens
        =[moodle]Arbeiten in individuellem Tempo und Niveau -> Merkmal offener Lern- und Unterrichtsformen
        =[moodle]Eingehen auf Bedürfnisse eines jeden einzelnen Kindes -> Ziel des adaptiven Unterrichtens
        =[moodle]Bessere Lernergebnisse durch adaptive Lehrkompetenz -> Erkenntnis aus der Studie nach Beck et al. 2008
        ####Das adaptive Unterrichten zielt darauf ab, die Lernangebote an die individuellen Lernvoraussetzungen der einzelnen Schüler:innen anzupassen. Dabei können verschiedene Unterrichtsformate wie gemeinsame, individualisierende, differenzierende oder offene Lernangebote zum Einsatz kommen. Offene Lern- und Unterrichtsformen erlauben es den Kindern, in ihrem eigenen Tempo und auf ihrem Niveau zu arbeiten. Im Zentrum steht immer das Eingehen auf die Bedürfnisse eines jeden einzelnen Kindes. Untersuchungen, wie die von Beck et al. 2008, belegen, dass adaptive Lehrkompetenz zu besseren Lernergebnissen führt.
        }'; 
      $questions->text = '::Adaptives Unterrichten 1:: Was bedeutet adaptives Unterrichten? { =Die Lernangebote werden an die individuellen Lernvoraussetzungen angepasst.#Richtig, adaptives Unterrichten zielt darauf ab, den Unterricht an die individuellen Bedürfnisse und Fähigkeiten der Schüler anzupassen. ~Adaptives Unterrichten bedeutet, alle Schüler mit dem gleichen Material zu unterrichten.#Leider falsch, adaptives Unterrichten ist vielmehr eine Methode, die auf die individuellen Lernbedürfnisse der Schüler eingeht. ~Adaptives Unterrichten hat nichts mit dem Anpassen von Lehrmaterial zu tun.#Das ist nicht korrekt, adaptives Unterrichten erfordert das Anpassen der Lernangebote an die unterschiedlichen Bedürfnisse und Fähigkeiten der Schüler. ~Adaptives Unterrichten ist nur für Schüler mit Lernschwierigkeiten relevant.#Falsch. Adaptives Unterrichten ist für alle Schüler relevant. }

::Adaptives Unterrichten 2:: Wie lässt sich adaptives Unterrichten umsetzen? {  =Durch den Wechsel von gemeinsamen, individualisierenden, differenzierenden, offenen Unterrichtsangeboten je nach Bedarf.#Richtig, das ist eine wichtige Methode zur Umsetzung von adaptivem Unterrichten. ~Durch das Anbieten des gleichen Unterrichtsmaterials für alle Schüler.#Falsch, adaptives Unterrichten bedeutet den Unterricht an die individuellen Lernbedürfnisse der Schüler anzupassen. ~Durch das Ignorieren der individuellen Lernbedürfnisse der Schüler.#Das ist nicht korrekt, adaptives Unterrichten zielt darauf ab, die individuellen Lernbedürfnisse der Schüler zu berücksichtigen. ~Durch das Durchführen von Massenprüfungen, anstatt individuellen Prüfungen.#Falsch, adaptives Unterrichten betreibt Differenzierung und Individualisierung.}

::Adaptives Unterrichten 3:: Was erreicht man durch offene Lern- und Unterrichtsformen im Kontext von adaptivem Unterrichten? { =Das Kind kann in seinem Tempo und auf seinem Niveau arbeiten.#Richtig. Offene Lern- und Unterrichtsformen ermöglichen es den Schülern, in ihrem eigenen Tempo zu lernen und auf ihrem Niveau zu arbeiten. ~Alle Schüler werden auf die gleiche Weise unterrichtet.#Falsch. Offene Lern- und Unterrichtsformen zielen darauf ab, dass jedes Kind in seinem Tempo und auf seinem Niveau arbeiten kann. ~Die Schüler werden gezwungen, im gleichen Tempo zu lernen.#Falsch. Offene Lern- und Unterrichtsformen ermöglichen individuelles Lernen. ~Es wird keine Berücksichtigung der individuellen Bedürfnisse der Schüler vorgenommen.#Falsch. Offene Lernformen erlauben es, auf die einzelnen Schüler und deren Bedürfnisse einzugehen. }

::Adaptives Unterrichten 4:: Was ist ein nachgewiesener Vorteil adaptiven Unterrichtens laut der Studie von Beck et al. 2008? { =Es führt nachweislich zu besseren Lernergebnissen auf Seiten der Schüler:innen.#Richtig. Die Studie von Beck et al. (2008) hat gezeigt, dass adaptives Unterrichten zu besseren Lernergebnissen bei den Schüler:innen führt. ~Auf der Grundlage der Studie von Beck et al. wurde keine Verbesserung der Lernergebnisse festgestellt.#Falsch, laut Beck et al. verbessert adaptives Unterrichten die Lernergebnisse. ~Die Studie von Beck et al. fand heraus, dass adaptives Unterrichten keinen Einfluss auf die Lernergebnisse hat.#Falsch, die Studie hat gezeigt, dass adaptives Unterrichten zu besseren Lernergebnissen führt. ~Die Studie von Beck et al. zeigte, dass adaptives Unterrichten die Lernergebnisse verschlechtert.#Falsch, die Studie zeigte, dass adaptives Unterrichten die Lernergebnisse verbessert. }
';
      #$questions->text= "prü+zlönö+jöd";*/
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
          $parsedquestions = \qbank_genai\local\gift::parse_questions(
              $dbrecord->category,
              $questions,
              $dbrecord->numofquestions,
              $dbrecord->userid,
              $dbrecord->aiidentifier,
              $dbrecord->id
          );
          break;

        case "xml":
          $parsedquestions = \qbank_genai\local\xml::parse_questions(
              $dbrecord->category,
              $questions,
              $dbrecord->numofquestions,
              $dbrecord->userid,
              $dbrecord->aiidentifier,
              $dbrecord->id
          );
          break;
      }
      $i++;
    }
    if ($parsedquestions) {
      $dbquestions = array();
      foreach ($parsedquestions as $pquestion) {
        $dbquestions[] = array("id" => $pquestion->id,
          "questiontext" => strip_tags($pquestion->name . ": " . $pquestion->questiontext));
      }
      $update = new \stdClass();
      $update->id = $genaiid;
      $update->success = 1;
      $update->createdquestions = json_encode($dbquestions);
      $DB->update_record('qbank_genai', $update);
    }
    // If questions were not created.
    if (!$parsedquestions) {
      // Insert error info to DB.
      $update = new \stdClass();
      $update->id = $genaiid;
      $update->tries = $i - 1;
      $update->timemodified = time();
      $update->success = '0';
      $DB->update_record('qbank_genai', $update);
    }

    // Print error message.
    // It will be shown on cron/adhoc output (file/whatever).
    if ($error != '') {
      echo '[qbank_genai adhoc_task]' . $error;
    }
  }

}
