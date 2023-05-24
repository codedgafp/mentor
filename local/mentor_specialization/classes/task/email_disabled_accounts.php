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
 * Send email to disabled account trying to login
 *
 * @package    local_mentor_specialization
 * @copyright  2022 Edunao SAS (contact@edunao.com)
 * @author     adrien <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mentor_specialization\task;

class email_disabled_accounts extends \core\task\scheduled_task {

    public function get_name() {
        // Shown in admin screens.
        return get_string('email_disabled_accounts', 'local_mentor_specialization');
    }

    public function execute() {
        global $DB;

        // Get login failure for the last 10 minutes.
        $lastexecution = time() - 600;

        $sql = '
            SELECT
                DISTINCT u.id, u.*
            FROM
                {logstore_standard_log} ll
            JOIN
                {user} u ON ll.userid = u.id
            WHERE
                ll.eventname = :eventname
                AND
                ll.other LIKE :reason
                AND
                ll.timecreated > :lastexecution
        ';

        $records = $DB->get_records_sql($sql, [
                'eventname' => '\core\event\user_login_failed', 'reason' => '%\"reason\":2%', 'lastexecution'
                => $lastexecution
        ]);

        $supportuser = \core_user::get_support_user();

        $object = get_string('email_disabled_accounts_object', 'local_mentor_specialization');

        // Get the content of the email.
        $content = get_string('email_disabled_accounts_content', 'local_mentor_specialization');
        $contenthtml = text_to_html($content, false, false, true);

        foreach ($records as $record) {

            // Suspended user cannot receive emails.
            $record->suspended = 0;

            // Send the email.
            email_to_user($record, $supportuser, $object, $content, $contenthtml);
            mtrace('email_disabled_accounts: ' . $record->email);
        }
    }
}
