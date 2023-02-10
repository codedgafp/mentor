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

use local_mentor_core\training;
use local_mentor_core\training_api;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");
require_once($CFG->dirroot . '/local/mentor_specialization/lib.php');

/**
 * training form
 *
 * @package    local_mentor_specialization * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     Nabil Hamdi <nabil.hamdi@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class training_form extends \moodleform {

    protected $modalities
        = [
            ''   => 'emptychoice',
            'p'  => 'presentiel',
            'd'  => 'online',
            'dp' => 'mixte'
        ];

    /**
     * @var mentor_entity
     */
    public $entity;

    /**
     * @var mentor_training
     */
    public $training;

    /**
     * @var \stdClass|false $publish
     */
    public $publish;

    public $logourl;

    protected $allskills;
    protected $_entitylogo;
    protected $returnto;

    /**
     * training_form constructor.
     *
     * @param string $action
     * @param stdClass $data
     * @throws \moodle_exception
     */
    public function __construct($action, $data) {
        $db = database_interface::get_instance();

        // Init entity object.
        $this->entity   = $data->entity;
        $this->training = isset($data->training) ? $data->training : null;
        $this->logourl  = $data->logourl;
        $this->returnto = isset($data->returnto) ? $data->returnto : '';
        $this->publish  = isset($data->publish) ? $data->publish : null;

        // Init skills.
        $this->allskills = $db->get_skills();

        parent::__construct($data->actionurl);
    }

    /**
     * init training form
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    protected function definition() {
        global $CFG, $OUTPUT;
        $mform = $this->_form;

        $strwarning  = get_string('requiredelaborationcompleted', 'local_trainings');
        $warningicon = '<div class="text-warning" title="' . $strwarning .
                       '"><i class="icon fa fa-exclamation-circle text-warning fa-fw " title="' . $strwarning . '" aria-label="' .
                       $strwarning . '"></i></div>';

        $acceptedtypes = preg_split('/\s*,\s*/', trim($CFG->courseoverviewfilesext), -1, PREG_SPLIT_NO_EMPTY);

        $context = $this->training ? $this->training->get_context() : $this->entity->get_context();

        // Structure créatrice.
        $structurehtml  = '<span>' . $this->entity->get_entity_path() . '</span>';
        $structurelabel = $this->entity->is_main_entity() ? get_string('space', 'local_mentor_core') :
            get_string('space', 'local_mentor_core') . '/' . get_string('subspace', 'local_mentor_core');
        $mform->addElement('static', 'creativestructurestatic', $structurelabel, $structurehtml);

        // Date de création.
        $timecreated = empty($this->training) ? date('Y-m-d H:i') : date('Y-m-d H:i', $this->training->get_course()->timecreated);
        $mform->addElement('static', 'timecreated', get_string('createdat', 'local_trainings'), $timecreated);

        // Check if the course has been published in the library.
        if ($this->publish) {
            // Date de première publication.
            $mform->addElement('static', 'publishtimecreated', get_string('publishtimecreated', 'local_mentor_specialization'),
                date('Y-m-d H:i', $this->publish->timecreated));
            $mform->addHelpButton('publishtimecreated', 'publishtimecreated', 'local_mentor_specialization');

            if($this->publish->timecreated !== $this->publish->timemodified) {
                // Date de dernière publication.
                $mform->addElement('static', 'publishtimemodified', get_string('publishtimemodified', 'local_mentor_specialization'),
                    date('Y-m-d H:i', $this->publish->timemodified));
                $mform->addHelpButton('publishtimemodified', 'publishtimemodified', 'local_mentor_specialization');
            }
        }

        // Status.
        $mform->addElement('select', 'status', get_string('status', 'local_trainings'), array_map(function($status) {
            return get_string($status, 'local_trainings');
        }, training_api::get_status_list()), array('style' => 'width : 405px'));
        $mform->addRule('status', get_string('required'), 'required');

        if (!has_capability('local/mentor_core:changetrainingstatus', $context)) {
            $mform->disabledIf('status', '');
        }

        // Libellé de la formation.
        $mform->addElement('text', 'name', get_string('name', 'local_trainings'), array('size' => 40));
        $mform->setType('name', PARAM_RAW_TRIMMED);
        $mform->addHelpButton('name', 'name', 'local_trainings');
        if (!has_capability('local/mentor_core:changefullname', $context)) {
            $mform->disabledIf('name', '');
        } else {
            $mform->addRule('name', get_string('errorallstatus', 'local_mentor_specialization'), 'required');
        }

        // Nom abrégé du cours.
        $mform->addElement('text', 'shortname', get_string('shortname', 'local_trainings'), array('size' => 40));
        $mform->setType('shortname', PARAM_NOTAGS);
        $mform->addHelpButton('shortname', 'shortname', 'local_trainings');
        if (!has_capability('local/mentor_core:changeshortname', $context)) {
            $mform->disabledIf('shortname', '');
        } else {
            $mform->addRule('shortname', get_string('errorallstatus', 'local_mentor_specialization'), 'required');
        }

        $thumbnail    = is_null($this->training) ? false : $this->training->get_training_picture();
        $thumbnailurl = '';

        if ($thumbnail) {
            $thumbnailurl = \moodle_url::make_pluginfile_url(
                $thumbnail->get_contextid(),
                $thumbnail->get_component(),
                $thumbnail->get_filearea(),
                $thumbnail->get_itemid(),
                $thumbnail->get_filepath(),
                $thumbnail->get_filename()
            );
        }

        // Vignette.
        if (!has_capability('local/mentor_core:changethumbnail', $context)) {

            if ($thumbnailurl) {
                $thumbnailhtml = '<div class="col-md-9 form-inline felement"><img class="session-logo" src="' . $thumbnailurl .
                                 '" /></div>';

                $thumbnailoption = [
                    'label'           => get_string('thumbnail', 'local_trainings'),
                    'warning_text'    => get_string('requiredelaborationcompleted', 'local_trainings'),
                    'help_text'       => get_string('thumbnail_help', 'local_trainings'),
                    'help_icon_title' => get_string('thumbnail_help_icon', 'local_trainings'),
                    'html'            => $thumbnailhtml,
                ];

                // Custom form field.
                $mform->addElement('html', $OUTPUT->render_from_template('local_mentor_core/form_custom_field', $thumbnailoption));
            }
        } else {

            // Delete the thumbnail.
            if ($thumbnailurl) {
                $mform->addElement('static', 'currentthumbnail', get_string('currentthumbnail', 'local_mentor_core') . $warningicon,
                    '<img src="' . $thumbnailurl .
                    '" width="72px"/>');

                $mform->addElement('checkbox', 'deletethumbnail', get_string('deletethumbnail', 'local_mentor_core'));
                $mform->setDefault('deletethumbnail', 0);
            }

            $mform->addElement('filepicker', 'thumbnail',
                get_string('thumbnail', 'local_trainings') . ' ' . get_string('recommandedratio', 'local_mentor_core', '3:2')
                . $warningicon,
                null,
                array('accepted_types' => $acceptedtypes, 'subdirs' => 0, 'maxfiles' => 1, 'maxbytes' => 1024000));
            $mform->addHelpButton('thumbnail', 'thumbnail', 'local_trainings');
        }

        // Collection.
        $collectionsnames = local_mentor_specialization_get_collections();

        if (has_capability('local/mentor_specialization:changecollection', $context)) {
            $mform->addElement(
                'autocomplete',
                'collection',
                get_string('collections', 'local_trainings'),
                $collectionsnames,
                ['multiple' => true]
            );
            $mform->addRule('collection', get_string('errorallstatus', 'local_mentor_specialization'), 'required');
            $mform->addHelpButton('collection', 'collection', 'local_trainings');
        } else {
            if (isset($this->training) && $this->training->collection) {
                $selectedcollections = explode(',', $this->training->collection);
                $collectionshtml     = '<div class="form-autocomplete-selection">';
                foreach ($selectedcollections as $skill) {
                    $collectionshtml .= \html_writer::tag('span', $skill,
                        array('style' => 'font-size:100%;', 'class' => 'badge badge-info mb-3 mr-1', 'role' => 'listitem'));
                }
                $collectionshtml .= '</div>';
            } else {
                $collectionshtml = \html_writer::tag('span', get_string('noselectedcollections', 'local_trainings'));
            }

            $mform->addElement('static', '', get_string('collection', 'local_trainings'), $collectionshtml);
            $mform->addHelpButton('', 'collection', 'local_trainings');
        }

        // Formation certifiante.
        $radioarray   = array();
        $radioarray[] = $mform->createElement('radio', 'certifying', '', get_string('yes'), 1);
        $radioarray[] = $mform->createElement('radio', 'certifying', '', get_string('no'), 0);
        $mform->addGroup($radioarray, 'certifying', get_string('certifying', 'local_trainings'), array(' '), false);
        $mform->addRule('certifying', get_string('errorallstatus', 'local_mentor_specialization'), 'required');
        if (!has_capability('local/mentor_specialization:changecertifying', $context)) {
            $mform->disabledIf('certifying', '');
        }

        // Termes de la licence.
        $mform->addElement('select', 'licenseterms', get_string('licenseterms', 'local_trainings'),
            local_mentor_specialization_get_license_terms());
        $mform->addRule('licenseterms', get_string('errorallstatus', 'local_mentor_specialization'), 'required');
        $mform->setDefault('licenseterms', 'cc-sa');
        if (!has_capability('local/mentor_specialization:changelicenseterms', $context)) {
            $mform->disabledIf('licenseterms', '');
        }

        // Prérequis.
        $mform->addElement('text', 'prerequisite',
            get_string('prerequisite', 'local_trainings') . get_string('optional', 'local_mentor_specialization'),
            array('size' => 40, 'class' => 'optional'));
        $mform->setType('prerequisite', PARAM_NOTAGS);
        $mform->addHelpButton('prerequisite', 'prerequisite', 'local_trainings');
        if (!has_capability('local/mentor_specialization:changeprerequisite', $context)) {
            $mform->disabledIf('prerequisite', '');
        }

        // Catchphrase.
        $mform->addElement('text', 'catchphrase', get_string('catchphrase', 'local_mentor_specialization') .
                                                  get_string('maxcaracters', 'local_mentor_specialization', 152) . $warningicon,
            array(
                'size' => 130
            ));
        $mform->setType('catchphrase', PARAM_NOTAGS);
        if (!has_capability('local/mentor_specialization:changecatchphrase', $context)) {
            $mform->disabledIf('catchphrase', '');
        }

        // Objectifs de la formation.
        $mform->addElement('editor', 'traininggoal', get_string('traininggoal', 'local_trainings') . $warningicon, array
        (
            'rows' => 8, 'cols' => 60
        ));
        $mform->setType('traininggoal', PARAM_RAW);
        $mform->addHelpButton('traininggoal', 'traininggoal', 'local_trainings');
        if (!has_capability('local/mentor_core:changetraininggoal', $context)) {
            $mform->disabledIf('traininggoal', '');
        }

        // Contenu de la formation.
        $mform->addElement('editor', 'content', get_string('trainingcontent', 'local_mentor_specialization') . $warningicon,
            ['rows' => 8, 'cols' => 60]);
        $mform->setType('content', PARAM_RAW);
        $mform->addHelpButton('content', 'content', 'local_trainings');
        if (!has_capability('local/mentor_core:changecontent', $context)) {
            $mform->disabledIf('content', '');
        }

        // Durée estimée en présence.
        for ($i = 0; $i < 60; $i += 5) {
            $minutes[$i] = sprintf("%02d", $i);
        }

        $estimatedpresencetime[] = $mform->createElement('text', 'presenceestimatedtimehours', 'presenceestimatedtimehours',
            array('size' => 2));
        $estimatedpresencetime[] = $mform->createElement('static', '', '', get_string('hours', 'local_mentor_specialization'));
        $estimatedpresencetime[] = $mform->createElement('select', 'presenceestimatedtimeminutes', 'presenceestimatedtimeminutes',
            $minutes);
        $estimatedpresencetime[] = $mform->createElement('static', '', '', get_string('minutes', 'local_mentor_specialization'));
        $mform->addGroup($estimatedpresencetime, 'presenceestimatedtime', get_string('presenceestimatedtime', 'local_trainings'),
            array(' '), false);
        $mform->setType('presenceestimatedtimehours', PARAM_NOTAGS);
        $mform->addHelpButton('presenceestimatedtime', 'presenceestimatedtime', 'local_trainings');
        $mform->setDefault('presenceestimatedtimehours', '00');
        if (!has_capability('local/mentor_specialization:changepresenceestimatedtimehours', $context)) {
            $mform->disabledIf('presenceestimatedtimehours', '');
            $mform->disabledIf('presenceestimatedtime', '');
        }

        // Durée estimée à distance.
        $estimatedremotetime[] = $mform->createElement('text', 'remoteestimatedtimehours', 'remoteestimatedtimehours',
            array('size' => 2));
        $estimatedremotetime[] = $mform->createElement('static', '', '', get_string('hours', 'local_mentor_specialization'));
        $estimatedremotetime[] = $mform->createElement('select', 'remoteestimatedtimeminutes', 'remoteestimatedtimeminutes',
            $minutes);
        $estimatedremotetime[] = $mform->createElement('static', '', '', get_string('minutes', 'local_mentor_specialization'));
        $mform->addGroup($estimatedremotetime, 'remoteestimatedtime', get_string('remoteestimatedtime', 'local_trainings'),
            array(' '), false);
        $mform->setType('remoteestimatedtimehours', PARAM_NOTAGS);
        $mform->addHelpButton('remoteestimatedtime', 'remoteestimatedtime', 'local_trainings');
        $mform->setDefault('remoteestimatedtimehours', '00');
        if (!has_capability('local/mentor_specialization:changeremoteestimatedtimehours', $context)) {
            $mform->disabledIf('remoteestimatedtimehours', '');
            $mform->disabledIf('remoteestimatedtime', '');
        }

        // Modalités envisagées de la formation.
        $mform->addElement('select', 'trainingmodalities', get_string('trainingmodalities', 'local_trainings') . $warningicon,
            array_map(function($modality) {
                return get_string($modality, 'local_mentor_specialization');
            }, $this->modalities), array('style' => 'width : 405px'));
        if (!has_capability('local/mentor_specialization:changetrainingmodalities', $context)) {
            $mform->disabledIf('trainingmodalities', '');
        }

        // Teaser.
        $mform->addElement('text', 'teaser',
            get_string('teaservideo', 'local_trainings') . get_string('optional', 'local_mentor_specialization'),
            array('size' => 40, 'class' => 'optional'));
        $mform->setType('teaser', PARAM_RAW);
        $mform->addHelpButton('teaser', 'teaservideo', 'local_trainings');
        if (!has_capability('local/mentor_specialization:changeteaser', $context)) {
            $mform->disabledIf('teaser', '');
        }

        $teaserpicture    = is_null($this->training) ? false : $this->training->get_training_picture('teaserpicture');
        $teaserpictureurl = '';
        if ($teaserpicture) {
            $teaserpictureurl = \moodle_url::make_pluginfile_url(
                $teaserpicture->get_contextid(),
                $teaserpicture->get_component(),
                $teaserpicture->get_filearea(),
                $teaserpicture->get_itemid(),
                $teaserpicture->get_filepath(),
                $teaserpicture->get_filename()
            );
        }

        // Teaser images.
        if (!has_capability('local/mentor_specialization:changeteaserpicture', $context)) {

            if ($teaserpictureurl) {

                $teaserpicturehtml = '<div class="col-md-9 form-inline felement"><img src="' . $teaserpictureurl .
                                     '" width="72px"/></div>';

                $teaserpictureoption = [
                    'label'           => get_string('teaserpicture', 'local_trainings') .
                                         get_string('optional', 'local_mentor_specialization'),
                    'help_text'       => get_string('teaserpicture_help', 'local_trainings'),
                    'help_icon_title' => get_string('teaserpicture_help_icon', 'local_trainings'),
                    'html'            => $teaserpicturehtml,
                ];

                // Custom form field.
                $mform->addElement('html',
                    $OUTPUT->render_from_template('local_mentor_core/form_custom_field', $teaserpictureoption));
            }
        } else {

            // Delete the teaser picture.
            if ($teaserpictureurl) {
                $mform->addElement('static',
                    'currentteaserpicture',
                    '<div class="form-group optional"><div><label>' .
                    get_string('currentteaserpicture', 'local_mentor_specialization') .
                    get_string('optional', 'local_mentor_specialization') .
                    '</label></div></div>',
                    '<img src="' . $teaserpictureurl .
                    '" width="72px"/>');

                $mform->addElement('checkbox', 'deleteteaserpicture',
                    get_string('deleteteaserpicture', 'local_mentor_specialization'));
                $mform->setDefault('deleteteaserpicture', 0);
            }

            $mform->addElement('filepicker', 'teaserpicture',
                get_string('teaserpicture', 'local_trainings') . ' ' .
                get_string('recommandedratio', 'local_mentor_core', '16:9') .
                get_string('optional', 'local_mentor_specialization'),
                array('class' => 'optional'), array(
                    'accepted_types' => $acceptedtypes, 'subdirs' => 0, 'maxfiles' => 1,
                    'maxbytes'       => 1024000
                ));
            $mform->addHelpButton('teaserpicture', 'teaserpicture', 'local_trainings');
        }

        // Identifiant SIRH d’origine.
        $mform->addElement('text', 'idsirh',
            get_string('idsirh', 'local_trainings') . get_string('optional', 'local_mentor_specialization'),
            array('size' => 40, 'class' => 'optional'));
        $mform->setType('idsirh', PARAM_NOTAGS);
        if (!has_capability('local/mentor_specialization:changeidsirh', $context)) {
            $mform->disabledIf('idsirh', '');
        }

        // Métiers.
        $jobarray   = array();
        $jobarray[] = $mform->createElement('text', 'typicaljob', get_string('typicaljob', 'local_trainings'), array('size' => 40));
        $jobarray[] = $mform->createElement('static', 'typicaljobstaticrime', '',
            'Référentiel :&nbsp;<a href="' . get_config('local_trainings', 'rime_link') .
            '" target="_blank" rel="external help opener">
                                        RIME
                                    </a>&nbsp;(vous pouvez également vous aider des référentiels métiers des ministères)');
        $jobarray[] = $mform->createElement('static', 'typicaljobstaticsep', '', \html_writer::tag('span', '&nbsp;'));
        $mform->addGroup($jobarray, 'typicaljobhtml',
            get_string('typicaljob', 'local_trainings') . get_string('optional', 'local_mentor_specialization'), array(' '),
            false);
        $mform->setType('typicaljob', PARAM_NOTAGS);

        $element             = $this->_form->getElement('typicaljobhtml');
        $attributes          = $element->getAttributes();
        $attributes['class'] = 'optional';
        $element->setAttributes($attributes);

        if (!has_capability('local/mentor_specialization:changetypicaljob', $context)) {
            $mform->disabledIf('typicaljob', '');
        }

        // Compétences.
        if (has_capability('local/mentor_specialization:changeskills', $context)) {
            $mform->addElement('autocomplete', 'skills',
                get_string('skills', 'local_trainings') . get_string('optional', 'local_mentor_specialization'),
                $this->allskills,
                ['multiple' => true, 'class' => 'optional']);
        } else {
            if (isset($this->training) && $this->training->skills) {
                $selectedskills = explode(',', $this->training->skills);
                $skillshtml     = '<div class="form-autocomplete-selection">';
                foreach ($selectedskills as $skill) {
                    $skillshtml .= \html_writer::tag('span', $this->allskills[$skill],
                        array('style' => 'font-size:100%;', 'class' => 'badge badge-info mb-3 mr-1', 'role' => 'listitem'));
                }
                $skillshtml .= '</div>';
            } else {
                $skillshtml = \html_writer::tag('span', get_string('noselectedskills', 'local_trainings'));
            }

            $mform->addElement('static', '', get_string('skills', 'local_trainings'), $skillshtml);
        }

        // Organisme producteur.
        $mform->addElement('text', 'producingorganization',
            get_string('producingorganization', 'local_trainings') . get_string('optional', 'local_mentor_specialization'),
            array('size' => 40, 'class' => 'optional'));
        $mform->setType('producingorganization', PARAM_NOTAGS);
        $mform->addHelpButton('producingorganization', 'producingorganization', 'local_trainings');
        if (!has_capability('local/mentor_specialization:changeproducingorganization', $context)) {
            $mform->disabledIf('producingorganization', '');
        }

        // Nom abrégé de l'organisme producteur.
        $mform->addElement('text', 'producerorganizationshortname',
            get_string('producerorganizationshortname', 'local_mentor_specialization') .
            get_string('maxcaracters', 'local_mentor_specialization', 18) .
            get_string('optional', 'local_mentor_specialization'),
            array('size' => 40, 'class' => 'optional'));
        $mform->setType('producerorganizationshortname', PARAM_NOTAGS);
        $mform->addHelpButton('producerorganizationshortname', 'producerorganizationshortname', 'local_mentor_specialization');
        if (!has_capability('local/mentor_specialization:changeproducerorganizationshortname', $context)) {
            $mform->disabledIf('producerorganizationshortname', '');
        }

        // Logo de l’organisme producteur.
        $producerorganizationlogo = is_null($this->training) ? false :
            $this->training->get_training_picture('producerorganizationlogo');

        $producerorganizationlogourl = '';
        if ($producerorganizationlogo) {
            $producerorganizationlogourl = \moodle_url::make_pluginfile_url(
                $producerorganizationlogo->get_contextid(),
                $producerorganizationlogo->get_component(),
                $producerorganizationlogo->get_filearea(),
                $producerorganizationlogo->get_itemid(),
                $producerorganizationlogo->get_filepath(),
                $producerorganizationlogo->get_filename()
            );
        }

        if (!has_capability('local/mentor_specialization:changeproducerorganizationlogo', $context)) {
            // Logo de l’organisme producteur.
            if ($producerorganizationlogourl) {
                $mform->addElement('html', '<div class="form-group row fitem"><div class="col-md-3">' .
                                           get_string('producerorganizationlogo', 'local_trainings') .
                                           '</div><div class="col-md-9 form-inline felement"><img class="session-logo" src="' .
                                           $producerorganizationlogourl
                                           . '" /></div></div>');
            }
        } else {

            // Delete the producer organization logo.
            if ($producerorganizationlogo) {

                $mform->addElement('static', 'currentproducerorganizationlogo',
                    '<div class="form-group optional"><div><label>' .
                    get_string('currentproducerorganizationlogo', 'local_mentor_specialization') .
                    get_string('optional', 'local_mentor_specialization') . '</label></div></div>',
                    '<img src="' . $producerorganizationlogourl .
                    '" width="72px"/>');

                $mform->addElement('checkbox', 'deleteproducerorganizationlogo',
                    get_string('deleteproducerorganizationlogo', 'local_mentor_specialization'));
                $mform->setDefault('deleteproducerorganizationlogo', 0);
            }

            $squarestr = get_string('square', 'local_mentor_core');
            $mform->addElement('filepicker', 'producerorganizationlogo',
                get_string('producerorganizationlogo', 'local_trainings') . ' ' .
                get_string('recommandedratio', 'local_mentor_core', $squarestr) .
                get_string('optional', 'local_mentor_specialization'),
                array('class' => 'optional'),
                array('accepted_types' => $acceptedtypes, 'subdirs' => 0, 'maxfiles' => 1, 'maxbytes' => 1024000));
        }

        // Contact organisme producteur.
        $mform->addElement('text', 'contactproducerorganization', get_string('contactproducerorganization', 'local_trainings') .
                                                                  get_string('optional', 'local_mentor_specialization'),
            array('size' => 40, 'class' => 'optional'));
        $mform->setType('contactproducerorganization', PARAM_NOTAGS);
        $mform->addHelpButton('contactproducerorganization', 'contactproducerorganization', 'local_trainings');
        if (!has_capability('local/mentor_specialization:changecontactproducerorganization', $context)) {
            $mform->disabledIf('contactproducerorganization', '');
        }

        // Concepteur(s).
        $mform->addElement('text', 'designers',
            get_string('designers', 'local_trainings') . get_string('optional', 'local_mentor_specialization'),
            array('size' => 40, 'class' => 'optional'));
        $mform->setType('designers', PARAM_NOTAGS);
        $mform->addHelpButton('designers', 'designers', 'local_trainings');
        if (!has_capability('local/mentor_specialization:changedesigners', $context)) {
            $mform->disabledIf('designers', '');
        }

        // Structure créatrice.
        $mform->addElement('hidden', 'creativestructure', $this->entity->id);
        $mform->setType('creativestructure', PARAM_INT);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'categoryid', $this->entity->id);
        $mform->setType('categoryid', PARAM_INT);

        $mform->addElement('hidden', 'categorychildid', $this->entity->get_entity_formation_category());
        $mform->setType('categorychildid', PARAM_INT);

        $mform->addElement('hidden', 'returnto', $this->returnto);
        $mform->setType('returnto', PARAM_LOCALURL);

        if (has_capability('local/trainings:update', $context)) {
            $buttonarray   = array();
            $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('savechanges'));
            $buttonarray[] = &$mform->createElement('submit', 'submitbuttonpreview', 'Enregistrer et prévisualiser',
                ['id' => 'training-preview'], ['class' => 'test'], ['class' => 'test']);
            $buttonarray[] = &$mform->createElement('cancel');
            $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
            $mform->closeHeaderBefore('buttonar');
        }
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
     * @throws \moodle_exception
     */
    public function validation($data, $files) {
        global $USER;
        $dbinterface = database_interface::get_instance();

        $errors = parent::validation($data, $files);

        // Status that must have a thumbnail.
        $statusmandatorythumbnail = [training::STATUS_ELABORATION_COMPLETED, training::STATUS_ARCHIVED];

        // Check thumbnail.
        if (in_array($data['status'], $statusmandatorythumbnail)) {
            $fs      = get_file_storage();
            $context = \context_user::instance($USER->id);

            $thumbnail = $fs->get_area_files(
                $context->id,
                'user',
                'draft', $data['thumbnail'],
                'id DESC', false);

            if (isset($data['deletethumbnail']) && $data['deletethumbnail'] == 1 && $thumbnail) {
                $errors['deletethumbnail'] = get_string('errorthumbnail', 'local_mentor_specialization');
            } else if (!$thumbnail) {
                $errors['thumbnail'] = get_string('errorthumbnail', 'local_mentor_specialization');
            }
        }

        // Collection is always required.
        if (isset($data['collection']) && empty($data['collection'])) {
            $errors['collection'] = get_string('errorallstatus', 'local_mentor_specialization');
        }

        // Check the teaser field.
        if (isset($data['teaser']) && !empty($data['teaser'])) {
            $fullauthorizeddomains = get_config('local_mentor_specialization', 'videodomains');

            // Teaser must be a video of an authorized domain.
            if (!empty($fullauthorizeddomains)) {
                $authorizeddomains = explode("\n", $fullauthorizeddomains);

                $found = false;
                foreach ($authorizeddomains as $authorizeddomain) {

                    if (strpos($data['teaser'], trim($authorizeddomain)) === 0) {
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $errors['teaser'] = get_string('errorteaser', 'local_mentor_specialization', implode(', ', $authorizeddomains));
                }
            }
        }

        // Check required fields when status is "Elaboration Completed".
        if ($data['status'] == training::STATUS_ELABORATION_COMPLETED) {
            // Check catchphrase.
            if ($data['catchphrase'] == '') {
                $errors['catchphrase'] = get_string('errorelaborationcompleted', 'local_mentor_specialization');
            }

            // Check content.
            if ($data['content']['text'] == '') {
                $errors['content'] = get_string('errorelaborationcompleted', 'local_mentor_specialization');
            }

            // Check traininggoal.
            if ($data['traininggoal']['text'] == '') {
                $errors['traininggoal'] = get_string('errorelaborationcompleted', 'local_mentor_specialization');
            }

            // Check trainingmodalities.
            if ($data['trainingmodalities'] == '') {
                $errors['trainingmodalities'] = get_string('errorelaborationcompleted', 'local_mentor_specialization');
            }

            // Check presenceestimatedtimehours.
            if ($data['presenceestimatedtimehours'] == '') {
                $errors['presenceestimatedtime'] = get_string('errorelaborationcompleted', 'local_mentor_specialization');
            }

            // Check remoteestimatedtimehours.
            if ($data['remoteestimatedtimehours'] == '') {
                $errors['remoteestimatedtime'] = get_string('errorelaborationcompleted', 'local_mentor_specialization');
            }
        }

        // Check if the fullname is not too long.
        if (isset($data['name']) && mb_strlen($data['name']) > 254) {
            $errors['name'] = get_string('fieldtoolong', 'local_mentor_core', 254);
        }

        // Check if the catchphrase is not too long.
        if (isset($data['catchphrase']) && mb_strlen($data['catchphrase']) > 152) {
            $errors['catchphrase'] = get_string('fieldtoolong', 'local_mentor_core', 152);
        }

        // Check if the shortname already exists and if is not too long.
        if (isset($data['shortname'])) {
            $shortname = trim($data['shortname']);

            // Check shortname length.
            if (mb_strlen($shortname) > 255) {
                $errors['shortname'] = get_string('fieldtoolong', 'local_mentor_core', 255);
            } else {
                // Check if the shortname already exists.
                if (isset($this->training) && ($this->training->shortname != $shortname) &&
                    ($dbinterface->course_shortname_exists($shortname) || $dbinterface->training_exists($shortname))) {
                    $errors['shortname'] = get_string('shortnameexist', 'local_trainings');
                }

                // Check if the shortname already exists.
                if (!isset($this->training) && ($dbinterface->course_shortname_exists($shortname) ||
                                                $dbinterface->training_exists($shortname))) {
                    $errors['shortname'] = get_string('shortnameexist', 'local_trainings');
                }
            }
        }

        // Check presence estimated hours are numeric.
        $presencehours = $data['presenceestimatedtimehours'];
        if (preg_match("/[^0-9]/", $presencehours)) {
            $errors['presenceestimatedtime'] = get_string('errorhoursnotnumbers', 'local_trainings');
        }

        // Check remote estimated hours are numeric.
        $remotehours = $data['remoteestimatedtimehours'];
        if (preg_match("/[^0-9]/", $remotehours)) {
            $errors['remoteestimatedtime'] = get_string('errorhoursnotnumbers', 'local_trainings');
        }

        // Check if the idsirh already exists and if is not too long.
        if (isset($data['idsirh'])) {

            if (mb_strlen($data['idsirh']) > 45) {
                $errors['idsirh'] = get_string('fieldtoolong', 'local_mentor_core', 45);
            }
        }

        // Check if the typicaljob already exists and if is not too long.
        if (isset($data['typicaljob'])) {
            if (mb_strlen($data['typicaljob']) > 255) {
                $errors['typicaljob'] = get_string('fieldtoolong', 'local_mentor_core');
            }
        }

        // Check if the producingorganization already exists and if is not too long.
        if (isset($data['producingorganization'])) {
            if (mb_strlen($data['producingorganization']) > 255) {
                $errors['producingorganization'] = get_string('fieldtoolong', 'local_mentor_core', 255);
            }
        }

        // Check if the contactproducerorganization already exists and if is not too long.
        if (isset($data['contactproducerorganization'])) {
            if (mb_strlen($data['contactproducerorganization']) > 255) {
                $errors['contactproducerorganization'] = get_string('fieldtoolong', 'local_mentor_core', 255);
            }

            if (!empty($data['contactproducerorganization']) && !validate_email($data['contactproducerorganization'])) {
                $errors['contactproducerorganization'] = get_string('erroromail', 'local_mentor_specialization');
            }
        }

        // Check if the designers already exists and if is not too long.
        if (isset($data['designers'])) {
            if (mb_strlen($data['designers']) > 255) {
                $errors['designers'] = get_string('fieldtoolong', 'local_mentor_core', 255);
            }
        }

        // Check if the prerequisite already exists and if is not too long.
        if (isset($data['prerequisite'])) {
            if (mb_strlen($data['prerequisite']) > 255) {
                $errors['prerequisite'] = get_string('fieldtoolong', 'local_mentor_core', 255);
            }
        }

        // Check if the producerorganizationshortname already exists and if is not too long.
        if (isset($data['producerorganizationshortname'])) {
            if (mb_strlen($data['producerorganizationshortname']) > 18) {
                $errors['producerorganizationshortname'] = get_string('fieldtoolong', 'local_mentor_core', 18);
            }
        }

        return $errors;
    }
}
