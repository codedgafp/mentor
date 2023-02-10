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
 * Display an error message because the session in unavailable for the current user
 *
 * @package    theme_mentor
 * @copyright  2022 Edunao SAS (contact@edunao.com)
 * @author     adrien <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');

// Get session id.
$courseid = required_param('id', PARAM_INT);

$PAGE->set_url(new moodle_url('/theme/mentor/pages/unavailable_session.php', ['id' => $courseid]));

$id      = required_param('id', PARAM_INT);
$course  = get_course($id);
$context = context_course::instance($id);
$PAGE->set_context($context);
echo $OUTPUT->header();

echo $OUTPUT->notification(get_string('unavailablesession', 'theme_mentor'), 'error');

$continuebutton = $OUTPUT->render(new \single_button(new moodle_url('/'), get_string('continue'), 'post', true));
$continuebutton = html_writer::tag('div', $continuebutton, array('class' => 'mdl-align'));
echo $continuebutton;

echo $OUTPUT->footer();
