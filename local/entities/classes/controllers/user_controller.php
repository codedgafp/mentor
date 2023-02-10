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
 * User controller
 *
 * @package    local_user
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_entities;

use local_mentor_core\profile_api;
use local_mentor_core\controller_base;

require_once(__DIR__ . '/../../../../config.php');

require_login();

require_once($CFG->dirroot . '/local/user/lib.php');
require_once($CFG->dirroot . '/local/mentor_core/api/profile.php');
require_once($CFG->dirroot . '/local/mentor_core/classes/controllers/controller_base.php');

defined('MOODLE_INTERNAL') || die();

class user_controller extends controller_base {

    /**
     * Execute action
     *
     * @return array
     */
    public function execute() {

        try {
            $action = $this->get_param('action');

            switch ($action) {

                case 'search_users' :
                    $searchtext = $this->get_param('searchtext', PARAM_TEXT);
                    return $this->success($this->search_users($searchtext));

                default:
                    $cohortid = $this->get_param('userid', PARAM_INT);
                    return $this->success($this->$action($cohortid));
            }

        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }

    }

    /**
     * Search among users
     *
     * @param string $searchtext
     * @return array
     * @throws \dml_exception
     */
    public static function search_users($searchtext) {
        return profile_api::search_users($searchtext);
    }

    /**
     * Assign role to user
     *
     * @param string $rolename
     * @param int $userid
     * @param int $contextid
     * @return int
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function role_assign($rolename, $userid, $contextid) {
        return profile_api::role_assign($rolename, $userid, $contextid);
    }

}
