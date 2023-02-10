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
 * Test cases for mentor_training class
 *
 * @package    local_mentor_specialzation
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     rcolet <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/local/mentor_core/api/library.php');
require_once($CFG->dirroot . '/local/mentor_core/api/training.php');
require_once($CFG->dirroot . '/local/mentor_specialization/classes/models/mentor_library.php');

class local_mentor_specialization_library_class_testcase extends advanced_testcase {

    public const UNAUTHORISED_CODE = 2020120810;

    /**
     * Tests set up.
     */
    public function setUp() {
        $this->resetAfterTest(false);
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

    public function init_competencies() {
        global $CFG;

        $text      = file_get_contents($CFG->dirroot .
                                       '/local/mentor_specialization/data/competencies/competencies_comma_separated.csv');
        $encoding  = 'UTF-8';
        $delimiter = 'comma';
        $importer  = new \tool_lpimportcsv\framework_importer($text, $encoding, $delimiter, 0, null, true);
        $importer->import();
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
                'regions'   => [5], // Corse.
                'userid'    => 2  // Set the admin user as manager of the entity.
            ],
        ];
    }

    public function create_entity($entityname) {
        $entityid = \local_mentor_core\entity_api::create_entity(['name' => $entityname, 'shortname' => $entityname]);
        return \local_mentor_core\entity_api::get_entity($entityid);
    }

    public function create_training($entity, $shorntname = 'shortname') {

        $trainingdata                               = new stdClass();
        $trainingdata->name                         = 'fullname';
        $trainingdata->shortname                    = $shorntname;
        $trainingdata->teaser                       = 'http://www.edunao.com/';
        $trainingdata->teaserpicture                = '';
        $trainingdata->prerequisite                 = 'TEST';
        $trainingdata->collection                   = 'accompagnement';
        $trainingdata->traininggoal                 = 'TEST TRAINING ';
        $trainingdata->idsirh                       = 'TEST ID SIRH';
        $trainingdata->licenseterms                 = 'cc-sa';
        $trainingdata->typicaljob                   = 'TEST';
        $trainingdata->skills                       = ['FP2SF001', 'FP2SF002'];
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
        $formationid                                = $entity->get_entity_formation_category();
        $trainingdata->categorychildid              = $formationid;
        $trainingdata->categoryid                   = $entity->id;
        $trainingdata->creativestructure            = $entity->id;
        return \local_mentor_core\training_api::create_training($trainingdata);
    }

    /**
     * Test get_trainings
     *
     * @covers \local_mentor_specialization\mentor_library::__construct
     * @covers \local_mentor_specialization\mentor_library::get_trainings
     */
    public function test_get_trainings_library() {
        global $DB;

        $this->resetAfterTest(true);
        $this->reset_singletons();
        $this->init_config();
        $this->init_competencies();

        self::setAdminUser();

        $library = \local_mentor_core\library_api::get_or_create_library();

        self::assertEmpty($library->get_trainings());

        $entity   = $this->create_entity('Entity');
        $training = $this->create_training($entity);
        \local_mentor_core\library_api::publish_to_library($training->id, true);

        $newtraininglibraryid = $DB->get_field('library', 'trainingid', array('originaltrainingid' => $training->id));

        $trainingslibrary = $library->get_trainings();

        self::assertCount(1, $trainingslibrary);
        self::assertArrayHasKey($newtraininglibraryid, $trainingslibrary);

        $training2 = $this->create_training($entity, 'shorname1');
        \local_mentor_core\library_api::publish_to_library($training2->id, true);

        $newtraininglibraryid2 = $DB->get_field('library', 'trainingid', array('originaltrainingid' => $training2->id));

        $trainingslibrary = $library->get_trainings();

        self::assertCount(2, $trainingslibrary);
        self::assertArrayHasKey($newtraininglibraryid, $trainingslibrary);
        self::assertArrayHasKey($newtraininglibraryid2, $trainingslibrary);

        self::resetAllData();
    }
}
