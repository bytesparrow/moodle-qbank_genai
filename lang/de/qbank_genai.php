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
 * Plugin strings are defined here.
 *
 * @package     qbank_genai
 * @category    string
 * @copyright   2023 Ruthy Salomon <ruthy.salomon@gmail.com> , Yedidia Klein <yedidia@openapp.co.il>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Grundlagen.
$string['aiquestions'] = 'KI-Fragen';
$string['pluginname'] = 'KI-Text-zu-Fragen-Generator';
$string['pluginname_desc'] = 'Dieses Plugin ermöglicht es, automatisch Fragen aus einem Text mithilfe einer Sprach-KI (z. B. ChatGPT) zu generieren.';
$string['pluginname_help'] = 'Verwenden Sie dieses Plugin im Kursverwaltungsmenü oder in der Fragensammlung.';
$string['privacy:metadata'] = 'Der KI-Text-zu-Fragen-Generator speichert keine personenbezogenen Daten.';
$string['gift_format'] = 'GIFT-Format';
$string['xml_format'] = 'Moodle-XML-Format';
$string['use_coursecontents'] = 'Stattdessen Kursinhalte als Thema verwenden';
$string['activitylist'] = 'Liste der Aktivitäten';

// Einstellungsseite.
$string['provider'] = 'GPT-Anbieter';
$string['providerdesc'] = 'Wählen Sie, ob Sie Azure oder OpenAI verwenden';
$string['azureapiendpoint'] = 'Azure-API-Endpunkt';
$string['azureapiendpointdesc'] = 'Geben Sie hier die URL des Azure-API-Endpunkts ein';
$string['openaikey'] = 'OpenAI- oder Azure-API-Schlüssel';
$string['openaikeydesc'] = 'Sie können einen OpenAI-API-Schlüssel von <a href="https://platform.openai.com/account/api-keys">https://platform.openai.com/account/api-keys</a> erhalten<br>
Klicken Sie auf die Schaltfläche "+ Create New Secret Key" und kopieren Sie den Schlüssel in dieses Feld.<br>
Beachten Sie, dass Sie ein OpenAI-Konto mit aktivierter Abrechnung benötigen, um einen API-Schlüssel zu erhalten.';
$string['model'] = 'Modell';
$string['model_desc'] = 'Zu verwendendes Sprachmodell. <a href="https://platform.openai.com/docs/models/">Weitere Informationen</a>.';
$string['presetname'] = 'Name der Voreinstellung';
$string['presetnamedesc'] = 'Name, der den Nutzern angezeigt wird';
$string['presetprimer'] = 'Voreingabe';
$string['presetinstructions'] = 'Voreingestellte Anweisungen';
$string['presetexample'] = 'Beispiel für die Voreinstellung';
$string['presetformat'] = 'Format der Voreinstellung';
$string['presetformatdesc'] = 'Wählen Sie das Format des Beispiels, das vom LLM zurückgegeben werden soll';
$string['numoftries'] = '<b>{$a}</b> Versuche';
$string['numoftriesset'] = 'Anzahl der Versuche';
$string['numoftriesdesc'] = 'Anzahl der Versuche, die an OpenAI gesendet werden';
$string['presets'] = 'Voreinstellungen';
$string['presetsdesc'] = 'Sie können bis zu 10 Voreinstellungen angeben, die in den Kursen auswählbar sind. Benutzer können die Voreinstellungen weiterhin vor dem Senden an OpenAI bearbeiten.';
$string['preset'] = 'Voreinstellung';
$string['shareyourprompts'] = 'Weitere Ideen für Prompts oder eigene Beiträge finden Sie auf der <a target="_blank" href="https://docs.moodle.org/402/en/AI_Text_to_questions_generator">Moodle-Dokumentationsseite zu diesem Plugin</a>.';

// Formular zur Themenangabe.
$string['category'] = 'Fragenkategorie';
$string['category_help'] = 'Falls keine Kategorie angezeigt wird, öffnen Sie die Fragensammlung dieses Kurses einmal.';
$string['addidentifier'] = '„GPT-erzeugt: “-Präfix zum Fragetitel hinzufügen';
$string['editpreset'] = 'Voreinstellung vor dem Senden an die KI bearbeiten';
$string['primer'] = 'Eingabe';
$string['primer_help'] = 'Die Eingabe ist die erste Information, die an die KI gesendet wird, um sie auf die Aufgabe vorzubereiten.';
$string['instructions'] = 'Anweisungen';
$string['instructions_help'] = 'Die Anweisungen sagen der KI, was sie tun soll.';
$string['example'] = 'Beispiel';
$string['example_help'] = 'Das Beispiel zeigt der KI ein Ausgabeformat zur Orientierung.';
$string['story'] = 'Thema';
$string['story_help'] = 'Das Thema Ihrer Fragen. Sie können auch ganze Artikel einfügen, z. B. von Wikipedia.';
$string['numofquestions'] = 'Anzahl der zu generierenden Fragen';
$string['generate'] = 'Fragen generieren';
$string['backtocourse'] = 'Zurück zum Kurs';

// Ergebnisse.
$string['gotoquestionbank'] = 'Zur Fragensammlung';
$string['generatemore'] = 'Weitere Fragen generieren';
$string['createdquestionwithid'] = 'Frage mit der ID erstellt: ';
$string['tasksuccess'] = 'Die Aufgaben zur Fragenerstellung wurden erfolgreich erstellt';
$string['generating'] = 'Fragen werden generiert ... (Sie können diese Seite sicher verlassen und später in der Fragensammlung nachsehen)';
$string['generationfailed'] = 'Die Fragenerstellung ist nach {$a} Versuchen fehlgeschlagen';
$string['generationtries'] = 'Anzahl der an OpenAI gesendeten Versuche: <b>{$a}</b>';
$string['outof'] = 'von';
$string['preview'] = 'Frage in neuem Tab anzeigen';
$string['cronoverdue'] = 'Der Cron-Task scheint nicht zu laufen.
Die Fragenerstellung basiert auf Ad-hoc-Aufgaben, die vom Cron erstellt werden. Bitte überprüfen Sie Ihre Cron-Einstellungen.
Siehe <a href="https://docs.moodle.org/en/Cron#Setting_up_cron_on_your_system">
https://docs.moodle.org/en/Cron#Setting_up_cron_on_your_system
</a> für weitere Informationen.';
$string['createdquestionsuccess'] = 'Frage erfolgreich erstellt';
$string['createdquestionssuccess'] = 'Fragen erfolgreich erstellt';
$string['errornotcreated'] = 'Fehler: Fragen wurden nicht erstellt';

// Voreinstellungen (Beispiele).
$string['presetnamedefault1'] = "Multiple-Choice-Frage (Englisch)";
$string['presetprimerdefault1'] = "Sie sind ein hilfreicher Assistent eines Lehrers und erstellen Multiple-Choice-Fragen basierend auf den vom Benutzer angegebenen Themen.";
$string['presetinstructionsdefault1'] = "Bitte schreiben Sie eine Multiple-Choice-Frage auf Englisch im GIFT-Format zu einem Thema, das ich Ihnen separat mitteile. Im GIFT-Format wird die richtige Antwort mit Gleichheitszeichen und falsche mit Tilde eingeleitet. Beispiel: '::Fragetitel:: Fragetext { =richtige Antwort#Feedback ~falsche Antwort#Feedback ~falsche Antwort#Feedback ~falsche Antwort#Feedback }' Bitte eine Leerzeile zwischen den Fragen einfügen. Der Fragetitel soll nicht am Anfang des Fragetextes erscheinen.";
$string['presetexampledefault1'] = "::Indexikalität und Ikonizität 1:: Stell dir vor, du stehst an einem Seeufer. Der Wind kommt auf und erzeugt Wellen auf der Seeoberfläche. Laut C.S. Peirce, in welcher Weise zeigen die Wellen den Wind an? { =Die Beziehung ist sowohl indexikalisch als auch ikonisch.#Richtig. Es gibt eine raum-zeitliche Verbindung zwischen Wind und Wellen – ein Merkmal von Indexikalität. Außerdem besteht eine formale Ähnlichkeit zwischen Windrichtung und Wellenrichtung – ein Merkmal von Ikonizität. ~Die Beziehung ist indexikalisch.#Fast richtig. Es gibt zwar eine raum-zeitliche Verbindung, aber es liegt noch eine weitere Zeichenfunktion vor. ~Zwischen Wind und Wellen gibt es kein Zeichenphänomen, sie sind zwei getrennte Zeichen.#Falsch. Die Bewegung der Wellen wird vom Wind bestimmt. ~Die Beziehung ist symbolisch.#Falsch. Die Bewegung der Wellen ist nicht willkürlich, was bei Symbolen der Fall wäre. }";

$string['presetnamedefault2'] = '';
$string['presetprimerdefault2'] = '';
$string['presetinstructionsdefault2'] = '';
$string['presetexampledefault2'] = '';
$string['presetnamedefault3'] = '';
$string['presetprimerdefault3'] = '';
$string['presetinstructionsdefault3'] = '';
$string['presetexampledefault3'] = '';
$string['presetnamedefault4'] = '';
$string['presetprimerdefault4'] = '';
$string['presetinstructionsdefault4'] = '';
$string['presetexampledefault4'] = '';
$string['presetnamedefault5'] = '';
$string['presetprimerdefault5'] = '';
$string['presetinstructionsdefault5'] = '';
$string['presetexampledefault5'] = '';
$string['presetnamedefault6'] = '';
$string['presetprimerdefault6'] = '';
$string['presetinstructionsdefault6'] = '';
$string['presetexampledefault6'] = '';
$string['presetnamedefault7'] = '';
$string['presetprimerdefault7'] = '';
$string['presetinstructionsdefault7'] = '';
$string['presetexampledefault7'] = '';
$string['presetnamedefault8'] = '';
$string['presetprimerdefault8'] = '';
$string['presetinstructionsdefault8'] = '';
$string['presetexampledefault8'] = '';
$string['presetnamedefault9'] = '';
$string['presetprimerdefault9'] = '';
$string['presetinstructionsdefault9'] = '';
$string['presetexampledefault9'] = '';
$string['presetnamedefault10'] = '';
$string['presetprimerdefault10'] = '';
$string['presetinstructionsdefault10'] = '';
$string['presetexampledefault10'] = '';
