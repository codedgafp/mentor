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
 * Store test file
 *
 * @package    logstore_mentor
 * @copyright  2022 Edunao SAS (contact@edunao.com)
 * @author     adrien <adrien.jamot@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/local/mentor_core/classes/model/session.php');
require_once($CFG->dirroot . '/local/mentor_core/api/session.php');
require_once($CFG->dirroot . '/local/mentor_core/api/profile.php');

class logstore_mentor2_store_testcase extends advanced_testcase {

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

        // Reset the database interface.
        $dbi = \logstore_mentor2\database_interface\database_interface::get_instance();
        $reflection = new ReflectionClass($dbi);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true); // Now we can modify that :).
        $instance->setValue(null, null); // Instance is gone.
        $instance->setAccessible(false); // Clean up.

        // Reset the database interface.
        $dbi = \local_mentor_core\database_interface::get_instance();
        $reflection = new ReflectionClass($dbi);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true); // Now we can modify that :).
        $instance->setValue(null, null); // Instance is gone.
        $instance->setAccessible(false); // Clean up.

        \local_mentor_core\training_api::clear_cache();
        \local_mentor_core\session_api::clear_cache();
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

        $field = $DB->get_record('user_info_field', ['shortname' => 'mainentity']);

        $userdata = new stdClass();
        $userdata->fieldid = $field->id;
        $userdata->data = 'New Entity 1';
        $userdata->userid = $userid;

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

        // Fields for taining.
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
                'regions' => [5], // Corse.
                'userid' => 2  // Set the admin user as manager of the entity.
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
        $data = new stdClass();
        $data->opento = 'current_entity';
        $session->update($data);

        return $session->id;
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
     * Tests log writing.
     *
     * @covers \logstore_mentor2\log\store::insert_event_entries
     * @covers \logstore_mentor2\log\store::write
     * @covers \logstore_mentor2\log\store::is_event_ignored
     * @covers \logstore_mentor2\log\store::flush
     * @covers \logstore_mentor2\models\entity::create
     * @covers \logstore_mentor2\models\entity::get_or_create_record
     * @covers \logstore_mentor2\models\log::get_or_create_record
     * @covers \logstore_mentor2\models\log::create_record
     * @covers \logstore_mentor2\models\region::get_or_create_record
     * @covers \logstore_mentor2\models\session::get_or_create_record
     * @covers \logstore_mentor2\models\session::create
     * @throws ReflectionException
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function test_log_writing_ok() {
        global $DB, $USER;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();
        $this->preventResetByRollback(); // Logging waits till the transaction gets committed.

        self::setAdminUser();

        $profile = \local_mentor_core\profile_api::get_profile($USER->id);

        // Test all plugins are disabled by this command.
        set_config('enabled_stores', '', 'tool_log');
        $manager = get_log_manager(true);
        $stores = $manager->get_readers();
        self::assertCount(0, $stores);

        // Enable logging plugin.
        set_config('enabled_stores', 'logstore_mentor2', 'tool_log');
        set_config('buffersize', 0, 'logstore_mentor2');
        set_config('logguests', 1, 'logstore_mentor2');

        // Create session.
        $sessionid = $this->init_session_creation();

        $session = \local_mentor_core\session_api::get_session($sessionid);
        $session->sessionstartdate = time();
        $session->update($session);

        // Set the same main entity for the user and for the session.
        $profile->set_main_entity($session->get_entity());
        $profile->set_profile_field('region', 'Auvergne-Rhône-Alpes');
        $profile->set_profile_field('department', '07 - Ardèche');
        $profile->set_profile_field('category', 'A+');
        $profile->set_profile_field('status', 'Fonctionnaire');

        self::getDataGenerator()->enrol_user($USER->id, $session->get_course()->id);

        $logs = $DB->get_records('logstore_mentor_log2');
        self::assertCount(0, $logs);

        $event1 = \core\event\course_viewed::create(
            array('context' => $session->get_context(), 'other' => array('sample' => 5, 'xx' => 10)));

        $store = new \logstore_mentor2\log\store($manager);
        $store->write($event1);

        // Check mentor log.
        $logs = $DB->get_records('logstore_mentor_log2');
        self::assertCount(1, $logs);
        $log = reset($logs);
        self::assertEquals(1, $log->numberview);

        // The log must be the same with an updated numberview.
        $store->write($event1);
        $logs = $DB->get_records('logstore_mentor_log2');
        self::assertCount(1, $logs);
        $log = reset($logs);
        self::assertEquals(2, $log->numberview);

        // Check entity log.
        $entitylogs = $DB->get_records('logstore_mentor_entity2');
        self::assertCount(1, $entitylogs);
        $entitylog = reset($entitylogs);
        self::assertEquals($session->get_entity()->get_name(), $entitylog->name);
        self::assertEquals($session->get_entity()->id, $entitylog->entityid);

        // Check region log.
        $regionlogs = $DB->get_records('logstore_mentor_region2');
        self::assertCount(2, $regionlogs); // One log for the entity and one log for the user.
        $regionlog = reset($regionlogs);
        self::assertEquals('Corse', $regionlog->name);

        // Check collection log.
        $collectionlogs = $DB->get_records('logstore_mentor_collection2');
        self::assertCount(1, $collectionlogs);
        $collectionlog = reset($collectionlogs);
        self::assertEquals('accompagnement', $collectionlog->name);

        // Check session log.
        $sessionlogs = $DB->get_records('logstore_mentor_session2');
        self::assertCount(1, $sessionlogs);
        $sessionlog = reset($sessionlogs);
        self::assertEquals($session->id, $sessionlog->sessionid);
        self::assertEquals('inpreparation', $sessionlog->status);
        self::assertEquals(0, $sessionlog->shared);
        self::assertEquals($entitylog->id, $sessionlog->entitylogid);
        self::assertEquals($entitylog->id, $sessionlog->subentitylogid);
        self::assertEquals($entitylog->id, $sessionlog->trainingentitylogid);
        self::assertEquals($entitylog->id, $sessionlog->trainingsubentitylogid);

        // Check user log.
        $userlogs = $DB->get_records('logstore_mentor_user2');
        self::assertCount(1, $userlogs);
        $userlog = reset($userlogs);
        self::assertEquals($USER->id, $userlog->userid);
        self::assertEquals('Fonctionnaire', $userlog->status);
        self::assertEquals('A+', $userlog->category);
        self::assertEquals('07 - Ardèche', $userlog->department);
        $userregionlog = $regionlogs[$userlog->regionlogid];
        self::assertEquals('Auvergne-Rhône-Alpes', $userregionlog->name);
        $userentitylog = $entitylogs[$userlog->entitylogid];
        self::assertEquals($session->get_entity()->get_name(), $userentitylog->name);

        // Check session collection.
        $sessioncollectionlogs = $DB->get_records('logstore_mentor_sesscoll2');
        self::assertCount(1, $sessioncollectionlogs);
        $sessioncollectionlog = reset($sessioncollectionlogs);
        self::assertArrayHasKey($sessioncollectionlog->sessionlogid, $sessionlogs);
        $sessionlog = $sessionlogs[$sessioncollectionlog->sessionlogid];
        self::assertEquals($session->id, $sessionlog->sessionid);
        self::assertArrayHasKey($sessioncollectionlog->collectionlogid, $collectionlogs);
        $collectionlog = $collectionlogs[$sessioncollectionlog->collectionlogid];
        self::assertEquals('accompagnement', $collectionlog->name);

        /**********************************************************************************/

        // Part 2 : update user profile.
        $profile->set_profile_field('region', 'Martinique');
        $event3 = \core\event\course_viewed::create(
            array('context' => $session->get_context(), 'other' => array('sample' => 5, 'xx' => 10)));

        // Start a 3rd view event.
        $store->write($event3);

        // Check logs.
        $logs = $DB->get_records('logstore_mentor_log2');
        self::assertCount(2, $logs);

        // Check user log.
        $userlogs = $DB->get_records('logstore_mentor_user2');
        self::assertCount(2, $userlogs);

        // Check region log.
        $regionlogs = $DB->get_records('logstore_mentor_region2');
        self::assertCount(3, $regionlogs); // One log for the entity and 2 logs for the user.

        // Check session log : still one session.
        $sessionlogs = $DB->get_records('logstore_mentor_session2');
        self::assertCount(1, $sessionlogs);

        /**********************************************************************************/

        // Part 3 update session status.
        $session->update_status(\local_mentor_core\session::STATUS_IN_PROGRESS);
        $event4 = \core\event\course_viewed::create(
            array('context' => $session->get_context(), 'other' => array('sample' => 5, 'xx' => 10)));

        // Start a 4th view event.
        $store->write($event4);

        // Check logs.
        $logs = $DB->get_records('logstore_mentor_log2');
        self::assertCount(3, $logs);

        // Check user log.
        $userlogs = $DB->get_records('logstore_mentor_user2');
        self::assertCount(2, $userlogs);

        // Check region log.
        $regionlogs = $DB->get_records('logstore_mentor_region2');
        self::assertCount(3, $regionlogs); // One log for the entity and 2 logs for the user.

        // Check session log.
        $sessionlogs = $DB->get_records('logstore_mentor_session2');
        self::assertCount(2, $sessionlogs);

        /**********************************************************************************/

        // Part 4 : create a new user.
        $user2 = $this->getDataGenerator()->create_user();
        $user2 = \local_mentor_core\profile_api::get_profile($user2->id);

        // Set the same main entity for the user and for the session.
        $user2->set_main_entity($session->get_entity());
        $user2->set_profile_field('region', 'Auvergne-Rhône-Alpes');
        $user2->set_profile_field('department', '07 - Ardèche');
        $user2->set_profile_field('category', 'A+');
        $user2->set_profile_field('status', 'Fonctionnaire');

        self::setUser($user2->id);
        self::getDataGenerator()->enrol_user($user2->id, $session->get_course()->id);

        $event5 = \core\event\course_viewed::create(
            array('context' => $session->get_context(), 'other' => array('sample' => 5, 'xx' => 10)));

        // Start a 5th view event.
        $store->write($event5);

        // Check logs.
        $logs = $DB->get_records('logstore_mentor_log2');

        self::assertCount(4, $logs);

        // Check user log.
        $userlogs = $DB->get_records('logstore_mentor_user2');
        self::assertCount(3, $userlogs);

        // Check region log.
        $regionlogs = $DB->get_records('logstore_mentor_region2');
        self::assertCount(3, $regionlogs); // One log for the entity and 2 logs for the user.

        // Check session log.
        $sessionlogs = $DB->get_records('logstore_mentor_session2');
        self::assertCount(2, $sessionlogs);

        self::resetAllData();
    }

    /**
     * Tests ignored case in log writing.
     *
     * @covers \logstore_mentor2\log\store::insert_event_entries
     * @covers \logstore_mentor2\log\store::write
     * @covers \logstore_mentor2\log\store::is_event_ignored
     * @covers \logstore_mentor2\log\store::flush
     * @throws ReflectionException
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function test_log_writing_nok() {
        global $DB;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();
        $this->preventResetByRollback(); // Logging waits till the transaction gets committed.

        self::setAdminUser();

        $newcourse = self::getDataGenerator()->create_course();
        $user = self::getDataGenerator()->create_user();

        // Course is not a session.
        $event1 = \core\event\course_viewed::create(
            array(
                'context' => context_course::instance($newcourse->id),
                'userid' => $user->id,
                'other' => array('sample' => 5, 'xx' => 10)
            )
        );

        $manager = get_log_manager(true);
        $store = new \logstore_mentor2\log\store($manager);
        $store->write($event1);

        $logs = $DB->get_records('logstore_mentor_log2');
        self::assertCount(0, $logs);

        // Wrong event.
        $event2 = \core\event\course_updated::create(
            array(
                'context' => context_course::instance($newcourse->id),
                'userid' => $user->id,
                'other' => array('sample' => 5, 'xx' => 10)
            )
        );

        $store = new \logstore_mentor2\log\store($manager);
        $store->write($event2);

        $sessionid = $this->init_session_creation();
        $session = \local_mentor_core\session_api::get_session($sessionid);
        $session->sessionstartdate = time();
        $session->update($session);

        $logs = $DB->get_records('logstore_mentor_log2');
        self::assertCount(0, $logs);

        $event3 = \core\event\course_viewed::create(
            array(
                'context' => $session->get_context(),
                'userid' => $user->id,
                'other' => array('sample' => 5, 'xx' => 10)
            )
        );
        $store->write($event3);

        // User is not enrol.
        $logs = $DB->get_records('logstore_mentor_log2');
        self::assertCount(0, $logs);

        self::getDataGenerator()->enrol_user($user->id, $session->get_course()->id);

        // User is enrol but user has no main entity.
        $event4 = \core\event\course_viewed::create(
            array(
                'context' => $session->get_context(),
                'userid' => $user->id,
                'other' => array('sample' => 5, 'xx' => 10)
            )
        );
        $store->write($event4);

        $logs = $DB->get_records('logstore_mentor_log2');
        self::assertCount(0, $logs);

        // Set main entity.
        $profile = \local_mentor_core\profile_api::get_profile($user->id);
        $profile->set_main_entity($session->get_entity());
        $profile->set_profile_field('region', 'Auvergne-Rhône-Alpes');
        $profile->set_profile_field('department', '07 - Ardèche');
        $profile->set_profile_field('category', 'A+');
        $profile->set_profile_field('status', 'Fonctionnaire');

        $event5 = \core\event\course_viewed::create(
            array(
                'context' => $session->get_context(),
                'userid' => $user->id,
                'other' => array('sample' => 5, 'xx' => 10)
            )
        );
        $store->write($event5);

        // User has main entity.
        $logs = $DB->get_records('logstore_mentor_log2');
        self::assertCount(1, $logs);

        // Delete session from database.
        $DB->delete_records('session', ['id' => $session->id]);
        $DB->delete_records('logstore_mentor_log2');

        $event6 = \core\event\course_viewed::create(
            array(
                'context' => $session->get_context(),
                'userid' => $user->id,
                'other' => array('sample' => 5, 'xx' => 10)
            )
        );
        $store->write($event6);

        $logs = $DB->get_records('logstore_mentor_log2');
        self::assertCount(0, $logs);

        self::resetAllData();
    }
}
