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
 * Test cases for training API
 *
 * @package    local_mentor_core
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     rcolet <remi.colet@edunao.com>
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

class local_mentor_specialization_session_testcase extends advanced_testcase {

    public const UNAUTHORISED_CODE = 2020120810;
    public const DEFAULT_USER      = 2;

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
        $reflection     = new ReflectionClass($specialization);
        $instance       = $reflection->getProperty('instance');
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
        $user             = new stdClass();
        $user->lastname   = 'lastname';
        $user->firstname  = 'firstname';
        $user->email      = 'test@test.com';
        $user->username   = 'testusername';
        $user->password   = 'to be generated';
        $user->mnethostid = 1;
        $user->confirmed  = 1;
        $user->auth       = 'manual';

        $userid = local_mentor_core\profile_api::create_user($user);
        set_user_preference('auth_forcepasswordchange', 0, $user);

        $field = $DB->get_record('user_info_field', ['shortname' => 'mainentity']);

        $userdata          = new stdClass();
        $userdata->fieldid = $field->id;
        $userdata->data    = 'New Entity 1';
        $userdata->userid  = $userid;

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
            $data->name      = 'fullname';
            $data->shortname = 'shortname';
            $data->content   = 'summary';
            $data->status    = 'ec';
        } else {
            $data->trainingname      = 'fullname';
            $data->trainingshortname = 'shortname';
            $data->trainingcontent   = 'summary';
            $data->trainingstatus    = 'ec';
        }

        // Fields for taining.
        $data->teaser                       = 'http://www.edunao.com/';
        $data->teaserpicture                = '';
        $data->prerequisite                 = 'TEST';
        $data->collection                   = 'accompagnement';
        $data->traininggoal                 = 'TEST TRAINING ';
        $data->idsirh                       = 'TEST ID SIRH';
        $data->licenseterms                 = 'cc-sa';
        $data->typicaljob                   = 'TEST';
        $data->skills                       = [];
        $data->certifying                   = '1';
        $data->presenceestimatedtimehours   = '12';
        $data->presenceestimatedtimeminutes = '10';
        $data->remoteestimatedtimehours     = '15';
        $data->remoteestimatedtimeminutes   = '30';
        $data->trainingmodalities           = 'd';
        $data->producingorganization        = 'TEST';
        $data->producerorganizationlogo     = '';
        $data->designers                    = 'TEST';
        $data->contactproducerorganization  = 'TEST';
        $data->thumbnail                    = '';

        // Specific fields for session (only for update).
        if ($sessionid) {
            $data->id                      = $sessionid;
            $data->opento                  = 'all';
            $data->publiccible             = 'TEST';
            $data->termsregistration       = 'autre';
            $data->termsregistrationdetail = 'TEST';

            $data->onlinesessionestimatedtimehours     = '10';
            $data->onlinesessionestimatedtimeminutes   = '15';
            $data->presencesessionestimatedtimehours   = '12';
            $data->presencesessionestimatedtimeminutes = '25';

            $data->sessionpermanent    = 0;
            $data->sessionstartdate    = 1609801200;
            $data->sessionenddate      = 1609801200;
            $data->sessionmodalities   = 'presentiel';
            $data->accompaniment       = 'TEST';
            $data->maxparticipants     = 10;
            $data->placesavailable     = 8;
            $data->numberparticipants  = 2;
            $data->location            = 'PARIS';
            $data->organizingstructure = 'TEST ORGANISATION';
            $data->sessionnumber       = 1;
            $data->opentolist          = '';
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
                'name'      => 'New Entity 1',
                'shortname' => 'New Entity 1',
                'regions'   => [5], // Corse.
                'userid'    => 2  // Set the admin user as manager of the entity.
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
    public function init_session_creation() {
        // Create training.
        $training = $this->init_training_creation();

        $sessionname = 'TESTUNITCREATESESSION';

        // Test standard session creation.
        try {
            $session = \local_mentor_core\session_api::create_session($training->id, $sessionname, true);
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        // Open to current entity.
        $data         = new stdClass();
        $data->opento = 'current_entity';
        $session->update($data);

        return $session->id;
    }

    /**
     * Init training categery by entity id
     */
    public function init_training_entity($data, $entity) {
        // Get "Formation" category id (child of entity category).
        $formationid           = $entity->get_entity_formation_category();
        $data->categorychildid = $formationid;

        $data->categoryid        = $entity->id;
        $data->creativestructure = $entity->id;

        return $data;
    }

    /**
     * Init training category by entity id
     */
    public function init_session_entity($data, $entity) {
        // Get "Formation" category id (child of entity category).
        $formationid           = $entity->get_entity_formation_category();
        $data->categorychildid = $formationid;

        $data->categoryid        = $entity->id;
        $data->creativestructure = $entity->id;

        return $data;
    }

    /**
     * Test get session
     *
     * @covers  \local_mentor_core\session_api::get_session
     * @covers  \local_mentor_core\session::__construct
     * @covers  \local_mentor_core\specialization::get_specialization
     * @covers  \local_mentor_specialization\mentor_specialization::get_specialization
     * @covers  \local_mentor_specialization\mentor_specialization::get_session
     * @covers  \local_mentor_specialization\mentor_session::__construct
     */
    public function test_get_session_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        // Create session.
        $sessionid = $this->init_session_creation();

        try {
            $session = \local_mentor_core\session_api::get_session($sessionid);
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        self::assertIsObject($session);
        self::assertEquals($sessionid, $session->id);

        self::resetAllData();
    }

    /**
     * Test get session fail
     *
     * @covers  \local_mentor_core\session_api::get_session
     * @covers  \local_mentor_specialization\mentor_session::__construct
     * @covers  \local_mentor_core\specialization::get_specialization
     * @covers  \local_mentor_specialization\mentor_specialization::get_specialization
     * @covers  \local_mentor_specialization\mentor_specialization::get_session
     */
    public function test_get_session_nok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $falsesessionid = 10;

        // Test session not found.
        try {
            \local_mentor_core\session_api::get_session($falsesessionid);
            self::fail('Not possible exist');
        } catch (\Exception $e) {
            // Session does not exist.
            self::assertInstanceOf('moodle_exception', $e);
        }

        self::resetAllData();
    }

    /**
     * Test get session by entity ok
     *
     * @covers  \local_mentor_core\session_api::get_sessions_by_entity
     * @covers  \local_mentor_core\session_api::get_session
     * @covers  \local_mentor_core\specialization::get_specialization
     * @covers  \local_mentor_specialization\mentor_specialization::get_specialization
     * @covers  \local_mentor_specialization\mentor_specialization::get_sessions_by_entity
     * @covers  \local_mentor_specialization\mentor_specialization::get_session
     * @covers  \local_mentor_specialization\mentor_session::__construct
     * @covers  \local_mentor_specialization\mentor_session::get_url
     * @covers  \local_mentor_specialization\mentor_session::get_course
     * @covers  \local_mentor_specialization\mentor_session::is_shared
     * @covers  \local_mentor_specialization\mentor_session::get_actions
     * @covers  \local_mentor_core\database_interface::get_sessions_by_entity_id
     */
    public function test_get_session_by_entity_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        // Create session.
        $sessionid = $this->init_session_creation();

        try {
            $session = \local_mentor_core\session_api::get_session($sessionid);
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        // Set data passed as an argument.
        $datasessionandfilter           = new stdClass();
        $datasessionandfilter->entityid = $session->get_entity()->id;
        $datasessionandfilter->status   = null;
        $datasessionandfilter->dateto   = null;
        $datasessionandfilter->datefrom = null;
        $datasessionandfilter->draw     = 1;
        $datasessionandfilter->length   = 10;
        $datasessionandfilter->start    = 0;
        $datasessionandfilter->order    = ['column' => 0, 'dir' => 'asc'];
        $datasessionandfilter->search   = ['value' => '', 'regex' => 'false'];

        // Test session not found.
        try {
            $sessions = \local_mentor_core\session_api::get_sessions_by_entity($datasessionandfilter);
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        self::assertCount(1, $sessions);
        self::assertEquals($session->id, $sessions[0]['id']);
    }

    /**
     * Test get count session by entity id ok
     *
     * @covers  \local_mentor_core\session_api::count_sessions_by_entity_id
     * @covers  \local_mentor_core\specialization::get_instance
     * @covers  \local_mentor_core\specialization::get_specialization
     * @covers  \local_mentor_specialization\mentor_specialization::get_specialization
     * @covers  \local_mentor_specialization\mentor_specialization::count_sessions_by_entity_id
     */
    public function test_count_sessions_by_entity_id_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        // Create session.
        $sessionid = $this->init_session_creation();

        try {
            $session = \local_mentor_core\session_api::get_session($sessionid);
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        // Set data passed as an argument.
        $datasessionandfilter           = new stdClass();
        $datasessionandfilter->entityid = $session->get_entity()->id;
        $datasessionandfilter->status   = null;
        $datasessionandfilter->dateto   = null;
        $datasessionandfilter->datefrom = null;
        $datasessionandfilter->draw     = 1;
        $datasessionandfilter->length   = 10;
        $datasessionandfilter->start    = 0;
        $datasessionandfilter->order    = ['column' => 0, 'dir' => 'asc'];
        $datasessionandfilter->search   = ['value' => '', 'regex' => 'false'];

        // Test session not found.
        try {
            $sessioncount = \local_mentor_core\session_api::count_sessions_by_entity_id($datasessionandfilter);
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        self::assertEquals(1, $sessioncount);

        self::resetAllData();
    }

    /**
     * Test get count session by entity id ok
     *
     * @covers  \local_mentor_core\session_api::get_next_training_session_index
     */
    public function test_get_next_training_session_index_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        // Create session.
        $training = $this->init_training_creation();

        // Get next index session.
        $nextsessionindex = \local_mentor_core\session_api::get_next_training_session_index($training->id);

        self::assertEquals(1, $nextsessionindex);

        self::resetAllData();
    }

    /**
     * Test add a session
     * returns the training created for the following tests
     *
     * @covers  \local_mentor_core\session_api::create_session
     * @covers  \local_mentor_core\task\create_session_task::execute
     *
     * @throws ReflectionException
     * @throws moodle_exception
     */
    public function test_create_session_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        // Create training.
        $training = $this->init_training_creation();

        $sessionname = 'TESTUNITCREATESESSION';

        // Test standard training creation.
        try {
            $session = \local_mentor_core\session_api::create_session($training->id, $sessionname, true);
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        // Test if we have received an object.
        self::assertIsObject($session);
        self::assertIsNumeric($session->id);

        self::resetAllData();
    }

    /**
     * Test update session
     *
     * @covers  \local_mentor_core\session_api::update_session
     * @covers  \local_mentor_core\session_api::get_session
     * @covers  \local_mentor_specialization\mentor_session::is_updater
     * @covers  \local_mentor_specialization\mentor_session::update
     * @covers  \local_mentor_specialization\mentor_session::get_context
     */
    public function test_update_session_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        // Create session.
        $sessionid = $this->init_session_creation();

        $data = new stdClass();

        // New session data.
        $data->id                                  = $sessionid;
        $data->fullname                            = 'NEWFULLNAME';
        $data->presencesessionestimatedtimehours   = 1;
        $data->presencesessionestimatedtimeminutes = 45;
        $data->onlinesessionestimatedtimehours     = 2;
        $data->onlinesessionestimatedtimeminutes   = 30;

        try {
            $session = \local_mentor_core\session_api::update_session($data);
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        // Check if session is update.
        self::assertIsObject($session);
        self::assertEquals($data->fullname, $session->fullname);

        self::resetAllData();
    }

    /**
     * Test get session form ok
     *
     * @covers  \local_mentor_core\session_api::get_session_form
     * @covers  \local_mentor_specialization\session_form::__construct
     * @covers  \local_mentor_specialization\session_form::definition
     * @covers  \local_mentor_core\specialization::__construct
     * @covers  \local_mentor_core\specialization::get_instance
     * @covers  \local_mentor_core\specialization::get_specialization
     * @covers  \local_mentor_specialization\mentor_specialization::get_specialization
     * @covers  \local_mentor_specialization\mentor_specialization::get_session_form
     * @covers  \local_mentor_specialization\mentor_session::__construct
     * @covers  \local_mentor_specialization\mentor_session::get_url
     * @covers  \local_mentor_specialization\mentor_session::get_sheet_url
     */
    public function test_get_session_form_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        // Create session.
        $sessionid = $this->init_session_creation();

        try {
            $session = \local_mentor_core\session_api::get_session($sessionid);
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        // Set data session form.
        $sessionentity = $session->get_entity();

        $logo    = $sessionentity->get_logo();
        $logourl = '';
        if ($logo) {
            $logourl = moodle_url::make_pluginfile_url(
                $logo->get_contextid(),
                $logo->get_component(),
                $logo->get_filearea(),
                $logo->get_itemid(),
                $logo->get_filepath(),
                $logo->get_filename()
            );
        }

        $sharedentities = [];
        $allentities    = \local_mentor_core\entity_api::get_all_entities(true, [$sessionentity->id]);
        foreach ($allentities as $entity) {
            $sharedentities[$entity->id] = $entity->name;
        }

        $formparams                 = new stdClass();
        $formparams->session        = $session;
        $formparams->returnto       = $session->get_url();
        $formparams->session        = $session;
        $formparams->entity         = $sessionentity;
        $formparams->sharedentities = $sharedentities;
        $formparams->logourl        = $logourl;
        $formparams->actionurl      = $session->get_sheet_url()->out();

        // Get session form.
        try {
            $sessionform = \local_mentor_core\session_api::get_session_form($session->get_sheet_url()->out(), $formparams);
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        // Check instance session form.
        self::assertInstanceOf('local_mentor_specialization\session_form', $sessionform);

        self::resetAllData();
    }

    /**
     * Test get session javascript ok
     *
     * @covers  \local_mentor_core\session_api::get_session_javascript
     * @covers  \local_mentor_core\specialization::__construct
     * @covers  \local_mentor_core\specialization::get_instance
     * @covers  \local_mentor_core\specialization::get_specialization
     * @covers  \local_mentor_specialization\mentor_specialization::get_specialization
     * @covers  \local_mentor_specialization\mentor_specialization::get_session_javascript
     */
    public function test_get_session_javascript_ok() {

        $this->resetAfterTest(true);

        self::setAdminUser();
        $this->init_config();
        $this->reset_singletons();

        // Check if return good session javascirpt path string.
        self::assertEquals(
            'local_mentor_specialization/session',
            \local_mentor_core\session_api::get_session_javascript('local_mentor_core/session')
        );

        self::resetAllData();
    }

    /**
     * Test get session template ok
     *
     * @covers  \local_mentor_core\session_api::get_session_template
     * @covers  \local_mentor_core\specialization::__construct
     * @covers  \local_mentor_core\specialization::get_instance
     * @covers  \local_mentor_core\specialization::get_specialization
     * @covers  \local_mentor_specialization\mentor_specialization::get_specialization
     * @covers  \local_mentor_specialization\mentor_specialization::get_session_template
     */
    public function test_get_session_template_ok() {
        $this->resetAfterTest(true);

        self::setAdminUser();
        $this->init_config();
        $this->reset_singletons();

        // Check if return good session template path string.
        self::assertEquals(
            'local_mentor_specialization/session',
            \local_mentor_core\session_api::get_session_template('local_mentor_core/session')
        );

        self::resetAllData();
    }

    /**
     * Test get session by course id ok
     *
     * @covers  \local_mentor_core\session_api::get_session_by_course_id
     * @covers  \local_mentor_core\session_api::get_session
     * @covers  \local_mentor_specialization\mentor_session::get_course
     */
    public function test_get_session_by_course_id_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        // Create session.
        $sessionid = $this->init_session_creation();

        // Get session.
        try {
            $session = \local_mentor_core\session_api::get_session($sessionid);
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        // Get course id session.
        $courseid = $session->get_course()->id;

        // Get session of course.
        try {
            $coursesession = \local_mentor_core\session_api::get_session_by_course_id($courseid);
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        // Check if good session.
        self::assertEquals($sessionid, $coursesession->id);

        self::resetAllData();
    }

    /**
     * Test get session by course id not ok
     *
     * @covers  \local_mentor_core\session_api::get_session_by_course_id
     */
    public function test_get_session_by_course_id_nok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $falsecourseid = '100';

        // Get session of course.
        try {
            $sessionid = \local_mentor_core\session_api::get_session_by_course_id($falsecourseid);
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        // Check if fail.
        self::assertFalse($sessionid);

        self::resetAllData();
    }

    /**
     * Test cancel_session
     *
     * @covers  \local_mentor_core\session_api::cancel_session
     * @covers  \local_mentor_core\session_api::get_session
     * @covers  \local_mentor_specialization\mentor_session::is_updater
     * @covers  \local_mentor_specialization\mentor_session::update_status
     * @covers  \local_mentor_specialization\mentor_session::cancel
     * @covers  \local_mentor_specialization\mentor_session::hide_course
     * @covers  \local_mentor_specialization\mentor_session::send_message_to_all
     * @covers  \local_mentor_specialization\mentor_session::get_all_users
     * @covers  \local_mentor_specialization\mentor_session::get_editors
     * @covers  \local_mentor_specialization\mentor_session::get_participants
     * @covers  \local_mentor_specialization\mentor_session::send_message_to_users
     * @covers  \local_mentor_specialization\mentor_session::disable_enrolment_instance
     * @covers  \local_mentor_specialization\mentor_session::get_enrolment_instances
     */
    public function test_cancel_session_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        // Create session.
        $sessionid = $this->init_session_creation();

        try {
            self::assertTrue(\local_mentor_core\session_api::cancel_session($sessionid));
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        self::resetAllData();
    }

    /**
     * Test get user session courses
     *
     * @covers  \local_mentor_core\session_api::get_user_session_courses
     * @covers  \local_mentor_core\session_api::get_session
     * @covers  \local_mentor_specialization\mentor_session::get_entity
     */
    public function test_get_user_session_courses_ok() {
        global $DB;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $DB->delete_records('course_categories');

        // Create session.
        $sessionid = $this->init_session_creation();

        // Get session.
        try {
            $session = \local_mentor_core\session_api::get_session($sessionid);
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        // Assign new role.
        \local_mentor_core\profile_api::role_assign('referentlocal', self::DEFAULT_USER, $session->get_entity()->get_context());

        // Check if user has session manager.
        $sessionmanage = \local_mentor_core\session_api::get_user_session_courses(self::DEFAULT_USER);
        self::assertCount(1, $sessionmanage);

        self::resetAllData();
    }

    /**
     * Test get user available sessions
     *
     * @covers \local_mentor_core\session_api::user_is_enrolled
     * @covers \local_mentor_core\session_api::get_session
     * @covers \local_mentor_specialization\mentor_session::create_self_enrolment_instance
     * @covers \local_mentor_specialization\mentor_session::get_course
     * @covers \local_mentor_specialization\mentor_session::get_enrolment_instances_by_type
     * @covers \local_mentor_specialization\mentor_session::enable_self_enrolment_instance
     * @covers \local_mentor_specialization\mentor_session::get_enrolment_instances
     * @covers \local_mentor_specialization\mentor_session::user_is_enrolled
     * @covers \local_mentor_specialization\mentor_session::get_context
     */
    public function test_get_user_available_sessions_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        // Check if not session is available.
        $avaiblesessions = \local_mentor_core\session_api::get_user_available_sessions(self::DEFAULT_USER);
        self::assertCount(0, $avaiblesessions);

        // Create session.
        $sessionid = $this->init_session_creation();

        // Get session.
        try {
            $session = \local_mentor_core\session_api::get_session($sessionid);
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        // Update the status of the session so that it becomes available.
        $session->status = \local_mentor_core\session::STATUS_IN_PROGRESS;
        \local_mentor_core\session_api::update_session($session);

        $user     = new stdClass();
        $user->id = self::DEFAULT_USER;
        $session->get_entity()->add_member($user);

        // Check if user has one available session.
        $avaiblesessions = \local_mentor_core\session_api::get_user_available_sessions(self::DEFAULT_USER);
        self::assertCount(1, $avaiblesessions);

        self::resetAllData();
    }

    /**
     * Test user is enrolled
     *
     * @covers \local_mentor_core\session_api::user_is_enrolled
     * @covers \local_mentor_core\session_api::get_session
     * @covers \local_mentor_core\session::create_self_enrolment_instance
     * @covers \local_mentor_specialization\mentor_session::get_course
     * @covers \local_mentor_specialization\mentor_session::get_enrolment_instances_by_type
     * @covers \local_mentor_specialization\mentor_session::enable_self_enrolment_instance
     * @covers \local_mentor_specialization\mentor_session::get_enrolment_instances
     * @covers \local_mentor_specialization\mentor_session::user_is_enrolled
     * @covers \local_mentor_specialization\mentor_session::get_context
     */
    public function test_user_is_enrolled_ok() {
        $this->resetAfterTest();
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        // Create session.
        $sessionid = $this->init_session_creation();

        $userid = $this->init_create_user();

        // Get session.
        try {
            $session = \local_mentor_core\session_api::get_session($sessionid);
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        // Updating the status session to have return sessions.
        $session->status = \local_mentor_core\session::STATUS_IN_PROGRESS;
        $session->opento = \local_mentor_core\session::OPEN_TO_ALL;
        \local_mentor_core\session_api::update_session($session);

        // Create self enrolment instance.
        $session->create_self_enrolment_instance();

        self::setUser($userid);

        // Check if user user is not enrolled.
        $isenrolled = \local_mentor_core\session_api::user_is_enrolled($userid, $session->id);
        self::assertFalse($isenrolled);

        // Enrol user.
        $session->enrol_current_user();

        // Check if user user is enrolled.
        $isenrolled = \local_mentor_core\session_api::user_is_enrolled($userid, $session->id);
        self::assertTrue($isenrolled);

        self::resetAllData();
    }

    /**
     * Test get all sessions where the user is not enrolled
     *
     * @covers  \local_mentor_core\session_api::get_user_sessions
     * @covers  \local_mentor_core\session_api::get_session_by_course_id
     * @covers  \local_mentor_core\session_api::get_session
     * @covers  \local_mentor_core\session_api::update_session
     * @covers  \local_mentor_specialization\mentor_session::convert_for_template
     * @covers  \local_mentor_specialization\mentor_session::get_progression
     * @covers  \local_mentor_specialization\mentor_session::is_trainer
     * @covers  \local_mentor_specialization\mentor_session::is_participant
     * @covers  \local_mentor_specialization\mentor_session::get_training
     * @covers  \local_mentor_specialization\mentor_session::get_course
     */
    public function test_get_sessions_user_not_enrolled() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        $userid = $this->init_create_user();

        self::setAdminUser();

        // Create session.
        $sessionid = $this->init_session_creation();

        // Get session.
        try {
            $session = \local_mentor_core\session_api::get_session($sessionid);
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        // Updating the status session to have return sessions.
        $session->status = \local_mentor_core\session::STATUS_IN_PROGRESS;
        \local_mentor_core\session_api::update_session($session);

        // User is not enrol.
        self::assertCount(0, \local_mentor_core\session_api::get_user_sessions($userid));

        self::resetAllData();
    }

    /**
     * Test get all sessions where the user is enrolled
     *
     * @covers  \local_mentor_core\session_api::get_user_sessions
     * @covers  \local_mentor_core\session_api::get_session
     * @covers  \local_mentor_core\session_api::update_session
     * @covers  \local_mentor_core\session_api::user_is_enrolled
     * @covers  \local_mentor_core\session_api::get_session_by_course_id
     * @covers  \local_mentor_specialization\mentor_session::create_self_enrolment_instance
     * @covers  \local_mentor_specialization\mentor_session::enrol_current_user
     * @covers  \local_mentor_specialization\mentor_session::is_trainer
     * @covers  \local_mentor_specialization\mentor_session::convert_for_template
     * @covers  \local_mentor_specialization\mentor_session::is_participant
     * @covers  \local_mentor_specialization\mentor_session::get_url
     * @covers  \local_mentor_specialization\mentor_session::get_available_places
     * @covers  \local_mentor_specialization\mentor_session::user_is_enrolled
     * @covers  \local_mentor_specialization\mentor_session::get_training
     * @covers  \local_mentor_specialization\mentor_session::get_progression
     * @covers  \local_mentor_specialization\mentor_session::get_course
     */
    public function test_get_user_sessions() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        $userid = $this->init_create_user();

        self::setAdminUser();

        // Create session.
        $sessionid = $this->init_session_creation();

        // Get session.
        try {
            $session = \local_mentor_core\session_api::get_session($sessionid);
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        // Updating the status session to have return sessions.
        $session->status = \local_mentor_core\session::STATUS_IN_PROGRESS;
        \local_mentor_core\session_api::update_session($session);

        // Create self enrolment instance.
        $session->create_self_enrolment_instance();

        // Check if user user is not enrolled.
        $isenrolled = \local_mentor_core\session_api::user_is_enrolled($userid, $session->id);
        self::assertFalse($isenrolled);

        // Enrol user.
        self::setUser($userid);
        $session->enrol_current_user();
        self::setAdminUser();

        // User is enrol.
        self::assertCount(1, \local_mentor_core\session_api::get_user_sessions($userid));
        // User is not trainer.
        self::assertFalse($session->is_trainer($userid));

        self::resetAllData();
    }

    /**
     * Test get all sessions where the user is trainer
     *
     * @covers  \local_mentor_core\session_api::get_user_sessions
     * @covers  \local_mentor_core\session_api::get_session
     * @covers  \local_mentor_core\session_api::update_session
     * @covers  \local_mentor_core\session_api::user_is_enrolled
     * @covers  \local_mentor_core\session_api::get_session_by_course_id
     * @covers  \local_mentor_specialization\mentor_session::create_self_enrolment_instance
     * @covers  \local_mentor_specialization\mentor_session::enrol_current_user
     * @covers  \local_mentor_specialization\mentor_session::get_context
     * @covers  \local_mentor_specialization\mentor_session::convert_for_template
     * @covers  \local_mentor_specialization\mentor_session::get_progression
     * @covers  \local_mentor_specialization\mentor_session::user_is_enrolled
     */
    public function test_get_sessions_user_trainer() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        // Create simple user.
        $userid = $this->init_create_user();

        self::setAdminUser();

        // Create session.
        $sessionid = $this->init_session_creation();

        // Get session.
        try {
            $session = \local_mentor_core\session_api::get_session($sessionid);
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        // Updating the status session to have return sessions.
        $session->status = \local_mentor_core\session::STATUS_IN_PROGRESS;
        \local_mentor_core\session_api::update_session($session);

        // Create self enrolment instance.
        $session->create_self_enrolment_instance();

        // Check if user user is not enrolled.
        $isenrolled = \local_mentor_core\session_api::user_is_enrolled($userid, $session->id);
        self::assertFalse($isenrolled);

        // Enrol user.
        self::setUser($userid);
        $session->enrol_current_user();
        self::setAdminUser();

        \local_mentor_core\profile_api::role_assign('formateur', $userid, $session->get_context());

        // User is enrol.
        self::assertCount(1, \local_mentor_core\session_api::get_user_sessions($userid));
        // User id trainer.
        self::assertTrue($session->is_trainer($userid));

        self::resetAllData();
    }

    /**
     * Test get session enrolment data
     */
    public function test_move_session() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        // Create session.
        $sessionid = $this->init_session_creation();

        // Get session.
        try {
            $session = \local_mentor_core\session_api::get_session($sessionid);
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        // Get old entity id.
        $oldentityid = $session->get_entity()->id;

        // Create new enrity.
        $newentityid = \local_mentor_core\entity_api::create_entity([
            'name'      => 'New Entity 2',
            'shortname' => 'New Entity 2',
            'userid'    => 2  // Set the admin user as manager of the entity.
        ]);

        // Move session in new entity.
        \local_mentor_core\session_api::move_session($session->id, $newentityid);

        // Check if session entity is not old entity.
        self::assertNotEquals($session->get_entity()->id, $oldentityid);
        // Check if session entity is new entity.
        self::assertEquals($session->get_entity()->id, $newentityid);

        $this->resetAllData();
    }

    /**
     * Test count session record ok
     *
     * @covers  \local_mentor_core\session_api::count_session_record
     * @covers  \local_mentor_core\session_api::get_session
     * @covers  \local_mentor_core\specialization::__construct
     * @covers  \local_mentor_core\specialization::get_instance
     * @covers  \local_mentor_core\specialization::get_specialization
     * @covers  \local_mentor_core\database_interface::get_sessions_by_entity_id
     */
    public function test_count_session_record_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        // Create session.
        $sessionid = $this->init_session_creation();

        try {
            $session = \local_mentor_core\session_api::get_session($sessionid);
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        // Set data passed as an argument.
        $datasessionandfilter           = new stdClass();
        $datasessionandfilter->entityid = $session->get_entity()->id;
        $datasessionandfilter->status   = null;
        $datasessionandfilter->dateto   = null;
        $datasessionandfilter->datefrom = null;
        $datasessionandfilter->draw     = 1;
        $datasessionandfilter->length   = 10;
        $datasessionandfilter->start    = 0;
        $datasessionandfilter->order    = ['column' => 0, 'dir' => 'asc'];
        $datasessionandfilter->search   = ['value' => '', 'regex' => 'false'];

        // Test session not found.
        try {
            $countsessions = \local_mentor_core\session_api::count_session_record($datasessionandfilter);
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        self::assertEquals(1, $countsessions);

        $this->resetAllData();
    }

    /**
     * Test user permissions on session
     *
     * @covers  \local_mentor_core\session::is_manager
     * @covers  \local_mentor_core\session::is_creator
     * @covers  \local_mentor_core\session::is_deleter
     * @covers  \local_mentor_core\session::is_tutor
     * @covers  \local_mentor_core\session::is_updater
     * @covers  \local_mentor_core\session::is_trainer
     */
    public function test_user_permissions() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        // Create session.
        $sessionid = $this->init_session_creation();

        try {
            $session = \local_mentor_core\session_api::get_session($sessionid);
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        // Create a simple user.
        $user = $this->getDataGenerator()->create_user();
        self::setUser($user);

        self::assertFalse($session->is_manager());
        self::assertFalse($session->is_creator($user));
        self::assertFalse($session->is_deleter($user));
        self::assertFalse($session->is_tutor($user));
        self::assertFalse($session->is_updater($user));
        self::assertFalse($session->is_trainer($user));

        $entity = $session->get_entity();

        // Set user as manager.
        $entity->assign_manager($user->id);

        self::assertTrue($session->is_manager());
        self::assertTrue($session->is_creator($user));
        self::assertTrue($session->is_deleter($user));
        self::assertFalse($session->is_tutor($user));
        self::assertTrue($session->is_updater($user));
        self::assertFalse($session->is_trainer($user));

        $this->resetAllData();
    }

    /**
     * Test get_user_sessions
     * hidden entity
     *
     * @covers  \local_mentor_core\session_api::get_user_sessions
     */
    public function test_get_sessions_user_hidden_entity() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        // Create simple user.
        $userid = $this->init_create_user();

        self::setAdminUser();

        // Create session.
        $sessionid = $this->init_session_creation();

        // Get session.
        try {
            $session = \local_mentor_core\session_api::get_session($sessionid);
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        // Updating the status session to have return sessions.
        $session->opento = 'all';
        $session->status = \local_mentor_core\session::STATUS_IN_PROGRESS;
        \local_mentor_core\session_api::update_session($session);

        // Create self enrolment instance.
        $session->create_self_enrolment_instance();

        // Check if user user is not enrolled.
        $isenrolled = \local_mentor_core\session_api::user_is_enrolled($userid, $session->id);
        self::assertFalse($isenrolled);

        // Enrol user.
        self::setUser($userid);
        $session->enrol_current_user();

        // User is enrol.
        self::assertCount(1, \local_mentor_core\session_api::get_user_sessions($userid));

        $session->get_entity()->get_main_entity()->update_visibility(1);

        // User is enrol but entity is hidden.
        self::assertCount(0, \local_mentor_core\session_api::get_user_sessions($userid));

        self::resetAllData();
    }
}
