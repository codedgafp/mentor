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

namespace local_mentor_specialization;

use local_mentor_core\session;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . "/formslib.php");
require_once($CFG->dirroot . '/local/mentor_core/classes/model/session.php');
require_once($CFG->dirroot . '/local/mentor_specialization/lib.php');

/**
 * session form
 *
 * @package    local_mentor_specialization * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     Nabil Hamdi <nabil.hamdi@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class session_form extends \moodleform {

    /** @var array $modalities */
    protected $modalities
        = [
            ''   => 'emptychoice',
            'p'  => 'presentiel',
            'd'  => 'online',
            'dp' => 'mixte'
        ];

    /** @var array $_termsregistrationoptions */
    protected $_termsregistrationoptions = ['inscriptionlibre' => 'Inscription libre', 'autre' => 'Autre'];

    /**
     * @var mentor_entity
     */
    public $entity;

    /**
     * @var mentor_session
     */
    public $session;

    public $logourl;

    protected $allskills;
    protected $_entitylogo;
    protected $returnto;

    /**
     * training_form constructor.
     *
     * @param stdClass $forminfos
     * @throws \moodle_exception
     */
    public function __construct($action, $forminfos) {
        $db = database_interface::get_instance();

        // Init entity object.
        $this->entity  = $forminfos->entity;
        $this->session = isset($forminfos->session) ? $forminfos->session : null;

        $this->sharedentities = $forminfos->sharedentities;

        $this->logourl  = $forminfos->logourl;
        $this->returnto = $forminfos->returnto;

        // Init skills.
        $this->allskills = $db->get_skills();

        parent::__construct($forminfos->actionurl);
    }

    /**
     * init training form
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    protected function definition() {

        $mform = $this->_form;

        $strwarning  = get_string('requiredopentoregistration', 'local_session');
        $warningicon = '<div class="text-warning" title="' . $strwarning .
                       '"><i class="icon fa fa-exclamation-circle text-warning fa-fw " title="' . $strwarning . '" aria-label="' .
                       $strwarning . '"></i></div>';

        $training = $this->session->get_training();

        $mform->addElement('header', 'trainingblockfields', get_string('trainingblockfields', 'local_mentor_specialization'));
        $mform->setExpanded('trainingblockfields', false);

        $mform->addElement('html', '<div class="block_training">');

        // Structure créatrice.
        $trainingentity = $training->get_entity();
        $structurehtml  = '<span>' . $trainingentity->get_entity_path() . '</span>';
        $structurelabel = $trainingentity->is_main_entity() ? get_string('space', 'local_mentor_core') :
            get_string('space', 'local_mentor_core') . '/' . get_string('subspace', 'local_mentor_core');
        $mform->addElement('static', 'creativestructurestatic', $structurelabel, $structurehtml);

        // Libellé de la formation.
        $mform->addElement('text', 'trainingname', get_string('trainingname', 'local_trainings'),
            array('disabled' => 'disabled', 'size' => 40));
        $mform->setType('trainingname', PARAM_RAW);

        // Nom abrégé du cours.
        $mform->addElement('text', 'trainingshortname', get_string('shortname', 'local_trainings'),
            array('disabled' => 'disabled', 'size' => 40));
        $mform->setType('trainingshortname', PARAM_NOTAGS);

        // Vignette.
        if ($thumbnail = $training->get_training_picture()) {
            $url = \moodle_url::make_pluginfile_url(
                $thumbnail->get_contextid(),
                $thumbnail->get_component(),
                $thumbnail->get_filearea(),
                $thumbnail->get_itemid(),
                $thumbnail->get_filepath(),
                $thumbnail->get_filename()
            );
            $mform->addElement('html',
                '<div class="form-group row fitem"><div class="col-md-3">' . get_string('thumbnail', 'local_trainings') .
                '</div><div class="col-md-9 form-inline felement"><img class="session-logo" src="' . $url . '" /></div></div>');
        }

        // Collection.
        $collectionsnames = local_mentor_specialization_get_collections();

        if (isset($this->session) && $collections = $this->session->get_training()->collection) {
            $selectedcollections = explode(',', $collections);
            $collectionshtml     = '<div class="form-autocomplete-selection">';

            foreach ($selectedcollections as $collection) {
                if (!isset($collectionsnames[$collection])) {
                    continue;
                }

                $collectionshtml .= \html_writer::tag('span', $collectionsnames[$collection],
                    array('style' => 'font-size:100%;', 'class' => 'badge badge-info mb-3 mr-1', 'role' => 'listitem'));
            }

            $collectionshtml .= '</div>';
        } else {
            $collectionshtml = \html_writer::tag('span', get_string('noselectedcollections', 'local_trainings'));
        }

        $mform->addElement('static', '', get_string('collections', 'local_trainings'), $collectionshtml);

        // Formation certifiante.
        $radioarray   = array();
        $radioarray[] = $mform->createElement('radio', 'certifying', '', get_string('yes'), 1);
        $radioarray[] = $mform->createElement('radio', 'certifying', '', get_string('no'), 0);
        $mform->addGroup($radioarray, 'certifying', get_string('certifying', 'local_trainings'), array(' '), false);
        $mform->disabledIf('certifying', '');

        // Prérequis.
        $mform->addElement('text', 'prerequisite', get_string('prerequisite', 'local_trainings'),
            array('disabled' => 'disabled', 'size' => 40));
        $mform->setType('prerequisite', PARAM_NOTAGS);

        // Catchphrase.
        $mform->addElement('text', 'catchphrase', get_string('catchphrase', 'local_mentor_specialization'),
            array('disabled' => 'disabled', 'size' => 40));
        $mform->setType('catchphrase', PARAM_NOTAGS);

        // Objectifs de la formation.
        $mform->addElement('editor', 'traininggoal', get_string('traininggoal', 'local_trainings'), array
        (
            'rows' => 8, 'cols' => 60
        ));
        $mform->disabledIf('traininggoal', '');
        $mform->setType('traininggoal', PARAM_RAW);

        // Contenu de la formation.
        $mform->addElement('editor', 'trainingcontent', get_string('trainingcontent', 'local_mentor_specialization'), array
        (
            'rows' => 8, 'cols' => 60
        ));
        $mform->disabledIf('trainingcontent', '');
        $mform->setType('trainingcontent', PARAM_RAW);

        // Durée estimée en présence.
        for ($i = 0; $i < 60; $i += 5) {
            $minutes[$i] = sprintf("%02d", $i);
        }

        $estimatedpresencetime[] = $mform->createElement('text', 'presenceestimatedtimehours', 'presenceestimatedtimehours',
            array('disabled' => 'disabled', 'size' => 2));
        $estimatedpresencetime[] = $mform->createElement('select', 'presenceestimatedtimeminutes', 'presenceestimatedtimeminutes',
            $minutes, array('disabled' => 'disabled'));
        $mform->addGroup($estimatedpresencetime, 'presenceestimatedtime', get_string('presenceestimatedtime', 'local_trainings'),
            array(' '), false);
        $mform->setType('presenceestimatedtimehours', PARAM_NOTAGS);
        $mform->setDefault('presenceestimatedtimehours', '00');

        // Durée estimée à distance.
        $estimatedremotetime[] = $mform->createElement('text', 'remoteestimatedtimehours', 'remoteestimatedtimehours',
            array('disabled' => 'disabled', 'size' => 2));
        $estimatedremotetime[] = $mform->createElement('select', 'remoteestimatedtimeminutes', 'remoteestimatedtimeminutes',
            $minutes, array('disabled' => 'disabled'));
        $mform->addGroup($estimatedremotetime, 'remoteestimatedtime', get_string('remoteestimatedtime', 'local_trainings'),
            array(' '), false);
        $mform->setType('remoteestimatedtimehours', PARAM_NOTAGS);
        $mform->setDefault('remoteestimatedtimehours', '00');

        // Modalités envisagées de la formation.
        $mform->addElement('select', 'trainingmodalities', get_string('trainingmodalities', 'local_trainings'),
            array_map(function($modality) {
                return get_string($modality, 'local_mentor_specialization');
            }, $this->modalities), array('disabled' => 'disabled', 'style' => 'width : 405px'));

        // Teaser.
        $mform->addElement('text', 'teaser', get_string('teaservideo', 'local_trainings'),
            array('disabled' => 'disabled', 'size' => 40));
        $mform->setType('teaser', PARAM_RAW);

        // Teaser images.
        if ($teaserpicture = $training->get_training_picture('teaserpicture')) {
            $url = \moodle_url::make_pluginfile_url(
                $teaserpicture->get_contextid(),
                $teaserpicture->get_component(),
                $teaserpicture->get_filearea(),
                $teaserpicture->get_itemid(),
                $teaserpicture->get_filepath(),
                $teaserpicture->get_filename()
            );
            $mform->addElement('html',
                '<div class="form-group row fitem"><div class="col-md-3">' . get_string('teaserpicture', 'local_trainings') .
                '</div><div class="col-md-9 form-inline felement"><img class="session-logo" src="' . $url . '" /></div></div>');
        }

        // Identifiant SIRH d’origine.
        $mform->addElement('text', 'idsirh', get_string('idsirh', 'local_trainings'), ['disabled' => 'disabled', 'size' => 40]);
        $mform->setType('idsirh', PARAM_NOTAGS);

        // Emploi type.
        $jobarray   = array();
        $jobarray[] = $mform->createElement('text', 'typicaljob', get_string('typicaljob', 'local_trainings'), array('size' => 40));
        $jobarray[] = $mform->createElement('static', 'typicaljobstaticrime', '',
            \html_writer::tag('a', 'RIME', array('target' => '_blank', 'href' => get_config('local_trainings', 'rime_link'))));
        $jobarray[] = $mform->createElement('static', 'typicaljobstaticsep', '', \html_writer::tag('span', '&nbsp;'));
        $jobarray[] = $mform->createElement('static', 'typicaljobstaticrmm', '',
            \html_writer::tag('a', 'RMM', array('target' => '_blank', 'href' => get_config('local_trainings', 'rmm_link'))));
        $mform->addGroup($jobarray, 'typicaljobhtml', get_string('typicaljob', 'local_trainings'), array(' '), false);
        $mform->setType('typicaljob', PARAM_NOTAGS);
        $mform->disabledIf('typicaljobhtml', '');

        // Compétences.
        if (isset($this->session) && $this->session->skills) {
            $selectedskills = explode(',', $this->session->skills);
            $skillshtml     = '<div class="form-autocomplete-selection">';

            foreach ($selectedskills as $skill) {
                if (!isset($this->allskills[$skill])) {
                    continue;
                }
                $skillshtml .= \html_writer::tag('span', $this->allskills[$skill],
                    array('style' => 'font-size:100%;', 'class' => 'badge badge-info mb-3 mr-1', 'role' => 'listitem'));
            }
            $skillshtml .= '</div>';
        } else {
            $skillshtml = \html_writer::tag('span', get_string('noselectedskills', 'local_trainings'));
        }

        $mform->addElement('static', '', get_string('skills', 'local_trainings'), $skillshtml);

        // Organisme producteur.
        $mform->addElement('text', 'producingorganization', get_string('producingorganization', 'local_trainings'),
            array('disabled' => 'disabled', 'size' => 40));
        $mform->setType('producingorganization', PARAM_NOTAGS);

        // Nom abrégé de l'organisme producteur.
        $mform->addElement('text', 'producerorganizationshortname',
            get_string('producerorganizationshortname', 'local_mentor_specialization'),
            array('disabled' => 'disabled', 'size' => 40));
        $mform->setType('producerorganizationshortname', PARAM_NOTAGS);

        // Logo de l’organisme producteur.
        if ($producerorganizationlogo = $training->get_training_picture('producerorganizationlogo')) {
            $url = \moodle_url::make_pluginfile_url(
                $producerorganizationlogo->get_contextid(),
                $producerorganizationlogo->get_component(),
                $producerorganizationlogo->get_filearea(),
                $producerorganizationlogo->get_itemid(),
                $producerorganizationlogo->get_filepath(),
                $producerorganizationlogo->get_filename()
            );
            $mform->addElement('html', '<div class="form-group row fitem"><div class="col-md-3">' .
                                       get_string('producerorganizationlogo', 'local_trainings') .
                                       '</div><div class="col-md-9 form-inline felement"><img class="session-logo" src="' . $url
                                       . '" /></div></div>');
        }

        // Contact organisme producteur.
        $mform->addElement('text', 'contactproducerorganization', get_string('contactproducerorganization', 'local_trainings'),
            array('disabled' => 'disabled', 'size' => 40));
        $mform->setType('contactproducerorganization', PARAM_NOTAGS);

        // Concepteur(s).
        $mform->addElement('hidden', 'designers', $this->session->designers);
        $mform->setType('designers', PARAM_NOTAGS);
        // Status de formation.
        $mform->addElement('hidden', 'trainingstatus', $this->session->status);
        $mform->setType('trainingstatus', PARAM_NOTAGS);
        // Date de création.
        $mform->addElement('hidden', 'timecreated', $this->session->timecreated);
        $mform->setType('timecreated', PARAM_NOTAGS);
        // Termes de la licence.
        $mform->addElement('hidden', 'licenseterms', $this->session->licenseterms);
        $mform->setType('licenseterms', PARAM_NOTAGS);

        // Structure créatrice.
        $mform->addElement('hidden', 'creativestructure', $this->entity->id);
        $mform->setType('creativestructure', PARAM_INT);

        $mform->addElement('hidden', 'categoryid', $this->entity->id);
        $mform->setType('categoryid', PARAM_INT);

        $mform->addElement('hidden', 'categorychildid', $this->entity->get_entity_formation_category());
        $mform->setType('categorychildid', PARAM_INT);

        $mform->addElement('html', '</div>');
        // End block training.

        /**************************************************** Block Session***********************************************/

        $mform->addElement('header', 'sessionblockfields', get_string('sessionblockfields', 'local_mentor_specialization'));

        // Begin block session.
        $mform->addElement('html', '<div class="block_session">');

        $structurelabel = $this->entity->is_main_entity() ? get_string('space', 'local_mentor_core') :
            get_string('space', 'local_mentor_core') . '/' . get_string('subspace', 'local_mentor_core');

        $mform->addElement('static', '', $structurelabel, $this->entity->get_entity_path());

        // Statut de la session.
        $mform->addElement('select', 'status', get_string('status', 'local_mentor_specialization'), array_map(function($status) {
            return get_string($status, 'local_mentor_specialization');
        }, $this->session->get_available_status()), array(
            'style'                     => 'width : 405px',
            'data-sessionstatusconfirm' => get_string('statusconfirmmessage', 'local_mentor_specialization')
        ));
        $mform->addRule('status', get_string('required'), 'required');

        // Info popup.
        $mform->addElement('button', 'infolifecycle', get_string('infolifecycle', 'local_mentor_specialization'),
            ['id' => 'infolifecycle']);

        $confirmpopup = '<div id="session_status_confirm" style="display:none">
            <p>' . get_string('statusconfirmmessage', 'local_mentor_specialization') . '</p>
        </div>';
        $mform->addElement('html', $confirmpopup);

        // Libellé de la session.
        $mform->addElement('text', 'fullname',
            get_string('fullname', 'local_mentor_specialization') . get_string('optional', 'local_mentor_specialization'),
            array('size' => 40, 'class' => 'optional'));
        $mform->setType('fullname', PARAM_RAW);
        $mform->addHelpButton('fullname', 'fullname', 'local_session');
        if (!has_capability('local/mentor_specialization:changesessionfullname', $this->session->get_context())) {
            $mform->disabledIf('fullname', '');
        }

        // Nom abrégé du cours.
        $mform->addElement('text', 'shortname', get_string('shortname', 'local_mentor_specialization'), array('size' => 40));
        $mform->setType('shortname', PARAM_NOTAGS);
        $mform->addHelpButton('shortname', 'shortname', 'local_session');
        if (!has_capability('local/mentor_specialization:changesessionshortname', $this->session->get_context())) {
            $mform->disabledIf('shortname', '');
        } else {
            $mform->addRule('shortname', get_string('errorsessionallstatus', 'local_mentor_specialization'), 'required');
        }

        // Get main entity.
        $mainentity = $this->entity->get_main_entity();

        // Set attributes to opento options for external.
        $opentoexternalattributes = [];
        if (!has_capability('local/mentor_core:changesessionopentoexternal', $this->session->get_context())) {
            $opentoexternalattributes = ['disabled'];
        }

        // Ouverte à.
        $opentoarray   = array();
        $opentoarray[] = $mform->createElement('radio', 'opento', '',
            get_string('notvisibleincatalog', 'local_mentor_core'), 'not_visible');
        $opentoarray[] = $mform->createElement('radio', 'opento', '',
            get_string('all_user_current_entity', 'local_mentor_core', $mainentity->shortname), 'current_entity');
        $opentoarray[] = $mform->createElement('radio', 'opento', '',
            get_string('all_user_all_entity', 'local_mentor_core'), 'all', $opentoexternalattributes);
        $opentoarray[] = $mform->createElement('radio', 'opento', '',
            get_string('all_user_current_entity_others', 'local_mentor_core', $mainentity->shortname), 'other_entities',
            $opentoexternalattributes);
        $mform->addGroup($opentoarray, 'opentogroup', get_string('opento', 'local_mentor_core'), ['<br>', ''], false);

        if (!has_capability('local/mentor_specialization:changesessionopento', $this->session->get_context())) {
            $mform->disabledIf('opentogroup', '');
        } else {
            $mform->setDefault('opento', 'notvisibleincatalog');
            $mform->addRule('opentogroup', get_string('required'), 'required');
        }

        if ($this->session->opento != 'other_entities') {
            $display = 'style="display: none;"';
        } else {
            $display = '';
        }
        $mform->addElement('html', '<div id="other_session_content" class="form-group row fitem" ' . $display .
                                   '><div class="col-md-3"></div><div class="col-md-9">');
        $mform->addElement('autocomplete', 'opentolist', '', $this->sharedentities,
            ['multiple' => true]);
        $mform->addElement('html', '</div></div>');

        // Numéro de session : req-nomodif INT.
        $mform->addElement('static', 'sessionnumber', get_string('sessionnumber', 'local_mentor_specialization'),
            $this->session->sessionnumber);

        // Public cible : req == rfc rlf == TEXT.
        $mform->addElement('text', 'publiccible', get_string('publiccible', 'local_mentor_specialization'), array('size' => 40));
        $mform->setType('publiccible', PARAM_NOTAGS);
        if (!has_capability('local/mentor_specialization:changesessionpubliccible', $this->session->get_context())) {
            $mform->disabledIf('publiccible', '');
        } else {
            $mform->setDefault('publiccible', get_string('allpublic', 'local_mentor_specialization'));
            $mform->addRule('publiccible', get_string('errorsessionallstatus', 'local_mentor_specialization'), 'required');
        }

        // Modalités de l’inscription.
        $mform->addElement('select', 'termsregistration', get_string('termsregistration', 'local_mentor_specialization'),
            $this->_termsregistrationoptions, array('style' => 'width : 405px'));
        $mform->addHelpButton('termsregistration', 'termsregistration', 'local_mentor_specialization');
        if (!has_capability('local/mentor_specialization:changesessiontermsregistration', $this->session->get_context())) {
            $mform->disabledIf('termsregistration', '');
        }

        $display = ($this->session->termsregistration === 'autre') ? 'block' : 'none';

        $mform->addElement('html', '<div id="termsregistrationdetail-bloc" style="display: ' . $display . ';">');
        $mform->addElement('editor', 'termsregistrationdetail',
            get_string('termsregistrationdetail', 'local_mentor_specialization'));
        $mform->setType('termsregistrationdetail', PARAM_RAW);
        $mform->addElement('html', '</div>');

        // Durée estimée en ligne : req == rfc rlf ==  [XX]h [YY]min.
        for ($i = 0; $i < 60; $i += 5) {
            $minutes[$i] = sprintf("%02d", $i);
        }

        $onlinesessionestimatedtime[] = $mform->createElement('text', 'onlinesessionestimatedtimehours',
            'onlinesessionestimatedtimehours',
            array('size' => 2));
        $onlinesessionestimatedtime[] = $mform->createElement('static', '', '', get_string('hours', 'local_mentor_specialization'));
        $onlinesessionestimatedtime[] = $mform->createElement('select', 'onlinesessionestimatedtimeminutes',
            'onlinesessionestimatedtimeminutes',
            $minutes);
        $onlinesessionestimatedtime[] = $mform->createElement('static', '', '',
            get_string('minutes', 'local_mentor_specialization'));
        $mform->addGroup($onlinesessionestimatedtime, 'onlinesessionestimatedtime',
            get_string('onlinesessionestimatedtime', 'local_mentor_specialization') . $warningicon,
            array(' '), false);
        $mform->setType('onlinesessionestimatedtimehours', PARAM_NOTAGS);
        if (!has_capability('local/mentor_specialization:changesessiononlinetime', $this->session->get_context())) {
            $mform->disabledIf('onlinesessionestimatedtime', '');
        } else {
            $mform->setDefault('onlinesessionestimatedtimehours', '00');
        }

        // Durée estimée en présence : req == rfc rlf ==  [XX]h [YY]min.
        $presencesessionestimatedtime[] = $mform->createElement('text', 'presencesessionestimatedtimehours',
            'presencesessionestimatedtimehours',
            array('size' => 2));
        $presencesessionestimatedtime[] = $mform->createElement('static', '', '',
            get_string('hours', 'local_mentor_specialization'));
        $presencesessionestimatedtime[] = $mform->createElement('select', 'presencesessionestimatedtimeminutes',
            'presencesessionestimatedtimeminutes',
            $minutes);
        $presencesessionestimatedtime[] = $mform->createElement('static', '', '',
            get_string('minutes', 'local_mentor_specialization'));
        $mform->addGroup($presencesessionestimatedtime, 'presencesessionestimatedtime',
            get_string('presencesessionestimatedtime', 'local_mentor_specialization') . $warningicon,
            array(' '), false);
        $mform->setType('presencesessionestimatedtimehours', PARAM_NOTAGS);
        if (!has_capability('local/mentor_specialization:changesessionpresencetime', $this->session->get_context())) {
            $mform->disabledIf('presencesessionestimatedtime', '');
        } else {
            $mform->setDefault('presencesessionestimatedtimehours', '00');
        }

        // Session permanente : rfc rlf ==  [XX]h [YY]min == CHECKBOX.
        $sessionpermanentarray   = array();
        $sessionpermanentarray[] = $mform->createElement('radio', 'sessionpermanent', '', get_string('yes'), 1);
        $sessionpermanentarray[] = $mform->createElement('radio', 'sessionpermanent', '', get_string('no'), 0);
        $mform->addGroup($sessionpermanentarray, 'sessionpermanent', get_string('sessionpermanent', 'local_mentor_specialization'),
            array(' '), false);
        $mform->addHelpButton('sessionpermanent', 'sessionpermanent', 'local_mentor_specialization');

        if (!has_capability('local/mentor_specialization:changesessionpermanentsession', $this->session->get_context())) {
            $mform->disabledIf('sessionpermanent', '');
        }

        // Date de début de la session de formation : req == rfc rlf == DATE.
        if (!has_capability('local/mentor_specialization:changesessionstartdate', $this->session->get_context())
            || in_array($this->session->status, [
                session::STATUS_OPENED_REGISTRATION, session::STATUS_IN_PROGRESS,
                session::STATUS_COMPLETED, session::STATUS_ARCHIVED
            ])) {
            $mform->addElement('date_selector', 'sessionstartdate_disabled',
                get_string('sessionstartdate', 'local_mentor_core') . $warningicon,
                [], array('disabled'));
            $mform->disabledIf('sessionstartdate_disabled', '');
            $mform->setDefault('sessionstartdate_disabled', $this->session->sessionstartdate);

            $mform->addElement('hidden', 'sessionstartdate', $this->session->sessionstartdate);
            $mform->setType('sessionstartdate', PARAM_INT);
        } else if (in_array($this->session->status, [session::STATUS_IN_PREPARATION])) {
            $mform->addElement('date_selector', 'sessionstartdate',
                get_string('sessionstartdate', 'local_mentor_core') . $warningicon
                , array('optional' => true));
        } else {
            $mform->addElement('date_selector', 'sessionstartdate',
                get_string('sessionstartdate', 'local_mentor_core') . $warningicon);
        }

        // Date de fin de la session de formation : rfc rlf  == DATE.
        if (!has_capability('local/mentor_specialization:changesessionenddate', $this->session->get_context())
            || in_array($this->session->status, ['archived'])) {
            $mform->addElement('date_selector', 'sessionenddate_disabled',
                get_string('sessionenddate', 'local_mentor_core') . $warningicon,
                [], array('disabled'));
            $mform->disabledIf('sessionenddate_disabled', '');
            $mform->setDefault('sessionenddate_disabled', $this->session->sessionenddate);

            $mform->addElement('hidden', 'sessionenddate', $this->session->sessionenddate);
            $mform->setType('sessionenddate', PARAM_INT);
        } else {
            $mform->addElement('date_selector', 'sessionenddate',
                get_string('sessionenddate', 'local_mentor_core') . $warningicon,
                array('optional' => true));
        }

        // Modalités de la session == SELECTLIST.
        $mform->addElement('select', 'sessionmodalities', get_string('sessionmodalities', 'local_mentor_specialization'),
            [
                'presentiel' => get_string('presentiel', 'local_mentor_specialization'),
                'online'     => get_string('online', 'local_mentor_specialization'),
                'mixte'      => get_string('mixte', 'local_mentor_specialization')
            ], array('style' => 'width : 405px'));

        if (!has_capability('local/mentor_specialization:changesessionsessionmodalities', $this->session->get_context())) {
            $mform->disabledIf('sessionmodalities', '');
        } else {
            if ($this->session->status != session::STATUS_IN_PREPARATION) {
                $mform->addRule('sessionmodalities', get_string('errorenpreparation', 'local_mentor_specialization'), 'required');
            }
        }

        // Accompagnement : req == rfc rlf == TEXT.
        $mform->addElement('text', 'accompaniment', get_string('accompaniment', 'local_mentor_specialization') . $warningicon,
            array('size' => 40));
        $mform->setType('accompaniment', PARAM_NOTAGS);
        if (!has_capability('local/mentor_specialization:changesessionaccompaniment', $this->session->get_context())) {
            $mform->disabledIf('accompaniment', '');
        }

        // Nombre maximum de participants : rfc rlf == TEXT.
        $mform->addElement('text', 'maxparticipants',
            get_string('maxparticipants', 'local_mentor_core') . get_string('optional', 'local_mentor_specialization'),
            array('size' => 40, 'class' => 'optional'));
        $mform->setType('maxparticipants', PARAM_RAW_TRIMMED);
        $mform->setDefault('maxparticipants', '');
        if (!has_capability('local/mentor_specialization:changesessionmaxparticipants', $this->session->get_context())) {
            $mform->disabledIf('maxparticipants', '');
        }

        // Places disponibles : req  (si "Nombre maximum de participants" renseigné) == nomodif.
        $mform->addElement('static', 'placesavailable', get_string('placesavailable', 'local_mentor_specialization'),
            $this->session->placesavailable);

        // Nombre de participants inscrits: req-nomodif.
        $mform->addElement('static', 'numberparticipants', get_string('numberparticipants', 'local_mentor_specialization'),
            $this->session->get_participants_number());

        // Lieu(x) de formation :rfc rlf == TEXT.
        $mform->addElement('text', 'location',
            get_string('location', 'local_mentor_specialization') . get_string('optional', 'local_mentor_specialization'),
            array('size' => 40, 'class' => 'optional'));
        $mform->setType('location', PARAM_NOTAGS);
        if (!has_capability('local/mentor_specialization:changesessionlocation', $this->session->get_context())) {
            $mform->disabledIf('location', '');
        }

        // Structure organisatrice :rfc rlf == TEXT.
        $mform->addElement('text', 'organizingstructure', get_string('organizingstructure', 'local_mentor_specialization') .
                                                          get_string('optional', 'local_mentor_specialization'),
            array('size' => 40, 'class' => 'optional'));
        $mform->setType('organizingstructure', PARAM_NOTAGS);
        if (!has_capability('local/mentor_specialization:changesessionorganizingstructure', $this->session->get_context())) {
            $mform->disabledIf('organizingstructure', '');
        }

        $mform->addElement('html', '</div">');
        // End block session.

        /*************************** End Block Session ******************************/

        $mform->addElement('hidden', 'returnto', $this->returnto);
        $mform->setType('returnto', PARAM_LOCALURL);

        $mform->addElement('hidden', 'oldshortname', $this->session->courseshortname);
        $mform->setType('oldshortname', PARAM_RAW);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons();
    }

    /**
     * Form validation
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK (true allowed for backwards compatibility too).
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function validation($data, $files) {

        $dbinterface = database_interface::get_instance();

        $errors = parent::validation($data, $files);

        // Check if at least one entity has been selected.
        if (isset($data['opento']) && ($data['opento'] == 'other_entities') && empty($data['opentolist'])) {
            $errors['opentolist'] = get_string('errorsessionallstatus', 'local_mentor_specialization');
        }

        // Check if enddate is valid.
        if (isset($data['sessionenddate']) && $data['sessionenddate'] !== 0) {
            if ($data['sessionstartdate'] > $data['sessionenddate']) {
                $errors['sessionenddate'] = get_string('errorenddate', 'local_mentor_core');
            }
        }

        // Check accompaniment field.
        if (isset($data['accompaniment'])) {
            if (mb_strlen($data['accompaniment']) > 255) {
                $errors['accompaniment'] = get_string('fieldtoolong', 'local_mentor_core', 255);
            } else {
                // Check accompaniment if status different from "inpreparation".
                if (isset($data['status']) && $data['status'] != session::STATUS_IN_PREPARATION) {
                    // Check field length.
                    if ($data['accompaniment'] == '') {
                        $errors['accompaniment'] = get_string('errorenpreparation', 'local_mentor_specialization');
                    }
                }
            }
        }

        // Check if the fullname is not too long.
        if (isset($data['termsregistration']) && $data['termsregistration'] == 'autre') {
            if (!isset($data['termsregistrationdetail']) || $data['termsregistrationdetail'] == '') {
                $errors['termsregistrationdetail'] = get_string('required');
            }
        }

        // Check if the fullname is not too long.
        if (isset($data['fullname']) && mb_strlen($data['fullname']) > 254) {
            $errors['fullname'] = get_string('fieldtoolong', 'local_mentor_core', 254);
        }

        // Check if the shortname already exists and if is not too long.
        if (isset($data['shortname']) && ($data['shortname'] != $data['oldshortname'])) {
            $shortname = trim($data['shortname']);

            // Check field length.
            if (mb_strlen($shortname) > 255) {
                $errors['shortname'] = get_string('fieldtoolong', 'local_mentor_core', 255);
            } else {
                if ($dbinterface->course_shortname_exists($shortname)) {
                    $errors['shortname'] = get_string('shortnameexist', 'local_trainings');
                }

                if ($dbinterface->training_exists($shortname)) {
                    $errors['shortname'] = get_string('shortnameexist', 'local_trainings');
                }

                // Check if shortname is not used.
                if ($dbinterface->course_exists(trim($shortname))) {
                    $errors['shortname'] = get_string('courseshortnameused', 'local_mentor_core');
                }
            }
        }

        // Check if the publiccible already exists and if is not too long.
        if (isset($data['publiccible'])) {
            // Check field length.
            if (mb_strlen($data['publiccible']) > 255) {
                $errors['publiccible'] = get_string('fieldtoolong', 'local_mentor_core', 255);
            }
        }

        // Check max participants value.
        if (isset($data['maxparticipants'])) {

            if ('' !== $data['maxparticipants'] && (int) $data['maxparticipants'] <= 0) {
                $errors['maxparticipants'] = get_string('errorzero', 'local_mentor_core');
            }

            if (!empty($data['maxparticipants']) && !is_numeric($data['maxparticipants'])) {
                $errors['maxparticipants'] = get_string('errorzero', 'local_mentor_core');
            }
        }

        // Check presence estimated hours are numeric.
        $presencehours = $data['presenceestimatedtimehours'];
        if (preg_match("/[^0-9]/", $presencehours)) {
            $errors['presenceestimatedtime'] = get_string('errornotnumeric', 'local_trainings');
        }

        // Check remote estimated hours are numeric.
        $remotehours = $data['remoteestimatedtimehours'];
        if (preg_match("/[^0-9]/", $remotehours)) {
            $errors['remoteestimatedtime'] = get_string('errorhoursnotnumbers', 'local_trainings');
        }

        // Check if date is set if status is not draft.
        if ($data['status'] !== session::STATUS_IN_PREPARATION &&
            ($data['sessionstartdate'] === 0 || !isset($data['sessionenddate']))) {
            $errors['sessionstartdate'] = get_string('errorenpreparation', 'local_mentor_specialization');
        }

        // Check if the session is permanent or has an end date.
        if ($data['status'] !== session::STATUS_IN_PREPARATION &&
            ($data['sessionpermanent'] == 0 && $data['sessionenddate'] == 0)) {
            $errors['sessionenddate'] = get_string('errorsessionpermanent', 'local_mentor_specialization');
        }

        // Check if the location already exists and if is not too long.
        if (isset($data['location'])) {
            if (mb_strlen($data['location']) > 255) {
                $errors['location'] = get_string('fieldtoolong', 'local_mentor_core', 255);
            }
        }

        // Check if the organizingstructure already exists and if is not too long.
        if (isset($data['organizingstructure'])) {
            if (mb_strlen($data['organizingstructure']) > 255) {
                $errors['organizingstructure'] = get_string('fieldtoolong', 'local_mentor_core', 255);
            }
        }

        return $errors;
    }
}
