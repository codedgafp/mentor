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
 * Signup page.
 *
 * @package    theme_mentor
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');
require_once($CFG->libdir . '/authlib.php');
require_once($CFG->dirroot . '/theme/mentor/forms/verify_email_form.php');
require_once($CFG->dirroot . '/theme/mentor/forms/signup_form.php');
require_once($CFG->dirroot . '/local/mentor_core/api/login.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/user/editlib.php');
require_once($CFG->dirroot . '/login/lib.php');
require_once($CFG->libdir . '/authlib.php');

if (isloggedin() and !isguestuser()) {
    // Prevent signing up when already logged in.
    redirect(new moodle_url('/'), get_string('cannotsignup', 'error', fullname($USER)));
}

// Settings params.
$email = optional_param('email', null, PARAM_TEXT);

core_login_pre_signup_requests();

$authplugin = signup_is_enabled();

// Setting config page.
$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/theme/mentor/pages/signup.php'));
$PAGE->set_pagelayout('login');
$PAGE->set_title(get_string('createaccount', 'theme_mentor'));
$PAGE->navbar->add(get_string('checkeligibity', 'theme_mentor'), new moodle_url('/theme/mentor/pages/verify_email.php'));
$PAGE->navbar->add(get_string('createaccount', 'theme_mentor'));
$sitename = format_string($SITE->fullname);

// Get form.
$mform = new \theme_mentor\signup_form($FULLME, array('email' => $email));

if ($mform->is_cancelled()) {
    // When user cancel form.
    redirect(new moodle_url('/login/index.php'));
} else if ($mform->is_submitted() && $mform->is_validated()) {
    $data = $mform->get_data();

    $data->username = $data->email;

    // Remove < and > characters.
    $data->firstname                         = str_replace(['<', '>'], '', $data->firstname);
    $data->lastname                          = str_replace(['<', '>'], '', $data->lastname);
    $data->profile_field_attachmentstructure = str_replace(['<', '>'], '', $data->profile_field_attachmentstructure);
    $data->profile_field_affectation         = str_replace(['<', '>'], '', $data->profile_field_affectation);

    // Add missing required fields.
    $user = signup_setup_new_user($data);

    // Plugins can perform post sign up actions once data has been validated.
    core_login_post_signup_requests($data);

    $authplugin->user_signup($data, true); // Prints notice and link to login/index.php.
} else {
    // When user want to see form.
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('createaccount', 'theme_mentor'));
    echo $OUTPUT->navbar();
    echo $mform->display();
}

echo $OUTPUT->footer();
