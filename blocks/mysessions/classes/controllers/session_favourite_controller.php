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
 * Session favourite controller
 *
 * @package    block_mysessions
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_mysessions;

use local_mentor_core\controller_base;

require_once(__DIR__ . '/../../../../config.php');

require_login();

require_once($CFG->dirroot . '/local/mentor_core/api/session.php');
require_once($CFG->dirroot . '/local/mentor_core/classes/controllers/controller_base.php');

defined('MOODLE_INTERNAL') || die();

class session_favourite_controller extends controller_base {

    /**
     * Execute action
     *
     * @return array
     */
    public function execute() {

        try {
            $action = $this->get_param('action');
            $sessionid = $this->get_param('sessionid', PARAM_INT);
            return $this->success($this->$action($sessionid));
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }

    }

    /**
     * Add session to user's favourite
     *
     * @param int $sessionid
     * @return int|bool
     * @throws \dml_exception
     */
    public function add_favourite($sessionid) {
        return \local_mentor_core\session_api::add_user_favourite_session($sessionid);

    }

    /**
     * Remove session to user's favourite
     *
     * @param int $sessionid
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function remove_favourite($sessionid) {
        return \local_mentor_core\session_api::remove_user_favourite_session($sessionid);
    }

}
