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
 * Session log table Class
 *
 * @package    logstore_mentor/models
 * @copyright  2021 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace logstore_mentor2\models;

class session extends abstractlog {

    public function get_required_fields() {
        return [
                'sessionid',
                'entitylogid',
                'subentitylogid',
                'trainingentitylogid',
                'trainingsubentitylogid',
                'status',
                'shared'
        ];
    }

    /**
     * if @datas exit in log @table
     *      return id element
     * else
     *      insert @data in log @table and return id element created
     *
     * @param $data data's event
     * @param $table table name where save data's event
     * @return int id element
     * @throws \dml_exception
     * @throws \ReflectionException
     */
    public function get_or_create_record($table = '', $data = null) {
        global $DB;

        // By default the table name is the same as the class name.
        if (empty($table)) {
            $table = (new \ReflectionClass($this))->getShortName();
        }

        // Return id if exists.
        $existingsessions = $DB->get_records('logstore_mentor_session2', [
                'sessionid'              => $this->eventdata['sessionid'],
                'shared'                 => $this->eventdata['shared'],
                'status'                 => $this->eventdata['status'],
                'entitylogid'            => $this->eventdata['entitylogid'],
                'subentitylogid'         => $this->eventdata['subentitylogid'],
                'trainingentitylogid'    => $this->eventdata['trainingentitylogid'],
                'trainingsubentitylogid' => $this->eventdata['trainingsubentitylogid']
        ]);

        // A record already exists.
        if ($existingsessions) {

            foreach ($existingsessions as $existingsession) {
                // Check if collections have changed.

                $dbcollections = $DB->get_records_sql('
                    SELECT
                        c.name
                    FROM
                        {logstore_mentor_collection2} c
                    JOIN {logstore_mentor_sesscoll2} sc ON c.id = sc.collectionlogid
                    WHERE
                        sc.sessionlogid = :sessionlogid
                    ORDER BY c.name ASC
                ', ['sessionlogid' => $existingsession->id]);

                $existingcollections = [];

                foreach ($dbcollections as $dbcollection) {
                    $existingcollections[] = $dbcollection->name;
                }

                // Check if the session log already exists.
                if (empty(array_diff($data['collections'], $existingcollections))) {
                    $this->id = $existingsession->id;
                    return $this->id;
                }
            }

            // The session log does not exist, create it.
            return $this->create($table, $data);
        }

        // Create a new session log.
        return $this->create($table, $data);
    }

    /**
     * Create a new session log
     *
     * @param $table
     * @param $data
     * @return bool|int
     * @throws \ReflectionException
     * @throws \dml_exception
     */
    private function create($table, $data) {

        // Create a new session log.
        $this->id = $this->dbinterface->insert_record($table, $this->eventdata);

        // Manage collections.
        foreach ($data['collections'] as $collectionname) {
            $collectionlog = new \logstore_mentor2\models\collection(['name' => $collectionname]);
            $collectionlogid = $collectionlog->get_or_create_record('collection2');

            $sesscolldata = new \stdClass();
            $sesscolldata->sessionlogid = $this->id;
            $sesscolldata->collectionlogid = $collectionlogid;
            $this->dbinterface->insert_record('sesscoll2', $sesscolldata);
        }

        return $this->id;
    }
}
