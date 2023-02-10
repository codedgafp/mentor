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
 * Automatically close sessions
 *
 * @package    local_mentor_specialization
 * @copyright  2021 Edunao SAS (contact@edunao.com)
 * @author     adrien <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mentor_specialization\task;

use local_mentor_specialization\mentor_session;

class close_sessions extends \core\task\scheduled_task {

    public function get_name() {
        // Shown in admin screens.
        return get_string('task_close_sessions', 'local_mentor_specialization');
    }

    public function execute() {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/local/mentor_specialization/classes/models/mentor_session.php');

        $status = mentor_session::STATUS_IN_PROGRESS;

        // End of the end date day.
        $now = time() - 86400;

        // Get sessions with state "inprogress" that must be closed.
        $sessions = $DB->get_records_sql('
            SELECT
                s.*
            FROM
                {session} s
            JOIN
                {course} c ON c.shortname = s.courseshortname
            WHERE
                s.sessionenddate < :now
                AND
                s.status = :status
                AND
                s.sessionenddate != 0
                AND
                s.sessionenddate IS NOT NULL
        ', array('now' => $now, 'status' => $status));

        // Update the status of each session.
        foreach ($sessions as $session) {
            mtrace('Close session : ' . $session->id . ' - ' . $session->courseshortname);

            $session = new mentor_session($session->id);
            $session->update_status(mentor_session::STATUS_COMPLETED);
        }
    }
}
