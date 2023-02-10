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
 * trainings local.
 *
 * @package    local_trainings
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     nabil <nabil.hamdi@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_trainings;

defined('MOODLE_INTERNAL') || die();

use local_mentor_core;
use local_mentor_core\controller_base;
use local_mentor_core\training_api;

require_once($CFG->dirroot . '/local/mentor_core/classes/controllers/controller_base.php');
require_once($CFG->dirroot . '/local/mentor_core/api/training.php');

class training_controller extends controller_base {

    /**
     * Execute action
     *
     * @return mixed
     * @throws \moodle_exception
     */
    public function execute() {

        $action = $this->get_param('action');

        try {
            switch ($action) {
                case 'get_trainings' :
                    return $this->success(self::get_trainings());

                case 'get_trainings_by_entity':

                    // Get count all trainings record by entity.
                    $data                 = new \stdClass();
                    $data->entityid       = $this->get_param('entityid', PARAM_INT);
                    $data->start          = 0;
                    $data->length         = 0;
                    $data->status         = false;
                    $data->datefrom       = false;
                    $data->dateto         = false;
                    $data->search         = false;
                    $data->order          = false;
                    $data->filters        = [];
                    $data->onlymainentity = $this->get_param('onlymainentity', PARAM_INT, true) ? true : false;
                    $data->recordsTotal   = self::count_trainings_by_entity($data);

                    // Get count all trainings record by entity with filter.
                    $data->status          = $this->get_param('status', PARAM_TEXT, null);
                    $data->dateto          = $this->get_param('dateto', PARAM_INT, null);
                    $data->datefrom        = $this->get_param('datefrom', PARAM_INT, null);
                    $data->draw            = $this->get_param('draw', PARAM_INT, null);
                    $data->order           = $this->get_param('order', PARAM_RAW, null);
                    $data->order           = is_null($data->order) ? $data->order : $data->order[0];
                    $data->search          = $this->get_param('search', PARAM_RAW, null);
                    $data->filters         = $this->get_param('filter', PARAM_RAW, []);
                    $data->recordsFiltered = self::count_trainings_by_entity($data);

                    // Get trainings record by entity with filter, lentgh and start.
                    $data->length = $this->get_param('length', PARAM_INT, null);
                    $data->start  = $this->get_param('start', PARAM_INT, null);
                    $data->data   = self::get_trainings_by_entity($data);
                    return $data;
                case 'duplicate_training':
                    $trainingid        = $this->get_param('trainingid', PARAM_INT);
                    $trainingshortname = $this->get_param('trainingshortname', PARAM_TEXT);
                    $destinationentity = $this->get_param('destinationentity', PARAM_INT);

                    return $this->success(self::duplicate_training($trainingid, $trainingshortname, $destinationentity));

                case 'move_training':
                    $trainingid        = $this->get_param('trainingid', PARAM_INT);
                    $destinationentity = $this->get_param('destinationentity', PARAM_INT);

                    return $this->success(self::move_training($trainingid, $destinationentity));

                default:
                    $trainingid = $this->get_param('trainingid', PARAM_INT);
                    return $this->success(self::$action($trainingid));
            }
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }

    }

    /**
     * Add a training
     *
     * @param \stdClass $datas
     * @param training_form $mform
     * @return local_mentor_core\training
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function add_training($datas, $mform) {
        return training_api::create_training($datas, $mform);
    }

    /**
     * update a training
     *
     * @param \stdClass $datas
     * @param training_form $mform
     * @return local_mentor_core\training
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function update_training($datas, $mform) {
        return training_api::update_training($datas, $mform);
    }

    /**
     * Get a training by id
     *
     * @param int $trainingid
     * @return local_mentor_core\training
     * @throws \ExceptionMentor Sprint 29
     */
    public static function get_training($trainingid) {
        return training_api::get_training($trainingid);
    }

    /**
     * @param \stdClass|int $data
     * @return \stdClass[]
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function get_trainings_by_entity($data) {
        return training_api::get_trainings_by_entity($data);
    }

    /**
     * @param \stdClass|int $data
     * @return int
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function count_trainings_by_entity($data) {
        return training_api::count_trainings_by_entity($data);
    }

    /**
     * Remove training
     *
     * @param int $trainingid
     * @return false|string
     * @throws \dml_exception
     * @throws \moodle_exception
     * @throws \required_capability_exception
     */
    public static function remove_training($trainingid) {
        return training_api::remove_training($trainingid);
    }

    /**
     * Duplicate a training
     *
     * @param int $trainingid
     * @param string $trainingshortname
     * @param int $destinationentity - optional default null
     * @return local_mentor_core\training|\stdClass
     * @throws \dml_exception
     * @throws \moodle_exception
     * @throws \required_capability_exception
     */
    public static function duplicate_training($trainingid, $trainingshortname, $destinationentity = null) {
        return training_api::duplicate_training($trainingid, $trainingshortname, $destinationentity);
    }

    /**
     * Move a training into an other entity
     *
     * @param int $trainingid
     * @param int $destinationentity
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function move_training($trainingid, $destinationentity) {
        return training_api::move_training($trainingid, $destinationentity);
    }

    /**
     * Get training course
     *
     * @param int $trainingid
     * @return \stdClass
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function get_training_course($trainingid) {
        return training_api::get_training_course($trainingid);
    }

    /**
     * Get next available name for a training
     *
     * @param int $trainingid
     * @return string
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function get_next_available_training_name($trainingid) {
        return training_api::get_next_available_training_name($trainingid);
    }
}
