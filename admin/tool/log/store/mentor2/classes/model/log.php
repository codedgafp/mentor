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
 * Log table Class
 *
 * @package    logstore_mentor
 * @copyright  2021 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace logstore_mentor2\models;

class log extends abstractlog {

    /**
     * if $data exit in log $table
     *      update data and return $id
     * else
     *      insert $data in log $table and return id element created
     *
     * @param string $table name where save data's event
     * @return int id element
     * @throws \ReflectionException
     * @throws \dml_exception
     */
    public function get_or_create_record($table = '', $data = null) {

        if (empty($table)) {
            $table = (new \ReflectionClass($this))->getShortName();
        }

        if (!is_null($data)) {
            $this->eventdata = $data;
        }

        // Get id record.
        $id = $this->dbinterface->get_record_id($table, $this->eventdata);

        // Get lastview log data.
        $this->eventdata['lastview'] = $this->eventdata['lastview'] ?? time();

        // If record not exists.
        if (!$id) {
            return $this->create_record($table, $this->eventdata);
        }

        // Get record data.
        $logrecord = $this->dbinterface->get_record_by_id($table, $id);

        // Check that the data is from today.
        // If not create new log record.
        if ($logrecord->timecreated < strtotime("0:00", time())) {
            return $this->create_record($table, $this->eventdata);
        }

        // Set new data for update.
        $this->eventdata['numberview'] = $this->eventdata['numberview'] ?? $logrecord->numberview + 1; // New view.
        $this->eventdata['id'] = $id; // Set id for update.
        $this->id = $id;

        // Update log record.
        $this->dbinterface->update_record($table, $this->eventdata);

        return $this->id;
    }

    /**
     * Create new log record
     *
     * @param $table
     * @param $data
     * @return bool|int
     * @throws \dml_exception
     */
    public function create_record($table, $data) {

        // Set initial log record data.
        $data['timecreated'] = $data['timecreated'] ?? time();
        $data['numberview'] = $data['numberview'] ?? 1;

        // Insert new log record.
        $this->id = $this->dbinterface->insert_record($table, $data);

        return $this->id;
    }

    /**
     * Get required fields
     *
     * @return array
     */
    public function get_required_fields() {
        return [
            'userlogid',
            'sessionlogid'
        ];
    }
}
