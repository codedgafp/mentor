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
 * Test cases for class mentor_session
 *
 * @package    local_mentor_specialization
 * @copyright  2021 Edunao SAS (contact@edunao.com)
 * @author     adrien <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/local/mentor_core/api/entity.php');
require_once($CFG->dirroot . '/local/mentor_core/api/training.php');
require_once($CFG->dirroot . '/local/mentor_core/api/session.php');
require_once($CFG->dirroot . '/local/mentor_core/api/profile.php');
require_once($CFG->dirroot . '/local/mentor_core/lib.php');
require_once($CFG->dirroot . '/local/mentor_specialization/classes/models/mentor_session.php');
require_once($CFG->dirroot . '/lib/enrollib.php');

class local_mentor_specialization_session_class_testcase extends advanced_testcase {

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

    /**
     * Reset the singletons
     *
     * @throws ReflectionException
     */
    public function reset_singletons() {
        // Reset the mentor core specialization singleton.
        $specialization = \local_mentor_core\specialization::get_instance();
        $reflection     = new ReflectionClass($specialization);
        $instance       = $reflection->getProperty('instance');
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
     * Initialization of the session or trainig data
     *
     * @param false $training
     * @param null $sessionid
     * @return stdClass
     */
    public function init_session_data($training = false, $sessionid = null) {
        $data = new stdClass();

        set_config('collections', 'accompagnement|Accompagnement des transitions professionnelles|#CECECE',
            'local_mentor_specialization');

        if ($training) {
            $data->name      = 'fullname';
            $data->shortname = 'shortname';
            $data->content   = 'summary';
            $data->status    = 'ec';
        } else {
            $data->trainingname      = 'fullname';
            $data->trainingshortname = 'shortname';
            $data->trainingcontent   = 'summary';
            $data->trainingstatus    = 'ec';
        }

        // Fields for taining.
        $data->teaser                       = 'http://www.edunao.com/';
        $data->teaserpicture                = '';
        $data->prerequisite                 = 'TEST';
        $data->collection                   = 'accompagnement';
        $data->traininggoal                 = 'TEST TRAINING ';
        $data->idsirh                       = 'TEST ID SIRH';
        $data->licenseterms                 = 'cc-sa';
        $data->typicaljob                   = 'TEST';
        $data->skills                       = [];
        $data->certifying                   = '1';
        $data->presenceestimatedtimehours   = '12';
        $data->presenceestimatedtimeminutes = '10';
        $data->remoteestimatedtimehours     = '15';
        $data->remoteestimatedtimeminutes   = '30';
        $data->trainingmodalities           = 'd';
        $data->producingorganization        = 'TEST';
        $data->producerorganizationlogo     = '';
        $data->designers                    = 'TEST';
        $data->contactproducerorganization  = 'TEST';
        $data->thumbnail                    = '';

        // Specific fields for session (only for update).
        if ($sessionid) {
            $data->id                      = $sessionid;
            $data->opento                  = 'all';
            $data->publiccible             = 'TEST';
            $data->termsregistration       = 'autre';
            $data->termsregistrationdetail = 'TEST';

            $data->onlinesessionestimatedtimehours     = '10';
            $data->onlinesessionestimatedtimeminutes   = '15';
            $data->presencesessionestimatedtimehours   = '12';
            $data->presencesessionestimatedtimeminutes = '25';

            $data->sessionpermanent    = 0;
            $data->sessionstartdate    = 1609801200;
            $data->sessionenddate      = 1609801200;
            $data->sessionmodalities   = 'presentiel';
            $data->accompaniment       = 'TEST';
            $data->maxparticipants     = 10;
            $data->placesavailable     = 8;
            $data->numberparticipants  = 2;
            $data->location            = 'PARIS';
            $data->organizingstructure = 'TEST ORGANISATION';
            $data->sessionnumber       = 1;
            $data->opentolist          = '';
        }

        return $data;
    }

    /**
     * Init training categery by entity id
     */
    public function init_training_entity($data, $entity) {
        // Get "Formation" category id (child of entity category).
        $formationid           = $entity->get_entity_formation_category();
        $data->categorychildid = $formationid;

        $data->categoryid        = $entity->id;
        $data->creativestructure = $entity->id;

        return $data;
    }

    /**
     * Init training creation
     *
     * @return training
     * @throws moodle_exception
     */
    public function init_training_creation() {
        global $DB;

        // Remove the miscelleanous category.
        $DB->delete_records('course_categories', array('id' => 1));

        // Init test data.
        $data = $this->init_session_data(true);

        try {
            // Get entity object for default category.
            $entityid = \local_mentor_core\entity_api::create_entity([
                'name'      => 'New Entity 1',
                'shortname' => 'New Entity 1'
            ]);

            $entity = \local_mentor_core\entity_api::get_entity($entityid);
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        // Init data with entity data.
        $data = $this->init_training_entity($data, $entity);

        // Test standard training creation.
        try {
            $training = \local_mentor_core\training_api::create_training($data);
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        return $training;
    }

    /**
     * Init session creation
     *
     * @return int
     * @throws moodle_exception
     */
    public function init_session_creation() {
        // Create training.
        $training = $this->init_training_creation();

        $sessionname = 'TESTUNITCREATESESSION';

        // Test standard session creation.
        try {
            $session = \local_mentor_core\session_api::create_session($training->id, $sessionname, true);
        } catch (\Exception $e) {
            self::fail($e->getMessage());
        }

        return $session->id;
    }

    /**
     * Test prepare edit form
     *
     * @covers \local_mentor_specialization\mentor_session::prepare_edit_form
     * @covers \local_mentor_core\session::prepare_edit_form
     */
    public function test_prepare_edit_form_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->init_database();
        $this->reset_singletons();
        $this->init_config();

        self::setAdminUser();

        $sessionid                       = $this->init_session_creation();
        $session                         = \local_mentor_core\session_api::get_session($sessionid);
        $training                        = $session->get_training();
        $training->presenceestimatedtime = 120;
        $training->remoteestimatedtime   = 130;
        $session->get_training()->update($training);
        $session->presencesessionestimatedtime = 140;
        $session->onlinesessionestimatedtime   = 150;
        $session->update($session);

        $sessioneditform = $session->prepare_edit_form();

        self::assertEquals($sessioneditform->trainingname, $session->get_training()->name);
        self::assertEquals($sessioneditform->trainingshortname, $session->get_training()->shortname);
        self::assertEquals($sessioneditform->trainingcontent['text'], $session->get_training()->content);
        self::assertEquals($sessioneditform->teaser, $session->get_training()->teaser);
        self::assertEquals($sessioneditform->teaserpicture, $session->get_training()->teaserpicture);
        self::assertEquals($sessioneditform->prerequisite, $session->get_training()->prerequisite);
        self::assertEquals($sessioneditform->collection, $session->get_training()->collection);
        self::assertEquals($sessioneditform->creativestructure, $session->get_training()->creativestructure);
        self::assertEquals($sessioneditform->traininggoal['text'], $session->get_training()->traininggoal);
        self::assertEquals($sessioneditform->idsirh, $session->get_training()->idsirh);
        self::assertEquals($sessioneditform->trainingmodalities, $session->get_training()->trainingmodalities);
        self::assertEquals($sessioneditform->typicaljob, $session->get_training()->typicaljob);
        self::assertEquals($sessioneditform->skills, $session->get_training()->skills);
        self::assertEquals($sessioneditform->certifying, $session->get_training()->certifying);
        self::assertEquals($sessioneditform->presenceestimatedtime, $session->get_training()->presenceestimatedtime);
        self::assertEquals($sessioneditform->presenceestimatedtimehours, floor($training->presenceestimatedtime / 60));
        self::assertEquals($sessioneditform->presenceestimatedtimeminutes, $training->presenceestimatedtime % 60);
        self::assertEquals($sessioneditform->remoteestimatedtime, $session->get_training()->remoteestimatedtime);
        self::assertEquals($sessioneditform->remoteestimatedtimehours, floor($training->remoteestimatedtime / 60));
        self::assertEquals($sessioneditform->remoteestimatedtimeminutes, $training->remoteestimatedtime % 60);
        self::assertEquals($sessioneditform->presencesessionestimatedtimehours, floor($session->presencesessionestimatedtime / 60));
        self::assertEquals($sessioneditform->presencesessionestimatedtimeminutes, $session->presencesessionestimatedtime % 60);
        self::assertEquals($sessioneditform->onlinesessionestimatedtimehours, floor($session->onlinesessionestimatedtime / 60));
        self::assertEquals($sessioneditform->onlinesessionestimatedtimeminutes, $session->onlinesessionestimatedtime % 60);
        self::assertEquals($sessioneditform->producingorganization, $session->get_training()->producingorganization);
        self::assertEquals($sessioneditform->producerorganizationlogo, $session->get_training()->producerorganizationlogo);
        self::assertEquals($sessioneditform->contactproducerorganization, $session->get_training()->contactproducerorganization);
        self::assertEquals($sessioneditform->thumbnail, $session->get_training()->thumbnail);
        self::assertEquals($sessioneditform->trainingstatus, $session->get_training()->status);
        self::assertEquals($sessioneditform->licenseterms, $session->get_training()->licenseterms);
        self::assertEquals($sessioneditform->designers, $session->get_training()->designers);
        self::assertEquals($sessioneditform->timecreated, $session->get_training()->timecreated);
        self::assertEquals($sessioneditform->trainingcontent, array('text' => $session->get_training()->content));
        self::assertEquals($sessioneditform->traininggoal, array('text' => $session->get_training()->traininggoal));

        self::resetAllData();
    }

    /**
     * Test update_status method (to open_to_registration)
     *
     * @covers  \local_mentor_specialization\mentor_session::update_status
     * @covers  \local_mentor_specialization\mentor_session::update
     * @covers  \local_mentor_specialization\mentor_session::open_to_registration
     */
    public function test_update_status_to_open_to_registration() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->init_database();
        $this->reset_singletons();
        $this->init_config();

        self::setAdminUser();

        // Create session.
        $sessionid               = $this->init_session_creation();
        $session                 = \local_mentor_core\session_api::get_session($sessionid);
        $data                    = new stdClass();
        $data->termsregistration = 'inscriptionlibre';
        $session->update($data);

        $session->update_status(\local_mentor_core\session::STATUS_OPENED_REGISTRATION);

        // Status is "in preparation".
        self::assertEquals($session->status, \local_mentor_core\session::STATUS_OPENED_REGISTRATION);
        self::assertEquals($session->get_course()->visible, 1);
        $selfenrolmentinstance = $session->get_enrolment_instances_by_type('self');
        // Enrolment is enable.
        self::assertEquals($selfenrolmentinstance->status, 0);

        $data                    = new stdClass();
        $data->termsregistration = 'autre';
        $session->update($data);

        $session->update_status(\local_mentor_core\session::STATUS_OPENED_REGISTRATION);

        // Status is "in preparation".
        self::assertEquals($session->status, \local_mentor_core\session::STATUS_OPENED_REGISTRATION);
        self::assertEquals($session->get_course()->visible, 1);
        $selfenrolmentinstance = $session->get_enrolment_instances_by_type('self');
        // Enrolment is disable.
        self::assertEquals($selfenrolmentinstance->status, 1);

        $this->resetAllData();
    }

    /**
     * Test update_status method (report to open_to_registration)
     *
     * @covers  \local_mentor_specialization\mentor_session::update_status
     * @covers  \local_mentor_specialization\mentor_session::open_to_registration
     * @covers  \local_mentor_specialization\mentor_session::send_message_to_all
     */
    public function test_update_status_report_to_open_to_registration() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->init_database();
        $this->reset_singletons();
        $this->init_config();

        self::setAdminUser();

        // Create session.
        $sessionid = $this->init_session_creation();
        $session   = \local_mentor_core\session_api::get_session($sessionid);
        $session->create_self_enrolment_instance();

        $session->update_status(\local_mentor_core\session::STATUS_REPORTED);

        // Status is "complete".
        self::assertEquals($session->status, \local_mentor_core\session::STATUS_REPORTED);
        self::assertEquals($session->get_course()->visible, 0);
        $selfenrolmentinstance = $session->get_enrolment_instances_by_type('self');
        self::assertEquals($selfenrolmentinstance->status, 1);

        $session->update_status(
            \local_mentor_core\session::STATUS_OPENED_REGISTRATION,
            \local_mentor_core\session::STATUS_REPORTED
        );

        // Status is "in preparation".
        self::assertEquals($session->status, \local_mentor_core\session::STATUS_OPENED_REGISTRATION);
        self::assertEquals($session->get_course()->visible, 1);
        $selfenrolmentinstance = $session->get_enrolment_instances_by_type('self');
        self::assertEquals($selfenrolmentinstance->status, 1);

        $this->resetAllData();
    }

    /**
     * Test update_status method (to open)
     *
     * @covers  \local_mentor_specialization\mentor_session::update_status
     * @covers  \local_mentor_specialization\mentor_session::open
     * @covers  \local_mentor_specialization\mentor_session::show_course
     */
    public function test_update_status_to_open() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->init_database();
        $this->reset_singletons();
        $this->init_config();

        self::setAdminUser();

        // Create session.
        $sessionid = $this->init_session_creation();
        $session   = \local_mentor_core\session_api::get_session($sessionid);

        $data                    = new stdClass();
        $data->termsregistration = 'inscriptionlibre';
        $session->update($data);

        self::assertFalse($session->get_enrolment_instances_by_type('self'));

        $session->update_status(\local_mentor_core\session::STATUS_IN_PROGRESS);

        // Status is "open".
        self::assertEquals($session->status, \local_mentor_core\session::STATUS_IN_PROGRESS);
        self::assertEquals($session->get_course()->visible, 1);
        $selfenrolmentinstance = $session->get_enrolment_instances_by_type('self');
        // Self enrolment is enable.
        self::assertIsObject($selfenrolmentinstance);
        self::assertEquals($selfenrolmentinstance->status, 0);

        $data                    = new stdClass();
        $data->termsregistration = 'autre';
        $session->update($data);
        $session->update_status(\local_mentor_core\session::STATUS_IN_PROGRESS);

        // Status is "open".
        self::assertEquals($session->status, \local_mentor_core\session::STATUS_IN_PROGRESS);
        $selfenrolmentinstance = $session->get_enrolment_instances_by_type('self');
        // Self enrolment is disable.
        self::assertIsObject($selfenrolmentinstance);
        self::assertEquals($selfenrolmentinstance->status, 1);

        $this->resetAllData();
    }

    /**
     * Test update_status method (to archive)
     *
     * @covers  \local_mentor_specialization\mentor_session::update_status
     * @covers  \local_mentor_specialization\mentor_session::archive
     */
    public function test_update_status_to_archive() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->init_database();
        $this->reset_singletons();
        $this->init_config();

        self::setAdminUser();

        // Create session.
        $sessionid = $this->init_session_creation();
        $session   = \local_mentor_core\session_api::get_session($sessionid);
        $session->create_self_enrolment_instance();

        $session->update_status(\local_mentor_core\session::STATUS_ARCHIVED);

        // Status is "complete".
        self::assertEquals($session->status, \local_mentor_core\session::STATUS_ARCHIVED);
        self::assertEquals($session->get_course()->visible, 1);
        $selfenrolmentinstance = $session->get_enrolment_instances_by_type('self');
        self::assertEquals($selfenrolmentinstance->status, 1);

        $this->resetAllData();
    }

    /**
     * Test get available status
     *
     * @covers  \local_mentor_specialization\mentor_session::get_available_status
     */
    public function test_get_available_status_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->init_database();
        $this->reset_singletons();
        $this->init_config();

        self::setAdminUser();

        // Create session.
        $sessionid = $this->init_session_creation();
        $session   = \local_mentor_core\session_api::get_session($sessionid);

        $avaiblestatus = $session->get_available_status();

        // In preparation status.
        self::assertCount(2, $avaiblestatus);
        self::assertArrayHasKey(\local_mentor_specialization\mentor_session::STATUS_IN_PREPARATION, $avaiblestatus);
        self::assertArrayHasKey(\local_mentor_specialization\mentor_session::STATUS_OPENED_REGISTRATION, $avaiblestatus);

        $session->update_status(\local_mentor_specialization\mentor_session::STATUS_OPENED_REGISTRATION);

        $avaiblestatus = $session->get_available_status();

        // Opened registration status.
        self::assertCount(2, $avaiblestatus);
        self::assertArrayHasKey(\local_mentor_specialization\mentor_session::STATUS_OPENED_REGISTRATION, $avaiblestatus);
        self::assertArrayHasKey(\local_mentor_specialization\mentor_session::STATUS_REPORTED, $avaiblestatus);

        $session->update_status(\local_mentor_specialization\mentor_session::STATUS_IN_PROGRESS);

        $avaiblestatus = $session->get_available_status();

        // In progress status.
        self::assertCount(2, $avaiblestatus);
        self::assertArrayHasKey(\local_mentor_specialization\mentor_session::STATUS_IN_PROGRESS, $avaiblestatus);
        self::assertArrayHasKey(\local_mentor_specialization\mentor_session::STATUS_REPORTED, $avaiblestatus);

        $session->update_status(\local_mentor_specialization\mentor_session::STATUS_COMPLETED);

        $avaiblestatus = $session->get_available_status();

        // Completed status.
        self::assertCount(2, $avaiblestatus);
        self::assertArrayHasKey(\local_mentor_specialization\mentor_session::STATUS_COMPLETED, $avaiblestatus);
        self::assertArrayHasKey(\local_mentor_specialization\mentor_session::STATUS_ARCHIVED, $avaiblestatus);

        $session->update_status(\local_mentor_specialization\mentor_session::STATUS_REPORTED);

        $avaiblestatus = $session->get_available_status();

        // Reported status.
        self::assertCount(2, $avaiblestatus);
        self::assertArrayHasKey(\local_mentor_specialization\mentor_session::STATUS_REPORTED, $avaiblestatus);
        self::assertArrayHasKey(\local_mentor_specialization\mentor_session::STATUS_OPENED_REGISTRATION, $avaiblestatus);

        $session->update_status(\local_mentor_specialization\mentor_session::STATUS_CANCELLED);

        $avaiblestatus = $session->get_available_status();

        // Canceled status.
        self::assertCount(1, $avaiblestatus);
        self::assertArrayHasKey(\local_mentor_specialization\mentor_session::STATUS_CANCELLED, $avaiblestatus);

        $session->update_status(\local_mentor_specialization\mentor_session::STATUS_ARCHIVED);

        $avaiblestatus = $session->get_available_status();

        // Archived status.
        self::assertCount(1, $avaiblestatus);
        self::assertArrayHasKey(\local_mentor_specialization\mentor_session::STATUS_ARCHIVED, $avaiblestatus);

        // Change session end date.
        $data                   = new stdClass();
        $data->sessionstartdate = time();
        $data->sessionenddate   = strtotime('+3 months', time());
        $session->update($data);

        $avaiblestatus = $session->get_available_status();

        // Archived status.
        self::assertCount(2, $avaiblestatus);
        self::assertArrayHasKey(\local_mentor_specialization\mentor_session::STATUS_ARCHIVED, $avaiblestatus);
        self::assertArrayHasKey(\local_mentor_specialization\mentor_session::STATUS_COMPLETED, $avaiblestatus);

        $this->resetAllData();
    }

    /**
     * Test get_session_picture
     *
     * @covers  \local_mentor_specialization\mentor_session::get_session_picture
     */
    public function test_get_session_picture_ok() {
        global $CFG;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->init_database();
        $this->reset_singletons();
        $this->init_config();

        self::setAdminUser();

        // Create session.
        $sessionid = $this->init_session_creation();
        $session   = \local_mentor_core\session_api::get_session($sessionid);

        // Thumbnail not exist.
        self::assertFalse($session->get_session_picture());

        // Create thumbnail file.
        $fs                    = get_file_storage();
        $component             = 'local_session';
        $itemid                = $session->id;
        $filearea              = 'thumbnail';
        $contextid             = $session->get_context()->id;
        $filerecord            = new stdClass();
        $filerecord->contextid = $contextid;
        $filerecord->component = $component;
        $filerecord->filearea  = $filearea;
        $filerecord->itemid    = $itemid;
        $filerecord->filepath  = '/';
        $filerecord->filename  = 'logo.png';
        $filepath              = $CFG->dirroot . '/local/mentor_core/pix/logo.png';
        $fs->create_file_from_pathname($filerecord, $filepath);

        // Thumbnail exist.
        $thumbnailfile = $session->get_session_picture();
        self::assertEquals($component, $thumbnailfile->get_component());
        self::assertEquals($itemid, $thumbnailfile->get_itemid());
        self::assertEquals($filearea, $thumbnailfile->get_filearea());
        self::assertEquals($contextid, $thumbnailfile->get_contextid());

        // Create teaserpicture file.
        $filearea             = 'teaserpicture';
        $filerecord->filearea = $filearea;
        $fs->create_file_from_pathname($filerecord, $filepath);

        // Teaserpicture exist.
        $teaserpicturefile = $session->get_session_picture('teaserpicture');
        self::assertEquals($component, $teaserpicturefile->get_component());
        self::assertEquals($itemid, $teaserpicturefile->get_itemid());
        self::assertEquals($filearea, $teaserpicturefile->get_filearea());
        self::assertEquals($contextid, $teaserpicturefile->get_contextid());

        // Create teaserpicture file.
        $filearea             = 'otherpicture';
        $filerecord->filearea = $filearea;
        $fs->create_file_from_pathname($filerecord, $filepath);

        // Other file not allowed.
        self::assertFalse($session->get_session_picture('otherpicture'));

        $this->resetAllData();
    }

    /**
     * Test update
     *
     * @covers  \local_mentor_specialization\mentor_session::update
     */
    public function test_update_ok() {
        global $CFG;

        $this->resetAfterTest(true);
        $this->init_config();
        $this->init_database();
        $this->reset_singletons();
        $this->init_config();

        self::setAdminUser();

        // Create session.
        $sessionid = $this->init_session_creation();
        $session   = \local_mentor_core\session_api::get_session($sessionid);
        $session->create_self_enrolment_instance();

        $training                        = $session->get_training();
        $training->presenceestimatedtime = 120;
        $training->remoteestimatedtime   = 130;
        $session->get_training()->update($training);
        $session->presencesessionestimatedtime = 140;
        $session->onlinesessionestimatedtime   = 150;
        $session->update($session);

        $sessionformdata = $session->prepare_edit_form();

        $sharedentities = [];
        $allentities    = \local_mentor_core\entity_api::get_all_entities(true, [$session->get_entity()->id]);
        foreach ($allentities as $entity) {
            $sharedentities[$entity->id] = $entity->name;
        }

        $formparams                 = new stdClass();
        $formparams->session        = $session;
        $formparams->returnto       = $session->get_url();
        $formparams->session        = $session;
        $formparams->entity         = $session->get_entity();
        $formparams->sharedentities = $sharedentities;
        $formparams->logourl        = $CFG->wwwroot;
        $formparams->actionurl      = $session->get_sheet_url()->out();

        $sessionform = \local_mentor_core\session_api::get_session_form($session->get_sheet_url()->out(), $formparams);

        $sessionformdata->presencesessionestimatedtimeminutes = 10;
        $sessionformdata->presencesessionestimatedtimehours   = 4;
        $sessionformdata->onlinesessionestimatedtimeminutes   = 10;
        $sessionformdata->onlinesessionestimatedtimehours     = 10;

        $session->update($sessionformdata, $sessionform);

        // Check if estimate time data is update.
        self::assertEquals($sessionformdata->presencesessionestimatedtimeminutes +
                           ($sessionformdata->presencesessionestimatedtimehours * 60), $session->presencesessionestimatedtime);
        self::assertEquals($sessionformdata->onlinesessionestimatedtimeminutes +
                           ($sessionformdata->onlinesessionestimatedtimehours * 60), $session->onlinesessionestimatedtime);

        // Check if data en enrolment update.
        $data                    = new stdClass();
        $data->termsregistration = 'inscriptionlibre';
        $data->status            = \local_mentor_core\session::STATUS_OPENED_REGISTRATION;
        $session->update($data);

        self::assertEquals($data->termsregistration, $session->termsregistration);
        self::assertEquals(\local_mentor_core\session::STATUS_OPENED_REGISTRATION, $session->status);
        $selfenrolment = $session->get_enrolment_instances_by_type('self');
        self::assertIsObject($selfenrolment);
        self::assertEquals('0', $selfenrolment->status);

        $data->termsregistration = 'autre';
        $session->update($data);

        self::assertEquals($data->termsregistration, $session->termsregistration);
        $selfenrolment = $session->get_enrolment_instances_by_type('self');
        self::assertIsObject($selfenrolment);
        self::assertEquals('1', $selfenrolment->status);

        $this->resetAllData();
    }

    /**
     * Test create_self_enrolment_instance
     *
     * @covers  \local_mentor_specialization\mentor_session::check_default_enrol
     */
    public function test_check_default_enrol() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->init_database();
        $this->reset_singletons();
        $this->init_config();

        self::setAdminUser();

        // Create session.
        $sessionid = $this->init_session_creation();
        $session   = \local_mentor_core\session_api::get_session($sessionid);

        $session->check_default_enrol();

        // Self enrolment is enable.
        $selfenrolment = $session->get_enrolment_instances_by_type('manual');

        self::assertEquals($selfenrolment->status, 0);

        $session->update_status(\local_mentor_core\session::STATUS_CANCELLED);
        $session->check_default_enrol();

        // Self enrolment is disable.
        $selfenrolment = $session->get_enrolment_instances_by_type('manual');
        self::assertEquals($selfenrolment->status, 1);

        $this->resetAllData();
    }

    /**
     * Test convert_for_template
     *
     * @covers  \local_mentor_specialization\mentor_session::convert_for_template
     */
    public function test_convert_for_template() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->init_database();
        $this->reset_singletons();
        $this->init_config();

        self::setAdminUser();

        // Create session.
        $sessionid = $this->init_session_creation();
        $session   = new \local_mentor_specialization\mentor_session($sessionid);

        $sessiontemplatedata = $session->convert_for_template();

        self::assertObjectHasAttribute('sessionpermanent', $sessiontemplatedata);
        self::assertFalse($sessiontemplatedata->sessionpermanent);

        self::assertObjectHasAttribute('organizingstructure', $sessiontemplatedata);
        self::assertEquals($session->organizingstructure, $sessiontemplatedata->organizingstructure);

        self::assertObjectHasAttribute('contactproducerorganization', $sessiontemplatedata);
        self::assertEquals($session->contactproducerorganization, $sessiontemplatedata->contactproducerorganization);

        self::assertObjectHasAttribute('publiccible', $sessiontemplatedata);
        self::assertEquals($session->publiccible, $sessiontemplatedata->publiccible);

        self::assertObjectHasAttribute('accompaniment', $sessiontemplatedata);
        self::assertEquals($session->accompaniment, $sessiontemplatedata->accompaniment);

        self::assertObjectHasAttribute('location', $sessiontemplatedata);
        self::assertEquals($session->location, $sessiontemplatedata->location);

        self::assertObjectHasAttribute('onlinesessionestimatedtime', $sessiontemplatedata);
        self::assertEquals($session->onlinesessionestimatedtime, $sessiontemplatedata->onlinesessionestimatedtime);

        self::assertObjectHasAttribute('trainingname', $sessiontemplatedata);
        self::assertEquals($session->trainingname, $sessiontemplatedata->trainingname);

        self::assertObjectHasAttribute('onlinesession', $sessiontemplatedata);
        self::assertFalse($sessiontemplatedata->onlinesession);

        self::assertObjectHasAttribute('presencesession', $sessiontemplatedata);
        self::assertFalse($sessiontemplatedata->presencesession);

        self::assertObjectHasAttribute('sessionmodalities', $sessiontemplatedata);
        self::assertEquals('', $sessiontemplatedata->sessionmodalities);

        // With online and presence data.
        $data                                      = new \stdClass();
        $data->presencesessionestimatedtimehours   = 1;
        $data->presencesessionestimatedtimeminutes = 2;
        $data->onlinesessionestimatedtimeminutes   = 3;
        $data->onlinesessionestimatedtimehours     = 4;
        $data->publiccible                         = 'public cicble';
        $data->termsregistration                   = 'termsregistration';
        $data->termsregistrationdetail             = 'termsregistrationdetail';
        $data->sessionpermanent                    = 0;
        $data->sessionmodalities                   = 'presentiel';
        $data->accompaniment                       = 'accompaniment';
        $data->location                            = 'location';
        $data->organizingstructure                 = 'organizingstructure';

        $presencesessiontime = (int) $data->presencesessionestimatedtimehours * 60 +
                               $data->presencesessionestimatedtimeminutes;
        $onlinesessiontime   = (int) $data->onlinesessionestimatedtimehours * 60 + $data->onlinesessionestimatedtimeminutes;

        $session->update($data, true);

        $sessiontemplatedata = $session->convert_for_template(true);

        self::assertObjectHasAttribute('onlinesession', $sessiontemplatedata);
        self::assertEquals(local_mentor_core_minutes_to_hours($presencesessiontime), $sessiontemplatedata->presencesession);

        self::assertObjectHasAttribute('presencesession', $sessiontemplatedata);
        self::assertEquals(local_mentor_core_minutes_to_hours($onlinesessiontime), $sessiontemplatedata->onlinesession);

        $this->resetAllData();
    }

    /**
     * Test convert_for_template with session modality to "presentiel"
     *
     * @covers  \local_mentor_specialization\mentor_session::convert_for_template
     */
    public function test_convert_for_template_modality_to_presentiel() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->init_database();
        $this->reset_singletons();
        $this->init_config();

        self::setAdminUser();

        // Create session.
        $sessionid = $this->init_session_creation();
        $session   = \local_mentor_core\session_api::get_session($sessionid);

        // Set modality session to "presentiel".
        $data                    = new stdClass();
        $data->sessionmodalities = 'presentiel';
        $session->update($data);

        $sessiontemplatedata = $session->convert_for_template(true);

        self::assertObjectHasAttribute('sessionmodalities', $sessiontemplatedata);
        self::assertEquals(get_string($data->sessionmodalities, 'local_catalog'), $sessiontemplatedata->sessionmodalities);

        $this->resetAllData();
    }

    /**
     * Test convert_for_template with session modality to "online"
     *
     * @covers  \local_mentor_specialization\mentor_session::convert_for_template
     */
    public function test_convert_for_template_modality_to_online() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->init_database();
        $this->reset_singletons();
        $this->init_config();

        self::setAdminUser();

        // Create session.
        $sessionid = $this->init_session_creation();
        $session   = \local_mentor_core\session_api::get_session($sessionid);

        // Set modality session to "online".
        $data                    = new stdClass();
        $data->sessionmodalities = 'online';
        $session->update($data);

        $sessiontemplatedata = $session->convert_for_template(true);

        self::assertObjectHasAttribute('sessionmodalities', $sessiontemplatedata);
        self::assertEquals(get_string($data->sessionmodalities, 'local_catalog'), $sessiontemplatedata->sessionmodalities);

        $this->resetAllData();
    }

    /**
     * Test convert_for_template with session modality to "mixte"
     *
     * @covers  \local_mentor_specialization\mentor_session::convert_for_template
     */
    public function test_convert_for_template_modality_to_mixte() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->init_database();
        $this->reset_singletons();
        $this->init_config();

        self::setAdminUser();

        // Create session.
        $sessionid = $this->init_session_creation();
        $session   = \local_mentor_core\session_api::get_session($sessionid);

        // Set modality session to "mixte".
        $data                    = new stdClass();
        $data->sessionmodalities = 'mixte';
        $session->update($data);

        $sessiontemplatedata = $session->convert_for_template(true);

        self::assertObjectHasAttribute('sessionmodalities', $sessiontemplatedata);
        self::assertEquals(get_string($data->sessionmodalities, 'local_catalog'), $sessiontemplatedata->sessionmodalities);

        $this->resetAllData();
    }

    /**
     * Test is_participant
     *
     * @covers  \local_mentor_specialization\mentor_session::is_participant
     */
    public function test_is_participant() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->init_database();
        $this->reset_singletons();
        $this->init_config();

        self::setAdminUser();

        // Create session.
        $sessionid = $this->init_session_creation();
        $session   = \local_mentor_core\session_api::get_session($sessionid);
        $session->create_self_enrolment_instance();

        $user = self::getDataGenerator()->create_user();

        // User is not participant.
        self::assertFalse($session->is_participant($user->id));

        $user2 = self::getDataGenerator()->create_user();

        // User2 is new participant.
        self::setUser($user2);
        \enrol_self_external::enrol_user($session->get_course()->id, null, $session->get_enrolment_instances_by_type('self')->id);
        self::setAdminUser();

        // User is not participant.
        self::assertFalse($session->is_participant($user->id));

        // User2 is participant.
        self::assertTrue($session->is_participant($user2->id));

        // Create participant cache data.
        $session->get_participants();

        // User is not participant.
        self::assertFalse($session->is_participant($user->id));

        // User2 is participant.
        self::assertTrue($session->is_participant($user2->id));

        // User is new participant.
        self::setUser($user);
        \enrol_self_external::enrol_user($session->get_course()->id, null, $session->get_enrolment_instances_by_type('self')->id);
        self::setAdminUser();

        // User is participant.
        self::assertFalse($session->is_participant($user->id));

        $this->resetAllData();
    }

    /**
     * Test is available to user ok
     * is registrer
     *
     * @covers  \local_mentor_specialization\mentor_session::is_available_to_user
     */
    public function test_is_available_to_user_ok_can_registrer() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->init_database();
        $this->reset_singletons();
        $this->init_config();

        self::setAdminUser();

        // Create session.
        $sessionid       = $this->init_session_creation();
        $session         = \local_mentor_core\session_api::get_session($sessionid);
        $session->opento = \local_mentor_core\session::OPEN_TO_ALL;
        $session->update($session);

        // User is admin.
        self::assertTrue($session->is_available_to_user());

        $this->resetAllData();
    }

    /**
     * Test is available to user ok
     * link with entity region
     *
     * @covers  \local_mentor_specialization\mentor_session::is_available_to_user
     */
    public function test_is_available_to_user_ok_region() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->init_database();
        $this->reset_singletons();
        $this->init_config();

        self::setAdminUser();

        $db = \local_mentor_specialization\database_interface::get_instance();

        // Create session.
        $sessionid       = $this->init_session_creation();
        $session         = \local_mentor_core\session_api::get_session($sessionid);
        $session->opento = \local_mentor_core\session::OPEN_TO_CURRENT_ENTITY;
        $session->update($session);

        // Get entity session.
        $entity = $session->get_entity()->get_main_entity();

        // Add region to session entity.
        $entity->regions = 5;// Corse.
        $entity->update($entity);
        \local_mentor_core\entity_api::get_entity($entity->id, true);

        // Create new entity.
        $entity2 = \local_mentor_core\entity_api::create_entity(array('name' => 'Entity2', 'shortname' => 'Entity2'));

        // Setting user data.
        $lastname  = 'lastname';
        $firstname = 'firstname';
        $email     = 'user@gouv.fr';
        $auth      = 'manual';

        // Create user.
        \local_mentor_core\profile_api::create_and_add_user($lastname, $firstname, $email, $entity2, [], null, $auth);
        $user = $db->get_user_by_email('user@gouv.fr');

        self::setUser($user->id);

        self::assertFalse($session->is_available_to_user());

        $db->set_profile_field_value($user->id, 'region', 'Corse');

        // Link with session entity region.
        self::assertTrue($session->is_available_to_user());

        $this->resetAllData();
    }

    /**
     * Test is available to user ok
     * link with sedondary entity
     *
     * @covers  \local_mentor_specialization\mentor_session::is_available_to_user
     */
    public function test_is_available_to_user_ok_secondary_entities() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->init_database();
        $this->reset_singletons();
        $this->init_config();

        self::setAdminUser();

        $db = \local_mentor_specialization\database_interface::get_instance();

        // Create session.
        $sessionid       = $this->init_session_creation();
        $session         = \local_mentor_core\session_api::get_session($sessionid);
        $session->opento = \local_mentor_core\session::OPEN_TO_CURRENT_ENTITY;
        $session->update($session);

        // Get session entity.
        $entity = $session->get_entity()->get_main_entity();

        // Create new entity.
        $entity2 = \local_mentor_core\entity_api::create_entity(array('name' => 'Entity2', 'shortname' => 'Entity2'));

        // Setting user data.
        $lastname  = 'lastname';
        $firstname = 'firstname';
        $email     = 'user@gouv.fr';
        $auth      = 'manual';

        // Create user.
        \local_mentor_core\profile_api::create_and_add_user($lastname, $firstname, $email, $entity2, [], null, $auth);
        $user = $db->get_user_by_email('user@gouv.fr');

        self::setUser($user->id);

        self::assertFalse($session->is_available_to_user());

        $db->set_profile_field_value($user->id, 'secondaryentities', $entity->name);

        // Link with secondary entity.
        self::assertTrue($session->is_available_to_user());

        $this->resetAllData();
    }

    /**
     * Test is available to user not ok
     *
     * @covers  \local_mentor_specialization\mentor_session::is_available_to_user
     */
    public function test_is_available_to_user_nok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->init_database();
        $this->reset_singletons();
        $this->init_config();

        self::setAdminUser();

        // Create session.
        $sessionid       = $this->init_session_creation();
        $session         = \local_mentor_core\session_api::get_session($sessionid);
        $session->opento = \local_mentor_core\session::OPEN_TO_NOT_VISIBLE;
        $session->update($session);

        // Session does not visible.
        self::assertFalse($session->is_available_to_user());

        // Update session.
        $session->opento = \local_mentor_core\session::OPEN_TO_CURRENT_ENTITY;
        $session->update($session);

        // User id is zero.
        self::assertFalse($session->is_available_to_user(0));

        $this->resetAllData();
    }

    /**
     * Test is available to user ok
     * link with entity region
     *
     * @covers  \local_mentor_specialization\mentor_session::is_tutor
     */
    public function test_is_tutor_ok() {
        $this->resetAfterTest(true);
        $this->init_config();
        $this->init_database();
        $this->reset_singletons();
        $this->init_config();

        self::setAdminUser();

        $db = \local_mentor_specialization\database_interface::get_instance();

        // Create session.
        $sessionid       = $this->init_session_creation();
        $session         = \local_mentor_core\session_api::get_session($sessionid);

        // Setting user data.
        $lastname  = 'lastname';
        $firstname = 'firstname';
        $email     = 'user@gouv.fr';
        $auth      = 'manual';

        // Create user.
        \local_mentor_core\profile_api::create_and_add_user($lastname, $firstname, $email, $session->get_entity(), [], null, $auth);
        $user = $db->get_user_by_email('user@gouv.fr');

        self::assertFalse($session->is_tutor($user));

        self::getDataGenerator()->enrol_user($user->id, $session->get_course()->id, 'tuteur');

        self::assertTrue($session->is_tutor($user));

        $this->resetAllData();
    }
}
