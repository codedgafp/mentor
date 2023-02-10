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
 * Database upgrades for the logstore_mentor.
 *
 * @package    logstore_mentor
 * @copyright  2021 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade the logstore_mentor database.
 *
 * @param int $oldversion The version number of the plugin that was installed.
 * @return boolean
 * @throws ddl_exception
 * @throws ddl_table_missing_exception
 */
function xmldb_logstore_mentor2_upgrade($oldversion) {
    global $CFG, $DB;
    require_once($CFG->dirroot . '/local/mentor_core/api/session.php');
    require_once($CFG->dirroot . '/local/mentor_core/api/profile.php');
    require_once($CFG->dirroot . '/admin/tool/log/store/mentor2/classes/model/user.php');
    require_once($CFG->dirroot . '/admin/tool/log/store/mentor2/classes/model/session.php');
    require_once($CFG->dirroot . '/admin/tool/log/store/mentor2/classes/model/collection.php');
    require_once($CFG->dirroot . '/admin/tool/log/store/mentor2/classes/model/region.php');
    require_once($CFG->dirroot . '/admin/tool/log/store/mentor2/classes/model/entity.php');
    require_once($CFG->dirroot . '/admin/tool/log/store/mentor2/classes/model/log.php');

    if ($oldversion < 2022081900) {
        raise_memory_limit(MEMORY_UNLIMITED);
        set_time_limit(0);

        logstore_mentor_2_clean_logs();
        logstore_mentor_2_migrate_logs('logstore_mentor_history_log');
        logstore_mentor_2_migrate_logs('logstore_mentor_log');
    }
    return true;
}

/**
 * Clean all logs
 *
 * @return void
 * @throws dml_exception
 */
function logstore_mentor_2_clean_logs() {
    global $DB;
    $log2tables = [
            'history_log2',
            'log2',
            'user2',
            'sesscoll2',
            'session2',
            'entityreg2',
            'entity2',
            'collection2',
            'region2',
    ];

    // Purge new logstore tables.
    foreach ($log2tables as $log2table) {
        $DB->execute('TRUNCATE TABLE {logstore_mentor_' . $log2table . '}');
    }
}

/**
 * Migrate logs
 *
 * @param $table
 * @return void
 * @throws ReflectionException
 * @throws dml_exception
 */
function logstore_mentor_2_migrate_logs($table) {
    global $DB;

    $oldlogs = $DB->get_records_sql('
            SELECT ml.*, c.id as courseid, u.id as userid, mu.trainer, ms.status as sessionstatus, ms.shared
            FROM {'.$table.'} ml
            JOIN {logstore_mentor_session} ms ON ml.sessionlogid = ms.id
            JOIN {session} s ON ms.sessionid = s.id
            JOIN {course} c ON s.courseshortname = c.shortname
            JOIN {logstore_mentor_user} mu ON ml.userlogid = mu.id
            JOIN {user} u ON mu.userid = u.id
            ORDER BY ml.timecreated ASC
        ');

    // Convert all logs from old logstore to the new logstore.
    foreach ($oldlogs as $oldlog) {

        $event = [
                'courseid' => $oldlog->courseid,
                'userid'   => $oldlog->userid
        ];

        // Get session object.
        $session = \local_mentor_core\session_api::get_session_by_course_id($event['courseid']);

        // Skip the event if it's not a session event.
        if (!$session) {
            continue;
        }

        // Get user's main entity.
        $mainentity = \local_mentor_core\profile_api::get_user_main_entity($event['userid']);

        // User main entity is not defined.
        if (!$mainentity) {
            continue;
        }

        // Get or create entity log.
        $data = [
                'entityid' => $mainentity->id,
                'name'     => $mainentity->name,
                'regions'  => $mainentity->regions
        ];

        $userentitylog   = new \logstore_mentor2\models\entity($data);
        $userentitylogid = $userentitylog->get_or_create_record('entity2');

        $userprofilefields = (array) profile_user_record($event['userid'], false);

        $userregionlog   = new \logstore_mentor2\models\region(['name' => $userprofilefields['region']]);
        $userregionlogid = $userregionlog->get_or_create_record('region2');

        // Create data to log store.
        $data = array(
                'userid'      => $event['userid'],
                'trainer'     => $oldlog->trainer,
                'entitylogid' => $userentitylogid,
                'status'      => $userprofilefields['status'],
                'category'    => $userprofilefields['category'],
                'department'  => $userprofilefields['department'],
                'regionlogid' => $userregionlogid,
        );

        // Get or create user log store record.
        $userlog   = new \logstore_mentor2\models\user($data);
        $userlogid = $userlog->get_or_create_record('user2');

        // Session sub entity.
        $sessionsubentity = $session->get_entity();
        $data             = [
                'entityid' => $sessionsubentity->id,
                'name'     => $sessionsubentity->name,
                'regions'  => $sessionsubentity->regions
        ];

        $sessionsubentitylog   = new \logstore_mentor2\models\entity($data);
        $sessionsubentitylogid = $sessionsubentitylog->get_or_create_record('entity2');

        // Session main entity.
        $sessionmainentity = $sessionsubentity->get_main_entity();
        $data              = [
                'entityid' => $sessionmainentity->id,
                'name'     => $sessionmainentity->name,
                'regions'  => $sessionmainentity->regions
        ];

        $sessionentitylog   = new \logstore_mentor2\models\entity($data);
        $sessionentitylogid = $sessionentitylog->get_or_create_record('entity2');

        // Training sub entity.
        $training = $session->get_training();

        $trainingsubentity = $training->get_entity();
        $data              = [
                'entityid' => $trainingsubentity->id,
                'name'     => $trainingsubentity->name,
                'regions'  => $trainingsubentity->regions
        ];

        $trainingsubentitylog   = new \logstore_mentor2\models\entity($data);
        $trainingsubentitylogid = $trainingsubentitylog->get_or_create_record('entity2');

        // Training main entity.
        $trainingmainentity = $trainingsubentity->get_main_entity();
        $data               = [
                'entityid' => $trainingmainentity->id,
                'name'     => $trainingmainentity->name,
                'regions'  => $trainingmainentity->regions
        ];

        $trainingentitylog   = new \logstore_mentor2\models\entity($data);
        $trainingentitylogid = $trainingentitylog->get_or_create_record('entity2');

        $data = array(
                'sessionid'              => $session->id,
                'shared'                 => $oldlog->shared,
                'status'                 => $oldlog->sessionstatus,
                'entitylogid'            => $sessionentitylogid,
                'subentitylogid'         => $sessionsubentitylogid,
                'trainingentitylogid'    => $trainingentitylogid,
                'trainingsubentitylogid' => $trainingsubentitylogid
        );

        // Get or create session log store record.
        $sessionlog = new \logstore_mentor2\models\session($data);

        $param        = ['collections' => explode(',', $training->collection)];
        $sessionlogid = $sessionlog->get_or_create_record('session2', $param);

        // Create final log.
        $finallogdata = array(
                'sessionlogid' => $sessionlogid,
                'userlogid'    => $userlogid,

            // Use old data here.
                'timecreated'  => $oldlog->timecreated,
                'lastview'     => $oldlog->lastview,
                'numberview'   => $oldlog->numberview
        );

        // Get or create log store record.
        $log = new \logstore_mentor2\models\log($finallogdata);
        $log->get_or_create_record('log2', $finallogdata);
    }
}
