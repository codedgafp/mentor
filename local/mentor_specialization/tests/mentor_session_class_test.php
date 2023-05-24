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
 * Test cases for class mentor_session
 *
 * @package    local_mentor_specialization
 * @copyright  2021 Edunao SAS (contact@edunao.com)
 * @author     adrien <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/local/mentor_core/api/entity.php');
require_once($CFG->dirroot . '/local/mentor_core/api/training.php');
require_once($CFG->dirroot . '/local/mentor_core/api/session.php');
require_once($CFG->dirroot . '/local/mentor_core/api/profile.php');
require_once($CFG->dirroot . '/local/mentor_core/classes/model/session.php');
require_once($CFG->dirroot . '/lib/enrollib.php');

class local_mentor_specialization_mentor_session_class_testcase extends advanced_testcase {

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

        // Fields for training.
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
                'regions' => [1]
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

        return $session->id;
    }

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

    public function get_instance_data($courseid) {
        // Create new self enrol instance.
        $sirhplugin = enrol_get_plugin('sirh');

        $instance = (object) $sirhplugin->get_instance_defaults();
        $instance->status = 0;
        $instance->id = '';
        $instance->courseid = $courseid;
        $instance->expirythreshold = 0;
        $instance->enrolstartdate = 0;
        $instance->enrolenddate = 0;
        $instance->timecreated = time();
        $instance->timemodified = time();
        $instance->customchar1 = 'sirh';
        $instance->customchar2 = 'sirhtraining';
        $instance->customchar3 = 'sirhsession';
        $instance->roleid = $sirhplugin->get_config('roleid');

        return $instance;
    }

    /**
     * Test entity constructor
     *
     * @covers \local_mentor_specialization\mentor_session::__construct
     */
    public function test_session_construct_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $sessionid = $this->init_session_creation();

        $session = \local_mentor_core\session_api::get_session($sessionid);

        $sessionobj = new \local_mentor_specialization\mentor_session($sessionid);

        self::assertEquals($session->id, $sessionobj->id);
        self::assertEquals($session->fullname, $sessionobj->fullname);
        self::assertEquals($session->shortname, $sessionobj->shortname);

        $baddata = new stdClass();
        $baddata->id = $sessionid;

        try {
            $sessionobj = new \local_mentor_specialization\mentor_session($sessionid);
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        $sessionobjbis = new \stdClass();
        $sessionobjbis->id = $sessionobj->id;
        $sessionobjbis->courseshortname = $sessionobj->courseshortname;
        $sessionobjbis->courseid = $sessionobj->courseid;
        $sessionobjbis->contextid = $sessionobj->contextid;
        $sessionobjbis->trainingid = $sessionobj->trainingid;
        $sessionobjbis->fullname = $sessionobj->fullname;
        $sessionobjbis->shortname = $sessionobj->shortname;
        $sessionobjbis->status = $sessionobj->status;
        $sessionobjbis->opento = $sessionobj->opento;
        $sessionobjbis->sessionstartdate = $sessionobj->sessionstartdate;
        $sessionobjbis->sessionenddate = $sessionobj->sessionenddate;
        $sessionobjbis->maxparticipants = $sessionobj->maxparticipants;

        try {
            $sessionexception = new \local_mentor_specialization\mentor_session($sessionobjbis);
            self::assertFalse($sessionexception);
        } catch (\Exception $e) {
            self::assertInstanceOf('exception', $e);
        }

        self::resetAllData();
    }

    /**
     * Test method duplicate_as_new_training
     *
     * @covers \local_mentor_specialization\mentor_session::duplicate_as_new_training
     * @covers \local_mentor_core\session::duplicate_as_new_training
     * @covers \local_mentor_core\training::reset
     */
    public function test_duplicate_as_new_training() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $sessionid = $this->init_session_creation();

        $session = \local_mentor_core\session_api::get_session($sessionid);

        $entityid = $session->get_entity()->id;

        $newtraining = $session->duplicate_as_new_training('trainingfullname', 'trainingshortname', $entityid);

        self::assertIsObject($newtraining);
        self::assertEquals('trainingfullname', $newtraining->name);
        self::assertEquals('trainingshortname', $newtraining->shortname);

        $newtraining = $session->duplicate_as_new_training('trainingfullname', 'trainingshortname', $entityid);

        self::assertIsObject($newtraining);
        self::assertEquals('trainingfullname', $newtraining->name);
        self::assertEquals('trainingshortname copie', $newtraining->shortname);

        self::resetAllData();
    }

    /**
     * Test method update
     *
     * @covers \local_mentor_specialization\mentor_session::update
     */
    public function test_session_update_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $sessionid = $this->init_session_creation();

        $session = new \local_mentor_specialization\mentor_session($sessionid);

        $data = new stdClass();
        $data->status = \local_mentor_core\session::STATUS_COMPLETED;
        $data->termsregistrationdetail = 'termsregistrationdetail';
        $data->sessionpermanent = 0;
        $session->update($data, true);

        self::assertEquals($session->status, \local_mentor_core\session::STATUS_COMPLETED);

        $data = new stdClass();
        $data->status = \local_mentor_core\session::STATUS_CANCELLED;
        $data->termsregistrationdetail = 'termsregistrationdetail';
        $data->sessionpermanent = 0;
        $session->update($data, true);

        self::assertEquals($session->status, \local_mentor_core\session::STATUS_CANCELLED);

        self::resetAllData();
    }

    /**
     * Test method create_self_enrolment_instance not ok
     *
     * @covers \local_mentor_specialization\mentor_session::create_self_enrolment_instance
     */
    public function test_create_self_enrolment_instance_nok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $sessionid = $this->init_session_creation();

        // Create database interface Mock.
        $sessionmock = $this->getMockBuilder('\local_mentor_specialization\mentor_session')
            ->setMethods(['get_enrolment_instances_by_type', 'enable_self_enrolment_instance'])
            ->setConstructorArgs([$sessionid])
            ->getMock();

        $sessionmock->expects($this->any())
            ->method('get_enrolment_instances_by_type')
            ->will($this->returnValue(true));

        // Return false value when create_self_enrolment_instance function call.
        $sessionmock->expects($this->any())
            ->method('enable_self_enrolment_instance')
            ->will($this->returnValue(false));

        self::assertFalse($sessionmock->create_self_enrolment_instance());

        self::resetAllData();
    }

    /**
     * Test method get sirh instances ok
     *
     * @covers \local_mentor_specialization\mentor_session::get_sirh_instances
     */
    public function test_get_sirh_instances_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $sessionid = $this->init_session_creation();
        $session = new \local_mentor_specialization\mentor_session($sessionid);

        // Instance not existe.
        self::assertEmpty($session->get_sirh_instances());

        // Create new self enrol instance.
        $sirhplugin = enrol_get_plugin('sirh');
        $instance = $this->get_instance_data($session->get_course()->id);
        $instanceid = $sirhplugin->add_instance($session->get_course(), (array) $instance);

        // Instance existe.
        $sessionsirhinstance = $session->get_sirh_instances();

        self::assertCount(1, $sessionsirhinstance);
        self::assertEquals($instanceid, current($sessionsirhinstance)->id);

        self::resetAllData();
    }

    /**
     * Test method get actions ok
     *
     * @covers \local_mentor_specialization\mentor_session::get_actions
     */
    public function test_get_actions_ok() {
        global $CFG;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $sessionid = $this->init_session_creation();
        $session = new \local_mentor_specialization\mentor_session($sessionid);
        $entity = $session->get_entity()->get_main_entity();
        $liststatus = \local_mentor_core\session_api::get_status_list();
        $sirhaccessstats = [
            \local_mentor_core\session::STATUS_OPENED_REGISTRATION,
            \local_mentor_core\session::STATUS_IN_PROGRESS
        ];

        foreach ($liststatus as $status) {
            $data = new stdClass();
            $data->status = $status;
            $data->termsregistrationdetail = 'termsregistrationdetail';
            $data->sessionpermanent = 0;
            $session->update($data, true);

            // Without SIRH.
            $entity->update_sirh_list([]);

            self::assertArrayNotHasKey('importSIRH', $session->get_actions(null, true));

            // With SIRH.
            $entity->update_sirh_list(['RENOIRH_AES']);
            if (in_array($status, $sirhaccessstats)) {
                $action = $session->get_actions(null, true);
                self::assertArrayHasKey('importSIRH', $action);
                self::assertCount(2, $action['importSIRH']);

                self::assertArrayHasKey('url', $action['importSIRH']);
                self::assertEquals(
                    $CFG->wwwroot . '/enrol/sirh/pages/index.php?sessionid=' . $session->id,
                    $action['importSIRH']['url']
                );

                self::assertArrayHasKey('tooltip', $action['importSIRH']);
                self::assertEquals(
                    'Inscriptions SIRH', $action['importSIRH']['tooltip']
                );
            } else {
                self::assertArrayNotHasKey('importSIRH', $session->get_actions(null, true));
            }

        }

        self::resetAllData();
    }
}
