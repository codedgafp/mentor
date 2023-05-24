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
 * entity renderer
 *
 * @package    local
 * @subpackage entities
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     Nabil Hamdi <nabil.hamdi@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_entities\output;

use local_entities\entity_form;
use local_mentor_core\entity_api;
use moodle_page;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/local/entities/lib.php');
require_once($CFG->dirroot . '/local/mentor_core/api/entity.php');

class entities_renderer extends \plugin_renderer_base implements \format_edadmin\output\edadmin_renderer {

    /**
     * Render the entity settings form
     *
     * @param $course
     * @param $section
     * @return string html
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     * @throws \required_capability_exception
     */
    public function display($course, $section) {
        global $CFG;

        // Set page url.
        $url = new \moodle_url('/course/view.php', ['id' => $course->id]);

        // Init message info.
        $messageinfo = '';

        $form = entity_api::get_entity_form($url->out(), $course->category);

        $entity = entity_api::get_entity($course->category);

        // Check capabilities.
        require_capability('local/entities:manageentity', $entity->get_context());

        // If form was submitted.
        if ($datas = $form->get_data()) {

            try {
                // If name field is disabled.
                if (!isset($datas->namecategory)) {
                    $datas->namecategory = $entity->name;
                }

                // If idnumber field is disabled.
                if (!isset($datas->shortname)) {
                    $datas->shortname = $entity->shortname;
                }

                // Check hidden field.
                if (isset($datas->hidden) && !is_siteadmin()) {
                    unset($datas->hidden);
                }

                // Update entity if exist.
                if (isset($datas->idcategory) && !empty($datas->idcategory)) {
                    $datas->name = $datas->namecategory;
                    $datas->id = $datas->idcategory;
                    \local_mentor_core\entity_api::update_entity($entity->id, $datas, $form);
                }

                // Message Info.
                $messageinfo = '<div class="alert alert-info">'
                               . get_string('success_editing_entity', 'local_entities')
                               . '</div>';

            } catch (\moodle_exception $e) {
                $messageinfo = '<div class="alert alert-danger">'
                               . get_string('failure_editing_entity', 'local_entities')
                               . '</div>';
                debugging('Cannot update an entity: ' . $e->getMessage(), DEBUG_DEVELOPER);
            }

        } else if ($form->is_cancelled()) {
            // Redirection to entities page when form is cancelled.
            redirect($CFG->wwwroot . '/local/entities/index.php');
        }

        $categorycontext = \context_coursecat::instance($course->category);
        $manageurl = $CFG->wwwroot . '/admin/roles/assign.php?contextid=' . $categorycontext->id;
        $manageurl .= '&returnurl=' . rawurlencode('/course/view.php?id=' . $course->id);

        // Manage roles button.
        echo '<div id="manageroles"><a href="' . $manageurl . '" target="__blank" class="btn-secondary">' .
             get_string('manageroles', 'local_user')
             . '</a></div>';

        // Edit contact page button for main entities.
        if ($entity->is_main_entity()) {
            $contactpageurl = $entity->get_contact_page_url();
            echo '<div id="contactpage"><a href="' . $contactpageurl . '" target="__blank" class="btn-secondary">' .
                 get_string('editcontactpage', 'local_entities')
                 . '</a></div>';

            $presentationpageurl = $entity->get_presentation_page_url();
            if ($presentationpageurl) {
                // Edit presentation page.
                echo '<div id="presentationpage"><a href="' . $presentationpageurl . '" target="__blank" class="btn-secondary">' .
                     get_string('editpresentationpage', 'local_entities')
                     . '</a></div>';
            } else {
                // Create presentation page.
                echo '<div id="presentationpage"><a href="' . $CFG->wwwroot .
                     '/local/entities/pages/createpresentationpage.php?entityid=' . $entity->id . '" target="__blank"
                class="btn-secondary">' .
                     get_string('createpresentationpage', 'local_entities')
                     . '</a></div>';
            }
        }

        // Get entity form data.
        $entityobj = $entity->get_form_data();

        // Add strings for JS.
        $this->page->requires->strings_for_js(array(
            'warning'
        ), 'local_mentor_core');

        $this->page->requires->strings_for_js(array(
            'canbemainentity_popup_content'
        ), 'local_mentor_specialization');

        // Get the right JS to use and call it.
        $js = entity_api::get_entity_javascript('local_entities/local_entities');
        $this->page->requires->js_call_amd($js, 'adminFormEvent', [$entity->can_be_main_entity(true)]);

        // Set data for form.
        $form->set_data($entityobj);

        // Show message Info.
        echo $messageinfo;

        // Show form for updating entity.
        return $form->render();

    }
}
