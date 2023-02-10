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
 * Tests for dbinterface class
 *
 * @package    local_mentor_specialization
 * @copyright  2021 Edunao SAS (contact@edunao.com)
 * @author     adrien <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

class local_mentor_specialization_dbinterface_testcase extends advanced_testcase {

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
        // Reset the mentor core db interface singleton.
        $dbinterface = \local_mentor_core\database_interface::get_instance();
        $reflection  = new ReflectionClass($dbinterface);
        $instance    = $reflection->getProperty('instance');
        $instance->setAccessible(true); // Now we can modify that :).
        $instance->setValue(null, null); // Instance is gone.
        $instance->setAccessible(false); // Clean up.

        \local_mentor_core\training_api::clear_cache();
    }

    /**
     * Init entities data
     *
     * @return array
     */
    public function get_entities_data() {
        $this->init_database();

        return [
            'name'      => 'New Entity 1',
            'shortname' => 'New Entity 1',
            'regions'   => [5], // Corse.
            'userid'    => 2  // Set the admin user as manager of the entity.
        ];
    }

    /**
     * Initialization of the database for the tests
     */
    public function init_database() {
        global $DB;

        // Delete Miscellaneous category.
        $DB->delete_records('course_categories', array('id' => 1));
    }

    public function init_competencies() {
        global $CFG;

        $text      = file_get_contents($CFG->dirroot .
                                       '/local/mentor_specialization/data/competencies/competencies_comma_separated.csv');
        $encoding  = 'UTF-8';
        $delimiter = 'comma';
        $importer  = new \tool_lpimportcsv\framework_importer($text, $encoding, $delimiter, 0, null, true);
        $importer->import();
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

        set_config('collections', "accompagnement|Accompagnement des transitions professionnelles|#CECECE
preparation|Préparation aux épreuves de concours et d\'examens professionnels|rgba(255, 153, 64, 0.4)
transformation|Transformation de l\'action publique|rgba(255, 141, 126, 0.4)",
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
            $data->termsregistration       = 'inscriptionlibre';
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
     * Init training creation
     *
     * @return \local_mentor_core\training
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
                'shortname' => 'New Entity 1'
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
     * @return \local_mentor_core\session
     * @throws moodle_exception
     */
    public function init_session_creation() {
        // Create training.
        $training = $this->init_training_creation();

        $sessionname = 'TESTUNITCREATESESSION';

        // Test standard session creation.
        return \local_mentor_core\session_api::create_session($training->id, $sessionname, true);
    }

    /**
     * Test get_category_option
     *
     * @covers \local_mentor_specialization\database_interface::get_category_option
     */
    public function test_get_category_option() {
        $this->resetAfterTest(true);
        $this->init_config();
        self::setAdminUser();

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();

        $entitydata = $this->get_entities_data();

        // Create entity.
        try {
            $entityid = \local_mentor_core\entity_api::create_entity($entitydata);
        } catch (\Exception $e) {
            // Failed if the name of this entity is already in use.
            self::fail($e->getMessage());
        }

        $categoryoption = $dbinterface->get_category_option($entityid, 'regionid');

        self::assertEquals($categoryoption->value, implode(',', $entitydata['regions']));

        self::resetAllData();
    }

    /**
     * Test get_category_options
     *
     * @covers \local_mentor_specialization\database_interface::get_category_options
     */
    public function test_get_category_options() {
        $this->resetAfterTest(true);
        $this->init_config();
        self::setAdminUser();

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();

        $entitydata = $this->get_entities_data();

        // Create entity.
        try {
            $entityid = \local_mentor_core\entity_api::create_entity($entitydata);
        } catch (\Exception $e) {
            // Failed if the name of this entity is already in use.
            self::fail($e->getMessage());
        }

        $categoryoptions = $dbinterface->get_category_options($entityid, 'regionid');

        self::assertCount(1, $categoryoptions);

        self::resetAllData();
    }

    /**
     * Test update_entity_region
     *
     * @covers \local_mentor_specialization\database_interface::update_entity_regions
     */
    public function test_update_entity_regions() {
        $this->resetAfterTest(true);
        $this->init_config();
        self::setAdminUser();

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();

        $entitydata = $this->get_entities_data();
        unset($entitydata['regions']);

        // Create entity.
        try {
            $entityid = \local_mentor_core\entity_api::create_entity($entitydata);
        } catch (\Exception $e) {
            // Failed if the name of this entity is already in use.
            self::fail($e->getMessage());
        }

        // Update entity without region.
        self::assertTrue($dbinterface->update_entity_regions($entityid, 10));

        // Update entity with region.
        self::assertTrue($dbinterface->update_entity_regions($entityid, 11));

        self::resetAllData();
    }

    /**
     * Test get_cohorts_by_region
     *
     * @covers \local_mentor_specialization\database_interface::get_cohorts_by_region
     */
    public function test_get_cohorts_by_region() {
        $this->resetAfterTest(true);
        $this->init_config();
        self::setAdminUser();

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();

        $entitydata = $this->get_entities_data();

        // Create entity.
        $entityid = \local_mentor_core\entity_api::create_entity($entitydata);
        $entity   = \local_mentor_core\entity_api::get_entity($entityid);

        $resultrequest = $dbinterface->get_cohorts_by_region($entitydata['regions'][0]);

        self::assertCount(1, $resultrequest);
        self::assertEquals($entity->get_cohort()->id, current($resultrequest)->id);

        self::resetAllData();
    }

    /**
     * Test get_all_regions
     *
     * @covers \local_mentor_specialization\database_interface::get_all_regions
     * @covers ::local_mentor_specialization_get_regions_and_departments
     */
    public function test_get_all_regions() {
        $this->resetAfterTest(true);
        $this->init_config();
        self::setAdminUser();

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();

        $resultrequest = $dbinterface->get_all_regions();

        $listregionresultrequest = array_map(function($a) {
            return $a->name;
        }, $resultrequest);

        self::assertEquals(array_keys(local_mentor_specialization_get_regions_and_departments()),
            array_values($listregionresultrequest));

        self::resetAllData();
    }

    /**
     * Test get_skills
     *
     * @covers \local_mentor_specialization\database_interface::get_skills
     */
    public function test_get_skills() {
        global $DB;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();
        $this->setOutputCallback(function() {
        });

        self::setAdminUser();
        $this->init_competencies();

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();

        $allskills  = $dbinterface->get_skills();
        $skillsdata = $DB->get_records_sql('
                SELECT *
                FROM {competency} c
                WHERE c.parentid != 0
                ORDER BY c.parentid, c.sortorder
            ');

        foreach ($skillsdata as $skilldata) {
            self::assertEquals($skilldata->shortname, $allskills[$skilldata->idnumber]);
        }

        self::resetAllData();
    }

    /**
     * Test convert_course_role
     *
     * @covers \local_mentor_specialization\database_interface::convert_course_role
     */
    public function test_convert_course_role_ok() {
        global $CFG;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();
        $this->setOutputCallback(function() {
        });

        require_once($CFG->libdir . '/enrollib.php');
        require_once($CFG->dirroot . '/enrol/self/externallib.php');

        self::setAdminUser();
        $this->init_competencies();

        // Create session.
        $sessionid = $this->init_session_creation();
        $session   = \local_mentor_core\session_api::get_session($sessionid, true);
        $session->create_self_enrolment_instance();
        $session->enable_self_enrolment_instance();

        $user = self::getDataGenerator()->create_user();

        // Enrol user.
        self::setUser($user->id);
        $courseid = $session->get_course()->id;
        \enrol_self_external::enrol_user($courseid, null, $session->get_enrolment_instances_by_type('self')->id);
        self::setAdminUser();

        $oldrolename = 'participant';
        $newrolename = 'participantnonediteur';

        $oldrole = get_user_roles($session->get_context(), $user->id);

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();
        $dbinterface->convert_course_role($session->get_course()->id, $oldrolename, $newrolename);

        $newrole = get_user_roles($session->get_context(), $user->id);

        self::assertEquals(current($oldrole)->shortname, $oldrolename);
        self::assertEquals(current($newrole)->shortname, $newrolename);

        self::resetAllData();
    }

    /**
     * Test convert_course_role with not existing role
     *
     * @covers \local_mentor_specialization\database_interface::convert_course_role
     */
    public function test_convert_course_role_nok() {
        global $CFG;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();
        $this->setOutputCallback(function() {
        });

        require_once($CFG->libdir . '/enrollib.php');
        require_once($CFG->dirroot . '/enrol/self/externallib.php');

        self::setAdminUser();
        $this->init_competencies();

        // Create session.
        $sessionid = $this->init_session_creation();
        $session   = \local_mentor_core\session_api::get_session($sessionid, true);
        $session->create_self_enrolment_instance();
        $session->enable_self_enrolment_instance();

        $user = self::getDataGenerator()->create_user();

        $courseid = $session->get_course()->id;

        // Enrol user.
        self::setUser($user->id);
        \enrol_self_external::enrol_user($courseid, null, $session->get_enrolment_instances_by_type('self')->id);
        self::setAdminUser();

        $existrole1   = 'role1';
        $notexistrole = 'participant';

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();

        self::assertFalse($dbinterface->convert_course_role($courseid, $notexistrole, $existrole1));
        self::assertFalse($dbinterface->convert_course_role($courseid, $existrole1, $notexistrole));

        self::resetAllData();
    }

    /**
     * Test convert_course_role NOT OK
     * Error notification
     *
     * @covers \local_mentor_specialization\database_interface::convert_course_role
     */
    public function test_convert_course_role_nok_error_notification() {
        global $CFG, $DB;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        require_once($CFG->libdir . '/enrollib.php');
        require_once($CFG->dirroot . '/enrol/self/externallib.php');

        // Clear notification.
        $notification = \core\notification::fetch();

        self::setAdminUser();

        // Create DB Mock.
        $DB = $this->createMock(get_class($DB));

        $role     = new \stdClass();
        $role->id = 1;

        $DB->expects($this->any())
            ->method('get_record')
            ->will($this->returnValue($role));

        $DB->expects($this->once())
            ->method('execute')
            ->will($this->throwException(new \dml_exception('DB Error!!!')));

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();

        // Replace dbinterface data with database interface Mock in training Mock.
        $reflection         = new ReflectionClass($dbinterface);
        $reflectionproperty = $reflection->getProperty('db');
        $reflectionproperty->setAccessible(true);
        $reflectionproperty->setValue($dbinterface, $DB);

        self::assertFalse($dbinterface->convert_course_role(0, 'notexistrole', 'existrole1'));

        $notification = \core\notification::fetch();

        self::assertCount(1, $notification);
        self::assertEquals($notification[0]->get_message_type(), 'error');
        self::assertEquals(
            $notification[0]->get_message(),
            "ERROR : Update enrolment methods!!!\nerror/DB Error!!!\n\$a contents: "
        );

        self::resetAllData();

        // Clear notification.
        $notification = \core\notification::fetch();

        // Second notification.

        $course  = self::getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);

        // Create DB Mock.
        $DB = $this->createMock(get_class($DB));

        $role     = new \stdClass();
        $role->id = 1;

        $DB->expects($this->any())
            ->method('get_record')
            ->will($this->returnValue($role));

        $DB->expects($this->at(2))
            ->method('execute')
            ->will($this->returnValue(true));

        $DB->expects($this->at(3))
            ->method('execute')
            ->will($this->throwException(new \dml_exception('DB Error!!!')));

        // Replace dbinterface data with database interface Mock in training Mock.
        $reflection         = new ReflectionClass($dbinterface);
        $reflectionproperty = $reflection->getProperty('db');
        $reflectionproperty->setAccessible(true);
        $reflectionproperty->setValue($dbinterface, $DB);

        self::assertFalse($dbinterface->convert_course_role($course->id, 'notexistrole', 'existrole1'));

        $notification = \core\notification::fetch();

        self::assertCount(1, $notification);
        self::assertEquals($notification[0]->get_message_type(), 'error');
        self::assertEquals(
            $notification[0]->get_message(),
            "ERROR : Update role assignments!!!\nerror/DB Error!!!\n\$a contents: "
        );

        self::resetAllData();
    }

    /**
     * Test disable_course_mods
     *
     * @covers \local_mentor_specialization\database_interface::disable_course_mods
     */
    public function test_disable_course_mods_ok() {
        global $CFG;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();
        $this->setOutputCallback(function() {
        });

        require_once($CFG->libdir . '/enrollib.php');
        require_once($CFG->dirroot . '/enrol/self/externallib.php');

        self::setAdminUser();

        // Create session.
        $sessionid         = $this->init_session_creation();
        $session           = \local_mentor_core\session_api::get_session($sessionid, true);
        $sessioncourseid   = $session->get_course()->id;
        $sessioncoursemods = get_course_mods($sessioncourseid);
        $currentmod        = current($sessioncoursemods);

        self::assertCount(1, $sessioncoursemods);
        self::assertEquals($currentmod->visible, 1);

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();
        $dbinterface->disable_course_mods($sessioncourseid, $currentmod->modname);

        $sessioncoursemods = get_course_mods($sessioncourseid);
        $currentmod        = current($sessioncoursemods);

        self::assertCount(1, $sessioncoursemods);
        self::assertEquals($currentmod->visible, 0);

        self::resetAllData();
    }

    /**
     * Test is_participant
     *
     * @covers \local_mentor_specialization\database_interface::is_participant
     */
    public function test_is_participant_ok() {
        global $CFG;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();
        $this->setOutputCallback(function() {
        });

        require_once($CFG->libdir . '/enrollib.php');
        require_once($CFG->dirroot . '/enrol/self/externallib.php');

        self::setAdminUser();
        $this->init_competencies();

        // Create session.
        $sessionid = $this->init_session_creation();
        $session   = \local_mentor_core\session_api::get_session($sessionid, true);
        $session->create_self_enrolment_instance();
        $session->enable_self_enrolment_instance();

        $courseid = $session->get_course()->id;

        $user = self::getDataGenerator()->create_user();
        self::setUser($user->id);

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();

        // User is not participant.
        self::assertFalse($dbinterface->is_participant($user->id, $session->get_context()->id));

        // Enrol user.
        \enrol_self_external::enrol_user($courseid, null, $session->get_enrolment_instances_by_type('self')->id);

        // User is participant.
        self::assertTrue($dbinterface->is_participant($user->id, $session->get_context()->id));

        self::resetAllData();
    }

    /**
     * Test get_course_participants
     *
     * @covers \local_mentor_specialization\database_interface::get_course_participants
     */
    public function test_get_course_participants_ok() {
        global $CFG;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();
        $this->setOutputCallback(function() {
        });

        require_once($CFG->libdir . '/enrollib.php');
        require_once($CFG->dirroot . '/enrol/self/externallib.php');

        self::setAdminUser();
        $this->init_competencies();

        // Create session.
        $sessionid = $this->init_session_creation();
        $session   = \local_mentor_core\session_api::get_session($sessionid, true);
        $session->create_self_enrolment_instance();
        $session->enable_self_enrolment_instance();

        $courseid = $session->get_course()->id;

        $user = self::getDataGenerator()->create_user();
        self::setUser($user->id);

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();

        // Session has not participant.
        self::assertCount(0, $dbinterface->get_course_participants($session->get_context()->id));

        // Enrol user.
        \enrol_self_external::enrol_user($courseid, null, $session->get_enrolment_instances_by_type('self')->id);

        // Session has one participant.
        $sessionparticipants = $dbinterface->get_course_participants($session->get_context()->id);
        $sessionparticipant  = current($sessionparticipants);

        self::assertCount(1, $sessionparticipants);
        self::assertEquals($sessionparticipant->id, $user->id);
        self::assertEquals($sessionparticipant->firstname, $user->firstname);
        self::assertEquals($sessionparticipant->lastname, $user->lastname);

        self::resetAllData();
    }

    /**
     * Test get_sessions_by_entity_id
     *
     * @covers \local_mentor_specialization\database_interface::get_sessions_by_entity_id
     */
    public function test_get_sessions_by_entity_id_ok() {
        global $DB;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();

        // Remove the miscelleanous category.
        $DB->delete_records('course_categories', array('id' => 1));

        self::setAdminUser();

        // Create trainings.
        $trainingdata1            = $this->init_session_data(true);
        $entityid1                = \local_mentor_core\entity_api::create_entity([
            'name' => 'New Entity 1', 'shortname' => 'New Entity 1'
        ]);
        $entity1                  = \local_mentor_core\entity_api::get_entity($entityid1);
        $trainingdata1            = $this->init_training_entity($trainingdata1, $entity1);
        $training1                = \local_mentor_core\training_api::create_training($trainingdata1);
        $trainingdata2            = $this->init_session_data(true);
        $trainingdata2->name      = 'fullname2';
        $trainingdata2->shortname = 'shortname2';
        $entityid2                = \local_mentor_core\entity_api::create_entity([
            'name'      => 'New Entity 2',
            'shortname' => 'New Entity 2'
        ]);
        $entity2                  = \local_mentor_core\entity_api::get_entity($entityid2);
        $trainingdata2            = $this->init_training_entity($trainingdata2, $entity2);
        $training2                = \local_mentor_core\training_api::create_training($trainingdata2);
        $trainingdata3            = $this->init_session_data(true);
        $trainingdata3->name      = 'fullname3';
        $trainingdata3->shortname = 'shortname3';
        $entityid3                = \local_mentor_core\entity_api::create_entity([
            'name'     => 'New Entity 3',
            'parentid' => $entity1->id
        ]);
        $entity3                  = \local_mentor_core\entity_api::get_entity($entityid3);
        $trainingdata3            = $this->init_training_entity($trainingdata3, $entity3);
        $training3                = \local_mentor_core\training_api::create_training($trainingdata3);

        // Session in main entity.
        $session1 = \local_mentor_core\session_api::create_session($training1->id, 'Sessionname1', true);

        // Session in other main entity.
        $session2 = \local_mentor_core\session_api::create_session($training2->id, 'Sessionname2', true);

        // Sessions in sub-entity.
        $session3 = \local_mentor_core\session_api::create_session($training3->id, 'Sessionname3', true);

        $data                  = new stdClass();
        $data->entityid        = $entity1->id;
        $data->search          = [];
        $data->search['value'] = '';
        $data->order           = false;
        $data->start           = 0;
        $data->length          = 10;

        // Get sessions in main entity with sub-entity.
        $sessionsbyentity = $dbinterface->get_sessions_by_entity_id($data);
        self::assertCount(2, $sessionsbyentity);
        self::assertArrayHasKey($session1->id, $sessionsbyentity);
        self::assertEquals($session1->id, $sessionsbyentity[$session1->id]->id);
        self::assertEquals($session1->shortname, $sessionsbyentity[$session1->id]->shortname);
        self::assertArrayHasKey($session3->id, $sessionsbyentity);
        self::assertEquals($session3->id, $sessionsbyentity[$session3->id]->id);
        self::assertEquals($session3->shortname, $sessionsbyentity[$session3->id]->shortname);

        $data->entityid = $entity2->id;

        // Get sessions in main entity with sub-entity.
        $sessionsbyentity = $dbinterface->get_sessions_by_entity_id($data);
        self::assertCount(1, $sessionsbyentity);
        self::assertArrayHasKey($session2->id, $sessionsbyentity);
        self::assertEquals($session2->id, $sessionsbyentity[$session2->id]->id);
        self::assertEquals($session2->shortname, $sessionsbyentity[$session2->id]->shortname);

        self::resetAllData();
    }

    /**
     * Test get_sessions_by_entity_id with filter
     *
     * @covers \local_mentor_specialization\database_interface::get_sessions_by_entity_id
     * @covers \local_mentor_specialization\database_interface::generate_sessions_by_entity_id_filter
     * @covers \local_mentor_specialization\mentor_session::update
     */
    public function test_get_sessions_by_entity_id_with_filter_ok() {
        global $DB;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();

        // Remove the miscelleanous category.
        $DB->delete_records('course_categories', array('id' => 1));

        self::setAdminUser();

        // Create training in main entity.
        $trainingdata1         = $this->init_session_data(true);
        $trainingdata1->status = \local_mentor_core\training::STATUS_DRAFT;
        $entityid1             = \local_mentor_core\entity_api::create_entity([
            'name'      => 'New Entity 1',
            'shortname' => 'New Entity 1'
        ]);
        $entity1               = \local_mentor_core\entity_api::get_entity($entityid1);
        $trainingdata1         = $this->init_training_entity($trainingdata1, $entity1);
        $training1             = \local_mentor_core\training_api::create_training($trainingdata1);

        // Create training in sub entity.
        $trainingdata2             = $this->init_session_data(true);
        $trainingdata2->name       = 'fullname2';
        $trainingdata2->shortname  = 'shortname2';
        $trainingdata2->collection = 'preparation';
        $trainingdata2->status     = \local_mentor_core\training::STATUS_TEMPLATE;
        $entityid2                 = \local_mentor_core\entity_api::create_entity([
            'name'     => 'New Entity 2',
            'parentid' => $entity1->id
        ]);
        $entity2                   = \local_mentor_core\entity_api::get_entity($entityid2);
        $trainingdata2             = $this->init_training_entity($trainingdata2, $entity2);
        $training2                 = \local_mentor_core\training_api::create_training($trainingdata2);

        // Create training in sub entity.
        $trainingdata3             = $this->init_session_data(true);
        $trainingdata3->name       = 'fullname3';
        $trainingdata3->shortname  = 'shortname3';
        $trainingdata3->collection = 'accompagnement, transformation';
        $trainingdata3->status     = \local_mentor_core\training::STATUS_ELABORATION_COMPLETED;
        $entityid3                 = \local_mentor_core\entity_api::create_entity([
            'name'     => 'New Entity 3',
            'parentid' => $entity1->id
        ]);
        $entity3                   = \local_mentor_core\entity_api::get_entity($entityid3);
        $trainingdata3             = $this->init_training_entity($trainingdata3, $entity3);
        $training3                 = \local_mentor_core\training_api::create_training($trainingdata3);

        // Session in main entity.
        $session1               = \local_mentor_core\session_api::create_session($training1->id, 'Sessionname1', true);
        $data                   = new \stdClass();
        $data->status           = \local_mentor_core\session::STATUS_IN_PROGRESS;
        $data->sessionstartdate = 5;
        $session1->update($data);

        // Sessions in sub-entity.
        $session2               = \local_mentor_core\session_api::create_session($training2->id, 'Sessionname2', true);
        $data                   = new \stdClass();
        $data->status           = \local_mentor_core\session::STATUS_OPENED_REGISTRATION;
        $data->sessionstartdate = 10;
        $session2->update($data);

        $session3 = \local_mentor_core\session_api::create_session($training3->id, 'Sessionname3', true);
        $session3->update_status(\local_mentor_core\session::STATUS_COMPLETED);
        $data                   = new \stdClass();
        $data->status           = \local_mentor_core\session::STATUS_COMPLETED;
        $data->sessionstartdate = 15;
        $session3->update($data);

        $data                  = new stdClass();
        $data->entityid        = $entity1->id;
        $data->search          = [];
        $data->search['value'] = '';
        $data->order           = false;
        $data->start           = 0;
        $data->length          = 10;

        // Get sessions in main entity without filter.
        $sessionsbyentity = $dbinterface->get_sessions_by_entity_id($data);
        self::assertCount(3, $sessionsbyentity);
        self::assertArrayHasKey($session1->id, $sessionsbyentity);
        self::assertEquals($session1->id, $sessionsbyentity[$session1->id]->id);
        self::assertEquals($session1->shortname, $sessionsbyentity[$session1->id]->shortname);
        self::assertArrayHasKey($session2->id, $sessionsbyentity);
        self::assertEquals($session2->id, $sessionsbyentity[$session2->id]->id);
        self::assertEquals($session2->shortname, $sessionsbyentity[$session2->id]->shortname);
        self::assertArrayHasKey($session3->id, $sessionsbyentity);
        self::assertEquals($session3->id, $sessionsbyentity[$session3->id]->id);
        self::assertEquals($session3->shortname, $sessionsbyentity[$session3->id]->shortname);

        $data->filters              = [];
        $data->filters['subentity'] = [$entity2->id];

        // Get sessions in main entity with filter (one sub entity).
        $sessionsbyentity = $dbinterface->get_sessions_by_entity_id($data);
        self::assertCount(1, $sessionsbyentity);
        self::assertArrayHasKey($session2->id, $sessionsbyentity);
        self::assertEquals($session2->id, $sessionsbyentity[$session2->id]->id);
        self::assertEquals($session2->shortname, $sessionsbyentity[$session2->id]->shortname);

        $data->filters['subentity'] = [$entity2->id, $entity3->id];

        // Get sessions in main entity with filter (Two sub entity).
        $sessionsbyentity = $dbinterface->get_sessions_by_entity_id($data);
        self::assertCount(2, $sessionsbyentity);
        self::assertArrayHasKey($session2->id, $sessionsbyentity);
        self::assertEquals($session2->id, $sessionsbyentity[$session2->id]->id);
        self::assertEquals($session2->shortname, $sessionsbyentity[$session2->id]->shortname);
        self::assertArrayHasKey($session3->id, $sessionsbyentity);
        self::assertEquals($session3->id, $sessionsbyentity[$session3->id]->id);
        self::assertEquals($session3->shortname, $sessionsbyentity[$session3->id]->shortname);

        $data->filters               = [];
        $data->filters['collection'] = ['accompagnement'];

        // Get sessions in main entity with filter (one collection).
        $sessionsbyentity = $dbinterface->get_sessions_by_entity_id($data);
        self::assertCount(2, $sessionsbyentity);
        self::assertArrayHasKey($session1->id, $sessionsbyentity);
        self::assertEquals($session1->id, $sessionsbyentity[$session1->id]->id);
        self::assertEquals($session1->shortname, $sessionsbyentity[$session1->id]->shortname);
        self::assertArrayHasKey($session3->id, $sessionsbyentity);
        self::assertEquals($session3->id, $sessionsbyentity[$session3->id]->id);
        self::assertEquals($session3->shortname, $sessionsbyentity[$session3->id]->shortname);

        $data->filters['collection'] = ['preparation', 'transformation'];

        // Get sessions in main entity with filter (Two collections).
        $sessionsbyentity = $dbinterface->get_sessions_by_entity_id($data);
        self::assertCount(2, $sessionsbyentity);
        self::assertArrayHasKey($session2->id, $sessionsbyentity);
        self::assertEquals($session2->id, $sessionsbyentity[$session2->id]->id);
        self::assertEquals($session2->shortname, $sessionsbyentity[$session2->id]->shortname);
        self::assertArrayHasKey($session3->id, $sessionsbyentity);
        self::assertEquals($session3->id, $sessionsbyentity[$session3->id]->id);
        self::assertEquals($session3->shortname, $sessionsbyentity[$session3->id]->shortname);

        $data->filters           = [];
        $data->filters['status'] = [\local_mentor_core\session::STATUS_IN_PROGRESS];

        // Get sessions in main entity with filter (one status).
        $sessionsbyentity = $dbinterface->get_sessions_by_entity_id($data);
        self::assertCount(1, $sessionsbyentity);
        self::assertArrayHasKey($session1->id, $sessionsbyentity);
        self::assertEquals($session1->id, $sessionsbyentity[$session1->id]->id);
        self::assertEquals($session1->shortname, $sessionsbyentity[$session1->id]->shortname);

        $data->filters['status'] = [
            \local_mentor_core\session::STATUS_OPENED_REGISTRATION,
            \local_mentor_core\session::STATUS_COMPLETED
        ];

        // Get sessions in main entity with filter (Two status).
        $sessionsbyentity = $dbinterface->get_sessions_by_entity_id($data);
        self::assertCount(2, $sessionsbyentity);
        self::assertArrayHasKey($session2->id, $sessionsbyentity);
        self::assertEquals($session2->id, $sessionsbyentity[$session2->id]->id);
        self::assertEquals($session2->shortname, $sessionsbyentity[$session2->id]->shortname);
        self::assertArrayHasKey($session3->id, $sessionsbyentity);
        self::assertEquals($session3->id, $sessionsbyentity[$session3->id]->id);
        self::assertEquals($session3->shortname, $sessionsbyentity[$session3->id]->shortname);

        $data->filters              = [];
        $data->filters['startdate'] = 11;

        // Get sessions in main entity with filter (start date).
        $sessionsbyentity = $dbinterface->get_sessions_by_entity_id($data);
        self::assertCount(1, $sessionsbyentity);
        self::assertArrayHasKey($session3->id, $sessionsbyentity);
        self::assertEquals($session3->id, $sessionsbyentity[$session3->id]->id);
        self::assertEquals($session3->shortname, $sessionsbyentity[$session3->id]->shortname);

        $data->filters            = [];
        $data->filters['enddate'] = 11;

        // Get sessions in main entity with filter (end date).
        $sessionsbyentity = $dbinterface->get_sessions_by_entity_id($data);
        self::assertCount(2, $sessionsbyentity);
        self::assertArrayHasKey($session1->id, $sessionsbyentity);
        self::assertEquals($session1->id, $sessionsbyentity[$session1->id]->id);
        self::assertEquals($session1->shortname, $sessionsbyentity[$session1->id]->shortname);
        self::assertArrayHasKey($session2->id, $sessionsbyentity);
        self::assertEquals($session2->id, $sessionsbyentity[$session2->id]->id);
        self::assertEquals($session2->shortname, $sessionsbyentity[$session2->id]->shortname);

        $data->filters              = [];
        $data->filters['startdate'] = 9;
        $data->filters['enddate']   = 20;

        // Get sessions in main entity with filter (start date and end date).
        $sessionsbyentity = $dbinterface->get_sessions_by_entity_id($data);
        self::assertCount(2, $sessionsbyentity);
        self::assertArrayHasKey($session2->id, $sessionsbyentity);
        self::assertEquals($session2->id, $sessionsbyentity[$session2->id]->id);
        self::assertEquals($session2->shortname, $sessionsbyentity[$session2->id]->shortname);
        self::assertArrayHasKey($session3->id, $sessionsbyentity);
        self::assertEquals($session3->id, $sessionsbyentity[$session3->id]->id);
        self::assertEquals($session3->shortname, $sessionsbyentity[$session3->id]->shortname);

        self::resetAllData();
    }

    /**
     * Test get_sessions_by_entity_id with search
     *
     * @covers \local_mentor_specialization\database_interface::get_sessions_by_entity_id
     * @covers \local_mentor_specialization\database_interface::generate_sessions_by_entity_id_search
     */
    public function test_get_sessions_by_entity_id_with_search_ok() {
        global $DB;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();

        // Remove the miscelleanous category.
        $DB->delete_records('course_categories', array('id' => 1));

        self::setAdminUser();

        // Create training in main entity.
        $trainingdata1         = $this->init_session_data(true);
        $trainingdata1->status = \local_mentor_core\training::STATUS_DRAFT;
        $entityid1             = \local_mentor_core\entity_api::create_entity([
            'name'      => 'New Entity 1',
            'shortname' => 'New Entity 1'
        ]);
        $entity1               = \local_mentor_core\entity_api::get_entity($entityid1);
        $trainingdata1         = $this->init_training_entity($trainingdata1, $entity1);
        $training1             = \local_mentor_core\training_api::create_training($trainingdata1);

        // Create training in sub entity.
        $trainingdata2             = $this->init_session_data(true);
        $trainingdata2->name       = 'fullname2';
        $trainingdata2->shortname  = 'shortname2';
        $trainingdata2->collection = 'preparation';
        $trainingdata2->status     = \local_mentor_core\training::STATUS_TEMPLATE;
        $entityid2                 = \local_mentor_core\entity_api::create_entity([
            'name'     => 'New Entity 2',
            'parentid' => $entity1->id
        ]);
        $entity2                   = \local_mentor_core\entity_api::get_entity($entityid2);
        $trainingdata2             = $this->init_training_entity($trainingdata2, $entity2);
        $training2                 = \local_mentor_core\training_api::create_training($trainingdata2);

        // Create training in sub entity.
        $trainingdata3             = $this->init_session_data(true);
        $trainingdata3->name       = 'fullname3';
        $trainingdata3->shortname  = 'shortname3';
        $trainingdata3->collection = 'accompagnement, transformation';
        $trainingdata3->status     = \local_mentor_core\training::STATUS_ELABORATION_COMPLETED;
        $entityid3                 = \local_mentor_core\entity_api::create_entity([
            'name'     => 'New Entity 3',
            'parentid' => $entity1->id
        ]);
        $entity3                   = \local_mentor_core\entity_api::get_entity($entityid3);
        $trainingdata3             = $this->init_training_entity($trainingdata3, $entity3);
        $training3                 = \local_mentor_core\training_api::create_training($trainingdata3);

        // Session in main entity.
        $session1               = \local_mentor_core\session_api::create_session($training1->id, 'Sessionname1', true);
        $data                   = new \stdClass();
        $data->status           = \local_mentor_core\session::STATUS_IN_PROGRESS;
        $data->sessionstartdate = 5;
        $session1->update($data);

        // Sessions in sub-entity.
        $session2               = \local_mentor_core\session_api::create_session($training2->id, 'SessionnameBis2', true);
        $data                   = new \stdClass();
        $data->status           = \local_mentor_core\session::STATUS_OPENED_REGISTRATION;
        $data->sessionstartdate = 10;
        $session2->update($data);

        $session3 = \local_mentor_core\session_api::create_session($training3->id, 'SessionnameBis3', true);
        $session3->update_status(\local_mentor_core\session::STATUS_COMPLETED);
        $data                   = new \stdClass();
        $data->status           = \local_mentor_core\session::STATUS_COMPLETED;
        $data->sessionstartdate = 15;
        $session3->update($data);

        $data                  = new stdClass();
        $data->entityid        = $entity1->id;
        $data->search          = [];
        $data->search['value'] = '';
        $data->order           = false;
        $data->start           = 0;
        $data->length          = 10;

        // Get sessions in main entity without search.
        $sessionsbyentity = $dbinterface->get_sessions_by_entity_id($data);
        self::assertCount(3, $sessionsbyentity);
        self::assertArrayHasKey($session1->id, $sessionsbyentity);
        self::assertEquals($session1->id, $sessionsbyentity[$session1->id]->id);
        self::assertEquals($session1->shortname, $sessionsbyentity[$session1->id]->shortname);
        self::assertArrayHasKey($session2->id, $sessionsbyentity);
        self::assertEquals($session2->id, $sessionsbyentity[$session2->id]->id);
        self::assertEquals($session2->shortname, $sessionsbyentity[$session2->id]->shortname);
        self::assertArrayHasKey($session3->id, $sessionsbyentity);
        self::assertEquals($session3->id, $sessionsbyentity[$session3->id]->id);
        self::assertEquals($session3->shortname, $sessionsbyentity[$session3->id]->shortname);

        $data->search['value'] = 'Sessionname';

        // Get sessions in main entity without search (Session name).
        $sessionsbyentity = $dbinterface->get_sessions_by_entity_id($data);
        self::assertCount(3, $sessionsbyentity);
        self::assertArrayHasKey($session1->id, $sessionsbyentity);
        self::assertEquals($session1->id, $sessionsbyentity[$session1->id]->id);
        self::assertEquals($session1->shortname, $sessionsbyentity[$session1->id]->shortname);
        self::assertArrayHasKey($session2->id, $sessionsbyentity);
        self::assertEquals($session2->id, $sessionsbyentity[$session2->id]->id);
        self::assertEquals($session2->shortname, $sessionsbyentity[$session2->id]->shortname);
        self::assertArrayHasKey($session3->id, $sessionsbyentity);
        self::assertEquals($session3->id, $sessionsbyentity[$session3->id]->id);
        self::assertEquals($session3->shortname, $sessionsbyentity[$session3->id]->shortname);

        $data->search['value'] = 'SessionnameBis';

        // Get sessions in main entity without search (Session name).
        $sessionsbyentity = $dbinterface->get_sessions_by_entity_id($data);
        self::assertCount(2, $sessionsbyentity);
        self::assertArrayHasKey($session2->id, $sessionsbyentity);
        self::assertEquals($session2->id, $sessionsbyentity[$session2->id]->id);
        self::assertEquals($session2->shortname, $sessionsbyentity[$session2->id]->shortname);
        self::assertArrayHasKey($session3->id, $sessionsbyentity);
        self::assertEquals($session3->id, $sessionsbyentity[$session3->id]->id);
        self::assertEquals($session3->shortname, $sessionsbyentity[$session3->id]->shortname);

        $data->search['value'] = 'fullname3';

        // Get sessions in main entity without search (Training name).
        $sessionsbyentity = $dbinterface->get_sessions_by_entity_id($data);
        self::assertCount(1, $sessionsbyentity);
        self::assertArrayHasKey($session3->id, $sessionsbyentity);
        self::assertEquals($session3->id, $sessionsbyentity[$session3->id]->id);
        self::assertEquals($session3->shortname, $sessionsbyentity[$session3->id]->shortname);

        $data->search['value'] = 'New Entity 1';

        // Get sessions in main entity without search (Entity name).
        $sessionsbyentity = $dbinterface->get_sessions_by_entity_id($data);
        self::assertCount(1, $sessionsbyentity);
        self::assertArrayHasKey($session1->id, $sessionsbyentity);
        self::assertEquals($session1->id, $sessionsbyentity[$session1->id]->id);
        self::assertEquals($session1->shortname, $sessionsbyentity[$session1->id]->shortname);

        self::resetAllData();
    }

    /**
     * Test get_sessions_by_entity_id with order
     *
     * @covers \local_mentor_specialization\database_interface::get_sessions_by_entity_id
     */
    public function test_get_sessions_by_entity_id_with_order_ok() {
        global $DB;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();

        // Remove the miscelleanous category.
        $DB->delete_records('course_categories', array('id' => 1));

        self::setAdminUser();

        // Create training in main entity.
        $trainingdata1         = $this->init_session_data(true);
        $trainingdata1->status = \local_mentor_core\training::STATUS_DRAFT;
        $entityid1             = \local_mentor_core\entity_api::create_entity([
            'name'      => 'New Entity 1',
            'shortname' => 'New Entity 1'
        ]);
        $entity1               = \local_mentor_core\entity_api::get_entity($entityid1);
        $trainingdata1         = $this->init_training_entity($trainingdata1, $entity1);
        $training1             = \local_mentor_core\training_api::create_training($trainingdata1);

        // Create training in sub entity.
        $trainingdata2             = $this->init_session_data(true);
        $trainingdata2->name       = 'fullname2';
        $trainingdata2->shortname  = 'shortname2';
        $trainingdata2->collection = 'preparation';
        $trainingdata2->status     = \local_mentor_core\training::STATUS_TEMPLATE;
        $entityid2                 = \local_mentor_core\entity_api::create_entity([
            'name'     => 'New Entity 3',
            'parentid' => $entity1->id
        ]);
        $entity2                   = \local_mentor_core\entity_api::get_entity($entityid2);
        $trainingdata2             = $this->init_training_entity($trainingdata2, $entity2);
        $training2                 = \local_mentor_core\training_api::create_training($trainingdata2);

        // Create training in sub entity.
        $trainingdata3             = $this->init_session_data(true);
        $trainingdata3->name       = 'fullname3';
        $trainingdata3->shortname  = 'shortname3';
        $trainingdata3->collection = 'accompagnement, transformation';
        $trainingdata3->status     = \local_mentor_core\training::STATUS_ELABORATION_COMPLETED;
        $entityid3                 = \local_mentor_core\entity_api::create_entity([
            'name'     => 'New Entity 2',
            'parentid' => $entity1->id
        ]);
        $entity3                   = \local_mentor_core\entity_api::get_entity($entityid3);
        $trainingdata3             = $this->init_training_entity($trainingdata3, $entity3);
        $training3                 = \local_mentor_core\training_api::create_training($trainingdata3);

        // Session in main entity.
        $session1               = \local_mentor_core\session_api::create_session($training1->id, 'Sessionname1', true);
        $data                   = new \stdClass();
        $data->id               = $session1->id;
        $data->status           = \local_mentor_core\session::STATUS_IN_PROGRESS;
        $data->sessionstartdate = 5;
        $data->sessionnumber    = 20;
        $DB->update_record('session', $data);

        // Sessions in sub-entity.
        $session2               = \local_mentor_core\session_api::create_session($training2->id, 'SessionnameBis2', true);
        $data                   = new \stdClass();
        $data->status           = \local_mentor_core\session::STATUS_OPENED_REGISTRATION;
        $data->sessionstartdate = 2;
        $session2->update($data);

        $session3 = \local_mentor_core\session_api::create_session($training3->id, 'SessionnameBis3', true);
        $session3->update_status(\local_mentor_core\session::STATUS_COMPLETED);
        $data                   = new \stdClass();
        $data->status           = \local_mentor_core\session::STATUS_COMPLETED;
        $data->sessionstartdate = 15;
        $session3->update($data);

        $data                  = new stdClass();
        $data->entityid        = $entity1->id;
        $data->search          = [];
        $data->search['value'] = '';
        $data->order           = false;
        $data->start           = 0;
        $data->length          = 10;

        // Get sessions in main entity without order.
        $sessionsbyentity = $dbinterface->get_sessions_by_entity_id($data);
        self::assertCount(3, $sessionsbyentity);
        self::assertArrayHasKey($session1->id, $sessionsbyentity);
        self::assertArrayHasKey($session2->id, $sessionsbyentity);
        self::assertArrayHasKey($session3->id, $sessionsbyentity);
        $sessionsbyentitydata = array_values($sessionsbyentity);
        self::assertEquals($session1->id, $sessionsbyentitydata[0]->id);
        self::assertEquals($session1->shortname, $sessionsbyentitydata[0]->shortname);
        self::assertEquals($session2->id, $sessionsbyentitydata[1]->id);
        self::assertEquals($session2->shortname, $sessionsbyentitydata[1]->shortname);
        self::assertEquals($session3->id, $sessionsbyentitydata[2]->id);
        self::assertEquals($session3->shortname, $sessionsbyentitydata[2]->shortname);

        $data->order           = [];
        $data->order['column'] = 0;
        $data->order['dir']    = 'asc';

        // Get sessions in main entity without search ASC (Entity name).
        $sessionsbyentity = $dbinterface->get_sessions_by_entity_id($data);

        self::assertCount(3, $sessionsbyentity);
        self::assertArrayHasKey($session1->id, $sessionsbyentity);
        self::assertArrayHasKey($session2->id, $sessionsbyentity);
        self::assertArrayHasKey($session3->id, $sessionsbyentity);
        $sessionsbyentitydata = array_values($sessionsbyentity);
        self::assertEquals($session1->id, $sessionsbyentitydata[0]->id);
        self::assertEquals($session1->shortname, $sessionsbyentitydata[0]->shortname);
        self::assertEquals($session3->id, $sessionsbyentitydata[1]->id);
        self::assertEquals($session3->shortname, $sessionsbyentitydata[1]->shortname);
        self::assertEquals($session2->id, $sessionsbyentitydata[2]->id);
        self::assertEquals($session2->shortname, $sessionsbyentitydata[2]->shortname);

        $data->order['dir'] = 'desc';

        // Get sessions in main entity without search DESC (Entity name).
        $sessionsbyentity = $dbinterface->get_sessions_by_entity_id($data);
        self::assertCount(3, $sessionsbyentity);
        self::assertArrayHasKey($session1->id, $sessionsbyentity);
        self::assertArrayHasKey($session2->id, $sessionsbyentity);
        self::assertArrayHasKey($session3->id, $sessionsbyentity);
        $sessionsbyentitydata = array_values($sessionsbyentity);
        self::assertEquals($session2->id, $sessionsbyentitydata[0]->id);
        self::assertEquals($session2->shortname, $sessionsbyentitydata[0]->shortname);
        self::assertEquals($session3->id, $sessionsbyentitydata[1]->id);
        self::assertEquals($session3->shortname, $sessionsbyentitydata[1]->shortname);
        self::assertEquals($session1->id, $sessionsbyentitydata[2]->id);
        self::assertEquals($session1->shortname, $sessionsbyentitydata[2]->shortname);

        $data->order           = [];
        $data->order['column'] = 2;
        $data->order['dir']    = 'asc';

        // Get sessions in main entity without search ASC (Session name).
        $sessionsbyentity = $dbinterface->get_sessions_by_entity_id($data);
        self::assertCount(3, $sessionsbyentity);
        self::assertArrayHasKey($session1->id, $sessionsbyentity);
        self::assertArrayHasKey($session2->id, $sessionsbyentity);
        self::assertArrayHasKey($session3->id, $sessionsbyentity);
        $sessionsbyentitydata = array_values($sessionsbyentity);
        self::assertEquals($session1->id, $sessionsbyentitydata[0]->id);
        self::assertEquals($session1->shortname, $sessionsbyentitydata[0]->shortname);
        self::assertEquals($session2->id, $sessionsbyentitydata[1]->id);
        self::assertEquals($session2->shortname, $sessionsbyentitydata[1]->shortname);
        self::assertEquals($session3->id, $sessionsbyentitydata[2]->id);
        self::assertEquals($session3->shortname, $sessionsbyentitydata[2]->shortname);

        $data->order['dir'] = 'desc';

        // Get sessions in main entity without search DESC (Session name).
        $sessionsbyentity = $dbinterface->get_sessions_by_entity_id($data);
        self::assertCount(3, $sessionsbyentity);
        self::assertArrayHasKey($session1->id, $sessionsbyentity);
        self::assertArrayHasKey($session2->id, $sessionsbyentity);
        self::assertArrayHasKey($session3->id, $sessionsbyentity);
        $sessionsbyentitydata = array_values($sessionsbyentity);
        self::assertEquals($session3->id, $sessionsbyentitydata[0]->id);
        self::assertEquals($session3->shortname, $sessionsbyentitydata[0]->shortname);
        self::assertEquals($session2->id, $sessionsbyentitydata[1]->id);
        self::assertEquals($session2->shortname, $sessionsbyentitydata[1]->shortname);
        self::assertEquals($session1->id, $sessionsbyentitydata[2]->id);
        self::assertEquals($session1->shortname, $sessionsbyentitydata[2]->shortname);

        $data->order           = [];
        $data->order['column'] = 3;
        $data->order['dir']    = 'asc';

        // Get sessions in main entity without search ASC (Session shortname).
        $sessionsbyentity = $dbinterface->get_sessions_by_entity_id($data);
        self::assertCount(3, $sessionsbyentity);
        self::assertArrayHasKey($session1->id, $sessionsbyentity);
        self::assertArrayHasKey($session2->id, $sessionsbyentity);
        self::assertArrayHasKey($session3->id, $sessionsbyentity);
        $sessionsbyentitydata = array_values($sessionsbyentity);
        self::assertEquals($session1->id, $sessionsbyentitydata[0]->id);
        self::assertEquals($session1->shortname, $sessionsbyentitydata[0]->shortname);
        self::assertEquals($session2->id, $sessionsbyentitydata[1]->id);
        self::assertEquals($session2->shortname, $sessionsbyentitydata[1]->shortname);
        self::assertEquals($session3->id, $sessionsbyentitydata[2]->id);
        self::assertEquals($session3->shortname, $sessionsbyentitydata[2]->shortname);

        $data->order['dir'] = 'desc';

        // Get sessions in main entity without search DESC (Session shortname).
        $sessionsbyentity = $dbinterface->get_sessions_by_entity_id($data);
        self::assertCount(3, $sessionsbyentity);
        self::assertArrayHasKey($session1->id, $sessionsbyentity);
        self::assertArrayHasKey($session2->id, $sessionsbyentity);
        self::assertArrayHasKey($session3->id, $sessionsbyentity);
        $sessionsbyentitydata = array_values($sessionsbyentity);
        self::assertEquals($session3->id, $sessionsbyentitydata[0]->id);
        self::assertEquals($session3->shortname, $sessionsbyentitydata[0]->shortname);
        self::assertEquals($session2->id, $sessionsbyentitydata[1]->id);
        self::assertEquals($session2->shortname, $sessionsbyentitydata[1]->shortname);
        self::assertEquals($session1->id, $sessionsbyentitydata[2]->id);
        self::assertEquals($session1->shortname, $sessionsbyentitydata[2]->shortname);

        $data->order           = [];
        $data->order['column'] = 4;
        $data->order['dir']    = 'asc';

        // Get sessions in main entity without search ASC (Session number).
        $sessionsbyentity = $dbinterface->get_sessions_by_entity_id($data);
        self::assertCount(3, $sessionsbyentity);
        self::assertArrayHasKey($session1->id, $sessionsbyentity);
        self::assertArrayHasKey($session2->id, $sessionsbyentity);
        self::assertArrayHasKey($session3->id, $sessionsbyentity);
        $sessionsbyentitydata = array_values($sessionsbyentity);
        self::assertEquals($session2->id, $sessionsbyentitydata[0]->id);
        self::assertEquals($session2->shortname, $sessionsbyentitydata[0]->shortname);
        self::assertEquals($session3->id, $sessionsbyentitydata[1]->id);
        self::assertEquals($session3->shortname, $sessionsbyentitydata[1]->shortname);
        self::assertEquals($session1->id, $sessionsbyentitydata[2]->id);
        self::assertEquals($session1->shortname, $sessionsbyentitydata[2]->shortname);

        $data->order['dir'] = 'desc';

        // Get sessions in main entity without search DESC (Session number).
        $sessionsbyentity = $dbinterface->get_sessions_by_entity_id($data);
        self::assertCount(3, $sessionsbyentity);
        self::assertArrayHasKey($session1->id, $sessionsbyentity);
        self::assertArrayHasKey($session2->id, $sessionsbyentity);
        self::assertArrayHasKey($session3->id, $sessionsbyentity);
        $sessionsbyentitydata = array_values($sessionsbyentity);
        self::assertEquals($session1->id, $sessionsbyentitydata[0]->id);
        self::assertEquals($session1->shortname, $sessionsbyentitydata[0]->shortname);
        self::assertEquals($session2->id, $sessionsbyentitydata[1]->id);
        self::assertEquals($session2->shortname, $sessionsbyentitydata[1]->shortname);
        self::assertEquals($session3->id, $sessionsbyentitydata[2]->id);
        self::assertEquals($session3->shortname, $sessionsbyentitydata[2]->shortname);

        $data->order           = [];
        $data->order['column'] = 5;
        $data->order['dir']    = 'asc';

        // Get sessions in main entity without search ASC (Session start date).
        $sessionsbyentity = $dbinterface->get_sessions_by_entity_id($data);
        self::assertCount(3, $sessionsbyentity);
        self::assertArrayHasKey($session1->id, $sessionsbyentity);
        self::assertArrayHasKey($session2->id, $sessionsbyentity);
        self::assertArrayHasKey($session3->id, $sessionsbyentity);
        $sessionsbyentitydata = array_values($sessionsbyentity);
        self::assertEquals($session2->id, $sessionsbyentitydata[0]->id);
        self::assertEquals($session2->shortname, $sessionsbyentitydata[0]->shortname);
        self::assertEquals($session1->id, $sessionsbyentitydata[1]->id);
        self::assertEquals($session1->shortname, $sessionsbyentitydata[1]->shortname);
        self::assertEquals($session3->id, $sessionsbyentitydata[2]->id);
        self::assertEquals($session3->shortname, $sessionsbyentitydata[2]->shortname);

        $data->order['dir'] = 'desc';

        // Get sessions in main entity without search DESC (Session start date).
        $sessionsbyentity = $dbinterface->get_sessions_by_entity_id($data);
        self::assertCount(3, $sessionsbyentity);
        self::assertArrayHasKey($session1->id, $sessionsbyentity);
        self::assertArrayHasKey($session2->id, $sessionsbyentity);
        self::assertArrayHasKey($session3->id, $sessionsbyentity);
        $sessionsbyentitydata = array_values($sessionsbyentity);
        self::assertEquals($session3->id, $sessionsbyentitydata[0]->id);
        self::assertEquals($session3->shortname, $sessionsbyentitydata[0]->shortname);
        self::assertEquals($session1->id, $sessionsbyentitydata[1]->id);
        self::assertEquals($session1->shortname, $sessionsbyentitydata[1]->shortname);
        self::assertEquals($session2->id, $sessionsbyentitydata[2]->id);
        self::assertEquals($session2->shortname, $sessionsbyentitydata[2]->shortname);

        self::resetAllData();
    }

    /**
     * Test count_sessions_by_entity_id
     *
     * @covers \local_mentor_specialization\database_interface::count_sessions_by_entity_id
     */
    public function test_count_sessions_by_entity_id_ok() {
        global $DB;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();

        // Remove the miscelleanous category.
        $DB->delete_records('course_categories', array('id' => 1));

        self::setAdminUser();

        // Create training in main entity.
        $trainingdata1         = $this->init_session_data(true);
        $trainingdata1->status = \local_mentor_core\training::STATUS_DRAFT;
        $entityid1             = \local_mentor_core\entity_api::create_entity([
            'name'      => 'New Entity 1',
            'shortname' => 'New Entity 1'
        ]);
        $entity1               = \local_mentor_core\entity_api::get_entity($entityid1);
        $trainingdata1         = $this->init_training_entity($trainingdata1, $entity1);
        $training1             = \local_mentor_core\training_api::create_training($trainingdata1);

        // Create training in sub entity.
        $trainingdata2             = $this->init_session_data(true);
        $trainingdata2->name       = 'fullname2';
        $trainingdata2->shortname  = 'shortname2';
        $trainingdata2->collection = 'preparation';
        $trainingdata2->status     = \local_mentor_core\training::STATUS_TEMPLATE;
        $entityid2                 = \local_mentor_core\entity_api::create_entity([
            'name'     => 'New Entity 2',
            'parentid' => $entity1->id
        ]);
        $entity2                   = \local_mentor_core\entity_api::get_entity($entityid2);
        $trainingdata2             = $this->init_training_entity($trainingdata2, $entity2);
        $training2                 = \local_mentor_core\training_api::create_training($trainingdata2);

        // Create training in sub entity.
        $trainingdata3             = $this->init_session_data(true);
        $trainingdata3->name       = 'fullname3';
        $trainingdata3->shortname  = 'shortname3';
        $trainingdata3->collection = 'accompagnement, transformation';
        $trainingdata3->status     = \local_mentor_core\training::STATUS_ELABORATION_COMPLETED;
        $entityid3                 = \local_mentor_core\entity_api::create_entity([
            'name'     => 'New Entity 3',
            'parentid' => $entity1->id
        ]);
        $entity3                   = \local_mentor_core\entity_api::get_entity($entityid3);
        $trainingdata3             = $this->init_training_entity($trainingdata3, $entity3);
        $training3                 = \local_mentor_core\training_api::create_training($trainingdata3);

        // Session in main entity.
        $session1               = \local_mentor_core\session_api::create_session($training1->id, 'Sessionname1', true);
        $data                   = new \stdClass();
        $data->status           = \local_mentor_core\session::STATUS_OPENED_REGISTRATION;
        $data->sessionstartdate = 5;
        $session1->update($data);

        // Sessions in sub-entity.
        $session2               = \local_mentor_core\session_api::create_session($training2->id, 'SessionnameBis2', true);
        $data                   = new \stdClass();
        $data->status           = \local_mentor_core\session::STATUS_OPENED_REGISTRATION;
        $data->sessionstartdate = 10;
        $session2->update($data);

        $session3 = \local_mentor_core\session_api::create_session($training3->id, 'SessionnameBis3', true);
        $session3->update_status(\local_mentor_core\session::STATUS_COMPLETED);
        $data                   = new \stdClass();
        $data->status           = \local_mentor_core\session::STATUS_COMPLETED;
        $data->sessionstartdate = 15;
        $session3->update($data);

        $data                  = new \stdClass();
        $data->entityid        = $entity1->id;
        $data->filters         = [];
        $data->search          = [];
        $data->search['value'] = '';

        // Count sessions in main entity without filter.
        self::assertEquals(3, $dbinterface->count_sessions_by_entity_id($data));

        // Count sessions in main entity with status filter.
        $data->filters['status'] = [\local_mentor_core\session::STATUS_COMPLETED];
        self::assertEquals(1, $dbinterface->count_sessions_by_entity_id($data));
        $data->filters['status'] = [\local_mentor_core\session::STATUS_OPENED_REGISTRATION];
        self::assertEquals(2, $dbinterface->count_sessions_by_entity_id($data));

        unset($data->filters['status']);

        // Count sessions in main entity with date to filter.
        $data->filters['startdate'] = 1;
        self::assertEquals(3, $dbinterface->count_sessions_by_entity_id($data));
        $data->filters['startdate'] = 20;
        self::assertEquals(0, $dbinterface->count_sessions_by_entity_id($data));

        unset($data->filters['startdate']);

        // Count sessions in main entity with date from filter.
        $data->filters['enddate'] = 1;
        self::assertEquals(0, $dbinterface->count_sessions_by_entity_id($data));
        $data->filters['enddate'] = 20;
        self::assertEquals(3, $dbinterface->count_sessions_by_entity_id($data));

        unset($data->filters['enddate']);

        // Count sessions in main entity with search filter.
        $data->search['value'] = 'New Entity 1';// Entity name.
        self::assertEquals(1, $dbinterface->count_sessions_by_entity_id($data));
        $data->search['value'] = 'Sessionname';// Session name.
        self::assertEquals(3, $dbinterface->count_sessions_by_entity_id($data));
        $data->search['value'] = 'SessionnameBis';// Session name.
        self::assertEquals(2, $dbinterface->count_sessions_by_entity_id($data));
        $data->search['value'] = get_string(\local_mentor_core\session::STATUS_OPENED_REGISTRATION,
            'local_mentor_specialization');// Session Status.
        self::assertEquals(2, $dbinterface->count_sessions_by_entity_id($data));

        self::resetAllData();
    }

    /**
     * Test get_trainings_by_entity_id
     *
     * @covers \local_mentor_specialization\database_interface::get_trainings_by_entity_id
     */
    public function test_get_trainings_by_entity_id_ok() {
        global $DB;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();

        // Remove the miscelleanous category.
        $DB->delete_records('course_categories', array('id' => 1));

        self::setAdminUser();

        // Create training in main entity.
        $trainingdata1 = $this->init_session_data(true);
        $entityid1     = \local_mentor_core\entity_api::create_entity(['name' => 'New Entity 1', 'shortname' => 'New Entity 1']);
        $entity1       = \local_mentor_core\entity_api::get_entity($entityid1);
        $trainingdata1 = $this->init_training_entity($trainingdata1, $entity1);
        $training1     = \local_mentor_core\training_api::create_training($trainingdata1);

        // Test training in main entity.
        $trainingsbyentity1 = $dbinterface->get_trainings_by_entity_id($entity1->id, true);
        self::assertCount(1, $trainingsbyentity1);
        self::assertArrayHasKey($training1->id, $trainingsbyentity1);
        self::assertEquals($training1->id, $trainingsbyentity1[$training1->id]->id);
        self::assertEquals($training1->shortname, $trainingsbyentity1[$training1->id]->courseshortname);

        // Create training in other main entity.
        $trainingdata2            = $this->init_session_data(true);
        $trainingdata2->name      = 'fullname2';
        $trainingdata2->shortname = 'shortname2';
        $entityid2                = \local_mentor_core\entity_api::create_entity([
            'name'      => 'New Entity 2',
            'shortname' => 'New Entity 2'
        ]);
        $entity2                  = \local_mentor_core\entity_api::get_entity($entityid2);
        $trainingdata2            = $this->init_training_entity($trainingdata2, $entity2);
        $training2                = \local_mentor_core\training_api::create_training($trainingdata2);

        // Test training in other main entity.
        $trainingsbyentity2 = $dbinterface->get_trainings_by_entity_id($entity2->id, true);
        self::assertCount(1, $trainingsbyentity2);
        self::assertArrayHasKey($training2->id, $trainingsbyentity2);
        self::assertEquals($training2->id, $trainingsbyentity2[$training2->id]->id);
        self::assertEquals($training2->shortname, $trainingsbyentity2[$training2->id]->courseshortname);

        // Create training in sub entity.
        $trainingdata3            = $this->init_session_data(true);
        $trainingdata3->name      = 'fullname3';
        $trainingdata3->shortname = 'shortname3';
        $entityid3                = \local_mentor_core\entity_api::create_entity([
            'name'     => 'New Entity 3',
            'parentid' => $entity1->id
        ]);
        $entity3                  = \local_mentor_core\entity_api::get_entity($entityid3);
        $trainingdata3            = $this->init_training_entity($trainingdata3, $entity3);
        $training3                = \local_mentor_core\training_api::create_training($trainingdata3);

        // Test training in main entity with sub entity.
        $trainingsbyentity3 = $dbinterface->get_trainings_by_entity_id($entity1->id, false);
        self::assertCount(2, $trainingsbyentity3);
        self::assertArrayHasKey($training1->id, $trainingsbyentity3);
        self::assertEquals($training1->id, $trainingsbyentity3[$training1->id]->id);
        self::assertEquals($training1->shortname, $trainingsbyentity3[$training1->id]->courseshortname);
        self::assertArrayHasKey($training3->id, $trainingsbyentity3);
        self::assertEquals($training3->id, $trainingsbyentity3[$training3->id]->id);
        self::assertEquals($training3->shortname, $trainingsbyentity3[$training3->id]->courseshortname);

        self::resetAllData();
    }

    /**
     * Test count_trainings_by_entity_id
     *
     * @covers \local_mentor_specialization\database_interface::count_trainings_by_entity_id
     */
    public function test_count_trainings_by_entity_id_ok() {
        global $DB;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();

        // Remove the miscelleanous category.
        $DB->delete_records('course_categories', array('id' => 1));

        self::setAdminUser();

        // Create training in main entity.
        $trainingdata1 = $this->init_session_data(true);
        $entityid1     = \local_mentor_core\entity_api::create_entity(['name' => 'New Entity 1', 'shortname' => 'New Entity 1']);
        $entity1       = \local_mentor_core\entity_api::get_entity($entityid1);
        $trainingdata1 = $this->init_training_entity($trainingdata1, $entity1);
        $training1     = \local_mentor_core\training_api::create_training($trainingdata1);

        // Test training in main entity.
        self::assertEquals(1, $dbinterface->count_trainings_by_entity_id($entity1->id, true));

        // Create training in other main entity.
        $trainingdata2            = $this->init_session_data(true);
        $trainingdata2->name      = 'fullname2';
        $trainingdata2->shortname = 'shortname2';
        $entityid2                = \local_mentor_core\entity_api::create_entity([
            'name'      => 'New Entity 2',
            'shortname' => 'New Entity 2'
        ]);
        $entity2                  = \local_mentor_core\entity_api::get_entity($entityid2);
        $trainingdata2            = $this->init_training_entity($trainingdata2, $entity2);
        $training2                = \local_mentor_core\training_api::create_training($trainingdata2);

        // Test training in other main entity.
        self::assertEquals(1, $dbinterface->count_trainings_by_entity_id($entity2->id, true));

        // Create training in sub entity.
        $trainingdata3            = $this->init_session_data(true);
        $trainingdata3->name      = 'fullname3';
        $trainingdata3->shortname = 'shortname3';
        $entityid3                = \local_mentor_core\entity_api::create_entity([
            'name'     => 'New Entity 3',
            'parentid' => $entity1->id
        ]);
        $entity3                  = \local_mentor_core\entity_api::get_entity($entityid3);
        $trainingdata3            = $this->init_training_entity($trainingdata3, $entity3);
        $training3                = \local_mentor_core\training_api::create_training($trainingdata3);

        // Test training in main entity with sub entity.
        self::assertEquals(2, $dbinterface->count_trainings_by_entity_id($entity1->id, false));

        self::resetAllData();
    }

    /**
     * Test get_trainings_by_entity_id with filter
     *
     * @covers \local_mentor_specialization\database_interface::get_trainings_by_entity_id
     * @covers \local_mentor_specialization\database_interface::generate_trainings_by_entity_id_filter
     */
    public function test_get_trainings_by_entity_id_with_filter_ok() {
        global $DB;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();

        // Remove the miscelleanous category.
        $DB->delete_records('course_categories', array('id' => 1));

        self::setAdminUser();

        // Create training in main entity.
        $trainingdata1         = $this->init_session_data(true);
        $trainingdata1->status = \local_mentor_core\training::STATUS_DRAFT;
        $entityid1             = \local_mentor_core\entity_api::create_entity([
            'name'      => 'New Entity 1',
            'shortname' => 'New Entity 1'
        ]);
        $entity1               = \local_mentor_core\entity_api::get_entity($entityid1);
        $trainingdata1         = $this->init_training_entity($trainingdata1, $entity1);
        $training1             = \local_mentor_core\training_api::create_training($trainingdata1);

        // Create training in sub entity.
        $trainingdata2             = $this->init_session_data(true);
        $trainingdata2->name       = 'fullname2';
        $trainingdata2->shortname  = 'shortname2';
        $trainingdata2->collection = 'preparation';
        $trainingdata2->status     = \local_mentor_core\training::STATUS_TEMPLATE;
        $entityid2                 = \local_mentor_core\entity_api::create_entity([
            'name'     => 'New Entity 2',
            'parentid' => $entity1->id
        ]);
        $entity2                   = \local_mentor_core\entity_api::get_entity($entityid2);
        $trainingdata2             = $this->init_training_entity($trainingdata2, $entity2);
        $training2                 = \local_mentor_core\training_api::create_training($trainingdata2);

        // Create training in sub entity.
        $trainingdata3             = $this->init_session_data(true);
        $trainingdata3->name       = 'fullname3';
        $trainingdata3->shortname  = 'shortname3';
        $trainingdata3->collection = 'accompagnement, transformation';
        $trainingdata3->status     = \local_mentor_core\training::STATUS_ELABORATION_COMPLETED;
        $entityid3                 = \local_mentor_core\entity_api::create_entity([
            'name'     => 'New Entity 3',
            'parentid' => $entity1->id
        ]);
        $entity3                   = \local_mentor_core\entity_api::get_entity($entityid3);
        $trainingdata3             = $this->init_training_entity($trainingdata3, $entity3);
        $training3                 = \local_mentor_core\training_api::create_training($trainingdata3);

        // Test training in main entity without filter.
        $datarequest                  = new stdClass();
        $datarequest->entityid        = $entity1->id;
        $datarequest->search          = [];
        $datarequest->search['value'] = '';
        $datarequest->order           = false;
        $datarequest->start           = 0;
        $datarequest->length          = 10;

        $trainingsbyentity = $dbinterface->get_trainings_by_entity_id($datarequest, false);
        self::assertCount(3, $trainingsbyentity);
        self::assertArrayHasKey($training1->id, $trainingsbyentity);
        self::assertEquals($training1->id, $trainingsbyentity[$training1->id]->id);
        self::assertEquals($training1->shortname, $trainingsbyentity[$training1->id]->courseshortname);
        self::assertArrayHasKey($training2->id, $trainingsbyentity);
        self::assertEquals($training2->id, $trainingsbyentity[$training2->id]->id);
        self::assertEquals($training2->shortname, $trainingsbyentity[$training2->id]->courseshortname);
        self::assertArrayHasKey($training3->id, $trainingsbyentity);
        self::assertEquals($training3->id, $trainingsbyentity[$training3->id]->id);
        self::assertEquals($training3->shortname, $trainingsbyentity[$training3->id]->courseshortname);

        // Test training in main entity with filter (one sub entity).
        $datarequest                       = new stdClass();
        $datarequest->entityid             = $entity1->id;
        $datarequest->filters              = [];
        $datarequest->filters['subentity'] = [$entity2->id];
        $datarequest->search               = [];
        $datarequest->search['value']      = '';
        $datarequest->order                = false;
        $datarequest->start                = 0;
        $datarequest->length               = 10;

        $trainingsbyentity = $dbinterface->get_trainings_by_entity_id($datarequest, false);
        self::assertCount(1, $trainingsbyentity);
        self::assertArrayHasKey($training2->id, $trainingsbyentity);
        self::assertEquals($training2->id, $trainingsbyentity[$training2->id]->id);
        self::assertEquals($training2->shortname, $trainingsbyentity[$training2->id]->courseshortname);

        // Test training in main entity with filter (two sub entity).
        $datarequest                       = new stdClass();
        $datarequest->entityid             = $entity1->id;
        $datarequest->filters              = [];
        $datarequest->filters['subentity'] = [$entity2->id, $entity3->id];
        $datarequest->search               = [];
        $datarequest->search['value']      = '';
        $datarequest->order                = false;
        $datarequest->start                = 0;
        $datarequest->length               = 10;

        $trainingsbyentity = $dbinterface->get_trainings_by_entity_id($datarequest, false);
        self::assertCount(2, $trainingsbyentity);
        self::assertArrayHasKey($training2->id, $trainingsbyentity);
        self::assertEquals($training2->id, $trainingsbyentity[$training2->id]->id);
        self::assertEquals($training2->shortname, $trainingsbyentity[$training2->id]->courseshortname);
        self::assertArrayHasKey($training3->id, $trainingsbyentity);
        self::assertEquals($training3->id, $trainingsbyentity[$training3->id]->id);
        self::assertEquals($training3->shortname, $trainingsbyentity[$training3->id]->courseshortname);

        // Test training in main entity with filter (one collection).
        $datarequest                        = new stdClass();
        $datarequest->entityid              = $entity1->id;
        $datarequest->filters               = [];
        $datarequest->filters['collection'] = ['accompagnement'];
        $datarequest->search                = [];
        $datarequest->search['value']       = '';
        $datarequest->order                 = false;
        $datarequest->start                 = 0;
        $datarequest->length                = 10;

        $trainingsbyentity = $dbinterface->get_trainings_by_entity_id($datarequest, false);
        self::assertCount(2, $trainingsbyentity);
        self::assertArrayHasKey($training1->id, $trainingsbyentity);
        self::assertEquals($training1->id, $trainingsbyentity[$training1->id]->id);
        self::assertEquals($training1->shortname, $trainingsbyentity[$training1->id]->courseshortname);
        self::assertArrayHasKey($training3->id, $trainingsbyentity);
        self::assertEquals($training3->id, $trainingsbyentity[$training3->id]->id);
        self::assertEquals($training3->shortname, $trainingsbyentity[$training3->id]->courseshortname);

        // Test training in main entity with filter (two collection).
        $datarequest                        = new stdClass();
        $datarequest->entityid              = $entity1->id;
        $datarequest->filters               = [];
        $datarequest->filters['collection'] = ['preparation', 'transformation'];
        $datarequest->search                = [];
        $datarequest->search['value']       = '';
        $datarequest->order                 = false;
        $datarequest->start                 = 0;
        $datarequest->length                = 10;

        $trainingsbyentity = $dbinterface->get_trainings_by_entity_id($datarequest, false);
        self::assertCount(2, $trainingsbyentity);
        self::assertArrayHasKey($training2->id, $trainingsbyentity);
        self::assertEquals($training2->id, $trainingsbyentity[$training2->id]->id);
        self::assertEquals($training2->shortname, $trainingsbyentity[$training2->id]->courseshortname);
        self::assertArrayHasKey($training3->id, $trainingsbyentity);
        self::assertEquals($training3->id, $trainingsbyentity[$training3->id]->id);
        self::assertEquals($training3->shortname, $trainingsbyentity[$training3->id]->courseshortname);

        // Test training in main entity with filter (one status).
        $datarequest                    = new stdClass();
        $datarequest->entityid          = $entity1->id;
        $datarequest->filters           = [];
        $datarequest->filters['status'] = [\local_mentor_core\training::STATUS_DRAFT];
        $datarequest->search            = [];
        $datarequest->search['value']   = '';
        $datarequest->order             = false;
        $datarequest->start             = 0;
        $datarequest->length            = 10;

        $trainingsbyentity = $dbinterface->get_trainings_by_entity_id($datarequest, false);
        self::assertCount(1, $trainingsbyentity);
        self::assertArrayHasKey($training1->id, $trainingsbyentity);
        self::assertEquals($training1->id, $trainingsbyentity[$training1->id]->id);
        self::assertEquals($training1->shortname, $trainingsbyentity[$training1->id]->courseshortname);

        // Test training in main entity with filter (two status).
        $datarequest                    = new stdClass();
        $datarequest->entityid          = $entity1->id;
        $datarequest->filters           = [];
        $datarequest->filters['status'] = [
            \local_mentor_core\training::STATUS_TEMPLATE,
            \local_mentor_core\training::STATUS_ELABORATION_COMPLETED
        ];
        $datarequest->search            = [];
        $datarequest->search['value']   = '';
        $datarequest->order             = false;
        $datarequest->start             = 0;
        $datarequest->length            = 10;

        $trainingsbyentity = $dbinterface->get_trainings_by_entity_id($datarequest, false);
        self::assertCount(2, $trainingsbyentity);
        self::assertArrayHasKey($training2->id, $trainingsbyentity);
        self::assertEquals($training2->id, $trainingsbyentity[$training2->id]->id);
        self::assertEquals($training2->shortname, $trainingsbyentity[$training2->id]->courseshortname);
        self::assertArrayHasKey($training3->id, $trainingsbyentity);
        self::assertEquals($training3->id, $trainingsbyentity[$training3->id]->id);
        self::assertEquals($training3->shortname, $trainingsbyentity[$training3->id]->courseshortname);

        self::resetAllData();
    }

    /**
     * Test count_trainings_by_entity_id with filter
     *
     * @covers \local_mentor_specialization\database_interface::count_trainings_by_entity_id
     * @covers \local_mentor_core\database_interface::count_trainings_by_entity_id
     * @covers \local_mentor_specialization\database_interface::generate_trainings_by_entity_id_filter
     */
    public function test_count_trainings_by_entity_id_with_filter_ok() {
        global $DB;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();

        // Remove the miscelleanous category.
        $DB->delete_records('course_categories', array('id' => 1));

        self::setAdminUser();

        // Create training in main entity.
        $trainingdata1         = $this->init_session_data(true);
        $trainingdata1->status = \local_mentor_core\training::STATUS_DRAFT;
        $entityid1             = \local_mentor_core\entity_api::create_entity([
            'name'      => 'New Entity 1',
            'shortname' => 'New Entity 1'
        ]);
        $entity1               = \local_mentor_core\entity_api::get_entity($entityid1);
        $trainingdata1         = $this->init_training_entity($trainingdata1, $entity1);
        $training1             = \local_mentor_core\training_api::create_training($trainingdata1);

        // Create training in sub entity.
        $trainingdata2             = $this->init_session_data(true);
        $trainingdata2->name       = 'fullname2';
        $trainingdata2->shortname  = 'shortname2';
        $trainingdata2->collection = 'preparation';
        $trainingdata2->status     = \local_mentor_core\training::STATUS_TEMPLATE;
        $entityid2                 = \local_mentor_core\entity_api::create_entity([
            'name'     => 'New Entity 2',
            'parentid' => $entity1->id
        ]);
        $entity2                   = \local_mentor_core\entity_api::get_entity($entityid2);
        $trainingdata2             = $this->init_training_entity($trainingdata2, $entity2);
        $training2                 = \local_mentor_core\training_api::create_training($trainingdata2);

        // Create training in sub entity.
        $trainingdata3             = $this->init_session_data(true);
        $trainingdata3->name       = 'fullname3';
        $trainingdata3->shortname  = 'shortname3';
        $trainingdata3->collection = 'accompagnement, transformation';
        $trainingdata3->status     = \local_mentor_core\training::STATUS_ELABORATION_COMPLETED;
        $entityid3                 = \local_mentor_core\entity_api::create_entity([
            'name'     => 'New Entity 3',
            'parentid' => $entity1->id
        ]);
        $entity3                   = \local_mentor_core\entity_api::get_entity($entityid3);
        $trainingdata3             = $this->init_training_entity($trainingdata3, $entity3);
        $training3                 = \local_mentor_core\training_api::create_training($trainingdata3);

        // Test training in main entity without filter.
        $datarequest                  = new stdClass();
        $datarequest->entityid        = $entity1->id;
        $datarequest->search          = [];
        $datarequest->search['value'] = '';
        $datarequest->order           = false;
        $datarequest->start           = 0;
        $datarequest->length          = 10;

        self::assertEquals(1, $dbinterface->count_trainings_by_entity_id($datarequest, true));
        self::assertEquals(3, $dbinterface->count_trainings_by_entity_id($datarequest, false));

        // Test training in main entity with filter (one sub entity).
        $datarequest                       = new stdClass();
        $datarequest->entityid             = $entity1->id;
        $datarequest->filters              = [];
        $datarequest->filters['subentity'] = [$entity2->id];
        $datarequest->search               = [];
        $datarequest->search['value']      = '';
        $datarequest->order                = false;
        $datarequest->start                = 0;
        $datarequest->length               = 10;

        self::assertEquals(1, $dbinterface->count_trainings_by_entity_id($datarequest, false));

        // Test training in main entity with filter (two sub entity).
        $datarequest                       = new stdClass();
        $datarequest->entityid             = $entity1->id;
        $datarequest->filters              = [];
        $datarequest->filters['subentity'] = [$entity2->id, $entity3->id];
        $datarequest->search               = [];
        $datarequest->search['value']      = '';
        $datarequest->order                = false;
        $datarequest->start                = 0;
        $datarequest->length               = 10;

        self::assertEquals(2, $dbinterface->count_trainings_by_entity_id($datarequest, false));

        // Test training in main entity with filter (one collection).
        $datarequest                        = new stdClass();
        $datarequest->entityid              = $entity1->id;
        $datarequest->filters               = [];
        $datarequest->filters['collection'] = ['accompagnement'];
        $datarequest->search                = [];
        $datarequest->search['value']       = '';
        $datarequest->order                 = false;
        $datarequest->start                 = 0;
        $datarequest->length                = 10;

        self::assertEquals(2, $dbinterface->count_trainings_by_entity_id($datarequest, false));

        // Test training in main entity with filter (two collection).
        $datarequest                        = new stdClass();
        $datarequest->entityid              = $entity1->id;
        $datarequest->filters               = [];
        $datarequest->filters['collection'] = ['preparation', 'transformation'];
        $datarequest->search                = [];
        $datarequest->search['value']       = '';
        $datarequest->order                 = false;
        $datarequest->start                 = 0;
        $datarequest->length                = 10;

        self::assertEquals(2, $dbinterface->count_trainings_by_entity_id($datarequest, false));

        // Test training in main entity with filter (one status).
        $datarequest                    = new stdClass();
        $datarequest->entityid          = $entity1->id;
        $datarequest->filters           = [];
        $datarequest->filters['status'] = [\local_mentor_core\training::STATUS_DRAFT];
        $datarequest->search            = [];
        $datarequest->search['value']   = '';
        $datarequest->order             = false;
        $datarequest->start             = 0;
        $datarequest->length            = 10;

        self::assertEquals(1, $dbinterface->count_trainings_by_entity_id($datarequest, false));

        // Test training in main entity with filter (two status).
        $datarequest                    = new stdClass();
        $datarequest->entityid          = $entity1->id;
        $datarequest->filters           = [];
        $datarequest->filters['status'] = [
            \local_mentor_core\training::STATUS_TEMPLATE,
            \local_mentor_core\training::STATUS_ELABORATION_COMPLETED
        ];
        $datarequest->search            = [];
        $datarequest->search['value']   = '';
        $datarequest->order             = false;
        $datarequest->start             = 0;
        $datarequest->length            = 10;

        self::assertEquals(2, $dbinterface->count_trainings_by_entity_id($datarequest, false));

        self::resetAllData();
    }

    /**
     * Test get_trainings_by_entity_id with search
     *
     * @covers \local_mentor_specialization\database_interface::get_trainings_by_entity_id
     * @covers \local_mentor_specialization\database_interface::generate_trainings_by_entity_id_search
     */
    public function test_get_trainings_by_entity_id_with_search_ok() {
        global $DB;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();

        // Remove the miscelleanous category.
        $DB->delete_records('course_categories', array('id' => 1));

        self::setAdminUser();

        // Create training in main entity.
        $trainingdata1            = $this->init_session_data(true);
        $trainingdata1->status    = \local_mentor_core\training::STATUS_DRAFT;
        $trainingdata1->name      = 'fullname1';
        $trainingdata1->shortname = 'shortname1';
        $entityid1                = \local_mentor_core\entity_api::create_entity([
            'name'      => 'New Entity 1',
            'shortname' => 'New Entity 1'
        ]);
        $entity1                  = \local_mentor_core\entity_api::get_entity($entityid1);
        $trainingdata1            = $this->init_training_entity($trainingdata1, $entity1);
        $training1                = \local_mentor_core\training_api::create_training($trainingdata1);

        // Create training in other main entity.
        $trainingdata2             = $this->init_session_data(true);
        $trainingdata2->name       = 'fullname2';
        $trainingdata2->shortname  = 'shortnamebis2';
        $trainingdata2->collection = 'preparation';
        $trainingdata2->status     = \local_mentor_core\training::STATUS_TEMPLATE;
        $entityid2                 = \local_mentor_core\entity_api::create_entity([
            'name'     => 'New Entity 2',
            'parentid' => $entity1->id
        ]);
        $entity2                   = \local_mentor_core\entity_api::get_entity($entityid2);
        $trainingdata2             = $this->init_training_entity($trainingdata2, $entity2);
        $training2                 = \local_mentor_core\training_api::create_training($trainingdata2);

        // Create training in sub entity.
        $trainingdata3             = $this->init_session_data(true);
        $trainingdata3->name       = 'fullname3';
        $trainingdata3->shortname  = 'shortnamebis3';
        $trainingdata3->collection = 'accompagnement, transformation';
        $trainingdata3->status     = \local_mentor_core\training::STATUS_ELABORATION_COMPLETED;
        $entityid3                 = \local_mentor_core\entity_api::create_entity([
            'name'     => 'New Entity 3',
            'parentid' => $entity1->id
        ]);
        $entity3                   = \local_mentor_core\entity_api::get_entity($entityid3);
        $trainingdata3             = $this->init_training_entity($trainingdata3, $entity3);
        $training3                 = \local_mentor_core\training_api::create_training($trainingdata3);

        // Test training in main entity without search.
        $datarequest                  = new stdClass();
        $datarequest->entityid        = $entity1->id;
        $datarequest->search          = [];
        $datarequest->search['value'] = '';
        $datarequest->order           = false;
        $datarequest->start           = 0;
        $datarequest->length          = 10;

        $trainingsbyentity = $dbinterface->get_trainings_by_entity_id($datarequest, false);
        self::assertCount(3, $trainingsbyentity);
        self::assertArrayHasKey($training1->id, $trainingsbyentity);
        self::assertEquals($training1->id, $trainingsbyentity[$training1->id]->id);
        self::assertEquals($training1->shortname, $trainingsbyentity[$training1->id]->courseshortname);
        self::assertArrayHasKey($training2->id, $trainingsbyentity);
        self::assertEquals($training2->id, $trainingsbyentity[$training2->id]->id);
        self::assertEquals($training2->shortname, $trainingsbyentity[$training2->id]->courseshortname);
        self::assertArrayHasKey($training3->id, $trainingsbyentity);
        self::assertEquals($training3->id, $trainingsbyentity[$training3->id]->id);
        self::assertEquals($training3->shortname, $trainingsbyentity[$training3->id]->courseshortname);

        // Test training in main entity with search.
        $datarequest                  = new stdClass();
        $datarequest->entityid        = $entity1->id;
        $datarequest->search          = [];
        $datarequest->search['value'] = 'fullname1';
        $datarequest->order           = false;
        $datarequest->start           = 0;
        $datarequest->length          = 10;

        $trainingsbyentity = $dbinterface->get_trainings_by_entity_id($datarequest, false);
        self::assertCount(1, $trainingsbyentity);
        self::assertArrayHasKey($training1->id, $trainingsbyentity);
        self::assertEquals($training1->id, $trainingsbyentity[$training1->id]->id);
        self::assertEquals($training1->shortname, $trainingsbyentity[$training1->id]->courseshortname);

        // Test training in main entity with search.
        $datarequest                  = new stdClass();
        $datarequest->entityid        = $entity1->id;
        $datarequest->search          = [];
        $datarequest->search['value'] = 'shortnamebis';
        $datarequest->order           = false;
        $datarequest->start           = 0;
        $datarequest->length          = 10;

        $trainingsbyentity = $dbinterface->get_trainings_by_entity_id($datarequest, false);
        self::assertCount(2, $trainingsbyentity);
        self::assertArrayHasKey($training2->id, $trainingsbyentity);
        self::assertEquals($training2->id, $trainingsbyentity[$training2->id]->id);
        self::assertEquals($training2->shortname, $trainingsbyentity[$training2->id]->courseshortname);
        self::assertArrayHasKey($training3->id, $trainingsbyentity);
        self::assertEquals($training3->id, $trainingsbyentity[$training3->id]->id);
        self::assertEquals($training3->shortname, $trainingsbyentity[$training3->id]->courseshortname);

        self::resetAllData();
    }

    /**
     * Test count_trainings_by_entity_id with search
     *
     * @covers \local_mentor_specialization\database_interface::count_trainings_by_entity_id
     * @covers \local_mentor_specialization\database_interface::generate_trainings_by_entity_id_search
     */
    public function test_count_trainings_by_entity_id_with_search_ok() {
        global $DB;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();

        // Remove the miscelleanous category.
        $DB->delete_records('course_categories', array('id' => 1));

        self::setAdminUser();

        // Create training in main entity.
        $trainingdata1            = $this->init_session_data(true);
        $trainingdata1->status    = \local_mentor_core\training::STATUS_DRAFT;
        $trainingdata1->name      = 'fullname1';
        $trainingdata1->shortname = 'shortname1';
        $entityid1                = \local_mentor_core\entity_api::create_entity([
            'name'      => 'New Entity 1',
            'shortname' => 'New Entity 1'
        ]);
        $entity1                  = \local_mentor_core\entity_api::get_entity($entityid1);
        $trainingdata1            = $this->init_training_entity($trainingdata1, $entity1);
        $training1                = \local_mentor_core\training_api::create_training($trainingdata1);

        // Create training in other main entity.
        $trainingdata2             = $this->init_session_data(true);
        $trainingdata2->name       = 'fullname2';
        $trainingdata2->shortname  = 'shortnamebis2';
        $trainingdata2->collection = 'preparation';
        $trainingdata2->status     = \local_mentor_core\training::STATUS_TEMPLATE;
        $entityid2                 = \local_mentor_core\entity_api::create_entity([
            'name'     => 'New Entity 2',
            'parentid' => $entity1->id
        ]);
        $entity2                   = \local_mentor_core\entity_api::get_entity($entityid2);
        $trainingdata2             = $this->init_training_entity($trainingdata2, $entity2);
        $training2                 = \local_mentor_core\training_api::create_training($trainingdata2);

        // Create training in sub entity.
        $trainingdata3             = $this->init_session_data(true);
        $trainingdata3->name       = 'fullname3';
        $trainingdata3->shortname  = 'shortnamebis3';
        $trainingdata3->collection = 'accompagnement, transformation';
        $trainingdata3->status     = \local_mentor_core\training::STATUS_ELABORATION_COMPLETED;
        $entityid3                 = \local_mentor_core\entity_api::create_entity([
            'name'     => 'New Entity 3',
            'parentid' => $entity1->id
        ]);
        $entity3                   = \local_mentor_core\entity_api::get_entity($entityid3);
        $trainingdata3             = $this->init_training_entity($trainingdata3, $entity3);
        $training3                 = \local_mentor_core\training_api::create_training($trainingdata3);

        // Test training in main entity without search.
        $datarequest                  = new stdClass();
        $datarequest->entityid        = $entity1->id;
        $datarequest->search          = [];
        $datarequest->search['value'] = '';
        $datarequest->order           = false;
        $datarequest->start           = 0;
        $datarequest->length          = 10;

        self::assertEquals(3, $dbinterface->count_trainings_by_entity_id($datarequest, false));

        // Test training in main entity with search.
        $datarequest                  = new stdClass();
        $datarequest->entityid        = $entity1->id;
        $datarequest->search          = [];
        $datarequest->search['value'] = 'fullname1';
        $datarequest->order           = false;
        $datarequest->start           = 0;
        $datarequest->length          = 10;

        self::assertEquals(1, $dbinterface->count_trainings_by_entity_id($datarequest, false));

        // Test training in main entity with search.
        $datarequest                  = new stdClass();
        $datarequest->entityid        = $entity1->id;
        $datarequest->search          = [];
        $datarequest->search['value'] = 'shortnamebis';
        $datarequest->order           = false;
        $datarequest->start           = 0;
        $datarequest->length          = 10;

        self::assertEquals(2, $dbinterface->count_trainings_by_entity_id($datarequest, false));

        self::resetAllData();
    }

    /**
     * Test get_trainings_by_entity_id with order
     *
     * @covers \local_mentor_specialization\database_interface::get_trainings_by_entity_id
     */
    public function test_get_trainings_by_entity_id_with_order_ok() {
        global $DB;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();

        // Remove the miscelleanous category.
        $DB->delete_records('course_categories', array('id' => 1));

        self::setAdminUser();

        // Create training in main entity.
        $trainingdata1             = $this->init_session_data(true);
        $trainingdata1->status     = \local_mentor_core\training::STATUS_DRAFT;
        $trainingdata1->name       = 'fullname1';
        $trainingdata1->shortname  = 'shortname1';
        $trainingdata1->collection = 'transformation';
        $trainingdata1->idsirh     = 'a';
        $entityid1                 = \local_mentor_core\entity_api::create_entity([
            'name'      => 'New Entity 1',
            'shortname' => 'New Entity 1'
        ]);
        $entity1                   = \local_mentor_core\entity_api::get_entity($entityid1);
        $trainingdata1             = $this->init_training_entity($trainingdata1, $entity1);
        $training1                 = \local_mentor_core\training_api::create_training($trainingdata1);

        // Create training in other main entity.
        $trainingdata2             = $this->init_session_data(true);
        $trainingdata2->name       = 'fullname2';
        $trainingdata2->shortname  = 'shortnamebis2';
        $trainingdata2->collection = 'accompagnement';
        $trainingdata2->idsirh     = 'c';
        $trainingdata2->status     = \local_mentor_core\training::STATUS_TEMPLATE;
        $entityid2                 = \local_mentor_core\entity_api::create_entity([
            'name'     => 'New Entity 2',
            'parentid' => $entity1->id
        ]);
        $entity2                   = \local_mentor_core\entity_api::get_entity($entityid2);
        $trainingdata2             = $this->init_training_entity($trainingdata2, $entity2);
        $training2                 = \local_mentor_core\training_api::create_training($trainingdata2);

        // Create training in sub entity.
        $trainingdata3             = $this->init_session_data(true);
        $trainingdata3->name       = 'fullname3';
        $trainingdata3->shortname  = 'shortnamebis3';
        $trainingdata3->collection = 'preparation';
        $trainingdata3->idsirh     = 'b';
        $trainingdata3->status     = \local_mentor_core\training::STATUS_ELABORATION_COMPLETED;
        $entityid3                 = \local_mentor_core\entity_api::create_entity([
            'name'     => 'New Entity 3',
            'parentid' => $entity1->id
        ]);
        $entity3                   = \local_mentor_core\entity_api::get_entity($entityid3);
        $trainingdata3             = $this->init_training_entity($trainingdata3, $entity3);
        $training3                 = \local_mentor_core\training_api::create_training($trainingdata3);

        // Test training in main entity without order.
        $datarequest                  = new stdClass();
        $datarequest->entityid        = $entity1->id;
        $datarequest->search          = [];
        $datarequest->search['value'] = '';
        $datarequest->order           = false;
        $datarequest->start           = 0;
        $datarequest->length          = 10;

        $trainingsbyentity = $dbinterface->get_trainings_by_entity_id($datarequest, false);
        self::assertCount(3, $trainingsbyentity);
        self::assertArrayHasKey($training1->id, $trainingsbyentity);
        self::assertEquals($training1->id, $trainingsbyentity[$training1->id]->id);
        self::assertEquals($training1->shortname, $trainingsbyentity[$training1->id]->courseshortname);
        self::assertArrayHasKey($training2->id, $trainingsbyentity);
        self::assertEquals($training2->id, $trainingsbyentity[$training2->id]->id);
        self::assertEquals($training2->shortname, $trainingsbyentity[$training2->id]->courseshortname);
        self::assertArrayHasKey($training3->id, $trainingsbyentity);
        self::assertEquals($training3->id, $trainingsbyentity[$training3->id]->id);
        self::assertEquals($training3->shortname, $trainingsbyentity[$training3->id]->courseshortname);

        // Test training in main entity with order ASC (sub-entity name).
        $datarequest                  = new stdClass();
        $datarequest->entityid        = $entity1->id;
        $datarequest->search          = [];
        $datarequest->search['value'] = '';
        $datarequest->order           = [];
        $datarequest->order['column'] = 0;
        $datarequest->order['dir']    = 'asc';
        $datarequest->start           = 0;
        $datarequest->length          = 10;

        $trainingsbyentity = $dbinterface->get_trainings_by_entity_id($datarequest, false);

        self::assertCount(3, $trainingsbyentity);
        self::assertArrayHasKey($training1->id, $trainingsbyentity);
        self::assertArrayHasKey($training2->id, $trainingsbyentity);
        self::assertArrayHasKey($training3->id, $trainingsbyentity);

        $trainingsbyentity = array_values($trainingsbyentity);
        self::assertEquals($training1->id, $trainingsbyentity[0]->id);
        self::assertEquals($training1->shortname, $trainingsbyentity[0]->courseshortname);
        self::assertEquals($training2->id, $trainingsbyentity[1]->id);
        self::assertEquals($training2->shortname, $trainingsbyentity[1]->courseshortname);
        self::assertEquals($training3->id, $trainingsbyentity[2]->id);
        self::assertEquals($training3->shortname, $trainingsbyentity[2]->courseshortname);

        // Test training in main entity with order DESC (sub-entity name).
        $datarequest                  = new stdClass();
        $datarequest->entityid        = $entity1->id;
        $datarequest->search          = [];
        $datarequest->search['value'] = '';
        $datarequest->order           = [];
        $datarequest->order['column'] = 0;
        $datarequest->order['dir']    = 'desc';
        $datarequest->start           = 0;
        $datarequest->length          = 10;

        $trainingsbyentity = $dbinterface->get_trainings_by_entity_id($datarequest, false);

        self::assertCount(3, $trainingsbyentity);
        self::assertArrayHasKey($training1->id, $trainingsbyentity);
        self::assertArrayHasKey($training2->id, $trainingsbyentity);
        self::assertArrayHasKey($training3->id, $trainingsbyentity);

        $trainingsbyentity = array_values($trainingsbyentity);
        self::assertEquals($training3->id, $trainingsbyentity[0]->id);
        self::assertEquals($training3->shortname, $trainingsbyentity[0]->courseshortname);
        self::assertEquals($training2->id, $trainingsbyentity[1]->id);
        self::assertEquals($training2->shortname, $trainingsbyentity[1]->courseshortname);
        self::assertEquals($training1->id, $trainingsbyentity[2]->id);
        self::assertEquals($training1->shortname, $trainingsbyentity[2]->courseshortname);

        // Test training in main entity with order ASC (collection name).
        $datarequest                  = new stdClass();
        $datarequest->entityid        = $entity1->id;
        $datarequest->search          = [];
        $datarequest->search['value'] = '';
        $datarequest->order           = [];
        $datarequest->order['column'] = 1;
        $datarequest->order['dir']    = 'asc';
        $datarequest->start           = 0;
        $datarequest->length          = 10;

        $trainingsbyentity = $dbinterface->get_trainings_by_entity_id($datarequest, false);

        self::assertCount(3, $trainingsbyentity);
        self::assertArrayHasKey($training1->id, $trainingsbyentity);
        self::assertArrayHasKey($training2->id, $trainingsbyentity);
        self::assertArrayHasKey($training3->id, $trainingsbyentity);

        $trainingsbyentity = array_values($trainingsbyentity);
        self::assertEquals($training2->id, $trainingsbyentity[0]->id);
        self::assertEquals($training2->shortname, $trainingsbyentity[0]->courseshortname);
        self::assertEquals($training3->id, $trainingsbyentity[1]->id);
        self::assertEquals($training3->shortname, $trainingsbyentity[1]->courseshortname);
        self::assertEquals($training1->id, $trainingsbyentity[2]->id);
        self::assertEquals($training1->shortname, $trainingsbyentity[2]->courseshortname);

        // Test training in main entity with order DESC (collection name).
        $datarequest                  = new stdClass();
        $datarequest->entityid        = $entity1->id;
        $datarequest->search          = [];
        $datarequest->search['value'] = '';
        $datarequest->order           = [];
        $datarequest->order['column'] = 1;
        $datarequest->order['dir']    = 'desc';
        $datarequest->start           = 0;
        $datarequest->length          = 10;

        $trainingsbyentity = $dbinterface->get_trainings_by_entity_id($datarequest, false);

        self::assertCount(3, $trainingsbyentity);
        self::assertArrayHasKey($training1->id, $trainingsbyentity);
        self::assertArrayHasKey($training2->id, $trainingsbyentity);
        self::assertArrayHasKey($training3->id, $trainingsbyentity);

        $trainingsbyentity = array_values($trainingsbyentity);
        self::assertEquals($training1->id, $trainingsbyentity[0]->id);
        self::assertEquals($training1->shortname, $trainingsbyentity[0]->courseshortname);
        self::assertEquals($training3->id, $trainingsbyentity[1]->id);
        self::assertEquals($training3->shortname, $trainingsbyentity[1]->courseshortname);
        self::assertEquals($training2->id, $trainingsbyentity[2]->id);
        self::assertEquals($training2->shortname, $trainingsbyentity[2]->courseshortname);

        // Test training in main entity with order ASC (shortname name).
        $datarequest                  = new stdClass();
        $datarequest->entityid        = $entity1->id;
        $datarequest->search          = [];
        $datarequest->search['value'] = '';
        $datarequest->order           = [];
        $datarequest->order['column'] = 2;
        $datarequest->order['dir']    = 'asc';
        $datarequest->start           = 0;
        $datarequest->length          = 10;

        $trainingsbyentity = $dbinterface->get_trainings_by_entity_id($datarequest, false);

        self::assertCount(3, $trainingsbyentity);
        self::assertArrayHasKey($training1->id, $trainingsbyentity);
        self::assertArrayHasKey($training2->id, $trainingsbyentity);
        self::assertArrayHasKey($training3->id, $trainingsbyentity);

        $trainingsbyentity = array_values($trainingsbyentity);
        self::assertEquals($training1->id, $trainingsbyentity[0]->id);
        self::assertEquals($training1->shortname, $trainingsbyentity[0]->courseshortname);
        self::assertEquals($training2->id, $trainingsbyentity[1]->id);
        self::assertEquals($training2->shortname, $trainingsbyentity[1]->courseshortname);
        self::assertEquals($training3->id, $trainingsbyentity[2]->id);
        self::assertEquals($training3->shortname, $trainingsbyentity[2]->courseshortname);

        // Test training in main entity with order DESC (shortname name).
        $datarequest                  = new stdClass();
        $datarequest->entityid        = $entity1->id;
        $datarequest->search          = [];
        $datarequest->search['value'] = '';
        $datarequest->order           = [];
        $datarequest->order['column'] = 2;
        $datarequest->order['dir']    = 'desc';
        $datarequest->start           = 0;
        $datarequest->length          = 10;

        $trainingsbyentity = $dbinterface->get_trainings_by_entity_id($datarequest, false);

        self::assertCount(3, $trainingsbyentity);
        self::assertArrayHasKey($training1->id, $trainingsbyentity);
        self::assertArrayHasKey($training2->id, $trainingsbyentity);
        self::assertArrayHasKey($training3->id, $trainingsbyentity);

        $trainingsbyentity = array_values($trainingsbyentity);
        self::assertEquals($training3->id, $trainingsbyentity[0]->id);
        self::assertEquals($training3->shortname, $trainingsbyentity[0]->courseshortname);
        self::assertEquals($training2->id, $trainingsbyentity[1]->id);
        self::assertEquals($training2->shortname, $trainingsbyentity[1]->courseshortname);
        self::assertEquals($training1->id, $trainingsbyentity[2]->id);
        self::assertEquals($training1->shortname, $trainingsbyentity[2]->courseshortname);

        // Test training in main entity with order ASC (Id SIRH name).
        $datarequest                  = new stdClass();
        $datarequest->entityid        = $entity1->id;
        $datarequest->search          = [];
        $datarequest->search['value'] = '';
        $datarequest->order           = [];
        $datarequest->order['column'] = 3;
        $datarequest->order['dir']    = 'asc';
        $datarequest->start           = 0;
        $datarequest->length          = 10;

        $trainingsbyentity = $dbinterface->get_trainings_by_entity_id($datarequest, false);

        self::assertCount(3, $trainingsbyentity);
        self::assertArrayHasKey($training1->id, $trainingsbyentity);
        self::assertArrayHasKey($training2->id, $trainingsbyentity);
        self::assertArrayHasKey($training3->id, $trainingsbyentity);

        $trainingsbyentity = array_values($trainingsbyentity);
        self::assertEquals($training1->id, $trainingsbyentity[0]->id);
        self::assertEquals($training1->shortname, $trainingsbyentity[0]->courseshortname);
        self::assertEquals($training3->id, $trainingsbyentity[1]->id);
        self::assertEquals($training3->shortname, $trainingsbyentity[1]->courseshortname);
        self::assertEquals($training2->id, $trainingsbyentity[2]->id);
        self::assertEquals($training2->shortname, $trainingsbyentity[2]->courseshortname);

        // Test training in main entity with order DESC (Id SIRH name).
        $datarequest                  = new stdClass();
        $datarequest->entityid        = $entity1->id;
        $datarequest->search          = [];
        $datarequest->search['value'] = '';
        $datarequest->order           = [];
        $datarequest->order['column'] = 3;
        $datarequest->order['dir']    = 'desc';
        $datarequest->start           = 0;
        $datarequest->length          = 10;

        $trainingsbyentity = $dbinterface->get_trainings_by_entity_id($datarequest, false);

        self::assertCount(3, $trainingsbyentity);
        self::assertArrayHasKey($training1->id, $trainingsbyentity);
        self::assertArrayHasKey($training2->id, $trainingsbyentity);
        self::assertArrayHasKey($training3->id, $trainingsbyentity);

        $trainingsbyentity = array_values($trainingsbyentity);
        self::assertEquals($training2->id, $trainingsbyentity[0]->id);
        self::assertEquals($training2->shortname, $trainingsbyentity[0]->courseshortname);
        self::assertEquals($training3->id, $trainingsbyentity[1]->id);
        self::assertEquals($training3->shortname, $trainingsbyentity[1]->courseshortname);
        self::assertEquals($training1->id, $trainingsbyentity[2]->id);
        self::assertEquals($training1->shortname, $trainingsbyentity[2]->courseshortname);

        self::resetAllData();
    }

    /**
     * Test update entity sirh list function with array
     *
     * @covers \local_mentor_specialization\database_interface::update_entity_sirh_list
     */
    public function test_update_entity_sirh_list_with_array_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();

        // Create entity.
        $entityid = \local_mentor_core\entity_api::create_entity(['name' => 'New Entity 1', 'shortname' => 'New Entity 1']);
        $entity   = \local_mentor_core\entity_api::get_entity($entityid);

        // With array and empty sirh data.
        $sirhlist = [
            'SIRH1',
            'SIRH2'
        ];

        self::assertTrue($dbinterface->update_entity_sirh_list($entityid, $sirhlist));

        $entitysirhlist = $entity->get_sirh_list();

        self::assertIsArray($entitysirhlist);
        self::assertEquals('SIRH1', $entitysirhlist[0]);
        self::assertEquals('SIRH2', $entitysirhlist[1]);

        // With array and existing sirh data.
        $sirhlist = [
            'SIRH3',
            'SIRH4',
            'SIRH5'
        ];

        self::assertTrue($dbinterface->update_entity_sirh_list($entityid, $sirhlist));

        $entitysirhlist = $entity->get_sirh_list(true);

        self::assertIsArray($entitysirhlist);
        self::assertEquals('SIRH3', $entitysirhlist[0]);
        self::assertEquals('SIRH4', $entitysirhlist[1]);
        self::assertEquals('SIRH5', $entitysirhlist[2]);

        self::resetAllData();
    }

    /**
     * Test update entity sirh list function with string
     *
     * @covers \local_mentor_specialization\database_interface::update_entity_sirh_list
     */
    public function test_update_entity_sirh_list_with_string_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();

        // Create entity.
        $entityid = \local_mentor_core\entity_api::create_entity(['name' => 'New Entity 1', 'shortname' => 'New Entity 1']);
        $entity   = \local_mentor_core\entity_api::get_entity($entityid);

        // With array and empty sirh data.
        $sirhlist = 'SIRH1,SIRH2';

        self::assertTrue($dbinterface->update_entity_sirh_list($entityid, $sirhlist));

        $entitysirhlist = $entity->get_sirh_list();

        self::assertIsArray($entitysirhlist);
        self::assertEquals('SIRH1,SIRH2', implode(',', $entitysirhlist));

        // With array and existing sirh data.
        $sirhlist = 'SIRH3,SIRH4,SIRH5';

        self::assertTrue($dbinterface->update_entity_sirh_list($entityid, $sirhlist));

        $entitysirhlist = $entity->get_sirh_list(true);

        self::assertIsArray($entitysirhlist);
        self::assertEquals('SIRH3,SIRH4,SIRH5', implode(',', $entitysirhlist));

        self::resetAllData();
    }

    /**
     * Test generate session sql search by collection function
     *
     * @covers \local_mentor_specialization\database_interface::generate_session_sql_search_by_collection
     */
    public function test_generate_session_sql_search_by_collection() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        set_config('collections', 'langues|Langues|rgba(225, 6, 0, 0.2)
achat|Achat public|rgba(166, 57, 80, 0.2)
finances|Finances publiques, gestion budgétaire et financière|rgba(22, 155, 98, 0.3)
numerique|Numérique et système d\'information et de communication|rgba(106, 106, 106, 0.28)',
            'local_mentor_specialization');

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();

        $sqlgenerate = $dbinterface->generate_session_sql_search_by_collection("langue");

        self::assertIsArray($sqlgenerate);
        self::assertCount(2, $sqlgenerate);
        self::assertEquals($sqlgenerate['0'], " OR t.collection ILIKE :collectionlangues ESCAPE '\'");
        self::assertEquals($sqlgenerate['1'], ['collectionlangues' => "%langues%"]);

        $sqlgenerate = $dbinterface->generate_session_sql_search_by_collection("publi");

        self::assertIsArray($sqlgenerate);
        self::assertCount(2, $sqlgenerate);
        self::assertEquals($sqlgenerate['0'],
            " OR t.collection ILIKE :collectionachat ESCAPE '\' OR t.collection ILIKE :collectionfinances ESCAPE '\'");
        self::assertEquals($sqlgenerate['1'], ['collectionachat' => "%achat%", 'collectionfinances' => "%finances%"]);

        $sqlgenerate = $dbinterface->generate_session_sql_search_by_collection("d\'information");

        self::assertIsArray($sqlgenerate);
        self::assertCount(2, $sqlgenerate);
        self::assertEquals($sqlgenerate['0'], " OR t.collection ILIKE :collectionnumerique ESCAPE '\'");
        self::assertEquals($sqlgenerate['1'], ['collectionnumerique' => "%numerique%"]);

        self::resetAllData();
    }

    /**
     * Test generate session sql search by status function
     *
     * @covers \local_mentor_specialization\database_interface::generate_session_sql_search_by_status
     */
    public function test_generate_session_sql_search_by_status() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();

        $sqlgenerate = $dbinterface->generate_session_sql_search_by_status("préparation");

        self::assertIsArray($sqlgenerate);
        self::assertCount(2, $sqlgenerate);
        self::assertEquals($sqlgenerate['0'], " OR s.status = :statusinpreparation");
        self::assertEquals($sqlgenerate['1'], ['statusinpreparation' => "inpreparation"]);

        $sqlgenerate = $dbinterface->generate_session_sql_search_by_status("ouvertes");

        self::assertIsArray($sqlgenerate);
        self::assertCount(2, $sqlgenerate);
        self::assertEquals($sqlgenerate['0'], " OR s.status = :statusopenedregistration");
        self::assertEquals($sqlgenerate['1'], ['statusopenedregistration' => "openedregistration"]);

        $sqlgenerate = $dbinterface->generate_session_sql_search_by_status("cours");

        self::assertIsArray($sqlgenerate);
        self::assertCount(2, $sqlgenerate);
        self::assertEquals($sqlgenerate['0'], " OR s.status = :statusinprogress");
        self::assertEquals($sqlgenerate['1'], ['statusinprogress' => "inprogress"]);

        $sqlgenerate = $dbinterface->generate_session_sql_search_by_status("terminee");

        self::assertIsArray($sqlgenerate);
        self::assertCount(2, $sqlgenerate);
        self::assertEquals($sqlgenerate['0'], " OR s.status = :statuscompleted");
        self::assertEquals($sqlgenerate['1'], ['statuscompleted' => "completed"]);

        $sqlgenerate = $dbinterface->generate_session_sql_search_by_status("Archivée");

        self::assertIsArray($sqlgenerate);
        self::assertCount(2, $sqlgenerate);
        self::assertEquals($sqlgenerate['0'], " OR s.status = :statusarchived");
        self::assertEquals($sqlgenerate['1'], ['statusarchived' => "archived"]);

        $sqlgenerate = $dbinterface->generate_session_sql_search_by_status("Reportée");

        self::assertIsArray($sqlgenerate);
        self::assertCount(2, $sqlgenerate);
        self::assertEquals($sqlgenerate['0'], " OR s.status = :statusreported");
        self::assertEquals($sqlgenerate['1'], ['statusreported' => "reported"]);

        $sqlgenerate = $dbinterface->generate_session_sql_search_by_status("Annulée");

        self::assertIsArray($sqlgenerate);
        self::assertCount(2, $sqlgenerate);
        self::assertEquals($sqlgenerate['0'], " OR s.status = :statuscancelled");
        self::assertEquals($sqlgenerate['1'], ['statuscancelled' => "cancelled"]);

        self::resetAllData();
    }

    /**
     * Test generate session sql search exact expression function
     *
     * @covers \local_mentor_specialization\database_interface::generate_session_sql_search_exact_expression
     */
    public function test_generate_session_sql_search_exact_expression() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();

        $sqlgenerate = $dbinterface->generate_session_sql_search_exact_expression("searchtext");

        self::assertIsArray($sqlgenerate);
        self::assertCount(2, $sqlgenerate);
        self::assertEquals($sqlgenerate['0'],
            "LOWER(t.courseshortname) = LOWER(:trainingname) OR " .
            "LOWER(s.courseshortname) = LOWER(:courseshortname) OR " .
            "LOWER(cc4.name) = LOWER(:entityname)");
        self::assertCount(3, $sqlgenerate['1']);
        self::assertArrayHasKey("trainingname", $sqlgenerate['1']);
        self::assertEquals($sqlgenerate['1']["trainingname"], "searchtext");
        self::assertArrayHasKey("courseshortname", $sqlgenerate['1']);
        self::assertEquals($sqlgenerate['1']["courseshortname"], "searchtext");
        self::assertArrayHasKey("entityname", $sqlgenerate[1]);
        self::assertEquals($sqlgenerate['1']["entityname"], "searchtext");

        self::resetAllData();
    }

    /**
     * Test get status search value trainings function
     *
     * @covers \local_mentor_specialization\database_interface::get_status_search_value_trainings
     */
    public function test_get_status_search_value_trainings() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        // Get list status and there string.
        $liststatus      = \local_mentor_core\training_api::get_status_list();
        $lisstatusstring = array_map(function($status) {
            return strtolower(get_string($status, 'local_mentor_specialization'));
        }, $liststatus);

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();

        $sqlgenerate = $dbinterface->get_status_search_value_trainings($lisstatusstring, "Brouillon");

        self::assertIsArray($sqlgenerate);
        self::assertCount(1, $sqlgenerate);
        self::assertArrayHasKey("draft", $sqlgenerate);
        self::assertEquals($sqlgenerate['draft'], "brouillon");

        $sqlgenerate = $dbinterface->get_status_search_value_trainings($lisstatusstring, "Gabarit");

        self::assertIsArray($sqlgenerate);
        self::assertCount(1, $sqlgenerate);
        self::assertArrayHasKey("template", $sqlgenerate);
        self::assertEquals($sqlgenerate['template'], "gabarit");

        $sqlgenerate = $dbinterface->get_status_search_value_trainings($lisstatusstring, "Elaboration");

        self::assertIsArray($sqlgenerate);
        self::assertCount(1, $sqlgenerate);
        self::assertArrayHasKey("elaboration_completed", $sqlgenerate);
        self::assertEquals($sqlgenerate['elaboration_completed'], "elaboration terminée");

        $sqlgenerate = $dbinterface->get_status_search_value_trainings($lisstatusstring, "Archivée");

        self::assertIsArray($sqlgenerate);
        self::assertCount(1, $sqlgenerate);
        self::assertArrayHasKey("archived", $sqlgenerate);
        self::assertEquals($sqlgenerate['archived'], "archivée");

        self::resetAllData();
    }

    /**
     * Test get collection search value trainings function
     *
     * @covers \local_mentor_specialization\database_interface::get_collection_search_value_trainings
     */
    public function test_get_collection_search_value_trainings() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        set_config('collections', 'langues|Langues|rgba(225, 6, 0, 0.2)
achat|Achat public|rgba(166, 57, 80, 0.2)
finances|Finances publiques, gestion budgétaire et financière|rgba(22, 155, 98, 0.3)
numerique|Numérique et système d\'information et de communication|rgba(106, 106, 106, 0.28)',
            'local_mentor_specialization');

        // Get list collection and there string.
        $listcollection = local_mentor_specialization_get_collections();

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();

        $sqlgenerate = $dbinterface->get_collection_search_value_trainings($listcollection, "langue");

        self::assertIsArray($sqlgenerate);
        self::assertCount(1, $sqlgenerate);
        self::assertArrayHasKey("langues", $sqlgenerate);
        self::assertEquals($sqlgenerate['langues'], "Langues");

        $sqlgenerate = $dbinterface->get_collection_search_value_trainings($listcollection, "Achat");

        self::assertIsArray($sqlgenerate);
        self::assertCount(1, $sqlgenerate);
        self::assertArrayHasKey("achat", $sqlgenerate);
        self::assertEquals($sqlgenerate['achat'], "Achat public");

        $sqlgenerate = $dbinterface->get_collection_search_value_trainings($listcollection, "système");

        self::assertIsArray($sqlgenerate);
        self::assertCount(1, $sqlgenerate);
        self::assertArrayHasKey("numerique", $sqlgenerate);
        self::assertEquals($sqlgenerate['numerique'], "Numérique et système d'information et de communication");

        $sqlgenerate = $dbinterface->get_collection_search_value_trainings($listcollection, "false");

        self::assertIsArray($sqlgenerate);
        self::assertCount(0, $sqlgenerate);

        self::resetAllData();
    }

    /**
     * Test generate training sql search exact expression function
     *
     * @covers \local_mentor_specialization\database_interface::generate_training_sql_search_exact_expression
     */
    public function test_generate_training_sql_search_exact_expression() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();

        $sqlgenerate = $dbinterface->generate_training_sql_search_exact_expression("searchtext");

        self::assertIsArray($sqlgenerate);
        self::assertCount(2, $sqlgenerate);
        self::assertEquals($sqlgenerate['0'],
            "LOWER(cc3.name) = LOWER(:subentityname) OR " .
            "LOWER(t.courseshortname) = LOWER(:trainingname) OR " .
            "LOWER(co.fullname) = LOWER(:trainingnameco) OR " .
            "LOWER(co2.fullname) = LOWER(:trainingnameco2) OR " .
            "LOWER(t.idsirh) = LOWER(:idsirh)");
        self::assertCount(5, $sqlgenerate['1']);
        self::assertArrayHasKey("subentityname", $sqlgenerate['1']);
        self::assertEquals($sqlgenerate['1']["subentityname"], "searchtext");
        self::assertArrayHasKey("trainingname", $sqlgenerate['1']);
        self::assertEquals($sqlgenerate['1']["trainingname"], "searchtext");
        self::assertArrayHasKey("trainingnameco", $sqlgenerate[1]);
        self::assertEquals($sqlgenerate['1']["trainingnameco"], "searchtext");
        self::assertArrayHasKey("trainingnameco2", $sqlgenerate[1]);
        self::assertEquals($sqlgenerate['1']["trainingnameco2"], "searchtext");
        self::assertArrayHasKey("idsirh", $sqlgenerate[1]);
        self::assertEquals($sqlgenerate['1']["idsirh"], "searchtext");

        self::resetAllData();
    }

    /**
     * Test generate training sql search by status function
     *
     * @covers \local_mentor_specialization\database_interface::generate_training_sql_search_by_status
     */
    public function test_generate_training_sql_search_by_status() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();

        // Get list status and there string.
        $liststatus      = \local_mentor_core\training_api::get_status_list();
        $lisstatusstring = array_map(function($status) {
            return strtolower(get_string($status, 'local_mentor_specialization'));
        }, $liststatus);
        $statusresult    = $dbinterface->get_status_search_value_trainings($lisstatusstring, "Brouillon");

        $sqlgenerate = $dbinterface->generate_training_sql_search_by_status($statusresult);

        self::assertIsArray($sqlgenerate);
        self::assertCount(2, $sqlgenerate);
        self::assertEquals($sqlgenerate[0], " OR t.status = :statussearch0");
        self::assertIsArray($sqlgenerate[1]);
        self::assertArrayHasKey("statussearch0", $sqlgenerate[1]);
        self::assertEquals($sqlgenerate[1]["statussearch0"], "draft");

        $sqlgenerate = $dbinterface->generate_training_sql_search_by_status("Brouillon", true);

        self::assertIsArray($sqlgenerate);
        self::assertCount(2, $sqlgenerate);
        self::assertEquals($sqlgenerate[0], " OR t.status = :statussearch");
        self::assertIsArray($sqlgenerate[1]);
        self::assertArrayHasKey("statussearch", $sqlgenerate[1]);
        self::assertEquals($sqlgenerate[1]["statussearch"], "Brouillon");

        self::resetAllData();
    }

    /**
     * Test generate training sql search by collection function
     *
     * @covers \local_mentor_specialization\database_interface::generate_training_sql_search_by_collection
     */
    public function test_generate_training_sql_search_by_collection() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();

        set_config('collections', 'langues|Langues|rgba(225, 6, 0, 0.2)',
            'local_mentor_specialization');

        // Get list collection and there string.
        $listcollection = local_mentor_specialization_get_collections();

        $collectionresult = $dbinterface->get_collection_search_value_trainings($listcollection, "langue");

        $sqlgenerate = $dbinterface->generate_training_sql_search_by_collection($collectionresult);

        self::assertIsArray($sqlgenerate);
        self::assertCount(2, $sqlgenerate);
        self::assertEquals($sqlgenerate[0], " OR t.collection ILIKE :collectionsearchlangues ESCAPE '\'");
        self::assertIsArray($sqlgenerate[1]);
        self::assertArrayHasKey("collectionsearchlangues", $sqlgenerate[1]);
        self::assertEquals($sqlgenerate[1]["collectionsearchlangues"], "%langues%");

        $sqlgenerate = $dbinterface->generate_training_sql_search_by_collection("langue", true);

        self::assertIsArray($sqlgenerate);
        self::assertCount(2, $sqlgenerate);
        self::assertEquals($sqlgenerate[0], " OR t.collection LIKE :collectionsearch ESCAPE '\'");
        self::assertIsArray($sqlgenerate[1]);
        self::assertArrayHasKey("collectionsearch", $sqlgenerate[1]);
        self::assertEquals($sqlgenerate[1]["collectionsearch"], "%langue%");

        self::resetAllData();
    }

    /**
     * Test get_max_training_session_index
     *
     * @covers \local_mentor_specialization\database_interface::get_max_training_session_index
     */
    public function test_get_max_training_session_index() {
        global $DB;

        $this->resetAfterTest(true);
        self::setAdminUser();
        $this->reset_singletons();

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();

        // Create entity.
        $entityid = \local_mentor_core\entity_api::create_entity(['name' => 'New Entity 1', 'shortname' => 'New Entity 1']);
        $entity   = \local_mentor_core\entity_api::get_entity($entityid);

        // Create a training course.
        $trainingdata = $this->init_session_data(true);
        $trainingdata = $this->init_training_entity($trainingdata, $entity);
        $training     = \local_mentor_core\training_api::create_training($trainingdata);

        // Create a session course.
        $session                = local_mentor_core\session_api::create_session($training->id, "Session course", true);
        $session->sessionnumber = 1;
        $session->update($session);

        self::assertEquals(1, $dbinterface->get_max_training_session_index($training->id));

        // Create a session course.
        $session                = local_mentor_core\session_api::create_session($training->id, "Session course 2", true);
        $session->sessionnumber = 2;
        $session->update($session);

        self::assertEquals(2, $dbinterface->get_max_training_session_index($training->id));

        self::resetAllData();
    }

    /**
     * Test update_entity_visibility
     *
     * @covers \local_mentor_specialization\database_interface::update_entity_visibility
     */
    public function test_update_entity_visibility() {
        global $DB;

        $this->resetAfterTest(true);
        self::setAdminUser();
        $this->reset_singletons();

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();

        $entityid = \local_mentor_core\entity_api::create_entity(
            array('name' => 'Entity1', 'shortname' => 'Entityshortname1')
        );

        // Hidden data doesn't exist.
        self::assertFalse(
            $DB->record_exists('category_options', array('categoryid' => $entityid, 'name' => 'hidden'))
        );

        // Create hidden data.
        $dbinterface->update_entity_visibility($entityid, 0);
        self::assertTrue(
            $DB->record_exists('category_options', array('categoryid' => $entityid, 'name' => 'hidden'))
        );
        $hiddendata = $DB->get_record('category_options', array('categoryid' => $entityid, 'name' => 'hidden'));
        self::assertEquals(0, $hiddendata->value);

        // Update hidden data.
        $dbinterface->update_entity_visibility($entityid, 1);
        self::assertTrue(
            $DB->record_exists('category_options', array('categoryid' => $entityid, 'name' => 'hidden'))
        );
        $hiddendata2 = $DB->get_record('category_options', array('categoryid' => $entityid, 'name' => 'hidden'));
        self::assertEquals(1, $hiddendata2->value);
        self::assertEquals($hiddendata->id, $hiddendata2->id); // Same data.

        self::resetAllData();
    }

    /**
     * Test get_library_trainings
     *
     * @covers \local_mentor_specialization\database_interface::get_library_trainings
     */
    public function test_get_library_trainings() {
        global $DB;

        $this->resetAfterTest(true);
        self::setAdminUser();
        $this->reset_singletons();

        \local_mentor_core\library_api::get_or_create_library();

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();

        self::assertEmpty($dbinterface->get_library_trainings());

        $entityid     = \local_mentor_core\entity_api::create_entity([
            'name'      => 'New Entity 1',
            'shortname' => 'New Entity 1'
        ]);
        $entity       = \local_mentor_core\entity_api::get_entity($entityid);
        $trainingdata = $this->init_session_data(true);
        $trainingdata = $this->init_training_entity($trainingdata, $entity);
        $training     = \local_mentor_core\training_api::create_training($trainingdata);

        \local_mentor_core\library_api::publish_to_library($training->id, true);
        $newtraininglibraryid = $DB->get_field('library', 'trainingid', array('originaltrainingid' => $training->id));

        $librayrtrinings = $dbinterface->get_library_trainings();
        self::assertCount(1, $librayrtrinings);
        self::assertArrayHasKey($newtraininglibraryid, $librayrtrinings);

        sleep(2);

        $trainingdata->shortname = 'Training';
        $training2     = \local_mentor_core\training_api::create_training($trainingdata);

        \local_mentor_core\library_api::publish_to_library($training2->id, true);
        $newtraininglibraryid2 = $DB->get_field('library', 'trainingid', array('originaltrainingid' => $training2->id));

        $librayrtrinings = $dbinterface->get_library_trainings();
        self::assertCount(2, $librayrtrinings);
        self::assertArrayHasKey($newtraininglibraryid, $librayrtrinings);
        self::assertArrayHasKey($newtraininglibraryid2, $librayrtrinings);
        $orderlibrayrtrinings = array_values($librayrtrinings);
        self::assertEquals($orderlibrayrtrinings[0]->id, $newtraininglibraryid2);
        self::assertEquals($orderlibrayrtrinings[1]->id, $newtraininglibraryid);

        sleep(2);

        \local_mentor_core\library_api::publish_to_library($training->id, true);
        $newtraininglibraryid3 = $DB->get_field('library', 'trainingid', array('originaltrainingid' => $training->id));

        $librayrtrinings = $dbinterface->get_library_trainings();
        self::assertCount(2, $librayrtrinings);
        self::assertArrayHasKey($newtraininglibraryid3, $librayrtrinings);
        self::assertArrayHasKey($newtraininglibraryid2, $librayrtrinings);
        $orderlibrayrtrinings = array_values($librayrtrinings);
        self::assertEquals($orderlibrayrtrinings[0]->id, $newtraininglibraryid3);
        self::assertEquals($orderlibrayrtrinings[1]->id, $newtraininglibraryid2);

        self::resetAllData();
    }
}
