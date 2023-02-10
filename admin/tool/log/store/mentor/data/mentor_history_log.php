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
 * Content of mentor_history_log table
 *
 * @package    logstore_mentor
 * @copyright  2021 Edunao SAS (contact@edunao.com)
 * @author     adrien <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../../../../config.php');

require_login();

if (!is_siteadmin()) {
    print_error('Permission denied');
}

echo '<a href="' . $CFG->wwwroot . '/admin/tool/log/store/mentor/database.php">Retour</a>';

echo '<h1>Table logstore_mentor_history_log</h1>';

$logs = $DB->get_records('logstore_mentor_history_log');

echo '<table border="1">';

echo '<thead>';
echo '<tr>';
echo '<th>id</th>';
echo '<th>userlogid</th>';
echo '<th>sessionlogid</th>';
echo '<th>timecreated</th>';
echo '<th>lastview</th>';
echo '<th>numberview</th>';
echo '<th>completed</th>';
echo '</tr>';
echo '</thead>';

echo '<tbody>';
foreach ($logs as $log) {
    echo '<tr>';
    echo '<td>' . $log->id . '</td>';
    echo '<td>' . $log->userlogid . '</td>';
    echo '<td>' . $log->sessionlogid . '</td>';
    echo '<td>' . userdate($log->timecreated) . '</td>';
    echo '<td>' . userdate($log->lastview) . '</td>';
    echo '<td>' . $log->numberview . '</td>';
    echo '<td>' . $log->completed . '</td>';
    echo '</tr>';
}
echo '</tbody>';

echo '</table>';
