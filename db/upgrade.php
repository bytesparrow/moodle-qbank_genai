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
 * Plugin upgrade steps are defined here.
 *
 * @package     qbank_genai
 * @category    upgrade
 * @copyright   2023 Ruthy Salomon <ruthy.salomon@gmail.com> , Yedidia Klein <yedidia@openapp.co.il>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/upgradelib.php');

/**
 * Execute qbank_genai upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_qbank_genai_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2024090400) {

        // Define index test (not unique) to be dropped form qbank_genai.
        $table = new xmldb_table('qbank_genai');
        $index = new xmldb_index('courseid', XMLDB_INDEX_NOTUNIQUE, ['courseid']);

        // Conditionally launch drop index test.
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        // Define field numoftries to be dropped from qbank_genai.
        $field = new xmldb_field('courseid');

        // Conditionally launch drop field numoftries.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Genai savepoint reached.
        upgrade_plugin_savepoint(true, 2024090400, 'qbank', 'genai');
    }

    if ($oldversion < 2024090401) {

        // Rename field gift on table qbank_genai to llmresponse.
        $table = new xmldb_table('qbank_genai');
        $field = new xmldb_field('gift', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null, 'userid');

        // Launch rename field gift.
        $dbman->rename_field($table, $field, 'llmresponse');

        // Now add a new database field.
        $field = new xmldb_field('qformat', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'id');

        // Conditionally launch add field qformat.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Genai savepoint reached.
        upgrade_plugin_savepoint(true, 2024090401, 'qbank', 'genai');
    }

    if ($oldversion < 2024090402) {

        // Define field primer to be added to qbank_genai.
        $table = new xmldb_table('qbank_genai');
        $field = new xmldb_field('primer', XMLDB_TYPE_TEXT, null, null, null, null, null, 'uniqid');

        // Conditionally launch add field primer.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('instructions', XMLDB_TYPE_TEXT, null, null, null, null, null, 'primer');

        // Conditionally launch add field instructions.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('example', XMLDB_TYPE_TEXT, null, null, null, null, null, 'instructions');

        // Conditionally launch add field example.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Genai savepoint reached.
        upgrade_plugin_savepoint(true, 2024090402, 'qbank', 'genai');
    }

    if ($oldversion < 2024090403) {

        // Define field numofquestions to be added to qbank_genai.
        $table = new xmldb_table('qbank_genai');

        $field = new xmldb_field('numofquestions', XMLDB_TYPE_INTEGER, '5', null, null, null, null, 'qformat');
        // Conditionally launch add field numofquestions.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('aiidentifier', XMLDB_TYPE_INTEGER, '1', null, null, null, null, 'timemodified');
        // Conditionally launch add field aiidentifier.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('story', XMLDB_TYPE_TEXT, null, null, null, null, null, 'numofquestions');
        // Conditionally launch add field story.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('category', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'numofquestions');
        // Conditionally launch add field category.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Genai savepoint reached.
        upgrade_plugin_savepoint(true, 2024090403, 'qbank', 'genai');
    }
    if ($oldversion < 2024090404) {
      
        $table = new xmldb_table('qbank_genai');
        $field = new xmldb_field('createdquestions', XMLDB_TYPE_TEXT, null, null, null, null, null, 'success');
        // Conditionally launch add field story.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
 

        // Genai savepoint reached.
        upgrade_plugin_savepoint(true, 2024090404, 'qbank', 'genai');
    }
    



    return true;
}
