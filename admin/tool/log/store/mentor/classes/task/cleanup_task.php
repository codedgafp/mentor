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
 * Cleans the log table by cloning the elements that are not current
 * in the history_log table and then deletes them
 *
 * @package    logstore_mentor
 * @copyright  2021 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace logstore_mentor\task;

class cleanup_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     * @throws \coding_exception
     */
    public function get_name() {
        return get_string('taskcleanup', 'logstore_mentor');
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        global $DB;

        $lastmidnight = strtotime("0:00", time());

        // Get logs before midnight.
        $oldlogs = $DB->get_records_sql(
                'SELECT *
                FROM {logstore_mentor_log}
                WHERE timecreated < :lastmidnight'
                , ['lastmidnight' => $lastmidnight]);

        // Clone old logs in history log table.
        $DB->insert_records('logstore_mentor_history_log', $oldlogs);

        // Delete old logs.
        foreach ($oldlogs as $oldlog) {
            $DB->delete_records('logstore_mentor_log', array('id' => $oldlog->id));
        }
    }
}
