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
 * Block Mysessions controller tests
 *
 * @package    block_mysession
 * @copyright  2022 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

class block_mysessions_favourite_controller_testcase extends advanced_testcase {
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

    public function call_controller($params = []) {
        // Call front controller.
        return new \local_mentor_core\front_controller('mysessions', 'block_mysessions\\', $params);
    }

    /**
     * Initialization of the entity data
     *
     * @param string $entityname
     * @return int
     */
    public function init_create_entity($entityname = 'New Entity 1') {

        $entitydata = [
            'name'      => $entityname,
            'shortname' => $entityname
        ];

        // Test standard Entity creation.
        try {
            $entityid = \local_mentor_core\entity_api::create_entity($entitydata);
        } catch (\Exception $e) {
            // Failed if the name of this entity is already in use.
            self::fail($e->getMessage());
        }

        return $entityid;
    }

    /**
     * Create a training
     *
     * @param $name
     * @param $shortname
     * @param $entityid
     * @return mixed
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function init_create_training($name, $shortname, $entityid) {
        // Init test data.
        $trainingdata = new stdClass();

        $trainingdata->name      = $name;
        $trainingdata->shortname = $shortname;
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
            $entity = \local_mentor_core\entity_api::get_entity($entityid);
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        // Fill with entity data.
        $formationid                     = $entity->get_entity_formation_category();
        $trainingdata->categorychildid   = $formationid;
        $trainingdata->categoryid        = $entity->id;
        $trainingdata->creativestructure = $entity->id;

        return \local_mentor_core\training_api::create_training($trainingdata);
    }

    /**
     * Initalize a session
     *
     * @return local_mentor_core\session
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function init_create_session() {
        $entityid = $this->init_create_entity();
        $training = $this->init_create_training('trainingname', 'trainingshortname', $entityid);

        $sessionname = 'sessionname';

        try {
            $session = \local_mentor_core\session_api::create_session($training->id, $sessionname, true);
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        $data         = new stdClass();
        $data->opento = 'current_entity';
        $session->update($data);

        return $session;
    }

    /**
     * Test add_favourite function
     *
     * @covers block_mysessions\session_favourite_controller::execute
     * @covers block_mysessions\session_favourite_controller::add_favourite
     */
    public function test_add_favourite() {
        global $DB, $USER;

        $this->resetAfterTest(true);
        $this->reset_singletons();
        self::setAdminUser();

        $session = $this->init_create_session();

        $DB->delete_records('user_info_field');
        $user = self::getDataGenerator()->create_user();
        self::setUser($user);

        $DB->delete_records('favourite');

        // Not favourite.
        self::assertFalse($DB->record_exists('favourite', array('userid' => $user->id)));

        $params = [
            'plugintype' => 'blocks',
            'controller' => 'session_favourite',
            'action'     => 'add_favourite',
            'format'     => 'json',
            'sessionid'  => $session->id,
        ];

        // Call front controller.
        $frontcontroller = $this->call_controller($params);

        // Execute and create favourite.
        $executeresult = $frontcontroller->execute();
        self::assertTrue($executeresult['success']);

        // New favourite.
        self::assertTrue($DB->record_exists('favourite', array('userid' => $user->id)));

        // Get favourite.
        $userfavourite = $DB->get_records('favourite', array('userid' => $user->id));

        // Check data favourite.
        self::assertCount(1, $userfavourite);
        $userfavouriteid = current($userfavourite)->id;
        self::assertEquals($userfavourite[$userfavouriteid]->component, 'local_session');
        self::assertEquals($userfavourite[$userfavouriteid]->itemtype, 'favourite_session');
        self::assertEquals($userfavourite[$userfavouriteid]->itemid, $session->id);
        self::assertEquals($userfavourite[$userfavouriteid]->contextid, $session->get_context()->id);
        self::assertEquals($userfavourite[$userfavouriteid]->userid, $user->id);

        self::resetAllData();
    }

    /**
     * Test remove_favourite function
     *
     * @covers block_mysessions\session_favourite_controller::execute
     * @covers block_mysessions\session_favourite_controller::remove_favourite
     */
    public function test_remove_favourite() {
        global $DB;

        $this->resetAfterTest(true);
        $this->reset_singletons();
        self::setAdminUser();

        $session = $this->init_create_session();

        $DB->delete_records('user_info_field');
        $user = self::getDataGenerator()->create_user();
        self::setUser($user);

        $DB->delete_records('favourite');

        // Not favourite.
        self::assertFalse($DB->record_exists('favourite', array('userid' => $user->id)));

        // Add favourite with defined user.
        $favourite               = new \stdClass();
        $favourite->component    = 'local_session';
        $favourite->itemtype     = 'favourite_session';
        $favourite->itemid       = $session->id;
        $favourite->contextid    = $session->get_context()->id;
        $favourite->userid       = $user->id;
        $favourite->timecreated  = time();
        $favourite->timemodified = time();
        $favouriteid1            = $DB->insert_record('favourite', $favourite);

        $favourites = $DB->get_records('favourite');
        self::assertCount(1, $favourites);
        self::assertArrayHasKey($favouriteid1, $favourites);

        $params = [
            'plugintype' => 'blocks',
            'controller' => 'session_favourite',
            'action'     => 'remove_favourite',
            'format'     => 'json',
            'sessionid'  => $session->id,
        ];

        // Call front controller.
        $frontcontroller = $this->call_controller($params);

        // Execute and create favourite.
        $executeresult = $frontcontroller->execute();
        self::assertTrue($executeresult['success']);

        $favourites = $DB->get_records('favourite');
        self::assertEmpty($favourites);

        self::resetAllData();
    }
}
