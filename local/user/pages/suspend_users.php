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
 * Suspend accounts by csv import
 *
 * @package    local_user
 * @copyright  2022 Edunao SAS (contact@edunao.com)
 * @author     adrien <adrien.jamot@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Include config.php.
require_once('../../../config.php');

// Includes.
require_once($CFG->dirroot . '/local/mentor_core/lib.php');
require_once($CFG->dirroot . '/local/user/forms/suspendcsv_form.php');
require_once($CFG->dirroot . '/local/user/forms/suspendusers_form.php');

// Raise time and memory limit.
core_php_time_limit::raise(60 * 60);
raise_memory_limit(MEMORY_HUGE);

// Require login.
require_login();

// Require entity id.
$entityid = required_param('entityid', PARAM_INT);

// Get entity.
$entity = \local_mentor_core\entity_api::get_entity($entityid);

// Get local user edadmin course for navbar link.
$usercourse = $entity->get_edadmin_courses('user');

// Get and set entity context.
$entitycontext = $entity->get_context();
$PAGE->set_context($entitycontext);

// Check capabilities.
require_capability('local/mentor_core:suspendusers', $entitycontext);

$title             = get_string('userssuspension', 'local_user');
$url               = new moodle_url('/local/user/pages/suspend_users.php', ['entityid' => $entityid]);
$usermanagementurl = new moodle_url('/course/view.php', ['id' => $usercourse['id']]);

// Anchor directly to the import report.
$anchorurl = new moodle_url('/local/user/pages/suspend_users.php', ['entityid' => $entityid], 'import-reports');

// Suspend CSV form.
$csvmform    = new suspendcsv_form($anchorurl->out(), ['entityid' => $entityid]);
$csvformdata = $csvmform->get_data();

// Suspend users form.
$suspendusersform     = new suspendusers_form([], $url);
$suspendusersformdata = $suspendusersform->get_data();

// Import users with validated data.
if (null !== $suspendusersformdata) {
    // Suspend accounts.

    // List of users to import.
    $users = json_decode($suspendusersformdata->users, true);

    // Import users.
    local_mentor_core_suspend_users($users);
}

// Set navbar.
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('managespaces', 'format_edadmin'), new moodle_url('/local/entities/index.php'));
$PAGE->navbar->add($entity->get_name());
$PAGE->navbar->add(get_string('edadminusercoursetitle', 'local_user'), $usermanagementurl->out());
$PAGE->navbar->add($title, $url);

// Settings first element page.
$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);

// Output content.
$out = '';

echo $OUTPUT->header();
echo $OUTPUT->heading($title);

// Validate given data from CSV.
if (null !== $csvformdata) {
    $out .= $csvmform->render();

    echo html_writer::div($out);
    $out = '';

    // Convert line breaks into standard line breaks.
    $filecontent = local_mentor_core_decode_csv_content($csvmform->get_file_content('userscsv'));

    // Check if file is valid UTF-8.
    if (false === mb_detect_encoding($filecontent, 'UTF-8', false)) {
        \core\notification::error(get_string('error_encoding', 'local_mentor_core'));
    } else {
        // Convert lines into array.
        $content = str_getcsv($filecontent, "\n");

        $out .= $OUTPUT->heading($title);

        // Errors array.
        $errors = [
            'list' => [], // Errors list.
        ];

        // Preview array.
        $preview = [
            'list'               => [], // Cleaned list of accounts.
            'validforsuspension' => 0, // Number of lines that will suspend accounts.
        ];

        // Build preview and errors array.
        $hasfatalerrors = local_mentor_core_validate_suspend_users_csv($content, $entity, $preview,
            $errors);

        // No fatal error, so we display the preview.
        if (false === $hasfatalerrors) {
            // Preview table.
            $out .= html_writer::tag('h5', get_string('preview_table', 'local_mentor_core'), ['class' => 'report-title']);

            // Building preview table.
            $previewstable       = new html_table();
            $previewstable->head = [
                get_string('csv_line', 'local_mentor_core'),
                get_string('email')
            ];

            // Preview 10 users.
            $previewstable->data = array_slice($preview['list'], 0, 10);

            $out .= html_writer::table($previewstable);

            // Add validated data into the suspend users form.
            $importusersform = new suspendusers_form($preview['list'], $url);
        }

        // Preview report.
        $out .= html_writer::tag('h5', get_string('preview_report', 'local_mentor_core'), ['class' => 'report-title']);

        // Display the report.
        $out .= html_writer::alist([
            get_string('suspender_users_number', 'local_mentor_core', $preview['validforsuspension']),
            get_string('errors_number', 'local_mentor_core', count($errors['list'])),
        ]);

        // Display errors bloc.
        if (count($errors['list']) !== 0) {
            // Errors detected notification.
            \core\notification::warning(get_string('errors_detected', 'local_mentor_core'));

            // Building errors report table.
            $errorstable       = new html_table();
            $errorstable->head = ['Ligne', get_string('error')];
            $errorstable->data = $errors['list'];
            $out               .= html_writer::table($errorstable);
        }

        // Display import users form.
        if (false === $hasfatalerrors) {
            $out .= $importusersform->render();
        }
    }

} else {
    $csvmform->set_data($csvformdata);
    $csvmform->display();
}

echo html_writer::div($out, 'local_mentor_core_report', ['id' => 'import-reports']);

echo $OUTPUT->footer();
