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
 * Publication library task tests
 *
 * @package    local_library
 * @copyright  2022 Edunao SAS (contact@edunao.com)
 * @author     rcolet <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/local/library/lib.php');

class publication_library_task_testcase extends advanced_testcase {

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
     * Init training object
     *
     * @return stdClass
     * @throws dml_exception
     * @throws moodle_exception
     */
    private function get_training_data($entitydata = null, $shortname = 'shortname') {

        if ($entitydata === null) {
            $entitydata = $this->get_entities_data()[0];
        }

        // Init test data.
        $trainingdata = new stdClass();

        $trainingdata->name      = 'fullname';
        $trainingdata->shortname = $shortname;
        $trainingdata->content   = 'summary';

        // Create training object.
        $trainingdata->traininggoal = 'TEST TRAINING';
        $trainingdata->thumbnail    = '';
        $trainingdata->status       = \local_mentor_core\training::STATUS_ELABORATION_COMPLETED;

        try {
            // Get entity object for default category.
            $entityid = \local_mentor_core\entity_api::create_entity($entitydata);

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
     * Test local_library_init_config
     * Field missing
     *
     * @covers  \local_library\task\publication_library_task::execute
     */
    public function test_execute_nok() {
        global $USER;

        $this->resetAfterTest(true);
        $this->reset_singletons();

        self::setAdminUser();

        $customdata         = new stdClass();
        $customdata->userid = $USER->id;

        $task = new \local_library\task\publication_library_task();
        $task->set_userid($USER->id);
        $task->set_custom_data($customdata);

        // Missing sessionname.
        try {
            $task->execute();
            self::fail();
        } catch (\Exception $e) {
            // Session course has already been deleted.
            self::assertInstanceOf('coding_exception', $e);
            self::assertEquals(
                'Coding error detected, it must be fixed by a programmer: Field trainingid is missing in custom data',
                $e->getMessage()
            );
        }

        self::resetAllData();
    }

    /**
     * Test local_library_init_config
     *
     * @covers  \local_library\task\publication_library_task::execute
     */
    public function test_execute_ok() {
        $this->resetAfterTest(true);
        $this->reset_singletons();
        $this->setOutputCallback(function() {
        });

        self::setAdminUser();

        local_library_init_config();

        $trainingdata           = $this->get_training_data(
            [
                'name'      => 'New Entity 1',
                'shortname' => 'New Entity 1'
            ]
        );
        $training               = \local_mentor_core\training_api::create_training($trainingdata);
        $user                   = $this->getDataGenerator()->create_user();
        $customdata             = new stdClass();
        $customdata->userid     = $user->id;
        $customdata->trainingid = $training->id;

        self::assertFalse(\local_mentor_core\library_api::get_library_publication($training->id));

        // Create.
        $task = new \local_library\task\publication_library_task();
        $task->set_userid($user->id);
        $task->set_custom_data($customdata);

        try {
            $task->execute();
        } catch (\Exception $e) {
            self::fail();
        }

        $traininglibraryobject = \local_mentor_core\library_api::get_library_publication($training->id);
        try {
            // Is training.
            $traininglibrary = \local_mentor_core\training_api::get_training($traininglibraryobject->trainingid);
        } catch (\Exception $e) {
            self::fail();
        }

        self::assertIsObject($traininglibraryobject);
        // Is new training.
        self::assertEquals($traininglibraryobject->trainingid, $traininglibrary->id);
        // Is original training.
        self::assertEquals($traininglibraryobject->originaltrainingid, $training->id);
        // Is same time.
        self::assertEquals($traininglibraryobject->timecreated, $traininglibraryobject->timemodified);

        sleep(2);

        // Update.
        $task = new \local_library\task\publication_library_task();
        $task->set_userid($user->id);
        $task->set_custom_data($customdata);
        try {
            $task->execute();
        } catch (\Exception $e) {
            self::fail();
        }

        $traininglibraryobject2 = \local_mentor_core\library_api::get_library_publication($training->id);

        try {
            // Is training.
            $traininglibrary2 = \local_mentor_core\training_api::get_training($traininglibraryobject2->trainingid);
        } catch (\Exception $e) {
            self::fail();
        }

        // Same id.
        self::assertEquals($traininglibraryobject->id, $traininglibraryobject2->id);
        // Is new training.
        self::assertNotEquals($traininglibraryobject->trainingid, $traininglibraryobject2->trainingid);
        self::assertNotEquals($traininglibraryobject2->trainingid, $traininglibrary->id);
        self::assertEquals($traininglibraryobject2->trainingid, $traininglibrary2->id);
        // Same original training.
        self::assertEquals($traininglibraryobject->originaltrainingid, $traininglibraryobject2->originaltrainingid);
        // Same timecreated.
        self::assertEquals($traininglibraryobject->timecreated, $traininglibraryobject2->timecreated);
        // Timemodified update.
        self::assertNotEquals($traininglibraryobject->timemodified, $traininglibraryobject2->timemodified);
        self::assertTrue(
            intval($traininglibraryobject->timemodified) < intval($traininglibraryobject2->timemodified)
        );

        self::resetAllData();
    }
}
