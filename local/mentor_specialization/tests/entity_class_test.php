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
 * Test cases for class mentor_entity
 *
 * @package    local_mentor_specialization
 * @copyright  2021 Edunao SAS (contact@edunao.com)
 * @author     adrien <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/local/mentor_core/api/entity.php');
require_once($CFG->dirroot . '/local/mentor_core/classes/model/entity.php');

class local_mentor_specialization_entity_class_testcase extends advanced_testcase {

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
                        'name' => 'New Entity 1',
                        'shortname' => 'New Entity 1',
                        'regions' => [5], // Corse.
                ],
                [
                        'name' => 'New Entity 2',
                        'shortname' => 'New Entity 2',
                ],
        ];
    }

    /**
     * Init default role if remove by specialization
     *
     * @throws coding_exception
     * @throws dml_exception
     */
    public function init_role() {
        global $DB;

        $db = \local_mentor_core\database_interface::get_instance();
        $manager = $db->get_role_by_name('manager');

        if (!$manager) {
            $otherrole = $DB->get_record('role', array('archetype' => 'manager'), '*', IGNORE_MULTIPLE);
            $this->duplicate_role($otherrole->shortname, 'manager', 'Manager',
                    'manager');
        }
    }

    /**
     * Test update region
     *
     * @covers \local_mentor_specialization\mentor_entity::update_regions
     * @covers \local_mentor_specialization\mentor_entity::get_members
     * @covers \local_mentor_specialization\mentor_entity::remove_member
     * @covers \local_mentor_specialization\mentor_entity::add_member
     */
    public function test_update_region_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $entitydata = $this->get_entities_data()[0];
        $entityid = \local_mentor_core\entity_api::create_entity($entitydata);
        $entity = new \local_mentor_specialization\mentor_entity($entityid);

        self::assertEquals($entity->regions, $entitydata['regions']);

        // Grand Est.
        $entity->update_regions('6');

        self::assertEquals($entity->regions, [6]);

        self::resetAllData();
    }

    /**
     * Test update
     * Update region
     *
     * @covers \local_mentor_specialization\mentor_entity::update
     */
    public function test_update_ok_update_region() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $entitydata = $this->get_entities_data()[0];
        $entityid = \local_mentor_core\entity_api::create_entity($entitydata);
        $entity = new \local_mentor_specialization\mentor_entity($entityid);

        self::assertEquals($entity->regions, $entitydata['regions']);

        $entity->regions = ['6'];

        // Grand Est.
        $entity->update($entity);

        self::assertEquals($entity->regions, [6]);

        self::resetAllData();
    }

    /**
     * Test update
     * Update sirh list
     *
     * @covers \local_mentor_specialization\mentor_entity::update
     */
    public function test_update_ok_update_sirh_list() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $entitydata = $this->get_entities_data()[0];
        $entityid = \local_mentor_core\entity_api::create_entity($entitydata);
        $entity = new \local_mentor_specialization\mentor_entity($entityid);

        self::assertCount(0, $entity->get_sirh_list());
        self::assertCount(0, $entity->get_sirh_list(true));

        $data = new stdClass();
        $data->id = $entity->id;
        $data->name = $entity->name;
        $data->sirhlist = ['SIRHTEST'];
        $entity->update($data);

        $enrirysirh = $entity->get_sirh_list();
        self::assertCount(1, $enrirysirh);
        self::assertEquals(['SIRHTEST'], $enrirysirh);

        self::resetAllData();
    }

    /**
     * Test get sirh list
     * Update visibility
     *
     * @covers \local_mentor_specialization\mentor_entity::update
     */
    public function test_update_ok_update_visibility() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $entitydata = $this->get_entities_data()[0];
        $entityid = \local_mentor_core\entity_api::create_entity($entitydata);
        $entity = new \local_mentor_specialization\mentor_entity($entityid);

        $data = new stdClass();
        $data->id = $entity->id;
        $data->name = $entity->name;
        $data->hidden = 1;
        $entity->update($data);

        self::assertEquals(1, $entity->is_hidden());

        $data = new stdClass();
        $data->id = $entity->id;
        $data->name = $entity->name;
        $data->hidden = 0;
        $entity->update($data);

        self::assertEquals(0, $entity->is_hidden());

        self::resetAllData();
    }

    /**
     * Test get form data
     *
     * @covers \local_mentor_core\entity::get_form_data
     * @covers \local_mentor_specialization\mentor_entity::get_form_data
     * @covers \local_mentor_specialization\mentor_entity::is_main_entity
     * @covers \local_mentor_specialization\mentor_entity::get_regions_id
     */
    public function test_get_form_data_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $entitydata = $this->get_entities_data()[0];
        $entityid = \local_mentor_core\entity_api::create_entity($entitydata);
        $entity = new \local_mentor_specialization\mentor_entity($entityid);

        $formdata = $entity->get_form_data();

        self::assertObjectHasAttribute('regions', $formdata);
        self::assertEquals($formdata->regions, $entitydata['regions']);

        self::resetAllData();
    }

    /**
     * Test get trainings
     *
     * @covers \local_mentor_specialization\mentor_entity::get_trainings
     */
    public function test_get_trainings_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $entitydata = $this->get_entities_data()[0];
        $entityid = \local_mentor_core\entity_api::create_entity($entitydata);
        $entity = new \local_mentor_specialization\mentor_entity($entityid);

        // No training.
        self::assertCount(0, $entity->get_trainings());

        // No training.
        self::assertCount(0, $entity->get_trainings());

        // Init training data.
        $trainingdata = new stdClass();
        $trainingdata->name = 'fullname';
        $trainingdata->shortname = 'shortname';
        $trainingdata->traininggoal = 'TEST TRAINING';
        $trainingdata->thumbnail = '';
        $trainingdata->status = \local_mentor_core\training::STATUS_DRAFT;
        $trainingdata->categorychildid = $entity->get_entity_formation_category();
        $trainingdata->categoryid = $entity->id;
        $trainingdata->creativestructure = $entity->id;
        $trainingdata->teaser = 'http://www.edunao.com/';
        $trainingdata->teaserpicture = '';
        $trainingdata->prerequisite = 'TEST';
        $trainingdata->collection = 'accompagnement';
        $trainingdata->idsirh = 'TEST ID SIRH';
        $trainingdata->licenseterms = 'cc-sa';
        $trainingdata->typicaljob = 'TEST';
        $trainingdata->skills = [1, 3];
        $trainingdata->certifying = '1';
        $trainingdata->presenceestimatedtimehours = '12';
        $trainingdata->presenceestimatedtimeminutes = '10';
        $trainingdata->remoteestimatedtimehours = '15';
        $trainingdata->remoteestimatedtimeminutes = '30';
        $trainingdata->trainingmodalities = 'd';
        $trainingdata->producingorganization = 'TEST';
        $trainingdata->producerorganizationlogo = '';
        $trainingdata->designers = 'TEST';
        $trainingdata->contactproducerorganization = 'TEST';
        $trainingdata->content['text'] = 'ContentText';

        // Create training.
        $training = \local_mentor_core\training_api::create_training($trainingdata);

        // One training.
        $entitytrainings = $entity->get_trainings();
        self::assertCount(1, $entitytrainings);
        self::assertArrayHasKey($training->id, $entitytrainings);
        self::assertEquals($entitytrainings[$training->id]->courseshortname, $training->courseshortname);
        self::assertEquals($entitytrainings[$training->id]->status, $training->status);
        self::assertEquals($entitytrainings[$training->id]->traininggoal, $training->traininggoal);

        self::resetAllData();
    }

    /**
     * Test get sirh list
     *
     * @covers \local_mentor_specialization\mentor_entity::get_sirh_list
     * @covers \local_mentor_specialization\mentor_entity::update_sirh_list
     */
    public function test_get_sirh_list_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        self::setAdminUser();

        $entitydata = $this->get_entities_data()[0];
        $entityid = \local_mentor_core\entity_api::create_entity($entitydata);
        $entity = new \local_mentor_specialization\mentor_entity($entityid);

        self::assertCount(0, $entity->get_sirh_list());
        self::assertCount(0, $entity->get_sirh_list(true));

        $entity->update_sirh_list(['SIRHTEST']);

        $enrirysirh = $entity->get_sirh_list();
        self::assertCount(1, $enrirysirh);
        self::assertEquals(['SIRHTEST'], $enrirysirh);

        self::resetAllData();
    }

    /**
     * Test is hidden
     *
     * @covers \local_mentor_specialization\mentor_entity::is_hidden
     */
    public function test_is_hidden() {
        global $DB;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        $DB->delete_records('course_categories');

        self::setAdminUser();

        $entityid = \local_mentor_core\entity_api::create_entity([
                'name' => 'New Entity 2',
                'shortname' => 'New Entity 2',
        ]);
        $entity = new \local_mentor_specialization\mentor_entity($entityid);

        // Hidden data not init.
        self::assertEquals(0, $entity->is_hidden());

        // Set Hidden data to 1.
        $categoryoption = new \stdClass();
        $categoryoption->categoryid = $entity->id;
        $categoryoption->name = 'hidden';
        $categoryoption->value = 1;
        $categoryoption->id = $DB->insert_record('category_options', $categoryoption);
        self::assertEquals(1, $entity->is_hidden());

        // Set Hidden data to 0.
        $categoryoption->value = 0;
        self::assertTrue($DB->update_record('category_options', $categoryoption));
        self::assertEquals(0, $entity->is_hidden());

        self::resetAllData();
    }

    /**
     * Test update visibility
     *
     * @covers \local_mentor_specialization\mentor_entity::update_visibility
     */
    public function test_update_visibility() {
        global $DB;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->reset_singletons();

        $DB->delete_records('course_categories');

        self::setAdminUser();

        $entityid = \local_mentor_core\entity_api::create_entity([
                'name' => 'New Entity 2',
                'shortname' => 'New Entity 2',
        ]);
        $entity = new \local_mentor_specialization\mentor_entity($entityid);

        // Hidden data not init.
        self::assertEquals(0, $entity->is_hidden());

        // Set Hidden data to 1.
        $entity->update_visibility(1);
        self::assertEquals(1, $DB->get_field(
                'category_options',
                'value',
                ['categoryid' => $entity->id, 'name' => 'hidden']
        ));
        self::assertEquals(1, $entity->is_hidden());

        // Set Hidden data to 0.
        $entity->update_visibility(0);
        self::assertEquals(0, $DB->get_field(
                'category_options',
                'value',
                ['categoryid' => $entity->id, 'name' => 'hidden']
        ));
        self::assertEquals(0, $entity->is_hidden());

        self::resetAllData();
    }
}
