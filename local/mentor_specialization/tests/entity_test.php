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
 * Test cases for entity API
 *
 * @package    local_mentor_specialization
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     rcolet <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_mentor_core\specialization;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/local/mentor_core/api/entity.php');
require_once($CFG->dirroot . '/local/mentor_core/classes/model/entity.php');

class local_mentor_specialization_entity_testcase extends advanced_testcase {

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
        ];
    }

    /**
     * Test of the creation of an entity
     *
     * @covers \local_mentor_core\entity_api::create_entity
     * @covers \local_mentor_core\entity_api::entity_exists
     * @covers \local_mentor_core\entity_api::get_entity
     * @covers \local_mentor_specialization\mentor_entity::assign_manager
     * @covers \local_mentor_specialization\mentor_entity::update
     * @covers \local_mentor_specialization\mentor_entity::create_edadmin_courses_if_missing
     * @covers \local_mentor_specialization\mentor_entity::get_context
     * @covers \local_mentor_specialization\mentor_entity::get_name
     * @covers \local_mentor_specialization\mentor_entity::get_edadmin_courses
     * @covers \local_mentor_specialization\mentor_entity::get_cohort
     */
    public function test_create_entity_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $entitydata = $this->get_entities_data()[0];

        // Test standard Entity creation.
        try {
            $entityid = \local_mentor_core\entity_api::create_entity($entitydata);
        } catch (\Exception $e) {
            // Failed if the name of this entity is already in use.
            self::fail($e->getMessage());
        }

        // Test if we have received an identifier.
        self::assertIsInt($entityid);

        self::resetAllData();
    }

    /**
     * Test the entity update
     *
     * @covers \local_mentor_core\entity_api::update_entity
     * @covers \local_mentor_core\entity_api::create_entity
     * @covers \local_mentor_core\entity_api::get_entity
     * @covers \local_mentor_core\entity::rename_cohort
     * @covers \local_mentor_specialization\mentor_entity::update
     * @covers \local_mentor_specialization\mentor_entity::get_context
     * @covers \local_mentor_specialization\mentor_entity::get_name
     * @covers \local_mentor_specialization\mentor_entity::get_edadmin_courses
     * @covers \local_mentor_specialization\mentor_entity::get_edadmin_courses
     * @covers \local_mentor_specialization\mentor_entity::get_logo
     *
     * @throws moodle_exception
     */
    public function test_update_entity_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $entitydata = $this->get_entities_data()[0];

        // Test standard Entity creation.
        try {
            $entityid = \local_mentor_core\entity_api::create_entity($entitydata);
        } catch (\Exception $e) {
            // Failed if the name of this entity is already in use.
            self::fail($e->getMessage());
        }

        $entity = \local_mentor_core\entity_api::get_entity($entityid);

        $newdata       = new stdClass();
        $newdata->name = 'Name updated';

        try {
            $result = $entity->update($newdata);
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        // Check if the update is ok.
        self::assertTrue($result);

        self::resetAllData();
    }

    /**
     * Test get entity
     *
     * @covers \local_mentor_core\entity_api::get_entity
     * @covers \local_mentor_core\entity_api::create_entity
     * @covers \local_mentor_core\specialization::__construct
     * @covers \local_mentor_core\specialization::get_instance
     * @covers \local_mentor_core\specialization::get_specialization
     * @covers \local_mentor_specialization\mentor_entity::__construct
     * @covers \local_mentor_specialization\mentor_entity::get_regions_id
     */
    public function test_get_entity_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $entitydata = $this->get_entities_data()[0];

        // Test standard Entity creation.
        try {
            $entityid = \local_mentor_core\entity_api::create_entity($entitydata);
        } catch (\Exception $e) {
            // Failed if the name of this entity is already in use.
            self::fail($e->getMessage());
        }

        // Test get entity.
        try {
            $entity = \local_mentor_core\entity_api::get_entity($entityid);
        } catch (\Exception $e) {
            // Failed if entity not exist.
            self::assertInstanceOf('moodle_exception', $e);
        }

        self::assertEquals($entityid, $entity->id);

        self::resetAllData();
    }

    /**
     * Test get entity by name
     *
     * @covers \local_mentor_core\entity_api::get_entity_by_name
     * @covers \local_mentor_core\entity_api::get_entity
     * @covers \local_mentor_core\entity_api::create_entity
     */
    public function test_get_entity_by_name_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $entitydata = $this->get_entities_data()[0];

        // Test standard Entity creation.
        try {
            $entityid = \local_mentor_core\entity_api::create_entity($entitydata);
        } catch (\Exception $e) {
            // Failed if the name of this entity is already in use.
            self::fail($e->getMessage());
        }

        // Test get entity.
        try {
            $entity = \local_mentor_core\entity_api::get_entity_by_name($entitydata['name']);
        } catch (\Exception $e) {
            // Failed if entity not exist.
            self::assertInstanceOf('moodle_exception', $e);
        }

        self::assertIsObject($entity);
        self::assertEquals($entitydata['name'], $entity->name);

        self::resetAllData();
    }

    /**
     * Test get all entities function
     *
     * @covers  \local_mentor_core\entity_api::get_all_entities
     * @covers  \local_mentor_core\entity_api::create_entity
     * @covers  \local_mentor_core\entity_api::get_entity
     */
    public function test_get_all_entities_ok() {
        global $DB;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $DB->delete_records('course_categories');

        $entitiesdata = $this->get_entities_data();

        // Create all entities.
        foreach ($entitiesdata as $entitydata) {
            // Test standard Entity creation.
            try {
                $entityid = \local_mentor_core\entity_api::create_entity($entitydata);
            } catch (\Exception $e) {
                // Failed if the name of this entity is already in use.
                self::fail($e->getMessage());
            }
        }

        // Get all entities.
        try {
            $allentities = \local_mentor_core\entity_api::get_all_entities(true, [], true);
        } catch (\Exception $e) {
            // Moodle exception.
            self::fail($e->getMessage());
        }

        // Check if all entities had been created.
        self::assertEquals(count($entitiesdata), count($allentities));

        self::resetAllData();
    }

    /**
     * Test if entity exist
     *
     * @covers  \local_mentor_core\entity_api::entity_exists
     * @covers  \local_mentor_core\entity_api::create_entity
     */
    public function test_entity_exists_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $entitydata = $this->get_entities_data()[0];

        // Test standard Entity creation.
        try {
            $entityid = \local_mentor_core\entity_api::create_entity($entitydata);
        } catch (\Exception $e) {
            // Failed if the name of this entity is already in use.
            self::fail($e->getMessage());
        }

        // Check if entity exist after its creation.
        try {
            self::assertTrue(\local_mentor_core\entity_api::entity_exists($entitydata['name']));
        } catch (\moodle_exception $e) {
            self::fail($e->getMessage());
        }

        self::resetAllData();
    }

    /**
     * Test of effect of a user as manager of an entity
     *
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     *
     * @covers  \local_mentor_core\entity_api::get_managed_entities
     * @covers  \local_mentor_core\entity_api::get_all_entities
     * @covers  \local_mentor_core\entity_api::create_entity
     * @covers  \local_mentor_core\entity_api::get_entity
     * @covers  \local_mentor_specialization\mentor_entity::is_manager
     * @covers  \local_mentor_specialization\mentor_entity::get_members
     * @covers  \local_mentor_specialization\mentor_entity::get_cohort
     * @covers  \local_mentor_specialization\mentor_entity::assign_manager
     */
    public function test_get_managed_entities_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $newuser = self::getDataGenerator()->create_user();

        $entitydata           = $this->get_entities_data()[0];
        $entitydata['userid'] = 0; // Remove the manager.

        // Test standard Entity creation.
        try {
            $entityid = \local_mentor_core\entity_api::create_entity($entitydata);
        } catch (\Exception $e) {
            // Failed if the name of this entity is already in use.
            self::fail($e->getMessage());
        }

        $managedentities = \local_mentor_core\entity_api::get_managed_entities($newuser->id);

        // The user must not manage any entity at this point.
        self::assertCount(0, $managedentities);

        try {
            $entity = \local_mentor_core\entity_api::get_entity($entityid);
        } catch (\Exception $e) {
            // Fail if entity not exist.
            self::fail($e->getMessage());
        }

        // No members exist.
        self::assertCount(0, $entity->get_members());

        // Standard test assign user.
        // If return true, the user has been assigned.
        try {
            self::assertTrue($entity->assign_manager($newuser->id));
        } catch (\Exception $e) {
            // Fail if user not exist.
            self::fail($e->getMessage());
        }

        // One members exist.
        self::assertCount(1, $entity->get_members());

        $managedentities = \local_mentor_core\entity_api::get_managed_entities($newuser->id);

        // Check if the user can manage the entity.
        self::assertCount(1, $managedentities);

        self::resetAllData();
    }

    /**
     * Test get entities list
     *
     * @covers  \local_mentor_core\entity_api::get_entities_list
     * @covers  \local_mentor_core\entity_api::get_all_entities
     * @covers  \local_mentor_core\entity_api::get_entity
     * @covers  \local_mentor_core\entity_api::create_entity
     */
    public function test_get_entities_list_ok() {
        global $DB;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $DB->delete_records('course_categories');

        $entitiesdata = $this->get_entities_data();

        $list = [];

        // Create all entities.
        foreach ($entitiesdata as $entitydata) {
            // Test standard Entity creation.
            try {
                $entityid = \local_mentor_core\entity_api::create_entity($entitydata);
                $list[]   = $entitydata['name'];
            } catch (\Exception $e) {
                // Failed if the name of this entity is already in use.
                self::fail($e->getMessage());
            }
        }

        try {
            self::assertEquals(
                implode("\n", $list),
                \local_mentor_core\entity_api::get_entities_list(true, true)
            );
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        self::resetAllData();
    }

    /**
     * Test of the no creation of an entity
     *
     * @covers  \local_mentor_core\entity_api::create_entity
     */
    public function test_create_entity_nok() {
        $this->resetAfterTest();
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $entitydata = $this->get_entities_data()[0];

        // Test with no entity name.
        try {
            $entity = \local_mentor_core\entity_api::create_entity($entitydata);
        } catch (\Exception $e) {
            // Failed if the name of this entity is already in use.
            self::assertInstanceOf('moodle_exception', $e);
        }

        // Test duplicate entity.
        try {
            $entity = \local_mentor_core\entity_api::create_entity($entitydata);
        } catch (\Exception $e) {
            // Failed if the name of this entity is already in use.
            self::assertInstanceOf('moodle_exception', $e);
        }

        // Test with an empty entity name.
        try {
            $entity = \local_mentor_core\entity_api::create_entity(['name' => '']);
        } catch (\Exception $e) {
            // Failed if the name of this entity is already in use.
            self::assertInstanceOf('moodle_exception', $e);
        }

        self::resetAllData();
    }

    /**
     * Test get entity fail
     *
     * @covers \local_mentor_core\entity_api::get_entity
     * @covers \local_mentor_core\specialization::__construct
     * @covers \local_mentor_core\specialization::get_instance
     * @covers \local_mentor_core\specialization::get_specialization
     * @covers \local_mentor_specialization\mentor_specialization::get_specialization
     * @covers \local_mentor_specialization\mentor_specialization::get_entity
     * @covers \local_mentor_specialization\mentor_entity::__construct
     */
    public function test_get_entity_nok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $falseentityid = 10;

        // Test entity not found.
        try {
            $entity = \local_mentor_core\entity_api::get_entity($falseentityid);
            self::fail('Not possible exist');
        } catch (\Exception $e) {
            // Entity not exist.
            self::assertInstanceOf('dml_missing_record_exception', $e);
        }

        self::resetAllData();
    }

    /**
     * Test get entity by name not exist
     *
     * @covers \local_mentor_core\entity_api::get_entity_by_name
     * @covers \local_mentor_core\entity_api::get_entity
     */
    public function test_get_entity_by_name_nok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $existingentity = 'Entity Inexistant Test Name ';

        try {
            \local_mentor_core\entity_api::get_entity_by_name($existingentity);
        } catch (\Exception $e) {
            self::assertInstanceOf('dml_missing_record_exception', $e);
        }

        self::resetAllData();
    }

    /**
     * Test if entity not exist
     *
     * @covers  \local_mentor_core\entity_api::entity_exists
     */
    public function test_entity_exists_nok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        // Check if entity not exist.
        self::assertFalse(\local_mentor_core\entity_api::entity_exists('Entity Inexistant Test Name'));

        self::resetAllData();
    }

    /**
     * Test of assigning an unknown user as manager of an entity
     *
     * @covers \local_mentor_core\entity_api::get_managed_entities
     * @covers \local_mentor_core\entity_api::get_all_entities
     * @covers \local_mentor_core\entity_api::create_entity
     * @covers \local_mentor_core\entity_api::get_entity
     * @covers \local_mentor_specialization\mentor_entity::is_manager
     * @covers \local_mentor_specialization\mentor_entity::get_members
     * @covers \local_mentor_specialization\mentor_entity::assign_manager
     */
    public function test_get_managed_entities_nok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $this->init_database();

        // Userid=3 is an unregistered user.
        $userid = 3;

        try {
            $entityid = \local_mentor_core\entity_api::create_entity(['name' => 'Test managed entity', 'shortname' => 'TME']);
        } catch (\Exception $e) {
            // Failed if the name of this entity is already in use.
            self::fail($e->getMessage());
        }

        try {
            $entity = \local_mentor_core\entity_api::get_entity($entityid);
        } catch (\Exception $e) {
            // Fail if entity not exist.
            self::fail($e->getMessage());
        }

        self::assertCount(0, $entity->get_members());

        // Standard test assign user.
        // If return true, the user has been assigned.
        try {
            $entity->assign_manager($userid);
        } catch (\Exception $e) {
            // Failled because user not exist.
            self::assertInstanceOf('moodle_exception', $e);
        }

        self::assertCount(0, $entity->get_members());

        self::resetAllData();
    }

    /**
     * Test get entity form
     *
     * @covers \local_mentor_core\entity_api::get_entity_form
     * @covers \local_mentor_core\entity_api::create_entity
     * @covers \local_mentor_core\entity_api::get_entity
     * @covers \local_mentor_core\specialization::__construct
     * @covers \local_mentor_core\specialization::get_instance
     * @covers \local_mentor_core\specialization::get_specialization
     * @covers \local_mentor_specialization\mentor_specialization::get_specialization
     * @covers \local_mentor_specialization\mentor_specialization::get_entity_form
     * @covers \local_mentor_specialization\entity_form::definition
     * @covers \local_mentor_specialization\mentor_entity::create_edadmin_courses_if_missing
     * @covers \local_mentor_specialization\mentor_entity::get_edadmin_courses
     */
    public function test_get_entity_form_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $this->init_database();

        try {
            $entityid = \local_mentor_core\entity_api::create_entity(['name' => 'Test managed entity', 'shortname' => 'TME']);
            $entity   = \local_mentor_core\entity_api::get_entity($entityid);
            $entity->create_edadmin_courses_if_missing();
            $entitycourse = $entity->get_edadmin_courses('entities');
        } catch (\Exception $e) {
            // Failed if the name of this entity is already in use.
            self::fail($e->getMessage());
        }

        $url = new moodle_url('/course/view.php', ['id' => $entitycourse['id']]);

        $entityform = \local_mentor_core\entity_api::get_entity_form($url->out(), $entity->id);

        // Check if the returned value is an object.
        self::assertIsObject($entityform);

        self::resetAllData();
    }

    /**
     * Test get new entity form
     *
     * @covers \local_mentor_core\entity_api::get_new_entity_form
     * @covers \local_mentor_core\specialization::__construct
     * @covers \local_mentor_core\specialization::get_instance
     * @covers \local_mentor_core\specialization::get_specialization
     * @covers \local_mentor_specialization\mentor_specialization::get_specialization
     * @covers \local_mentor_specialization\mentor_specialization::get_entity_form_fields
     */
    public function test_get_new_entity_form_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();
        $entityform = \local_mentor_core\entity_api::get_new_entity_form();
        self::assertIsString($entityform);
        self::resetAllData();
    }

    /**
     * Test get user entities
     *
     * @covers \local_mentor_core\entity_api::get_user_entities
     * @covers \local_mentor_core\entity_api::get_entity
     * @covers \local_mentor_core\entity_api::create_entity
     * @covers \local_mentor_specialization\mentor_entity::add_member
     */
    public function test_get_user_entities_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $newuser = self::getDataGenerator()->create_user();

        // The user must not be a member of any entity.
        $userentities = \local_mentor_core\entity_api::get_user_entities($newuser->id);
        self::assertEquals(0, count($userentities));

        $entitiesdata = $this->get_entities_data();

        // Create all entities.
        foreach ($entitiesdata as $entitydata) {
            // Test standard Entity creation.
            try {
                $entityid = \local_mentor_core\entity_api::create_entity($entitydata);
                $entity   = \local_mentor_core\entity_api::get_entity($entityid);

                $entity->add_member($newuser);

            } catch (\Exception $e) {
                // Failed if the name of this entity is already in use.
                self::fail($e->getMessage());
            }
        }

        // Check if the user is a member of each entity.
        $userentities = \local_mentor_core\entity_api::get_user_entities($newuser->id);
        self::assertEquals(count($entitiesdata), count($userentities));

        self::resetAllData();
    }
}
