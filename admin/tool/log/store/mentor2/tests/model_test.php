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
 * Model class test
 *
 * @package    logstore_mentor
 * @copyright  2022 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . "/admin/tool/log/store/mentor2/classes/model/abstractlog.php");
require_once($CFG->dirroot . "/admin/tool/log/store/mentor2/classes/model/collection.php");
require_once($CFG->dirroot . "/admin/tool/log/store/mentor2/classes/model/entity.php");
require_once($CFG->dirroot . "/admin/tool/log/store/mentor2/classes/model/log.php");
require_once($CFG->dirroot . "/admin/tool/log/store/mentor2/classes/model/region.php");
require_once($CFG->dirroot . "/admin/tool/log/store/mentor2/classes/model/session.php");
require_once($CFG->dirroot . "/admin/tool/log/store/mentor2/classes/model/user.php");

class logstore_mentor2_models_testcase extends advanced_testcase {
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

    // Collection.

    /**
     * Test get required fields ok
     *
     * @covers logstore_mentor2\models\collection::get_required_fields
     * @covers logstore_mentor2\models\abstractlog::__construct
     */
    public function test_model_collection_get_required_fields_ok() {
        $this->resetAfterTest(true);
        $this->resetAfterTest();
        $this->reset_singletons();

        self::setAdminUser();

        $collection = new \logstore_mentor2\models\collection(array(
            'name' => 'colelction1'
        ));

        $requiredfields = $collection->get_required_fields();

        self::assertIsArray($requiredfields);
        self::assertCount(1, $requiredfields);
        self::assertEquals('name', $requiredfields['0']);

        self::resetAllData();
    }

    // Entity.

    /**
     * Test get required fields ok
     *
     * @covers logstore_mentor2\models\entity::get_required_fields
     * @covers logstore_mentor2\models\abstractlog::__construct
     */
    public function test_model_entity_get_required_fields_ok() {
        $this->resetAfterTest(true);
        $this->resetAfterTest();
        $this->reset_singletons();

        self::setAdminUser();

        $entity = new \logstore_mentor2\models\entity(array(
            'entityid' => 0,
            'name' => 'Entity name',
            'regions' => 'Region name'
        ));

        $requiredfields = $entity->get_required_fields();

        self::assertIsArray($requiredfields);
        self::assertCount(3, $requiredfields);
        self::assertEquals('entityid', $requiredfields['0']);
        self::assertEquals('name', $requiredfields['1']);
        self::assertEquals('regions', $requiredfields['2']);

        self::resetAllData();
    }

    // Log.

    /**
     * Test get required fields ok
     *
     * @covers logstore_mentor2\models\log::get_required_fields
     * @covers logstore_mentor2\models\abstractlog::__construct
     */
    public function test_model_log_get_required_fields_ok() {
        $this->resetAfterTest(true);
        $this->resetAfterTest();
        $this->reset_singletons();

        self::setAdminUser();

        $log = new \logstore_mentor2\models\log(array(
            'userlogid' => 1,
            'sessionlogid' => 2
        ));

        $requiredfields = $log->get_required_fields();

        self::assertIsArray($requiredfields);
        self::assertCount(2, $requiredfields);
        self::assertEquals('userlogid', $requiredfields['0']);
        self::assertEquals('sessionlogid', $requiredfields['1']);

        self::resetAllData();
    }

    // Log.

    /**
     * Test get required fields ok
     *
     * @covers logstore_mentor2\models\region::get_required_fields
     * @covers logstore_mentor2\models\abstractlog::__construct
     */
    public function test_model_region_get_required_fields_ok() {
        $this->resetAfterTest(true);
        $this->resetAfterTest();
        $this->reset_singletons();

        self::setAdminUser();

        $region = new \logstore_mentor2\models\region(array(
            'name' => 'Region name',
        ));

        $requiredfields = $region->get_required_fields();

        self::assertIsArray($requiredfields);
        self::assertCount(1, $requiredfields);
        self::assertEquals('name', $requiredfields['0']);

        self::resetAllData();
    }

    // Session.

    /**
     * Test get required fields ok
     *
     * @covers logstore_mentor2\models\session::get_required_fields
     * @covers logstore_mentor2\models\abstractlog::__construct
     */
    public function test_model_session_get_required_fields_ok() {
        $this->resetAfterTest(true);
        $this->resetAfterTest();
        $this->reset_singletons();

        self::setAdminUser();

        $session = new \logstore_mentor2\models\session(array(
            'sessionid' => 1,
            'entitylogid' => 2,
            'subentitylogid' => 3,
            'trainingentitylogid' => 4,
            'trainingsubentitylogid' => 5,
            'status' => 1,
            'shared' => 0
        ));

        $requiredfields = $session->get_required_fields();

        self::assertIsArray($requiredfields);
        self::assertCount(7, $requiredfields);
        self::assertEquals('sessionid', $requiredfields['0']);
        self::assertEquals('entitylogid', $requiredfields['1']);
        self::assertEquals('subentitylogid', $requiredfields['2']);
        self::assertEquals('trainingentitylogid', $requiredfields['3']);
        self::assertEquals('trainingsubentitylogid', $requiredfields['4']);
        self::assertEquals('status', $requiredfields['5']);
        self::assertEquals('shared', $requiredfields['6']);

        self::resetAllData();
    }

    // User.

    /**
     * Test get required fields ok
     *
     * @covers logstore_mentor2\models\user::get_required_fields
     * @covers logstore_mentor2\models\abstractlog::__construct
     */
    public function test_model_user_get_required_fields_ok() {
        $this->resetAfterTest(true);
        $this->resetAfterTest();
        $this->reset_singletons();

        self::setAdminUser();

        $user = new \logstore_mentor2\models\user(array(
            'userid' => 1,
            'entitylogid' => 2,
            'trainer' => 0,
            'status' => 1,
            'category' => 3,
            'regionlogid' => 4,
            'department' => 5
        ));

        $requiredfields = $user->get_required_fields();

        self::assertIsArray($requiredfields);
        self::assertCount(7, $requiredfields);
        self::assertEquals('userid', $requiredfields['0']);
        self::assertEquals('entitylogid', $requiredfields['1']);
        self::assertEquals('trainer', $requiredfields['2']);
        self::assertEquals('status', $requiredfields['3']);
        self::assertEquals('category', $requiredfields['4']);
        self::assertEquals('regionlogid', $requiredfields['5']);
        self::assertEquals('department', $requiredfields['6']);

        self::resetAllData();
    }
}
