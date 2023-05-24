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
 * Data recovery
 * Put back the links between users profile and their registrations to the entity cohort.
 * (to launch at the root of the project)
 *
 * @package    local_mentor_spcialization
 * @copyright  2023 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This script can only be called by CLI.
define('CLI_SCRIPT', true);

require_once(__DIR__ . '/config.php');
require_once($CFG->dirroot . '/local/mentor_core/api/entity.php');

// Set admin user.
$admin = get_admin();
\core\session\manager::set_user($admin);

// Get all entities.
$allmainentity = \local_mentor_core\entity_api::get_all_entities(true, [], true, null, false);

foreach ($allmainentity as $mainentity) {
    check_consistency_cohort_members_and_profiles_sync_link_user_to_cohort_by_entity($mainentity);
}

/**
 * Put back the links between users profile and their registrations to the entity cohort.
 *
 * @param \local_mentor_specialization\mentor_entity $entity
 * @return void
 * @throws dml_exception
 */
function check_consistency_cohort_members_and_profiles_sync_link_user_to_cohort_by_entity($entity) {
    // Database interface.
    $dbi = \local_mentor_specialization\database_interface::get_instance();

    // Get all users with this entity to main entity of his profile.
    $entityusers = \local_mentor_core\profile_api::get_users_by_mainentity($entity->name);

    // Get all users with this entity to secondary entity of his profile.
    $secondaryentityusers = check_consistency_cohort_members_and_profiles_get_users_by_secondaryentity($entity->name);

    // Get all users having one of the regions of attachment of the entity.
    $options = $dbi->get_category_option($entity->id, 'regionid');
    $regions = $options && !empty($options->value) ? explode(',', $options->value) : [];
    $newusers = $dbi->get_users_by_regions($regions);

    // Merge all users.
    $users = $newusers + $entityusers + $secondaryentityusers;

    // Get entity cohort member.
    $userscohort = $dbi->get_cohort_members_by_cohort_id($entity->get_cohort()->id, 'active');

    // Difference between users linked with their profile and those registered in the cohort.
    // List of users to be registered in the cohort.
    $addtheseusers = array_diff(array_keys($users), array_keys($userscohort));

    check_consistency_cohort_members_and_profiles_cohort_add_members($entity->get_cohort()->id, $addtheseusers);
}

/**
 * Add cohort members
 *
 * @param int $cohortid
 * @param int[] $usersid
 * @return void
 */
function check_consistency_cohort_members_and_profiles_cohort_add_members($cohortid, $usersid) {
    global $DB;

    // List user is empty.
    if (empty($usersid)) {
        return;
    }

    $records = [];

    // Create all line to add to database.
    foreach ($usersid as $userid) {
        if ($DB->record_exists('cohort_members', array('cohortid' => $cohortid, 'userid' => $userid))) {
            // No duplicates!
            continue;
        }

        $record = new stdClass();
        $record->cohortid = $cohortid;
        $record->userid = $userid;
        $record->timeadded = time();
        $records[] = $record;
    }

    // Adds data at once.
    $DB->insert_records('cohort_members', $records);

    $cohort = $DB->get_record('cohort', array('id' => $cohortid), '*', MUST_EXIST);

    // Trigger the events of each addition in the cohort.
    foreach ($records as $record) {
        $event = \core\event\cohort_member_added::create(array(
            'context' => context::instance_by_id($cohort->contextid),
            'objectid' => $cohortid,
            'relateduserid' => $record->userid,
        ));
        $event->add_record_snapshot('cohort', $cohort);
        $event->trigger();
    }
}

/**
 * Get all users by secondaryentity
 *
 * @param string $secondaryentity
 * @return \stdClass[]
 * @throws \dml_exception
 */
function check_consistency_cohort_members_and_profiles_get_users_by_secondaryentity($secondaryentity) {
    global $DB;

    $users = [];

    $usersdata = $DB->get_records_sql('
            SELECT u.id, u.*, uid.data
            FROM {user} u
            JOIN {user_info_data} uid ON u.id = uid.userid
            JOIN {user_info_field} uif ON uif.id = uid.fieldid
            WHERE uif.shortname = :fieldname
            AND (' . $DB->sql_like('uid.data', ':data', false, false) . '
            OR uid.data = :data2)
        ', array(
        'fieldname' => 'secondaryentities',
        'data' => '%' . $DB->sql_like_escape($secondaryentity) . '%',
        'data2' => $secondaryentity
    ));

    foreach ($usersdata as $userdata) {
        $secondaryentities = explode(', ', $userdata->data);
        $key = array_search($secondaryentity, $secondaryentities);

        if ($key === false) {
            continue;
        }

        unset($userdata->data);
        $users[$userdata->id] = $userdata;
    }

    return $users;
}
