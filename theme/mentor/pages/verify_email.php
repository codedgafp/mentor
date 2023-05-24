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

require('../../../config.php');
require_once($CFG->libdir . '/authlib.php');
require_once($CFG->dirroot . '/theme/mentor/forms/verify_email_form.php');

if (isloggedin() and !isguestuser()) {
    // Prevent signing up when already logged in.
    redirect(new moodle_url('/'), get_string('cannotsignup', 'error', fullname($USER)));
}

$authplugin = signup_is_enabled();

// Setting config page.
$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/theme/mentor/pages/verify_email.php'));
$PAGE->set_pagelayout('login');
$PAGE->set_title(get_string('checkeligibity', 'theme_mentor'));
$PAGE->navbar->add(get_string('checkeligibity', 'theme_mentor'));
$sitename = format_string($SITE->fullname);

// Get form.
$mform = new \theme_mentor\verify_email_form();

if ($mform->is_cancelled()) {
    // When user cancel form.
    redirect(new moodle_url('/login/index.php'));
} else if ($mform->is_submitted() && $mform->is_validated()) {
    // When form is valid.
    $data = $mform->get_data();
    redirect(new moodle_url('/theme/mentor/pages/signup.php', array('email' => $data->email)));
} else {
    $userprofileurl = new moodle_url('/user/profile.php');

    // When user want to see form.
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('checkeligibity', 'theme_mentor'));
    echo $OUTPUT->navbar();
    echo '<p id="verifywarningsinfo"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> ' .
         get_string('verifywarningsinfo', 'theme_mentor', $userprofileurl->out()) .
         '</p>';
    $ensavoirplusurl = (new moodle_url('/local/staticpage/view.php', array('page' => 'ensavoirplus')))->out(false);
    $donneespersonnellesurl = (new moodle_url('/local/staticpage/view.php', array('page' => 'donneespersonnelles')))->out(false);
    echo '<p id="rgpd-mentions">' .
         get_string('rgpdmentions', 'theme_mentor',
                 ['ensavoirplusurl' => $ensavoirplusurl, 'donneespersonnellesurl' => $donneespersonnellesurl]) .
         '</p>';
    echo $mform->display();
}

echo $OUTPUT->footer();
