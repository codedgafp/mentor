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
 * Data provider.
 *
 * @package    logstore_mentor
 * @copyright  2021 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace logstore_mentor\privacy;

use context;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\contextlist;

/**
 * Data provider class.
 *
 * @package    logstore_edunao
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
        \core_privacy\local\metadata\provider,
        \tool_log\local\privacy\logstore_provider,
        \tool_log\local\privacy\logstore_userlist_provider {

    use \tool_log\local\privacy\moodle_database_export_and_delete;

    /**
     * Returns metadata.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('logstore_mentor_user', [
                'userid'  => 'privacy:metadata:loguser:userid',
                'entity'  => 'privacy:metadata:loguser:entity',
                'trainer' => 'privacy:metadata:loguser:trainer',
        ], 'privacy:metadata:loguser');

        $collection->add_database_table('logstore_mentor_session', [
                'sessionid' => 'privacy:metadata:logsession:sessionid',
                'space'     => 'privacy:metadata:logsession:space',
                'status'    => 'privacy:metadata:logsession:status',
        ], 'privacy:metadata:logsession');

        $collection->add_database_table('logstore_mentor_collection', [
                'name'         => 'privacy:metadata:logcollection:name',
                'sessionlogid' => 'privacy:metadata:logcollection:sessionlogid',
        ], 'privacy:metadata:logcollection');

        $collection->add_database_table('logstore_mentor_log', [
                'userlogid'    => 'privacy:metadata:log:userlogid',
                'sessionlogid' => 'privacy:metadata:log:sessionlogid',
                'timecreated'  => 'privacy:metadata:log:timecreated',
                'lastview'     => 'privacy:metadata:log:lastview',
                'numberview'   => 'privacy:metadata:log:numberview',
                'completed'    => 'privacy:metadata:log:completed',
        ], 'privacy:metadata:log');

        $collection->add_database_table('logstore_mentor_history_log', [
                'userlogid'    => 'privacy:metadata:loghistory:userlogid',
                'sessionlogid' => 'privacy:metadata:loghistory:sessionlogid',
                'timecreated'  => 'privacy:metadata:loghistory:timecreated',
                'lastview'     => 'privacy:metadata:loghistory:lastview',
                'numberview'   => 'privacy:metadata:loghistory:numberview',
                'completed'    => 'privacy:metadata:loghistory:completed',
        ], 'privacy:metadata:loghistory');
        return $collection;
    }

    /**
     * Add contexts that contain user information for the specified user.
     *
     * @param contextlist $contextlist The contextlist to add the contexts to.
     * @param int $userid The user to find the contexts for.
     * @return void
     */
    public static function add_contexts_for_userid(contextlist $contextlist, $userid) {
        $sql = "
            SELECT c.id
            FROM {context} c
            JOIN {course} co ON co.id = c.instanceid
            JOIN {session} s ON s.courseshortname = co.shortname
            JOIN {logstore_mentor_session} lms ON  lms.sessionid = s.id
            JOIN {logstore_mentor_log} lml ON lml.sessionlogid = lms.id
            JOIN {logstore_mentor_user} lmu ON lmu.id = lml.userlogid
            WHERE c.contextlevel = :contextlevel AND
                  lmu.userid = :userid";
        $contextlist->add_from_sql($sql, [
                'contextlevel' => CONTEXT_COURSE,
                'userid' => $userid
        ]);
    }

    /**
     * Add user IDs that contain user information for the specified context.
     *
     * @param \core_privacy\local\request\userlist $userlist The userlist to add the users to.
     * @return void
     */
    public static function add_userids_for_context(\core_privacy\local\request\userlist $userlist) {
        $sql    = "SELECT userid
                   FROM {logstore_mentor_user} lmu
                   JOIN {logstore_mentor_log} lml ON lmu.id = lml.userlogid
                   JOIN {logstore_mentor_session} lms ON lml.sessionlogid = lms.id
                   JOIN {session} s ON lms.sessionid = s.id
                   JOIN {course} co ON s.courseshortname = co.shortname
                   JOIN {context} c ON co.id = c.instanceid
                   WHERE c.id = :contextid";
        $userlist->add_from_sql('userid', $sql, [
                'contextid' => $userlist->get_context()->id
        ]);
    }

    /**
     * Get the database object.
     *
     * @return array Containing moodle_database, string, or null values.
     */
    protected static function get_database_and_table() {
        return [];
    }

    /**
     * Get the path to export the logs to.
     *
     * @return array
     * @throws \coding_exception
     */
    protected static function get_export_subcontext() {
        return [get_string('privacy:path:logs', 'tool_log'), get_string('pluginname', 'logstore_mentor')];
    }
}
