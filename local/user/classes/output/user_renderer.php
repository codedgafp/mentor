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
 * User admin renderer
 *
 * @package    local_user
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_user\output;

require_once(__DIR__ . '/../../../../config.php');
require_once($CFG->dirroot . '/local/user/lib.php');
require_once($CFG->dirroot . '/local/mentor_core/api/entity.php');

// Require login.
require_login();

use local_mentor_core\entity_api;
use local_mentor_core\profile_api;
use \local_user;

class user_renderer extends \plugin_renderer_base implements \format_edadmin\output\edadmin_renderer {

    protected $dbinterface;

    /**
     * First enter to render
     *
     * @param $course
     * @param $section
     * @return string
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function display($course, $section) {
        global $CFG, $USER;

        require_once($CFG->dirroot . '/local/mentor_core/api/profile.php');

        $entity = entity_api::get_entity($course->category);

        require_capability('local/user:manageusers', $entity->get_context());

        // Load language strings for JavaScript.
        $this->page->requires->strings_for_js(array(
            'lastname',
            'firstname',
            'email',
            'connectingentity',
            'region',
            'deletemultipleusers',
            'deletemultipleuserswhithusername',
            'deleteoneuser',
            'removeuser',
            'adduser',
            'validemailrequired',
            'neverconnected',
            'langfile',
            'exportlistusers'
        ), 'local_user');

        $this->page->requires->strings_for_js(
            array(
                'save',
                'cancel',
                'confirm'
            ),
            'format_edadmin'
        );

        $this->page->requires->strings_for_js(
            array(
                'add',
                'remove',
                'yes',
                'no',
                'emailonlyallowed'
            ),
            'moodle'
        );

        $this->page->requires->strings_for_js(
            array(
                'requiredfields',
                'erroreother',
                'erroremailused',
                'invalidemail',
                'importusersglobal',
                'mergeusers',
                'mergeusers_help'
            ),
            'local_mentor_core'
        );

        // Get cohort id.
        $courseformatoptions = new \format_edadmin\course_format_option($course->id);
        $cohortid            = $courseformatoptions->get_option_value('cohortlink');

        $ismanager = $entity->is_manager($USER);

        // Initialize params to JS.
        $params            = new \stdClass();
        $params->cohortid  = $cohortid;
        $params->entityid  = $course->category;
        $params->ismanager = $ismanager;
        $params->isadmin   = is_siteadmin();
        $params->entityshortname   = $entity->shortname;

        // Get the right js file.
        $js = profile_api::get_user_javascript('local_user/local_user');

        // Call the js init function.
        $this->page->requires->js_call_amd($js, 'init', array($params));

        // Get the right user table template.
        $template                 = profile_api::get_user_template('local_user/local_user');
        $params                   = profile_api::get_user_template_params();
        $params['ismanager']      = $ismanager;
        $params['importusersurl'] = $CFG->wwwroot . '/local/user/pages/importcsv.php?entityid=' . $entity->id;
        $params['mergeusersurl']  = $CFG->wwwroot . '/admin/tool/mergeusers/index_mentor.php';

        if (has_capability('local/mentor_core:suspendusers', $entity->get_context())) {
            $params['suspendusersurl'] = $CFG->wwwroot . '/local/user/pages/suspend_users.php?entityid=' . $entity->id;
        }

        // Get entity context.
        $categorycontext     = $entity->get_context();
        $manageurl           = $CFG->wwwroot . '/admin/roles/assign.php?contextid=' . $categorycontext->id;
        $manageurl           .= '&returnurl=' . rawurlencode('/course/view.php?id=' . $course->id);
        $params['manageurl'] = $manageurl;

        return $this->render_from_template($template, $params);
    }
}
