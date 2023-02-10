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
 *
 *
 * @package    tool_adhoctasksqueue
 * @copyright  2022 Edunao SAS (contact@edunao.com)
 * @author     Julien Buabent <julien.buabent@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * QUESTIONS :
 * pourquoi ne pas utiliser une classe comme block_teachers.php ?
 *
 *
 * A FAIRE :
 * renderer
 * lib
 * template et js ?
 */

require_once(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('adhoctasksqueue');

$renderer = $PAGE->get_renderer('tool_adhoctasksqueue');

$PAGE->requires->strings_for_js([
    'pluginname',
    'customdatacontent',
    'customdatacontentnojson',
    'nocustomdata',
    'deletetask',
    'deletetaskquestion',
    'error',
    'deletetaskerror',
    'langfile'
], 'tool_adhoctasksqueue');

$PAGE->requires->js_call_amd('tool_adhoctasksqueue/tool_adhoctasksqueue', 'init');

echo $OUTPUT->header();
echo $renderer->adhoc_tasks_table();
echo $OUTPUT->footer();
