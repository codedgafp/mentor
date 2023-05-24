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

use local_mentor_core\session;
use local_mentor_core\entity_api;
use local_mentor_core\session_api;
use local_mentor_core\specialization;
use local_mentor_core\training_api;
use local_mentor_specialization\mentor_training;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/local/mentor_core/api/training.php');
require_once($CFG->dirroot . '/local/mentor_core/classes/model/session.php');

class local_mentor_specialization_training_testcase extends advanced_testcase {

    public const UNAUTHORISED_CODE = 2020120810;

    public function init_config() {
        global $CFG;

        $CFG->mentor_specializations = [
                '\\local_mentor_specialization\\mentor_specialization' =>
                        'local/mentor_specialization/classes/mentor_specialization.php'
        ];
    }

    /**
     * Tests set up.
     */
    public function setUp() {
        $this->resetAfterTest(false);
        self::setAdminUser();
    }

    /**
     * Reset the singletons
     *
     * @throws ReflectionException
     */
    public function reset_singletons() {
        // Reset the mentor core db interface singleton.
        $dbinterface = \local_mentor_core\database_interface::get_instance();
        $reflection = new ReflectionClass($dbinterface);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true); // Now we can modify that :).
        $instance->setValue(null, null); // Instance is gone.
        $instance->setAccessible(false); // Clean up.

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
        return $userid;
    }

    /**
     * Init training object
     *
     * @return stdClass
     * @throws dml_exception
     * @throws moodle_exception
     */
    private function get_training_data($shortname = 'shortname', $entityid = null) {
        set_config('collections', 'accompagnement|Accompagnement des transitions professionnelles|#CECECE',
                'local_mentor_specialization');

        // Init test data.
        $trainingdata = new stdClass();

        $trainingdata->name = $shortname;
        $trainingdata->shortname = $shortname;
        $trainingdata->content = 'summary';

        // Create training object.
        $trainingdata->teaser = 'http://www.edunao.com/';
        $trainingdata->teaserpicture = '';
        $trainingdata->prerequisite = 'TEST';
        $trainingdata->collection = 'accompagnement';
        $trainingdata->traininggoal = 'TEST TRAINING ';
        $trainingdata->idsirh = 'TEST ID SIRH';
        $trainingdata->licenseterms = 'cc-sa';
        $trainingdata->typicaljob = 'TEST';
        $trainingdata->skills = [1, 3];
        $trainingdata->certifying = '1';
        $trainingdata->presenceestimatedtimehours = '12';
        $trainingdata->presenceestimatedtimeminutes = '10';
        $trainingdata->remoteestimatedtimehours = '15';
        $trainingdata->remoteestimatedtimeminutes = '30';
        $trainingdata->trainingmodalities = 'd';
        $trainingdata->producingorganization = 'TEST';
        $trainingdata->producerorganizationlogo = '';
        $trainingdata->designers = 'TEST';
        $trainingdata->contactproducerorganization = 'TEST';
        $trainingdata->thumbnail = '';
        $trainingdata->status = 'dr';
        $trainingdata->content = [];
        $trainingdata->content['text'] = 'ContentText';

        if (is_null($entityid)) {
            // Get entity object for default category.
            $entityid = \local_mentor_core\entity_api::create_entity([
                    'name' => 'New Entity 1',
                    'shortname' => 'New Entity 1',
                    'regions' => [5], // Corse.
            ]);
        }

        $entity = \local_mentor_core\entity_api::get_entity($entityid);

        // Fill with entity data.
        $formationid = $entity->get_entity_formation_category();
        $trainingdata->categorychildid = $formationid;
        $trainingdata->categoryid = $entity->id;
        $trainingdata->creativestructure = $entity->id;

        return $trainingdata;
    }

    /**
     * Test create training
     *
     * @throws moodle_exception
     * @covers \local_mentor_core\training_api::create_training
     * @covers \local_mentor_core\training_api::get_training
     * @covers \local_mentor_specialization\mentor_training::update_training_course
     * @covers \local_mentor_specialization\mentor_training::update
     * @covers \local_mentor_specialization\mentor_training::get_context
     * @covers \local_mentor_specialization\mentor_training::get_course
     * @covers \local_mentor_specialization\mentor_training::create_files_by_training_form
     * @covers \local_mentor_specialization\mentor_training::create_course_training
     */
    public function test_create_training_ok() {
        $this->resetAfterTest();
        $this->init_config();
        $this->reset_singletons();

        // Init training data.
        $trainingdata = $this->get_training_data();

        $oldname = $trainingdata->name;
        unset($trainingdata->name);

        // Check with missing required field.
        try {
            training_api::create_training($trainingdata);
        } catch (\Exception $e) {
            // User is not updater.
            self::assertInstanceOf('exception', $e);
        }

        $trainingdata->name = $oldname;

        // Check with not creater user.
        self::setUser($this->init_create_user());
        try {
            training_api::create_training($trainingdata);
        } catch (\Exception $e) {
            // User is not updater.
            self::assertInstanceOf('exception', $e);
        }

        self::setAdminUser();

        /** @var mentor_training $training */
        $training = training_api::create_training($trainingdata);

        // Test if we have received an object.
        self::assertIsObject($training);
        self::assertInstanceOf(local_mentor_specialization\mentor_training::class, $training);

        self::resetAllData();
    }

    /**
     * Test update training
     *
     * @throws moodle_exception
     *
     * @covers  \local_mentor_core\training_api::update_training
     * @covers  \local_mentor_core\training_api::create_training
     * @covers  \local_mentor_core\training_api::get_training
     * @covers  \local_mentor_specialization\mentor_training::get_context
     * @covers  \local_mentor_specialization\mentor_training::get_course
     * @covers  \local_mentor_specialization\mentor_training::update
     */
    public function test_update_training_ok() {
        $this->resetAfterTest();
        $this->init_config();
        $this->reset_singletons();

        // Init training data.
        $trainingdata = $this->get_training_data();

        /** @var mentor_training $training */
        $training = training_api::create_training($trainingdata);

        // Init test data.
        $trainingdata->id = $training->id;
        $trainingdata->typicaljob = 'TEST JOB';
        $trainingdata->status = 'ar';
        $trainingdata->content = [];
        $trainingdata->content['text'] = 'content';

        self::setUser(self::init_create_user());

        try {
            $training = training_api::update_training($trainingdata);
        } catch (\Exception $e) {
            // User is not updater.
            self::assertInstanceOf('exception', $e);
        }

        self::setAdminUser();

        $training = training_api::update_training($trainingdata);

        // Test if we have received an object.
        self::assertIsObject($training);
        self::assertInstanceOf(local_mentor_specialization\mentor_training::class, $training);
        self::assertEquals('TEST JOB', $training->typicaljob);
        self::assertEquals('ar', $training->status);

        self::resetAllData();
    }

    /**
     * Test get training
     *
     * @covers  \local_mentor_core\training_api::get_training
     * @covers  \local_mentor_core\specialization::__construct
     * @covers  \local_mentor_core\specialization::get_instance
     * @covers  \local_mentor_core\specialization::get_specialization
     * @covers  \local_mentor_specialization\mentor_specialization::get_specialization
     * @covers  \local_mentor_specialization\mentor_specialization::get_training
     * @covers  \local_mentor_specialization\mentor_training::__construct
     */
    public function test_get_training_ok() {
        $this->resetAfterTest();
        $this->init_config();
        $this->reset_singletons();

        /** @var mentor_training $training */
        $training = training_api::create_training($this->get_training_data());

        $trainingobj = training_api::get_training($training->id);

        self::assertIsObject($trainingobj);
        self::assertInstanceOf(local_mentor_specialization\mentor_training::class, $trainingobj);
        self::assertEquals($training->id, $trainingobj->id);

        self::resetAllData();
    }

    /**
     * Test get training course
     *
     * @covers  \local_mentor_core\training_api::get_training_course
     * @covers  \local_mentor_core\training_api::create_training
     * @covers  \local_mentor_core\training_api::get_training
     * @covers  \local_mentor_specialization\mentor_training::get_course
     */
    public function test_get_training_course_ok() {
        $this->resetAfterTest();
        $this->init_config();
        $this->reset_singletons();

        /** @var mentor_training $training */
        $training = training_api::create_training($this->get_training_data());

        $course = training_api::get_training_course($training->id);

        self::assertIsObject($course);
        self::assertEquals($course->shortname, $training->courseshortname);

        self::resetAllData();
    }

    /**
     * Test get training by course id
     *
     * @covers  \local_mentor_core\training_api::get_training_by_course_id
     * @covers  \local_mentor_core\training_api::create_training
     * @covers  \local_mentor_core\training_api::get_training_course
     * @covers  \local_mentor_core\training_api::get_training
     */
    public function test_get_training_by_course_id_ok() {
        $this->resetAfterTest();
        $this->init_config();

        /** @var mentor_training $training */
        $training = training_api::create_training($this->get_training_data());

        $course = training_api::get_training_course($training->id);
        $courseshortname = $course->shortname;

        $trainingobj = training_api::get_training_by_course_id($course->id);

        self::assertIsObject($trainingobj);
        self::assertEquals($courseshortname, $trainingobj->courseshortname);

        self::resetAllData();
    }

    /**
     * Test get training by course id not ok
     *
     * @covers  \local_mentor_core\training_api::get_training_by_course_id
     * @covers  \local_mentor_core\training_api::get_training
     */
    public function test_get_training_by_course_id_nok() {
        $this->resetAfterTest();
        $this->reset_singletons();

        $falsetrainingcourseid = 14214471;

        self::assertFalse(training_api::get_training_by_course_id($falsetrainingcourseid));

        self::resetAllData();
    }

    /**
     * Test get trainings by entity
     *
     * @covers  \local_mentor_core\training_api::get_trainings_by_entity
     * @covers  \local_mentor_core\training_api::get_training
     * @covers  \local_mentor_specialization\mentor_training::get_entity
     * @covers  \local_mentor_specialization\mentor_training::get_url
     * @covers  \local_mentor_specialization\mentor_training::get_actions
     * @covers  \local_mentor_specialization\mentor_training::get_course
     */
    public function test_get_trainings_by_entity() {
        $this->resetAfterTest();
        $this->init_config();
        $this->reset_singletons();

        /** @var mentor_training $training */
        $training = training_api::create_training($this->get_training_data());

        $trainings = training_api::get_trainings_by_entity($training->get_entity()->id);

        // Check if the entity has a training.
        self::assertCount(1, $trainings);

        // Check if the current training exists within the entity.
        self::assertCount(1, $trainings);
        self::assertEquals($training->id, $trainings[0]['id']);

        self::resetAllData();
    }

    /**
     * Test the duplication of a training
     *
     * @covers  \local_mentor_core\training_api::duplicate_training
     * @covers  \local_mentor_core\training_api::create_training
     * @covers  \local_mentor_core\training_api::get_training
     * @covers  \local_mentor_specialization\mentor_training::get_course
     */
    public function test_duplicate_training_ok() {
        $this->resetAfterTest();
        $this->init_config();

        /** @var mentor_training $training */
        $training = training_api::create_training($this->get_training_data());

        $newtraining = training_api::duplicate_training($training->id, 'newtrainingshortname', null, true);

        // CHeck if an object has been created by the duplication.
        self::assertIsObject($newtraining);
        self::assertInstanceOf(local_mentor_specialization\mentor_training::class, $training);

        self::resetAllData();
    }

    /**
     * Test remove training
     *
     * @covers  \local_mentor_core\training_api::remove_training
     * @covers  \local_mentor_core\training_api::create_training
     * @covers  \local_mentor_core\training_api::get_training
     * @covers  \local_mentor_specialization\mentor_training::delete
     * @covers  \local_mentor_specialization\mentor_training::get_context
     * @covers  \local_mentor_specialization\mentor_training::get_course
     */
    public function test_remove_training_ok() {
        $this->resetAfterTest();
        $this->init_config();

        /** @var mentor_training $training */
        $training = training_api::create_training($this->get_training_data());

        $removeresult = training_api::remove_training($training->id);

        self::assertTrue($removeresult);

        self::resetAllData();
    }

    /**
     * Test get user training courses
     *
     * @covers  \local_mentor_core\training_api::get_user_training_courses
     */
    public function test_get_user_training_courses() {
        global $USER, $DB;

        $this->resetAfterTest();
        $this->init_config();
        self::setAdminUser();

        $DB->delete_records('course_categories');

        $this->reset_singletons();

        self::assertCount(count(entity_api::get_all_entities(true, [], true)), training_api::get_user_training_courses($USER));

        // Create entity.
        $entityid = entity_api::create_entity(['name' => 'test', 'shortname' => 'test']);
        $entity = entity_api::get_entity($entityid);

        // Adding user to entity.
        $newuser = self::getDataGenerator()->create_user();
        $entity->assign_manager($newuser->id);

        self::assertCount(1, training_api::get_user_training_courses($newuser));

        self::resetAllData();
    }

    /**
     * Test get entities training managed
     *
     * @covers  \local_mentor_core\training_api::get_entities_training_managed
     */
    public function test_get_entities_training_managed() {
        $this->resetAfterTest();
        $this->init_config();
        self::setAdminUser();

        // Create entities.
        $entityid = entity_api::create_entity(['name' => 'test', 'shortname' => 'test']);
        $entity = entity_api::get_entity($entityid);

        $entityid2 = entity_api::create_entity(['name' => 'test2', 'shortname' => 'test2']);
        $entity2 = entity_api::get_entity($entityid2);

        // Adding user to entity.
        $newuser = self::getDataGenerator()->create_user();
        $entity->assign_manager($newuser->id);
        $entity2->assign_manager($newuser->id);

        self::assertCount(2, training_api::get_entities_training_managed($newuser));

        self::resetAllData();
    }

    /**
     * Test get next available training name
     *
     * @covers  \local_mentor_core\training_api::get_next_available_training_name
     * @covers  \local_mentor_core\training_api::get_training
     * @covers  \local_mentor_core\training_api::create_training
     * @covers  \local_mentor_specialization\mentor_training::get_course
     */
    public function test_get_next_available_training_name() {
        $this->resetAfterTest();
        $this->init_config();
        self::setAdminUser();

        $trainingdata = $this->get_training_data();

        /** @var mentor_training $training1 */
        $training1 = training_api::create_training($trainingdata);

        $trainingdata->shortname = 'shortname 1';

        // Create second training.
        training_api::create_training($trainingdata);

        self::assertEquals('shortname 2', training_api::get_next_available_training_name($training1->id));

        self::resetAllData();
    }

    /**
     * Test get user available sessions by trainings
     *
     * @covers  \local_mentor_core\training_api::get_user_available_sessions_by_trainings
     * @covers  \local_mentor_core\training_api::create_training
     * @covers  \local_mentor_core\training_api::get_training
     * @covers  \local_mentor_core\specialization::__construct
     * @covers  \local_mentor_core\specialization::get_instance
     * @covers  \local_mentor_core\specialization::get_specialization
     * @covers  \local_mentor_specialization\mentor_specialization::get_specialization
     * @covers  \local_mentor_specialization\mentor_specialization::get_user_available_sessions_by_trainings
     * @covers  \local_mentor_specialization\mentor_training::get_entity
     * @covers  \local_mentor_specialization\mentor_training::get_course
     * @covers  \local_mentor_specialization\mentor_training::convert_for_template
     */
    public function test_get_user_available_sessions_by_trainings() {
        $this->resetAfterTest();
        $this->init_config();

        self::reset_singletons();

        self::setAdminUser();

        $newuser = self::getDataGenerator()->create_user();

        try {
            $training = training_api::create_training($this->get_training_data());

            $entity = $training->get_entity();

            // Add user to the default entity.
            $entity->add_member($newuser);

            // Session creation.
            $session = session_api::create_session($training->id, 'session 1', true);

        } catch (\Exception $e) {
            // Failed if the name of this entity is already in use.
            self::fail($e->getMessage());
        }

        // Update status.
        $sessionupdate = new stdClass();
        $sessionupdate->id = $session->id;
        $sessionupdate->status = session::STATUS_OPENED_REGISTRATION;
        $session = \local_mentor_core\session_api::update_session($sessionupdate);

        // Session creation.
        $session2 = session_api::create_session($training->id, 'session 2', true);

        // Update status.
        $updatedata = new stdClass();
        $updatedata->id = $session2->id;
        $updatedata->status = session::STATUS_OPENED_REGISTRATION;
        $updatedata->opento = 'all';
        $session2 = session_api::update_session($updatedata);

        $specialization = new \local_mentor_specialization\mentor_specialization();
        $trainings = $specialization->get_user_available_sessions_by_trainings([], $newuser->id);

        self::assertEquals($training->id, $trainings[0]->id);
        self::assertCount(2, $trainings[0]->sessions);
        self::assertEquals($session->id, $trainings[0]->sessions[1]->id);
        self::assertEquals($session2->id, $trainings[0]->sessions[0]->id);

        self::resetAllData();
    }

    /**
     * Test get the trainings that the user designs
     *
     * @covers  \local_mentor_core\training_api::get_trainings_user_designer
     * @covers  \local_mentor_core\training_api::get_training_by_course_id
     * @covers  \local_mentor_core\training_api::create_training
     * @covers  \local_mentor_core\training_api::get_training
     * @covers  \local_mentor_specialization\mentor_training::get_course
     * @covers  \local_mentor_specialization\mentor_training::get_context
     * @covers  \local_mentor_specialization\mentor_training::convert_for_template
     */
    public function test_get_trainings_user_designer() {
        global $CFG;
        $this->resetAfterTest();
        $this->init_config();
        self::setAdminUser();

        require_once($CFG->libdir . '/enrollib.php');
        require_once($CFG->dirroot . '/enrol/self/externallib.php');

        // Create simple user.
        $user = new stdClass();
        $user->id = $this->init_create_user();

        /** @var mentor_training $training */
        $training = training_api::create_training($this->get_training_data());

        // Create self enrol instance.
        $plugin = enrol_get_plugin('self');
        $instance = (object) $plugin->get_instance_defaults();
        $instance->status = 0;
        $instance->id = '';
        $instance->courseid = $training->get_course()->id;
        $instance->customint1 = 0;
        $instance->customint2 = 0;
        $instance->customint3 = 0; // Max participants.
        $instance->customint4 = 1;
        $instance->customint5 = 0;
        $instance->customint6 = 1; // Enable.
        $instance->name = '';
        $instance->password = '';
        $instance->customtext1 = '';
        $instance->returnurl = '';
        $instance->expirythreshold = 0;
        $instance->enrolstartdate = 0;
        $instance->enrolenddate = 0;
        $fields = (array) $instance;
        $instanceid = $plugin->add_instance($training->get_course(), $fields);

        // Enrol user.
        self::setUser($user->id);
        \enrol_self_external::enrol_user($training->get_course()->id, null, $instanceid);
        self::setAdminUser();

        // User is enrol.
        \local_mentor_core\profile_api::role_assign('concepteur', $user->id, $training->get_context());
        self::assertCount(1, \local_mentor_core\training_api::get_trainings_user_designer($user));

        // User id designer.
        self::assertTrue(has_capability('local/trainings:update', $training->get_context(), $user));

        self::resetAllData();
    }

    /**
     * Test get training form
     *
     * @covers  \local_mentor_core\training_api::get_training_form
     * @covers  \local_mentor_core\specialization::__construct
     * @covers  \local_mentor_core\specialization::get_instance
     * @covers  \local_mentor_core\specialization::get_specialization
     * @covers  \local_mentor_specialization\mentor_specialization::get_specialization
     * @covers  \local_mentor_specialization\mentor_specialization::get_training_form
     * @covers  \local_mentor_specialization\training_form::__construct
     * @covers  \local_mentor_specialization\training_form::definition
     */
    public function test_get_training_form() {
        global $CFG;
        $this->resetAfterTest();
        $this->reset_singletons();
        $this->init_config();
        self::setAdminUser();

        $training = $this->get_training_data();
        $entity = \local_mentor_core\entity_api::get_entity($training->categoryid);

        $forminfos = new stdClass();
        $forminfos->entity = $entity;
        $forminfos->logourl = 'mentor.fr';
        $forminfos->actionurl = 'mentor.fr';
        $forminfos->publish = false;
        $url = 'mentor.fr';

        $form = \local_mentor_core\training_api::get_training_form($url, $forminfos);

        self::assertEquals('local_mentor_specialization\training_form', get_class($form));

        self::resetAllData();
    }

    /**
     * Test get trainings template
     *
     * @covers  \local_mentor_core\training_api::get_trainings_template
     * @covers  \local_mentor_core\specialization::__construct
     * @covers  \local_mentor_core\specialization::get_instance
     * @covers  \local_mentor_core\specialization::get_specialization
     * @covers  \local_mentor_specialization\mentor_specialization::get_specialization
     * @covers  \local_mentor_specialization\mentor_specialization::get_trainings_template
     */
    public function test_get_trainings_template() {
        $this->resetAfterTest();
        $this->reset_singletons();
        $this->init_config();
        self::setAdminUser();

        $defaulttemplate = 'local_trainings/local_trainings';
        $specializationtemplate = 'local_mentor_specialization/trainings';

        $template = training_api::get_trainings_template($defaulttemplate);
        self::assertEquals($specializationtemplate, $template);

        self::resetAllData();
    }

    /**
     * Test get trainings template
     *
     * @covers  \local_mentor_core\training_api::get_trainings_javascript
     * @covers  \local_mentor_core\specialization::__construct
     * @covers  \local_mentor_core\specialization::get_instance
     * @covers  \local_mentor_core\specialization::get_specialization
     * @covers  \local_mentor_specialization\mentor_specialization::get_specialization
     * @covers  \local_mentor_specialization\mentor_specialization::get_trainings_javascript
     */
    public function test_get_trainings_javascript() {
        $this->resetAfterTest();
        $this->reset_singletons();
        $this->init_config();
        self::setAdminUser();

        $defaultjavascript = 'local_trainings/local_trainings';
        $specializationjavascript = 'local_mentor_specialization/trainings';

        $javascript = training_api::get_trainings_javascript($defaultjavascript);
        self::assertEquals($specializationjavascript, $javascript);

        self::resetAllData();
    }

    /**
     * Test move training
     *
     * @covers  \local_mentor_core\training_api::create_training
     * @covers  \local_mentor_core\training_api::get_training
     * @covers  \local_mentor_core\training_api::move_training
     */
    public function test_move_training_ok() {
        $this->resetAfterTest();
        $this->reset_singletons();
        $this->init_config();
        self::setAdminUser();

        /** @var training $training */
        $training = training_api::create_training($this->get_training_data());

        $otherentitydata = [
                'name' => 'New Entity 2',
                'shortname' => 'New Entity 2',
                'regions' => [5], // Corse.
        ];

        try {
            // Get entity object for default category.
            $otherentityid = \local_mentor_core\entity_api::create_entity($otherentitydata);
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        // Move training.
        \local_mentor_core\training_api::move_training($training->id, $otherentityid);

        // Refresh training data.
        $refreshtraining = training_api::get_training($training->id, true);

        // Check if new training entity is entity used to move training.
        self::assertEquals($refreshtraining->get_entity()->id, $otherentityid);

        self::resetAllData();
    }

    /**
     * Test get_trainings_user_designer
     * With hidden entity
     *
     * @covers  \local_mentor_core\training_api::get_trainings_user_designer
     */
    public function test_get_trainings_user_designer_with_entity_hidden() {
        global $CFG;
        $this->resetAfterTest();
        $this->reset_singletons();
        $this->init_config();
        self::setAdminUser();

        require_once($CFG->libdir . '/enrollib.php');
        require_once($CFG->dirroot . '/enrol/self/externallib.php');

        // Create simple user.
        $user = new stdClass();
        $user->id = $this->init_create_user();

        /** @var training $training */
        $training = training_api::create_training($this->get_training_data());

        // Create self enrol instance.
        $plugin = enrol_get_plugin('self');
        $instance = (object) $plugin->get_instance_defaults();
        $instance->status = 0;
        $instance->id = '';
        $instance->courseid = $training->get_course()->id;
        $instance->customint1 = 0;
        $instance->customint2 = 0;
        $instance->customint3 = 0; // Max participants.
        $instance->customint4 = 1;
        $instance->customint5 = 0;
        $instance->customint6 = 1; // Enable.
        $instance->name = '';
        $instance->password = '';
        $instance->customtext1 = '';
        $instance->returnurl = '';
        $instance->expirythreshold = 0;
        $instance->enrolstartdate = 0;
        $instance->enrolenddate = 0;
        $fields = (array) $instance;
        $instanceid = $plugin->add_instance($training->get_course(), $fields);

        // Enrol user.
        self::setUser($user->id);
        \enrol_self_external::enrol_user($training->get_course()->id, null, $instanceid);
        self::setAdminUser();

        // Enrol the user into a random course (not a training course!).
        $randomcourse = self::getDataGenerator()->create_course();
        self::getDataGenerator()->enrol_user($user->id, $randomcourse->id);

        // Check user trainings enrolments.
        \local_mentor_core\profile_api::role_assign('editingteacher', $user->id, $training->get_context());
        $trainingsdesigner = \local_mentor_core\training_api::get_trainings_user_designer($user);
        self::assertCount(1, $trainingsdesigner);
        self::assertEquals($training->id, $trainingsdesigner[0]->id);

        // User is designer.
        self::assertTrue(has_capability('local/trainings:update', $training->get_context(), $user));

        $training->get_entity()->get_main_entity()->update_visibility(1);

        // Is enrol but entity is hidden.
        $trainingsdesigner = \local_mentor_core\training_api::get_trainings_user_designer($user);
        self::assertCount(0, $trainingsdesigner);

        self::resetAllData();
    }

    /**
     * Test count_trainings_by_entity
     *
     * @covers \local_mentor_core\training_api::count_trainings_by_entity
     */
    public function test_count_trainings_by_entity() {
        global $DB;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        // Remove the miscelleanous category.
        $DB->delete_records('course_categories', array('id' => 1));

        self::setAdminUser();

        // Create training in main entity.
        $entityid1 = \local_mentor_core\entity_api::create_entity(['name' => 'New Entity 1', 'shortname' => 'New Entity 1']);
        $entity1 = \local_mentor_core\entity_api::get_entity($entityid1);
        $trainingdata1 = $this->get_training_data('training1', $entityid1);
        \local_mentor_core\training_api::create_training($trainingdata1);

        $data = new \stdClass();
        $data->entityid = $entity1->id;
        $data->onlymainentity = true;

        // Test training in main entity.
        self::assertEquals(1, \local_mentor_core\training_api::count_trainings_by_entity($data));

        // Create training in other main entity.
        $entityid2 = \local_mentor_core\entity_api::create_entity([
                'name' => 'New Entity 2',
                'shortname' => 'New Entity 2'
        ]);
        $entity2 = \local_mentor_core\entity_api::get_entity($entityid2);
        $trainingdata2 = $this->get_training_data('training2', $entityid2);
        \local_mentor_core\training_api::create_training($trainingdata2);

        $data = new \stdClass();
        $data->entityid = $entity2->id;
        $data->onlymainentity = true;

        // Test training in other main entity.
        self::assertEquals(1, \local_mentor_core\training_api::count_trainings_by_entity($data));

        // Create training in sub entity.
        $entityid3 = \local_mentor_core\entity_api::create_entity([
                'name' => 'New Entity 3',
                'parentid' => $entity1->id
        ]);
        $entity3 = \local_mentor_core\entity_api::get_entity($entityid3);
        $trainingdata3 = $this->get_training_data('training3', $entityid3);
        \local_mentor_core\training_api::create_training($trainingdata3);

        $data = new \stdClass();
        $data->entityid = $entity1->id;
        $data->onlymainentity = true;

        // Test training in main entity with sub entity.
        self::assertEquals(1, \local_mentor_core\training_api::count_trainings_by_entity($data));

        $data = new \stdClass();
        $data->entityid = $entity1->id;
        $data->onlymainentity = false;

        // Test training in main entity with sub entity.
        self::assertEquals(2, \local_mentor_core\training_api::count_trainings_by_entity($data));

        self::resetAllData();
    }
}
