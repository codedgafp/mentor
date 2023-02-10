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
 * Library api tests
 *
 * @package    local_mentor_core
 * @copyright  2022 Edunao SAS (contact@edunao.com)
 * @author     rcolet <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/local/mentor_core/lib.php');
require_once($CFG->dirroot . '/local/library/lib.php');
require_once($CFG->dirroot . '/local/mentor_core/api/library.php');

class local_mentor_core_library_testcase extends advanced_testcase {

    /**
     * Init $CFG
     */
    public function init_config() {
        global $CFG;

        $CFG->mentor_specializations = [
            '\\local_mentor_specialization\\mentor_specialization' =>
                'local/mentor_specialization/classes/mentor_specialization.php'
        ];

        local_library_init_config();
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
     * Test test_get_library
     *
     * @covers  \local_mentor_core\library_api::get_library
     */
    public function test_get_library() {

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();
        $this->setOutputCallback(function() {
        });

        $library = \local_mentor_core\library_api::get_library();

        self::assertIsObject($library);
        self::assertInstanceOf('local_mentor_core\library', $library);

        self::resetAllData();
    }

    /**
     * Test create_library
     *
     * @covers  \local_mentor_core\library_api::create_library
     */
    public function test_create_library() {
        global $DB;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();
        $this->setOutputCallback(function() {
        });

        self::setAdminUser();

        // Library exist and add to config.
        self::assertIsString(get_config('core', \local_mentor_core\library::CONFIG_VALUE_ID));
        self::assertTrue(\local_mentor_core\library_api::create_library());

        // Create library and don't add to config.
        $category = core_course_category::get(get_config('core', \local_mentor_core\library::CONFIG_VALUE_ID));
        $category->delete_full(false);
        unset_config(\local_mentor_core\library::CONFIG_VALUE_ID);
        self::assertTrue(\local_mentor_core\library_api::create_library(false));
        self::assertFalse(get_config('core', \local_mentor_core\library::CONFIG_VALUE_ID));

        // Create library and add to config.
        $dbi      = \local_mentor_core\database_interface::get_instance();
        $category = core_course_category::get($dbi->get_library_object()->id);
        $category->delete_full(false);
        self::assertTrue(\local_mentor_core\library_api::create_library());
        self::assertIsString(get_config('core', \local_mentor_core\library::CONFIG_VALUE_ID));

        self::resetAllData();
    }

    /**
     * Test test_get_or_create_library
     *
     * @covers  \local_mentor_core\library_api::get_or_create_library
     */
    public function test_get_or_create_library() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();
        $this->setOutputCallback(function() {
        });

        self::setAdminUser();

        // Remove library entity.
        $dbi      = \local_mentor_core\database_interface::get_instance();
        $category = core_course_category::get($dbi->get_library_object()->id);
        $category->delete_full(false);
        unset_config(\local_mentor_core\library::CONFIG_VALUE_ID);

        // Create library.
        $library = \local_mentor_core\library_api::get_or_create_library();
        self::assertIsObject($library);
        self::assertInstanceOf('local_mentor_core\library', $library);

        // Get library.
        $library2 = \local_mentor_core\library_api::get_or_create_library();
        self::assertIsObject($library2);
        self::assertInstanceOf('local_mentor_core\library', $library2);
        self::assertEquals($library->id, $library2->id);

        self::resetAllData();
    }

    /**
     * Test get_library_id
     *
     * @covers  \local_mentor_core\library_api::get_library_id
     */
    public function test_get_library_id() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();
        $this->setOutputCallback(function() {
        });

        self::setAdminUser();

        $libraryid = \local_mentor_core\library_api::get_library_id();
        $cat       = core_course_category::get($libraryid);

        self::assertEquals($cat->name, \local_mentor_core\library::NAME);
        self::assertEquals($cat->idnumber, \local_mentor_core\library::SHORTNAME);

        self::resetAllData();
    }

    /**
     * Test set_library_id_to_config
     *
     * @covers  \local_mentor_core\library_api::set_library_id_to_config
     */
    public function test_set_library_id_to_config() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();
        $this->setOutputCallback(function() {
        });

        self::setAdminUser();

        $oldlibraryid = \local_mentor_core\library_api::get_library_id();
        $cat          = core_course_category::get($oldlibraryid);

        self::assertEquals($cat->name, \local_mentor_core\library::NAME);
        self::assertEquals($cat->idnumber, \local_mentor_core\library::SHORTNAME);

        $newlibraryid = 10;

        \local_mentor_core\library_api::set_library_id_to_config($newlibraryid);
        self::assertNotEquals($oldlibraryid, \local_mentor_core\library_api::get_library_id());
        self::assertEquals($newlibraryid, \local_mentor_core\library_api::get_library_id());

        self::resetAllData();
    }

    /**
     * Test publish_to_library
     *
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     *
     * @covers  \local_mentor_core\library_api::publish_to_library
     */
    public function test_publish_to_library() {
        global $DB;

        $this->resetAfterTest(true);
        $this->reset_singletons();

        self::setAdminUser();

        $entityid  = $this->init_create_entity();
        $training  = $this->init_create_training('trainingname', 'trainingshortname', $entityid);
        $user      = self::getDataGenerator()->create_user();

        self::setUser($user);

        $dbi = \local_mentor_core\database_interface::get_instance();
        self::assertFalse($dbi->get_library_publication($training->id));
        self::assertFalse($DB->get_record('task_adhoc', array('classname' => '\local_library\task\publication_library_task')));

        \local_mentor_core\library_api::publish_to_library($training->id);

        self::assertFalse($dbi->get_library_publication($training->id));
        $publicationlibrarytask = $DB->get_record(
            'task_adhoc',
            array('classname' => '\local_library\task\publication_library_task')
        );
        $customdata             = json_decode($publicationlibrarytask->customdata);
        self::assertEquals($customdata->trainingid, $training->id);
        self::assertEquals($customdata->userid, $user->id);

        $DB->delete_records('task_adhoc');

        \local_mentor_core\library_api::publish_to_library($training->id, true);

        self::assertFalse($DB->get_record('task_adhoc', array('classname' => '\local_library\task\publication_library_task')));
        $traininglibrary = $dbi->get_library_publication($training->id);
        self::assertEquals($traininglibrary->originaltrainingid, $training->id);
        self::assertEquals($traininglibrary->timecreated, $traininglibrary->timemodified);
        self::assertEquals($traininglibrary->userid, $user->id);

        self::resetAllData();
    }

    /**
     * Test get_library_publication
     *
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     *
     * @covers  \local_mentor_core\library_api::get_library_publication
     */
    public function test_get_library_publication() {
        $this->resetAfterTest(true);
        $this->reset_singletons();

        self::setAdminUser();

        $entityid = $this->init_create_entity();
        $training = $this->init_create_training('trainingname', 'trainingshortname', $entityid);

        $dbi = \local_mentor_core\database_interface::get_instance();
        self::assertFalse(local_mentor_core\library_api::get_library_publication($training->id));

        \local_mentor_core\library_api::publish_to_library($training->id, true);

        $traininglibrary = local_mentor_core\library_api::get_library_publication($training->id);
        self::assertEquals($traininglibrary->originaltrainingid, $training->id);

        self::resetAllData();
    }
}
