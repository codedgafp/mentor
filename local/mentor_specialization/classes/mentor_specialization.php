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

namespace local_mentor_specialization;

use core_course\search\course;
use dml_missing_record_exception;
use local_mentor_core\entity;
use local_mentor_core\profile;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/mentor_specialization/classes/models/mentor_session.php');
require_once($CFG->dirroot . '/local/mentor_specialization/classes/models/mentor_library.php');

/**
 * Mentor specialization file
 *
 * @package    local_mentor_specialization
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     adrien <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mentor_specialization {

    /**
     * Get the specialization of a given action if exists
     *
     * @param string $action
     * @param mixed $object
     * @param mixed $params default null
     * @return mixed
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function get_specialization($action, $object = null, $params = null) {

        switch ($action) {
            case 'get_entity_form' :
                return $this->get_entity_form($params);
            case 'get_training_form' :
                return $this->get_training_form($action, $params);
            case 'get_session_form' :
                return $this->get_session_form($action, $params);
            case 'get_entity' :
                return $this->get_entity($object);
            case 'get_training' :
                return $this->get_training($object);
            case 'get_session' :
                return $this->get_session($params);
            case 'get_profile' :
                return $this->get_profile($object);
            case 'get_entity_form_fields' :
                return $this->get_entity_form_fields($object);
            case 'get_sub_entity_form_fields' :
                return $this->get_sub_entity_form_fields($object);
            case 'get_trainings_template' :
                return $this->get_trainings_template($object);
            case 'get_user_template' :
                return $this->get_user_template($object);
            case 'get_trainings_by_entity':
                return $this->get_trainings_by_entity($params['data']);
            case 'count_trainings_by_entity':
                return $this->count_trainings_by_entity($params['data']);
            case 'get_user_template_params' :
                return $this->get_user_template_params($object);
            case 'get_training_template_params' :
                return $this->get_training_template_params($object);
            case 'get_session_template_params' :
                return $this->get_session_template_params($object);
            case 'get_session_template' :
                return $this->get_session_template($object);
            case 'get_trainings_javascript' :
                return $this->get_trainings_javascript($object);
            case 'get_session_javascript' :
                return $this->get_session_javascript($object);
            case 'get_entity_javascript' :
                return $this->get_entity_javascript($object);
            case 'get_user_javascript' :
                return $this->get_user_javascript($object);
            case 'get_sessions_by_entity':
                return $this->get_sessions_by_entity($params['data']);
            case 'count_session_record':
                return $this->count_session_record($params['data']);
            case 'get_session_enrolment_data':
                return $this->get_session_enrolment_data($object, $params);
            case 'get_user_available_sessions_by_trainings':
                return $this->get_user_available_sessions_by_trainings($object, $params['userid'], $params['onlytrainings']);
            case 'count_sessions_by_entity_id':
                return $this->count_sessions_by_entity_id($params['data']);
            case 'prepare_update_session_editor_data':
                return $this->prepare_update_session_editor_data($object);
            case 'convert_update_session_editor_data':
                return $this->convert_update_session_editor_data($object);
            case 'get_params_renderer_catalog':
                return $this->get_params_renderer_catalog($object);
            case 'get_footer':
                return $this->get_footer($object);
            case 'get_training_sheet_template':
                return $this->get_training_sheet_template($object);
            case 'get_session_sheet_template':
                return $this->get_session_sheet_template($object);
            case 'get_user_manager_role_name':
                return $this->get_user_manager_role_name();
            case 'create_sub_entity':
                return $this->create_sub_entity($object, $params);
            case 'get_signup_url':
                return $this->get_signup_url($object);
            case 'get_library':
                return $this->get_library();
            default:
                return $object;
                break;
        }
    }

    /**
     * Get mentor specific form
     *
     * @param string $url
     * @return entity_form
     */
    public function get_entity_form($url) {
        global $CFG;
        require_once($CFG->dirroot . '/local/mentor_specialization/forms/entity_form.php');
        return new entity_form($url);
    }

    /**
     * Get training mentor specific form
     *
     * @param string $url
     * @param $obj
     * @return training_form
     * @throws \moodle_exception
     */
    public function get_training_form($url, $obj) {
        global $CFG;
        require_once($CFG->dirroot . '/local/mentor_specialization/forms/training_form.php');
        return new training_form($url, $obj);
    }

    /**
     * Get session mentor specific form
     *
     * @param string $url
     * @param $obj
     * @return session_form
     * @throws \moodle_exception
     */
    public function get_session_form($url, $obj) {
        global $CFG;
        require_once($CFG->dirroot . '/local/mentor_specialization/forms/session_form.php');
        return new session_form($url, $obj);
    }

    /**
     * Get a mentor entity from a core entity
     *
     * @param entity $entity
     * @return mentor_entity
     * @throws \moodle_exception
     */
    public function get_entity($entityid) {
        global $CFG;
        require_once($CFG->dirroot . '/local/mentor_specialization/classes/models/mentor_entity.php');
        return new mentor_entity($entityid);
    }

    /**
     * Get a mentor training from a core training
     *
     * @param int $trainingid
     * @return mentor_training
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function get_training($trainingid) {
        global $CFG;
        require_once($CFG->dirroot . '/local/mentor_specialization/classes/models/mentor_training.php');
        return new mentor_training($trainingid);
    }

    /**
     * Get a mentor session from a core session
     *
     * @param \stdClass $sessionid
     * @return mentor_session
     * @throws \moodle_exception
     */
    public function get_session($sessionid) {
        global $CFG;
        require_once($CFG->dirroot . '/local/mentor_specialization/classes/models/mentor_session.php');
        return new mentor_session($sessionid);
    }

    /**
     * Get a mentor profile
     *
     * @param profile $profile
     * @return mentor_profile
     * @throws \moodle_exception
     */
    public function get_profile($profile) {
        global $CFG;
        require_once($CFG->dirroot . '/local/mentor_specialization/classes/models/mentor_profile.php');
        return new mentor_profile($profile);
    }

    /**
     * Get entity extra form fields
     *
     * @param string $extrahtml
     * @return string html
     */
    public function get_entity_form_fields($extrahtml) {
        global $PAGE;

        $renderer = $PAGE->get_renderer('local_mentor_specialization', 'entity');

        // Add the specific fields to the form html.
        return $extrahtml . $renderer->get_entity_form_fields();
    }

    /**
     * Get sub entity extra form fields
     *
     * @param string $extrahtml
     * @return string html
     */
    public function get_sub_entity_form_fields($extrahtml) {
        global $PAGE;

        $renderer = $PAGE->get_renderer('local_mentor_specialization', 'entity');

        // Add the specific fields to the form html.
        return $extrahtml . $renderer->get_sub_entity_form_fields();
    }

    /**
     * Override the default trainings template
     *
     * @param string $defaulttemplate
     * @return string
     */
    public function get_trainings_template($defaulttemplate) {
        return 'local_mentor_specialization/trainings';
    }

    /**
     * Override the default user template params
     *
     * @param array $params
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function get_user_template_params($params) {
        $dbinterface = \local_mentor_specialization\database_interface::get_instance();

        $listmainentities = \local_mentor_core\entity_api::get_all_entities(true, [], true, null, false, false);

        // Sort entity by shortname.
        uasort($listmainentities, function($a, $b) {
            return strcmp(local_mentor_core_sanitize_string($a->shortname), local_mentor_core_sanitize_string($b->shortname));
        });

        $params['mainentities'] = array_merge([0 => ''], $listmainentities);
        $listsecondaryentities = \local_mentor_core\entity_api::get_all_entities(true, [], true, null, false);

        // Sort entity by shortname.
        uasort($listsecondaryentities, function($a, $b) {
            return strcmp(local_mentor_core_sanitize_string($a->shortname), local_mentor_core_sanitize_string($b->shortname));
        });

        $params['secondarymainentities'] = array_merge([0 => ''], $listsecondaryentities);

        $noregion = new \stdClass();
        $noregion->id = 0;
        $noregion->name = get_string('none', 'local_mentor_core');
        $regions = $dbinterface->get_all_regions();
        $regionsoptions = array_merge([$noregion], $regions);
        $params['regions'] = $regionsoptions;
        return $params;
    }

    /**
     * Override the default training template params
     *
     * @param \stdClass $params
     * @return \stdClass
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_training_template_params($params) {

        // Create collection list.
        $collections = local_mentor_specialization_get_collections();
        $params->collections = [];
        foreach ($collections as $key => $val) {
            $params->collections[] = array("key" => $key, "value" => $val);
        }

        // Create status liste.
        $status = \local_mentor_core\training_api::get_status_list();
        $params->status = [];
        foreach ($status as $key => $val) {
            $params->status[] = array("key" => $key, "value" => get_string($val, 'local_trainings'));
        }
        return $params;
    }

    /**
     * Override the default training template params
     *
     * @param \stdClass $params
     * @return \stdClass
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_session_template_params($params) {

        // Create collection list.
        $collections = local_mentor_specialization_get_collections();
        $params->collections = [];
        foreach ($collections as $key => $val) {
            $params->collections[] = array("key" => $key, "value" => $val);
        }

        // Create status liste.
        $status = \local_mentor_core\session_api::get_status_list();
        $params->status = [];
        foreach ($status as $key => $val) {
            $params->status[] = array("key" => $key, "value" => get_string($val, 'local_mentor_specialization'));
        }
        return $params;
    }

    /**
     * Override the default user template
     *
     * @param string $defaulttemplate
     * @return string
     */
    public function get_user_template($defaulttemplate) {
        return 'local_mentor_specialization/user';
    }

    /**
     * Override the default session template
     *
     * @param string $defaulttemplate
     * @return string
     */
    public function get_session_template($defaulttemplate) {
        return 'local_mentor_specialization/session';
    }

    /**
     * Override the default trainings javascript
     *
     * @param string $defaultjs
     * @return string
     */
    public function get_trainings_javascript($defaultjs) {
        return 'local_mentor_specialization/trainings';
    }

    /**
     * Override the default user javascript
     *
     * @param string $defaultjs
     * @return string
     */
    public function get_user_javascript($defaultjs) {
        return 'local_mentor_specialization/user';
    }

    /**
     * Override the default session javascript
     *
     * @param string $defaultjs
     * @return string
     */
    public function get_session_javascript($defaultjs) {
        return 'local_mentor_specialization/session';
    }

    /**
     * Override the default entity javascript
     *
     * @param string $defaultjs
     * @return string
     */
    public function get_entity_javascript($defaultjs) {
        return 'local_mentor_specialization/entities';
    }

    /**
     * Get sessions by entity
     *
     * @param \stdClass $data
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function get_sessions_by_entity($data) {

        $db = database_interface::get_instance();
        $listsessionsrecord = $db->get_sessions_by_entity_id($data);
        $listsession = array();
        $trainings = array();

        foreach ($listsessionsrecord as $sessionrecord) {

            $session = \local_mentor_core\session_api::get_session($sessionrecord->id, false);

            // Check if user manage session.
            if (!$session->is_manager()) {
                continue;
            }

            if (!isset($trainings[$session->trainingid])) {
                try {
                    $trainings[$session->trainingid] = $session->get_training();
                } catch (dml_missing_record_exception $e) {
                    // When the course does not exist in the database.
                    continue;
                }
            }

            $entity = $session->get_entity();

            $listsession[] = array(
                'id' => $session->id,
                'link' => $session->get_url()->out(),
                'shortname' => $session->shortname,
                'status' => get_string($session->status, 'local_mentor_core'),
                'statusshortname' => $session->status,
                'timecreated' => $session->sessionstartdate,
                'nbparticipant' => $sessionrecord->numberparticipants,
                'shared' => $session->is_shared(),
                'trainingfullname' => $trainings[$session->trainingid]->name,
                'subentityname' => !$entity->is_main_entity() ? $entity->get_name() : '',
                'sessionname' => $session->get_course()->fullname,
                'actions' => $session->get_actions(),
                'sessionnumber' => '#' . $session->sessionnumber,
                'collection' => $session->collection,
                'collectionstr' => str_replace(';', "<br/>", $trainings[$session->trainingid]->collectionstr),
                'entityid' => $entity->id,
                'maxparticipants' => $sessionrecord->maxparticipants,
            );

        }

        if ($data->order) {
            switch ($data->order['column']) {
                case 7:
                    // Order by shared.
                    usort($listsession, function($a, $b) use ($data) {
                        if ($data->order['dir'] === 'asc') {
                            // True first.
                            return (($a['shared'] === $b['shared']) ? 0 : $a['shared']) ? -1 : 1;
                        }

                        // False first.
                        return (($a['shared'] === $b['shared']) ? 0 : $a['shared']) ? 1 : -1;
                    });
                    break;
                case 8 :
                    // Order by status.
                    usort($listsession, function($a, $b) use ($data) {
                        if ($data->order['dir'] === 'asc') {
                            // Acs.
                            return strnatcmp($a['status'], $b['status']);
                        }

                        // Desc.
                        return strnatcmp($b['status'], $a['status']);
                    });
                    break;
                default:
                    break;
            }
        }

        return $listsession;
    }

    /**
     * Count all session record by entity
     *
     * @param \stdClass $data
     * @return int|mixed
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function count_session_record($data) {
        $db = database_interface::get_instance();
        $listsessionsrecord = $db->get_sessions_by_entity_id($data);
        $countsession = count($listsessionsrecord);

        foreach ($listsessionsrecord as $sessionrecord) {
            $context = \context_course::instance($sessionrecord->courseid);

            // Check if user manage session.
            if (has_capability('local/session:manage', $context)) {
                continue;
            }

            $countsession--;
        }

        return $countsession;
    }

    /**
     * Count session by entity id
     *
     * @param \stdClass $data
     * @return int
     * @throws \dml_exception
     */
    public function count_sessions_by_entity_id($data) {
        $db = database_interface::get_instance();
        return $db->count_sessions_by_entity_id($data);
    }

    /**
     * Prepare editor data for update session
     *
     * @param \stdClass $data
     * @return \stdClass
     */
    public function prepare_update_session_editor_data($data) {

        $data->termsregistrationdetail = [
            'text' => $data->termsregistrationdetail,
            'format' => FORMAT_HTML
        ];

        $data->placesavailable = $data->placesavailable < 0 ? '<span style="color: red;">' . $data->placesavailable . '</span>' :
            $data->placesavailable;

        return $data;
    }

    /**
     * Convert update session editor data
     *
     * @param \stdClass $data
     * @return \stdClass
     */
    public function convert_update_session_editor_data($data) {
        $data->termsregistrationdetail = $data->termsregistrationdetail['text'];
        return $data;
    }

    /**
     * Prepare parameters renderer for catalog
     *
     * @param \stdClass $paramsrenderer
     * @return \stdClass
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function get_params_renderer_catalog($paramsrenderer) {
        global $USER, $CFG;

        // Get trainings with its sessions for the current user.
        $trainings = \local_mentor_core\training_api::get_user_available_sessions_by_trainings($USER->id, true);

        // Get all collections.
        $collectionsnames = local_mentor_specialization_get_collections();
        $collectionscolors = local_mentor_specialization_get_collections('color');

        // Fill entities and collections list.
        $entities = [];
        $collections = [];
        foreach ($trainings as $idx => $training) {
            if ('' !== $training->entityname) {
                $entities[$training->entityname] = [
                    'id' => $training->entityid,
                    'name' => $training->entityname,
                ];
            }

            foreach (explode(';', $training->collectionstr) as $collection) {
                if ('' !== $collection) {
                    $collections[$collection] = $collection;
                }
            }

            // Build collection tiles.
            $trainings[$idx]->collectiontiles = [];
            foreach (explode(',', $training->collection) as $collection) {
                // If a collection is missing, we skip.
                if (!isset($collectionsnames[$collection])) {
                    continue;
                }

                $tile = new \stdClass();
                $tile->name = $collectionsnames[$collection];
                $tile->color = $collectionscolors[$collection];
                $trainings[$idx]->collectiontiles[] = $tile;
            }
        }

        // Collections list.
        sort($collections);
        $paramsrenderer->collections = array_values($collections);

        // Entities list.
        uksort($entities, 'strcasecmp');
        $paramsrenderer->entities = array_values($entities);

        // Trainings list.
        $paramsrenderer->trainings = array_values($trainings);
        $paramsrenderer->trainingscount = count($trainings);

        // Json encode amd data.
        $paramsrenderer->available_trainings = json_encode($trainings, JSON_HEX_TAG);
        $paramsrenderer->trainings_dictionnary = json_encode(local_catalog_get_dictionnary($trainings));

        // Variable used for performance tests.
        $paramsrenderer->isdev = isset($CFG->sitetype) && $CFG->sitetype != 'prod' ? 1 : 0;

        return $paramsrenderer;
    }

    /**
     * Get all available sessions by trainings for a given user
     *
     * @param array $trainings
     * @param int $userid
     * @param bool $onlytrainings
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function get_user_available_sessions_by_trainings($trainings, $userid, $onlytrainings = false) {
        global $CFG;
        require_once($CFG->dirroot . '/local/mentor_core/api/session.php');

        $db = \local_mentor_specialization\database_interface::get_instance();
        $sessions = $db->get_user_available_sessions($userid);

        foreach ($sessions as $session) {

            // Fetch the training for the first time.
            if (!isset($trainings[$session->trainingid])) {
                // Get a light version of the training.
                try {
                    $training = self::get_training($session->trainingid);
                    $trainingmainentity = $training->get_entity(false)
                        ->get_main_entity();

                    // Skip hidden entities.
                    if ($trainingmainentity->is_hidden()) {
                        continue;
                    }

                    $trainings[$session->trainingid] = new \stdClass();
                    $trainings[$session->trainingid]->id = $training->id;
                    $trainings[$session->trainingid]->trainingsheeturl = $CFG->wwwroot .
                        '/local/catalog/pages/training.php?trainingid=' .
                        $training->id;
                    $trainings[$session->trainingid]->name = $training->name;
                    $trainings[$session->trainingid]->thumbnail = $training->get_file_url();
                    $trainings[$session->trainingid]->entityid = $trainingmainentity->id;
                    $trainings[$session->trainingid]->entityname = $trainingmainentity->shortname;
                    $trainings[$session->trainingid]->entityfullname = $trainingmainentity->name;
                    $trainings[$session->trainingid]->producingorganization = $training->producingorganization;
                    $trainings[$session->trainingid]->producerorganizationshortname = $training->producerorganizationshortname;
                    $trainings[$session->trainingid]->catchphrase = $training->catchphrase;
                    $trainings[$session->trainingid]->collection = $training->collection;
                    $trainings[$session->trainingid]->collectionstr = $training->collectionstr;
                    $trainings[$session->trainingid]->typicaljob = $training->typicaljob;
                    $trainings[$session->trainingid]->skills = $training->get_skills_name();
                    $trainings[$session->trainingid]->content = html_entity_decode($training->content);
                    $trainings[$session->trainingid]->idsirh = $training->idsirh;
                } catch (dml_missing_record_exception $e) {
                    // When the course does not exist in the database.
                    continue;
                }

                $trainings[$session->trainingid]->sessions = [];
                $trainings[$session->trainingid]->haspermanentsessions = false;
                $trainings[$session->trainingid]->hasinprogresssessions = false;
            }

            $session->timecreated = intval($session->timecreated);

            // Add session time created to data training.
            if (!isset($trainings[$session->trainingid]->lastsessiontimecreated)) {
                $trainings[$session->trainingid]->lastsessiontimecreated = $session->timecreated;
            }

            // Add last session created time to data training.
            if ($trainings[$session->trainingid]->lastsessiontimecreated < $session->timecreated) {
                $trainings[$session->trainingid]->lastsessiontimecreated = $session->timecreated;
            }

            // Get a light version of the session.
            if (!$onlytrainings) {
                $sessionobject = \local_mentor_core\session_api::get_session($session->id);
                $trainings[$session->trainingid]->sessions[] = $sessionobject->convert_for_template();
            }

            // Check if is permanent session.
            if (false === $trainings[$session->trainingid]->haspermanentsessions
                && '1' === $session->sessionpermanent
            ) {
                $trainings[$session->trainingid]->haspermanentsessions = true;
            }

            // Check if is session in progress.
            if (false === $trainings[$session->trainingid]->hasinprogresssessions
                && \local_mentor_specialization\mentor_session::STATUS_IN_PROGRESS === $session->status
            ) {
                $trainings[$session->trainingid]->hasinprogresssessions = true;
            }
        }

        usort($trainings, function($a, $b) {

            // Same session time created : Order by training name.
            if ($a->lastsessiontimecreated === $b->lastsessiontimecreated) {
                return strcmp($a->name, $b->name);
            }

            // Order by last session time created (DESC).
            return ($a->lastsessiontimecreated < $b->lastsessiontimecreated) ? 1 : -1;
        });

        return $trainings;
    }

    /**
     * Get the specialization of the page footer
     *
     * @param string $html
     * @return string
     */
    public function get_footer($html) {
        global $CFG;

        require_once($CFG->dirroot . '/local/mentor_specialization/lib.php');

        // Add the regions and departments encoded as json.
        $html .= '<div id="regions" style="display:none;">' . json_encode
            (local_mentor_specialization_get_regions_and_departments()) . '</div>';

        return $html;
    }

    /**
     *
     * Get session enrolment data
     *
     * @param \stdClass $data
     * @param int $sessionid
     * @return \stdClass
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function get_session_enrolment_data($data, $sessionid) {

        $session = \local_mentor_core\session_api::get_session($sessionid);

        // Self enrolment.
        if ($session->termsregistration == 'inscriptionlibre') {
            $data->selfenrolment = 1;
            $data->hasselfregistrationkey = $session->has_registration_key();
        } else {
            $data->selfenrolment = 0;
            $data->message = $session->termsregistrationdetail;
        }

        return $data;
    }

    /**
     * Return training sheet template namespace
     *
     * @param $object
     * @return string
     */
    private function get_training_sheet_template($object) {
        return 'local_mentor_specialization/catalog/training-sheet';
    }

    /**
     * Return session sheet template namespace
     *
     * @param $object
     * @return string
     */
    private function get_session_sheet_template($object) {
        return 'local_mentor_specialization/catalog/session-sheet';
    }

    /**
     * Return users manager role name
     *
     * @return string
     */
    private function get_user_manager_role_name() {
        return 'admindedie';
    }

    /**
     * Create a sub entity extension
     *
     * @param mentor_entity $newentity
     * @param array $formdata
     * @return string
     * @throws \coding_exception
     * @throws \dml_exception
     */
    private function create_sub_entity($newentity, $formdata) {

        // Check if ref local is selected.
        if (!isset($formdata['reflocalid']) || $formdata['reflocalid'] === 0 || $formdata['reflocalid'] === null) {
            return $newentity->id;
        }

        // Assign ref local role to user.
        $db = \local_mentor_core\database_interface::get_instance();
        $reflocalrole = $db->get_role_by_name('referentlocal');
        role_assign($reflocalrole->id, $formdata['reflocalid'], $newentity->get_context());

        return $newentity->id;
    }

    /**
     * Get sessions by entity
     *
     * @param \stdClass $data
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function get_trainings_by_entity($data) {
        global $USER;

        $db = \local_mentor_specialization\database_interface::get_instance();

        $onlymainentity = isset($data->onlymainentity) ? $data->onlymainentity : false;

        // Get all entity trainings objects.
        $trainingsrecord = $db->get_trainings_by_entity_id($data, $onlymainentity);

        // Format trainings as array.
        $trainingsarray = array();

        foreach ($trainingsrecord as $key => $training) {
            $training = self::get_training($training->id);
            $trainingentity = $training->get_entity(false);

            // The user has access if it is a master entity or if he manages formations on this entity or its sub-entity.
            if ($trainingentity->is_main_entity() || $trainingentity->is_trainings_manager($USER)) {
                $trainingsarray[] = array(
                    'id' => $training->id,
                    'name' => $training->name,
                    'idsirh' => $training->idsirh,
                    'status' => $training->status,
                    'entityid' => $trainingentity->id,
                    'subentityname' => !$trainingentity->is_main_entity() ?
                        $trainingentity->get_name() : '',
                    'collectionstr' => $training->collectionstr,
                    'url' => $training->get_url()->out(),
                    'actions' => $training->get_actions(),
                    'shortname' => $training->courseshortname,
                    'sessions' => $training->get_session_number(),
                    'urlsessions' => $trainingentity->get_main_entity()->get_edadmin_courses('session')['link'] .
                        '&trainingid=' . $training->id,

                );
            }
        }

        // Sort order.
        if (isset($data->order) && $data->order) {
            switch ($data->order['column']) {
                case 4 : // Status.
                case 5 : // Sessions.
                    $colname = ($data->order['column'] === 4) ? 'status' : 'sessions';
                    usort($trainingsarray, function($a, $b) use ($data, $colname) {
                        if ($data->order['dir'] === 'asc') {
                            // Asc.
                            return strnatcmp($a[$colname], $b[$colname]);
                        }

                        // Desc.
                        return strnatcmp($b[$colname], $a[$colname]);
                    });
                    break;
                default:
                    break;
            }
        }

        return array_values($trainingsarray);
    }

    /**
     * Count sessions by entity
     *
     * @param \stdClass $data
     * @return int
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function count_trainings_by_entity($data) {
        $onlymainentity = isset($data->onlymainentity) ? $data->onlymainentity : false;

        // Count all entity trainings objects.
        $db = \local_mentor_specialization\database_interface::get_instance();
        return $db->count_trainings_by_entity_id($data, $onlymainentity);
    }

    /**
     * Return first signup url page
     * (Verify email page)
     *
     * @return \moodle_url
     * @throws \moodle_exception
     */
    private function get_signup_url() {
        return new \moodle_url('/theme/mentor/pages/verify_email.php');
    }

    /**
     * Get mentor_library
     *
     * @return \local_mentor_core\library
     * @throws \moodle_exception
     */
    private function get_library() {
        return \local_mentor_specialization\mentor_library::get_instance();
    }
}

