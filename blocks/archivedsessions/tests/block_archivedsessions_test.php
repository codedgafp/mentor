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
 * block archivedsessions tests
 *
 * @package    block_archivedsessions
 * @copyright  2022 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/blocks/archivedsessions/block_archivedsessions.php');

class block_archivedsessions_testcase extends advanced_testcase {

    /**
     * Test html_attributes function
     *
     * @covers block_archivedsessions::html_attributes
     */
    public function test_html_attributes() {
        $this->resetAfterTest(true);
        self::setAdminUser();

        $block               = new \block_archivedsessions();
        $block->instance     = new \stdClass();
        $block->instance->id = 0;
        $htmlattribute       = $block->html_attributes();
        self::assertContains('hidden-block', $htmlattribute['class']);

        self::resetAllData();
    }

    /**
     * Test init function
     *
     * @covers block_archivedsessions::init
     */
    public function test_init() {
        $this->resetAfterTest(true);
        self::setAdminUser();

        $block = new \block_archivedsessions();
        $block->init();
        self::assertContains(get_string('pluginname', 'block_archivedsessions'), $block->title);

        self::resetAllData();
    }

    /**
     * Test applicable_formats function
     *
     * @covers block_archivedsessions::applicable_formats
     */
    public function test_applicable_formats() {
        $this->resetAfterTest(true);
        self::setAdminUser();

        $block             = new \block_archivedsessions();
        $applicableformats = $block->applicable_formats();
        self::assertIsArray($applicableformats);
        self::assertArrayHasKey('my', $applicableformats);
        self::assertTrue($applicableformats['my']);

        self::resetAllData();
    }

    /**
     * Test instance_allow_multiple function
     *
     * @covers block_archivedsessions::instance_allow_multiple
     */
    public function test_instance_allow_multiple() {
        $this->resetAfterTest(true);
        self::setAdminUser();

        $block = new \block_archivedsessions();
        self::assertFalse($block->instance_allow_multiple());

        self::resetAllData();
    }

    /**
     * Test hide_header function
     *
     * @covers block_archivedsessions::hide_header
     */
    public function test_hide_header() {
        $this->resetAfterTest(true);
        self::setAdminUser();

        $block = new \block_archivedsessions();
        self::assertTrue($block->hide_header());

        self::resetAllData();
    }

    /**
     * Test get_required_javascript function
     *
     * @covers block_archivedsessions::get_required_javascript
     */
    public function test_get_required_javascript() {

        $this->resetAfterTest(true);
        self::setAdminUser();

        $block       = new \block_archivedsessions();
        $block->page = new moodle_page();
        $block->get_required_javascript();

        $reflection = new ReflectionClass($block->page->requires);
        $instance   = $reflection->getProperty('stringsforjs_as');
        $instance->setAccessible(true); // Now we can modify that :).

        $stringjs = $instance->getValue($block->page->requires);

        self::assertArrayHasKey('local_mentor_core', $stringjs);
        self::assertArrayHasKey('next', $stringjs['local_mentor_core']);
        self::assertArrayHasKey('previous', $stringjs['local_mentor_core']);
        self::assertArrayHasKey('copylinktext', $stringjs['local_mentor_core']);

        self::assertArrayHasKey('block_archivedsessions', $stringjs);
        self::assertArrayHasKey('session', $stringjs['block_archivedsessions']);
        self::assertArrayHasKey('showmore', $stringjs['block_archivedsessions']);
        self::assertArrayHasKey('showless', $stringjs['block_archivedsessions']);

        $reflection = new ReflectionClass($block->page->requires);
        $instance   = $reflection->getProperty('amdjscode');
        $instance->setAccessible(true); // Now we can modify that :).

        $amdjs = $instance->getValue($block->page->requires);

        self::assertContains('block_archivedsessions/block_archivedsessions', $amdjs[1]);

        self::resetAllData();
    }

    /**
     * Test get_content function
     *
     * @covers block_archivedsessions::get_content
     */
    public function test_get_content() {

        $this->resetAfterTest(true);
        self::setAdminUser();

        $block       = new \block_archivedsessions();
        $block->page = new moodle_page();
        $content = $block->get_content();

        self::assertIsObject($content);
        self::assertObjectHasAttribute('text', $content);
        self::assertEmpty($content->footer);

        self::assertObjectHasAttribute('footer', $content);
        self::assertEmpty($content->footer);

        self::resetAllData();
    }

    /**
     * Test help_button function
     *
     * @covers block_archivedsessions::help_button
     */
    public function test_help_button() {

        $this->resetAfterTest(true);
        self::setAdminUser();

        $block       = new \block_archivedsessions();
        $block->page = new moodle_page();
        $helpbutton  = $block->help_button();

        self::assertContains(get_string('helpbuttontext', 'block_archivedsessions', get_config('theme_mentor', 'faq')),
            $helpbutton);
        self::assertContains(get_string('helpbuttontexttitle', 'block_archivedsessions'), $helpbutton);

        self::resetAllData();
    }
}
