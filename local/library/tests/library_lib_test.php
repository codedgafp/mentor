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
 * Library lib tests
 *
 * @package    local_library
 * @copyright  2022 Edunao SAS (contact@edunao.com)
 * @author     rcolet <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/local/library/lib.php');
require_once($CFG->libdir . '/navigationlib.php');

class local_library_lib_testcase extends advanced_testcase {

    /**
     * Reset the singletons
     *
     * @throws ReflectionException
     */
    public function reset_singletons() {
        // Reset the mentor core db interface singleton.
        $dbinterface = \local_mentor_core\database_interface::get_instance();
        $reflection = new ReflectionClass($dbinterface);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true); // Now we can modify that :).
        $instance->setValue(null, null); // Instance is gone.
        $instance->setAccessible(false); // Clean up.

        \local_mentor_core\training_api::clear_cache();
    }

    /**
     * Test local_library_init_config
     *
     * @covers  ::local_library_init_config
     * @covers  ::local_library_set_library
     */
    public function test_local_library_init_config() {
        global $DB;

        $this->resetAfterTest(true);
        $this->reset_singletons();
        $this->setOutputCallback(function() {
        });

        self::setAdminUser();

        // Remove library entity and config.
        $dbi = \local_mentor_core\database_interface::get_instance();
        $category = core_course_category::get($dbi->get_library_object()->id);
        $category->delete_full(false);
        unset_config(\local_mentor_core\library::CONFIG_VALUE_ID);

        $library = \local_mentor_core\library::get_instance();
        $reflection = new ReflectionClass($library);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true); // Now we can modify that :).
        $instance->setValue(null, null); // Instance is gone.
        $instance->setAccessible(false); // Clean up.

        $visiteurbibliorole = $DB->get_record('role', array('shortname' => get_string('viewroleshortname', 'local_library')));

        // Remove visiteurbiblio role.
        self::assertTrue(delete_role($visiteurbibliorole->id));

        self::assertFalse($DB->record_exists('role', array('id' => $visiteurbibliorole->id)));
        self::assertFalse(
            $DB->record_exists('role', array('shortname' => get_string('viewroleshortname', 'local_library')))
        );
        self::assertFalse($DB->record_exists('role_context_levels', array('roleid' => $visiteurbibliorole->id)));
        self::assertFalse($DB->record_exists('role_capabilities', array('roleid' => $visiteurbibliorole->id)));

        local_library_init_config();

        // Check if library has created.
        self::assertTrue($DB->record_exists('course_categories',
            array('name' => \local_mentor_core\library::NAME, 'idnumber' => \local_mentor_core\library::SHORTNAME)
        ));

        $libraryobject = $DB->get_record('course_categories',
            array('name' => \local_mentor_core\library::NAME, 'idnumber' => \local_mentor_core\library::SHORTNAME)
        );
        $librarysingleton = \local_mentor_core\library_api::get_library();
        self::assertEquals($libraryobject->id, $librarysingleton->id);
        self::assertEquals(\local_mentor_core\library::NAME, $librarysingleton->name);
        self::assertEquals(local_mentor_core\library::SHORTNAME, $librarysingleton->shortname);

        $libraryconfigid = \local_mentor_core\library_api::get_library_id();
        self::assertEquals($libraryobject->id, $libraryconfigid);

        // Check if role has created.
        self::assertTrue(
            $DB->record_exists('role', array('shortname' => get_string('viewroleshortname', 'local_library')))
        );
        $newvisiteurbibliorole = $DB->get_record('role', array('shortname' => get_string('viewroleshortname', 'local_library')));

        // Check new role context level.
        self::assertTrue($DB->record_exists('role_context_levels', array('roleid' => $newvisiteurbibliorole->id)));
        $newcontextlevel = $DB->get_records('role_context_levels', array('roleid' => $newvisiteurbibliorole->id));
        self::assertCount(1, $newcontextlevel);
        self::assertEquals(current($newcontextlevel)->contextlevel, CONTEXT_COURSECAT);

        // Check new role capability.
        self::assertTrue($DB->record_exists('role_capabilities', array('roleid' => $newvisiteurbibliorole->id)));
        $newcapabilty = $DB->get_records('role_capabilities', array('roleid' => $newvisiteurbibliorole->id));
        self::assertCount(1, $newcapabilty);
        self::assertEquals(current($newcapabilty)->capability, 'local/library:view');

        self::resetAllData();
    }

    /**
     * Test local_library_extend_settings_navigation
     *
     * @covers  ::local_library_extend_settings_navigation
     */
    public function test_local_library_extend_settings_navigation() {
        global $PAGE, $DB, $USER;

        $this->resetAfterTest(true);
        $this->reset_singletons();
        $this->setOutputCallback(function() {
        });

        // With admin.
        self::setAdminUser();

        // Remove library entity and config.
        $dbi = \local_mentor_core\database_interface::get_instance();
        $category = core_course_category::get($dbi->get_library_object()->id);
        $category->delete_full(false);
        unset_config(\local_mentor_core\library::CONFIG_VALUE_ID);

        $library = \local_mentor_core\library::get_instance();
        $reflection = new ReflectionClass($library);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true); // Now we can modify that :).
        $instance->setValue(null, null); // Instance is gone.
        $instance->setAccessible(false); // Clean up.

        local_library_init_config();

        // With simple course.
        $course = $this->getDataGenerator()->create_course();
        $PAGE->set_url(course_get_url($course->id));
        $PAGE->set_context(context_course::instance($course->id));
        $PAGE->set_course($course);

        $admin = $USER;
        $settingsnav = new settings_navigation($PAGE);
        $settingsnav->initialise();
        $settingsnav->extend_for_user($admin->id);

        self::assertNotFalse($settingnode = $settingsnav->find('courseadmin', navigation_node::TYPE_COURSE));
        self::assertNotContains('trainingtolibrary', $settingnode->get_children_key_list());

        $entityid = \local_mentor_core\entity_api::create_entity([
            'name' => 'New Entity 1',
            'shortname' => 'New Entity 1'
        ]);
        $entity = \local_mentor_core\entity_api::get_entity($entityid);

        // With training course.
        $data = new \stdClass();
        $data->name = 'fullname';
        $data->shortname = 'shortname';
        $data->content = 'summary';
        $data->status = \local_mentor_core\training::STATUS_ELABORATION_COMPLETED;
        $data->traininggoal = 'TEST TRAINING ';
        $data->thumbnail = '';
        $data->categorychildid = $entity->get_entity_formation_category();
        $data->categoryid = $entity->id;
        $data->creativestructure = $entity->id;
        $training = \local_mentor_core\training_api::create_training($data);
        $trainingcontext = $training->get_context();
        $trainingcourse = $training->get_course();

        $PAGE->set_url(course_get_url($trainingcourse->id));
        $PAGE->set_context($trainingcontext);
        $PAGE->set_course($trainingcourse);

        $settingsnav = new settings_navigation($PAGE);
        $settingsnav->initialise();
        $settingsnav->extend_for_user($admin->id);

        self::assertNotFalse($settingnode = $settingsnav->find('courseadmin', navigation_node::TYPE_COURSE));
        self::assertContains('trainingtolibrary', $settingnode->get_children_key_list());

        // With Administrateur espace dedie.
        $admindedie = $this->getDataGenerator()->create_user();
        $admindedierole = $DB->get_record('role', array('shortname' => 'admindedie'));
        $this->getDataGenerator()->role_assign(
            $admindedierole->id,
            $admindedie->id,
            context_coursecat::instance($entity->id)->id
        );

        self::setUser($admindedie);
        $PAGE->set_url(course_get_url($trainingcourse->id));
        $PAGE->set_context($trainingcontext);
        $PAGE->set_course($trainingcourse);

        $settingsnav = new settings_navigation($PAGE);
        $settingsnav->initialise();
        $settingsnav->extend_for_user($admindedie->id);

        self::assertNotFalse($settingnode = $settingsnav->find('courseadmin', navigation_node::TYPE_COURSE));
        self::assertContains('trainingtolibrary', $settingnode->get_children_key_list());

        // With Responsable de formation central.
        $respformation = $this->getDataGenerator()->create_user();
        $respformationrole = $DB->get_record('role', array('shortname' => 'respformation'));
        $this->getDataGenerator()->role_assign(
            $respformationrole->id,
            $respformation->id,
            context_coursecat::instance($entity->id)->id
        );

        self::setUser($respformation);
        $PAGE->set_url(course_get_url($trainingcourse->id));
        $PAGE->set_context($trainingcontext);
        $PAGE->set_course($trainingcourse);

        $settingsnav = new settings_navigation($PAGE);
        $settingsnav->initialise();
        $settingsnav->extend_for_user($respformation->id);

        self::assertNotFalse($settingnode = $settingsnav->find('courseadmin', navigation_node::TYPE_COURSE));
        self::assertContains('trainingtolibrary', $settingnode->get_children_key_list());

        // With Referent local de formation.
        $referentlocal = $this->getDataGenerator()->create_user();
        $referentlocalrole = $DB->get_record('role', array('shortname' => 'referentlocal'));
        $this->getDataGenerator()->role_assign(
            $referentlocalrole->id,
            $referentlocal->id,
            context_coursecat::instance($entity->id)->id
        );

        self::setUser($referentlocal);
        $PAGE->set_url(course_get_url($trainingcourse->id));
        $PAGE->set_context($trainingcontext);
        $PAGE->set_course($trainingcourse);

        $settingsnav = new settings_navigation($PAGE);
        $settingsnav->initialise();
        $settingsnav->extend_for_user($referentlocal->id);

        self::assertNotFalse($settingnode = $settingsnav->find('courseadmin', navigation_node::TYPE_COURSE));
        self::assertNotContains('trainingtolibrary', $settingnode->get_children_key_list());

        // With Participant.
        $participant = $this->getDataGenerator()->create_user();
        $participantrole = $DB->get_record('role', array('shortname' => 'participant'));
        $this->getDataGenerator()->role_assign(
            $participantrole->id,
            $participant->id,
            $trainingcontext
        );

        self::setUser($participant);
        $PAGE->set_url(course_get_url($trainingcourse->id));
        $PAGE->set_context($trainingcontext);
        $PAGE->set_course($trainingcourse);

        $settingsnav = new settings_navigation($PAGE);
        $settingsnav->initialise();
        $settingsnav->extend_for_user($participant->id);

        self::assertFalse($settingnode = $settingsnav->find('courseadmin', navigation_node::TYPE_COURSE));

        self::resetAllData();
    }
}
