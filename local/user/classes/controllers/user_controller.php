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

namespace local_user;

use local_mentor_core\controller_base;
use local_mentor_core\profile_api;

require_once(__DIR__ . '/../../../../config.php');
require_once($CFG->dirroot . '/local/user/lib.php');
require_once($CFG->dirroot . '/local/mentor_core/api/profile.php');
require_once($CFG->dirroot . '/local/mentor_core/classes/controllers/controller_base.php');

// Require login.
require_login();

class user_controller extends controller_base {

    /**
     * Execute action
     *
     * @return mixed
     * @throws \moodle_exception
     */
    public function execute() {
        $action = $this->get_param('action');

        switch ($action) {
            case 'search_users' :
                $searchtext = $this->get_param('searchtext', PARAM_TEXT);
                return $this->search_users($searchtext);
            case 'get_roles' :

                // Get count of all roles.
                $data = new \stdClass();
                $data->start = 0;
                $data->length = 0;
                $data->search = false;
                $data->order = false;
                $data->filters = [];
                $data->recordsTotal = count($this->get_roles($data));

                // Get count all trainings record by entity with filter.
                $data->draw = $this->get_param('draw', PARAM_INT, null);
                $data->order = $this->get_param('order', PARAM_RAW, null)[0];
                $data->search = $this->get_param('search', PARAM_RAW, null);
                $data->filters = $this->get_param('filter', PARAM_RAW, []);
                $data->recordsFiltered = count($this->get_roles($data));

                // Get trainings record by entity with filter, lentgh and start.
                $data->length = $this->get_param('length', PARAM_INT, null);
                $data->start = $this->get_param('start', PARAM_INT, null);
                $data->data = $this->get_roles($data);

                return $data;
            case  'create_and_add_user':
                $lastname = $this->get_param('lastname', PARAM_TEXT);
                $firstname = $this->get_param('firstname', PARAM_TEXT);
                $email = $this->get_param('mail', PARAM_TEXT);
                $entity = $this->get_param('entity', PARAM_TEXT, null);
                $secondaryentities = $this->get_param('secondaryentities', PARAM_RAW, []);
                $region = $this->get_param('region', PARAM_TEXT, null);
                return $this->create_and_add_user($lastname, $firstname, $email, $entity, $secondaryentities, $region);
            case 'set_user_preference' :
                $userid = $this->get_param('userid', PARAM_INT, null);
                $preferencename = $this->get_param('preferencename', PARAM_TEXT);
                $value = $this->get_param('value', PARAM_TEXT);
                return $this->set_user_preference($userid, $preferencename, $value);

            default:
                $userid = $this->get_param('userid', PARAM_INT);
                return $this->$action($userid);
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
     *  Asssign role to user
     *
     * @param string $rolename
     * @param int $userid
     * @param int $contextid
     * @return int new/existing id of the assignment
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function role_assign($rolename, $userid, $contextid) {
        return profile_api::role_assign($rolename, $userid, $contextid);
    }

    /**
     * Create and add new user with minimum information
     * The new user must fill in the rest of the information in his profile
     *
     * @param string $lastname
     * @param string $firstname
     * @param string $email
     * @param string|int|entity|null $entity
     * @param string|int|null $region
     * @return bool|int
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function create_and_add_user($lastname, $firstname, $email, $entity, $secondaryentities, $region) {
        return profile_api::create_and_add_user($lastname, $firstname, $email, $entity, $secondaryentities, $region);
    }

    /**
     * Get all user with category roles
     *
     * @param array $data
     * @return array
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function get_roles($data) {
        global $CFG;
        require_once($CFG->dirroot . '/local/mentor_core/api/profile.php');

        $roles = profile_api::get_all_users_roles($data);

        $data = [];

        foreach ($roles as $role) {
            $data[] = [
                $role->firstname . ' ' . $role->lastname,
                $role->email,
                $role->entityshortname,
                $role->rolename,
                $role->entityname,
                $role->timemodified,
                $role->lastaccessstr,
                $role->userid
            ];
        }

        return $data;
    }

    /**
     * Set a user preference
     *
     * @param int|null $userid
     * @param string $preferencename
     * @param mixed $value
     * @return void
     */
    public static function set_user_preference($userid = null, $preferencename, $value) {
        if (is_null($userid)) {
            global $USER;
            $userid = $USER->id;
        }

        return profile_api::set_user_preference($userid, $preferencename, $value);
    }
}
