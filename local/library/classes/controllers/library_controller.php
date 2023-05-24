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
 * Library controller
 *
 * @package    local_library
 * @copyright  2022 Edunao SAS (contact@edunao.com)
 * @author     adrien <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_library;

defined('MOODLE_INTERNAL') || die();

use local_mentor_core;
use local_mentor_core\controller_base;
use local_mentor_core\training;

require_once($CFG->dirroot . '/local/mentor_core/classes/controllers/controller_base.php');

class library_controller extends controller_base {

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
                case 'enrol_current_user':
                    $trainingid = $this->get_param('trainingid', PARAM_INT);
                    return $this->success(self::enrol_current_user($trainingid));
                case 'import_to_entity':
                    $trainingid = $this->get_param('trainingid', PARAM_INT);
                    $trainingshortname = $this->get_param('trainingshortname', PARAM_TEXT);
                    $entityid = $this->get_param('entityid', PARAM_INT);
                    return $this->success(self::import_to_entity($trainingid, $trainingshortname, $entityid));
                default:
                    break;
            }
        } catch (\moodle_exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Enrol the current user into a library training
     *
     * @param int $trainingid
     * @return string url of the training course
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function enrol_current_user($trainingid) {
        $training = local_mentor_core\training_api::get_training($trainingid);
        $result = $training->enrol_current_user();

        // Enrolment success, return the url of the session course.
        if ($result['status']) {
            return $training->get_url()->out(false);
        }

        // Use the first warning.
        if (count($result['warnings']) > 1 || isset($result['warnings'][0])) {
            throw new \moodle_exception('errormoodle', 'local_library', '', $result['warnings'][0]['message']);
        }

        throw new \moodle_exception('errormoodle', 'local_library', '', $result['warnings']['message']);
    }

    /**
     * Import training library to entity
     *
     * @param int $trainingid
     * @param string $trainingshortname
     * @param int $entityid
     * @return training|bool the created training
     * @throws \dml_exception
     * @throws \moodle_exception
     * @throws \required_capability_exception
     */
    public static function import_to_entity($trainingid, $trainingshortname, $entityid) {
        return \local_mentor_core\library_api::import_to_entity($trainingid, $trainingshortname, $entityid);
    }

}
