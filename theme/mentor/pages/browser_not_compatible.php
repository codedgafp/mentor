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
require_once($CFG->dirroot . '/theme/mentor/lib.php');

// Redirect to login page if browser is compatible.
if (theme_mentor_check_browser_compatible()) {
    redirect(new moodle_url('/'));
}

// Setting config page.
$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/theme/mentor/pages/browser_not_compatible.php'));
$PAGE->set_pagelayout('login');
$PAGE->set_title(get_string('invalidbrowser', 'theme_mentor'));
$sitename = format_string($SITE->fullname);

echo $OUTPUT->header();
echo $OUTPUT->navbar();

echo get_string('invalidbrowseralert', 'theme_mentor');

echo $OUTPUT->footer();
