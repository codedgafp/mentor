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
 * Tests for dbinterface class
 *
 * @package    logstore_mentor
 * @copyright  2021 Edunao SAS (contact@edunao.com)
 * @author     adrien <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_mentor_core\session;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . "/admin/tool/log/store/mentor2/classes/database_interface.php");

class logstore_mentor2_database_interface_testcase extends advanced_testcase {

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
     * Test get instance ok
     * Check get instance return same object.
     *
     * @covers \logstore_mentor2\database_interface\database_interface::get_instance
     * @return void
     * @throws ReflectionException
     */
    public function test_get_instance_ok() {
        $this->resetAfterTest(true);
        $this->reset_singletons();

        $dbinstance1 = \logstore_mentor2\database_interface\database_interface::get_instance();
        $dbinstance2 = \logstore_mentor2\database_interface\database_interface::get_instance();

        self::assertSame($dbinstance1, $dbinstance2);

        self::resetAllData();
    }

    /**
     * Test __construct ok
     * Check get instance return same object.
     *
     * @covers \logstore_mentor2\database_interface\database_interface::__construct
     * @return void
     * @throws ReflectionException
     */
    public function test_construct_ok() {
        global $DB;

        $this->resetAfterTest(true);
        $this->reset_singletons();

        // Get log database interface.
        $dbinterface = new \logstore_mentor2\database_interface\database_interface();

        // Check object attribute.
        self::assertObjectHasAttribute('db', $dbinterface);
        self::assertObjectHasAttribute('log', $dbinterface);

        // Check db attribute.
        $dbinterfacereflection = new ReflectionObject($dbinterface);
        $dbreflection = $dbinterfacereflection->getProperty('db');
        $dbreflection->setAccessible(true);

        self::assertSame($DB, $dbreflection->getValue($dbinterface));

        $logreflection = $dbinterfacereflection->getProperty('log');
        $logreflection->setAccessible(true);
        $logdata = $logreflection->getValue($dbinterface);

        // Check log attribute.
        self::assertArrayHasKey('log2', $logdata);
        self::assertArrayHasKey('log_history2', $logdata);
        self::assertArrayHasKey('session2', $logdata);
        self::assertArrayHasKey('collection2', $logdata);
        self::assertArrayHasKey('user2', $logdata);
        self::assertArrayHasKey('region2', $logdata);
        self::assertArrayHasKey('entity2', $logdata);

        self::assertEmpty($logdata['log2']);
        self::assertEmpty($logdata['log_history2']);
        self::assertEmpty($logdata['session2']);
        self::assertEmpty($logdata['collection2']);
        self::assertEmpty($logdata['user2']);
        self::assertEmpty($logdata['region2']);
        self::assertEmpty($logdata['entity2']);

        self::resetAllData();
    }

    /**
     * Test add to log ok
     *
     * @covers \logstore_mentor2\database_interface\database_interface::add_to_log
     * @return void
     * @throws ReflectionException
     */
    public function test_add_to_log_ok() {
        $this->resetAfterTest(true);
        $this->reset_singletons();

        // Get log database interface.
        $dbinterface = \logstore_mentor2\database_interface\database_interface::get_instance();

        // Create reflection to have access to log attribute and add_to_log method.
        $dbinterfacereflection = new ReflectionObject($dbinterface);
        $logreflection = $dbinterfacereflection->getProperty('log');
        $logreflection->setAccessible(true);
        $addtologmethod = $dbinterfacereflection->getMethod('add_to_log');
        $addtologmethod->setAccessible(true);

        // Log2.
        $logdata = $logreflection->getValue($dbinterface);
        self::assertEmpty($logdata['log2']);
        $addtologmethod->invokeArgs($dbinterface, array(
            'log2', 1, 'data1'
        ));
        $logdata = $logreflection->getValue($dbinterface);
        self::assertCount(1, $logdata['log2']);
        self::assertArrayHasKey(1, $logdata['log2']);
        self::assertEquals($logdata['log2'][1], 'data1');

        // Log_history2.
        $logdata = $logreflection->getValue($dbinterface);
        self::assertEmpty($logdata['log_history2']);
        $addtologmethod->invokeArgs($dbinterface, array(
            'log_history2', 2, 'data2'
        ));
        $logdata = $logreflection->getValue($dbinterface);
        self::assertCount(1, $logdata['log_history2']);
        self::assertArrayHasKey(2, $logdata['log_history2']);
        self::assertEquals($logdata['log_history2'][2], 'data2');

        // Session2.
        $logdata = $logreflection->getValue($dbinterface);
        self::assertEmpty($logdata['session2']);
        $addtologmethod->invokeArgs($dbinterface, array(
            'session2', 3, 'data3'
        ));
        $logdata = $logreflection->getValue($dbinterface);
        self::assertCount(1, $logdata['session2']);
        self::assertArrayHasKey(3, $logdata['session2']);
        self::assertEquals($logdata['session2'][3], 'data3');

        // Collection2.
        $logdata = $logreflection->getValue($dbinterface);
        self::assertEmpty($logdata['collection2']);
        $addtologmethod->invokeArgs($dbinterface, array(
            'collection2', 4, 'data4'
        ));
        $logdata = $logreflection->getValue($dbinterface);
        self::assertCount(1, $logdata['collection2']);
        self::assertArrayHasKey(4, $logdata['collection2']);
        self::assertEquals($logdata['collection2'][4], 'data4');

        // User2.
        $logdata = $logreflection->getValue($dbinterface);
        self::assertEmpty($logdata['user2']);
        $addtologmethod->invokeArgs($dbinterface, array(
            'user2', 5, 'data5'
        ));
        $logdata = $logreflection->getValue($dbinterface);
        self::assertCount(1, $logdata['user2']);
        self::assertArrayHasKey(5, $logdata['user2']);
        self::assertEquals($logdata['user2'][5], 'data5');

        // Region2.
        $logdata = $logreflection->getValue($dbinterface);
        self::assertEmpty($logdata['region2']);
        $addtologmethod->invokeArgs($dbinterface, array(
            'region2', 6, 'data6'
        ));
        $logdata = $logreflection->getValue($dbinterface);
        self::assertCount(1, $logdata['region2']);
        self::assertArrayHasKey(6, $logdata['region2']);
        self::assertEquals($logdata['region2'][6], 'data6');

        // Entity2.
        $logdata = $logreflection->getValue($dbinterface);
        self::assertEmpty($logdata['entity2']);
        $addtologmethod->invokeArgs($dbinterface, array(
            'entity2', 7, 'data7'
        ));
        $logdata = $logreflection->getValue($dbinterface);
        self::assertCount(1, $logdata['entity2']);
        self::assertArrayHasKey(7, $logdata['entity2']);
        self::assertEquals($logdata['entity2'][7], 'data7');

        self::resetAllData();
    }

    /**
     * Test get log index ok
     *
     * @covers \logstore_mentor2\database_interface\database_interface::get_log_index
     * @return void
     * @throws ReflectionException
     */
    public function test_get_log_index_ok() {
        $this->resetAfterTest(true);
        $this->reset_singletons();

        // Get log database interface.
        $dbinterface = \logstore_mentor2\database_interface\database_interface::get_instance();

        // Create reflection to have add_to_log method.
        $dbinterfacereflection = new ReflectionObject($dbinterface);
        $addtologmethod = $dbinterfacereflection->getMethod('add_to_log');
        $addtologmethod->setAccessible(true);

        // Log2.
        $addtologmethod->invokeArgs($dbinterface, array(
            'log2', 1, 'data1'
        ));
        self::assertEquals(1, $dbinterface->get_log_index('log2', 'data1'));
        self::assertFalse($dbinterface->get_log_index('log2', 'falsedata'));

        // Log_history2.
        $addtologmethod->invokeArgs($dbinterface, array(
            'log_history2', 2, 'data2'
        ));
        self::assertEquals(2, $dbinterface->get_log_index('log_history2', 'data2'));
        self::assertFalse($dbinterface->get_log_index('log_history2', 'falsedata'));

        // Session2.
        $addtologmethod->invokeArgs($dbinterface, array(
            'session2', 3, 'data3'
        ));
        self::assertEquals(3, $dbinterface->get_log_index('session2', 'data3'));
        self::assertFalse($dbinterface->get_log_index('session2', 'falsedata'));

        // Collection2.
        $addtologmethod->invokeArgs($dbinterface, array(
            'collection2', 4, 'data4'
        ));
        self::assertEquals(4, $dbinterface->get_log_index('collection2', 'data4'));
        self::assertFalse($dbinterface->get_log_index('collection2', 'falsedata'));

        // User2.
        $addtologmethod->invokeArgs($dbinterface, array(
            'user2', 5, 'data5'
        ));
        self::assertEquals(5, $dbinterface->get_log_index('user2', 'data5'));
        self::assertFalse($dbinterface->get_log_index('user2', 'falsedata'));

        // Region2.
        $addtologmethod->invokeArgs($dbinterface, array(
            'region2', 6, 'data6'
        ));
        self::assertEquals(6, $dbinterface->get_log_index('region2', 'data6'));
        self::assertFalse($dbinterface->get_log_index('region2', 'falsedata'));

        // Entity2.
        $addtologmethod->invokeArgs($dbinterface, array(
            'entity2', 7, 'data7'
        ));
        self::assertEquals(7, $dbinterface->get_log_index('entity2', 'data7'));
        self::assertFalse($dbinterface->get_log_index('entity2', 'falsedata'));

        self::resetAllData();
    }

    /**
     * Test get log ok
     *
     * @covers \logstore_mentor2\database_interface\database_interface::get_log
     * @return void
     * @throws ReflectionException
     */
    public function test_get_log_ok() {
        $this->resetAfterTest(true);
        $this->reset_singletons();

        // Get log database interface.
        $dbinterface = new \logstore_mentor2\database_interface\database_interface();

        // Get log data.
        $logdata = $dbinterface->get_log();

        // Check log data to attribute.
        self::assertArrayHasKey('log2', $logdata);
        self::assertArrayHasKey('log_history2', $logdata);
        self::assertArrayHasKey('session2', $logdata);
        self::assertArrayHasKey('collection2', $logdata);
        self::assertArrayHasKey('user2', $logdata);
        self::assertArrayHasKey('region2', $logdata);
        self::assertArrayHasKey('entity2', $logdata);

        self::assertEmpty($logdata['log2']);
        self::assertEmpty($logdata['log_history2']);
        self::assertEmpty($logdata['session2']);
        self::assertEmpty($logdata['collection2']);
        self::assertEmpty($logdata['user2']);
        self::assertEmpty($logdata['region2']);
        self::assertEmpty($logdata['entity2']);

        // Create reflection to have add_to_log method.
        $dbinterfacereflection = new ReflectionObject($dbinterface);
        $addtologmethod = $dbinterfacereflection->getMethod('add_to_log');
        $addtologmethod->setAccessible(true);

        // Add new log data.
        $addtologmethod->invokeArgs($dbinterface, array(
            'log2', 1, 'data1'
        ));
        $addtologmethod->invokeArgs($dbinterface, array(
            'log_history2', 2, 'data2'
        ));
        $addtologmethod->invokeArgs($dbinterface, array(
            'session2', 3, 'data3'
        ));
        $addtologmethod->invokeArgs($dbinterface, array(
            'collection2', 4, 'data4'
        ));
        $addtologmethod->invokeArgs($dbinterface, array(
            'user2', 5, 'data5'
        ));
        $addtologmethod->invokeArgs($dbinterface, array(
            'region2', 6, 'data6'
        ));
        $addtologmethod->invokeArgs($dbinterface, array(
            'entity2', 7, 'data7'
        ));

        // Get new log data to attribute.
        $logdata = $dbinterface->get_log();

        // Check new log data to attribute.
        self::assertArrayHasKey(1, $logdata['log2']);
        self::assertEquals('data1', $logdata['log2'][1]);
        self::assertArrayHasKey(2, $logdata['log_history2']);
        self::assertEquals('data2', $logdata['log_history2'][2]);
        self::assertArrayHasKey(3, $logdata['session2']);
        self::assertEquals('data3', $logdata['session2'][3]);
        self::assertArrayHasKey(4, $logdata['collection2']);
        self::assertEquals('data4', $logdata['collection2'][4]);
        self::assertArrayHasKey(5, $logdata['user2']);
        self::assertEquals('data5', $logdata['user2'][5]);
        self::assertArrayHasKey(6, $logdata['region2']);
        self::assertEquals('data6', $logdata['region2'][6]);
        self::assertArrayHasKey(7, $logdata['entity2']);
        self::assertEquals('data7', $logdata['entity2'][7]);

        self::resetAllData();
    }

    /**
     * Test insert record ok
     *
     * @covers \logstore_mentor2\database_interface\database_interface::insert_record
     * @return void
     * @throws ReflectionException
     */
    public function test_insert_record_ok() {
        global $DB;
        $this->resetAfterTest(true);
        $this->reset_singletons();

        // Get log database interface.
        $dbinterface = new \logstore_mentor2\database_interface\database_interface();

        // Check if all log data is empty (to database and attribute).
        self::assertEmpty($DB->get_records('logstore_mentor_log2'));
        $logdata = $dbinterface->get_log();
        self::assertEmpty($logdata['log2']);

        // Create log data.
        $time = time();
        $logid = $dbinterface->insert_record('log2',
            array(
                'userlogid' => 1,
                'sessionlogid' => 2,
                'timecreated' => $time,
                'lastview' => $time,
                'numberview' => 3
            )
        );

        // Check log to database.
        $log = $DB->get_records('logstore_mentor_log2');

        // Check log data to database.
        self::assertCount(1, $log);
        self::assertArrayHasKey($logid, $log);
        self::assertEquals($log[$logid]->userlogid, 1);
        self::assertEquals($log[$logid]->sessionlogid, 2);
        self::assertEquals($log[$logid]->timecreated, $time);
        self::assertEquals($log[$logid]->lastview, $time);
        self::assertEquals($log[$logid]->numberview, 3);

        // Get log data attribute.
        $logdata = $dbinterface->get_log();

        // Check log data attribute.
        self::assertCount(1, $logdata['log2']);
        self::assertArrayHasKey($logid, $logdata['log2']);
        self::assertEquals($logdata['log2'][$logid]['userlogid'], 1);
        self::assertEquals($logdata['log2'][$logid]['sessionlogid'], 2);
        self::assertEquals($logdata['log2'][$logid]['timecreated'], $time);
        self::assertEquals($logdata['log2'][$logid]['lastview'], $time);
        self::assertEquals($logdata['log2'][$logid]['numberview'], 3);

        self::resetAllData();
    }

    /**
     * Test get record by id ok
     *
     * @covers \logstore_mentor2\database_interface\database_interface::get_record_by_id
     * @return void
     * @throws ReflectionException
     */
    public function test_get_record_by_id_ok() {
        global $DB;
        $this->resetAfterTest(true);
        $this->reset_singletons();

        // Get log database interface.
        $dbinterface = new \logstore_mentor2\database_interface\database_interface();

        // Check if log in database is empty.
        self::assertEmpty($DB->get_records('logstore_mentor_log2'));

        // Insert log data to database.
        $time = time();
        $logid = $dbinterface->insert_record('log2',
            array(
                'userlogid' => 1,
                'sessionlogid' => 2,
                'timecreated' => $time,
                'lastview' => $time,
                'numberview' => 3
            )
        );

        // Get log data to database.
        $log = $dbinterface->get_record_by_id('log2', $logid);

        // Check log data to database.
        self::assertEquals($log->id, $logid);
        self::assertEquals($log->userlogid, 1);
        self::assertEquals($log->sessionlogid, 2);
        self::assertEquals($log->timecreated, $time);
        self::assertEquals($log->lastview, $time);
        self::assertEquals($log->numberview, 3);

        self::resetAllData();
    }

    /**
     * Test update record ok
     *
     * @covers \logstore_mentor2\database_interface\database_interface::update_record
     * @return void
     * @throws ReflectionException
     */
    public function test_update_record_ok() {
        global $DB;
        $this->resetAfterTest(true);
        $this->reset_singletons();

        // Get log database interface.
        $dbinterface = new \logstore_mentor2\database_interface\database_interface();

        // Check if all log data is empty.
        self::assertEmpty($DB->get_records('logstore_mentor_log2'));
        $logdata = $dbinterface->get_log();
        self::assertEmpty($logdata['log2']);

        // Create log data to database and log data.
        $time = time();
        $data = array(
            'userlogid' => 1,
            'sessionlogid' => 2,
            'timecreated' => $time,
            'lastview' => $time,
            'numberview' => 3
        );
        $logid = $DB->insert_record('logstore_mentor_log2', $data);

        // Update log data.
        $data['id'] = $logid;
        $data['sessionlogid'] = 20;
        $data['numberview'] = 30;
        $dbinterface->update_record('log2', $data);

        // Get updated log data to database.
        $log = $DB->get_records('logstore_mentor_log2');

        // Check log data to database.
        self::assertCount(1, $log);
        self::assertArrayHasKey($logid, $log);
        self::assertEquals($log[$logid]->userlogid, 1);
        self::assertNotEquals($log[$logid]->sessionlogid, 2);
        self::assertEquals($log[$logid]->sessionlogid, 20);
        self::assertEquals($log[$logid]->timecreated, $time);
        self::assertEquals($log[$logid]->lastview, $time);
        self::assertNotEquals($log[$logid]->numberview, 3);
        self::assertEquals($log[$logid]->numberview, 30);

        // Get log data attribute.
        $logdata = $dbinterface->get_log();

        self::assertCount(1, $logdata['log2']);
        self::assertArrayHasKey($logid, $logdata['log2']);
        self::assertEquals($logdata['log2'][$logid]['userlogid'], 1);
        self::assertEquals($logdata['log2'][$logid]['sessionlogid'], 20);
        self::assertEquals($logdata['log2'][$logid]['timecreated'], $time);
        self::assertEquals($logdata['log2'][$logid]['lastview'], $time);
        self::assertEquals($logdata['log2'][$logid]['numberview'], 30);

        self::resetAllData();
    }

    /**
     * Test get or create record ok
     *
     * @covers \logstore_mentor2\database_interface\database_interface::get_or_create_record
     * @return void
     * @throws ReflectionException
     */
    public function test_get_or_create_record_ok() {
        global $DB;
        $this->resetAfterTest(true);
        $this->reset_singletons();

        // Get log database interface.
        $dbinterface = new \logstore_mentor2\database_interface\database_interface();

        // Check if all log data is empty (to database and attribute).
        self::assertEmpty($DB->get_records('logstore_mentor_log2'));
        $logdata = $dbinterface->get_log();
        self::assertEmpty($logdata['log2']);

        // Create new data log to database and attribute.
        $time = time();
        $data = array(
            'userlogid' => 1,
            'sessionlogid' => 2,
            'timecreated' => $time,
            'lastview' => $time,
            'numberview' => 3
        );
        $logid = $dbinterface->get_or_create_record('log2', $data);

        // Get new log data to database.
        $log = $DB->get_records('logstore_mentor_log2');

        // Check log data to database.
        self::assertCount(1, $log);
        self::assertArrayHasKey($logid, $log);
        self::assertEquals($log[$logid]->userlogid, 1);
        self::assertEquals($log[$logid]->sessionlogid, 2);
        self::assertEquals($log[$logid]->timecreated, $time);
        self::assertEquals($log[$logid]->lastview, $time);
        self::assertEquals($log[$logid]->numberview, 3);

        // Check new log data to attribute.
        $logdata = $dbinterface->get_log();

        // Check log data to attribute.
        self::assertCount(1, $logdata['log2']);
        self::assertArrayHasKey($logid, $logdata['log2']);
        self::assertEquals($logdata['log2'][$logid]['userlogid'], 1);
        self::assertEquals($logdata['log2'][$logid]['sessionlogid'], 2);
        self::assertEquals($logdata['log2'][$logid]['timecreated'], $time);
        self::assertEquals($logdata['log2'][$logid]['lastview'], $time);
        self::assertEquals($logdata['log2'][$logid]['numberview'], 3);

        // Get existing log data.
        $dbinterface->get_or_create_record('log2', $data);

        // No new data.
        $log = $DB->get_records('logstore_mentor_log2');
        self::assertCount(1, $log);

        // No new data.
        $logdata = $dbinterface->get_log();
        self::assertCount(1, $logdata['log2']);

        // New log data.
        $data['sessionlogid'] = 20;
        $data['numberview'] = 30;

        // Create new log data to database and attribute.
        $newlogid = $dbinterface->get_or_create_record('log2', $data);

        // Get log data to database.
        $log = $DB->get_records('logstore_mentor_log2');

        // Check log data to database.
        self::assertCount(2, $log);
        self::assertArrayHasKey($newlogid, $log);
        self::assertEquals($log[$newlogid]->userlogid, 1);
        self::assertEquals($log[$newlogid]->sessionlogid, 20);
        self::assertEquals($log[$newlogid]->timecreated, $time);
        self::assertEquals($log[$newlogid]->lastview, $time);
        self::assertEquals($log[$newlogid]->numberview, 30);

        // Get log data to attribute.
        $logdata = $dbinterface->get_log();

        // Check log data.
        self::assertCount(2, $logdata['log2']);
        self::assertArrayHasKey($newlogid, $logdata['log2']);
        self::assertEquals($logdata['log2'][$newlogid]['userlogid'], 1);
        self::assertEquals($logdata['log2'][$newlogid]['sessionlogid'], 20);
        self::assertEquals($logdata['log2'][$newlogid]['timecreated'], $time);
        self::assertEquals($logdata['log2'][$newlogid]['lastview'], $time);
        self::assertEquals($logdata['log2'][$newlogid]['numberview'], 30);

        self::resetAllData();
    }
}
