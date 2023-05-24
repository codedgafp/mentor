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
 * Verify email before signup.
 *
 * @package    theme_mentor
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_mentor;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");
require_once($CFG->dirroot . '/local/mentor_core/classes/database_interface.php');
require_once($CFG->dirroot . '/lib/classes/text.php');

/**
 * verify email form
 *
 * @package    theme_mentor * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author    remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class verify_email_form extends \moodleform {

    /**
     * init verify email form
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    protected function definition() {
        $mform = $this->_form;

        // Professional email address.
        $mform->addElement('text', 'email', get_string('formemail', 'theme_mentor'), array('size' => 40));
        $mform->setType('email', \core_user::get_property_type('email'));
        $mform->addRule('email', get_string('formemptyemail', 'theme_mentor'), 'required');

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
        global $CFG;

        $errors = parent::validation($data, $files);
        $db = \local_mentor_core\database_interface::get_instance();

        // Check if email data exist.
        if (isset($data['email'])) {

            // Check if email is allowed.
            if (!local_mentor_core_email_is_allowed(\core_text::strtolower($data['email']))) {
                $errors['email'] = get_string('formnotallowedemail', 'theme_mentor', get_config('theme_mentor', 'about'));
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

        return $errors;
    }
}
