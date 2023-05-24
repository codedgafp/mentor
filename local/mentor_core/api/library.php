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
 * Class library_api
 *
 * @package    local_mentor_core
 * @copyright  2022 Edunao SAS (contact@edunao.com)
 * @author     Rémi Colet <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mentor_core;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/mentor_core/classes/database_interface.php');
require_once($CFG->dirroot . '/local/mentor_core/classes/model/library.php');
require_once($CFG->libdir . '/licenselib.php');

/**
 * library API
 *
 * @package    local_mentor_core
 * @copyright  2022 Edunao SAS (contact@edunao.com)
 * @author     Rémi Colet <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class library_api {

    /**
     * Get library instance.
     *
     * @return library
     */
    public static function get_library() {
        $specialization = specialization::get_instance();
        $library = $specialization->get_specialization('get_library');

        if (!is_object($library)) {
            $library = \local_mentor_core\library::get_instance();
        }

        return $library;
    }

    /**
     * Create library.
     * Setting library id to config if "setconfig" is true
     *
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function create_library($setconfig = true) {
        $db = \local_mentor_core\database_interface::get_instance();
        $library = $db->get_library_object();

        if ($library) {
            set_config(\local_mentor_core\library::CONFIG_VALUE_ID, $library->id);
            return true;
        }

        $libraryid = \local_mentor_core\entity_api::create_entity(
            array(
                'name' => \local_mentor_core\library::NAME,
                'shortname' => \local_mentor_core\library::SHORTNAME
            )
        );

        if ($setconfig) {
            set_config(\local_mentor_core\library::CONFIG_VALUE_ID, $libraryid);
        }

        return true;
    }

    /**
     * If not exist, create library And set library id to config.
     * After, get library instance.
     *
     * @return library
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function get_or_create_library() {
        // If not exist, create library.
        // And set library id to config.
        self::create_library();

        // Refresh database interface entity cache.
        $db = \local_mentor_core\database_interface::get_instance();
        $db->get_all_entities(true);

        // Return library.
        return self::get_library();
    }

    /**
     * Get library id.
     *
     * @return false|int
     * @throws \dml_exception
     */
    public static function get_library_id() {
        return get_config('core', \local_mentor_core\library::CONFIG_VALUE_ID);
    }

    /**
     * Set library id to config.
     *
     * @return void
     * @throws \dml_exception
     */
    public static function set_library_id_to_config($libraryid) {
        set_config(\local_mentor_core\library::CONFIG_VALUE_ID, $libraryid);
    }

    /**
     * Publish training to library.
     *
     * @param int $trainingid
     * @param bool $executenow
     * @return bool|training
     * @throws \dml_exception
     */
    public static function publish_to_library($trainingid, $executenow = false) {
        global $USER;

        // Get training.
        $training = \local_mentor_core\training_api::get_training($trainingid);

        // Check publish to library capability.
        if (!has_capability('local/library:publish', $training->get_context())) {
            return false;
        }

        $adhoctask = new \local_library\task\publication_library_task();

        $adhoctask->set_custom_data([
            'trainingid' => $trainingid,
            'userid' => $USER->id,
        ]);

        $adhoctask->set_userid($USER->id);

        // Execute the creation now.
        if ($executenow) {
            return $adhoctask->execute();
        }

        // Queued the creation and return true if the task has been created.
        return \core\task\manager::queue_adhoc_task($adhoctask);
    }

    /**
     * Get library publication by original training id.
     *
     * @param int $trainingid
     * @return bool|\stdClass
     * @throws \dml_exception
     */
    public static function get_library_publication($trainingid, $by = 'originaltrainingid') {
        $dbi = \local_mentor_core\database_interface::get_instance();
        return $dbi->get_library_publication($trainingid, $by);
    }

    /**
     * Get original training by library training id.
     *
     * @param $trainingid
     * @return false|training
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function get_original_trainging($trainingid) {
        if (!$librarypublication = self::get_library_publication($trainingid, 'trainingid')) {
            return false;
        }

        return \local_mentor_core\training_api::get_training($librarypublication->originaltrainingid);
    }

    /**
     * Get parameters used by the library renderer
     *
     * @return \stdClass
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function get_params_renderer() {
        global $CFG;

        // Get all collections.
        $collectionsnames = local_mentor_specialization_get_collections();
        $collectionscolors = local_mentor_specialization_get_collections('color');

        // Get library trainings.
        $trainingslibrary = self::get_library()->get_trainings();

        // Fill entities, collections and trainings list.
        $entities = [];
        $collections = [];
        $trainingslibraryparamsrenderer = [];

        foreach ($trainingslibrary as $traininglibrary) {

            $training = \local_mentor_core\training_api::get_training($traininglibrary->id);

            if (!$originaltraining = self::get_original_trainging($traininglibrary->id)) {
                continue;
            }

            $originaltrainingentity = $originaltraining->get_entity(false)
                ->get_main_entity();

            // Set entities list.
            if ('' !== $originaltrainingentity->shortname) {
                $entities[$originaltrainingentity->shortname] = [
                    'id' => $originaltrainingentity->id,
                    'name' => $originaltrainingentity->shortname,
                ];
            }

            // Set collections list.
            foreach (explode(';', $training->collectionstr) as $collection) {
                if ('' !== $collection) {
                    $collections[$collection] = $collection;
                }
            }

            // Set new training renderer params.
            $trainingrenderer = new \stdClass();
            $trainingrenderer->id = $traininglibrary->id;
            $trainingrenderer->trainingsheeturl = $CFG->wwwroot .
                                                  '/local/library/pages/training.php?trainingid=' .
                                                  $training->id;
            $trainingrenderer->name = $training->name;
            $trainingrenderer->thumbnail = $training->get_file_url();
            $trainingrenderer->entityid = $originaltrainingentity->id;
            $trainingrenderer->entityname = $originaltrainingentity->shortname;
            $trainingrenderer->entityfullname = $originaltrainingentity->name;
            $trainingrenderer->producingorganization = $training->producingorganization;
            $trainingrenderer->producerorganizationshortname
                = $training->producerorganizationshortname;
            $trainingrenderer->catchphrase = $training->catchphrase;
            $trainingrenderer->collection = $training->collection;
            $trainingrenderer->collectionstr = $training->collectionstr;
            $trainingrenderer->typicaljob = $training->typicaljob;
            $trainingrenderer->skills = $training->get_skills_name();
            $trainingrenderer->content = html_entity_decode($training->content);
            $trainingrenderer->idsirh = $training->idsirh;
            $trainingtime = $training->presenceestimatedtime + $training->remoteestimatedtime;
            $trainingrenderer->time
                = $trainingtime ?
                local_mentor_core_minutes_to_hours($trainingtime) : 0;
            $trainingrenderer->modality = get_string($training->get_modality_name(), 'local_mentor_specialization');

            // Build collection tiles.
            $trainingrenderer->collectiontiles = [];
            foreach (explode(',', $training->collection) as $collection) {
                // If a collection is missing, we skip.
                if (!isset($collectionsnames[$collection])) {
                    continue;
                }

                $tile = new \stdClass();
                $tile->name = $collectionsnames[$collection];
                $tile->color = $collectionscolors[$collection];
                $trainingrenderer->collectiontiles[] = $tile;
            }

            $trainingslibraryparamsrenderer[$traininglibrary->id] = $trainingrenderer;
        }

        // Set params renderer.
        $paramsrenderer = new \stdClass();

        // Collections list.
        sort($collections);
        $paramsrenderer->collections = array_values($collections);

        // Entities list.
        uksort($entities, 'strcasecmp');
        $paramsrenderer->entities = array_values($entities);

        // Trainings list.
        $paramsrenderer->trainings = array_values($trainingslibraryparamsrenderer);
        $paramsrenderer->trainingscount = count($trainingslibraryparamsrenderer);

        // Json encode amd data.
        $paramsrenderer->available_trainings = json_encode($trainingslibraryparamsrenderer, JSON_HEX_TAG);
        $paramsrenderer->trainings_dictionnary = json_encode(local_catalog_get_dictionnary($trainingslibraryparamsrenderer));

        // Variable used for performance tests.
        $paramsrenderer->isdev = isset($CFG->sitetype) && $CFG->sitetype != 'prod' ? 1 : 0;

        return $paramsrenderer;
    }

    /**
     * Check if the user can access the library
     *
     * @param int|\stdClass $userorid
     * @return bool
     */
    public static function user_has_access($userorid = null) {
        global $USER;

        if (is_null($userorid)) {
            $userorid = $USER;
        }

        $library = self::get_library();
        return has_capability('local/library:view', $library->get_context(), $userorid);
    }

    /**
     * Get training object to training sheet renderer
     *
     * @param \local_mentor_core\training $training
     * @return \stdClass
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function get_training_renderer($training) {

        // Get all collections.
        $collectionsnames = local_mentor_specialization_get_collections();
        $collectionscolors = local_mentor_specialization_get_collections('color');

        $trainingrenderer = $training->convert_for_template();
        $originaltraining = self::get_original_trainging($training->id);
        $trainingrenderer->entityfullname = $originaltraining->get_entity()->get_main_entity()->name;

        // Build collection tiles.
        $trainingrenderer->collectiontiles = [];
        foreach (explode(',', $training->collection) as $collection) {
            // If a collection is missing, we skip.
            if (!isset($collectionsnames[$collection])) {
                continue;
            }

            $tile = new \stdClass();
            $tile->name = $collectionsnames[$collection];
            $tile->color = $collectionscolors[$collection];
            $trainingrenderer->collectiontiles[] = $tile;
        }

        $trainingrenderer->hasproducerorganization = false;

        if (!empty($trainingrenderer->producingorganizationlogo) || !empty($trainingrenderer->producingorganization) || !empty
            ($trainingrenderer->contactproducerorganization)) {
            $trainingrenderer->hasproducerorganization = true;
        }

        $trainingrenderer->presenceestimatedtime = $training->presenceestimatedtime ?
            local_mentor_core_minutes_to_hours($training->presenceestimatedtime) : false;
        $trainingrenderer->remoteestimatedtime = $training->remoteestimatedtime ?
            local_mentor_core_minutes_to_hours($training->remoteestimatedtime) : false;
        $trainingrenderer->modality = get_string($training->get_modality_name(), 'local_mentor_specialization');

        $librarypublication = self::get_library_publication($training->id, 'trainingid');

        $trainingrenderer->timecreated = date('d/m/y', $librarypublication->timecreated);
        if ($librarypublication->timecreated !== $librarypublication->timemodified) {
            $trainingrenderer->timemodified = date('d/m/y', $librarypublication->timemodified);
        }

        return $trainingrenderer;
    }

    /**
     * Import training library to entity
     *
     * @param int $trainingid
     * @param string $trainingshortname
     * @param int $destinationentity
     * @param bool $executenow - true to execute the duplication now
     * @return training|bool the created training
     * @throws \dml_exception
     * @throws \moodle_exception
     * @throws \required_capability_exception
     */
    public static function import_to_entity($trainingid, $trainingshortname, $destinationentity, $executenow = false) {
        global $CFG, $USER;
        require_once($CFG->dirroot . '/local/mentor_core/classes/task/duplicate_training_task.php');
        require_once($CFG->dirroot . '/local/mentor_core/classes/task/duplicate_session_as_new_training_task.php');

        $dbinterface = \local_mentor_core\database_interface::get_instance();

        // Check if session name is not already in use.
        if ($dbinterface->training_exists($trainingshortname)) {
            return TRAINING_NAME_USED;
        }

        // Check if training name is not already in use.
        if ($dbinterface->course_shortname_exists($trainingshortname)) {
            return TRAINING_NAME_USED;
        }

        // Get the training.
        $oldtraining = \local_mentor_core\training_api::get_training($trainingid);

        $course = get_course($oldtraining->courseid);

        $context = \context_coursecat::instance($destinationentity);

        // Check user capabilities.
        if (!has_capability('local/trainings:create', $context) &&
            (!has_capability('local/trainings:createinsubentity', $context) &&
             $oldtraining->status != \local_mentor_core\training::STATUS_TEMPLATE)) {
            throw new \required_capability_exception($context, 'local/trainings:create', 'nopermissions', '');
        }

        $adhoctask = new \local_library\task\import_to_entity_task();

        $adhoctask->set_custom_data([
            'trainingid' => $trainingid,
            'trainingshortname' => $trainingshortname,
            'destinationentity' => $destinationentity,
        ]);

        // Use the current user id to launch the adhoc task.
        $adhoctask->set_userid($USER->id);

        // Execute the task now.
        if ($executenow) {
            return $adhoctask->execute();
        }

        // Queued the task.
        return \core\task\manager::queue_adhoc_task($adhoctask);
    }
}
