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
 * User controller tests
 *
 * @package    local_user
 * @copyright  2022 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

class local_user_controller_testcase extends advanced_testcase {
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
        $frontcontroller = new \local_mentor_core\front_controller('user', 'local_user\\', $params);
    }

    /**
     * Test set_user_preference function
     *
     * @covers local_user\user_controller::execute
     * @covers local_user\user_controller::set_user_preference
     */
    public function test_set_user_preference() {
        global $DB, $USER;

        $this->resetAfterTest(true);
        $this->reset_singletons();
        self::setAdminUser();

        $DB->delete_records('user_info_field');

        $user = $this->getDataGenerator()->create_user();

        // With set user.

        // Not preference.
        self::assertFalse($DB->record_exists('user_preferences', array('userid' => $user->id)));

        $params = [
            'controller'     => 'user',
            'action'         => 'set_user_preference',
            'format'         => 'json',
            'userid'         => $user->id,
            'preferencename' => 'preferencename',
            'value'          => 'preferencevalue'
        ];

        // Call front controller.
        $frontcontroller = new \local_mentor_core\front_controller('user', 'local_user\\', $params);

        // Execute and create preference.
        self::assertTrue($frontcontroller->execute());

        // New preference.
        self::assertTrue($DB->record_exists('user_preferences', array('userid' => $user->id)));

        // Get preference.
        $userpreference = $DB->get_records('user_preferences', array('userid' => $user->id));

        // Check data preference.
        self::assertCount(1, $userpreference);
        self::assertEquals(current($userpreference)->userid, $user->id);
        self::assertEquals(current($userpreference)->name, 'preferencename');
        self::assertEquals(current($userpreference)->value, 'preferencevalue');

        // With global user.

        // Not preference.
        self::assertFalse($DB->record_exists('user_preferences', array('userid' => $USER->id)));

        $params = [
            'controller'     => 'user',
            'action'         => 'set_user_preference',
            'format'         => 'json',
            'preferencename' => 'preferencename',
            'value'          => 'preferencevalue'
        ];

        // Call front controller.
        $frontcontroller = new \local_mentor_core\front_controller('user', 'local_user\\', $params);

        // Execute and create preference.
        self::assertTrue($frontcontroller->execute());

        // New preference.
        self::assertTrue($DB->record_exists('user_preferences', array('userid' => $USER->id)));

        // Get preference.
        $userpreference = $DB->get_records('user_preferences', array('userid' => $USER->id));

        // Check data preference.
        self::assertCount(1, $userpreference);
        self::assertEquals(current($userpreference)->userid, $USER->id);
        self::assertEquals(current($userpreference)->name, 'preferencename');
        self::assertEquals(current($userpreference)->value, 'preferencevalue');

        self::resetAllData();
    }
}
