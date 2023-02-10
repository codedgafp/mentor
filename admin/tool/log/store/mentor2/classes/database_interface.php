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
 * Database interface
 *
 * @package    logstore_mentor
 * @copyright  2021 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace logstore_mentor2\database_interface;

define('LIMIT_CACHE_LOG2', 2000);

class database_interface {

    /**
     * @var array
     */
    private $log;

    /**
     * @var \moodle_database
     */
    private $db;

    /**
     * @var database_interface
     */
    private static $instance;

    /**
     * @return database_interface
     */
    public static function get_instance() {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * database_interface constructor.
     */
    public function __construct() {
        global $DB;
        $this->db                  = $DB;
        $this->log                 = array();
        $this->log['log2']         = array();
        $this->log['log_history2'] = array();
        $this->log['session2']     = array();
        $this->log['collection2']  = array();
        $this->log['user2']        = array();
        $this->log['region2']      = array();
        $this->log['entity2']      = array();
    }

    /**
     * insert data to table name $table by $id
     *
     * @param $table string
     * @param $id int
     * @param $data array
     */
    private function add_to_log($table, $id, $data) {
        $this->log[$table][$id] = $data;
    }

    /**
     * return id if $data exist in row to table name $table
     * else return null
     *
     * @param $table string
     * @param $data array
     * @return false|int
     */
    public function get_log_index($table, $data) {
        return array_search($data, $this->log[$table]);
    }

    /**
     * return log table
     *
     * @return array
     */
    public function get_log() {
        return $this->log;
    }

    /**
     * if $data exist in database return $id
     * else insert $data in database and return $id
     *
     * @param $table string
     * @param $datas array
     * @return bool|int|mixed
     * @throws \dml_exception $id
     */
    public function get_record_id($table, $datas) {

        // Create where condition request.
        $where = "WHERE ";
        $size  = count($datas);
        $i     = 1;
        // Check all type of data for create condition.
        foreach ($datas as $key => $data) {
            $type = gettype($data);
            if ($type === "string") {
                $where .= $this->db->sql_compare_text($key) . " = " . $this->db->sql_compare_text(":$key");
            } else if ($type === "NULL") {
                $where .= $key . " IS " . $this->db->sql_compare_text(":$key");
            } else {
                $where .= $key . " = " . ":$key";
            }
            if ($i < $size) {
                $where .= " AND ";
            }
            $i++;
        }

        // Order by timecreated when is log table.
        $request = "SELECT id FROM {logstore_mentor_$table} " . $where;
        if ($table === 'log2') {
            $request .= ' ORDER BY timecreated DESC';
        }

        if ($table === 'session') {
            $request .= ' ORDER BY id DESC';
        }

        return $this->db->get_field_sql($request, $datas, IGNORE_MULTIPLE);
    }

    /**
     * get table log record by id
     *
     * @param $table string
     * @param $id int
     * @return \stdClass
     * @throws \dml_exception $id
     */
    public function get_record_by_id($table, $id) {
        return $this->db->get_record('logstore_mentor_' . $table, array('id' => $id));
    }

    /**
     * Insert record
     *
     * @param $table string
     * @param $data array
     * @return bool|int
     * @throws \dml_exception
     */
    public function insert_record($table, $data) {
        $id = $this->db->insert_record('logstore_mentor_' . $table, $data);
        $this->add_to_log($table, $id, $data);
        return $id;
    }

    /**
     * Update record
     *
     * @param $table string
     * @param $data array
     * @return bool
     * @throws \dml_exception
     */
    public function update_record($table, $data) {
        $result = $this->db->update_record('logstore_mentor_' . $table, $data);
        $this->add_to_log($table, $data['id'], $data);

        return $result;
    }

    /**
     * Get id record if exist
     * else create record and return new record id
     *
     * @param $table string
     * @param $data  array
     * @return bool|int|mixed
     * @throws \dml_exception
     */
    public function get_or_create_record($table, $data) {
        // Get record id.
        $id = $this->get_record_id($table, $data);

        // If record not exist create record and get new record id.
        if (!$id) {
            $id = $this->insert_record($table, $data);
        }

        $this->add_to_log($table, $id, $data);

        return $id;
    }
}
