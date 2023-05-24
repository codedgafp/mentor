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
 * Database upgrades for the user local.
 *
 * @package   local_user
 * @copyright  2023 Edunao SAS (contact@edunao.com)
 * @author     Adrien Jamot <adrien.jamot@edunao.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

/**
 * Upgrade the local_user database.
 *
 * @param int $oldversion The version number of the plugin that was installed.
 * @return boolean
 * @throws ddl_exception
 * @throws ddl_table_missing_exception
 * @throws dml_exception
 */
function xmldb_local_user_upgrade($oldversion) {
    global $DB;

    if ($oldversion < 2023030300) {
        mtrace('START : "Ministère de la Transition écologique & de la Cohésion des territoires"' .
            ' to "Ministère de la Transition écologique et de la Cohésion des territoires"');
        $oldtext = 'Ministère de la Transition écologique & de la Cohésion des territoires';
        $newtext = 'Ministère de la Transition écologique et de la Cohésion des territoires';
        $DB->execute('
            UPDATE {user_info_data}
            SET data = REPLACE(data, :oldtext, :newtext)
            ', ['oldtext' => $oldtext, 'newtext' => $newtext]
        );
        mtrace('END');
    }

    return true;
}
