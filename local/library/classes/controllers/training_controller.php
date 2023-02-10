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
 * @copyright  2022 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_library;

defined('MOODLE_INTERNAL') || die();

use local_mentor_core\controller_base;

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
            $trainingid = $this->get_param('trainingid', PARAM_INT);
            return $this->success(self::$action($trainingid));
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }

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
        return \local_mentor_core\training_api::get_next_available_training_name($trainingid);
    }
}
