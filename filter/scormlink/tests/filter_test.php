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
 * Test cases for scormlink filter
 *
 * @package    filter_scormlink
 * @copyright  2022 Edunao SAS (contact@edunao.com)
 * @author     Remi Colet <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/filter/scormlink/filter.php');

class filter_scormlink_filter_testcase extends advanced_testcase {

    /**
     * Test test_get_params_renderer function not ok
     * Does not have courser context
     *
     * @covers filter_scormlink::filter
     */
    public function test_filter_nok_not_course_context() {
        $this->resetAfterTest(true);

        $text = '<a href="https://mentor.gouv.fr/">Test text</a>';

        $category = self::getDataGenerator()->create_category();

        $filterscormlink = new filter_scormlink(context_coursecat::instance($category->id), array());

        self::assertEquals($text, $filterscormlink->filter($text));

        self::resetAllData();
    }

    /**
     * Test test_get_params_renderer function not ok
     * Bad check regex
     *
     * @covers filter_scormlink::filter
     */
    public function test_filter_nok_bad_check_regex() {
        $this->resetAfterTest(true);

        $text = 'Test text';

        $course = self::getDataGenerator()->create_course();

        $filterscormlink = new filter_scormlink(context_course::instance($course->id), array());

        self::assertEquals($text, $filterscormlink->filter($text));

        self::resetAllData();
    }

    /**
     * Test test_get_params_renderer function ok
     * Scorm does not exist
     *
     * @covers filter_scormlink::filter
     */
    public function test_filter_nok_not_exist() {
        $this->resetAfterTest(true);

        $text = '<a href="https://mentor.gouv.fr/mod/scorm/view.php?id=10">Test text</a>';

        $course = self::getDataGenerator()->create_course();

        $filterscormlink = new filter_scormlink(context_course::instance($course->id), array());

        self::assertEquals($text, $filterscormlink->filter($text));

        self::resetAllData();
    }

    /**
     * Test test_get_params_renderer function not ok
     * Scorm disable popup
     *
     * @covers filter_scormlink::filter
     */
    public function test_filter_nok_disable_popup() {
        global $DB;

        $this->resetAfterTest(true);

        $text = '<a href="https://mentor.gouv.fr/mod/scorm/view.php?id=10">Test text</a>';

        $course = self::getDataGenerator()->create_course();

        $filterscormlink = new filter_scormlink(context_course::instance($course->id), array());

        // Create DB Mock.
        $DB = $this->createMock(get_class($DB));

        // Setting scorm data.
        $scorm = new \stdClass();
        $scorm->popup = 0;

        // Return scorm data with disable popup.
        // When get_record_sql function call one time.
        $DB->expects($this->once())
            ->method('get_record_sql')
            ->will($this->returnValue($scorm));

        self::assertEquals($text, $filterscormlink->filter($text));

        self::resetAllData();
    }

    /**
     * Test test_get_params_renderer function not ok
     * Bad link regex
     *
     * @covers filter_scormlink::filter
     */
    public function test_filter_nok_bad_link_regex() {
        $this->resetAfterTest(true);

        $text = '<a href="https://mentor.gouv.fr/mod/url/view.php?id=10">Test text</a>';

        $course = self::getDataGenerator()->create_course();

        $filterscormlink = new filter_scormlink(context_course::instance($course->id), array());

        self::assertEquals($text, $filterscormlink->filter($text));

        self::resetAllData();
    }

    /**
     * Test test_get_params_renderer function ok
     *
     * @covers filter_scormlink::filter
     */
    public function test_filter_ok() {
        global $DB;

        $this->resetAfterTest(true);

        $text = '<a href="https://mentor.gouv.fr/mod/scorm/view.php?id=10">Test text</a>';
        $textfilter = '<a href="#" onclick="window.open(' .
                      '\'https://mentor.gouv.fr/local/mentor_core/pages/scorm.php?cmid=10\',' .
                      '\'_blank\').focus();return false;">Test text</a>';

        $course = self::getDataGenerator()->create_course();

        $filterscormlink = new filter_scormlink(context_course::instance($course->id), array());

        // Create DB Mock.
        $DB = $this->createMock(get_class($DB));

        // Setting scorm data.
        $scorm = new \stdClass();
        $scorm->popup = 1;

        // Return scorm data with disable popup.
        // When get_record_sql function call one time.
        $DB->expects($this->once())
            ->method('get_record_sql')
            ->will($this->returnValue($scorm));

        self::assertEquals($textfilter, $filterscormlink->filter($text));

        self::resetAllData();
    }
}
