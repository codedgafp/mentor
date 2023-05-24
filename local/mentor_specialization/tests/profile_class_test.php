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
 * Test cases for class mentor_profile
 *
 * @package    local_mentor_specialization
 * @copyright  2021 Edunao SAS (contact@edunao.com)
 * @author     rcolet <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/local/mentor_core/api/entity.php');
require_once($CFG->dirroot . '/local/mentor_core/api/profile.php');
require_once($CFG->dirroot . '/local/mentor_core/classes/model/profile.php');
require_once($CFG->dirroot . '/lib/enrollib.php');

class local_mentor_specialization_profile_class_testcase extends advanced_testcase {

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
     * Test sync entities
     *
     * @covers \local_mentor_specialization\mentor_profile::sync_entities
     */
    public function test_sync_entities_ok() {
        global $DB;
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $db = \local_mentor_core\database_interface::get_instance();

        $lastname = 'lastname';
        $firstname = 'firstname';
        $email = 'user@gouv.fr';
        $auth = 'manual';

        // Create user.
        self::assertTrue(\local_mentor_core\profile_api::create_and_add_user($lastname, $firstname, $email, null, [], null, $auth));

        // Get user.
        $profiledata = $db->get_user_by_email($email);
        $profile = \local_mentor_core\profile_api::get_profile($profiledata->id);

        // Nothing to synchronize.
        self::assertNull($profile->sync_entities());

        $lastname2 = 'lastname2';
        $firstname2 = 'firstname2';
        $email2 = 'user2@gouv.fr';

        $entity1 = \local_mentor_core\entity_api::get_entity(1);

        self::assertCount(0, $entity1->get_members());

        // Create user.
        self::assertTrue(\local_mentor_core\profile_api::create_and_add_user($lastname2, $firstname2, $email2, 1, [], null, $auth));

        // Get user.
        $profiledata2 = $db->get_user_by_email($email2);
        $profile2 = \local_mentor_core\profile_api::get_profile($profiledata2->id);

        // Entity cohort sync.
        self::assertTrue($profile2->sync_entities());
        self::assertCount(1, $entity1->get_members());

        $entity2id = \local_mentor_core\entity_api::create_entity([
            'name' => 'Entity2',
            'shortname' => 'Entity2',
            'regions' => [3]
        ]);
        $entity2 = \local_mentor_core\entity_api::get_entity($entity2id);

        $entity3id = \local_mentor_core\entity_api::create_entity([
            'name' => 'Entity3',
            'shortname' => 'Entity3'
        ]);
        $entity3 = \local_mentor_core\entity_api::get_entity($entity3id);

        self::assertCount(0, $entity2->get_members());
        self::assertCount(0, $entity3->get_members());

        // Change main entity and region profile.
        $mainentityuserfield = $DB->get_record('user_info_field', array('shortname' => 'mainentity'));
        $mainentityfielddata = $DB->get_record('user_info_data', array(
            'userid' => $profile2->id, 'fieldid' =>
                $mainentityuserfield->id
        ));
        $mainentityfielddata->data = 'Entity3';
        $DB->update_record('user_info_data', $mainentityfielddata);

        $regionuserfield = $DB->get_record('user_info_field', array('shortname' => 'region'));
        $regionlist = explode("\n", $regionuserfield->param1);
        $regionfielddata = new stdClass();
        $regionfielddata->userid = $profile2->id;
        $regionfielddata->fieldid = $regionuserfield->id;
        $regionfielddata->data = $regionlist[2];
        $regionfielddata->dataformat = 0;
        $DB->insert_record('user_info_data', $regionfielddata);

        // Refresh data.
        $profile2 = \local_mentor_core\profile_api::get_profile($profiledata2->id);
        $profile2->sync_entities();

        self::assertCount(1, $entity2->get_members());
        self::assertCount(1, $entity3->get_members());

        self::resetAllData();
    }

    /**
     * Test sync entities with secondary entities
     *
     * @covers \local_mentor_specialization\mentor_profile::sync_entities
     */
    public function test_sync_entities_ok_secondary_entities() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        // Setting user data.
        $lastname = 'lastname';
        $firstname = 'firstname';
        $email = 'user@gouv.fr';
        $auth = 'manual';

        // Create main and secondary entities.
        // Main.
        $entityid = \local_mentor_core\entity_api::create_entity(['name' => 'Entity', 'shortname' => 'Entity']);
        $entity = \local_mentor_core\entity_api::get_entity($entityid);
        // Secondary.
        $entity2id = \local_mentor_core\entity_api::create_entity(['name' => 'Entity2', 'shortname' => 'Entity2']);
        $entity2 = \local_mentor_core\entity_api::get_entity($entity2id);
        $entity3id = \local_mentor_core\entity_api::create_entity(['name' => 'Entity3', 'shortname' => 'Entity3']);
        $entity3 = \local_mentor_core\entity_api::get_entity($entity3id);

        // Check if entities does not have members.
        self::assertCount(0, $entity->get_members());
        self::assertCount(0, $entity2->get_members());
        self::assertCount(0, $entity3->get_members());

        // Create user.
        self::assertTrue(\local_mentor_core\profile_api::create_and_add_user($lastname, $firstname, $email, $entity, [$entity2],
            null, $auth));

        // Check if user has member to correctly entities.
        self::assertCount(1, $entity->get_members());
        self::assertCount(1, $entity2->get_members());
        self::assertCount(0, $entity3->get_members());

        self::resetAllData();
    }

    /**
     * Test get secondary entities ok
     *
     * @covers \local_mentor_specialization\mentor_profile::get_secondary_entities
     */
    public function test_get_secondary_entities_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $db = \local_mentor_core\database_interface::get_instance();

        // Setting user data.
        $lastname = 'lastname';
        $firstname = 'firstname';
        $email = 'user@gouv.fr';
        $auth = 'manual';

        // Create main and secondary entities.
        // Main.
        $entityid = \local_mentor_core\entity_api::create_entity(['name' => 'Entity', 'shortname' => 'Entity']);
        $entity = \local_mentor_core\entity_api::get_entity($entityid);
        // Secondary.
        $entity2id = \local_mentor_core\entity_api::create_entity(['name' => 'Entity2', 'shortname' => 'Entity2']);
        $entity2 = \local_mentor_core\entity_api::get_entity($entity2id);

        // Create user.
        \local_mentor_core\profile_api::create_and_add_user($lastname, $firstname, $email, $entity, [$entity2], null, $auth);
        $user = $db->get_user_by_email('user@gouv.fr');
        $profile = \local_mentor_core\profile_api::get_profile($user->id);

        // Get secondary entities user.
        $secondaryentities = $profile->get_secondary_entities();

        // Check if correctly entity add to seconday entities user.
        self::assertCount(1, $secondaryentities);
        self::assertEquals($entity2id, current($secondaryentities)->id);

        self::resetAllData();
    }

    /**
     * Test get secondary entities ok
     * secondary entities empty
     *
     * @covers \local_mentor_specialization\mentor_profile::get_secondary_entities
     */
    public function test_get_secondary_entities_ok_secondary_entities_empty() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $db = \local_mentor_core\database_interface::get_instance();

        // Setting user data.
        $lastname = 'lastname';
        $firstname = 'firstname';
        $email = 'user@gouv.fr';
        $auth = 'manual';

        // Create main entity.
        $entityid = \local_mentor_core\entity_api::create_entity(['name' => 'Entity', 'shortname' => 'Entity']);
        $entity = \local_mentor_core\entity_api::get_entity($entityid);

        // Create user.
        \local_mentor_core\profile_api::create_and_add_user($lastname, $firstname, $email, $entity, [], null, $auth);
        $user = $db->get_user_by_email('user@gouv.fr');
        $profile = \local_mentor_core\profile_api::get_profile($user->id);

        // Get secondary entities user.
        $secondaryentities = $profile->get_secondary_entities();

        // Check if user does not have secondary entity.
        self::assertCount(0, $secondaryentities);

        self::resetAllData();
    }

    /**
     * Test get secondary entities ok
     * secondary entities field return with DB interface.
     *
     * @covers \local_mentor_specialization\mentor_profile::get_secondary_entities
     */
    public function test_get_secondary_entities_ok_secondary_entities_to_db_interface() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $db = \local_mentor_core\database_interface::get_instance();

        // Setting user data.
        $lastname = 'lastname';
        $firstname = 'firstname';
        $email = 'user@gouv.fr';
        $auth = 'manual';

        // Create main and secondary entities.
        // Main.
        $entityid = \local_mentor_core\entity_api::create_entity(['name' => 'Entity', 'shortname' => 'Entity']);
        $entity = \local_mentor_core\entity_api::get_entity($entityid);
        // Secondary.
        $entity2id = \local_mentor_core\entity_api::create_entity(['name' => 'Entity2', 'shortname' => 'Entity2']);
        $entity2 = \local_mentor_core\entity_api::get_entity($entity2id);

        // Create user.
        \local_mentor_core\profile_api::create_and_add_user($lastname, $firstname, $email, $entity, [], null, $auth);
        $user = $db->get_user_by_email('user@gouv.fr');
        $user->secondaryentities = $entity2->name;
        $profile = \local_mentor_core\profile_api::get_profile($user->id);

        // Create database interface Mock.
        $dbinterfacemock = $this->getMockBuilder('\local_mentor_core\database_interface')
            ->setMethods(['get_user_by_id'])
            ->disableOriginalConstructor()
            ->getMock();

        // Return one time user data when call get_user_by_id function.
        $dbinterfacemock->expects($this->any())
            ->method('get_user_by_id')
            ->will($this->returnValue($user));

        // Replace dbinterface data to profile object with mock.
        $reflection = new ReflectionClass($profile);
        $reflectionproperty = $reflection->getProperty('dbinterface');
        $reflectionproperty->setAccessible(true);
        $reflectionproperty->setValue($profile, $dbinterfacemock);

        // Get secondary entities.
        $secondaryentities = $profile->get_secondary_entities();

        // Check if correctly entity add to secondary entities user.
        self::assertCount(1, $secondaryentities);
        self::assertEquals($entity2id, current($secondaryentities)->id);

        self::resetAllData();
    }

    /**
     * Test get secondary entities ok
     * secondary entities field return with DB interface.
     * empty data.
     *
     * @covers \local_mentor_specialization\mentor_profile::get_secondary_entities
     */
    public function test_get_secondary_entities_ok_secondary_entities_to_db_interface_empty() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $db = \local_mentor_core\database_interface::get_instance();

        // Setting user data.
        $lastname = 'lastname';
        $firstname = 'firstname';
        $email = 'user@gouv.fr';
        $auth = 'manual';

        // Create main entity.
        $entityid = \local_mentor_core\entity_api::create_entity(['name' => 'Entity', 'shortname' => 'Entity']);
        $entity = \local_mentor_core\entity_api::get_entity($entityid);

        // Create user.
        \local_mentor_core\profile_api::create_and_add_user($lastname, $firstname, $email, $entity, [], null, $auth);
        $user = $db->get_user_by_email('user@gouv.fr');
        $user->secondaryentities = '';
        $profile = \local_mentor_core\profile_api::get_profile($user->id);

        // Create database interface Mock.
        $dbinterfacemock = $this->getMockBuilder('\local_mentor_core\database_interface')
            ->setMethods(['get_user_by_id'])
            ->disableOriginalConstructor()
            ->getMock();

        // Return one time user data when call get_user_by_id function.
        $dbinterfacemock->expects($this->any())
            ->method('get_user_by_id')
            ->will($this->returnValue($user));

        // Replace dbinterface data to profile object with mock.
        $reflection = new ReflectionClass($profile);
        $reflectionproperty = $reflection->getProperty('dbinterface');
        $reflectionproperty->setAccessible(true);
        $reflectionproperty->setValue($profile, $dbinterfacemock);

        // Get secondary entities.
        $secondaryentities = $profile->get_secondary_entities();

        // Check if user does not have secondary entities.
        self::assertCount(0, $secondaryentities);

        self::resetAllData();
    }

    /**
     * Test get secondary entities ok
     * secondary entities not exist to fields.
     *
     * @covers \local_mentor_specialization\mentor_profile::get_secondary_entities
     */
    public function test_get_secondary_entities_ok_secondary_entities_not_exist_to_fields() {
        global $DB;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $db = \local_mentor_core\database_interface::get_instance();

        // Setting user data.
        $lastname = 'lastname';
        $firstname = 'firstname';
        $email = 'user@gouv.fr';
        $auth = 'manual';

        // Create main entity.
        $entityid = \local_mentor_core\entity_api::create_entity(['name' => 'Entity', 'shortname' => 'Entity']);
        $entity = \local_mentor_core\entity_api::get_entity($entityid);

        $DB->delete_records('user_info_field', array('shortname' => 'secondaryentities'));

        // Create user.
        \local_mentor_core\profile_api::create_and_add_user($lastname, $firstname, $email, $entity, [], null, $auth);
        $user = $db->get_user_by_email('user@gouv.fr');
        $user->secondaryentities = '';
        $profile = \local_mentor_core\profile_api::get_profile($user->id);

        // Get secondary entities.
        $secondaryentities = $profile->get_secondary_entities();

        // Check if user does not have secondary entities.
        self::assertCount(0, $secondaryentities);

        self::resetAllData();
    }

    /**
     * Test has secondary entity ok
     * One secondary entity
     *
     * @covers \local_mentor_specialization\mentor_profile::has_secondary_entity
     */
    public function test_has_secondary_entity_ok_one_secondary_entity() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $db = \local_mentor_core\database_interface::get_instance();

        // Setting user data.
        $lastname = 'lastname';
        $firstname = 'firstname';
        $email = 'user@gouv.fr';
        $auth = 'manual';

        // Create main entity.
        $entityid = \local_mentor_core\entity_api::create_entity(['name' => 'Entity', 'shortname' => 'Entity']);
        $entity = \local_mentor_core\entity_api::get_entity($entityid);

        // Create other entity.
        $entity2id = \local_mentor_core\entity_api::create_entity(['name' => 'Entity2', 'shortname' => 'Entity2']);

        // Create user.
        \local_mentor_core\profile_api::create_and_add_user($lastname, $firstname, $email, $entity, [$entity2id], null, $auth);
        $user = $db->get_user_by_email('user@gouv.fr');
        $user->secondaryentities = '';
        $profile = \local_mentor_core\profile_api::get_profile($user->id);

        self::assertTrue($profile->has_secondary_entity($entity2id));

        self::resetAllData();
    }

    /**
     * Test has secondary entity ok
     * multiple secondary entities
     *
     * @covers \local_mentor_specialization\mentor_profile::has_secondary_entity
     */
    public function test_has_secondary_entity_ok_multiple_secondary_entities() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $db = \local_mentor_core\database_interface::get_instance();

        // Setting user data.
        $lastname = 'lastname';
        $firstname = 'firstname';
        $email = 'user@gouv.fr';
        $auth = 'manual';

        // Create main entity.
        $entityid = \local_mentor_core\entity_api::create_entity(['name' => 'Entity', 'shortname' => 'Entity']);
        $entity = \local_mentor_core\entity_api::get_entity($entityid);

        // Create other entity.
        $entity2id = \local_mentor_core\entity_api::create_entity(['name' => 'Entity2', 'shortname' => 'Entity2']);

        // Create other entity.
        $entity3id = \local_mentor_core\entity_api::create_entity(['name' => 'Entity3', 'shortname' => 'Entity3']);

        // Create other entity.
        $entity4id = \local_mentor_core\entity_api::create_entity(['name' => 'Entity4', 'shortname' => 'Entity4']);

        // Create user.
        \local_mentor_core\profile_api::create_and_add_user(
            $lastname, $firstname, $email, $entity, [$entity2id, $entity4id], null, $auth
        );
        $user = $db->get_user_by_email('user@gouv.fr');
        $user->secondaryentities = '';
        $profile = \local_mentor_core\profile_api::get_profile($user->id);

        self::assertTrue($profile->has_secondary_entity($entity2id));
        self::assertTrue($profile->has_secondary_entity($entity4id));

        self::assertFalse($profile->has_secondary_entity($entity3id));

        self::resetAllData();
    }

    /**
     * Test has secondary entity not ok
     * User has not secondary entity
     *
     * @covers \local_mentor_specialization\mentor_profile::has_secondary_entity
     */
    public function test_has_secondary_entity_nok_has_not_secondary_entity() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $db = \local_mentor_core\database_interface::get_instance();

        // Setting user data.
        $lastname = 'lastname';
        $firstname = 'firstname';
        $email = 'user@gouv.fr';
        $auth = 'manual';

        // Create main entity.
        $entityid = \local_mentor_core\entity_api::create_entity(['name' => 'Entity', 'shortname' => 'Entity']);
        $entity = \local_mentor_core\entity_api::get_entity($entityid);

        // Create user.
        \local_mentor_core\profile_api::create_and_add_user($lastname, $firstname, $email, $entity, [], null, $auth);
        $user = $db->get_user_by_email('user@gouv.fr');
        $user->secondaryentities = '';
        $profile = \local_mentor_core\profile_api::get_profile($user->id);

        // Create other entity.
        $entity2id = \local_mentor_core\entity_api::create_entity(['name' => 'Entity2', 'shortname' => 'Entity2']);

        self::assertFalse($profile->has_secondary_entity($entity2id));

        self::resetAllData();
    }

    /**
     * Test has secondary entity not ok
     * User does not "Entity3" as a secondary entity
     *
     * @covers \local_mentor_specialization\mentor_profile::has_secondary_entity
     */
    public function test_has_secondary_entity_nok_does_not_secondary_entity() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $db = \local_mentor_core\database_interface::get_instance();

        // Setting user data.
        $lastname = 'lastname';
        $firstname = 'firstname';
        $email = 'user@gouv.fr';
        $auth = 'manual';

        // Create main entity.
        $entityid = \local_mentor_core\entity_api::create_entity(['name' => 'Entity', 'shortname' => 'Entity']);
        $entity = \local_mentor_core\entity_api::get_entity($entityid);

        // Create other entity.
        $entity2id = \local_mentor_core\entity_api::create_entity(['name' => 'Entity2', 'shortname' => 'Entity2']);

        // Create user.
        \local_mentor_core\profile_api::create_and_add_user($lastname, $firstname, $email, $entity, [$entity2id], null, $auth);
        $user = $db->get_user_by_email('user@gouv.fr');
        $user->secondaryentities = '';
        $profile = \local_mentor_core\profile_api::get_profile($user->id);

        // Create other entity.
        $entity3id = \local_mentor_core\entity_api::create_entity(['name' => 'Entity3', 'shortname' => 'Entity3']);

        self::assertFalse($profile->has_secondary_entity($entity3id));

        self::resetAllData();
    }

    /**
     * Test has secondary entity not ok
     * Secondary entities field does not exist
     *
     * @covers \local_mentor_specialization\mentor_profile::has_secondary_entity
     */
    public function test_has_secondary_entity_nok_field_does_not_exist() {
        global $DB;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $db = \local_mentor_core\database_interface::get_instance();

        // Setting user data.
        $lastname = 'lastname';
        $firstname = 'firstname';
        $email = 'user@gouv.fr';
        $auth = 'manual';

        // Create main entity.
        $entityid = \local_mentor_core\entity_api::create_entity(['name' => 'Entity', 'shortname' => 'Entity']);
        $entity = \local_mentor_core\entity_api::get_entity($entityid);

        // Create other entity.
        $entity2id = \local_mentor_core\entity_api::create_entity(['name' => 'Entity2', 'shortname' => 'Entity2']);

        // Create user.
        \local_mentor_core\profile_api::create_and_add_user($lastname, $firstname, $email, $entity, [$entity2id], null, $auth);
        $user = $db->get_user_by_email('user@gouv.fr');
        $user->secondaryentities = '';
        $profile = \local_mentor_core\profile_api::get_profile($user->id);

        $fieldid = $DB->get_field('user_info_field', 'id', array('shortname' => 'secondaryentities'));
        $DB->delete_records('user_info_data', array('userid' => $user->id, 'fieldid' => $fieldid));

        self::assertFalse($profile->has_secondary_entity($entity2id));

        self::resetAllData();
    }
}
