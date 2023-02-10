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

namespace logstore_mentor\models;

class session extends abstractlog {

    public function get_required_fields() {
        return [
                'sessionid',
                'space',
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

        $collections = explode(',', $data['collections']);

        // Return id if exists.
        $id = $this->dbinterface->get_record_id($table, $this->eventdata);

        // A record already exists.
        if ($id) {

            $this->id = $id;

            $oldcollections = $DB->get_fieldset_select('logstore_mentor_collection', 'name', 'sessionlogid = :sessionlogid',
                    ['sessionlogid' => $this->id]);

            $diff = array_diff($collections, $oldcollections);

            // Check if session's collections have changed.
            if (!empty($diff)) {
                // Create a new session log entry.
                $this->id = $this->dbinterface->insert_record($table, $this->eventdata);
            }
        } else {
            // Create a new session log entry.
            $this->id = $this->dbinterface->insert_record($table, $this->eventdata);
        }

        return $this->id;
    }
}
