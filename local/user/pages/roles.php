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
 * Display all categories roles
 *
 * @package    local_user
 * @copyright  2021 Edunao SAS (contact@edunao.com)
 * @author     adrien <adrien.jamot@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Include config.php.
require_once('../../../config.php');

// Includes.
require_once($CFG->dirroot . '/local/mentor_core/api/profile.php');

// Require login.
require_login();

if (!is_siteadmin()) {
    print_error('Permission denied');
}

$title   = get_string('elevatedroles', 'local_user');
$url     = new moodle_url('/local/user/pages/roles.php');
$context = context_system::instance();

// Settings first element page.
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title($title);
$PAGE->set_heading($title);

// Set navbar.
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('managespaces', 'format_edadmin'), new moodle_url('/local/entities/index.php'));
$PAGE->navbar->add($title, $url);

echo $OUTPUT->header();

$renderer = $PAGE->get_renderer('local_user', 'roles');

echo $renderer->display();

echo $OUTPUT->footer();
