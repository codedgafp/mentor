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
 *  Entity controller
 *
 * @package    local_entities
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_entities;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/local/mentor_core/api/entity.php');
require_once($CFG->dirroot . '/local/mentor_core/classes/controllers/controller_base.php');

use moodle_exception;
use local_mentor_core;
use local_mentor_core\controller_base;
use MyProject\void\Level\mixed;

class entity_controller extends controller_base {

    /**
     * Execute action
     *
     * @return array|\stdClass
     * @throws \moodle_exception
     */
    public function execute() {
        $action = $this->get_param('action');

        try {
            switch ($action) {
                case 'create_entity' :
                    return $this->success(self::create_entity($this->params));
                case 'search_main_entities' :
                    $searchtext = $this->get_param('searchtext', PARAM_TEXT);
                    return $this->success(self::search_main_entities($searchtext));
                case 'has_sub_entities' :
                    $entityid = $this->get_param('entityid', PARAM_INT);
                    return $this->success(self::has_sub_entities($entityid));
                case 'get_managed_entities' :
                    $data                  = new \stdClass();
                    $data->start           = 0;
                    $data->length          = 0;
                    $data->search          = false;
                    $data->order           = false;
                    $data->recordsTotal    = self::count_managed_entities();
                    $data->search          = $this->get_param('search', PARAM_RAW, null);
                    $data->recordsFiltered = self::count_managed_entities(null, false, $data);
                    $data->order           = $this->get_param('order', PARAM_RAW, null);
                    $data->order           = is_null($data->order) ? $data->order : $data->order[0];
                    $data->draw            = $this->get_param('draw', PARAM_INT, null);
                    $data->length          = $this->get_param('length', PARAM_INT, null);
                    $data->start           = $this->get_param('start', PARAM_INT, null);
                    $data->data            = self::get_managed_entities(null, false, $data);
                    $data->success         = true;
                    return $data;
                // Add cases here.
                default:
                    return $this->success(self::$action());
            }
        } catch (\dml_exception $e) {
            return $this->error($e->getMessage());
        } catch (moodle_exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Return all entities
     *
     * @return local_mentor_core\entity[]
     * @throws moodle_exception
     */
    public static function get_all_entities() {
        return local_mentor_core\entity_api::get_all_entities();
    }

    /**
     * Check if an entity exists
     *
     * @param string $entityname
     * @return bool
     * @throws \dml_exception
     * @throws moodle_exception
     */
    public static function entity_exists($entityname) {
        return local_mentor_core\entity_api::entity_exists($entityname);
    }

    /**
     * Create new entity with there name
     *
     * @param array $data - entityname, (userid), (regionid)...
     * @return int the entity id
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws moodle_exception
     */
    public static function create_entity($data) {
        return local_mentor_core\entity_api::create_entity($data);
    }

    /**
     * Get entities managed by a user
     *
     * @param null $user
     * @param bool $mainonly
     * @return local_mentor_core\entity[]
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws moodle_exception
     */
    public static function get_managed_entities($user = null, $mainonly = false, $filter = null) {

        $includehidden = is_siteadmin();
        $entities      = local_mentor_core\entity_api::get_managed_entities($user, $mainonly, $filter, true, $includehidden);

        $managedentities = [];

        foreach ($entities as $entity) {
            $entity->create_edadmin_courses_if_missing();
            $managedentities[] = $entity;
        }

        return $managedentities;
    }

    /**
     * Count entities managed by a user
     *
     * @param null $user
     * @param bool $mainonly
     * @return int
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws moodle_exception
     */
    public static function count_managed_entities($user = null, $mainonly = false, $filter = null) {
        return local_mentor_core\entity_api::count_managed_entities($user, $mainonly, $filter, true);
    }

    /**
     * Search among main entities
     *
     * @param string $searchtext
     * @return array
     * @throws \dml_exception
     */
    public static function search_main_entities($searchtext) {
        return local_mentor_core\entity_api::search_main_entities($searchtext, false);
    }

    /**
     * Has subentities
     *
     * @param int $entityd
     * @return bool
     * @throws \dml_exception
     * @throws moodle_exception
     */
    public function has_sub_entities($entityd) {
        return local_mentor_core\entity_api::has_sub_entities($entityd);
    }
}
