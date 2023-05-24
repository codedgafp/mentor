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
 * Class mentor_entity
 *
 * @package    local_mentor_specialization
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     adrien <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mentor_specialization;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/mentor_core/classes/model/entity.php');
require_once($CFG->dirroot . '/local/mentor_specialization/classes/database_interface.php');

class mentor_entity extends \local_mentor_core\entity {

    /**
     * @var int[]
     */
    public $regions;

    private $dbinterfacementor;

    private $sirhlist;

    public $ishidden;

    /**
     * @var bool
     */
    public $canbemainentity;

    public const CAN_BE_MAIN_ENTITY_DATA_OPTIONS
        = [
            '0' => false,
            '1' => true
        ];

    /**
     * mentor_entity constructor.
     *
     * @param entity|int $entityorid
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function __construct($entityorid) {
        parent::__construct($entityorid);

        $this->dbinterfacementor = database_interface::get_instance();
        $regionsid = $this->get_regions_id();
        $this->regions = !empty($regionsid) ? explode(',', $regionsid) : [];
        $this->canbemainentity = $this->can_be_main_entity();

        $this->ishidden = $this->is_hidden();
    }

    /**
     * Check if the entity is hidden
     *
     * @return int 1 for hidden, 0 if visible
     * @throws \dml_exception
     */
    public function is_hidden() {
        $option = $this->dbinterfacementor->get_category_option($this->id, 'hidden');
        if ($option) {
            return $option->value;
        }

        return 0;
    }

    /**
     * Get entity regions id
     *
     * @return string list of regions separated by commas
     * @throws \dml_exception
     */
    private function get_regions_id() {
        $option = $this->dbinterfacementor->get_category_option($this->id, 'regionid');
        if ($option && $this->is_main_entity()) {
            return $option->value;
        }

        return '';
    }

    /**
     * Get the list of sirh
     *
     * @return array
     * @throws \dml_exception
     */
    public function get_sirh_list($refresh = false) {
        if (empty($this->sirhlist) || $refresh) {
            $option = $this->dbinterfacementor->get_category_option($this->id, 'sirhlist');

            if ($option && $option->value && $this->is_main_entity()) {
                $this->sirhlist = explode(',', $option->value);
            } else {
                $this->sirhlist = [];
            }
        }

        return $this->sirhlist;
    }

    /**
     * Can be a main entity
     *
     * @return bool
     * @throws \dml_exception
     */
    public function can_be_main_entity($refresh = false) {
        if (empty($this->canbemainentity) || $refresh) {
            if ($this->is_main_entity()) {
                // Get data to DB.
                $option = $this->dbinterfacementor->get_category_option($this->id, 'canbemainentity');

                if ($option && isset($option->value)) {
                    $this->canbemainentity = $option->value === '1';
                } else {
                    // Main entity : default is yes.
                    $this->canbemainentity = true;
                }
            } else {
                // No main entity : default is no.
                $this->canbemainentity = false;
            }
        }

        return $this->canbemainentity;
    }

    /**
     * Override the entity update process
     *
     * @param \stdClass $data
     * @param null $mform
     * @return bool|void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function update($data, $mform = null) {

        // Update hidden field.
        if (is_siteadmin() && isset($data->hidden)) {
            $this->update_visibility($data->hidden);
        }

        // Info : Update entities list profile selection.
        parent::update($data, $mform);

        if ($this->is_main_entity()) {
            // Update entity region.
            if (isset($data->regions)) {
                $this->update_regions($data->regions);
            }

            if (is_siteadmin()) {
                // Update entity sirh list.
                if (isset($data->sirhlist)) {
                    $this->update_sirh_list($data->sirhlist);
                }

                // Update can be main entity data option.
                if (isset($data->canbemainentity)) {
                    $this->update_can_be_main_entity($data->canbemainentity);
                }
            }

            // Update list of available entities within the user profile.
            local_mentor_core_update_entities_list();
        }

        return true;
    }

    public function get_presentation_page_course() {
        // TODO: Change the autogenerated stub.
        return parent::get_presentation_page_course();
    }

    /**
     * Update the visibility of the entity
     *
     * @param int $hidden 1 to hide the entity
     * @return void
     * @throws \dml_exception
     */
    public function update_visibility($hidden) {
        $this->ishidden = $hidden;
        $this->dbinterfacementor->update_entity_visibility($this->id, $hidden);
    }

    /**
     * Update sirh list
     *
     * @param string|array $sirhlist
     * @throws \dml_exception
     */
    public function update_sirh_list($sirhlist) {
        if ($this->is_main_entity()) {
            $this->sirhlist = is_array($sirhlist) ? $sirhlist : explode(',', $sirhlist);

            $this->dbinterfacementor->update_entity_sirh_list($this->id, $this->sirhlist);
        }
    }

    /**
     * Update the entity region
     *
     * @param int|array $regionsid
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function update_regions($regionsid) {
        global $CFG;
        require_once($CFG->dirroot . '/local/user/lib.php');
        require_once($CFG->dirroot . '/local/mentor_core/api/profile.php');

        if (!is_array($regionsid)) {
            $regionsid = [$regionsid];
        }

        $this->dbinterfacementor->update_entity_regions($this->id, $regionsid);
        $this->regions = $regionsid;

        // Manage cohort members.
        $oldusers = $this->get_members();
        $newusers = $this->dbinterfacementor->get_users_by_regions($regionsid);

        // Get users by entity.
        $entityusers = \local_mentor_core\profile_api::get_users_by_mainentity($this->name);
        $secondaryentityusers = \local_mentor_core\profile_api::get_users_by_secondaryentity($this->name);

        $newusers = $newusers + $entityusers;
        $newusers = $newusers + $secondaryentityusers;

        // Determine members to add and to remove.
        $userstoadd = array_diff_key($newusers, $oldusers);
        $userstoremove = array_diff_key($oldusers, $newusers);

        // Remove old members.
        foreach ($userstoremove as $usertoremove) {
            $this->remove_member($usertoremove);
        }

        // Add new members.
        foreach ($userstoadd as $usertoadd) {
            $this->add_member($usertoadd);
        }
    }

    /**
     * Update the option to know if the entity can be main.
     *
     * @param string $canbemainentity
     * @return bool
     */
    public function update_can_be_main_entity($canbemainentity) {
        // Bad response value.
        if (!isset(self::CAN_BE_MAIN_ENTITY_DATA_OPTIONS[$canbemainentity])) {
            return false;
        }

        // Same value.
        if (
            self::CAN_BE_MAIN_ENTITY_DATA_OPTIONS[$canbemainentity] ===
            $this->canbemainentity
        ) {
            return false;
        }

        // Update data.
        $this->dbinterface->update_can_be_main_entity($this->id, $canbemainentity);
        $this->canbemainentity = self::CAN_BE_MAIN_ENTITY_DATA_OPTIONS[$canbemainentity];

        // True to false.
        if (!$this->canbemainentity) {
            // Get the users linked with the main entity and the regions.
            $regions = $this->dbinterfacementor->get_users_by_regions($this->regions);
            $mail = \local_mentor_core\profile_api::get_users_by_mainentity($this->name);

            // Remove the entity as the main entity from all users.
            $this->dbinterface->remove_main_entity_to_all_user($this->id);

            // Removes users from the cohort that are no longer linked to the entity.
            $usersremovecohort = array_diff_key($mail, $regions);
            $usersidremovecohort = array_map(function($user) {
                return $user->id;
            }, $usersremovecohort);
            local_mentor_specialization_cohort_remove_members($this->get_cohort()->id, $usersidremovecohort);
        }

        return true;
    }

    /**
     * Override the entity get_form_data method
     *
     * @return \stdClass
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_form_data() {
        $entityobj = parent::get_form_data();

        // Add specific fields for main entity only.
        if ($this->is_main_entity()) {
            // Add the region id to the form data.
            $entityobj->regions = $this->regions;
            $entityobj->sirhlist = $this->get_sirh_list();
        }

        $entityobj->hidden = $this->is_hidden();
        $entityobj->canbemainentity = $this->canbemainentity;

        return $entityobj;
    }

    /**
     * Get manager role
     *
     * @return \stdClass
     * @throws \dml_exception
     */
    public function get_manager_role() {
        return $this->dbinterface->get_role_by_name('admindedie');
    }

    /**
     * Get entity trainings
     *
     * @return \stdClass[]
     * @throws \dml_exception
     */
    public function get_trainings() {
        $db = database_interface::get_instance();
        return $db->get_trainings_by_entity_id($this->id, false);
    }
}
