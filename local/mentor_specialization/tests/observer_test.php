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
        $reflection = new ReflectionClass($specialization);
        $instance = $reflection->getProperty('instance');
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

        $referentlocal = $DB->get_record('role', ['shortname' => 'referentlocal']);
        $reflocalnonediteur = $DB->get_record('role', ['shortname' => 'reflocalnonediteur']);

        $entityid = \local_mentor_core\entity_api::create_entity(['name' => 'Entity', 'shortname' => 'Entity']);
        $entitycontext = \context_coursecat::instance($entityid);
        $entity2id = \local_mentor_core\entity_api::create_entity(['name' => 'Entity2', 'parentid' => $entityid]);
        $entity2context = \context_coursecat::instance($entity2id);

        self::assertFalse($DB->record_exists('role_assignments', array(
                'roleid' => $reflocalnonediteur->id,
                'contextid' => $entitycontext->id,
                'userid' => $USER->id
        )));

        $event = \core\event\role_assigned::create(array(
                'context' => $entity2context,
                'relateduserid' => $USER->id,
                'objectid' => $referentlocal->id,
                'other' => array(
                        'id' => $entity2id,
                        'component' => 0,
                )
        ));

        self::assertTrue(\local_mentor_specialization_observer::assign_reflocalnonediteur($event));

        self::assertTrue($DB->record_exists('role_assignments', array(
                'roleid' => $reflocalnonediteur->id,
                'contextid' => $entitycontext->id,
                'userid' => $USER->id
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
                'other' => array(
                        'id' => 0,
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
                'context' => \context_coursecat::instance($entityid),
                'relateduserid' => 0,
                'objectid' => 0,
                'other' => array(
                        'id' => $entityid,
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

        $entityid = \local_mentor_core\entity_api::create_entity(['name' => 'Entity', 'shortname' => 'Entity']);
        $entity2id = \local_mentor_core\entity_api::create_entity(['name' => 'Entity2', 'parentid' => $entityid]);

        $event = \core\event\role_assigned::create(array(
                'context' => \context_coursecat::instance($entity2id),
                'relateduserid' => 0,
                'objectid' => 0,
                'other' => array(
                        'id' => $entity2id,
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

        $entityid = \local_mentor_core\entity_api::create_entity(['name' => 'Entity', 'shortname' => 'Entity']);
        $entity2id = \local_mentor_core\entity_api::create_entity(['name' => 'Entity2', 'parentid' => $entityid]);

        $event = \core\event\role_assigned::create(array(
                'context' => \context_coursecat::instance($entity2id),
                'relateduserid' => 0,
                'objectid' => 0,
                'other' => array(
                        'id' => $entity2id,
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

        $entityid = \local_mentor_core\entity_api::create_entity(['name' => 'Entity', 'shortname' => 'Entity']);
        $entity2id = \local_mentor_core\entity_api::create_entity(['name' => 'Entity2', 'parentid' => $entityid]);
        $entity2context = \context_coursecat::instance($entity2id);

        role_assign($referentlocal->id, $USER->id, $entity2context->id);

        // Assign user.
        $event = \core\event\role_assigned::create(array(
                'context' => $entity2context,
                'relateduserid' => $USER->id,
                'objectid' => $referentlocal->id,
                'other' => array(
                        'id' => $entity2id,
                        'component' => 0,
                )
        ));
        \local_mentor_specialization_observer::assign_reflocalnonediteur($event);

        $event = \core\event\role_unassigned::create(array(
                'context' => $entity2context,
                'relateduserid' => $USER->id,
                'objectid' => $referentlocal->id,
                'other' => array(
                        'id' => $entity2id,
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
                'other' => array(
                        'id' => 0,
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
                'context' => \context_coursecat::instance($entityid),
                'relateduserid' => 0,
                'objectid' => $referentlocal->id,
                'other' => array(
                        'id' => $entityid,
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

        $entityid = \local_mentor_core\entity_api::create_entity(['name' => 'Entity', 'shortname' => 'Entity']);
        $entity2id = \local_mentor_core\entity_api::create_entity(['name' => 'Entity2', 'parentid' => $entityid]);

        $event = \core\event\role_unassigned::create(array(
                'context' => \context_coursecat::instance($entity2id),
                'relateduserid' => 0,
                'objectid' => 0,
                'other' => array(
                        'id' => $entity2id,
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

        $entityid = \local_mentor_core\entity_api::create_entity(['name' => 'Entity', 'shortname' => 'Entity']);
        $entity2id = \local_mentor_core\entity_api::create_entity(['name' => 'Entity2', 'parentid' => $entityid]);

        $event = \core\event\role_unassigned::create(array(
                'context' => \context_coursecat::instance($entity2id),
                'relateduserid' => 0,
                'objectid' => 0,
                'other' => array(
                        'id' => $entity2id,
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

        $entityid = \local_mentor_core\entity_api::create_entity(['name' => 'Entity', 'shortname' => 'Entity']);
        $entity2id = \local_mentor_core\entity_api::create_entity(['name' => 'Entity2', 'parentid' => $entityid]);

        $event = \core\event\role_unassigned::create(array(
                'context' => \context_coursecat::instance($entity2id),
                'relateduserid' => 0,
                'objectid' => $referentlocal->id,
                'other' => array(
                        'id' => $entity2id,
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

        $entityid = \local_mentor_core\entity_api::create_entity(['name' => 'Entity', 'shortname' => 'Entity']);
        $entity2id = \local_mentor_core\entity_api::create_entity(['name' => 'Entity2', 'parentid' => $entityid]);
        $entity2context = \context_coursecat::instance($entity2id);
        $entity3id = \local_mentor_core\entity_api::create_entity(['name' => 'Entity3', 'parentid' => $entityid]);

        role_assign($referentlocal->id, $USER->id, $entity2context->id);

        // Assign user.
        $event = \core\event\role_assigned::create(array(
                'context' => $entity2context,
                'relateduserid' => $USER->id,
                'objectid' => $referentlocal->id,
                'other' => array(
                        'id' => $entity2id,
                        'component' => 0,
                )
        ));
        \local_mentor_specialization_observer::assign_reflocalnonediteur($event);

        $event = \core\event\role_unassigned::create(array(
                'context' => \context_coursecat::instance($entity3id),
                'relateduserid' => $USER->id,
                'objectid' => $referentlocal->id,
                'other' => array(
                        'id' => $entity3id,
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
        $entityid = \local_mentor_core\entity_api::create_entity(['name' => 'Entity', 'shortname' => 'Entity']);
        $entity = \local_mentor_core\entity_api::get_entity($entityid);
        $entityid2 = \local_mentor_core\entity_api::create_entity(['name' => 'Entity2', 'shortname' => 'Entity2']);
        $entity2 = \local_mentor_core\entity_api::get_entity($entityid2);
        $entityid3 = \local_mentor_core\entity_api::create_entity(['name' => 'Entity3', 'shortname' => 'Entity3']);
        $entity3 = \local_mentor_core\entity_api::get_entity($entityid3);
        $entityid4 = \local_mentor_core\entity_api::create_entity(['name' => 'Entity4', 'shortname' => 'Entity4']);
        $entity4 = \local_mentor_core\entity_api::get_entity($entityid4);

        // Create data user.
        $user1 = self::getDataGenerator()->create_user();
        $user1->profile_field_mainentity = $entity->name;
        $user1->profile_field_secondaryentities = [$entity2->name];

        // Create data user with different data for entities.
        $user1bis = core_user::get_user($user1->id);
        $user1bis->profile_field_mainentity = $entity3->name;
        $user1bis->profile_field_secondaryentities = $entity4->name;

        // Create data for user_updated event.
        // Other old data missing.
        $other = json_encode(
                array(
                        'old' => $user1,
                        'new' => $user1bis
                )
        );
        $data = array(
                'objectid' => $user1->id,
                'relateduserid' => $user1->id,
                'context' => \context_user::instance($user1->id),
                'other' => $other
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
                'objectid' => $user->id,
                'relateduserid' => $user->id,
                'context' => \context_user::instance($user->id)
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
        $data = array(
                'objectid' => $user->id,
                'relateduserid' => $user->id,
                'context' => \context_user::instance($user->id),
                'other' => $other
        );

        // Create and trigger event.
        $event = \core\event\user_updated::create($data);
        self::assertFalse(local_mentor_specialization_observer::manager_change_user_entities_notification($event));

        // Create data for user_updated event.
        // Other new data missing.
        $other = json_encode(array('old' => 'test'));
        $data = array(
                'objectid' => $user->id,
                'relateduserid' => $user->id,
                'context' => \context_user::instance($user->id),
                'other' => $other
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
        $entity = \local_mentor_core\entity_api::get_entity($entityid);

        // Create user and main entity data.
        $user = self::getDataGenerator()->create_user();
        $user->profile_field_mainentity = $entity->name;

        // Create data for user_updated event.
        // Other old data missing.
        $other = json_encode(
                array(
                        'old' => $user,
                        'new' => new \stdClass()
                )
        );
        $data = array(
                'objectid' => $user->id,
                'relateduserid' => $user->id,
                'context' => \context_user::instance($user->id),
                'other' => $other
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
        $entity = \local_mentor_core\entity_api::get_entity($entityid);

        // Create user with main entity data.
        $user1 = self::getDataGenerator()->create_user();
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
        $data = array(
                'objectid' => $user1->id,
                'relateduserid' => $user1->id,
                'context' => \context_user::instance($user1->id),
                'other' => $other
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
        $entity = \local_mentor_core\entity_api::get_entity($entityid);

        // Create user with main and secondary entities data.
        $user1 = self::getDataGenerator()->create_user();
        $user1->profile_field_mainentity = $entity->name;
        $user1->profile_field_secondaryentities = [$entity->name . 1, $entity->name . 2];

        // Create user object with same data.
        $user1bis = core_user::get_user($user1->id);
        $user1bis->profile_field_mainentity = $entity->name;
        $user1bis->profile_field_secondaryentities = '' . $entity->name . '1, ' . $entity->name . '2';

        // Create data for user_updated event.
        // Other old data missing.
        $other = json_encode(
                array(
                        'old' => $user1,
                        'new' => $user1bis
                )
        );
        $data = array(
                'objectid' => $user1->id,
                'relateduserid' => $user1->id,
                'context' => \context_user::instance($user1->id),
                'other' => $other
        );

        // Create and trigger event.
        $event = \core\event\user_updated::create($data);
        self::assertFalse(local_mentor_specialization_observer::manager_change_user_entities_notification($event));

        self::resetAllData();
    }

    /**
     * Test enrol_session_send_mail not ok context level.
     *
     * @covers \local_mentor_specialization_observer::enrol_session_send_mail
     */
    public function test_enrol_session_send_mail_nok_context_level() {

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $event = \core\event\role_assigned::create(array(
                'context' => \context_coursecat::instance(1),
                'relateduserid' => 0,
                'objectid' => 0,
                'other' => array(
                        'id' => 1,
                        'component' => 0,
                )
        ));

        self::assertFalse(\local_mentor_specialization_observer::enrol_session_send_mail($event));

        self::resetAllData();
    }

    /**
     * Test enrol_session_send_mail not ok role.
     *
     * @covers \local_mentor_specialization_observer::enrol_session_send_mail
     */
    public function test_enrol_session_send_mail_nok_role() {

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        // Create course.
        $course = self::getDataGenerator()->create_course();

        // Get teacher role but the role does not allow to end the event.
        $dbi = \local_mentor_specialization\database_interface::get_instance();
        $teacher = $dbi->get_role_by_name('teacher');

        $event = \core\event\role_assigned::create(array(
                'context' => \context_course::instance($course->id),
                'relateduserid' => 0,
                'objectid' => $teacher->id,
                'other' => array(
                        'id' => 1,
                        'component' => 0,
                )
        ));

        self::assertFalse(\local_mentor_specialization_observer::enrol_session_send_mail($event));

        self::resetAllData();
    }

    /**
     * Test enrol_session_send_mail not ok not session.
     *
     * @covers \local_mentor_specialization_observer::enrol_session_send_mail
     */
    public function test_enrol_session_send_mail_nok_not_session() {

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        // Get participant role, the role allows the event to continue.
        $dbi = \local_mentor_specialization\database_interface::get_instance();
        $participant = $dbi->get_role_by_name('participant');

        // Is not session. The course does not allow to end the event.
        $course = self::getDataGenerator()->create_course();

        $event = \core\event\role_assigned::create(array(
                'context' => \context_course::instance($course->id),
                'relateduserid' => 0,
                'objectid' => $participant->id,
                'other' => array(
                        'id' => 1,
                        'component' => 0,
                )
        ));

        self::assertFalse(\local_mentor_specialization_observer::enrol_session_send_mail($event));

        self::resetAllData();
    }

    /**
     * Test enrol_session_send_mail not ok not session.
     *
     * @covers \local_mentor_specialization_observer::enrol_session_send_mail
     */
    public function test_enrol_session_send_mail_nok_enrol() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        // Create user.
        $user = self::getDataGenerator()->create_user();

        // Get participant role, the role allows the event to continue.
        $dbi = \local_mentor_specialization\database_interface::get_instance();
        $participant = $dbi->get_role_by_name('participant');

        // Create entity.
        $entityid = \local_mentor_core\entity_api::create_entity(['name' => 'Entity', 'shortname' => 'Entity']);
        $entity = \local_mentor_core\entity_api::get_entity($entityid);

        // Create training.
        $trainingdata = new \stdClass();
        $trainingdata->name = 'trainingfullname1';
        $trainingdata->shortname = 'trainingshortname1';
        $trainingdata->content = 'summary';
        $trainingdata->status = \local_mentor_core\training::STATUS_ELABORATION_COMPLETED;
        $trainingdata->categorychildid = $entity->get_entity_formation_category();
        $trainingdata->categoryid = $entity->id;
        $trainingdata->creativestructure = $entity->id;
        $training = \local_mentor_core\training_api::create_training($trainingdata);

        // Create session.
        $session = \local_mentor_core\session_api::create_session($training->id, 'sessionshortname', true);
        $session->create_self_enrolment_instance();

        // Self enrol user. The enrolment does not allow to end the event.
        self::getDataGenerator()->enrol_user($user->id, $session->courseid, 'participant', 'self');

        $event = \core\event\role_assigned::create(array(
                'context' => $session->get_context(),
                'relateduserid' => $user->id,
                'objectid' => $participant->id,
                'other' => array(
                        'id' => 1,
                        'component' => 0,
                )
        ));

        self::assertFalse(\local_mentor_specialization_observer::enrol_session_send_mail($event));

        self::resetAllData();
    }

    /**
     * Test enrol_session_send_mail ok enrol manual.
     *
     * @covers \local_mentor_specialization_observer::enrol_session_send_mail
     */
    public function test_enrol_session_send_mail_ok_enrol_manual() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        // Get participant, formateur, tuteur roles.
        $dbi = \local_mentor_specialization\database_interface::get_instance();
        $participant = $dbi->get_role_by_name('participant');
        $formateur = $dbi->get_role_by_name('formateur');
        $tutor = $dbi->get_role_by_name('tuteur');

        // Create entity.
        $entityid = \local_mentor_core\entity_api::create_entity(['name' => 'Entity', 'shortname' => 'Entity']);
        $entity = \local_mentor_core\entity_api::get_entity($entityid);

        // Create training.
        $trainingdata = new \stdClass();
        $trainingdata->name = 'trainingfullname2';
        $trainingdata->shortname = 'trainingshortname2';
        $trainingdata->content = 'summary';
        $trainingdata->status = \local_mentor_core\training::STATUS_ELABORATION_COMPLETED;
        $trainingdata->categorychildid = $entity->get_entity_formation_category();
        $trainingdata->categoryid = $entity->id;
        $trainingdata->creativestructure = $entity->id;
        $training = \local_mentor_core\training_api::create_training($trainingdata);

        // Create session.
        $session = \local_mentor_core\session_api::create_session($training->id, 'sessionshortname', true);
        $session->sessionstartdate = time();
        $session->update($session);
        $session->create_manual_enrolment_instance();

        // Participant.
        $user1 = self::getDataGenerator()->create_user();
        self::getDataGenerator()->enrol_user($user1->id, $session->courseid, 'participant');

        // Formateur.
        $user2 = self::getDataGenerator()->create_user();
        self::getDataGenerator()->enrol_user($user2->id, $session->courseid, 'formateur');

        // Tutor.
        $user3 = self::getDataGenerator()->create_user();
        self::getDataGenerator()->enrol_user($user3->id, $session->courseid, 'tuteur');

        // Close the default email sink.
        $sink = $this->redirectEmails();
        $sink->close();
        unset_config('noemailever');
        $sink = $this->redirectEmails();

        // Participant enrol event.
        $event = \core\event\role_assigned::create(array(
                'context' => $session->get_context(),
                'relateduserid' => $user1->id,
                'objectid' => $participant->id,
                'other' => array(
                        'id' => 1,
                        'component' => 0,
                )
        ));

        self::assertTrue(\local_mentor_specialization_observer::enrol_session_send_mail($event));

        // Formateur enrol event.
        $event = \core\event\role_assigned::create(array(
                'context' => $session->get_context(),
                'relateduserid' => $user2->id,
                'objectid' => $formateur->id,
                'other' => array(
                        'id' => 1,
                        'component' => 0,
                )
        ));

        self::assertTrue(\local_mentor_specialization_observer::enrol_session_send_mail($event));

        // Tutor enrol event.
        $event = \core\event\role_assigned::create(array(
                'context' => $session->get_context(),
                'relateduserid' => $user3->id,
                'objectid' => $tutor->id,
                'other' => array(
                        'id' => 1,
                        'component' => 0,
                )
        ));

        self::assertTrue(\local_mentor_specialization_observer::enrol_session_send_mail($event));

        // Check if send mail.
        $this->assertSame(3, $sink->count());
        $resultmail = $sink->get_messages();
        $this->assertCount(3, $resultmail);
        $sink->close();

        self::assertEquals($resultmail[0]->to, $user1->email);
        self::assertEquals($resultmail[0]->subject,
                get_string('email_enrol_user_session_object', 'local_mentor_specialization', $session->fullname));

        self::assertEquals($resultmail[1]->to, $user2->email);
        self::assertEquals($resultmail[1]->subject, get_string('email_enrol_user_session_object', 'local_mentor_specialization',
                $session->fullname));

        self::assertEquals($resultmail[2]->to, $user3->email);
        self::assertEquals($resultmail[2]->subject, get_string('email_enrol_user_session_object', 'local_mentor_specialization',
                $session->fullname));

        self::resetAllData();
    }

    /**
     * Test enrol_session_send_mail ok enrol sirh.
     *
     * @covers \local_mentor_specialization_observer::enrol_session_send_mail
     */
    public function test_enrol_session_send_mail_ok_enrol_sirh() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        // Get participant, formateur, tuteur roles.
        $dbi = \local_mentor_specialization\database_interface::get_instance();
        $participant = $dbi->get_role_by_name('participant');
        $formateur = $dbi->get_role_by_name('formateur');
        $tutor = $dbi->get_role_by_name('tuteur');

        // Create entity.
        $entityid = \local_mentor_core\entity_api::create_entity(['name' => 'Entity', 'shortname' => 'Entity']);
        $entity = \local_mentor_core\entity_api::get_entity($entityid);

        // Create training.
        $trainingdata = new \stdClass();
        $trainingdata->name = 'trainingfullname3';
        $trainingdata->shortname = 'trainingshortname3';
        $trainingdata->content = 'summary';
        $trainingdata->status = \local_mentor_core\training::STATUS_ELABORATION_COMPLETED;
        $trainingdata->categorychildid = $entity->get_entity_formation_category();
        $trainingdata->categoryid = $entity->id;
        $trainingdata->creativestructure = $entity->id;
        $training = \local_mentor_core\training_api::create_training($trainingdata);

        // Create session.
        $session = \local_mentor_core\session_api::create_session($training->id, 'sessionshortname', true);
        $session->sessionstartdate = time();
        $session->update($session);

        \enrol_sirh\sirh_api::create_enrol_sirh_instance(
                $session->courseid,
                'sirh',
                'sirhtraining',
                'sirhsession'
        );

        // Participant.
        $user1 = self::getDataGenerator()->create_user();
        self::getDataGenerator()->enrol_user($user1->id, $session->courseid, 'participant', 'sirh');

        // Formateur.
        $user2 = self::getDataGenerator()->create_user();
        self::getDataGenerator()->enrol_user($user2->id, $session->courseid, 'formateur', 'sirh');

        // Tutor.
        $user3 = self::getDataGenerator()->create_user();
        self::getDataGenerator()->enrol_user($user3->id, $session->courseid, 'tuteur', 'sirh');

        // Close the default email sink.
        $sink = $this->redirectEmails();
        $sink->close();
        unset_config('noemailever');
        $sink = $this->redirectEmails();

        // Participant enrol event.
        $event = \core\event\role_assigned::create(array(
                'context' => $session->get_context(),
                'relateduserid' => $user1->id,
                'objectid' => $participant->id,
                'other' => array(
                        'id' => 1,
                        'component' => 0,
                )
        ));

        self::assertTrue(\local_mentor_specialization_observer::enrol_session_send_mail($event));

        // Formateur enrol event.
        $event = \core\event\role_assigned::create(array(
                'context' => $session->get_context(),
                'relateduserid' => $user2->id,
                'objectid' => $formateur->id,
                'other' => array(
                        'id' => 1,
                        'component' => 0,
                )
        ));

        self::assertTrue(\local_mentor_specialization_observer::enrol_session_send_mail($event));

        // Tutor enrol event.
        $event = \core\event\role_assigned::create(array(
                'context' => $session->get_context(),
                'relateduserid' => $user3->id,
                'objectid' => $tutor->id,
                'other' => array(
                        'id' => 1,
                        'component' => 0,
                )
        ));

        self::assertTrue(\local_mentor_specialization_observer::enrol_session_send_mail($event));

        // Check if send mail.
        $this->assertSame(3, $sink->count());
        $resultmail = $sink->get_messages();
        $this->assertCount(3, $resultmail);
        $sink->close();

        self::assertEquals($resultmail[0]->to, $user1->email);
        self::assertEquals($resultmail[0]->subject,
                get_string('email_enrol_user_session_object', 'local_mentor_specialization', $session->fullname));

        self::assertEquals($resultmail[1]->to, $user2->email);
        self::assertEquals($resultmail[1]->subject, get_string('email_enrol_user_session_object', 'local_mentor_specialization',
                $session->fullname));

        self::assertEquals($resultmail[2]->to, $user3->email);
        self::assertEquals($resultmail[2]->subject, get_string('email_enrol_user_session_object', 'local_mentor_specialization',
                $session->fullname));

        self::resetAllData();
    }
}
