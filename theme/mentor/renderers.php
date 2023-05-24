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
 * Extends Moodle renderers
 *
 * @package    theme_mentor
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     adrien <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class theme_mentor_core_renderer extends core_renderer {

    // Define site types.
    private $types
            = [
                    'azure_dev' => 'dev',
                    'azure_formation' => 'prod',
                    'azure_hotfix' => 'preprod',
                    'azure_iso_prod' => 'prod',
                    'azure_iso_qualification' => 'qualif',
                    'azure_test' => 'dev',
                    'dev' => 'dev',
                    'developpement' => 'dev',
                    'preprod' => 'preprod',
                    'preproduction' => 'preprod',
                    'pre-production' => 'preprod',
                    'prod' => 'prod',
                    'production' => 'prod',
                    'qualif' => 'qualif',
                    'qualification' => 'qualif',
                    'test' => 'dev',
            ];

    /**
     * Define if the logo must be displayed
     *
     * @return bool
     */
    public function should_display_navbar_logo() {

        // Display the logo on every pages.
        return true;
    }

    /**
     * Return the favicon file url
     *
     * @return moodle_url|string url
     */
    public function favicon() {
        global $CFG;

        $filename = 'favicon';

        if (isset($CFG->sitetype) && isset($this->types[$CFG->sitetype]) && $this->types[$CFG->sitetype] != 'prod') {
            $filename .= '_' . $this->types[$CFG->sitetype];

            // Cannot use image_url here because the file name is not favicon.icon.
            return $CFG->wwwroot . '/theme/mentor/pix/' . $filename . '.ico';
        }

        return $this->image_url($filename, 'theme');
    }

    /**
     * Append site type if it's not a production site.
     *
     * @return string
     */
    public function site_type() {
        global $CFG;

        $output = '';

        if (isset($CFG->sitetype) && (!isset($this->types[$CFG->sitetype]) ||
                                      (isset($this->types[$CFG->sitetype]) && $this->types[$CFG->sitetype] != 'prod'))) {

            $type = isset($this->types[$CFG->sitetype]) ? $this->types[$CFG->sitetype] : $CFG->sitetype;
            $output .= '<span id="site-type" class="type-' . $type . '"> ' . $CFG->sitetype . '</span>';
        }
        return $output;
    }

    /**
     * Render the login form.
     *
     * @param \core_auth\output\login $form The renderable.
     * @return string
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function render_login(\core_auth\output\login $form) {
        global $CFG, $SITE;

        // Require mentor apis.
        require_once($CFG->dirroot . '/local/mentor_core/api/login.php');

        // Check if browser is compatible.
        if (!theme_mentor_check_browser_compatible()) {
            redirect($CFG->wwwroot . '/theme/mentor/pages/browser_not_compatible.php');
        }

        $context = $form->export_for_template($this);

        // Get about link, if exists.
        $context->aboutlink = get_config('theme_mentor', 'about');

        // Fix the list of enabled auths.
        get_enabled_auth_plugins(true);

        $context->agentconnectenabled = false;

        // Catch agent connect data button.
        foreach ($context->identityproviders as $key => $identityprovider) {
            if ($identityprovider['name'] === get_string('agentconnectname', 'theme_mentor')) {
                $context->agentconnectenabled = true;
                $context->agentconnecturl = $identityprovider['url'];
                $context->agentconnectkey = $key;
            }
        }

        // If there is Agent Connect, unset to "identityproviders" context data.
        if (isset($context->agentconnectkey)) {
            unset($context->identityproviders[$context->agentconnectkey]);
            $context->hasidentityproviders = count($context->identityproviders) > 0;
        }

        if (!empty($CFG->auth)) {
            $authsenabled = explode(',', $CFG->auth);
            if (in_array(get_config('theme_mentor', 'agentconnectidentifier'), $authsenabled)) {
                $context->agentconnectenabled = true;
            }
        }

        // Override because rendering is not supported in template yet.
        if ($CFG->rememberusername == 0) {
            $context->cookieshelpiconformatted = $this->help_icon('cookiesenabledonlysession');
        } else {
            $context->cookieshelpiconformatted = $this->help_icon('cookiesenabled');
        }
        $context->errorformatted = $this->error_text($context->error);

        // Manage logo.
        $url = $this->get_logo_url();
        if ($url) {
            $url = $url->out(false);
        }
        $context->logourl = $url;

        $context->sitename = format_string($SITE->fullname, true,
                ['context' => context_course::instance(SITEID), "escape" => false]);
        $context->signupurl = \local_mentor_core\login_api::get_signup_url($context->signupurl);

        $context->mentorpictureurl = $this->image_url('logo-mentor-w', 'theme_mentor');

        // Load the login form template.
        return $this->render_from_template('core/loginform', $context);
    }

    /**
     * Override the custom menu to add some links
     *
     * @param string $custommenuitems
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function custom_menu($custommenuitems = '') {
        global $CFG;

        // Add catalog link.
        $cataloglink = $CFG->wwwroot . '/local/catalog/index.php';
        $render = '<li class="nav-item catalog">';
        $render .= '<a class="nav-item nav-link" href="' . $cataloglink . '">';
        $render .= $this->pix_icon('offre', '', 'theme_mentor');
        $render .= '<span>' . get_string('trainingcatalog', 'theme_mentor') . '</span>';
        $render .= '</a>';
        $render .= '</li>';

        // If the user is not loggedin, return the standard custom menu.
        if (!isloggedin()) {
            return $render . parent::custom_menu($custommenuitems);
        }

        // Require mentor apis.
        require_once($CFG->dirroot . '/local/mentor_core/api/entity.php');
        require_once($CFG->dirroot . '/local/mentor_core/api/session.php');
        require_once($CFG->dirroot . '/local/mentor_core/api/training.php');

        if (empty($custommenuitems) && !empty($CFG->custommenuitems)) {
            $custommenuitems = $CFG->custommenuitems;
        }

        $managedentities = \local_mentor_core\entity_api::count_managed_entities(null, false, null, true, is_siteadmin());

        // Manage entities links.
        if ($managedentities) {

            $managedentitieswithothercapabilites = \local_mentor_core\entity_api::count_managed_entities(null, false, null, true,
                    is_siteadmin(), true);
            $strmanageentities = $managedentitieswithothercapabilites > 1 ? get_string('managemyentities', 'theme_mentor') :
                    get_string('managemyentity', 'theme_mentor');

            $custommenuitems = $strmanageentities . '|/local/entities/index.php' . "\n" .
                               $custommenuitems;

        } else {// Manage trainings links.

            if (isset($_SESSION['lastentity'])) {
                $entity = local_mentor_core\entity_api::get_entity($_SESSION['lastentity']);
                $admincourselist = $entity->get_main_entity()->get_edadmin_courses();
                $trainingcourse = $admincourselist['trainings'];
                $sessioncourse = $admincourselist['session'];
            } else {
                $trainings = local_mentor_core\training_api::get_user_training_courses();
                $trainingcourse = current($trainings);

                $sessions = local_mentor_core\session_api::get_user_session_courses();
                $sessioncourse = current($sessions);
            }

            // Manage training link.
            if (!empty($trainingcourse)) {
                $custommenuitems = get_string('managetrainings', 'theme_mentor') . '|' . $trainingcourse['link'] . "\n" .
                                   $custommenuitems;
            }

            // Manage session link.
            if (!empty($sessioncourse)) {
                $custommenuitems = get_string('managesessions', 'theme_mentor') . '|' . $sessioncourse['link'] . "\n" .
                                   $custommenuitems;
            }
        }

        // Create the menu.
        $custommenu = new custom_menu($custommenuitems, current_language());

        // Render the menu.
        $render .= $this->render_custom_menu($custommenu);

        return $render;
    }

    /**
     * Get the entity contact page link
     *
     * @return string
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function contact_page() {
        global $CFG;
        require_once($CFG->dirroot . '/local/mentor_core/api/profile.php');

        if (isloggedin() && $entity = \local_mentor_core\profile_api::get_user_main_entity()) {
            // Check if contact page is initiliazed.
            if ($entity->contact_page_is_initialized()) {
                return $entity->get_contact_page_url();
            }
        }
        return '';
    }

    /**
     * Get text information footer.
     *
     * @return false|string|null
     * @throws dml_exception
     */
    public function textinfofooter_page() {
        return get_config('theme_mentor', 'textinfofooter');
    }

    /**
     * Get about page link, if set.
     *
     * @return false|string|null
     * @throws dml_exception
     */
    public function about_page() {
        return get_config('theme_mentor', 'about');
    }

    /**
     * Get legal notice page link, if set.
     *
     * @return false|string|null
     * @throws dml_exception
     */
    public function legalnotice_page() {
        return get_config('theme_mentor', 'legalnotice');
    }

    /**
     * Get FAQ page link, if set.
     *
     * @return false|string|null
     * @throws dml_exception
     */
    public function faq_page() {
        return get_config('theme_mentor', 'faq');
    }

    /**
     * Get Mentor version number, if set.
     *
     * @return false|string|null
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function versionnumber_page() {

        $managedentities = \local_mentor_core\entity_api::count_managed_entities(null, false);

        if (!is_siteadmin() && $managedentities == 0) {
            return '';
        }

        return get_config('theme_mentor', 'versionnumber');
    }

    /**
     * Get Mentor licence, if set.
     *
     * @return false|string|null
     * @throws dml_exception
     */
    public function mentorlicence_page() {
        return get_config('theme_mentor', 'mentorlicence');
    }

    /**
     * Get external links.
     *
     * @return string[]
     * @throws dml_exception
     */
    public function externallink_page() {
        return explode("|", get_config('theme_mentor', 'externallinks'));
    }

    /**
     * Get Site Map page link, if set.
     *
     * @return false|string|null
     * @throws dml_exception
     */
    public function sitemap_page() {
        return get_config('theme_mentor', 'sitemap');
    }

    /**
     * Get Accessibility page link, if set.
     *
     * @return false|string|null
     * @throws dml_exception
     */
    public function accessibility_page() {
        return get_config('theme_mentor', 'accessibility');
    }

    /**
     * Get Personal Data page link, if set.
     *
     * @return false|string|null
     * @throws dml_exception
     */
    public function personaldata_page() {
        return get_config('theme_mentor', 'personaldata');
    }

    /**
     * Get url logo.
     *
     * @return false|string|null
     * @throws dml_exception
     */
    public function urllogo_page() {
        return $this->get_logo_url();
    }

    /**
     * Get if user is logged
     */
    public function islogged_page() {
        return isloggedin();
    }

    /**
     * Override the header to add jquery.
     *
     * @return string
     * @throws coding_exception
     */
    public function header() {
        global $CFG;

        // Add jquery and jquery ui.
        $this->page->requires->jquery();
        $this->page->requires->jquery_plugin('ui');
        $this->page->requires->jquery_plugin('ui-css');

        // Add a body class if the current course page is a demo.
        if ($this->page->course->id != 1) {
            require_once($CFG->dirroot . '/local/mentor_core/api/training.php');
            require_once($CFG->dirroot . '/local/mentor_core/api/library.php');

            $training = \local_mentor_core\training_api::get_training_by_course_id($this->page->course->id);
            if ($training && $training->is_from_library()) {
                $this->page->add_body_class('demo-course');
            }
        }
        return parent::header();
    }

    /**
     * Add the status into the course header
     *
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function course_header() {
        global $COURSE, $USER;

        $header = parent::course_header();

        // Check if the current course is not the frontpage.
        if ($COURSE->id == 1) {
            return $header;
        }

        // Get session status.
        if ($session = \local_mentor_core\session_api::get_session_by_course_id($COURSE->id)) {

            if (
                    !has_capability('moodle/course:update', $session->get_context(), $USER) &&
                    !$session->is_tutor($USER)
                    &&
                    (
                            $session->status === \local_mentor_core\session::STATUS_IN_PREPARATION ||
                            $session->status === \local_mentor_core\session::STATUS_OPENED_REGISTRATION
                    )
            ) {
                redirect(new \moodle_url('/theme/mentor/pages/unavailable_session.php', array('id' => $COURSE->id)));
            }

            // Does not display the status of the current session if it is permanent and "in progress" for user participant.
            if (
                    $session->status !== \local_mentor_core\session::STATUS_IN_PROGRESS ||
                    !$session->is_participant($USER) ||
                    $session->sessionpermanent !== '1'
            ) {
                $header .= '<div id="course-status">' . get_string($session->status, 'local_session') . '</div>';
            }

            // Get training status.
        } else if ($training = \local_mentor_core\training_api::get_training_by_course_id($COURSE->id)) {
            if ($training->is_from_library()) {
                $header .= '<div id="course-status">Démo</div>';
            } else {
                $header .= '<div id="course-status">' . get_string($training->status, 'local_trainings') . '</div>';
            }
        }

        return $header;
    }

    /**
     * The standard tags (typically performance information and validation links,
     * if we are in developer debug mode) that should be output in the footer area
     * of the page. Designed to be called in theme layout.php files.
     *
     * @return string HTML fragment.
     */
    public function standard_footer_html() {
        global $CFG;

        require_once($CFG->dirroot . '/local/mentor_core/lib.php');

        $output = parent::standard_footer_html();
        return local_mentor_core_get_footer_specialization($output);
    }

    /**
     * Wrapper for header elements.
     *
     * @return string HTML to display the main header.
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function full_header() {

        if ($this->page->include_region_main_settings_in_header_actions() &&
            !$this->page->blocks->is_block_present('settings')) {
            // Only include the region main settings if the page has requested it and it doesn't already have
            // the settings block on it. The region main settings are included in the settings block and
            // duplicating the content causes behat failures.
            $this->page->add_header_action(html_writer::div(
                    $this->region_main_settings_menu(),
                    'd-print-none',
                    ['id' => 'region-main-settings-menu']
            ));
        }

        $header = new stdClass();
        $header->settingsmenu = $this->context_header_settings_menu();
        $header->contextheader = $this->context_header();
        $header->hasnavbar = empty($this->page->layout_options['nonavbar']);
        $header->navbar = $this->navbar();
        $header->pageheadingbutton = $this->page_heading_button();
        $header->courseheader = $this->course_header();
        $header->headeractions = $this->page->get_header_actions();

        $header->hasprevbutton = 0;

        if ($this->page->has_set_url()) {

            // Back to the course for activity.
            if (strpos($this->page->url, '/mod/') !== false && $this->page->course->format != 'singleactivity') {
                $header->hasprevbutton = 1;
                $header->prevstepurl = (new moodle_url('/course/view.php',
                        ['id' => $this->page->course->id, 'section' => $this->page->cm->sectionnum]))->out();
                $header->prevstetitle = get_string('prevstep', 'theme_mentor');
            }

            // Back to the catalog for training catalog.
            if (strpos($this->page->url, '/local/catalog/pages/') !== false) {
                $header->hasprevbutton = 1;
                $header->prevstepurl = (new moodle_url('/local/catalog/index.php'))->out();
                $header->prevstetitle = get_string('prevstepcatalog', 'theme_mentor');
            }

            // Back to the dashboard for training sheet.
            if (strpos($this->page->url, '/local/trainings/pages/training.php') !== false) {
                $header->hasprevbutton = 1;
                $header->prevstepurl = (new moodle_url('/'))->out();
                $header->prevstetitle = get_string('prevstepdashboard', 'theme_mentor');
            }

            // Back to the training sheet page.
            if (strpos($this->page->url, '/local/trainings/pages/preview.php') !== false) {
                $trainingid = required_param('trainingid', PARAM_INT);
                $header->hasprevbutton = 1;
                $header->prevstepurl = (new moodle_url('/local/trainings/pages/update_training.php',
                        ['trainingid' => $trainingid]))->out();
                $header->prevstetitle = get_string('closetrainingpreview', 'local_trainings');
            }

            // Back to the library page.
            if (strpos($this->page->url, '/local/library/pages/training.php') !== false) {
                $header->hasprevbutton = 1;
                $header->prevstepurl = (new moodle_url('/local/library/index.php'))->out();
                $header->prevstetitle = get_string('libraryreturn', 'theme_mentor');
            }
        }

        return $this->render_from_template('core/full_header', $header);
    }
}
