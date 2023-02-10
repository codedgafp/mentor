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
 * Database upgrades for the mentor_core local.
 *
 * @package   local_mentor_core
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author    Nabil HAMDI <nabil.hamdi@edunao.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

/**
 * Upgrade the local_trainings database.
 *
 * @param int $oldversion The version number of the plugin that was installed.
 * @return boolean
 * @throws ddl_exception
 * @throws ddl_table_missing_exception
 * @throws dml_exception
 */
function xmldb_local_mentor_core_upgrade($oldversion) {
    global $CFG, $DB;

    require_once($CFG->dirroot . '/local/mentor_core/lib.php');
    require_once($CFG->libdir . '/db/upgradelib.php'); // Core Upgrade-related functions.

    if ($oldversion < 2021041302) {
        $liststatusnamechanges = local_mentor_core_get_list_status_name_changes();

        $trainings = $DB->get_records('training');

        foreach ($trainings as $training) {
            if (array_key_exists($training->status, $liststatusnamechanges)) {
                $training->status = $liststatusnamechanges[$training->status];
                $DB->update_record('training', $training);
            }
        }
    }

    $dbman = $DB->get_manager();

    if ($oldversion < 2021041900) {
        $trainingtable = new xmldb_table('training');

        // Training table fields.
        $trainingfields = [
            'traininggoal' => [XMLDB_TYPE_TEXT, '255', null, null, null],
            'thumbnail'    => [XMLDB_TYPE_CHAR, '255', null, null, null]
        ];

        // Adding fields to database.
        foreach ($trainingfields as $name => $definition) {
            $trainingfield = new xmldb_field($name, $definition[0], $definition[1], $definition[2], $definition[3], $definition[4]);
            if (!$dbman->field_exists($trainingtable, $trainingfield)) {
                $dbman->add_field($trainingtable, $trainingfield);
            }
        }
    }
    if ($oldversion < 2022051100) {
        try {
            $DB->execute("UPDATE {session}
            SET courseshortname = REPLACE(courseshortname,:search,:replace)", [
                'search'  => '&#39;',
                'replace' => "'"
            ]);
        } catch (\dml_exception $e) {
            mtrace('WARNING : Replace unicode to shortname in course to session!!!');
        }

        try {
            $DB->execute("UPDATE {course}
            SET shortname = REPLACE(shortname,:search,:replace)", [
                'search'  => '&#39;',
                'replace' => "'"
            ]);
        } catch (\dml_exception $e) {
            mtrace('WARNING : Replace unicode to shortname in course!!!');
        }
    }

    return true;
}
