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
 * Class session
 *
 * @package    local_specialization
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     nabil <nabil.hamdi@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mentor_specialization;

defined('MOODLE_INTERNAL') || die();

use coding_exception;
use dml_exception;
use Exception;
use local_mentor_core\profile_api;
use local_mentor_core\session;
use local_mentor_core\session_api;
use local_mentor_core\training;
use moodle_exception;
use stdClass;
use stored_file;

require_once($CFG->dirroot . '/local/mentor_core/classes/model/session.php');
require_once($CFG->dirroot . '/local/mentor_core/lib.php');
require_once($CFG->dirroot . '/local/mentor_specialization/classes/models/mentor_profile.php');
require_once($CFG->dirroot . '/local/mentor_specialization/classes/database_interface.php');

class mentor_session extends session {

    /**
     * @var string[]
     */
    protected $_allowedarea
            = [
                    'thumbnail',
                    'producerorganizationlogo',
                    'teaserpicture'
            ];

    /** @var string */
    public $trainingname;

    /** @var string */
    public $trainingshortname;

    /** @var string */
    public $trainingcontent;

    /** @var string */
    public $teaser;

    /** @var string */
    public $teaserpicture;

    /** @var string */
    public $prerequisite;

    /** @var string */
    public $collection;

    /** @var int */
    public $creativestructure;

    /** @var string */
    public $traininggoal;

    /** @var string */
    public $idsirh;

    /** @var string */
    public $licenseterms;

    /** @var string */
    public $typicaljob;

    /** @var string */
    public $skills;

    /** @var bool */
    public $certifying;

    /** @var int */
    public $presenceestimatedtime;

    /** @var int */
    public $remoteestimatedtime;

    /** @var string */
    public $trainingmodalities;

    /** @var string */
    public $producingorganization;

    /** @var string */
    public $producerorganizationlogo;

    /** @var string */
    public $designers;

    /** @var string */
    public $contactproducerorganization;

    /** @var string */
    public $producerorganizationshortname;

    /** @var string */
    public $thumbnail;

    /** @var string */
    public $trainingstatus;

    /** @var string */
    public $catchphrase;

    /** @var int */
    public $timecreated;

    /** @var string */
    public $publiccible;

    /** @var string */
    public $termsregistration;

    /** @var string */
    public $termsregistrationdetail;

    /** @var string */
    public $onlinesessionestimatedtime;

    /** @var string */
    public $presencesessionestimatedtime;

    /** @var string */
    public $sessionpermanent;

    /** @var string */
    public $sessionmodalities;

    /** @var string */
    public $accompaniment;

    /** @var string */
    public $location;

    /** @var string */
    public $organizingstructure;

    /** @var string */
    public $sessionnumber;

    /**
     * session constructor.
     *
     * @param int|stdClass $sessionidorinstance
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function __construct($sessionidorinstance) {
        // Call the generic session constructor.
        parent::__construct($sessionidorinstance);

        $requiredfields = [
                'publiccible',
                'termsregistration',
                'termsregistrationdetail',
                'onlinesessionestimatedtime',
                'presencesessionestimatedtime',
                'sessionpermanent',
                'sessionmodalities',
                'accompaniment',
                'location',
                'organizingstructure',
                'sessionnumber',
        ];

        // Check if required fields exist.
        foreach ($requiredfields as $requiredfield) {
            if (!property_exists($this->session, $requiredfield)) {
                throw new \Exception('Missing field: ' . $requiredfield);
            }

            $this->{$requiredfield} = $this->session->{$requiredfield};
        }

        // Add the manual enrolment method if missing.
        $this->check_default_enrol();
    }

    /**
     * Get session pictures (thumbnail or producer organization logo).
     *
     * @param string $filearea default thumbnail
     * @return bool|stored_file
     * @throws coding_exception
     * @throws dml_exception
     */
    public function get_session_picture($filearea = 'thumbnail') {
        // The only readable files are logo and thumbnail.
        if (!in_array($filearea, $this->_allowedarea)) {
            return false;
        }

        $fs = get_file_storage();

        $areafiles = $fs->get_area_files($this->get_context()->id, 'local_session', $filearea, $this->id,
                "itemid, filepath, filename", false);

        if (count($areafiles) === 0) {
            return false;
        }

        return current($areafiles);
    }

    /**
     * Get a session object for the edit form
     *
     * @return mentor_session
     * @throws dml_exception
     */
    public function prepare_edit_form() {
        $session = parent::prepare_edit_form();

        // Common fields with training.
        $training = $this->get_training();

        $session->trainingname = $training->name;
        $session->trainingshortname = $training->shortname;
        $session->trainingcontent = $training->content;
        $session->teaser = $training->teaser;
        $session->teaserpicture = $training->teaserpicture;
        $session->prerequisite = $training->prerequisite;
        $session->collection = $training->collection;
        $session->creativestructure = $training->creativestructure;
        $session->traininggoal = $training->traininggoal;
        $session->idsirh = $training->idsirh;
        $session->trainingmodalities = $training->trainingmodalities;
        $session->typicaljob = $training->typicaljob;
        $session->skills = $training->skills;
        $session->certifying = $training->certifying;
        $session->catchphrase = $training->catchphrase;
        $session->presenceestimatedtime = $training->presenceestimatedtime;
        $session->remoteestimatedtime = $training->remoteestimatedtime;
        $session->producingorganization = $training->producingorganization;
        $session->producerorganizationlogo = $training->producerorganizationlogo;
        $session->contactproducerorganization = $training->contactproducerorganization;
        $session->producerorganizationshortname = $training->producerorganizationshortname;
        $session->thumbnail = $training->thumbnail;
        $session->trainingstatus = $training->status;
        $session->licenseterms = $training->licenseterms;
        $session->designers = $training->designers;
        $session->timecreated = $training->timecreated;

        // Get presence estimated time.
        if (isset($session->presenceestimatedtime)) {
            $session->presenceestimatedtimehours = floor($session->presenceestimatedtime / 60);
            $session->presenceestimatedtimeminutes = $session->presenceestimatedtime % 60;
        }

        // Get remote estimated time.
        if (isset($session->remoteestimatedtime)) {
            $session->remoteestimatedtimehours = floor($session->remoteestimatedtime / 60);
            $session->remoteestimatedtimeminutes = $session->remoteestimatedtime % 60;
        }

        // Get presence session estimated time.
        if (isset($session->presencesessionestimatedtime)) {
            $session->presencesessionestimatedtimehours = floor($session->presencesessionestimatedtime / 60);
            $session->presencesessionestimatedtimeminutes = $session->presencesessionestimatedtime % 60;
        }

        // Get online session estimated time.
        if (isset($session->onlinesessionestimatedtime)) {
            $session->onlinesessionestimatedtimehours = floor($session->onlinesessionestimatedtime / 60);
            $session->onlinesessionestimatedtimeminutes = $session->onlinesessionestimatedtime % 60;
        }

        // Get session trainingcontent.
        if (isset($session->trainingcontent)) {
            $session->trainingcontent = array('text' => $session->trainingcontent);
        }

        // Get session traininggoal.
        if (isset($session->traininggoal)) {
            $session->traininggoal = array('text' => $session->traininggoal);
        }

        return $session;
    }

    /**
     * Override the training update
     *
     * @param stdClass $data
     * @param training_form $mform
     * @return mentor_session
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function update($data, $mform = null) {
        $oldtermsregistration = $this->termsregistration;

        // Call the generic update method.
        parent::update($data, $mform);

        $optionalfields = [
                'trainingname',
                'trainingshortname',
                'teaser',
                'teaserpicture',
                'prerequisite',
                'collection',
                'creativestructure',
                'traininggoal',
                'idsirh',
                'trainingcontent',
                'publiccible',
                'accompaniment',
                'sessionmodalities',
                'termsregistration',
                'location',
                'organizingstructure',
                'typicaljob',
                'skills',
                'certifying',
                'presenceestimatedtime',
                'remoteestimatedtime',
                'trainingmodalities',
                'producingorganization',
                'producerorganizationlogo',
                'contactproducerorganization',
                'producerorganizationshortname',
                'thumbnail',
                'trainingstatus',
                'licenseterms',
                'designers',
                'timecreated',
                'catchphrase'
        ];

        // Use the current value for optional fields if they are not set.
        foreach ($optionalfields as $optionalfield) {
            if (!isset($data->{$optionalfield})) {
                $data->{$optionalfield} = $this->{$optionalfield};
            }
        }

        if (is_array($data->trainingcontent)) {
            $data->trainingcontent = $data->trainingcontent['text'];
        }

        // If the session is not updated by moodle form, set precedent values.
        if (!$mform) {
            // Training data.
            $this->trainingname = trim($data->trainingname);
            $this->trainingshortname = trim($data->trainingshortname);
            $this->trainingcontent = $data->trainingcontent;
            $this->teaser = $data->teaser;
            $this->teaserpicture = $data->teaserpicture;
            $this->prerequisite = $data->prerequisite;
            $this->collection = $data->collection;
            $this->creativestructure = $data->creativestructure;
            $this->traininggoal = $data->traininggoal;
            $this->idsirh = $data->idsirh;
            $this->typicaljob = $data->typicaljob;
            $this->skills = $data->skills;
            $this->certifying = $data->certifying;
            $this->presenceestimatedtime = $data->presenceestimatedtime;
            $this->remoteestimatedtime = $data->remoteestimatedtime;
            $this->trainingmodalities = $data->trainingmodalities;
            $this->catchphrase = $data->catchphrase;
            $this->producingorganization = $data->producingorganization;
            $this->producerorganizationlogo = $data->producerorganizationlogo;
            $this->contactproducerorganization = $data->contactproducerorganization;
            $this->producerorganizationshortname = $data->producerorganizationshortname;
            $this->thumbnail = $data->thumbnail;
            $this->trainingstatus = $data->trainingstatus;
            $this->licenseterms = $data->licenseterms;
            $this->designers = $data->designers;
            $this->timecreated = $data->timecreated;
            $this->sessionmodalities = $data->sessionmodalities;

            // Set the session number.
            if (!$this->sessionnumber) {
                $this->sessionnumber = session_api::get_next_sessionnumber_index($this->trainingid);
            }

        } else {
            // Specific data for session.
            if (!isset($data->presencesessionestimatedtimeminutes) && !isset($data->presencesessionestimatedtimehours)) {
                $presencesessiontime = $this->presencesessionestimatedtime;
            } else {
                $presencesessiontime = (int) $data->presencesessionestimatedtimehours * 60 +
                                       $data->presencesessionestimatedtimeminutes;
            }

            if (!isset($data->onlinesessionestimatedtimeminutes) && !isset($data->onlinesessionestimatedtimehours)) {
                $onlinesessiontime = $this->onlinesessionestimatedtime;
            } else {
                $onlinesessiontime = (int) $data->onlinesessionestimatedtimehours * 60 + $data->onlinesessionestimatedtimeminutes;
            }

            $this->publiccible = trim($data->publiccible);
            $this->termsregistration = $data->termsregistration;
            $this->termsregistrationdetail = $data->termsregistrationdetail;
            $this->onlinesessionestimatedtime = $onlinesessiontime;
            $this->presencesessionestimatedtime = $presencesessiontime;
            $this->sessionpermanent = $data->sessionpermanent;
            $this->sessionmodalities = $data->sessionmodalities;
            $this->accompaniment = trim($data->accompaniment);
            $this->location = trim($data->location);
            $this->organizingstructure = trim($data->organizingstructure);
        }

        // Update the session with specific attributes in database.
        if (!$this->dbinterface->update_session($this)) {
            throw new \moodle_exception('sessionupdatefailed', 'local_mentor_core');
        }

        // Update session registration terms.
        if (isset($data->termsregistration) && $oldtermsregistration != $data->termsregistration) {

            if ($data->termsregistration != 'inscriptionlibre') {
                $this->disable_self_enrolment_instance();
            } else if ($data->status == self::STATUS_OPENED_REGISTRATION || $data->status == self::STATUS_IN_PROGRESS) {
                // Create self enrolment if not exist.
                if (!$this->enable_self_enrolment_instance()) {
                    $this->create_self_enrolment_instance();
                }
            }

        }

        return $this;
    }

    /**
     * Mark the session as opened to registration
     *
     * @param string $oldstatus
     * @throws coding_exception
     * @throws dml_exception
     * @throws \moodle_exception
     */
    protected function open_to_registration($oldstatus) {
        $dbinterface = database_interface::get_instance();

        // Update status.
        $dbinterface->update_session_status($this->id, self::STATUS_OPENED_REGISTRATION);

        // Show the course.
        $this->show_course();

        if ($this->termsregistration == 'inscriptionlibre') {
            // Create self enrol instance if not exist.
            // Or Enable.
            $this->create_self_enrolment_instance();
        } else {
            // Disable self enrol instance if exist.
            $this->disable_self_enrolment_instance();
        }

        // Send a message if the session was reported.
        if ($oldstatus == self::STATUS_REPORTED) {
            // Data for message.
            $infodata = new stdClass();
            $infodata->fullname = $this->fullname ?: $this->trainingname;
            $infodata->startdate = date('d/m/Y', $this->sessionstartdate);

            // Message text.
            $messagetext = get_string('new_date_email', 'local_mentor_core', $infodata);

            // Message subject.
            $subject = get_string('newsessiondate', 'local_mentor_core') . ' ' . $infodata->fullname;

            // Message HTML.
            $messagehtml = text_to_html($messagetext, false, false, true);

            // Send a report email to participants.
            $this->send_message_to_users(
                    array_merge($this->get_participants(), $this->get_tutors(), $this->get_formateurs()),
                    $subject, $messagetext, $messagehtml
            );
        }
    }

    /**
     * Mark the session as opened (inprogress)
     *
     * @param string $oldstatus
     * @throws coding_exception
     * @throws dml_exception
     * @throws \moodle_exception
     */
    protected function open($oldstatus) {
        $dbinterface = database_interface::get_instance();

        // Update status.
        $dbinterface->update_session_status($this->id, self::STATUS_IN_PROGRESS);

        // Show the course.
        $this->show_course();

        if ($this->termsregistration == 'inscriptionlibre') {
            // Create self enrol instance if not exist.
            // Or Enable.
            $this->create_self_enrolment_instance();
        } else {
            // Disable self enrol instance if exist.
            $this->disable_self_enrolment_instance();
        }

        // Send a message if the session was open.
        if ($oldstatus == self::STATUS_OPENED_REGISTRATION) {
            // Data for message.
            $infodata = new stdClass();
            $infodata->fullname = $this->fullname ?: $this->trainingname;
            $infodata->dashboardurl = (new \moodle_url('/my'))->out(false);

            // Message text.
            $messagetext = get_string('email_open_session_content', 'local_mentor_specialization', $infodata);

            // Message subject.
            $subject = get_string('email_open_session_object', 'local_mentor_specialization', $infodata->fullname);

            // Message HTML.
            $messagehtml = text_to_html($messagetext, false, false, true);

            // Send a report email to participants.
            $this->send_message_to_users(
                    array_merge($this->get_participants(), $this->get_tutors(), $this->get_formateurs()),
                    $subject, $messagetext, $messagehtml
            );
        }
    }

    /**
     * Mark the session as archived
     *
     * @param string $oldstatus
     * @throws dml_exception
     */
    protected function archive($oldstatus) {
        parent::archive($oldstatus);

        $dbinterface = database_interface::get_instance();

        $courseid = $this->get_course()->id;

        // Move participants to participantnonediteur.
        $dbinterface->convert_course_role($courseid, 'participant', 'participantnonediteur');

        // Disable all etherpad activities.
        $dbinterface->disable_course_mods($this->get_course()->id, 'etherpadlite');

        // Rebuild the course cache.
        rebuild_course_cache($courseid, true);
    }

    /**
     * Get available status depending on the current status
     *
     * @return array
     */
    public function get_available_status() {
        $allstatus = [
                self::STATUS_IN_PREPARATION => [self::STATUS_OPENED_REGISTRATION],
                self::STATUS_OPENED_REGISTRATION => [self::STATUS_REPORTED],
                self::STATUS_IN_PROGRESS => [self::STATUS_REPORTED],
                self::STATUS_COMPLETED => [self::STATUS_ARCHIVED],
                self::STATUS_REPORTED => [self::STATUS_OPENED_REGISTRATION],
                self::STATUS_CANCELLED => []
        ];

        // Check if the session has been archived for more than a month.
        $sessionmaxdate = strtotime('+2 months', $this->sessionenddate);

        if ($this->sessionenddate && $sessionmaxdate > time()) {
            $allstatus[self::STATUS_ARCHIVED] = [self::STATUS_COMPLETED];
        } else {
            $allstatus[self::STATUS_ARCHIVED] = [];
        }

        // Merge current status and available status.
        $availablestatus = array_merge([$this->status], $allstatus[$this->status]);

        return array_combine($availablestatus, $availablestatus);
    }

    /**
     * Create a self enrolment instance for this session
     * if not exist
     *
     * @return bool true if is create, false if already exists
     * @throws coding_exception
     * @throws dml_exception
     */
    public function create_self_enrolment_instance() {
        // Get the id of the enrolment instance.
        if (!$instanceid = parent::create_self_enrolment_instance()) {
            return false;
        }

        $instance = new stdClass();
        $instance->id = $instanceid;
        $instance->customint3 = $this->maxparticipants; // Max participants.
        $instance->customint4 = get_config('enrol_self', 'sendcoursewelcomemessage');

        // Update the enrolment instance.
        $this->update_enrolment_instance($instance);

        return $instanceid;
    }

    /**
     * Create a manual enrolment if missing
     *
     * @throws coding_exception
     * @throws dml_exception
     */
    public function check_default_enrol() {
        if ($this->status != self::STATUS_CANCELLED) {
            // Always allow manual enrol.
            if (!$this->enable_manual_enrolment_instance()) {
                // Create instance if not exist.
                $this->create_manual_enrolment_instance();
            }
        } else {
            // Disable all enrolment instance.
            $this->disable_enrolment_instance();
        }
    }

    /**
     * Get a lighter version of the current object for an usage on mustache
     *
     * @return \stdClass
     * @throws coding_exception
     */
    public function convert_for_template($refresh = false) {
        global $CFG;

        if (empty($this->template) || $refresh) {
            require_once($CFG->dirroot . '/local/catalog/lib.php');

            // Initialise the template with generic data.
            $templateobj = parent::convert_for_template();

            // Add specific data.
            $templateobj->sessionpermanent = $this->sessionpermanent == 1;
            $templateobj->organizingstructure = $this->organizingstructure;
            $templateobj->contactproducerorganization = $this->contactproducerorganization;
            $templateobj->publiccible = $this->publiccible;
            $templateobj->accompaniment = $this->accompaniment;
            $templateobj->location = $this->location;
            $templateobj->onlinesessionestimatedtime = $this->onlinesessionestimatedtime;
            $templateobj->trainingname = $this->trainingname;

            // Convert online session estimated time into hours.
            if (!$this->onlinesessionestimatedtime) {
                $templateobj->onlinesession = false;
            } else {
                $templateobj->onlinesession = local_mentor_core_minutes_to_hours($this->onlinesessionestimatedtime);
            }

            // Convert presence session estimated time into hours.
            if (!$this->presencesessionestimatedtime) {
                $templateobj->presencesession = false;
            } else {
                $templateobj->presencesession = local_mentor_core_minutes_to_hours($this->presencesessionestimatedtime);
            }

            // Set modality string.
            $templateobj->sessionmodalities = empty($this->sessionmodalities) ? '' :
                    get_string($this->sessionmodalities, 'local_catalog');

            $this->template = $templateobj;
        }

        return $this->template;
    }

    /**
     * Check if user is participant
     *
     * @param stdClass $user
     * @return boolean
     * @throws dml_exception
     */
    public function is_participant($user) {

        $dbinterface = \local_mentor_specialization\database_interface::get_instance();

        // Make sure the user is an object.
        if (!is_object($user)) {
            $oldid = $user;
            $user = new stdClass();
            $user->id = $oldid;
        }

        // If the participants list has already been loaded, check if it contains the user id.
        if (!empty($this->participants)) {
            return array_key_exists($user->id, $this->participants);
        }

        // Check in database if the user is a course participant.
        return $dbinterface->is_participant($user->id, $this->contextid);
    }

    /**
     * Check the user is session course trainer
     *
     * @param integer|stdClass $user
     * @return bool
     * @throws coding_exception
     * @throws dml_exception
     */
    public function is_trainer($user) {
        $userid = is_object($user) ? $user->id : $user;
        return $this->dbinterface->user_has_role_in_context($userid, 'formateur', $this->contextid);
    }

    /**
     * Check the user is session course tutor
     *
     * @param integer|stdClass $user
     * @return bool
     * @throws coding_exception
     * @throws dml_exception
     */
    public function is_tutor($user) {
        $userid = is_object($user) ? $user->id : $user;
        return $this->dbinterface->user_has_role_in_context($userid, 'tuteur', $this->contextid);
    }

    /**
     * Get all participants with no editing rights
     *
     * @param bool $refresh
     * @return stdClass[]
     * @throws dml_exception
     */
    public function get_participants($refresh = false) {

        // Check if the participants list must be reloaded.
        if ($refresh || empty($this->participants)) {

            $dbinterface = database_interface::get_instance();

            $this->participants = $dbinterface->get_course_participants($this->get_context()->id);
        }

        return $this->participants;
    }

    /**
     * Duplicate the session
     *
     * @param string $trainingfullname fullname of the created course
     * @param string $trainingshortname shortname of the created course
     * @param int $destinationentity optional default null move the created training into a new entity
     * @return training the created training
     * @throws \file_exception
     * @throws \restore_controller_exception
     * @throws \stored_file_creation_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function duplicate_as_new_training($trainingfullname, $trainingshortname, $destinationentity) {
        $newtraining = parent::duplicate_as_new_training($trainingfullname, $trainingshortname, $destinationentity);

        $sessiontraining = $this->get_training();

        // Copy the pictures.
        $fs = get_file_storage();
        $newpicturedata = ['contextid' => $newtraining->contextid, 'itemid' => $newtraining->id];

        // Copy the producerorganizationlogo.
        if ($oldpicture = $sessiontraining->get_training_picture('producerorganizationlogo')) {
            $fs->create_file_from_storedfile($newpicturedata, $oldpicture);
        }

        // Copy the teaserpicture.
        if ($oldpicture = $sessiontraining->get_training_picture('teaserpicture')) {
            $fs->create_file_from_storedfile($newpicturedata, $oldpicture);
        }

        return $newtraining;
    }

    /**
     * Get all sirh enrolment instances
     */
    public function get_sirh_instances() {
        $dbinterface = database_interface::get_instance();
        return $dbinterface->get_sirh_instances($this->get_course()->id);
    }

    /**
     * Get the list of user session actions
     *
     * @param null|int $userid
     * @param bool $refresh - true to fresh move action - optional default false
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function get_actions($userid = null, $refresh = false) {
        global $CFG;

        require_once($CFG->dirroot . '/enrol/sirh/locallib.php');

        // Get mentor_core actions.
        $actions = parent::get_actions($userid, $refresh);

        $sirhaccessstatus = [
                self::STATUS_OPENED_REGISTRATION,
                self::STATUS_IN_PROGRESS
        ];

        // SIRH enrolment.
        if (in_array($this->status, $sirhaccessstatus) && enrol_sirh_plugin_is_enabled()) {
            $sirh = $this->get_entity()->get_main_entity()->get_sirh_list();

            // Enrolment SIRH button.
            if (count($sirh) > 0) {
                $newaction = [];
                $newaction['importSIRH'] = [
                        'url' => $CFG->wwwroot . '/enrol/sirh/pages/index.php?sessionid=' . $this->id,
                        'tooltip' => 'Inscriptions SIRH'
                ];

                $index = array_search('importUsers', array_keys($actions));

                $actions = array_merge(array_slice($actions, 0, $index + 1), $newaction, array_slice($actions, $index + 1));
            }
        }

        return $actions;
    }

    /**
     * Check if the current user can register for the session
     *
     * @param null $userid
     * @return bool
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function is_available_to_user($userid = null) {
        if (null === $userid) {
            global $USER;
            $userid = $USER->id;
        }

        // Check generic conditions.
        $canregistrer = parent::is_available_to_user($userid);
        if ($canregistrer) {
            return true;
        }

        // Re-Check if the session is visible or the user exists.
        if (
                $this->opento == self::OPEN_TO_NOT_VISIBLE ||
                is_null($userid) ||
                $userid == 0
        ) {
            return false;
        }
        $dbinterfacementor = database_interface::get_instance();

        // Get regions list.
        $regionslist = array_map(function($key) {
            return $key->name;
        }, $dbinterfacementor->get_all_regions());

        // Check if the user's regions is included in session entity regions.
        $userprofilefields = (array) profile_user_record($userid, false);

        $mainentity = $this->get_entity()->get_main_entity();

        // Check for region.
        if (isset($userprofilefields['region']) && !empty($userprofilefields['region'])) {

            $regionid = array_search($userprofilefields['region'], $regionslist);
            $regions = $mainentity->regions;

            $canregistrer = in_array($regionid, $regions);
        }

        // Check for secondary entities.
        if (!$canregistrer && $this->opento === self::OPEN_TO_CURRENT_ENTITY) {
            $profile = profile_api::get_profile($userid);
            $secondaryentities = $profile->get_secondary_entities();
            foreach ($secondaryentities as $secondaryentity) {
                if ($mainentity->id == $secondaryentity->id) {
                    return true;
                }
            }
        }
        return $canregistrer;
    }
}
