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
 *  Session API
 *
 * @package    local_mentor_core
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     rcolet <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mentor_core;

use coding_exception;
use core_course\management\helper;
use dml_exception;
use Exception;
use moodle_exception;
use stdClass;

defined('MOODLE_INTERNAL') || die();

define('SESSION_NAME_USED', -1);
define('SESSION_TRAINING_NAME_USED', -2);
define('SESSION_NOT_FOUND', -3);
define('SESSION_ENTITY_NOT_FOUND', -4);
define('SESSION_TRAINING_NAME_EMPTY', -5);

require_once($CFG->dirroot . '/local/mentor_core/classes/database_interface.php');
require_once($CFG->dirroot . '/local/mentor_core/classes/model/session.php');
require_once($CFG->dirroot . '/local/mentor_core/api/entity.php');
require_once($CFG->dirroot . '/local/mentor_core/api/training.php');
require_once($CFG->dirroot . '/local/mentor_core/lib.php');

class session_api {

    private static $sessions = [];

    /**
     * Get a session by id
     *
     * @param int|stdClass $sessionidorinstance
     * @param boolean $refresh - optional default true.
     * @return session
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function get_session($sessionidorinstance, $refresh = true) {

        $sessionid = is_object($sessionidorinstance) ? $sessionidorinstance->id : $sessionidorinstance;

        if ($refresh || !isset(self::$sessions[$sessionid])) {
            $specialization = specialization::get_instance();
            $session = $specialization->get_specialization('get_session', null, $sessionidorinstance);

            // The session has no specialization, use the standard behaviour.
            if (!is_object($session)) {
                $session = new session($sessionidorinstance);
            }

            self::$sessions[$sessionid] = $session;
        }
        return self::$sessions[$sessionid];
    }

    /**
     * Get all entity sessions
     *
     * @param stdClass $data request parameters like status, dates, order...
     * @return array|mixed the list of sessions
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function get_sessions_by_entity($data) {

        $specialization = specialization::get_instance();

        // Get the specialization of the session list.
        $sessions = $specialization->get_specialization('get_sessions_by_entity', null, [
            'data' => $data,
        ]);

        // Return the specialization data.
        if (!is_null($sessions)) {
            return $sessions;
        }

        // Return default data is the function has no specialization.
        $db = database_interface::get_instance();
        $listsessionsrecord = $db->get_sessions_by_entity_id($data);

        $listsession = [];

        foreach ($listsessionsrecord as $sessionrecord) {
            $session = self::get_session($sessionrecord->id, false);

            // Skip the session if the user cannot manage it.
            if (!$session->is_manager()) {
                continue;
            }

            $listsession[] = [
                'id' => $session->id,
                'fullname' => $session->fullname,
                'link' => $session->get_url()->out(),
                'shortname' => $session->shortname,
                'status' => get_string($session->status, 'local_mentor_core'),
                'statusshortname' => $session->status,
                'timecreated' => $session->get_course()->timecreated,
                'shared' => $session->is_shared(),
                'nbparticipant' => $session->numberparticipants,
                'hasusers' => count($session->get_course_users()),
                'actions' => $session->get_actions(),
            ];
        }

        return $listsession;
    }

    /**
     * Get all training sessions
     *
     * @param int $trainingid
     * @param string $orderby
     * @return session[] the list of sessions
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function get_sessions_by_training($trainingid, $orderby = '') {

        $specialization = specialization::get_instance();

        // Get the specialization of the session list.
        $sessions = $specialization->get_specialization('get_sessions_by_training', null, [
            'data' => ['trainingid' => $trainingid, 'orderby' => $orderby]
        ]);

        // Return the specialization data.
        if (!is_null($sessions)) {
            return $sessions;
        }

        // Return default data is the function has no specialization.
        $db = database_interface::get_instance();
        $listsessionsrecord = $db->get_sessions_by_training_id($trainingid, $orderby);

        $listsession = [];

        foreach ($listsessionsrecord as $sessionrecord) {
            // Create session object.
            $listsession[] = self::get_session($sessionrecord->id, false);
        }

        return $listsession;
    }

    /**
     * Count all session record by entity
     *
     * @param stdClass $data
     * @return int|mixed
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function count_session_record($data) {

        $specialization = specialization::get_instance();

        // Get the specialization of the session list.
        $sessioncount = $specialization->get_specialization('count_session_record', null, [
            'data' => $data,
        ]);

        // Return the specialization data.
        if (!is_null($sessioncount)) {
            return $sessioncount;
        }

        // Return default data is the function has no specialization.
        $db = database_interface::get_instance();
        $listsessionsrecord = $db->get_sessions_by_entity_id($data);

        $sessioncount = 0;

        foreach ($listsessionsrecord as $sessionrecord) {
            $session = self::get_session($sessionrecord->id, false);

            // Skip the session if the user cannot manage it.
            if (!$session->is_manager()) {
                continue;
            }

            $sessioncount++;
        }

        return $sessioncount;
    }

    /**
     * Count sessions record by entity id
     *
     * @param stdClass $data
     * @return int session number
     * @throws dml_exception
     */
    public static function count_sessions_by_entity_id($data) {

        $db = database_interface::get_instance();
        $countsessionsrecord = $db->count_sessions_by_entity_id($data);

        $specialization = specialization::get_instance();

        return $specialization->get_specialization('count_sessions_by_entity_id', $countsessionsrecord, [
            'data' => $data,
        ]);
    }

    /**
     * Create a session
     *
     * @param int $trainingid
     * @param string $sessionname
     * @param bool $executenow - true to execute the creation now
     * @param int $entityid - optional default null to use the entity of the training
     * @return session|int a session if $executenow=true or the id of the created as hoc task
     * @throws \restore_controller_exception
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function create_session($trainingid, $sessionname, $executenow = false, $entityid = null) {
        global $USER;

        $training = training_api::get_training($trainingid);

        // Get the asked entity of the training entity.
        $entity = (is_null($entityid) || $entityid == 0) ? $training->get_entity() : entity_api::get_entity($entityid);

        $context = $entity->get_context();

        // Check user capabilities.
        if (!has_capability('local/session:create', $context)) {
            throw new moodle_exception('unauthorisedaction', 'local_mentor_core');
        }

        $dbinterface = database_interface::get_instance();

        // Clear the session name.
        $sessionname = str_replace(['<', '>'], '', $sessionname);

        // Check if session name is not already in use.
        if ($dbinterface->session_exists($sessionname) ||
            $dbinterface->course_shortname_exists($sessionname) ||
            self::is_session_in_recycle_bin($sessionname, $entity->id)) {
            return SESSION_NAME_USED;
        }

        $adhoctask = new \local_mentor_core\task\create_session_task();

        $adhoctask->set_custom_data([
            'trainingid' => $trainingid,
            'sessionname' => $sessionname,
            'entityid' => $entityid
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
     * Move a session into another entity
     *
     * @param int $sessionid
     * @param int $destinationentityid
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function move_session($sessionid, $destinationentityid) {

        // Instantiate the training object.
        $session = self::get_session($sessionid);

        $sessionentity = $session->get_entity();

        if ($sessionentity->id == $destinationentityid) {
            return true;
        }

        // Check user capabilities.
        if (!has_capability('local/session:manage', $sessionentity->get_context())) {
            throw new \Exception(get_string('unauthorisedaction', 'local_mentor_core'), 2020120810);
        }

        $destinationentity = entity_api::get_entity($destinationentityid);

        // Check user capabilities.
        if (!has_capability('local/session:create', $destinationentity->get_context())) {
            throw new \Exception(get_string('unauthorisedaction', 'local_mentor_core'), 2020120810);
        }

        return move_courses([$session->courseid], $destinationentity->get_entity_session_category());
    }

    /**
     * Get next session index of a training
     *
     * @param int $trainingid
     * @return string
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function get_next_training_session_index($trainingid) {
        $dbinterface = database_interface::get_instance();
        $training = training_api::get_training($trainingid);

        return $dbinterface->get_next_training_session_index($training->shortname);
    }

    /**
     * Get next sessionnumber index for a given training
     *
     * @param int $trainingid
     * @return string
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function get_next_sessionnumber_index($trainingid) {
        $dbinterface = database_interface::get_instance();
        return $dbinterface->get_next_sessionnumber_index($trainingid);
    }

    /**
     * Get next available shortname index
     *
     * @param string $shortname
     * @return int
     * @throws dml_exception
     */
    public static function get_next_available_shortname_index($shortname) {
        $dbinterface = database_interface::get_instance();
        $nextindex = 1;
        $shortnameexists = true;

        while ($shortnameexists) {
            $nextshortname = $shortname . ' ' . $nextindex;

            if (!$dbinterface->course_shortname_exists($nextshortname)) {
                $shortnameexists = false;
            } else {
                $nextindex++;
            }

        }

        return $nextindex;
    }

    /**
     * Update a session
     *
     * @param stdClass|session $data
     * @param session_form $form default null, the form is used for filepickers
     * @return session
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function update_session($data, $form = null) {
        global $USER;

        // Get the session object.
        if ($form) {
            $session = self::get_session($form->session->id);
        } else {
            $session = self::get_session($data->id);
        }

        // Check capabilities.
        if (!$session->is_updater($USER)) {
            throw new Exception(get_string('unauthorisedaction', 'local_mentor_core'), 2020120810);
        }

        // Capture the updated fields for the log store data.
        $updatedfields = [];

        foreach (get_object_vars($session) as $field => $value) {

            // Exception data.
            if ($field === 'trainingcontent' ||
                $field === 'traininggoal' ||
                $field === 'creativestructure') {
                continue;
            }

            // Opentolist data.
            // Array to string like data registration.
            if (($field === 'opentolist') && isset($data->$field) && is_array($data->$field)) {
                $collectiondata = implode(',', $data->$field);
                if ($collectiondata == $value) {
                    continue;
                }
            }

            // Other field.
            if (isset($data->$field) && $data->$field != $value) {
                $updatedfields[$field] = $data->$field;
            }
        }

        // Convert presence estimate time form data to minutes.
        if (isset($data->presencesessionestimatedtimehours) && isset($data->presencesessionestimatedtimeminutes)) {
            $presencesessionestimatedtime = (intval($data->presencesessionestimatedtimehours) * 60) + intval
                ($data->presencesessionestimatedtimeminutes);
            if ($presencesessionestimatedtime != $session->presencesessionestimatedtime) {
                $updatedfields['presencesessionestimatedtime'] = $presencesessionestimatedtime;
            }
        }

        // Convert online estimate time form data to minutes.
        if (isset($data->onlinesessionestimatedtimehours) && isset($data->onlinesessionestimatedtimeminutes)) {
            $onlinesessionestimatedtime = (intval($data->onlinesessionestimatedtimehours) * 60) +
                                          intval($data->onlinesessionestimatedtimeminutes);
            if ($onlinesessionestimatedtime != $session->onlinesessionestimatedtime) {
                $updatedfields['onlinesessionestimatedtime'] = $onlinesessionestimatedtime;
            }
        }

        // Clear the session name.
        if (isset($data->fullname)) {
            $data->fullname = str_replace(['<', '>'], '', $data->fullname);
        }

        // Update the session.
        $session->update($data, $form);

        // Trigger an session updated event.
        $event = \local_mentor_core\event\session_update::create(array(
            'objectid' => $session->id,
            'context' => $session->get_context(),
            'other' => array(
                'updatedfields' => $updatedfields
            )
        ));
        $event->set_legacy_logdata(array(
            $session->id, 'session', 'update', 'local/session/pages/update_session.php?sessionid=' . $session->id,
            $session->id
        ));
        $event->trigger();

        // Return a refreshed session object.
        return self::get_session($session->id);
    }

    /**
     * Get session form
     *
     * @param string $url
     * @param $params
     * @return session_form
     * @throws moodle_exception
     */
    public static function get_session_form($url, $params) {
        global $CFG;
        require_once($CFG->dirroot . '/local/mentor_core/forms/session_form.php');

        $form = new session_form($url, $params);
        $specialization = specialization::get_instance();

        return $specialization->get_specialization('get_session_form', $form, $params);
    }

    /**
     * Get the specilization of the session javascript
     *
     * @param string $defaultjs
     * @return mixed
     */
    public static function get_session_javascript($defaultjs) {
        $specialization = specialization::get_instance();
        return $specialization->get_specialization('get_session_javascript', $defaultjs);
    }

    /**
     * Get the specilization of the session template
     *
     * @param string $defaulttemplate
     * @return mixed
     */
    public static function get_session_template($defaulttemplate) {
        $specialization = specialization::get_instance();
        return $specialization->get_specialization('get_session_template', $defaulttemplate);
    }

    /**
     * Get a session by course id
     *
     * @param int $courseid
     * @return bool|session
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function get_session_by_course_id($courseid) {

        $db = database_interface::get_instance();

        if (!$sessiondb = $db->get_session_by_course_id($courseid)) {
            return false;
        }

        return self::get_session($sessiondb->id);
    }

    /**
     * Get the list of session status
     *
     * @return array
     */
    public static function get_status_list() {
        return [
            session::STATUS_IN_PREPARATION => session::STATUS_IN_PREPARATION,
            session::STATUS_OPENED_REGISTRATION => session::STATUS_OPENED_REGISTRATION,
            session::STATUS_IN_PROGRESS => session::STATUS_IN_PROGRESS,
            session::STATUS_COMPLETED => session::STATUS_COMPLETED,
            session::STATUS_ARCHIVED => session::STATUS_ARCHIVED,
            session::STATUS_REPORTED => session::STATUS_REPORTED,
            session::STATUS_CANCELLED => session::STATUS_CANCELLED,
        ];
    }

    /**
     * Cancel the session
     *
     * @param int $sessionid
     * @return bool
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function cancel_session($sessionid) {
        global $USER;

        $session = self::get_session($sessionid);

        // The user cannot update the session.
        if (!$session->is_updater($USER)) {
            return false;
        }

        return $session->update_status('cancelled');
    }

    /**
     * Get all edadmin - session courses that a user can manage
     *
     * @param stdClass|null $user default null
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function get_user_session_courses($user = null) {

        if ($user === null) {
            global $USER;
            $user = $USER;
        }

        $entities = entity_api::get_all_entities(false, [], true);

        $courses = [];

        foreach ($entities as $entity) {

            // Check if the user can manage entity sessions.
            if (has_capability('local/session:manage', $entity->get_context(), $user)) {
                $courses[] = $entity->get_main_entity()->get_edadmin_courses('session');
            }
        }

        return $courses;
    }

    /**
     * Get all available sessions for a given user
     *
     * @param int $userid
     * @return session[]
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function get_user_available_sessions($userid) {
        $db = database_interface::get_instance();

        $useravailablesessions = $db->get_user_available_sessions($userid);

        $sessions = [];

        // Convert stdClass into sessions.
        foreach ($useravailablesessions as $session) {
            $sessions[$session->id] = self::get_session($session);
        }

        // Ordered by session creation.
        krsort($sessions);

        return $sessions;
    }

    /**
     * Check if a user is already enrolled in a session
     *
     * @param int $userid
     * @param int $sessionid
     * @return bool
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function user_is_enrolled($userid, $sessionid) {
        $session = self::get_session($sessionid);
        return $session->user_is_enrolled($userid);
    }

    /**
     * Get all sessions where the user is enrolled or is trainer
     *
     * @param int $userid
     * @param bool $favouritefirst
     * @return stdClass[]
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function get_user_sessions($userid, $favouritefirst = false) {

        // Get database interface.
        $db = database_interface::get_instance();

        // Get all user courses.
        $enrolcourses = $db->get_user_courses($userid);

        // To Save session template by status.
        $enrolsessions = array();
        $enrolsessions['comingsoon'] = array();
        $enrolsessions['inprogress'] = array();
        $enrolsessions['completed'] = array();

        // To sort session by status.
        $sortarray = array();
        $sortarray['comingsoon'] = array();
        $sortarray['inprogress'] = array();
        $sortarray['completed'] = array();

        foreach ($enrolcourses as $enrolcourse) {

            // Check if it's a session.
            if (!$session = self::get_session_by_course_id($enrolcourse->id)) {
                continue;
            }

            // Skip hidden entity.
            if ($session->get_entity()->get_main_entity()->is_hidden()) {
                continue;
            }

            // Set session data for template.
            $lightersessionobject = $session->convert_for_template();
            $lightersessionobject->progress = $session->get_progression($userid);
            if ($lightersessionobject->progress !== false) {
                $lightersessionobject->showProgress = true;
            }

            // Always add the session if the user is a trainer.
            if ($lightersessionobject->istrainer || $lightersessionobject->status !== session::STATUS_IN_PREPARATION) {
                // Sort session by status.
                switch ($lightersessionobject->status) {
                    case session::STATUS_IN_PROGRESS :
                        $enrolsessions['inprogress'][] = $lightersessionobject;
                        $sortarray['inprogress'][] = $lightersessionobject->sessionstartdate ?? 0;
                        break;
                    case session::STATUS_OPENED_REGISTRATION :
                    case session::STATUS_IN_PREPARATION :
                    case session::STATUS_REPORTED :
                        $enrolsessions['comingsoon'][] = $lightersessionobject;
                        $sortarray['comingsoon'][] = $lightersessionobject->sessionstartdate ?? 0;
                        break;
                    default :
                        $enrolsessions['completed'][] = $lightersessionobject;
                        $sortarray['completed'][] = $lightersessionobject->sessionstartdate ?? 0;
                }
            }
        }

        // Sort sessions by status and start date.
        array_multisort($sortarray['inprogress'], SORT_ASC, $enrolsessions['inprogress']);
        array_multisort($sortarray['comingsoon'], SORT_ASC, $enrolsessions['comingsoon']);
        array_multisort($sortarray['completed'], SORT_ASC, $enrolsessions['completed']);

        $usersessions = array_merge($enrolsessions['inprogress'], $enrolsessions['comingsoon'], $enrolsessions['completed']);

        // Favourite is first.
        // Sort by favourite time created.
        if ($favouritefirst) {
            usort($usersessions, "local_mentor_core_usort_favourite_session_first");
        }

        return $usersessions;
    }

    /**
     * Prepare editor data for update session
     *
     * @param session $data
     * @return \stdClass
     */
    public static function prepare_update_session_editor_data($data) {
        $specialization = specialization::get_instance();
        return $specialization->get_specialization('prepare_update_session_editor_data', $data);
    }

    /**
     * Convert update session editor data
     *
     * @param \stdClass $data
     * @return \stdClass
     */
    public static function convert_update_session_editor_data($data) {
        $specialization = specialization::get_instance();
        return $specialization->get_specialization('convert_update_session_editor_data', $data);
    }

    /**
     * Get session enrolment data
     *
     * @param int $sessionid
     * @return \stdClass
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function get_session_enrolment_data($sessionid) {

        $specialization = specialization::get_instance();
        $data = $specialization->get_specialization('get_session_enrolment_data', new stdClass(), $sessionid);

        if (empty((array) $data)) {
            $session = self::get_session($sessionid);
            $data->hasselfregistrationkey = $session->has_registration_key();
        }

        return $data;
    }

    /**
     * Check if course id session course
     *
     * @param int $courseid
     * @return bool
     * @throws dml_exception
     */
    public static function is_session_course($courseid) {
        $dbinterface = \local_mentor_core\database_interface::get_instance();

        return $dbinterface->is_session_course($courseid);
    }

    /**
     * Session shortname is use in recycle bin
     *
     * @param string $shortname
     * @param int $entityid
     * @return bool
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function is_session_in_recycle_bin($shortname, $entityid) {

        // Get entity.
        $entity = \local_mentor_core\entity_api::get_entity($entityid);

        // Check if the recycle bin is enabled.
        if (!\tool_recyclebin\category_bin::is_enabled()) {
            return false;
        }

        // Check if session category recycle bin.
        $categorybin = new \tool_recyclebin\category_bin($entity->get_entity_session_category());

        // Check capabilities.
        if (!$categorybin->can_view()) {
            return false;
        }

        // Get all recycle bin items.
        $autohide = get_config('tool_recyclebin', 'autohide');

        if ($autohide) {
            $items = $categorybin->get_items();
        } else {
            $items = [];
        }

        // Check if session shortname is use in recycle bin.
        foreach ($items as $item) {

            if ($item->shortname === $shortname) {
                return true;
            }
        }

        return false;
    }

    /**
     * Override the default session template params
     *
     * @param \stdClass|null $params
     * @return mixed
     */
    public static function get_session_template_params($params = null) {
        if (is_null($params)) {
            $params = new \stdClass();
        }

        $specialization = specialization::get_instance();
        return $specialization->get_specialization('get_session_template_params', $params);
    }

    /**
     * Restore the deleted course from an entity's session
     *
     * @param int $entityid
     * @param int $itemid item id from recycle bin
     * @param string|null $urlredirect
     * @throws \restore_controller_exception
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function restore_session($entityid, $itemid, $urlredirect = null) {
        global $CFG, $OUTPUT, $PAGE;

        // Get entity.
        $entity = \local_mentor_core\entity_api::get_entity($entityid);

        // Get the entity's sessions recycle bin.
        $sessioncategoryid = $entity->get_entity_session_category();
        $contextsessioncategory = \context_coursecat::instance($sessioncategoryid);
        $recyclebin = new \tool_recyclebin\category_bin($contextsessioncategory->instanceid);

        // Check permissions.
        if (!$recyclebin->can_restore()) {
            // No permissions to restore.
            throw new \moodle_exception('Permission denied to restore a session');
        }

        // Get session's course item.
        $item = $recyclebin->get_item($itemid);

        // Restore session's course.
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
        require_once($CFG->dirroot . '/course/lib.php');
        require_once($CFG->dirroot . '/course/externallib.php');

        $user = get_admin();

        // Get the backup file.
        $fs = get_file_storage();
        $files = $fs->get_area_files($contextsessioncategory->id, 'tool_recyclebin', TOOL_RECYCLEBIN_COURSECAT_BIN_FILEAREA,
            $item->id,
            'itemid, filepath, filename', false);

        if (empty($files)) {
            throw new \moodle_exception('Invalid recycle bin item!');
        }

        if (count($files) > 1) {
            throw new \moodle_exception('Too many files found!');
        }

        // Get the backup file.
        $file = reset($files);

        // Get a backup temp directory name and create it.
        $tempdir = \restore_controller::get_tempdir_name($contextsessioncategory->id, $user->id);
        $fulltempdir = make_backup_temp_directory($tempdir);

        // Extract the backup to tmpdir.
        $fb = get_file_packer('application/vnd.moodle.backup');
        $fb->extract_to_pathname($file, $fulltempdir);

        // Build a course.
        $course = new \stdClass();
        $course->category = $sessioncategoryid;
        $course->shortname = $item->shortname;
        $course->fullname = $item->fullname;
        $course->summary = '';

        // Create a new course.
        $course = create_course($course);

        if (!$course) {
            throw new \moodle_exception("Could not create course to restore into.");
        }

        // Define the import.
        $controller = new \restore_controller(
            $tempdir,
            $course->id,
            \backup::INTERACTIVE_NO,
            \backup::MODE_GENERAL,
            $user->id,
            \backup::TARGET_NEW_COURSE
        );

        // Prechecks.
        if (!$controller->execute_precheck()) {
            $results = $controller->get_precheck_results();

            // Check if errors have been found.
            if (!empty($results['errors'])) {
                // Delete the temporary file we created.
                fulldelete($fulltempdir);

                // Delete the course we created.
                delete_course($course, false);

                echo $OUTPUT->header();
                $backuprenderer = $PAGE->get_renderer('core', 'backup');
                echo $backuprenderer->precheck_notices($results);
                echo $OUTPUT->continue_button(new \moodle_url('/course/index.php', array(
                    'categoryid' =>
                        $contextsessioncategory->id
                )));
                echo $OUTPUT->footer();
                exit();
            }
        }

        // Run the import.
        $controller->execute_plan();

        // Have finished with the controller, let's destroy it, freeing mem and resources.
        $controller->destroy();

        // Fire event.
        $event = \tool_recyclebin\event\category_bin_item_restored::create(array(
            'objectid' => $item->id,
            'context' => $contextsessioncategory
        ));
        $event->add_record_snapshot('tool_recyclebin_category', $item);
        $event->trigger();

        // Cleanup.
        fulldelete($fulltempdir);
        $recyclebin->delete_item($item);

        // Check shortname and fullname course.
        $courseaftercontroller = get_course($course->id);
        if ($item->shortname !== $courseaftercontroller->shortname || $item->fullname !== $courseaftercontroller->fullname) {

            // Restore shortname and fullname course to link with session.
            $courseaftercontroller->shortname = $item->shortname;
            $courseaftercontroller->fullname = $item->fullname;
            update_course($courseaftercontroller);
        }

        if (!is_null($urlredirect)) {
            redirect($urlredirect, get_string('alertrestored', 'local_mentor_core', $item), 2);
        }
    }

    /**
     * Get entity selector to session recycle bin
     *
     * @param int $entityid
     * @return \stdClass
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function entity_selector_sessions_recyclebin($entityid) {
        global $USER, $PAGE, $OUTPUT;

        // Get managed entities if user has any.
        $managedentities = \local_mentor_core\entity_api::get_managed_entities($USER);
        $trainingmanagedentities = \local_mentor_core\training_api::get_entities_training_managed();

        $managedentities = $managedentities + $trainingmanagedentities;

        if (count($managedentities) <= 1) {
            return '';
        }

        // Create an entity selector if it manages several entities.
        $data = new \stdClass();
        $data->switchentities = [];

        foreach ($managedentities as $managedentity) {
            if (!$managedentity->is_main_entity()) {
                continue;
            }
            $entitydata = new \stdClass();
            $entitydata->name = $managedentity->name;
            $entitydata->link = new \moodle_url('/local/session/pages/recyclebin_sessions.php',
                array('entityid' => $managedentity->id));
            $entitydata->selected = $entityid == $managedentity->id;

            // Add the entity to the selector.
            $data->switchentities[] = $entitydata;
        }

        return $data;
    }

    /**
     * Delete the deleted course from an entity's session
     *
     * @param int $entityid
     * @param int $itemid
     * @param string|null $urlredirect
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function remove_session_item($entityid, $itemid, $urlredirect = null) {
        // Get entity.
        $entity = \local_mentor_core\entity_api::get_entity($entityid);

        // Get the entity's sessions recycle bin.
        $sessioncategoryid = $entity->get_entity_session_category();
        $contextsessioncategory = \context_coursecat::instance($sessioncategoryid);
        $recyclebin = new \tool_recyclebin\category_bin($contextsessioncategory->instanceid);

        // Get session's course item.
        $item = $recyclebin->get_item($itemid);

        $dbinterface = database_interface::get_instance();
        $dbinterface->delete_session_sheet($item->shortname);

        // Delete session's course item.
        $recyclebin->delete_item($item);
        if (!is_null($urlredirect)) {
            redirect($urlredirect, get_string('alertdeleted', 'local_mentor_core', $item), 2,
                \core\output\notification::NOTIFY_SUCCESS);
        }
    }

    /**
     * Duplicate a session as a new training
     *
     * @param int $sessionid
     * @param string $trainingfullname
     * @param string $trainingshortname
     * @param int $entityid
     * @return int id of the task
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function duplicate_session_as_new_training($sessionid, $trainingfullname, $trainingshortname, $entityid,
        $executenow = false) {
        global $CFG, $USER;

        require_once($CFG->dirroot . '/local/mentor_core/classes/task/duplicate_training_task.php');
        require_once($CFG->dirroot . '/local/mentor_core/classes/task/duplicate_session_as_new_training_task.php');

        // Clean names.
        $trainingfullname = strip_tags(trim($trainingfullname));
        $trainingshortname = strip_tags(trim($trainingshortname));

        $dbinterface = database_interface::get_instance();

        // Check if the session exists.
        try {
            $dbinterface->get_session_by_id($sessionid);
        } catch (dml_exception $e) {
            return SESSION_NOT_FOUND;
        }

        // Check if the entity exists.
        try {
            $dbinterface->get_course_category_by_id($entityid);
        } catch (dml_exception $e) {
            return SESSION_ENTITY_NOT_FOUND;
        }

        // Check training names.
        if (empty($trainingfullname) || empty($trainingshortname)) {
            return SESSION_TRAINING_NAME_EMPTY;
        }

        // Check if training name is not already in use.
        if ($dbinterface->course_shortname_exists($trainingshortname)) {
            return SESSION_TRAINING_NAME_USED;
        }

        $entity = entity_api::get_entity($entityid);

        $trainingcategoryid = $entity->get_entity_formation_category();

        $context = \context_coursecat::instance($trainingcategoryid);

        // Check user capabilities.
        require_capability('local/trainings:create', $context);

        $adhoctask = new \local_mentor_core\task\duplicate_session_as_new_training_task();

        $adhoctask->set_custom_data([
            'sessionid' => $sessionid,
            'trainingshortname' => $trainingshortname,
            'trainingfullname' => $trainingfullname,
            'entityid' => $entityid,
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

    /**
     * Duplicate the session content into its training course
     *
     * @param int $sessionid
     * @param bool $executenow
     * @return bool
     * @throws \required_capability_exception
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function duplicate_session_into_training($sessionid, $executenow = false) {
        global $CFG, $USER;

        require_once($CFG->dirroot . '/local/mentor_core/classes/task/duplicate_session_into_training_task.php');

        $dbinterface = database_interface::get_instance();

        // Check if the session exists.
        try {
            $dbinterface->get_session_by_id($sessionid);
        } catch (dml_exception $e) {
            return SESSION_NOT_FOUND;
        }

        $session = self::get_session($sessionid);

        $training = $session->get_training();

        // Check capability.
        require_capability('local/trainings:update', $training->get_context());

        $adhoctask = new \local_mentor_core\task\duplicate_session_into_training_task();

        $adhoctask->set_custom_data([
            'sessionid' => $sessionid
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

    /**
     * Get session allowed roles
     *
     * @param int $courseid
     * @return array
     * @throws dml_exception
     */
    public static function get_allowed_roles($courseid) {

        $context = \context_course::instance($courseid);

        // Allowed roles.
        $allowedroles = [
            'participant',
            'tuteur',
            'formateur'
        ];

        $courseroles = [];

        $roles = role_get_names($context);

        foreach ($roles as $role) {
            if (in_array($role->shortname, $allowedroles)) {
                $courseroles[$role->shortname] = $role;
            }
        }

        return $courseroles;
    }

    /**
     * Get all the roles of the session
     *
     * @param int $courseid
     * @return array
     * @throws dml_exception
     */
    public static function get_all_roles($courseid) {
        $context = \context_course::instance($courseid);

        $courseroles = [];

        $roles = role_get_names($context);

        foreach ($roles as $role) {
            $courseroles[$role->shortname] = $role;
        }

        return $courseroles;
    }

    /**
     * Add the user's favorite session
     *
     * @param int $sessionid
     * @param int $userid
     * @return bool|int
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function add_user_favourite_session($sessionid, $userid = null) {
        global $USER;

        if (is_null($userid)) {
            $userid = $USER->id;
        }

        $session = self::get_session($sessionid);

        $db = database_interface::get_instance();
        return $db->add_user_favourite_session($sessionid, $session->get_context()->id, $userid);
    }

    /**
     * Remove the user's favorite session
     *
     * @param int $sessionid
     * @param int $userid
     * @return bool
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function remove_user_favourite_session($sessionid, $userid = null) {
        global $USER;

        if (is_null($userid)) {
            $userid = $USER->id;
        }

        $session = self::get_session($sessionid);

        $db = database_interface::get_instance();
        return $db->remove_user_favourite_session($sessionid, $session->get_context()->id, $userid);
    }

    /**
     * Clear session cache
     */
    public static function clear_cache() {
        self::$sessions = [];
    }

    /**
     * Gives the name of the capability that allows access to the edadmin course of the session format.
     *
     * @return string
     */
    public static function get_edadmin_course_view_capability() {
        return 'local/mentor_core:movesessions';
    }
}
