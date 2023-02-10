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
 * Signup form.
 *
 * @package    theme_mentor
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_mentor;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/mentor_core/classes/database_interface.php');
require_once($CFG->dirroot . '/local/mentor_specialization/lib.php');
require_once($CFG->dirroot . '/local/mentor_core/api/entity.php');
require_once($CFG->dirroot . '/local/mentor_core/lib.php');
require_once($CFG->dirroot . '/lib/classes/text.php');

/**
 * Signup form
 *
 * @package    theme_mentor * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class signup_form extends \moodleform {

    private $email;

    /**
     * signup_form constructor.
     *
     * @param null $action
     * @param null $customdata
     */
    public function __construct($action = null, $customdata = null) {

        if (isset($customdata['email'])) {
            $this->email = $customdata['email'];
        }

        parent::__construct($action, $customdata);
    }

    /**
     * init verify email form
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('html', get_string('formtsignupinformation', 'theme_mentor'));

        // Login collapse.
        $mform->addElement('header', 'header-login-identifier', 'Identifiants');

        // Professional email address.
        $mform->addElement('text', 'email', get_string('formemail', 'theme_mentor'), array('size' => 40));
        $mform->setType('email', \core_user::get_property_type('email'));
        $mform->addRule('email', get_string('formemptyemail', 'theme_mentor'), 'required');
        $mform->setDefault('email', $this->email);

        // Professional email address confirm.
        $mform->addElement('text', 'email2', get_string('formemailconfirm', 'theme_mentor'), array('size' => 40));
        $mform->setType('email2', \core_user::get_property_type('email'));
        $mform->addRule('email2', get_string('formemptyemail', 'theme_mentor'), 'required');

        // Password.
        $mform->addElement('passwordunmask', 'password', get_string('formpassword', 'theme_mentor'), 'size="20"');
        $mform->setType('password', \core_user::get_property_type('password'));
        $mform->disabledIf('password', 'createpassword', 'checked');
        $mform->addElement('static', 'passwordpolicyinfo', '', get_string('formpasswordinformation', 'theme_mentor'));
        $mform->addRule('password', get_string('formemptypassword', 'theme_mentor'), 'required');

        // More details.
        $mform->addElement('header', 'header-login-informations', 'Plus de détails');

        // Firstname.
        $mform->addElement('text', 'firstname', get_string('formfirstname', 'theme_mentor'), array('size' => 40));
        $mform->setType('firstname', PARAM_RAW_TRIMMED);
        $mform->addRule('firstname', get_string('formemptyfirstname', 'theme_mentor'), 'required');

        // Lastname.
        $mform->addElement('text', 'lastname', get_string('formlastname', 'theme_mentor'), array('size' => 40));
        $mform->setType('lastname', PARAM_RAW_TRIMMED);
        $mform->addRule('lastname', get_string('formemptylastname', 'theme_mentor'), 'required');

        // Sexe.
        $sexelist = explode("\n", local_mentor_specialization_list_sexe());
        $sexelist = array_combine($sexelist, $sexelist);
        $sexelist = ['' => get_string('choose') . '...'] + $sexelist;
        $mform->addElement('select', 'profile_field_sexe', get_string('formsexe', 'theme_mentor'), $sexelist);
        $mform->addRule('profile_field_sexe', '', 'required');

        // Birth year.
        $yearlist = explode("\n", local_mentor_specialization_list_years());
        $yearlist = array_combine($yearlist, $yearlist);
        $yearlist = ['' => get_string('choose') . '...'] + $yearlist;
        $mform->addElement('select', 'profile_field_birthyear', get_string('formbirthyear', 'theme_mentor'), $yearlist);
        $mform->addRule('profile_field_birthyear', '', 'required');

        // Status.
        $statuslist = explode("\n", local_mentor_specialization_list_status());
        $statuslist = array_combine($statuslist, $statuslist);
        $statuslist = ['' => get_string('choose') . '...'] + $statuslist;
        $mform->addElement('select', 'profile_field_status', get_string('formstatus', 'theme_mentor'), $statuslist);
        $mform->addRule('profile_field_status', '', 'required');

        // Category.
        $categorylist = explode("\n", local_mentor_specialization_list_categories());
        $categorylist = array_combine($categorylist, $categorylist);
        $categorylist = ['' => get_string('choose') . '...'] + $categorylist;
        $mform->addElement('select', 'profile_field_category', get_string('formcategory', 'theme_mentor'), $categorylist);
        $mform->addRule('profile_field_category', '', 'required');

        // Get all entities can become main or secondary entity.
        $listmainentities      = explode("\n", \local_mentor_core\entity_api::get_entities_list(true, true, false));
        $listmainentities      = array_combine($listmainentities, $listmainentities);
        $listsecondaryentities = $listmainentities;
        $listmainentities      = ['' => get_string('choose') . '...'] + $listmainentities;

        // Mainentity.
        $mform->addElement('select', 'profile_field_mainentity', get_string('formmainentity', 'theme_mentor'), $listmainentities);
        $mform->addRule('profile_field_mainentity', '', 'required');
        $mform->addElement('static', 'mainentitypolicyinfo', '', get_string('formmainentityinformation', 'theme_mentor'));

        // Secondary entities.
        $mform->addElement('autocomplete', 'profile_field_secondaryentities', get_string('formsecondaryentities', 'theme_mentor'),
            $listsecondaryentities,
            ['multiple' => true]
        );
        $mform->addElement('static', 'secondaryentitiespolicyinfo', '',
            get_string('formsecondaryentitiesinformation', 'theme_mentor'));

        // Attachment structure.
        $mform->addElement('text', 'profile_field_attachmentstructure', get_string('formattachmentstructure', 'theme_mentor'),
            array('size' => 40));
        $mform->setType('profile_field_attachmentstructure', PARAM_RAW_TRIMMED);

        // Affectation.
        $mform->addElement('text', 'profile_field_affectation', get_string('formaffectation', 'theme_mentor'), array('size' => 40));
        $mform->setType('profile_field_affectation', PARAM_RAW_TRIMMED);

        // Regions.
        $listregions = explode("\n", local_mentor_specialization_list_regions());
        $listregions = array_combine($listregions, $listregions);
        $listregions = ['' => get_string('choose') . '...'] + $listregions;
        $mform->addElement('select', 'profile_field_region', get_string('formregion', 'theme_mentor'), $listregions);
        $mform->addRule('profile_field_region', '', 'required');

        // Departments.
        $listdepartments = explode("\n", local_mentor_specialization_list_departments());
        $listdepartments = array_combine($listdepartments, $listdepartments);
        $listdepartments = ['' => get_string('choose') . '...'] + $listdepartments;
        $mform->addElement('select', 'profile_field_department', get_string('formdepartment', 'theme_mentor'), $listdepartments);

        // Legal mentions.
        $legalmentionurl = get_config('theme_mentor', 'legalnotice');
        if (!empty($legalmentionurl)) {
            $mform->addElement('html',
                '<a href="' . $legalmentionurl . '" class="signup-link" target="_blank" rel="help opener">' .
                get_string('legalnotice', 'theme_mentor') .
                '</a>');
        }

        // Personal data.
        $personaldataurl = get_config('theme_mentor', 'personaldata');
        if (!empty($personaldataurl)) {
            $mform->addElement('html',
                '<a href="' . $personaldataurl . '" class="signup-link" target="_blank" rel="help opener">' .
                get_string('personaldata', 'theme_mentor') .
                '</a>');
        }

        $this->add_action_buttons(true, get_string('createaccount', 'theme_mentor'));
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
        global $CFG;

        $data = $this->clean_data($data);

        $errors = parent::validation($data, $files);
        $db     = \local_mentor_core\database_interface::get_instance();

        $requiredfields = [
            'firstname', 'lastname', 'profile_field_sexe', 'profile_field_birthyear', 'profile_field_status',
            'profile_field_category', 'profile_field_mainentity', 'profile_field_region'
        ];

        // Check required fields and display a custom message.
        foreach ($requiredfields as $requiredfield) {
            if (empty($data[$requiredfield]) || $data[$requiredfield] == '') {
                $errors[$requiredfield] = get_string('formempty' . $requiredfield, 'theme_mentor');
            }
        }

        // Check if email data exist.
        if (isset($data['email'])) {

            // Check if email is allowed.
            if (!local_mentor_core_email_is_allowed(\core_text::strtolower($data['email'])) ||
                \core_text::strtolower($data['email']) !== $data['email']) {
                $errors['email'] = get_string('formnotallowedemail', 'theme_mentor', get_config('theme_mentor', 'about'));
            }

            // Check if two email input are egals.
            if ($data['email2'] != $data['email']) {
                $errors['email2'] = get_string('formpasswordnotmatch', 'theme_mentor');
            }

            // Check if the email does not exist.
            if ($db->get_user_by_email($data['email'])) {
                $errors['email'] = get_string('formexistemail', 'theme_mentor', $CFG->wwwroot . '/login/forgot_password.php');
            }

            // Check if the email does not empty.
            if (empty($data['email'])) {
                $errors['email'] = get_string('formemptyemail', 'theme_mentor');
            }
        }

        // Check if password data exist.
        if (isset($data['password'])) {
            $errmsg = '';
            if (!check_password_policy($data['password'], $errmsg)) {
                $errors['password'] = get_string('formpasswordinformation', 'theme_mentor');
            }
        }

        return $errors;
    }

    /**
     * Get form data
     *
     * @return mixed|object
     */
    public function get_data() {
        $data = parent::get_data();
        // Return cleanup data.
        return $this->clean_data($data);
    }

    /**
     * Clean form submitted data
     *
     * @param $data
     * @return mixed
     */
    private function clean_data($data) {

        $fields = [
            'firstname',
            'lastname',
            'profile_field_affectation',
            'profile_field_attachmentstructure'
        ];

        foreach ($fields as $field) {
            if (is_array($data)) {
                $data[$field] = str_replace('<', '', $data[$field]);
                $data[$field] = str_replace('>', '', $data[$field]);
            } else {
                $data->{$field} = str_replace('<', '', $data->{$field});
                $data->{$field} = str_replace('>', '', $data->{$field});
            }
        }

        return $data;
    }
}
