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

require_once('../../../../../config.php');
require_once($CFG->dirroot . '/local/mentor_core/api/entity.php');
require_once($CFG->dirroot . '/local/mentor_core/api/session.php');

require_login();

if (!is_siteadmin()) {
    print_error('Permission denied');
}

$alllogs = $DB->get_records_sql('
    SELECT
        l.*,
        s.sessionid,
        s.entitylogid,
        s.subentitylogid,
        s.trainingentitylogid,
        s.trainingsubentitylogid,
        s.status,
        s.shared,
        u.userid,
        us.firstname,
        us.lastname,
        us.email,
        u.trainer,
        u.status as userstatus,
        u.category,
        u.department,
        u.entitylogid as userentitylogid,
        r.name as regionname
    FROM
        {logstore_mentor_log2} l
    JOIN {logstore_mentor_session2} s ON s.id = l.sessionlogid
    JOIN {session} sess ON s.sessionid = sess.id
    JOIN {course} c ON sess.courseshortname = c.shortname
    JOIN {logstore_mentor_user2} u ON u.id = l.userlogid
    JOIN {logstore_mentor_region2} r ON r.id = u.regionlogid
    JOIN {user} us ON us.id = u.userid
    ORDER BY lastview DESC
');

$output = '';

$output = '<table border="1" style="text-align: center;">';
$output .= '<thead>';
$output .= '<tr>';
$output .= '<th>Date premier log</th>';
$output .= '<th>Date dernier log</th>';
$output .= '<th>NB logs</th>';
$output .= '<th>ID session</th>';
$output .= '<th>Libellé session</th>';
$output .= '<th>Entité session</th>';
$output .= '<th>Sous-entité session</th>';
$output .= '<th>Régions de la session</th>';
$output .= '<th>Entité formation</th>';
$output .= '<th>Sous-entité formation</th>';
$output .= '<th>Régions de la formation</th>';
$output .= '<th>Status session</th>';
$output .= '<th>Session partagée</th>';
$output .= '<th>Collections</th>';
$output .= '<th>Id utilisateur</th>';
$output .= '<th>Nom / Prénom</th>';
$output .= '<th>Entité principale</th>';
$output .= '<th>Formateur</th>';
$output .= '<th>Status</th>';
$output .= '<th>Catégorie</th>';
$output .= '<th>Région</th>';
$output .= '<th>Département</th>';
$output .= '</tr>';
$output .= '</thead>';
$output .= '<tbody>';

foreach ($alllogs as $log) {

    // Entities.
    $userentity = $DB->get_record_sql('SELECT name FROM {logstore_mentor_entity2} WHERE id = :id',
        ['id' => $log->userentitylogid]);

    $sessionentity = $DB->get_record_sql('SELECT name FROM {logstore_mentor_entity2} WHERE id = :id', [
        'id' =>
            $log->entitylogid
    ]);

    $sessionsubentity = $DB->get_record_sql('SELECT name FROM {logstore_mentor_entity2} WHERE id = :id',
        ['id' => $log->subentitylogid]);

    $trainingentity = $DB->get_record_sql('SELECT name FROM {logstore_mentor_entity2} WHERE id = :id',
        ['id' => $log->trainingentitylogid]);

    $trainingsubentity = $DB->get_record_sql('SELECT name FROM {logstore_mentor_entity2} WHERE id = :id',
        ['id' => $log->trainingsubentitylogid]);

    $session = \local_mentor_core\session_api::get_session($log->sessionid);

    // Entity regions.
    $dbregions = $DB->get_records_sql('
        SELECT r.name
        FROM {logstore_mentor_region2} r
        JOIN {logstore_mentor_entityreg2} me ON r.id = me.regionlogid
        WHERE
            me.entitylogid = :entitylogid
    ', ['entitylogid' => $log->entitylogid]);

    $regs = [];
    foreach ($dbregions as $dbregion) {
        $regs[] = $dbregion->name;
    }

    // Training regions.
    $dbregions = $DB->get_records_sql('
        SELECT r.name
        FROM {logstore_mentor_region2} r
        JOIN {logstore_mentor_entityreg2} me ON r.id = me.regionlogid
        WHERE
            me.entitylogid = :entitylogid
    ', ['entitylogid' => $log->trainingentitylogid]);

    $trainingregs = [];
    foreach ($dbregions as $dbregion) {
        $trainingregs[] = $dbregion->name;
    }

    // Collections.
    $dbcollections = $DB->get_records_sql('
        SELECT c.name
        FROM {logstore_mentor_collection2} c
        JOIN
            {logstore_mentor_sesscoll2} msc ON c.id = collectionlogid
        WHERE
          msc.sessionlogid = :sessionlogid
    ', ['sessionlogid' => $log->sessionlogid]);

    $coll = [];
    foreach ($dbcollections as $dbcoll) {
        $coll[] = $dbcoll->name;
    }

    $output .= '<tr>';
    $output .= '<td>' . userdate($log->timecreated) . '</td>';
    $output .= '<td>' . userdate($log->lastview) . '</td>';
    $output .= '<td>' . $log->numberview . '</td>';
    $output .= '<td>' . $log->sessionid . '</td>';
    $output .= '<td>' . $session->fullname . '</td>';
    $output .= '<td>' . $sessionentity->name . '</td>';

    $sessionsubentity = $sessionentity->name == $sessionsubentity->name ? '-' : $sessionsubentity->name;
    $output .= '<td>' . $sessionsubentity . '</td>';

    $output .= '<td>' . implode(',', $regs) . '</td>';

    $output .= '<td>' . $trainingentity->name . '</td>';

    $trainingsubentity = $trainingentity->name == $trainingsubentity->name ? '-' : $trainingsubentity->name;
    $output .= '<td>' . $trainingsubentity . '</td>';
    $output .= '<td>' . implode(',', $trainingregs) . '</td>';

    $output .= '<td>' . $log->status . '</td>';
    $output .= '<td>' . $log->shared . '</td>';
    $output .= '<td>' . implode(',', $coll) . '</td>';
    $output .= '<td>' . $log->userid . '</td>';
    $output .= '<td>' . $log->lastname . ' ' . $log->firstname . '</td>';
    $output .= '<td>' . $userentity->name . '</td>';
    $output .= '<td>' . $log->trainer . '</td>';
    $output .= '<td>' . $log->userstatus . '</td>';
    $output .= '<td>' . $log->category . '</td>';
    $output .= '<td>' . $log->regionname . '</td>';
    $output .= '<td>' . $log->department . '</td>';
    $output .= '</tr>';
}
$output .= '</tbody>';
$output .= '</table>';

echo $output;
