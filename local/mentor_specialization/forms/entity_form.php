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

use local_mentor_core\entity_api;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");
require_once($CFG->dirroot . '/enrol/sirh/locallib.php');

/**
 * Mentor entity form
 *
 * @package    local_mentor_specialization
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     Nabil Hamdi <nabil.hamdi@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class entity_form extends \moodleform {

    /**
     * Define entity form fields
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    protected function definition() {
        global $CFG;

        $mform = $this->_form;

        $action = $this->_form->getAttribute('action');
        parse_str(parse_url($action)['query'], $params);

        // Course context instance.
        $courseid = $params['id'];
        $course = get_course($courseid);
        $coursecontext = \context_course::instance($courseid);
        $this->entity = entity_api::get_entity($course->category, false);

        // DB interface.
        $dbinterface = database_interface::get_instance();

        // Fields attributes.
        $namecategoryattributes = ['size' => '50'];
        $regionattributes = ['multiple' => 'multiple'];
        $displaylogopicker = true;

        // Check capabilites.
        if (!has_capability('local/mentor_specialization:changeentityname', $coursecontext)) {
            $namecategoryattributes['readonly'] = 'readonly';
        }

        if (!has_capability('local/mentor_specialization:changeentityregion', $coursecontext)) {
            $regionattributes['disabled'] = 'disabled';
        }

        if (!has_capability('local/mentor_specialization:changeentitylogo', $coursecontext)) {
            $displaylogopicker = false;
        }

        // Entity name.
        $mform->addElement('text', 'namecategory', get_string('renameentity', 'local_entities'), $namecategoryattributes);
        if (is_siteadmin()) {
            $mform->addRule('namecategory', get_string('required'), 'required');
        }
        $mform->setType('namecategory', PARAM_NOTAGS);
        $mform->addRule('namecategory', get_string('entitynamelimit', 'local_mentor_core', 200), 'maxlength', 200, 'client');

        // Entity shortname.
        $mform->addElement('text', 'shortname',
                get_string('shortname', 'local_entities') . get_string('maxcaracters', 'local_mentor_specialization', 18),
                $namecategoryattributes);
        if (is_siteadmin()) {
            $mform->addRule('shortname', get_string('required'), 'required');
        }
        $mform->setType('shortname', PARAM_NOTAGS);
        $mform->addRule('shortname', get_string('entityshortnamelimit', 'local_mentor_core', 18), 'maxlength', 18, 'client');

        // Entity can be main entity.
        if (has_capability('local/entities:manageentity', $this->entity->get_context())) {
            $mform->addElement('advcheckbox', 'canbemainentity', get_string('mainentity', 'local_mentor_specialization'), ' ');
            if (!is_siteadmin()) {
                $mform->disabledIf('canbemainentity', '');
            }
        }

        // Regions selector.
        $allregions = $dbinterface->get_all_regions();

        if (is_siteadmin()) {
            $regionsoptions = [];

            foreach ($allregions as $region) {
                $regionsoptions[$region->id] = $region->name;
            }

            $mform->addElement('autocomplete', 'regions', get_string('region', 'local_mentor_specialization'), $regionsoptions,
                    $regionattributes);
        } else {
            $regions = $this->entity->regions;

            if (!empty($regions) && !empty($regions[0])) {
                $regionshtml = '<div class="form-autocomplete-selection">';
                foreach ($regions as $region) {
                    $regionshtml .= \html_writer::tag('span', $allregions[$region]->name,
                            array('style' => 'font-size:100%;', 'class' => 'badge badge-info mb-3 mr-1', 'role' => 'listitem'));
                }
                $regionshtml .= '</div>';

                $mform->addElement('static', '', get_string('region', 'local_mentor_specialization'), $regionshtml);
            }
        }

        $acceptedtypes = preg_split('/\s*,\s*/', trim($CFG->courseoverviewfilesext), -1, PREG_SPLIT_NO_EMPTY);

        if (false === $displaylogopicker) {
            $mform->addElement('html', '<div style="display: none;">');
        }

        // Entity logo.
        $mform->addElement('filepicker', 'logo', 'Logo', null,
                array('accepted_types' => $acceptedtypes, 'subdirs' => 0, 'maxfiles' => 1, 'maxbytes' => 1024000));

        if (false === $displaylogopicker) {
            $mform->addElement('html', '</div>');

            $logo = $this->entity->get_logo();

            if ($logo) {
                $logourl = \moodle_url::make_pluginfile_url(
                        $logo->get_contextid(),
                        $logo->get_component(),
                        $logo->get_filearea(),
                        $logo->get_itemid(),
                        $logo->get_filepath(),
                        $logo->get_filename()
                );

                $thumbnailhtml = '<img class="session-logo" src="' . $logourl . '" />';

                $mform->addElement('static', '', get_string('thumbnail', 'local_trainings'), $thumbnailhtml);
            }
        }

        $allsirh = enrol_sirh_get_sirh_list();

        // Identifiants SIRH.
        if (is_siteadmin()) {
            $mform->addElement(
                    'autocomplete',
                    'sirhlist',
                    'Identifiant SIRH d\'origine',
                    $allsirh,
                    ['multiple' => true]
            );

            // Hide the entity.
            $mform->addElement('advcheckbox', 'hidden', get_string('entityvisibility', 'local_mentor_specialization'), get_string
            ('setnotvisible', 'local_mentor_specialization'));

        } else { // Read only for non admins.
            // The autocomplete field has no disabled option so we generate a simple html.
            if (isset($this->entity) && $this->entity->get_sirh_list()) {
                $selectedsirh = $this->entity->get_sirh_list();

                if (count($selectedsirh) > 0) {

                    $sirhhtml = '<div class="form-autocomplete-selection">';
                    foreach ($selectedsirh as $sirh) {
                        $sirhhtml .= \html_writer::tag('span', $allsirh[$sirh],
                                array('style' => 'font-size:100%;', 'class' => 'badge badge-info mb-3 mr-1', 'role' => 'listitem'));
                    }
                    $sirhhtml .= '</div>';

                    $mform->addElement('static', '', 'Identifiant SIRH d\'origine', $sirhhtml);
                }
            }
        }

        // Category id.
        $mform->addElement('hidden', 'idcategory');
        $mform->setType('idcategory', PARAM_INT);

        // Action buttons.
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
     * @throws \moodle_exception
     */
    public function validation($data, $files) {

        $errors = parent::validation($data, $files);

        if (empty(trim($data['namecategory']))) {
            $errors['namecategory'] = get_string('required');
        }

        $data['namecategory'] = trim($data['namecategory']);

        if (empty(trim($data['shortname']))) {
            $errors['shortname'] = get_string('required');
        }

        $data['shortname'] = trim($data['shortname']);

        // Check if entity name already exists.
        if (\local_mentor_core\entity_api::main_entity_exists($data['namecategory'], true) && $data['namecategory'] !==
                                                                                              $this->entity->name) {
            $errors['namecategory'] = get_string('errorentityexist', 'local_mentor_core', $data['namecategory']);
        }

        // Check if entity name is already used.
        if (!empty($data['shortname']) && \local_mentor_core\entity_api::shortname_exists($data['shortname'],
                        $this->entity->id)) {
            $errors['shortname'] = get_string('errorentityshortnameexist', 'local_mentor_core');
        }

        return $errors;
    }
}
