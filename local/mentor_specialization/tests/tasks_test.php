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
 * Test cases for plugin tasks
 *
 * @package    local_mentor_specialization
 * @copyright  2021 Edunao SAS (contact@edunao.com)
 * @author     Adrien Jamot<adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_mentor_core\session_form;
use local_mentor_core\specialization;
use local_mentor_core\training;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/local/mentor_core/classes/model/session.php');
require_once($CFG->dirroot . '/local/mentor_core/api/session.php');
require_once($CFG->dirroot . '/local/mentor_core/api/profile.php');
require_once($CFG->dirroot . '/local/mentor_specialization/classes/task/archive_sessions.php');
require_once($CFG->dirroot . '/local/mentor_specialization/classes/task/close_sessions.php');
require_once($CFG->dirroot . '/local/mentor_specialization/classes/task/open_sessions.php');

class local_mentor_specialization_tasks_testcase extends advanced_testcase {
    /**
     * Init $CFG
     */
    public function init_config() {
        global $CFG;

        $CFG->mentor_specializations = [
                '\\local_mentor_specialization\\mentor_specialization' =>
                        'local/mentor_specialization/classes/mentor_specialization.php'
        ];
    }

    /**
     * Reset the singletons
     *
     * @throws ReflectionException
     */
    public function reset_singletons() {
        // Reset the mentor core specialization singleton.
        $specialization = \local_mentor_core\specialization::get_instance();
        $reflection = new ReflectionClass($specialization);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true); // Now we can modify that :).
        $instance->setValue(null, null); // Instance is gone.
        $instance->setAccessible(false); // Clean up.

        \local_mentor_core\training_api::clear_cache();
    }

    /**
     * Initialization of the user data
     *
     * @return int
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function init_create_user() {
        global $DB;

        // Create user.
        $user = new stdClass();
        $user->lastname = 'lastname';
        $user->firstname = 'firstname';
        $user->email = 'test@test.com';
        $user->username = 'testusername';
        $user->password = 'to be generated';
        $user->mnethostid = 1;
        $user->confirmed = 1;
        $user->auth = 'manual';

        $userid = local_mentor_core\profile_api::create_user($user);
        set_user_preference('auth_forcepasswordchange', 0, $user);

        $field = $DB->get_record('user_info_field', ['shortname' => 'mainentity']);

        $userdata = new stdClass();
        $userdata->fieldid = $field->id;
        $userdata->data = 'New Entity 1';
        $userdata->userid = $userid;

        $DB->insert_record('user_info_data', $userdata);

        return $userid;
    }

    /**
     * Initialization of the session or trainig data
     *
     * @param false $training
     * @param null $sessionid
     * @return stdClass
     */
    public function init_session_data($training = false, $sessionid = null) {
        $data = new stdClass();

        set_config('collections', 'accompagnement|Accompagnement des transitions professionnelles|#CECECE',
                'local_mentor_specialization');

        if ($training) {
            $data->name = 'fullname';
            $data->shortname = 'shortname';
            $data->content = 'summary';
            $data->status = 'ec';
        } else {
            $data->trainingname = 'fullname';
            $data->trainingshortname = 'shortname';
            $data->trainingcontent = 'summary';
            $data->trainingstatus = 'ec';
        }

        // Fields for taining.
        $data->teaser = 'http://www.edunao.com/';
        $data->teaserpicture = '';
        $data->prerequisite = 'TEST';
        $data->collection = 'accompagnement';
        $data->traininggoal = 'TEST TRAINING ';
        $data->idsirh = 'TEST ID SIRH';
        $data->licenseterms = 'cc-sa';
        $data->typicaljob = 'TEST';
        $data->skills = [];
        $data->certifying = '1';
        $data->presenceestimatedtimehours = '12';
        $data->presenceestimatedtimeminutes = '10';
        $data->remoteestimatedtimehours = '15';
        $data->remoteestimatedtimeminutes = '30';
        $data->trainingmodalities = 'd';
        $data->producingorganization = 'TEST';
        $data->producerorganizationlogo = '';
        $data->designers = 'TEST';
        $data->contactproducerorganization = 'TEST';
        $data->thumbnail = '';

        // Specific fields for session (only for update).
        if ($sessionid) {
            $data->id = $sessionid;
            $data->opento = 'all';
            $data->publiccible = 'TEST';
            $data->termsregistration = 'autre';
            $data->termsregistrationdetail = 'TEST';

            $data->onlinesessionestimatedtimehours = '10';
            $data->onlinesessionestimatedtimeminutes = '15';
            $data->presencesessionestimatedtimehours = '12';
            $data->presencesessionestimatedtimeminutes = '25';

            $data->sessionpermanent = 0;
            $data->sessionstartdate = 1609801200;
            $data->sessionenddate = 1609801200;
            $data->sessionmodalities = 'presentiel';
            $data->accompaniment = 'TEST';
            $data->maxparticipants = 10;
            $data->placesavailable = 8;
            $data->numberparticipants = 2;
            $data->location = 'PARIS';
            $data->organizingstructure = 'TEST ORGANISATION';
            $data->sessionnumber = 1;
            $data->opentolist = '';
        }

        return $data;
    }

    /**
     * Init training creation
     *
     * @return training
     * @throws moodle_exception
     */
    public function init_training_creation() {
        global $DB;

        // Remove the miscelleanous category.
        $DB->delete_records('course_categories', array('id' => 1));

        // Init test data.
        $data = $this->init_session_data(true);

        try {
            // Get entity object for default category.
            $entityid = \local_mentor_core\entity_api::create_entity([
                    'name' => 'New Entity 1',
                    'shortname' => 'New Entity 1',
                    'regions' => [5], // Corse.
                    'userid' => 2  // Set the admin user as manager of the entity.
            ]);

            $entity = \local_mentor_core\entity_api::get_entity($entityid);
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        // Init data with entity data.
        $data = $this->init_training_entity($data, $entity);

        // Test standard training creation.
        try {
            $training = \local_mentor_core\training_api::create_training($data);
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        return $training;
    }

    /**
     * Init session creation
     *
     * @return int
     * @throws moodle_exception
     */
    public function init_session_creation($sessionname = 'TESTUNITCREATESESSION') {
        // Create training.
        $training = $this->init_training_creation();

        // Test standard session creation.
        try {
            $session = \local_mentor_core\session_api::create_session($training->id, $sessionname, true);
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        return $session->id;
    }

    /**
     * Init training categery by entity id
     */
    public function init_training_entity($data, $entity) {
        // Get "Formation" category id (child of entity category).
        $formationid = $entity->get_entity_formation_category();
        $data->categorychildid = $formationid;

        $data->categoryid = $entity->id;
        $data->creativestructure = $entity->id;

        return $data;
    }

    /**
     * Init training category by entity id
     */
    public function init_session_entity($data, $entity) {
        // Get "Formation" category id (child of entity category).
        $formationid = $entity->get_entity_formation_category();
        $data->categorychildid = $formationid;

        $data->categoryid = $entity->id;
        $data->creativestructure = $entity->id;

        return $data;
    }

    /**
     * Test archive sessions
     *
     * @covers \local_mentor_specialization\task\archive_sessions::execute
     * @covers \local_mentor_specialization\task\archive_sessions::get_name
     */
    public function test_archive_sessions() {
        global $DB;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        // Create session.
        $sessionid = $this->init_session_creation('sessionarchive');

        try {
            $session = \local_mentor_core\session_api::get_session($sessionid);
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        $session->update_status(\local_mentor_specialization\mentor_session::STATUS_COMPLETED);

        $DB->update_record('session', ['id' => $sessionid, 'sessionenddate' => 1577833200]);

        $task = new \local_mentor_specialization\task\archive_sessions();

        self::assertEquals($task->get_name(), get_string('task_archive_sessions', 'local_mentor_specialization'));

        $this->expectOutputString('Archive session : ' . $session->id . ' - ' . $session->courseshortname . "\n");
        $task->execute();

        $session = \local_mentor_core\session_api::get_session($session->id);
        self::assertEquals(\local_mentor_specialization\mentor_session::STATUS_ARCHIVED, $session->status);

        self::resetAllData();
    }

    /**
     * Test close sessions
     *
     * @covers \local_mentor_specialization\task\close_sessions::execute
     * @covers \local_mentor_specialization\task\close_sessions::get_name
     */
    public function test_close_sessions() {
        global $DB;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        // Create session.
        $sessionid = $this->init_session_creation('sessionclose');

        try {
            $session = \local_mentor_core\session_api::get_session($sessionid);
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        $session->update_status(\local_mentor_specialization\mentor_session::STATUS_IN_PROGRESS);

        $DB->update_record('session', ['id' => $sessionid, 'sessionenddate' => 1577833200]);

        $task = new \local_mentor_specialization\task\close_sessions();

        self::assertEquals($task->get_name(), get_string('task_close_sessions', 'local_mentor_specialization'));

        $this->expectOutputString('Close session : ' . $session->id . ' - ' . $session->courseshortname . "\n");
        $task->execute();

        $session = \local_mentor_core\session_api::get_session($session->id);

        self::assertEquals(\local_mentor_specialization\mentor_session::STATUS_COMPLETED, $session->status);

        self::resetAllData();
    }

    /**
     * Test open sessions
     *
     * @covers \local_mentor_specialization\task\open_sessions::execute
     * @covers \local_mentor_specialization\task\open_sessions::get_name
     *
     * @throws ReflectionException
     * @throws moodle_exception
     */
    public function test_open_sessions() {
        global $DB;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        // Create session.
        $sessionid = $this->init_session_creation('sessionopen');

        try {
            $session = \local_mentor_core\session_api::get_session($sessionid);
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        $session->update_status(\local_mentor_specialization\mentor_session::STATUS_OPENED_REGISTRATION);

        $DB->update_record('session', ['id' => $session->id, 'sessionstartdate' => 1577833200]);

        $task = new \local_mentor_specialization\task\open_sessions();

        self::assertEquals($task->get_name(), get_string('task_open_sessions', 'local_mentor_specialization'));

        $this->expectOutputString('Open session : ' . $session->id . ' - ' . $session->courseshortname . "\n");
        $task->execute();

        $session = \local_mentor_core\session_api::get_session($session->id);

        // Check the new status.
        self::assertEquals(\local_mentor_specialization\mentor_session::STATUS_IN_PROGRESS, $session->status);

        self::resetAllData();
    }
}

