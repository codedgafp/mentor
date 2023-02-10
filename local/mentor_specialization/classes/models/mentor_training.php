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
 * Class training
 *
 * @package    local_specialization
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     nabil <nabil.hamdi@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mentor_specialization;

defined('MOODLE_INTERNAL') || die();

use local_mentor_core\training;
use local_mentor_core\training_api;

require_once($CFG->dirroot . '/local/mentor_core/classes/model/training.php');
require_once($CFG->dirroot . '/local/mentor_specialization/lib.php');
require_once($CFG->dirroot . '/local/mentor_specialization/classes/database_interface.php');

class mentor_training extends \local_mentor_core\training {

    /**
     * @var string[]
     */
    protected $_allowedarea
        = [
            'thumbnail',
            'producerorganizationlogo',
            'teaserpicture'
        ];

    /**
     * @var string
     */
    public $teaser;

    /**
     * @var string
     */
    public $teaserpicture;

    /**
     * @var string
     */
    public $prerequisite;

    /**
     * @var string
     */
    public $collection;

    /**
     * @var string
     */
    public $collectionstr;

    /**
     * @var int
     */
    public $creativestructure;

    /**
     * @var string
     */
    public $idsirh;

    /**
     * @var string
     */
    public $licenseterms;

    /**
     * @var string
     */
    public $typicaljob;

    /**
     * @var string
     */
    public $skills;

    /**
     * @var bool
     */
    public $certifying;

    /**
     * @var int
     */
    public $presenceestimatedtime;

    /**
     * @var int
     */
    public $remoteestimatedtime;

    /**
     * @var string
     */
    public $trainingmodalities;

    /**
     * @var string
     */
    public $producingorganization;

    /**
     * @var string
     */
    public $producerorganizationlogo;

    /**
     * @var string
     */
    public $designers;

    /**
     * @var string
     */
    public $contactproducerorganization;

    /**
     * @var string
     */
    public $producerorganizationshortname;

    /**
     * @var int
     */
    public $timecreated;

    /**
     * @var string
     */
    public $catchphrase;

    /**
     * @var mentor_session
     */
    public $sessions = [];

    /**
     * training constructor.
     *
     * @param int $trainingid
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function __construct($trainingid) {
        parent::__construct($trainingid);

        // Set training data.
        $this->teaser                        = $this->training->teaser;
        $this->teaserpicture                 = $this->training->teaserpicture;
        $this->prerequisite                  = $this->training->prerequisite;
        $this->collection                    = $this->training->collection;
        $this->collectionstr                 = $this->get_collections();
        $this->creativestructure             = $this->training->creativestructure;
        $this->idsirh                        = $this->training->idsirh;
        $this->licenseterms                  = $this->training->licenseterms;
        $this->typicaljob                    = $this->training->typicaljob;
        $this->skills                        = $this->training->skills;
        $this->certifying                    = $this->training->certifying;
        $this->presenceestimatedtime         = $this->training->presenceestimatedtime;
        $this->remoteestimatedtime           = $this->training->remoteestimatedtime;
        $this->trainingmodalities            = $this->training->trainingmodalities;
        $this->producingorganization         = $this->training->producingorganization;
        $this->producerorganizationlogo      = $this->training->producerorganizationlogo;
        $this->designers                     = $this->training->designers;
        $this->contactproducerorganization   = $this->training->contactproducerorganization;
        $this->producerorganizationshortname = $this->training->producerorganizationshortname;
        $this->timecreated                   = $this->training->timecreated;
        $this->catchphrase                   = $this->training->catchphrase;

    }

    /**
     * Get the list of user training actions
     *
     * @param null|int $userid
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function get_actions($userid = null) {
        global $USER;

        if (!$userid) {
            $userid = $USER->id;
        }

        $actions = parent::get_actions($userid);

        // Checks if the user can delete the training.
        if (!$this->has_sessions() &&
            !$this->has_sessions_in_recyclebin() &&
            ($this->status == self::STATUS_ARCHIVED || $this->status == self::STATUS_DRAFT) &&
            $this->is_deleter($userid)) {
            $actions['deletetraining'] = '';
        }

        return $actions;

    }

    /**
     * Get a training object for the edit form
     *
     * @return \local_mentor_core\training
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function prepare_edit_form() {
        $training = parent::prepare_edit_form();

        // Get training organisation logo.
        if ($producerorganizationlogo = $this->get_training_picture('producerorganizationlogo')) {
            $draftitemid = file_get_submitted_draft_itemid('producerorganizationlogo');
            file_prepare_draft_area($draftitemid, $producerorganizationlogo->get_contextid(), 'local_trainings',
                'producerorganizationlogo',
                $training->id);
            $training->producerorganizationlogo = $draftitemid;
        } else {
            $training->producerorganizationlogo = '';
        }

        // Get teaser picture.
        if ($teaerpicture = $this->get_training_picture('teaserpicture')) {
            $draftteaserpictureid = file_get_submitted_draft_itemid('teaserpicture');
            file_prepare_draft_area($draftteaserpictureid, $teaerpicture->get_contextid(), 'local_trainings', 'teaserpicture',
                $training->id);
            $training->teaserpicture = $draftteaserpictureid;
        } else {
            $training->teaserpicture = '';
        }

        // Get presence estimated time.
        if (isset($training->presenceestimatedtime)) {
            $training->presenceestimatedtimehours   = floor($training->presenceestimatedtime / 60);
            $training->presenceestimatedtimeminutes = $training->presenceestimatedtime % 60;
        }

        // Get remote estimated time.
        if (isset($training->remoteestimatedtime)) {
            $training->remoteestimatedtimehours   = floor($training->remoteestimatedtime / 60);
            $training->remoteestimatedtimeminutes = $training->remoteestimatedtime % 60;
        }

        // Get created time.
        if (isset($training->timecreated)) {
            $training->timecreated = date('Y-m-d H:i', $training->timecreated);
        }

        return $training;
    }

    /**
     * Override the training update
     *
     * @param \stdClass $data data to update
     * @param training_form|null $mform the $mform is only used to update training files
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function update($data, $mform = null) {

        parent::update($data, $mform);

        // If the data are not passed, use the current object data.

        // Collections.
        if (!isset($data->collection)) {
            $data->collection = explode(',', $this->collection);
        }

        if (empty($data->collection)) {
            $this->collection = '';
        } else if (is_array($data->collection)) {
            $this->collection = implode(',', $data->collection);
        } else {
            $this->collection = $data->collection;
        }

        // SIRH.
        if (!isset($data->idsirh)) {
            $data->idsirh = $this->idsirh;
        }
        $this->idsirh = trim($data->idsirh);

        // Skills.
        if (!isset($data->skills)) {
            $data->skills = explode(',', $this->skills);
        }

        if (empty($data->skills)) {
            $this->skills = '';
        } else if (is_array($data->skills)) {
            $this->skills = implode(',', $data->skills);
        } else {
            $this->skills = $data->skills;
        }

        // Estimated times.
        if (!isset($data->presenceestimatedtime)) {
            $data->presenceestimatedtime = $this->presenceestimatedtime;
        }

        if (!isset($data->remoteestimatedtime)) {
            $data->remoteestimatedtime = $this->remoteestimatedtime;
        }

        if (!empty($mform)) {
            // Get estimated time (minutes).
            $data->presenceestimatedtime = (int) $data->presenceestimatedtimehours * 60 + $data->presenceestimatedtimeminutes;
            $data->remoteestimatedtime   = (int) $data->remoteestimatedtimehours * 60 + $data->remoteestimatedtimeminutes;
        }

        $this->presenceestimatedtime = $data->presenceestimatedtime;
        $this->remoteestimatedtime   = $data->remoteestimatedtime;

        // Other fields.
        $this->teaser             = isset($data->teaser) ? trim($data->teaser) : $this->teaser;
        $this->prerequisite       = isset($data->prerequisite) ? trim($data->prerequisite) : $this->prerequisite;
        $this->licenseterms       = isset($data->licenseterms) ? trim($data->licenseterms) : $this->licenseterms;
        $this->typicaljob         = isset($data->typicaljob) ? trim($data->typicaljob) : $this->typicaljob;
        $this->certifying         = $data->certifying ?? $this->certifying;
        $this->trainingmodalities = $data->trainingmodalities ?? $this->trainingmodalities;
        $this->catchphrase        = $data->catchphrase ?? $this->catchphrase;

        $this->producingorganization         = isset($data->producingorganization) ? trim($data->producingorganization) :
            $this->producingorganization;
        $this->designers                     = isset($data->designers) ? trim($data->designers) :
            $this->designers;
        $this->contactproducerorganization   = isset($data->contactproducerorganization) ?
            trim($data->contactproducerorganization) :
            $this->contactproducerorganization;
        $this->producerorganizationshortname = isset($data->producerorganizationshortname) ?
            trim($data->producerorganizationshortname) :
            $this->producerorganizationshortname;
        $this->producerorganizationshortname = isset($data->producerorganizationshortname) ?
            trim($data->producerorganizationshortname) :
            $this->producerorganizationshortname;

        // Teaser picture.
        if (isset($data->deleteteaserpicture) && $data->deleteteaserpicture == 1) {
            $this->teaserpicture = '';
        } else {
            $this->teaserpicture = $data->teaserpicture ?? $this->teaserpicture;
        }

        // Producer organization logo.
        if (isset($data->deleteproducerorganizationlogo) && $data->deleteproducerorganizationlogo == 1) {
            $this->producerorganizationlogo = '';
        } else {
            $this->producerorganizationlogo = $data->producerorganizationlogo ?? $this->producerorganizationlogo;
        }

        if (!$this->dbinterface->update_training($this)) {
            throw new \Exception(get_string('trainingupdatefailed', 'local_mentor_core'));
        }

        // Create files only from the training form.
        if (!empty($mform)) {
            $this->create_files_by_training_form(['producerorganizationlogo', 'teaserpicture'], $mform, $data);
        }

    }

    /**
     * Duplicate the training
     *
     * @param string $trainingshortname shortname of the created course
     * @param int $destinationentity optional default null move the created training into a new entity
     * @return training the created training
     * @throws \dml_exception
     * @throws \file_exception
     * @throws \moodle_exception
     * @throws \restore_controller_exception
     * @throws \stored_file_creation_exception
     */
    public function duplicate($trainingshortname, $destinationentity = null) {
        $newtraining = parent::duplicate($trainingshortname, $destinationentity);

        // Copy the pictures.
        $fs             = get_file_storage();
        $newpicturedata = ['contextid' => $newtraining->contextid, 'itemid' => $newtraining->id];

        // Copy the producerorganizationlogo.
        if ($oldpicture = $this->get_training_picture('producerorganizationlogo')) {
            $fs->create_file_from_storedfile($newpicturedata, $oldpicture);
        }

        // Copy the teaserpicture.
        if ($oldpicture = $this->get_training_picture('teaserpicture')) {
            $fs->create_file_from_storedfile($newpicturedata, $oldpicture);
        }

        return training_api::get_training($newtraining->id);
    }

    /**
     * Get training skills name separated by <br />
     *
     * @return string
     * @throws \dml_exception
     */
    public function get_skills_name() {

        // The training has no skills so return an empty string.
        if (empty($this->skills)) {
            return '';
        }

        $dbinterface = database_interface::get_instance();

        // Load all skills.
        $allskills = $dbinterface->get_skills();

        $explodedskills = explode(',', $this->skills);

        $names = '';

        // For each skill id, get the skill text.
        foreach ($explodedskills as $explodedskill) {
            if (isset($allskills[$explodedskill])) {
                $names .= $allskills[$explodedskill] . '<br />';
            }
        }

        // Return the skill texts.
        return $names;
    }

    /**
     * Get collections texts separated by commas
     *
     * @return string
     * @throws \dml_exception
     */
    public function get_collections($separator = ";") {
        $allcollections = local_mentor_specialization_get_collections();

        if (0 === count($allcollections)) {
            return '';
        }

        $collectionids = explode(',', $this->collection);

        $collections = [];
        foreach ($collectionids as $collectionid) {
            if ($collectionid !== '' && isset($allcollections[$collectionid])) {
                $collections[$collectionid] = $allcollections[$collectionid];
            }
        }

        return (0 === count($collections)) ? '' : implode($separator, $collections);
    }

    /**
     * Get a lighter version of the current object for an usage on mustache
     *
     * @return \stdClass
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function convert_for_template() {
        global $CFG, $USER;

        // Check if the template has already been retrieve.
        if (empty($this->template)) {

            $templateobj                                = new \stdClass();
            $templateobj->id                            = $this->id;
            $templateobj->name                          = $this->name;
            $templateobj->idsirh                        = $this->idsirh;
            $templateobj->collection                    = $this->collection;
            $templateobj->collectionstr                 = $this->collectionstr;
            $templateobj->name                          = $this->name;
            $templateobj->typicaljob                    = $this->typicaljob;
            $templateobj->traininggoal                  = local_mentor_core_clean_html(html_entity_decode($this->traininggoal));
            $templateobj->prerequisite                  = $this->prerequisite;
            $templateobj->licenseterms                  = $this->licenseterms;
            $templateobj->content                       = local_mentor_core_clean_html(html_entity_decode($this->content));
            $templateobj->producingorganization         = $this->producingorganization;
            $templateobj->producerorganizationshortname = $this->producerorganizationshortname;
            $templateobj->contactproducerorganization   = $this->contactproducerorganization;
            $templateobj->courseurl                     = htmlspecialchars_decode($this->get_url()->out());
            $templateobj->isreviewer                    = false;
            $templateobj->catchphrase                   = $this->catchphrase;
            $templateobj->isreviewer                    = false;
            $templateobj->trainingsheeturl              = $CFG->wwwroot . '/local/catalog/pages/training.php?trainingid=' .
                                                          $this->id;

            // Check if the user can review the training.
            if (!has_capability('local/trainings:update', $this->get_context(), $USER)) {
                $templateobj->isreviewer = true;
            }

            // Set content without tags.
            $templateobj->trimmedcontent = strip_tags($this->content);

            $templateobj->iscertifying = ($this->certifying != '0');

            // Entity attached to the training.
            $trainingentity = $this->get_entity()->get_main_entity();

            // Set entity data.
            $templateobj->entityid       = $trainingentity->id;
            $templateobj->entityname     = $trainingentity->shortname;
            $templateobj->entityfullname = $trainingentity->name;

            $templateobj->licensetermsfullname = empty($this->licenseterms) ? '' : \license_manager::get_license_by_shortname
            ($this->licenseterms)->fullname;

            $filebycontextid = $this->dbinterface->get_files_by_component_order_by_filearea($this->contextid, 'local_trainings',
                $this->id);

            // Training thumbnail.
            $templateobj->thumbnail = $this->get_file_url();

            // Producing organization logo.
            $templateobj->producingorganizationlogo = $this->get_file_url('producerorganizationlogo');

            // Set teaser.
            $teaserresult                 = $templateobj->thumbnail;
            $fileurlteaserpicture         = $this->get_file_url('teaserpicture');
            $templateobj->teaserispicture = true;

            if (($this->teaserpicture !== '') && !is_null($fileurlteaserpicture)) {
                $teaserresult = $fileurlteaserpicture;
            }

            if ($this->teaser !== '') {
                $templateobj->teaserispicture = false;
                $teaserresult                 = str_replace("/watch/", "/embed/", $this->teaser);
                // PeerTube update.
                $teaserresult = str_replace("/w/", "/videos/embed/", $teaserresult);
                $teaserresult = '<div class="mentor-video embed-responsive embed-responsive-16by9">
                                                <iframe class="embed-responsive-item" aria-label="' .
                                get_string('teaserof', 'local_mentor_core', $templateobj->name) .
                                '"# sandbox="allow-same-origin allow-scripts allow-popups" src="' . $teaserresult .
                                '?title=0&warningTitle=0&peertubeLink=0" frameborder="0" allowfullscreen>' .
                                '</iframe></div>';
            }

            $templateobj->teaser                = $teaserresult;
            $templateobj->skills                = $this->get_skills_name();
            $templateobj->favouritedesignerdata = $this->get_favourite_designer_data();

            // Check if all enrolments user are enabled.
            $templateobj->hasenrollenabled = $this->has_enroll_user_enabled();

            $this->template = $templateobj;
        }

        return $this->template;
    }

    public function get_file_url($filename = 'thumbnail') {
        $filebycontextid = $this->dbinterface->get_files_by_component_order_by_filearea($this->contextid, 'local_trainings',
            $this->id);

        if (!isset($filebycontextid[$filename])) {
            return null;
        }

        return \moodle_url::make_pluginfile_url(
            $filebycontextid[$filename]->contextid,
            $filebycontextid[$filename]->component,
            $filebycontextid[$filename]->filearea,
            $filebycontextid[$filename]->itemid,
            $filebycontextid[$filename]->filepath,
            $filebycontextid[$filename]->filename
        )->out();
    }

    /**
     * Get training modality name.
     *
     * @return string
     */
    public function get_modality_name() {
        $modalities
            = [
            ''   => 'emptychoice',
            'p'  => 'presentiel',
            'd'  => 'online',
            'dp' => 'mixte'
        ];

        return $modalities[$this->trainingmodalities];
    }
}
