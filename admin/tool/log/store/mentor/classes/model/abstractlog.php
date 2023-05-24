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
 * log table abstract Class
 *
 * @package    logstrore_mentor/models
 * @copyright  2021 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace logstore_mentor\models;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . "/admin/tool/log/store/mentor/classes/database_interface.php");

use logstore_mentor\database_interface\database_interface;

abstract class abstractlog {

    protected $dbinterface;

    protected $eventdata;

    protected $id;

    /**
     * abstractlog constructor.
     *
     * @param $data
     * @throws \ReflectionException
     */
    public function __construct($data) {
        $this->dbinterface = database_interface::get_instance();

        $requiredfields = $this->get_required_fields();
        $table = (new \ReflectionClass($this))->getShortName();

        // Check if all required fields exist.
        foreach ($requiredfields as $requiredfield) {
            if (!in_array($requiredfield, $data)) {
                throw new \Exception("Missing field " . $requiredfield . " for class " . $table);
            }
            $this->eventdata[$requiredfield] = $data[$requiredfield];
        }

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

        // By default the table name is the same as the class name.
        if (empty($table)) {
            $table = (new \ReflectionClass($this))->getShortName();
        }

        // Return id if exists.
        if ($this->id = $this->dbinterface->get_log_index($table, $this->eventdata)) {
            return $this->id;
        }

        // Get record id if exists, else create record.
        $this->id = $this->dbinterface->get_or_create_record($table, $this->eventdata);

        return $this->id;
    }

    /**
     * Return the list of required fields that needs to be set in eventdata
     *
     * @return array
     */
    public function get_required_fields() {
        return [];
    }
}
