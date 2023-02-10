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
 * Test cases for mentor_core lib
 *
 * @package    local_mentor_specialization
 * @copyright  2021 Edunao SAS (contact@edunao.com)
 * @author     mounir <mounir.ganem@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_mentor_core\session_api;
use local_mentor_core\specialization;
use local_mentor_core\training_api;
use local_mentor_specialization\mentor_training;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/local/mentor_core/lib.php');

class local_mentor_specialization_testcase extends advanced_testcase {

    /**
     * Tests set up.
     */
    public function setUp() {
        self::setAdminUser();
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

        // Fields for training.
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

        $data         = new stdClass();
        $data->opento = 'current_entity';
        $session->update($data);

        return $session->id;
    }

    /**
     * Init training object
     *
     * @return stdClass
     * @throws dml_exception
     * @throws moodle_exception
     */
    private function get_training_data() {
        // Init test data.
        $trainingdata = new stdClass();

        $trainingdata->name      = 'fullname';
        $trainingdata->shortname = 'shortname';
        $trainingdata->content   = 'summary';

        // Create training object.
        $trainingdata->teaser                       = 'http://www.edunao.com/';
        $trainingdata->teaserpicture                = '';
        $trainingdata->prerequisite                 = 'TEST';
        $trainingdata->collection                   = 'accompagnement';
        $trainingdata->traininggoal                 = 'TEST TRAINING ';
        $trainingdata->idsirh                       = 'TEST ID SIRH';
        $trainingdata->licenseterms                 = 'cc-sa';
        $trainingdata->typicaljob                   = 'TEST';
        $trainingdata->skills                       = [1, 3];
        $trainingdata->certifying                   = '1';
        $trainingdata->presenceestimatedtimehours   = '12';
        $trainingdata->presenceestimatedtimeminutes = '10';
        $trainingdata->remoteestimatedtimehours     = '15';
        $trainingdata->remoteestimatedtimeminutes   = '30';
        $trainingdata->trainingmodalities           = 'd';
        $trainingdata->producingorganization        = 'TEST';
        $trainingdata->producerorganizationlogo     = '';
        $trainingdata->designers                    = 'TEST';
        $trainingdata->contactproducerorganization  = 'TEST';
        $trainingdata->thumbnail                    = '';
        $trainingdata->status                       = \local_mentor_core\training::STATUS_DRAFT;

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

        // Fill with entity data.
        $formationid                     = $entity->get_entity_formation_category();
        $trainingdata->categorychildid   = $formationid;
        $trainingdata->categoryid        = $entity->id;
        $trainingdata->creativestructure = $entity->id;

        return $trainingdata;
    }

    /**
     * @covers ::local_mentor_core_validate_users_csv()
     */
    public function test_local_mentor_core_validate_users_csv() {
        global $CFG;
        $CFG->defaultauth = 'manual';

        $this->resetAfterTest();
        $this->init_config();
        $this->reset_singletons();

        $delimitername = 'comma';

        /** @var mentor_training $training */
        $training = training_api::create_training($this->get_training_data());

        // Session creation.
        $session = session_api::create_session($training->id, 'session 1', true);

        // Course.
        $course = $session->get_course();

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

        // Group creation.
        $group           = new stdClass();
        $group->name     = 'testgroup';
        $group->courseid = $course->id;
        groups_create_group($group);

        // Check for invalid headers.
        $content = [
            'lastname,aaa,email,group',
            'lastname,firstname,test@test.com,testgroup',
        ];

        $hasfatalerrors = local_mentor_core_validate_users_csv($content, $delimitername, $course->id);

        self::assertTrue($hasfatalerrors);

        // Check for missing headers.
        $content = [
            'lastname,,email',
            'lastname,firstname,test@test.com,testgroup',
        ];

        $hasfatalerrors = local_mentor_core_validate_users_csv($content, $delimitername, $course->id);

        self::assertTrue($hasfatalerrors);

        // Errors array.
        $errors = [
            'list' => [], // Errors list.
        ];

        // Preview array.
        $preview = [
            'list'             => [], // Cleaned list of accounts.
            'validlines'       => 0, // Number of lines without error.
            'validforcreation' => 0, // Number of lines that will create an account.
        ];

        $content = [
            'lastname,firstname,email,group,role',
            'dadada,dadada,dada\&#da@aaa.com,dadada,',
            'dedede,dede?$*-_de,dedede@aaa.com,',
            '',
            'aaa,aaa',
            'ganem,,blablabla@gmail.com,dididi',
            'dididi,dididi,dididi@aaa.com,dididi',
            'lastname,firstname,test@test.com,dididi',
            'dididi,dididi,participant1@test.com,testgroup,Tuteur',
        ];

        $expectedpreview = [
            [
                'linenumber' => 7,
                'lastname'   => 'dididi',
                'firstname'  => 'dididi',
                'email'      => 'dididi@aaa.com',
                'role'       => 'Participant',
                'groupname'  => 'dididi',
            ],
            [
                'linenumber' => 8,
                'lastname'   => 'lastname',
                'firstname'  => 'firstname',
                'email'      => 'test@test.com',
                'role'       => 'Participant',
                'groupname'  => 'dididi',
            ],
            [
                'linenumber' => 9,
                'lastname'   => 'dididi',
                'firstname'  => 'dididi',
                'email'      => 'participant1@test.com',
                'role'       => 'Tuteur',
                'groupname'  => 'testgroup',
            ],
        ];

        $expectederrors = [
            [2, get_string('invalid_email', 'local_mentor_core')],
            [3, get_string('error_specials_chars', 'local_mentor_core')],
            [5, get_string('error_missing_field', 'local_mentor_core')],
            [6, get_string('error_missing_field', 'local_mentor_core')],
        ];

        $hasfatalerrors = local_mentor_core_validate_users_csv($content, $delimitername, $course->id, $preview, $errors, $warnings);

        self::assertTrue($hasfatalerrors);
        self::assertCount(3, $preview['list']);
        self::assertCount(4, $errors['list']);
        self::assertEquals(3, $preview['validlines']);
        self::assertEquals(2, $preview['validforcreation']);
        self::assertEquals($expectedpreview, $preview['list']);
        self::assertCount(2, $warnings);
        self::assertCount(2, $warnings['groupsnotfound']);
        self::assertCount(2, $warnings['list']);
        self::assertEquals($expectederrors, $errors['list']);

        // Check with 2 users (User 1 with his username equals to User 2 email).
        $user2             = new stdClass();
        $user2->lastname   = 'lastname';
        $user2->firstname  = 'firstname';
        $user2->email      = 'test22@test.com';
        $user2->username   = 'test@test.com';
        $user2->password   = 'to be generated';
        $user2->mnethostid = 1;
        $user2->confirmed  = 1;
        $user2->auth       = 'manual';

        local_mentor_core\profile_api::create_user($user2);

        // New content.
        $content = [
            'email,lastname,firstname,group',
            'test@test.com,lastname,firstname,testgroup',
        ];

        // Errors array.
        $errors = [
            'list'           => [], // Errors list.
            'groupsnotfound' => [], // List of not found group name.
        ];

        // Preview array.
        $preview = [
            'list'             => [], // Cleaned list of accounts.
            'validlines'       => 0, // Number of lines without error.
            'validforcreation' => 0, // Number of lines that will create an account.
        ];

        $hasfatalerrors = local_mentor_core_validate_users_csv($content, $delimitername, $course->id, $preview, $errors);

        self::assertTrue($hasfatalerrors);
        self::assertEquals([[2, get_string('email_already_used', 'local_mentor_core')]], $errors['list']);

        self::resetAllData();
    }

    /**
     * @covers ::local_mentor_core_enrol_users_csv()
     */
    public function test_local_mentor_core_enrol_users() {
        global $CFG, $DB;
        $CFG->defaultauth = 'manual';

        $this->resetAfterTest();
        $this->init_config();
        $this->reset_singletons();

        /** @var mentor_training $training */
        $training = training_api::create_training($this->get_training_data());

        // Session creation.
        $session = session_api::create_session($training->id, 'session 1', true);

        // Create self enrolment instance.
        $session->create_self_enrolment_instance();

        // Course.
        $course = $session->get_course();

        // Group creation.
        $group           = new stdClass();
        $group->name     = 'testgroup';
        $group->courseid = $course->id;
        groups_create_group($group);

        // Create user.
        $user             = new stdClass();
        $user->lastname   = 'lastname';
        $user->firstname  = 'firstname';
        $user->email      = 'test@test.com';
        $user->username   = 'test@test.com';
        $user->password   = 'to be generated';
        $user->mnethostid = 1;
        $user->confirmed  = 1;
        $user->auth       = 'manual';

        local_mentor_core\profile_api::create_user($user);

        self::assertEquals(1, get_users(false, 'lastname'));

        // Users to enrol.
        $users = [
            [
                'lastname'  => 'lastname',
                'firstname' => 'firstname',
                'email'     => 'test@test.com',
                'groupname' => 'testgroup',
            ],
            [
                'lastname'  => 'lastname2',
                'firstname' => 'firstname2',
                'email'     => 'test2@test.com',
                'groupname' => null,
            ]
        ];

        local_mentor_core_enrol_users_csv($course->id, $users);

        $participantrole = $DB->get_record('role', ['shortname' => 'participant']);

        $userlist = get_users(true, 'lastname', false, null, 'firstname ASC', '', '', '', 10);

        self::assertCount(2, $userlist);
        foreach ($userlist as $u) {
            self::assertTrue($session->user_is_enrolled($u->id));
            $roleassignement = $DB->get_record(
                'role_assignments',
                ['userid' => $u->id, 'contextid' => $session->get_context()->id]
            );
            self::assertEquals($roleassignement->roleid, $participantrole->id);
        }

        // Users to update role.
        $users = [
            [
                'lastname'  => 'lastname',
                'firstname' => 'firstname',
                'email'     => 'test@test.com',
                'groupname' => 'testgroup',
                'role'      => 'formateur',
            ],
            [
                'lastname'  => 'lastname2',
                'firstname' => 'firstname2',
                'email'     => 'test2@test.com',
                'groupname' => null,
                'role'      => 'formateur',
            ]
        ];

        $formateurrole = $DB->get_record('role', ['shortname' => 'formateur']);

        local_mentor_core_enrol_users_csv($course->id, $users);

        $userlist = get_users(true, 'lastname', false, null, 'firstname ASC', '', '', '', 10);

        foreach ($userlist as $u) {
            self::assertTrue($session->user_is_enrolled($u->id));
            $roleassignement = $DB->get_record(
                'role_assignments',
                ['userid' => $u->id, 'contextid' => $session->get_context()->id]
            );
            self::assertEquals($roleassignement->roleid, $formateurrole->id);
        }

        self::resetAllData();
    }

    /**
     * @covers \local_mentor_core\profile_api::create_user()
     */
    public function test_local_mentor_core_create_user() {
        global $CFG;
        $CFG->defaultauth = 'manual';

        $this->resetAfterTest();
        $this->init_config();
        $this->reset_singletons();

        $user             = new stdClass();
        $user->lastname   = 'lastname';
        $user->firstname  = 'firstname';
        $user->email      = 'test@test.com';
        $user->username   = 'test@test.com';
        $user->password   = 'to be generated';
        $user->mnethostid = 1;
        $user->confirmed  = 1;
        $user->auth       = 'manual';

        $userid = local_mentor_core\profile_api::create_user($user);

        self::assertIsInt($userid);

        self::resetAllData();
    }

    /**
     * Test get_sub_entity_form_fields function
     *
     * @covers \local_mentor_specialization\mentor_specialization::get_specialization()
     * @covers \local_mentor_specialization\mentor_specialization::get_sub_entity_form_fields()
     */
    public function test_local_specialization_get_sub_entity_form_fields() {
        global $PAGE;

        $this->resetAfterTest();
        $this->init_config();
        $this->reset_singletons();

        $specialization     = new \local_mentor_specialization\mentor_specialization();
        $extrahtml          = '<p>Test</p>';
        $subentityformfield = $specialization->get_specialization('get_sub_entity_form_fields', $extrahtml);

        $renderer = $PAGE->get_renderer('local_mentor_specialization', 'entity');
        self::assertEquals($extrahtml . $renderer->get_sub_entity_form_fields(), $subentityformfield);

        self::resetAllData();
    }

    /**
     * Test get_user_template_params function
     *
     * @covers \local_mentor_specialization\mentor_specialization::get_specialization()
     * @covers \local_mentor_specialization\mentor_specialization::get_user_template_params()
     */
    public function test_local_specialization_get_user_template_params() {
        $this->resetAfterTest();
        $this->init_config();
        $this->reset_singletons();

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();

        // Create new entity.
        \local_mentor_core\entity_api::create_entity([
            'name'      => 'New Entity 1',
            'shortname' => 'New Entity 1',
            'userid'    => 2  // Set the admin user as manager of the entity.
        ]);

        $specialization = new \local_mentor_specialization\mentor_specialization();
        $params         = [];
        $params         = $specialization->get_specialization('get_user_template_params', $params);

        $listentities = \local_mentor_core\entity_api::get_all_entities();
        self::assertEquals(array_merge([0 => ''], $listentities), $params['entities']);

        $noregion       = new \stdClass();
        $noregion->id   = 0;
        $noregion->name = get_string('none', 'local_mentor_core');
        $regions        = $dbinterface->get_all_regions();
        self::assertEquals(array_merge([$noregion], $regions), $params['regions']);

        self::resetAllData();
    }

    /**
     * Test get_training_template_params function
     *
     * @covers \local_mentor_specialization\mentor_specialization::get_specialization()
     * @covers \local_mentor_specialization\mentor_specialization::get_training_template_params()
     */
    public function test_local_specialization_get_training_template_params() {
        $this->resetAfterTest();
        $this->init_config();
        $this->reset_singletons();

        set_config('collections', 'accompagnement|Accompagnement des transitions professionnelles|#CECECE',
            'local_mentor_specialization');

        $specialization = new \local_mentor_specialization\mentor_specialization();
        $params         = new stdClass();
        $params         = $specialization->get_specialization('get_training_template_params', $params);

        self::assertCount(1, $params->collections);
        self::assertEquals($params->collections[0]['key'], 'accompagnement');
        self::assertEquals($params->collections[0]['value'], 'Accompagnement des transitions professionnelles');

        self::assertCount(4, $params->status);
        $draft          = [];
        $draft['key']   = 'draft';
        $draft['value'] = 'Brouillon';
        self::assertTrue(in_array($draft, $params->status));
        $template          = [];
        $template['key']   = 'template';
        $template['value'] = 'Gabarit';
        self::assertTrue(in_array($template, $params->status));
        $elaborationcompleted          = [];
        $elaborationcompleted['key']   = 'elaboration_completed';
        $elaborationcompleted['value'] = 'Elaboration terminée';
        self::assertTrue(in_array($elaborationcompleted, $params->status));
        $archived          = [];
        $archived['key']   = 'archived';
        $archived['value'] = 'Archivée';
        self::assertTrue(in_array($archived, $params->status));

        self::resetAllData();
    }

    /**
     * Test get_session_template_params function
     *
     * @covers \local_mentor_specialization\mentor_specialization::get_specialization()
     * @covers \local_mentor_specialization\mentor_specialization::get_session_template_params()
     */
    public function test_local_specialization_get_session_template_params() {
        $this->resetAfterTest();
        $this->init_config();
        $this->reset_singletons();

        set_config('collections', 'accompagnement|Accompagnement des transitions professionnelles|#CECECE',
            'local_mentor_specialization');

        $specialization = new \local_mentor_specialization\mentor_specialization();
        $params         = new stdClass();
        $params         = $specialization->get_specialization('get_session_template_params', $params);

        self::assertCount(1, $params->collections);
        self::assertEquals($params->collections[0]['key'], 'accompagnement');
        self::assertEquals($params->collections[0]['value'], 'Accompagnement des transitions professionnelles');

        self::assertCount(7, $params->status);
        $inpreparation          = [];
        $inpreparation['key']   = 'inpreparation';
        $inpreparation['value'] = 'En préparation';
        self::assertTrue(in_array($inpreparation, $params->status));
        $openedregistration          = [];
        $openedregistration['key']   = 'openedregistration';
        $openedregistration['value'] = 'Inscriptions ouvertes';
        self::assertTrue(in_array($openedregistration, $params->status));
        $inprogress          = [];
        $inprogress['key']   = 'inprogress';
        $inprogress['value'] = 'En cours';
        self::assertTrue(in_array($inprogress, $params->status));
        $completed          = [];
        $completed['key']   = 'completed';
        $completed['value'] = 'Terminée';
        self::assertTrue(in_array($completed, $params->status));
        $archived          = [];
        $archived['key']   = 'archived';
        $archived['value'] = 'Archivée';
        self::assertTrue(in_array($archived, $params->status));
        $reported          = [];
        $reported['key']   = 'reported';
        $reported['value'] = 'Reportée';
        self::assertTrue(in_array($reported, $params->status));
        $cancelled          = [];
        $cancelled['key']   = 'cancelled';
        $cancelled['value'] = 'Annulée';
        self::assertTrue(in_array($cancelled, $params->status));

        self::resetAllData();
    }

    /**
     * Test get_user_template function
     *
     * @covers \local_mentor_specialization\mentor_specialization::get_specialization()
     * @covers \local_mentor_specialization\mentor_specialization::get_user_template()
     */
    public function test_local_specialization_get_user_template() {
        $this->resetAfterTest();
        $this->init_config();
        $this->reset_singletons();

        $specialization = new \local_mentor_specialization\mentor_specialization();
        $usertemplate   = $specialization->get_specialization('get_user_template', '');

        self::assertEquals($usertemplate, 'local_mentor_specialization/user');

        self::resetAllData();
    }

    /**
     * Test get_user_javascript function
     *
     * @covers \local_mentor_specialization\mentor_specialization::get_specialization()
     * @covers \local_mentor_specialization\mentor_specialization::get_user_javascript()
     */
    public function test_local_specialization_get_user_javascript() {
        $this->resetAfterTest();
        $this->init_config();
        $this->reset_singletons();

        $specialization = new \local_mentor_specialization\mentor_specialization();
        $userjavascript = $specialization->get_specialization('get_user_javascript', '');

        self::assertEquals($userjavascript, 'local_mentor_specialization/user');

        self::resetAllData();
    }

    /**
     * Test get_entity_javascript function
     *
     * @covers \local_mentor_specialization\mentor_specialization::get_specialization()
     * @covers \local_mentor_specialization\mentor_specialization::get_entity_javascript()
     */
    public function test_local_specialization_get_entity_javascript() {
        $this->resetAfterTest();
        $this->init_config();
        $this->reset_singletons();

        $specialization   = new \local_mentor_specialization\mentor_specialization();
        $entityjavascript = $specialization->get_specialization('get_entity_javascript', '');

        self::assertEquals($entityjavascript, 'local_mentor_specialization/entities');

        self::resetAllData();
    }

    /**
     * Test count_session_record function
     *
     * @covers \local_mentor_specialization\mentor_specialization::get_specialization()
     * @covers \local_mentor_specialization\mentor_specialization::count_session_record()
     * @covers \local_mentor_specialization\database_interface::get_sessions_by_entity_id()
     */
    public function test_local_specialization_count_session_record() {
        $this->resetAfterTest();
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $sessionid = $this->init_session_creation();
        $session   = \local_mentor_core\session_api::get_session($sessionid);

        $params                = [];
        $data                  = new stdClass();
        $data->entityid        = $session->get_entity()->id;
        $data->search          = [];
        $data->search['value'] = '';
        $data->order           = 0;
        $data->start           = 0;
        $data->length          = 50;
        $params['data']        = $data;

        // One session when user is admin.
        $specialization = new \local_mentor_specialization\mentor_specialization();
        $countsession   = $specialization->get_specialization('count_session_record', null, $params);
        self::assertEquals(1, $countsession);

        // One session when user is manager.
        $user = self::getDataGenerator()->create_user();
        $session->get_entity()->assign_manager($user->id);
        self::setUser($user);
        $countsession = $specialization->get_specialization('count_session_record', null, $params);
        self::assertEquals(1, $countsession);

        // Zero session when user is not manager.
        $user2 = self::getDataGenerator()->create_user();
        self::setUser($user2);
        $countsession = $specialization->get_specialization('count_session_record', null, $params);
        self::assertEquals(0, $countsession);

        self::resetAllData();
    }

    /**
     * Test prepare_update_session_editor_data function
     *
     * @covers \local_mentor_specialization\mentor_specialization::get_specialization()
     * @covers \local_mentor_specialization\mentor_specialization::prepare_update_session_editor_data()
     */
    public function test_local_specialization_prepare_update_session_editor_data() {
        $this->resetAfterTest();
        $this->init_config();
        $this->reset_singletons();

        $termsregistrationdetail = 'termsregistrationdetailtest';
        $placesavailable         = 10;

        $data                          = new stdClass();
        $data->termsregistrationdetail = $termsregistrationdetail;
        $data->placesavailable         = $placesavailable;

        $specialization = new \local_mentor_specialization\mentor_specialization();
        $newdata        = $specialization->get_specialization('prepare_update_session_editor_data', $data);

        self::assertEquals($newdata->termsregistrationdetail, array(
            'text'   => $termsregistrationdetail,
            'format' => FORMAT_HTML
        ));
        self::assertEquals($newdata->placesavailable, 10);

        $placesavailable2               = -2;
        $data2                          = new stdClass();
        $data2->termsregistrationdetail = $termsregistrationdetail;
        $data2->placesavailable         = $placesavailable2;

        $newdata2 = $specialization->get_specialization('prepare_update_session_editor_data', $data2);

        self::assertEquals($newdata2->termsregistrationdetail, array(
            'text'   => $termsregistrationdetail,
            'format' => FORMAT_HTML
        ));
        self::assertEquals($newdata2->placesavailable, '<span style="color: red;">' . $placesavailable2 . '</span>');

        self::resetAllData();
    }

    /**
     * Test convert_update_session_editor_data function
     *
     * @covers \local_mentor_specialization\mentor_specialization::get_specialization()
     * @covers \local_mentor_specialization\mentor_specialization::convert_update_session_editor_data()
     */
    public function test_local_specialization_convert_update_session_editor_data() {
        $this->resetAfterTest();
        $this->init_config();
        $this->reset_singletons();

        $termsregistrationdetail = 'termsregistrationdetailtest';

        $data                                  = new stdClass();
        $data->termsregistrationdetail         = [];
        $data->termsregistrationdetail['text'] = $termsregistrationdetail;

        $specialization = new \local_mentor_specialization\mentor_specialization();
        $newdata        = $specialization->get_specialization('convert_update_session_editor_data', $data);

        self::assertIsNotArray($newdata->termsregistrationdetail);
        self::assertEquals($newdata->termsregistrationdetail, $termsregistrationdetail);

        self::resetAllData();
    }

    /**
     * Test get_params_renderer_catalog function
     *
     * @covers \local_mentor_specialization\mentor_specialization::get_specialization()
     * @covers \local_mentor_specialization\mentor_specialization::get_params_renderer_catalog()
     */
    public function test_local_specialization_get_params_renderer_catalog() {
        global $USER;

        $this->resetAfterTest();
        $this->init_config();
        $this->reset_singletons();

        set_config('collections', 'accompagnement|Accompagnement des transitions professionnelles|#CECECE',
            'local_mentor_specialization');

        self::setAdminUser();

        $sessonid = $this->init_session_creation('sessionname');
        $session  = \local_mentor_core\session_api::get_session($sessonid);
        $session->update_status(\local_mentor_core\session::STATUS_OPENED_REGISTRATION);
        $trainings
            = \local_mentor_core\training_api::get_user_available_sessions_by_trainings($USER->id);

        $paramsrenderer = new stdClass();

        $specialization = new \local_mentor_specialization\mentor_specialization();
        $paramsrenderer = $specialization->get_specialization('get_params_renderer_catalog', $paramsrenderer);

        // Collection.
        self::assertObjectHasAttribute('collections', $paramsrenderer);
        self::assertIsArray($paramsrenderer->collections);
        self::assertEquals($paramsrenderer->collections[0], 'Accompagnement des transitions professionnelles');

        // Entity.
        self::assertObjectHasAttribute('entities', $paramsrenderer);
        self::assertIsArray($paramsrenderer->entities);
        self::assertCount(1, $paramsrenderer->entities);
        self::assertEquals($paramsrenderer->entities[0], array(
            'id'   => $session->get_entity()->id,
            'name' => $session->get_entity()->shortname
        ));

        // Training.
        self::assertObjectHasAttribute('trainings', $paramsrenderer);
        self::assertIsArray($paramsrenderer->trainings);
        self::assertCount(1, $paramsrenderer->trainings);
        self::assertIsObject($paramsrenderer->trainings[0]);
        self::assertEquals($paramsrenderer->trainings[0]->id, $session->get_training()->id);
        self::assertEquals($paramsrenderer->trainings[0]->name, $session->get_training()->name);

        // Training count.
        self::assertObjectHasAttribute('trainingscount', $paramsrenderer);
        self::assertIsInt($paramsrenderer->trainingscount);
        self::assertEquals($paramsrenderer->trainingscount, 1);

        // Available trainings.
        self::assertObjectHasAttribute('available_trainings', $paramsrenderer);
        self::assertIsString($paramsrenderer->available_trainings);
        $collection                    = new stdClass();
        $collection->name              = 'Accompagnement des transitions professionnelles';
        $collection->color             = '#CECECE';
        $trainings[0]->collectiontiles = array($collection);
        $trainings[0]->sessions        = [];
        self::assertEquals($paramsrenderer->available_trainings, json_encode($trainings, JSON_HEX_TAG));

        // Training dictionnary.
        self::assertObjectHasAttribute('trainings_dictionnary', $paramsrenderer);
        self::assertIsString($paramsrenderer->trainings_dictionnary);
        self::assertEquals($paramsrenderer->trainings_dictionnary, json_encode(local_catalog_get_dictionnary($trainings)));

        self::resetAllData();
    }

    /**
     * Test get_footer function
     *
     * @covers \local_mentor_specialization\mentor_specialization::get_specialization()
     * @covers \local_mentor_specialization\mentor_specialization::get_footer()
     */
    public function test_local_specialization_get_footer() {
        $this->resetAfterTest();
        $this->init_config();
        $this->reset_singletons();

        $specialization = new \local_mentor_specialization\mentor_specialization();
        $footer         = $specialization->get_specialization('get_footer', '');

        self::assertEquals($footer, '<div id="regions" style="display:none;">' .
                                    json_encode(local_mentor_specialization_get_regions_and_departments()) . '</div>');

        self::resetAllData();
    }

    /**
     * Test get_session_enrolment_data function
     *
     * @covers \local_mentor_specialization\mentor_specialization::get_specialization()
     * @covers \local_mentor_specialization\mentor_specialization::get_session_enrolment_data()
     */
    public function test_local_specialization_get_session_enrolment_data_registration_is_not_free() {
        $this->resetAfterTest();
        $this->init_config();
        $this->reset_singletons();

        $data           = new stdClass();
        $sessionid      = $this->init_session_creation();
        $session        = \local_mentor_core\session_api::get_session($sessionid);
        $specialization = new \local_mentor_specialization\mentor_specialization();
        $data           = $specialization->get_specialization('get_session_enrolment_data', $data, $sessionid);

        // Training count.
        self::assertObjectHasAttribute('selfenrolment', $data);
        self::assertIsInt($data->selfenrolment);
        self::assertEquals($data->selfenrolment, 0);

        // Training count.
        self::assertObjectHasAttribute('message', $data);
        self::assertNull($data->message);
        self::assertEquals($data->message, $session->termsregistrationdetail);

        self::resetAllData();
    }

    /**
     * Test get_session_enrolment_data function
     *
     * @covers \local_mentor_specialization\mentor_specialization::get_specialization()
     * @covers \local_mentor_specialization\mentor_specialization::get_session_enrolment_data()
     */
    public function test_local_specialization_get_session_enrolment_data_registration_is_free() {
        $this->resetAfterTest();
        $this->init_config();
        $this->reset_singletons();

        $sessionid                         = $this->init_session_creation();
        $newdatasession                    = new stdClass();
        $newdatasession->id                = $sessionid;
        $newdatasession->termsregistration = 'inscriptionlibre';
        \local_mentor_core\session_api::update_session($newdatasession);

        $data           = new stdClass();
        $session        = \local_mentor_core\session_api::get_session($sessionid);
        $specialization = new \local_mentor_specialization\mentor_specialization();
        $data           = $specialization->get_specialization('get_session_enrolment_data', $data, $sessionid);

        // Termsregistration is not 'inscriptionlibre'.
        // Training count.
        self::assertObjectHasAttribute('selfenrolment', $data);
        self::assertIsInt($data->selfenrolment);
        self::assertEquals($data->selfenrolment, 1);

        // Training count.
        self::assertObjectHasAttribute('hasselfregistrationkey', $data);
        self::assertFalse($data->hasselfregistrationkey);
        self::assertEquals($data->hasselfregistrationkey, $session->has_registration_key());

        self::resetAllData();
    }

    /**
     * Test get_training_sheet_template function
     *
     * @covers \local_mentor_specialization\mentor_specialization::get_specialization()
     * @covers \local_mentor_specialization\mentor_specialization::get_training_sheet_template()
     */
    public function test_local_specialization_get_training_sheet_template() {
        $this->resetAfterTest();
        $this->init_config();
        $this->reset_singletons();

        $specialization        = new \local_mentor_specialization\mentor_specialization();
        $trainingsheettemplate = $specialization->get_specialization('get_training_sheet_template', '');

        self::assertEquals($trainingsheettemplate, 'local_mentor_specialization/catalog/training-sheet');

        self::resetAllData();
    }

    /**
     * Test get_session_sheet_template function
     *
     * @covers \local_mentor_specialization\mentor_specialization::get_specialization()
     * @covers \local_mentor_specialization\mentor_specialization::get_session_sheet_template()
     */
    public function test_local_specialization_get_session_sheet_template() {
        $this->resetAfterTest();
        $this->init_config();
        $this->reset_singletons();

        $specialization       = new \local_mentor_specialization\mentor_specialization();
        $sessionsheettemplate = $specialization->get_specialization('get_session_sheet_template', '');

        self::assertEquals($sessionsheettemplate, 'local_mentor_specialization/catalog/session-sheet');

        self::resetAllData();
    }

    /**
     * Test get_user_manager_role_name function
     *
     * @covers \local_mentor_specialization\mentor_specialization::get_specialization()
     * @covers \local_mentor_specialization\mentor_specialization::get_user_manager_role_name()
     */
    public function test_local_specialization_get_user_manager_role_name() {
        $this->resetAfterTest();
        $this->init_config();
        $this->reset_singletons();

        $specialization  = new \local_mentor_specialization\mentor_specialization();
        $usermanagerrole = $specialization->get_specialization('get_user_manager_role_name', '');

        self::assertEquals($usermanagerrole, 'admindedie');

        self::resetAllData();
    }

    /**
     * Test get_signup_url function
     *
     * @covers \local_mentor_specialization\mentor_specialization::get_specialization()
     * @covers \local_mentor_specialization\mentor_specialization::get_signup_url()
     */
    public function test_local_specialization_get_signup_url() {
        global $CFG;

        $this->resetAfterTest();
        $this->init_config();
        $this->reset_singletons();

        $specialization = new \local_mentor_specialization\mentor_specialization();
        $signupurl      = $specialization->get_specialization('get_signup_url');

        self::assertEquals($signupurl->out(), $CFG->wwwroot . '/theme/mentor/pages/verify_email.php');

        self::resetAllData();
    }

    /**
     * Test count_trainings_by_entity
     *
     * @covers \local_mentor_specialization\mentor_specialization::get_specialization()
     * @covers \local_mentor_specialization\mentor_specialization::count_trainings_by_entity()
     */
    public function test_count_trainings_by_entity() {
        $this->resetAfterTest();
        $this->init_config();
        $this->reset_singletons();

        // Get entity object for default category.
        $entityid = \local_mentor_core\entity_api::create_entity([
            'name'      => 'New Entity 1',
            'shortname' => 'New Entity 1'
        ]);
        $entity   = \local_mentor_core\entity_api::get_entity($entityid);

        $data           = new \stdClass();
        $data->entityid = $entityid;
        $data->start    = 0;
        $data->length   = 0;
        $data->status   = false;
        $data->datefrom = false;
        $data->dateto   = false;
        $data->search   = false;
        $data->order    = false;
        $data->filters  = [];
        $specialization = new \local_mentor_specialization\mentor_specialization();
        self::assertEquals(0, $specialization->get_specialization('count_trainings_by_entity', null, [
            'data' => $data,
        ]));

        $data = $this->init_session_data(true);
        $data = $this->init_training_entity($data, $entity);
        \local_mentor_core\training_api::create_training($data);

        $data           = new \stdClass();
        $data->entityid = $entityid;
        $data->start    = 0;
        $data->length   = 0;
        $data->status   = false;
        $data->datefrom = false;
        $data->dateto   = false;
        $data->search   = false;
        $data->order    = false;
        $data->filters  = [];
        self::assertEquals(1, $specialization->get_specialization('count_trainings_by_entity', null, [
            'data' => $data,
        ]));

        self::resetAllData();
    }

    /**
     * Test get_library
     *
     * @covers \local_mentor_specialization\mentor_specialization::get_specialization()
     * @covers \local_mentor_specialization\mentor_specialization::get_library()
     */
    public function test_get_library() {
        $this->resetAfterTest();
        $this->init_config();
        $this->reset_singletons();

        $specialization = new \local_mentor_specialization\mentor_specialization();
        $library        = $specialization->get_specialization('get_library');

        self::assertIsObject($library);
        self::assertInstanceOf('\local_mentor_core\library', $library);

        $dbi            = \local_mentor_specialization\database_interface::get_instance();
        $librairyobject = $dbi->get_library_object();
        self::assertEquals($library->id, $librairyobject->id);
        self::assertEquals($library->name, $librairyobject->name);
        self::assertEquals($library->shortname, $librairyobject->idnumber);

        self::resetAllData();
    }
}
