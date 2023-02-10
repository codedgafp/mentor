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
 * Test cases for RCF role
 *
 * @package    local_mentor_core
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     rcolet <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_mentor_core\specialization;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/local/mentor_core/api/entity.php');
require_once($CFG->dirroot . '/local/mentor_core/classes/model/entity.php');

class local_mentor_specialization_responsable_central_formation_testcase extends advanced_testcase {
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
     * Initialization of the database for the tests
     */
    public function init_database() {
        global $DB;

        // Delete Miscellaneous category.
        $DB->delete_records('course_categories', array('id' => 1));
    }

    /**
     * Init entities data
     *
     * @return array
     */
    public function get_entities_data() {
        $this->init_database();

        return [
            [
                'name'      => 'New Entity 1',
                'shortname' => 'New Entity 1',
                'regions'   => [5], // Corse.
                'userid'    => 2  // Set the admin user as manager of the entity.
            ],
            [
                'name'      => 'New Entity 2',
                'shortname' => 'New Entity 2',
                'regions'   => [8], // Guyane.
                'userid'    => 2  // Set the admin user as manager of the entity.
            ],
            [
                'name'      => 'New Entity 3',
                'shortname' => 'New Entity 3',
                'regions'   => [5], // Corse.
                'userid'    => 2  // Set the admin user as manager of the entity.
            ],
            [
                'name'      => 'New Entity 4',
                'shortname' => 'New Entity 4',
                'regions'   => [8], // Guyane.
                'userid'    => 2  // Set the admin user as manager of the entity.
            ],
        ];
    }

    /**
     * Init training object
     *
     * @return stdClass
     * @throws dml_exception
     * @throws moodle_exception
     */
    private function get_training_data($trainingname, $entitydata = null) {
        set_config('collections', 'accompagnement|Accompagnement des transitions professionnelles|#CECECE',
            'local_mentor_specialization');

        if ($entitydata === null) {
            $entitydata = $this->get_entities_data()[0];
        }

        // Init test data.
        $trainingdata = new stdClass();

        $trainingdata->name      = $trainingname;
        $trainingdata->shortname = $trainingname;
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
        $trainingdata->status                       = 'dr';
        $trainingdata->content                      = [];
        $trainingdata->content['text']              = 'ContentText';

        // Get entity object for default category.
        $entityid = \local_mentor_core\entity_api::create_entity($entitydata);

        $entity = \local_mentor_core\entity_api::get_entity($entityid);

        // Fill with entity data.
        $formationid                     = $entity->get_entity_formation_category();
        $trainingdata->categorychildid   = $formationid;
        $trainingdata->categoryid        = $entity->id;
        $trainingdata->creativestructure = $entity->id;

        return $trainingdata;
    }

    /**
     * Test of list entity user managed with role RCF
     * (managed entity is not allowed for RCF)
     *
     * @throws ReflectionException
     */
    public function test_get_managed_entities_rcf_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $entity1data = $this->get_entities_data()[0];

        // Test standard Entity creation.
        try {
            $entity1id = \local_mentor_core\entity_api::create_entity($entity1data);
        } catch (\Exception $e) {
            // Failed if the name of this entity is already in use.
            self::fail($e->getMessage());
        }

        $entity2data = $this->get_entities_data()[1];

        // Test standard Entity creation.
        try {
            \local_mentor_core\entity_api::create_entity($entity2data);
        } catch (\Exception $e) {
            // Failed if the name of this entity is already in use.
            self::fail($e->getMessage());
        }

        $entity1 = \local_mentor_core\entity_api::get_entity($entity1id);
        $newuser = self::getDataGenerator()->create_user();

        \local_mentor_core\profile_api::role_assign('respformation', $newuser->id, $entity1->get_context());

        self::setUser($newuser);

        // Is not manager.
        self::assertCount(0, \local_mentor_core\entity_api::get_managed_entities($newuser));

        self::setAdminUser();

        self::resetAllData();
    }

    /**
     * Test of list training by entity with role RCF
     *
     * @throws ReflectionException
     */
    public function test_get_user_training_courses_rcf_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $entitydata = $this->get_entities_data()[0];

        // Init training data.
        $trainingdata = $this->get_training_data('NEWTRAINING', $entitydata);

        // Create training.
        \local_mentor_core\training_api::create_training($trainingdata);

        $entity  = \local_mentor_core\entity_api::get_entity_by_name($entitydata['name']);
        $newuser = self::getDataGenerator()->create_user();

        self::setUser($newuser);

        // Check if user not manage training.
        self::assertCount(0, local_mentor_core\training_api::get_user_training_courses());

        self::setAdminUser();

        \local_mentor_core\profile_api::role_assign('respformation', $newuser->id, $entity->get_context());

        self::setUser($newuser);

        // Check if have 1 training.
        self::assertCount(1, local_mentor_core\training_api::get_trainings_by_entity($entity->id));
        // Check if user manage training.
        self::assertCount(1, local_mentor_core\training_api::get_user_training_courses());

        self::setAdminUser();

        self::resetAllData();
    }

    /**
     * Test of list training by entity with role RCF
     *
     * @throws ReflectionException
     */
    public function test_get_user_training_courses_with_sub_entity_rcf_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        // Create training in main entity.
        $entitydata = $this->get_entities_data()[0];
        // Init training data.
        $trainingdata = $this->get_training_data('NEWTRAINING', $entitydata);
        // Create training.
        \local_mentor_core\training_api::create_training($trainingdata);
        $entity = \local_mentor_core\entity_api::get_entity_by_name($entitydata['name']);

        // Create training in other main entity where user is not RCF.
        $entity2data = $this->get_entities_data()[1];
        // Init training data.
        $training2data = $this->get_training_data('NEWTRAINING2', $entity2data);
        // Create training.
        \local_mentor_core\training_api::create_training($training2data);
        $entity2 = \local_mentor_core\entity_api::get_entity_by_name($entity2data['name']);

        // Create 2 others trainings in 2 sub entities link with first main entity.
        $entity3data             = $this->get_entities_data()[2];
        $entity3data['parentid'] = $entity->id;
        // Init training data.
        $training3data = $this->get_training_data('NEWTRAINING3', $entity3data);
        // Create training.
        \local_mentor_core\training_api::create_training($training3data);
        $entity4data             = $this->get_entities_data()[3];
        $entity4data['parentid'] = $entity->id;
        // Init training data.
        $training4data = $this->get_training_data('NEWTRAINING4', $entity4data);
        // Create training.
        \local_mentor_core\training_api::create_training($training4data);

        $newuser = self::getDataGenerator()->create_user();

        self::setUser($newuser);

        // Check if user not manage training.
        self::assertCount(0, local_mentor_core\training_api::get_user_training_courses());

        self::setAdminUser();

        \local_mentor_core\profile_api::role_assign('respformation', $newuser->id, $entity->get_context());

        self::setUser($newuser);

        // Check if have 4 trainings (first and second main entity session sum).
        self::assertCount(3, local_mentor_core\training_api::get_trainings_by_entity($entity->id));
        self::assertCount(1, local_mentor_core\training_api::get_trainings_by_entity($entity2->id));
        // Check if user manage 1 trainings.
        self::assertCount(1, local_mentor_core\training_api::get_user_training_courses());

        self::setAdminUser();

        self::resetAllData();
    }

    /**
     * Test of list session by entity with role RCF
     *
     * @throws ReflectionException
     */
    public function test_get_user_session_courses_rcf_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        // Create session in main entity.
        $entitydata = $this->get_entities_data()[0];
        // Init training data.
        $trainingdata = $this->get_training_data('NEWTRAINING', $entitydata);
        // Create training.
        $training = \local_mentor_core\training_api::create_training($trainingdata);
        $entity   = \local_mentor_core\entity_api::get_entity_by_name($entitydata['name']);
        \local_mentor_core\session_api::create_session($training->id, 'NEWSESSION1', true);

        // Create session in other main entity where user is not RCF.
        $entity2data = $this->get_entities_data()[1];
        // Init training data.
        $training2data = $this->get_training_data('NEWTRAINING2', $entity2data);
        // Create training.
        $training2 = \local_mentor_core\training_api::create_training($training2data);
        $entity2   = \local_mentor_core\entity_api::get_entity_by_name($entity2data['name']);
        \local_mentor_core\session_api::create_session($training2->id, 'NEWSESSION2', true);

        $newuser = self::getDataGenerator()->create_user();

        self::setUser($newuser);

        // Check if user not manage training.
        self::assertCount(0, local_mentor_core\session_api::get_user_session_courses());

        self::setAdminUser();

        \local_mentor_core\profile_api::role_assign('respformation', $newuser->id, $entity->get_context());

        // Check if have 1 training.
        $data           = new stdClass();
        $data->entityid = $entity->id;
        $data->status   = null;
        $data->dateto   = null;
        $data->datefrom = null;
        $data->draw     = 1;
        $data->length   = 10;
        $data->start    = 0;
        $data->order    = ['column' => 0, 'dir' => 'asc'];
        $data->search   = ['value' => '', 'regex' => 'false'];
        self::assertCount(1, local_mentor_core\session_api::get_sessions_by_entity($data));
        $data->entityid = $entity2->id;
        self::assertCount(1, local_mentor_core\session_api::get_sessions_by_entity($data));

        self::setUser($newuser);

        // Check if user manage 1 training.
        self::assertCount(1, local_mentor_core\session_api::get_user_session_courses());

        self::setAdminUser();

        self::resetAllData();
    }

    /**
     * Test of list session by entity with sub entity with role RCF
     *
     * @throws ReflectionException
     */
    public function test_get_user_session_courses_with_sub_entity_rcf_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        // Create session in main entity.
        $entitydata = $this->get_entities_data()[0];
        // Init training data.
        $trainingdata = $this->get_training_data('NEWTRAINING', $entitydata);
        // Create training.
        $training = \local_mentor_core\training_api::create_training($trainingdata);
        $entity   = \local_mentor_core\entity_api::get_entity_by_name($entitydata['name']);
        \local_mentor_core\session_api::create_session($training->id, 'NEWSESSION1', true);

        // Create session in other main entity where user is not RCF.
        $entity2data = $this->get_entities_data()[1];
        // Init training data.
        $training2data = $this->get_training_data('NEWTRAINING2', $entity2data);
        // Create training.
        $training2 = \local_mentor_core\training_api::create_training($training2data);
        $entity2   = \local_mentor_core\entity_api::get_entity_by_name($entity2data['name']);
        \local_mentor_core\session_api::create_session($training2->id, 'NEWSESSION2', true);

        // Create 2 others session in 2 sub entities link with first main entity.
        // First sub entity.
        $entity3data             = $this->get_entities_data()[2];
        $entity3data['parentid'] = $entity->id;
        // Init training data.
        $training3data = $this->get_training_data('NEWTRAINING3', $entity3data);
        // Create training.
        $training3 = \local_mentor_core\training_api::create_training($training3data);
        \local_mentor_core\session_api::create_session($training3->id, 'NEWSESSION3', true);
        // Second sub entity.
        $entity4data             = $this->get_entities_data()[3];
        $entity4data['parentid'] = $entity->id;
        // Init training data.
        $training4data = $this->get_training_data('NEWTRAINING4', $entity4data);
        // Create training.
        $training4 = \local_mentor_core\training_api::create_training($training4data);
        \local_mentor_core\session_api::create_session($training4->id, 'NEWSESSION4', true);

        $newuser = self::getDataGenerator()->create_user();

        self::setUser($newuser);

        // Check if user not manage training.
        self::assertCount(0, local_mentor_core\session_api::get_user_session_courses());

        self::setAdminUser();

        \local_mentor_core\profile_api::role_assign('respformation', $newuser->id, $entity->get_context());

        // Check if have 3 trainings.
        $data           = new stdClass();
        $data->entityid = $entity->id;
        $data->status   = null;
        $data->dateto   = null;
        $data->datefrom = null;
        $data->draw     = 1;
        $data->length   = 10;
        $data->start    = 0;
        $data->order    = ['column' => 0, 'dir' => 'asc'];
        $data->search   = ['value' => '', 'regex' => 'false'];
        self::assertCount(3, local_mentor_core\session_api::get_sessions_by_entity($data));
        $data->entityid = $entity2->id;
        self::assertCount(1, local_mentor_core\session_api::get_sessions_by_entity($data));

        self::setUser($newuser);

        // Check if user manage 3 trainings.
        self::assertCount(3, local_mentor_core\session_api::get_user_session_courses());

        self::setAdminUser();

        self::resetAllData();
    }
}
