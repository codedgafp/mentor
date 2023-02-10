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
 * Test cases for plugin observer
 *
 * @package    local_mentor_specialization
 * @copyright  2022 Edunao SAS (contact@edunao.com)
 * @author     rcolet <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

class local_mentor_specialization_observer_testcase extends advanced_testcase {
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
     * Test assign reflocalnonediteur ok.
     *
     * @covers \local_mentor_specialization_observer::assign_reflocalnonediteur
     */
    public function test_assign_reflocalnonediteur_ok() {
        global $USER, $DB;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $referentlocal      = $DB->get_record('role', ['shortname' => 'referentlocal']);
        $reflocalnonediteur = $DB->get_record('role', ['shortname' => 'reflocalnonediteur']);

        $entityid       = \local_mentor_core\entity_api::create_entity(['name' => 'Entity', 'shortname' => 'Entity']);
        $entitycontext  = \context_coursecat::instance($entityid);
        $entity2id      = \local_mentor_core\entity_api::create_entity(['name' => 'Entity2', 'parentid' => $entityid]);
        $entity2context = \context_coursecat::instance($entity2id);

        self::assertFalse($DB->record_exists('role_assignments', array(
            'roleid'    => $reflocalnonediteur->id,
            'contextid' => $entitycontext->id,
            'userid'    => $USER->id
        )));

        $event = \core\event\role_assigned::create(array(
            'context'       => $entity2context,
            'relateduserid' => $USER->id,
            'objectid'      => $referentlocal->id,
            'other'         => array(
                'id'        => $entity2id,
                'component' => 0,
            )
        ));

        self::assertTrue(\local_mentor_specialization_observer::assign_reflocalnonediteur($event));

        self::assertTrue($DB->record_exists('role_assignments', array(
            'roleid'    => $reflocalnonediteur->id,
            'contextid' => $entitycontext->id,
            'userid'    => $USER->id
        )));

        self::resetAllData();
    }

    /**
     * Test assign reflocalnonediteur not ok.
     * Wrong context
     *
     * @covers \local_mentor_specialization_observer::assign_reflocalnonediteur
     */
    public function test_assign_reflocalnonediteur_nok_wrong_context() {
        global $USER;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $event = \core\event\role_assigned::create(array(
            'context' => \context_user::instance($USER->id),
            'other'   => array(
                'id'        => 0,
                'component' => 0,
            )
        ));

        self::assertFalse(\local_mentor_specialization_observer::assign_reflocalnonediteur($event));

        self::resetAllData();
    }

    /**
     * Test assign reflocalnonediteur not ok.
     * Is main entity
     *
     * @covers \local_mentor_specialization_observer::assign_reflocalnonediteur
     */
    public function test_assign_reflocalnonediteur_nok_main_entity() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $entityid = \local_mentor_core\entity_api::create_entity(['name' => 'Entity', 'shortname' => 'Entity']);

        $event = \core\event\role_assigned::create(array(
            'context'       => \context_coursecat::instance($entityid),
            'relateduserid' => 0,
            'objectid'      => 0,
            'other'         => array(
                'id'        => $entityid,
                'component' => 0,
            )
        ));

        self::assertFalse(\local_mentor_specialization_observer::assign_reflocalnonediteur($event));

        self::resetAllData();
    }

    /**
     * Test assign reflocalnonediteur not ok.
     * referentlocal not exist
     *
     * @covers \local_mentor_specialization_observer::assign_reflocalnonediteur
     */
    public function test_assign_reflocalnonediteur_nok_referentlocal_not_exist() {
        global $DB;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $DB->delete_records('role', ['shortname' => 'referentlocal']);

        $entityid  = \local_mentor_core\entity_api::create_entity(['name' => 'Entity', 'shortname' => 'Entity']);
        $entity2id = \local_mentor_core\entity_api::create_entity(['name' => 'Entity2', 'parentid' => $entityid]);

        $event = \core\event\role_assigned::create(array(
            'context'       => \context_coursecat::instance($entity2id),
            'relateduserid' => 0,
            'objectid'      => 0,
            'other'         => array(
                'id'        => $entity2id,
                'component' => 0,
            )
        ));

        self::assertFalse(\local_mentor_specialization_observer::assign_reflocalnonediteur($event));

        self::resetAllData();
    }

    /**
     * Test assign reflocalnonediteur not ok.
     * False role
     *
     * @covers \local_mentor_specialization_observer::assign_reflocalnonediteur
     */
    public function test_assign_reflocalnonediteur_nok_false_role() {

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $entityid  = \local_mentor_core\entity_api::create_entity(['name' => 'Entity', 'shortname' => 'Entity']);
        $entity2id = \local_mentor_core\entity_api::create_entity(['name' => 'Entity2', 'parentid' => $entityid]);

        $event = \core\event\role_assigned::create(array(
            'context'       => \context_coursecat::instance($entity2id),
            'relateduserid' => 0,
            'objectid'      => 0,
            'other'         => array(
                'id'        => $entity2id,
                'component' => 0,
            )
        ));

        self::assertFalse(\local_mentor_specialization_observer::assign_reflocalnonediteur($event));

        self::resetAllData();
    }

    /**
     * Test unassign reflocalnonediteur ok.
     *
     * @covers \local_mentor_specialization_observer::unassign_reflocalnonediteur
     */
    public function test_unassign_reflocalnonediteur_ok() {
        global $USER, $DB;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $referentlocal = $DB->get_record('role', ['shortname' => 'referentlocal']);

        $entityid       = \local_mentor_core\entity_api::create_entity(['name' => 'Entity', 'shortname' => 'Entity']);
        $entity2id      = \local_mentor_core\entity_api::create_entity(['name' => 'Entity2', 'parentid' => $entityid]);
        $entity2context = \context_coursecat::instance($entity2id);

        role_assign($referentlocal->id, $USER->id, $entity2context->id);

        // Assign user.
        $event = \core\event\role_assigned::create(array(
            'context'       => $entity2context,
            'relateduserid' => $USER->id,
            'objectid'      => $referentlocal->id,
            'other'         => array(
                'id'        => $entity2id,
                'component' => 0,
            )
        ));
        \local_mentor_specialization_observer::assign_reflocalnonediteur($event);

        $event = \core\event\role_unassigned::create(array(
            'context'       => $entity2context,
            'relateduserid' => $USER->id,
            'objectid'      => $referentlocal->id,
            'other'         => array(
                'id'        => $entity2id,
                'component' => 0,
            )
        ));

        self::assertTrue(\local_mentor_specialization_observer::unassign_reflocalnonediteur($event));

        self::resetAllData();
    }

    /**
     * Test unassign reflocalnonediteur not ok.
     * Wrong context
     *
     * @covers \local_mentor_specialization_observer::unassign_reflocalnonediteur
     */
    public function test_unassign_reflocalnonediteur_nok_wrong_context() {
        global $USER;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $event = \core\event\role_unassigned::create(array(
            'context' => \context_user::instance($USER->id),
            'other'   => array(
                'id'        => 0,
                'component' => 0,
            )
        ));

        self::assertFalse(\local_mentor_specialization_observer::unassign_reflocalnonediteur($event));

        self::resetAllData();
    }

    /**
     * Test unassign reflocalnonediteur not ok.
     * Is main entity
     *
     * @covers \local_mentor_specialization_observer::unassign_reflocalnonediteur
     */
    public function test_unassign_reflocalnonediteur_nok_main_entity() {
        global $DB;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $referentlocal = $DB->get_record('role', ['shortname' => 'referentlocal']);

        $entityid = \local_mentor_core\entity_api::create_entity(['name' => 'Entity', 'shortname' => 'Entity']);

        $event = \core\event\role_unassigned::create(array(
            'context'       => \context_coursecat::instance($entityid),
            'relateduserid' => 0,
            'objectid'      => $referentlocal->id,
            'other'         => array(
                'id'        => $entityid,
                'component' => 0,
            )
        ));

        self::assertFalse(\local_mentor_specialization_observer::unassign_reflocalnonediteur($event));

        self::resetAllData();
    }

    /**
     * Test unassign reflocalnonediteur not ok.
     * referentlocal not exist
     *
     * @covers \local_mentor_specialization_observer::unassign_reflocalnonediteur
     */
    public function test_unassign_reflocalnonediteur_nok_referentlocal_not_exist() {
        global $DB;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $DB->delete_records('role', ['shortname' => 'referentlocal']);

        $entityid  = \local_mentor_core\entity_api::create_entity(['name' => 'Entity', 'shortname' => 'Entity']);
        $entity2id = \local_mentor_core\entity_api::create_entity(['name' => 'Entity2', 'parentid' => $entityid]);

        $event = \core\event\role_unassigned::create(array(
            'context'       => \context_coursecat::instance($entity2id),
            'relateduserid' => 0,
            'objectid'      => 0,
            'other'         => array(
                'id'        => $entity2id,
                'component' => 0,
            )
        ));

        self::assertFalse(\local_mentor_specialization_observer::unassign_reflocalnonediteur($event));

        self::resetAllData();
    }

    /**
     * Test unassign reflocalnonediteur not ok.
     * False role
     *
     * @covers \local_mentor_specialization_observer::unassign_reflocalnonediteur
     */
    public function test_unassign_reflocalnonediteur_nok_false_role() {

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $entityid  = \local_mentor_core\entity_api::create_entity(['name' => 'Entity', 'shortname' => 'Entity']);
        $entity2id = \local_mentor_core\entity_api::create_entity(['name' => 'Entity2', 'parentid' => $entityid]);

        $event = \core\event\role_unassigned::create(array(
            'context'       => \context_coursecat::instance($entity2id),
            'relateduserid' => 0,
            'objectid'      => 0,
            'other'         => array(
                'id'        => $entity2id,
                'component' => 0,
            )
        ));

        self::assertFalse(\local_mentor_specialization_observer::unassign_reflocalnonediteur($event));

        self::resetAllData();
    }

    /**
     * Test unassign reflocalnonediteur not ok.
     * Is not assign
     *
     * @covers \local_mentor_specialization_observer::unassign_reflocalnonediteur
     */
    public function test_unassign_reflocalnonediteur_nok_is_not_assign() {
        global $DB;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $referentlocal = $DB->get_record('role', ['shortname' => 'referentlocal']);

        $entityid  = \local_mentor_core\entity_api::create_entity(['name' => 'Entity', 'shortname' => 'Entity']);
        $entity2id = \local_mentor_core\entity_api::create_entity(['name' => 'Entity2', 'parentid' => $entityid]);

        $event = \core\event\role_unassigned::create(array(
            'context'       => \context_coursecat::instance($entity2id),
            'relateduserid' => 0,
            'objectid'      => $referentlocal->id,
            'other'         => array(
                'id'        => $entity2id,
                'component' => 0,
            )
        ));

        self::assertFalse(\local_mentor_specialization_observer::unassign_reflocalnonediteur($event));

        self::resetAllData();
    }

    /**
     * Test unassign reflocalnonediteur not ok.
     * Is assign to an other sub entity
     *
     * @covers \local_mentor_specialization_observer::unassign_reflocalnonediteur
     */
    public function test_unassign_reflocalnonediteur_nok_assign_an_other_entity() {
        global $DB, $USER;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $referentlocal = $DB->get_record('role', ['shortname' => 'referentlocal']);

        $entityid       = \local_mentor_core\entity_api::create_entity(['name' => 'Entity', 'shortname' => 'Entity']);
        $entity2id      = \local_mentor_core\entity_api::create_entity(['name' => 'Entity2', 'parentid' => $entityid]);
        $entity2context = \context_coursecat::instance($entity2id);
        $entity3id      = \local_mentor_core\entity_api::create_entity(['name' => 'Entity3', 'parentid' => $entityid]);

        role_assign($referentlocal->id, $USER->id, $entity2context->id);

        // Assign user.
        $event = \core\event\role_assigned::create(array(
            'context'       => $entity2context,
            'relateduserid' => $USER->id,
            'objectid'      => $referentlocal->id,
            'other'         => array(
                'id'        => $entity2id,
                'component' => 0,
            )
        ));
        \local_mentor_specialization_observer::assign_reflocalnonediteur($event);

        $event = \core\event\role_unassigned::create(array(
            'context'       => \context_coursecat::instance($entity3id),
            'relateduserid' => $USER->id,
            'objectid'      => $referentlocal->id,
            'other'         => array(
                'id'        => $entity3id,
                'component' => 0,
            )
        ));

        self::assertFalse(\local_mentor_specialization_observer::unassign_reflocalnonediteur($event));

        self::resetAllData();
    }

    /**
     * Test manager change user entities notification ok.
     *
     * @covers \local_mentor_specialization_observer::manager_change_user_entities_notification
     */
    public function test_manager_change_user_entities_notification_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        // Create 4 entities.
        $entityid  = \local_mentor_core\entity_api::create_entity(['name' => 'Entity', 'shortname' => 'Entity']);
        $entity    = \local_mentor_core\entity_api::get_entity($entityid);
        $entityid2 = \local_mentor_core\entity_api::create_entity(['name' => 'Entity2', 'shortname' => 'Entity2']);
        $entity2   = \local_mentor_core\entity_api::get_entity($entityid2);
        $entityid3 = \local_mentor_core\entity_api::create_entity(['name' => 'Entity3', 'shortname' => 'Entity3']);
        $entity3   = \local_mentor_core\entity_api::get_entity($entityid3);
        $entityid4 = \local_mentor_core\entity_api::create_entity(['name' => 'Entity4', 'shortname' => 'Entity4']);
        $entity4   = \local_mentor_core\entity_api::get_entity($entityid4);

        // Create data user.
        $user1                                  = self::getDataGenerator()->create_user();
        $user1->profile_field_mainentity        = $entity->name;
        $user1->profile_field_secondaryentities = [$entity2->name];

        // Create data user with different data for entities.
        $user1bis                                  = core_user::get_user($user1->id);
        $user1bis->profile_field_mainentity        = $entity3->name;
        $user1bis->profile_field_secondaryentities = $entity4->name;

        // Create data for user_updated event.
        // Other old data missing.
        $other = json_encode(
            array(
                'old' => $user1,
                'new' => $user1bis
            )
        );
        $data  = array(
            'objectid'      => $user1->id,
            'relateduserid' => $user1->id,
            'context'       => \context_user::instance($user1->id),
            'other'         => $other
        );

        // Create and trigger event.
        $event = \core\event\user_updated::create($data);
        self::assertNull(local_mentor_specialization_observer::manager_change_user_entities_notification($event));

        self::resetAllData();
    }

    /**
     * Test manager change user entities notification not ok
     * Missing other data
     *
     * @covers \local_mentor_specialization_observer::manager_change_user_entities_notification
     */
    public function test_manager_change_user_entities_notification_nok_other_data_missing() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        // Create user.
        $user = self::getDataGenerator()->create_user();

        // Create data for user_updated event.
        // Other data missing.
        $data = array(
            'objectid'      => $user->id,
            'relateduserid' => $user->id,
            'context'       => \context_user::instance($user->id)
        );

        // Create and trigger event.
        $event = \core\event\user_updated::create($data);
        self::assertFalse(local_mentor_specialization_observer::manager_change_user_entities_notification($event));

        self::resetAllData();
    }

    /**
     * Test manager change user entities notification not ok
     * Missing old and new data
     *
     * @covers \local_mentor_specialization_observer::manager_change_user_entities_notification
     */
    public function test_manager_change_user_entities_notification_nok_old_and_new_data_missing() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        // Create user.
        $user = self::getDataGenerator()->create_user();

        // Create data for user_updated event.
        // Other old data missing.
        $other = json_encode(array('new' => 'test'));
        $data  = array(
            'objectid'      => $user->id,
            'relateduserid' => $user->id,
            'context'       => \context_user::instance($user->id),
            'other'         => $other
        );

        // Create and trigger event.
        $event = \core\event\user_updated::create($data);
        self::assertFalse(local_mentor_specialization_observer::manager_change_user_entities_notification($event));

        // Create data for user_updated event.
        // Other new data missing.
        $other = json_encode(array('old' => 'test'));
        $data  = array(
            'objectid'      => $user->id,
            'relateduserid' => $user->id,
            'context'       => \context_user::instance($user->id),
            'other'         => $other
        );

        // Create and trigger event.
        $event = \core\event\user_updated::create($data);
        self::assertFalse(local_mentor_specialization_observer::manager_change_user_entities_notification($event));

        self::resetAllData();
    }

    /**
     * Test manager change user entities notification not ok
     * Same user
     *
     * @covers \local_mentor_specialization_observer::manager_change_user_entities_notification
     */
    public function test_manager_change_user_entities_notification_nok_same_user() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $entityid = \local_mentor_core\entity_api::create_entity(['name' => 'Entity', 'shortname' => 'Entity']);
        $entity   = \local_mentor_core\entity_api::get_entity($entityid);

        // Create user and main entity data.
        $user                           = self::getDataGenerator()->create_user();
        $user->profile_field_mainentity = $entity->name;

        // Create data for user_updated event.
        // Other old data missing.
        $other = json_encode(
            array(
                'old' => $user,
                'new' => new \stdClass()
            )
        );
        $data  = array(
            'objectid'      => $user->id,
            'relateduserid' => $user->id,
            'context'       => \context_user::instance($user->id),
            'other'         => $other
        );

        self::setUser($user->id);

        // Create and trigger event.
        $event = \core\event\user_updated::create($data);
        self::assertFalse(local_mentor_specialization_observer::manager_change_user_entities_notification($event));

        self::resetAllData();
    }

    /**
     * Test manager change user entities notification not ok.
     * User doesn't have capability.
     *
     * @covers \local_mentor_specialization_observer::manager_change_user_entities_notification
     */
    public function test_manager_change_user_entities_notification_nok_not_capability() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        // Create entity.
        $entityid = \local_mentor_core\entity_api::create_entity(['name' => 'Entity', 'shortname' => 'Entity']);
        $entity   = \local_mentor_core\entity_api::get_entity($entityid);

        // Create user with main entity data.
        $user1                           = self::getDataGenerator()->create_user();
        $user1->profile_field_mainentity = $entity->name;

        // Create user without entity data.
        $user2 = self::getDataGenerator()->create_user();

        // Create data for user_updated event.
        // Other old data missing.
        $other = json_encode(
            array(
                'old' => $user1,
                'new' => new \stdClass()
            )
        );
        $data  = array(
            'objectid'      => $user1->id,
            'relateduserid' => $user1->id,
            'context'       => \context_user::instance($user1->id),
            'other'         => $other
        );

        self::setUser($user2->id);

        // Create and trigger event.
        $event = \core\event\user_updated::create($data);
        self::assertFalse(local_mentor_specialization_observer::manager_change_user_entities_notification($event));

        self::resetAllData();
    }

    /**
     * Test manager change user entities notification not ok.
     * Same data.
     *
     * @covers \local_mentor_specialization_observer::manager_change_user_entities_notification
     */
    public function test_manager_change_user_entities_notification_nok_same_data() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        // Create entity.
        $entityid = \local_mentor_core\entity_api::create_entity(['name' => 'Entity', 'shortname' => 'Entity']);
        $entity   = \local_mentor_core\entity_api::get_entity($entityid);

        // Create user with main and secondary entities data.
        $user1                                  = self::getDataGenerator()->create_user();
        $user1->profile_field_mainentity        = $entity->name;
        $user1->profile_field_secondaryentities = [$entity->name . 1, $entity->name . 2];

        // Create user object with same data.
        $user1bis                                  = core_user::get_user($user1->id);
        $user1bis->profile_field_mainentity        = $entity->name;
        $user1bis->profile_field_secondaryentities = '' . $entity->name . '1, ' . $entity->name . '2';

        // Create data for user_updated event.
        // Other old data missing.
        $other = json_encode(
            array(
                'old' => $user1,
                'new' => $user1bis
            )
        );
        $data  = array(
            'objectid'      => $user1->id,
            'relateduserid' => $user1->id,
            'context'       => \context_user::instance($user1->id),
            'other'         => $other
        );

        // Create and trigger event.
        $event = \core\event\user_updated::create($data);
        self::assertFalse(local_mentor_specialization_observer::manager_change_user_entities_notification($event));

        self::resetAllData();
    }
}
