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
 * Specialization of the mentor user profile
 *
 * @package    local_mentor_specialization
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     adrien <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mentor_specialization;

use local_mentor_core\entity_api;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/mentor_core/classes/model/profile.php');
require_once($CFG->dirroot . '/local/mentor_specialization/classes/database_interface.php');

class mentor_profile extends \local_mentor_core\profile {

    public const ROLE_ADMINDIE              = 'admindedie';
    public const ROLE_COURSECREATOR         = 'coursecreator';
    public const ROLE_RESPFORMATION         = 'respformation';
    public const ROLE_REFERENTLOCAL         = 'referentlocal';
    public const ROLE_CONCEPTEUR            = 'concepteur';
    public const ROLE_FORMATEUR             = 'formateur';
    public const ROLE_PARTICIPANT           = 'participant';
    public const ROLE_PARTICIPANTNONEDITEUR = 'participantnonediteur';
    public const ROLE_TUTEUR                = 'tuteur';

    /**
     * Sync user entities
     *
     * @param bool $isnewuser
     * @return bool true if success
     * @throws \Exception
     */
    public function sync_entities($isnewuser = false) {
        global $CFG;
        require_once($CFG->dirroot . '/local/entities/lib.php');
        require_once($CFG->dirroot . '/local/mentor_core/api/entity.php');

        $dbinterfacementor = database_interface::get_instance();

        // Get main entity.
        if (!$mainentity = $this->get_main_entity()) {
            return;
        }

        $oldcohorts = [];
        if (!$isnewuser) {
            // Get user cohorts.
            $usercohorts = $this->get_entities_cohorts();

            // Get old Cohorts.
            $oldcohorts = array_map(function($key) {
                return $key->cohortid;
            }, array_values($usercohorts));
        }

        $maincohortid = $mainentity->get_cohort()->id;

        $secondarycohorts = [];

        $secondaryentities = $this->get_secondary_entities();

        // Get secondary entities.
        foreach ($secondaryentities as $secondaryentity) {
            $secondarycohorts[] = $secondaryentity->get_cohort()->id;
        }

        $allcohorts = array_unique(array_merge([$maincohortid], $secondarycohorts));

        // Get user profile fields.
        $userprofilefields = (array) profile_user_record($this->id, false);

        // Get regions list.
        $regionslist = array_map(function($key) {
            return $key->name;
        }, $dbinterfacementor->get_all_regions());

        // Get users cohort by regions.
        $regionscohortslist = [];
        if (isset($userprofilefields['region']) && !empty($userprofilefields['region'])) {
            $regionid           = array_search($userprofilefields['region'], $regionslist);
            $regionscohorts     = $dbinterfacementor->get_cohorts_by_region($regionid);
            $regionscohortslist = array_keys($regionscohorts);
        }

        $allcohorts = array_unique(array_merge($allcohorts, $regionscohortslist));

        $cohortstoadd    = array_diff($allcohorts, $oldcohorts);
        $cohortstoremove = array_diff($oldcohorts, $allcohorts);

        // Add user to cohorts.
        foreach ($cohortstoadd as $toaddcohort) {
            cohort_add_member($toaddcohort, $this->id);
        }

        // Remove user from cohorts.
        foreach ($cohortstoremove as $toremovecohort) {
            cohort_remove_member($toremovecohort, $this->id);
        }

        return true;
    }

    /**
     * Get user secondary entities
     *
     * @return \local_mentor_core\entity[]
     * @throws \moodle_exception
     */
    public function get_secondary_entities() {
        global $CFG;
        require_once($CFG->dirroot . '/local/mentor_core/api/entity.php');

        $secondaryentities = [];

        // Fetch the user id database.
        $user = $this->dbinterface->get_user_by_id($this->id);

        // Check if the secondaryentities has already been set.
        if (property_exists($user, 'secondaryentities')) {
            $secondaryentitiesname = !empty($user->secondaryentities) ? explode(', ', $user->secondaryentities) : [];
        } else {
            $profileuserrecord = profile_user_record($this->id);
            if (property_exists($profileuserrecord, 'secondaryentities')) {
                $secondaryentitiesname = !empty($profileuserrecord->secondaryentities) ?
                    explode(', ', $profileuserrecord->secondaryentities) : [];
            } else {
                $secondaryentitiesname = [];
            }
        }

        foreach ($secondaryentitiesname as $secondaryentityname) {
            $secondaryentities[] = entity_api::get_entity_by_name($secondaryentityname, true);
        }

        return $secondaryentities;
    }

    /**
     * Check if the entity is part of the user's secondary entity list.
     *
     * @param $entityid
     * @return bool
     * @throws \moodle_exception
     */
    public function has_secondary_entity($entityid) {

        // Check if user's secondary entities data exist or is empty.
        if (!$secondaryentities = $this->dbinterface->get_profile_field_value($this->id, 'secondaryentities')) {
            return false;
        }

        // Check if the entity is part of the user's secondary entity list.
        $entity                = \local_mentor_core\entity_api::get_entity($entityid);
        $secondaryentitiesname = explode(', ', $secondaryentities);
        return in_array($entity->get_name(), $secondaryentitiesname);
    }

    /**
     * Get student role
     *
     * @return string
     * @throws \dml_exception
     */
    public function get_student_role() {
        return 'participant';
    }
}
