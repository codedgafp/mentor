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
 * Test theme mentor renderers
 *
 * @package    theme_mentor
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     rcolet <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/theme/mentor/renderers.php');
require_once($CFG->dirroot . '/local/mentor_core/api/training.php');
require_once($CFG->dirroot . '/local/mentor_core/api/session.php');

class theme_mentor_core_renderer_testcase extends \advanced_testcase {

    /**
     * Init $CFG
     */
    public function init_config() {
        global $CFG;

        $CFG->mentor_specializations = [];
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
     * Init training object
     *
     * @return stdClass
     * @throws dml_exception
     * @throws moodle_exception
     */
    private function get_training_data($entitydata = null) {

        // Init test data.
        $trainingdata = new \stdClass();

        $trainingdata->name      = 'fullname';
        $trainingdata->shortname = 'shortname';
        $trainingdata->content   = 'summary';

        // Create training object.
        $trainingdata->traininggoal = 'TEST TRAINING';
        $trainingdata->thumbnail    = '';
        $trainingdata->status       = \local_mentor_core\training::STATUS_DRAFT;

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
     * Test should_display_navbar_logo
     *
     * @covers \theme_mentor_core_renderer::should_display_navbar_logo
     */
    public function test_should_display_navbar_logo_ok() {
        global $PAGE;

        $this->resetAfterTest(true);
        $this->reset_singletons();
        $this->init_config();

        $renderer = new \theme_mentor_core_renderer($PAGE, 'target');

        self::assertTrue($renderer->should_display_navbar_logo());

        self::resetAllData();
    }

    /**
     * Test favicon
     *
     * @covers \theme_mentor_core_renderer::favicon
     */
    public function test_favicon_ok() {
        global $PAGE, $CFG;

        $this->resetAfterTest(true);
        $this->reset_singletons();
        $this->init_config();

        $renderer = new \theme_mentor_core_renderer($PAGE, 'target');

        $CFG->sitetype = 'azure_dev';
        self::assertEquals($renderer->favicon(), $CFG->wwwroot . '/theme/mentor/pix/favicon_dev.ico');

        $CFG->sitetype = 'azure_formation';
        self::assertEquals($renderer->favicon()->out(), $CFG->wwwroot . '/theme/image.php/_s/boost/theme/1/favicon');

        $CFG->sitetype = 'azure_hotfix';
        self::assertEquals($renderer->favicon(), $CFG->wwwroot . '/theme/mentor/pix/favicon_preprod.ico');

        $CFG->sitetype = 'azure_iso_prod';
        self::assertEquals($renderer->favicon()->out(), $CFG->wwwroot . '/theme/image.php/_s/boost/theme/1/favicon');

        $CFG->sitetype = 'azure_iso_qualification';
        self::assertEquals($renderer->favicon(), $CFG->wwwroot . '/theme/mentor/pix/favicon_qualif.ico');

        $CFG->sitetype = 'azure_test';
        self::assertEquals($renderer->favicon(), $CFG->wwwroot . '/theme/mentor/pix/favicon_dev.ico');

        $CFG->sitetype = 'dev';
        self::assertEquals($renderer->favicon(), $CFG->wwwroot . '/theme/mentor/pix/favicon_dev.ico');

        $CFG->sitetype = 'developpement';
        self::assertEquals($renderer->favicon(), $CFG->wwwroot . '/theme/mentor/pix/favicon_dev.ico');

        $CFG->sitetype = 'preprod';
        self::assertEquals($renderer->favicon(), $CFG->wwwroot . '/theme/mentor/pix/favicon_preprod.ico');

        $CFG->sitetype = 'preproduction';
        self::assertEquals($renderer->favicon(), $CFG->wwwroot . '/theme/mentor/pix/favicon_preprod.ico');

        $CFG->sitetype = 'pre-production';
        self::assertEquals($renderer->favicon(), $CFG->wwwroot . '/theme/mentor/pix/favicon_preprod.ico');

        $CFG->sitetype = 'prod';
        self::assertEquals($renderer->favicon()->out(), $CFG->wwwroot . '/theme/image.php/_s/boost/theme/1/favicon');

        $CFG->sitetype = 'production';
        self::assertEquals($renderer->favicon()->out(), $CFG->wwwroot . '/theme/image.php/_s/boost/theme/1/favicon');

        $CFG->sitetype = 'qualif';
        self::assertEquals($renderer->favicon(), $CFG->wwwroot . '/theme/mentor/pix/favicon_qualif.ico');

        $CFG->sitetype = 'qualification';
        self::assertEquals($renderer->favicon(), $CFG->wwwroot . '/theme/mentor/pix/favicon_qualif.ico');

        $CFG->sitetype = 'azure_test';
        self::assertEquals($renderer->favicon(), $CFG->wwwroot . '/theme/mentor/pix/favicon_dev.ico');

        $CFG->sitetype = 'azure_dev';
        self::assertEquals($renderer->favicon(), $CFG->wwwroot . '/theme/mentor/pix/favicon_dev.ico');

        self::resetAllData();
    }

    /**
     * Test site_type
     *
     * @covers \theme_mentor_core_renderer::site_type
     */
    public function test_site_type_ok() {
        global $PAGE, $CFG;

        $this->resetAfterTest(true);
        $this->reset_singletons();
        $this->init_config();

        $renderer = new \theme_mentor_core_renderer($PAGE, 'target');

        self::assertEmpty($renderer->site_type());

        $CFG->sitetype = 'dev';
        self::assertEquals('<span id="site-type" class="type-' . $CFG->sitetype . '"> ' . $CFG->sitetype . '</span>',
            $renderer->site_type());

        $CFG->sitetype = 'prod';
        self::assertEmpty($renderer->site_type());

        $CFG->sitetype = 'test_type';
        self::assertEquals('<span id="site-type" class="type-' . $CFG->sitetype . '"> ' . $CFG->sitetype . '</span>',
            $renderer->site_type());

        self::resetAllData();
    }

    /**
     * Test custom_menu
     *
     * @covers \theme_mentor_core_renderer::custom_menu
     */
    public function test_custom_menu_ok() {
        global $PAGE, $CFG;

        $this->resetAfterTest(true);
        $this->reset_singletons();
        $this->init_config();

        $renderer = new \theme_mentor_core_renderer($PAGE, 'target');

        $cataloglink = $CFG->wwwroot . '/local/catalog/index.php';
        $render      = '<li class="nav-item catalog">';
        $render      .= '<a class="nav-item nav-link" href="' . $cataloglink . '">';
        $render      .= $renderer->pix_icon('offre', '', 'theme_mentor');
        $render      .= '<span>' . get_string('trainingcatalog', 'theme_mentor') . '</span>';
        $render      .= '</a>';
        $render      .= '</li>';

        self::assertEquals($render, $renderer->custom_menu());

        self::resetAllData();
    }

    /**
     * Test contact_page
     *
     * @covers \theme_mentor_core_renderer::contact_page
     */
    public function test_contact_page_ok() {
        global $PAGE;

        $this->resetAfterTest(true);
        $this->reset_singletons();
        $this->init_config();

        $renderer = new \theme_mentor_core_renderer($PAGE, 'target');

        self::assertEquals('', $renderer->contact_page());

        self::resetAllData();
    }

    /**
     * Test textinfofooter_page
     *
     * @covers \theme_mentor_core_renderer::textinfofooter_page
     */
    public function test_textinfofooter_page_ok() {
        global $PAGE;

        $this->resetAfterTest(true);
        $this->reset_singletons();
        $this->init_config();

        $renderer = new \theme_mentor_core_renderer($PAGE, 'target');

        self::assertEquals(get_config('theme_mentor', 'textinfofooter'), $renderer->textinfofooter_page());

        self::resetAllData();
    }

    /**
     * Test about_page
     *
     * @covers \theme_mentor_core_renderer::about_page
     */
    public function test_about_page_ok() {
        global $PAGE;

        $this->resetAfterTest(true);
        $this->reset_singletons();
        $this->init_config();

        $renderer = new \theme_mentor_core_renderer($PAGE, 'target');

        self::assertEquals(get_config('theme_mentor', 'about'), $renderer->about_page());

        self::resetAllData();
    }

    /**
     * Test legalnotice_page
     *
     * @covers \theme_mentor_core_renderer::legalnotice_page
     */
    public function test_legalnotice_page_ok() {
        global $PAGE;

        $this->resetAfterTest(true);
        $this->reset_singletons();
        $this->init_config();

        $renderer = new \theme_mentor_core_renderer($PAGE, 'target');

        self::assertEquals(get_config('theme_mentor', 'legalnotice'), $renderer->legalnotice_page());

        self::resetAllData();
    }

    /**
     * Test faq_page
     *
     * @covers \theme_mentor_core_renderer::faq_page
     */
    public function test_faq_page_ok() {
        global $PAGE;

        $this->resetAfterTest(true);
        $this->reset_singletons();
        $this->init_config();

        $renderer = new \theme_mentor_core_renderer($PAGE, 'target');

        self::assertEquals(get_config('theme_mentor', 'faq'), $renderer->faq_page());

        self::resetAllData();
    }

    /**
     * Test versionnumber_page
     *
     * @covers \theme_mentor_core_renderer::versionnumber_page
     */
    public function test_versionnumber_page_ok() {
        global $PAGE;

        $this->resetAfterTest(true);
        $this->reset_singletons();
        $this->init_config();

        $renderer = new \theme_mentor_core_renderer($PAGE, 'target');

        self::assertEquals(get_config('theme_mentor', 'versionnumber'), $renderer->versionnumber_page());

        self::resetAllData();
    }

    /**
     * Test mentorlicence_page
     *
     * @covers \theme_mentor_core_renderer::mentorlicence_page
     */
    public function test_mentorlicence_page_ok() {
        global $PAGE;

        $this->resetAfterTest(true);
        $this->reset_singletons();
        $this->init_config();

        $renderer = new \theme_mentor_core_renderer($PAGE, 'target');

        self::assertEquals(get_config('theme_mentor', 'mentorlicence'), $renderer->mentorlicence_page());

        self::resetAllData();
    }

    /**
     * Test externallink_page
     *
     * @covers \theme_mentor_core_renderer::externallink_page
     */
    public function test_externallink_page_ok() {
        global $PAGE;

        $this->resetAfterTest(true);
        $this->reset_singletons();
        $this->init_config();

        $renderer = new \theme_mentor_core_renderer($PAGE, 'target');

        self::assertEquals(explode("|", get_config('theme_mentor', 'externallinks')), $renderer->externallink_page());

        self::resetAllData();
    }

    /**
     * Test accessibility_page
     *
     * @covers \theme_mentor_core_renderer::accessibility_page
     */
    public function test_accessibility_page_ok() {
        global $PAGE;

        $this->resetAfterTest(true);
        $this->reset_singletons();
        $this->init_config();

        $renderer = new \theme_mentor_core_renderer($PAGE, 'target');

        self::assertEquals(get_config('theme_mentor', 'accessibility'), $renderer->accessibility_page());

        self::resetAllData();
    }

    /**
     * Test personaldata_page
     *
     * @covers \theme_mentor_core_renderer::personaldata_page
     */
    public function test_personaldata_page_ok() {
        global $PAGE;

        $this->resetAfterTest(true);
        $this->reset_singletons();
        $this->init_config();

        $renderer = new \theme_mentor_core_renderer($PAGE, 'target');

        self::assertEquals(get_config('theme_mentor', 'personaldata'), $renderer->personaldata_page());

        self::resetAllData();
    }

    /**
     * Test urllogo_page
     *
     * @covers \theme_mentor_core_renderer::urllogo_page
     */
    public function test_urllogo_page_ok() {
        global $PAGE;

        $this->resetAfterTest(true);
        $this->reset_singletons();
        $this->init_config();

        $renderer = new \theme_mentor_core_renderer($PAGE, 'target');

        self::assertEquals($renderer->get_logo_url(), $renderer->urllogo_page());

        self::resetAllData();
    }

    /**
     * Test islogged_page
     *
     * @covers \theme_mentor_core_renderer::islogged_page
     */
    public function test_islogged_page_ok() {
        global $PAGE;

        $this->resetAfterTest(true);
        $this->reset_singletons();
        $this->init_config();

        $renderer = new \theme_mentor_core_renderer($PAGE, 'target');

        self::assertEquals(isloggedin(), $renderer->islogged_page());

        self::resetAllData();
    }

    /**
     * Test course_header
     *
     * @covers \theme_mentor_core_renderer::course_header
     */
    public function test_course_header_ok() {
        global $PAGE, $COURSE, $CFG;

        $this->resetAfterTest(true);
        $this->reset_singletons();
        $this->init_config();

        self::setAdminUser();

        $renderer       = new \theme_mentor_core_renderer($PAGE, 'target');
        $rendererparent = new \core_renderer($PAGE, 'target');

        // With standard header.
        self::assertEquals($rendererparent->course_header(), $renderer->course_header());

        // Header with training status.
        $trainingdata = $this->get_training_data(['name' => 'New Entity 1', 'shortname' => 'New Entity 1']);
        $training     = \local_mentor_core\training_api::create_training($trainingdata);
        $COURSE->id   = $training->get_course()->id;
        self::assertEquals($rendererparent->course_header() . '<div id="course-status">' .
                           get_string($training->status, 'local_trainings') . '</div>', $renderer->course_header());

        // Header with session status.
        $sessionname = 'TESTUNITCREATESESSION';
        $session     = \local_mentor_core\session_api::create_session($training->id, $sessionname, true);
        $COURSE->id  = $session->get_course()->id;
        self::assertEquals($rendererparent->course_header() . '<div id="course-status">' .
                           get_string($session->status, 'local_session') . '</div>', $renderer->course_header());

        self::resetAllData();
    }

    /**
     * Test course_header
     *
     * @covers \theme_mentor_core_renderer::standard_footer_html
     */
    public function test_standard_footer_html_ok() {
        global $PAGE, $CFG;

        require_once($CFG->dirroot . '/local/mentor_core/lib.php');

        $this->resetAfterTest(true);
        $this->reset_singletons();
        $this->init_config();

        $renderer       = new \theme_mentor_core_renderer($PAGE, 'target');
        $rendererparent = new \core_renderer($PAGE, 'target');

        $output = $rendererparent->standard_footer_html();

        self::assertEquals(local_mentor_core_get_footer_specialization($output), $renderer->standard_footer_html());

        self::resetAllData();
    }

    /**
     * Test header
     * Ok
     *
     * @covers \theme_mentor_core_renderer::header
     */
    public function test_header_ok() {
        global $PAGE, $CFG, $DB;

        require_once($CFG->dirroot . '/local/mentor_core/lib.php');

        $this->resetAfterTest(true);
        $this->reset_singletons();
        $this->init_config();

        $CFG->noemailever = 'no';

        self::setAdminUser();

        \local_mentor_core\library_api::get_or_create_library();
        $trainingdata    = $this->get_training_data(['name' => 'New Entity 1', 'shortname' => 'New Entity 1']);
        $training        = \local_mentor_core\training_api::create_training($trainingdata);
        $traininglibrary = \local_mentor_core\library_api::publish_to_library($training->id, true);

        // Make sure a debugging message is triggered.
        $this->assertDebuggingCalled('Not sending email due to $CFG->noemailever config setting');

        $PAGE->set_course($traininglibrary->get_course());
        $PAGE->set_url($traininglibrary->get_url()->out());
        $renderer = new \theme_mentor_core_renderer($PAGE, 'target');

        $output = $renderer->header();

        self::assertStringContainsString(
            'demo-course',
            $output
        );
        self::resetAllData();
    }

    /**
     * Test header
     * Not ok
     *
     * @covers \theme_mentor_core_renderer::header
     */
    public function test_header_notok() {
        global $PAGE, $CFG;

        require_once($CFG->dirroot . '/local/mentor_core/lib.php');

        $this->resetAfterTest(true);
        $this->reset_singletons();
        $this->init_config();

        self::setAdminUser();

        $PAGE->set_url('/');
        $renderer = new \theme_mentor_core_renderer($PAGE, 'target');

        $renderer->header();

        self::assertStringNotContainsString(
            'demo-course',
            $PAGE->bodyclasses
        );

        self::resetAllData();
    }
}
