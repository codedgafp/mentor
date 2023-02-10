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

use local_mentor_core\session;
use local_mentor_core\entity_api;
use local_mentor_core\session_api;
use local_mentor_core\training_api;
use local_mentor_core\training;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/local/mentor_core/api/training.php');
require_once($CFG->dirroot . '/local/mentor_specialization/classes/models/mentor_training.php');

class local_mentor_specialization_training_class_testcase extends advanced_testcase {

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

    public function create_training($entity) {

        $trainingdata                               = new stdClass();
        $trainingdata->name                         = 'fullname';
        $trainingdata->shortname                    = 'shortname';
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
     * Test prepare edit form
     *
     * @covers \local_mentor_specialization\mentor_training::prepare_edit_form
     * @covers \local_mentor_core\training::prepare_edit_form
     */
    public function test_mentor_specialization_prepare_edit_form_ok() {
        global $CFG;

        $this->resetAfterTest(true);
        $this->reset_singletons();
        $this->init_config();

        self::setAdminUser();

        $entity   = $this->create_entity('Entity');
        $training = $this->create_training($entity);

        $editform = $training->prepare_edit_form();

        self::assertEquals($editform->name, $training->name);
        self::assertEquals($editform->shortname, $training->shortname);
        self::assertEquals($editform->contextid, $training->get_context()->id);

        self::assertEmpty($editform->producerorganizationlogo);
        self::assertEmpty($editform->teaserpicture);

        self::assertFalse(isset($editform->presenceestimatedtimehours));
        self::assertFalse(isset($editform->presenceestimatedtimeminutes));

        self::assertFalse(isset($editform->remoteestimatedtimehours));
        self::assertFalse(isset($editform->remoteestimatedtimeminutes));

        // Create producer organization logo file.
        $fs                    = get_file_storage();
        $component             = 'local_trainings';
        $itemid                = $training->id;
        $filearea              = 'producerorganizationlogo';
        $contextid             = $training->get_context()->id;
        $filerecord            = new stdClass();
        $filerecord->contextid = $contextid;
        $filerecord->component = $component;
        $filerecord->filearea  = $filearea;
        $filerecord->itemid    = $itemid;
        $filerecord->filepath  = '/';
        $filerecord->filename  = 'logo.png';
        $filepath              = $CFG->dirroot . '/local/mentor_core/pix/logo.png';
        $fs->create_file_from_pathname($filerecord, $filepath);

        // Create teaser picture file.
        $filearea             = 'teaserpicture';
        $filerecord->filearea = $filearea;
        $fs->create_file_from_pathname($filerecord, $filepath);

        $data                        = new \stdClass();
        $data->presenceestimatedtime = 185;
        $data->remoteestimatedtime   = 75;
        $data->timecreated           = time();
        $training->update($data);

        $editform = $training->prepare_edit_form();

        self::assertIsInt($editform->producerorganizationlogo);
        self::assertIsInt($editform->teaserpicture);

        self::assertEquals(3, $editform->presenceestimatedtimehours);
        self::assertEquals(5, $editform->presenceestimatedtimeminutes);

        self::assertEquals(1, $editform->remoteestimatedtimehours);
        self::assertEquals(15, $editform->remoteestimatedtimeminutes);

        self::resetAllData();
    }

    /**
     * Test duplicate
     *
     * @covers \local_mentor_specialization\mentor_training::duplicate
     * @covers \local_mentor_core\training::duplicate
     */
    public function test_mentor_specialization_duplicate_ok() {
        $this->resetAfterTest(true);
        $this->reset_singletons();
        $this->init_config();

        self::setAdminUser();

        $entity   = $this->create_entity('Entity');
        $training = $this->create_training($entity);

        $duplicateshortname = 'duplicate_shortname';
        $duplicatetraining  = $training->duplicate($duplicateshortname);

        self::assertEquals($training->name, $duplicatetraining->name);
        self::assertEquals($training->get_entity()->id, $duplicatetraining->get_entity()->id);
        self::assertEquals($duplicateshortname, $duplicatetraining->shortname);

        self::resetAllData();
    }

    /**
     * Test get skills name
     *
     * @covers \local_mentor_specialization\mentor_training::get_skills_name
     */
    public function test_mentor_specialization_get_skills_name_ok() {
        $this->resetAfterTest(true);
        $this->setOutputCallback(function() {
        });
        $this->reset_singletons();
        $this->init_config();
        $this->init_competencies();

        self::setAdminUser();

        $entity         = $this->create_entity('Entity');
        $training       = $this->create_training($entity);
        $trainingskills = $training->skills;

        $db     = \local_mentor_specialization\database_interface::get_instance();
        $skills = $db->get_skills();

        $skillsnamestring = '';

        foreach (explode(',', $trainingskills) as $trainingskill) {
            $skillsnamestring .= $skills[$trainingskill] . '<br />';
        }

        self::assertEquals($skillsnamestring, $training->get_skills_name());

        self::resetAllData();
    }

    /**
     * Test get_actions
     *
     * @covers \local_mentor_specialization\mentor_training::get_actions
     */
    public function test_mentor_specialization_get_actions_ok() {
        $this->resetAfterTest(true);
        $this->reset_singletons();
        $this->init_config();
        $this->init_competencies();

        self::setAdminUser();

        $entity   = $this->create_entity('Entity');
        $training = $this->create_training($entity);

        self::assertArrayNotHasKey('deletetraining', $training->get_actions());

        $data         = new \stdClass();
        $data->status = \local_mentor_core\training::STATUS_ARCHIVED;
        $training->update($data);

        self::assertArrayHasKey('deletetraining', $training->get_actions());

        self::resetAllData();
    }

    /**
     * Test update collection
     *
     * @covers \local_mentor_specialization\mentor_training::update
     */
    public function test_mentor_specialization_update_collection_ok() {
        $this->resetAfterTest(true);
        $this->reset_singletons();
        $this->init_config();
        $this->init_competencies();

        self::setAdminUser();

        $entity   = $this->create_entity('Entity');
        $training = $this->create_training($entity);

        // Check actual collection.
        self::assertEquals('accompagnement', $training->collection);

        $data             = new stdClass();
        $data->collection = 'collection1';

        $training->update($data);

        // Check update with one collection.
        self::assertEquals('collection1', $training->collection);

        $data->collection = '';

        $training->update($data);

        // Check with empty collection.
        self::assertEmpty($training->collection);

        $data->collection = ['collection1', 'collection2'];

        $training->update($data);

        // Check with multiple collection.
        self::assertEquals('collection1,collection2', $training->collection);

        self::resetAllData();
    }

    /**
     * Test update skills
     *
     * @covers \local_mentor_specialization\mentor_training::update
     */
    public function test_mentor_specialization_update_skills_ok() {
        $this->resetAfterTest(true);
        $this->reset_singletons();
        $this->init_config();
        $this->init_competencies();

        self::setAdminUser();

        $entity   = $this->create_entity('Entity');
        $training = $this->create_training($entity);

        // Check actual skills.
        self::assertEquals('FP2SF001,FP2SF002', $training->skills);

        $data         = new stdClass();
        $data->skills = 'FP2SF003';

        $training->update($data);

        // Check update with one skills.
        self::assertEquals('FP2SF003', $training->skills);

        $data->skills = '';

        $training->update($data);

        // Check with empty skills.
        self::assertEmpty($training->skills);

        $data->skills = ['FP2SF004', 'FP2SF005'];

        $training->update($data);

        // Check with multiple skills.
        self::assertEquals('FP2SF004,FP2SF005', $training->skills);

        self::resetAllData();
    }

    /**
     * Test update not ok
     *
     * @covers \local_mentor_specialization\mentor_training::update
     */
    public function test_mentor_specialization_update_nok() {

        $this->resetAfterTest(true);
        $this->reset_singletons();
        $this->init_config();
        $this->init_competencies();

        self::setAdminUser();

        $entity   = $this->create_entity('Entity');
        $training = $this->create_training($entity);

        // Create database interface Mock.
        $dbinterfacemock = $this->getMockBuilder('\local_mentor_core\database_interface')
            ->setMethods(['update_training'])
            ->disableOriginalConstructor()
            ->getMock();

        // Return false value when update_training function call.
        $dbinterfacemock->expects($this->any())
            ->method('update_training')
            ->will($this->returnValue(false));

        $reflection         = new ReflectionClass($training);
        $reflectionproperty = $reflection->getProperty('dbinterface');
        $reflectionproperty->setAccessible(true);
        $reflectionproperty->setValue($training, $dbinterfacemock);

        $data = new stdClass();

        try {
            $training->update($data);
        } catch (\Exception $e) {
            self::assertInstanceOf('Exception', $e);
        }

        self::resetAllData();
    }

    /**
     * Test convert_for_template show thumbnail
     *
     * @covers \local_mentor_specialization\mentor_training::convert_for_template
     * @covers \local_mentor_core\database_interface::get_files_by_component_order_by_filearea
     */
    public function test_mentor_specialization_convert_for_template_thumbnail_nok() {
        global $CFG;

        $this->resetAfterTest(true);
        $this->reset_singletons();
        $this->init_config();
        $this->init_competencies();

        self::setAdminUser();

        $entity   = $this->create_entity('Entity');
        $training = $this->create_training($entity);

        // Create thumbnail file.
        $fs                    = get_file_storage();
        $component             = 'local_trainings';
        $itemid                = $training->id;
        $filearea              = 'thumbnail';
        $contextid             = $training->get_context()->id;
        $filerecord            = new stdClass();
        $filerecord->contextid = $contextid;
        $filerecord->component = $component;
        $filerecord->filearea  = $filearea;
        $filerecord->itemid    = $itemid;
        $filerecord->filepath  = '/';
        $filerecord->filename  = 'logo.png';
        $filepath              = $CFG->dirroot . '/local/mentor_core/pix/logo.png';
        $fs->create_file_from_pathname($filerecord, $filepath);

        self::assertEquals($training->convert_for_template()->thumbnail,
            \moodle_url::make_pluginfile_url(
                $filerecord->contextid,
                $filerecord->component,
                $filerecord->filearea,
                $filerecord->itemid,
                $filerecord->filepath,
                $filerecord->filename
            )->out()
        );

        self::resetAllData();
    }

    /**
     * Test convert_for_template show producing organization logo
     *
     * @covers \local_mentor_specialization\mentor_training::convert_for_template
     */
    public function test_mentor_specialization_convert_for_template_producer_organization_logo_nok() {
        global $CFG;

        $this->resetAfterTest(true);
        $this->reset_singletons();
        $this->init_config();
        $this->init_competencies();

        self::setAdminUser();

        $entity   = $this->create_entity('Entity');
        $training = $this->create_training($entity);

        // Create producing organization logo file.
        $fs                    = get_file_storage();
        $component             = 'local_trainings';
        $itemid                = $training->id;
        $filearea              = 'producerorganizationlogo';
        $contextid             = $training->get_context()->id;
        $filerecord            = new stdClass();
        $filerecord->contextid = $contextid;
        $filerecord->component = $component;
        $filerecord->filearea  = $filearea;
        $filerecord->itemid    = $itemid;
        $filerecord->filepath  = '/';
        $filerecord->filename  = 'logo.png';
        $filepath              = $CFG->dirroot . '/local/mentor_core/pix/logo.png';
        $fs->create_file_from_pathname($filerecord, $filepath);

        self::assertEquals($training->convert_for_template()->producingorganizationlogo,
            \moodle_url::make_pluginfile_url(
                $filerecord->contextid,
                $filerecord->component,
                $filerecord->filearea,
                $filerecord->itemid,
                $filerecord->filepath,
                $filerecord->filename
            )->out()
        );

        self::resetAllData();
    }

    /**
     * Test get_file_url
     *
     * @covers \local_mentor_specialization\mentor_training::get_file_url
     */
    public function test_get_file_url() {
        global $CFG;

        $this->resetAfterTest(true);
        $this->reset_singletons();
        $this->init_config();
        $this->init_competencies();

        self::setAdminUser();

        $entity   = $this->create_entity('Entity');
        $training = $this->create_training($entity);

        self::assertNull($training->get_file_url());

        // Create thumbnail file.
        $fs                    = get_file_storage();
        $component             = 'local_trainings';
        $itemid                = $training->id;
        $filearea              = 'thumbnail';
        $contextid             = $training->get_context()->id;
        $filerecord            = new stdClass();
        $filerecord->contextid = $contextid;
        $filerecord->component = $component;
        $filerecord->filearea  = $filearea;
        $filerecord->itemid    = $itemid;
        $filerecord->filepath  = '/';
        $filerecord->filename  = 'logo.png';
        $filepath              = $CFG->dirroot . '/local/mentor_core/pix/logo.png';
        $fs->create_file_from_pathname($filerecord, $filepath);

        self::assertNotNull($training->get_file_url());

        // With specific file.
        self::assertNull($training->get_file_url('specificfile'));

        // Create thumbnail file.
        $fs                    = get_file_storage();
        $component             = 'local_trainings';
        $itemid                = $training->id;
        $filearea              = 'specificfile';
        $contextid             = $training->get_context()->id;
        $filerecord            = new stdClass();
        $filerecord->contextid = $contextid;
        $filerecord->component = $component;
        $filerecord->filearea  = $filearea;
        $filerecord->itemid    = $itemid;
        $filerecord->filepath  = '/';
        $filerecord->filename  = 'logo.png';
        $filepath              = $CFG->dirroot . '/local/mentor_core/pix/logo.png';
        $fs->create_file_from_pathname($filerecord, $filepath);

        self::assertNotNull($training->get_file_url('specificfile'));

        self::resetAllData();
    }

    /**
     * Test get_modality_name
     *
     * @covers \local_mentor_specialization\mentor_training::get_modality_name
     */
    public function test_get_modality_name() {
        $this->resetAfterTest(true);
        $this->reset_singletons();
        $this->init_config();
        $this->init_competencies();

        self::setAdminUser();

        $entity   = $this->create_entity('Entity');
        $training = $this->create_training($entity);

        $training->trainingmodalities = '';

        self::assertEquals($training->get_modality_name(), 'emptychoice');

        $training->trainingmodalities = 'p';

        self::assertEquals($training->get_modality_name(), 'presentiel');

        $training->trainingmodalities = 'd';

        self::assertEquals($training->get_modality_name(), 'online');

        $training->trainingmodalities = 'dp';

        self::assertEquals($training->get_modality_name(), 'mixte');

        self::resetAllData();
    }
}
