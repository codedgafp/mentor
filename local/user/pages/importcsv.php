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
 * Create/update accounts by csv import
 *
 * @package    local_user
 * @copyright  2021 Edunao SAS (contact@edunao.com)
 * @author     adrien <adrien.jamot@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Include config.php.
require_once('../../../config.php');

// Includes.
require_once($CFG->dirroot . '/local/mentor_core/lib.php');
require_once($CFG->dirroot . '/local/mentor_core/forms/importcsv_form.php');
require_once($CFG->dirroot . '/local/user/forms/importusers_form.php');

// Raise time and memory limit.
core_php_time_limit::raise(60 * 60);
raise_memory_limit(MEMORY_HUGE);

// Require login.
require_login();

// Require entity id.
$entityid = required_param('entityid', PARAM_INT);

// Check if the users must be created in the entity.
$addtoentity = optional_param('addtoentity', 0, PARAM_INT);

// Get entity.
$entity = \local_mentor_core\entity_api::get_entity($entityid);

// Get local user edadmin course for navbar link.
$usercourse = $entity->get_edadmin_courses('user');

// Get entity context.
$entitycontext = $entity->get_context();

// Check capabilities.
require_capability('local/mentor_core:importusers', $entitycontext);

$title = get_string('importusers', 'local_user');
$url   = new moodle_url('/local/user/pages/importcsv.php', ['entityid' => $entityid]);

// Set navbar.
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('managespaces', 'format_edadmin'), new moodle_url('/local/entities/index.php'));
$PAGE->navbar->add($entity->get_name());
$PAGE->navbar->add(get_string('edadminusercoursetitle', 'local_user'), new moodle_url('/course/view.php', [
    'id' =>
        $usercourse['id']
]));
$PAGE->navbar->add($title, $url);

// Settings first element page.
$PAGE->set_url($url);
$PAGE->set_context($entitycontext);
$PAGE->set_title($title);
$PAGE->set_heading($title);

$params = [
    'modaltitle'   => get_string('import_reactivate_modal_title', 'local_mentor_core'),
    'modalcontent' => get_string('import_reactivate_modal_content', 'local_mentor_core'),
];

// Require JS.
$PAGE->requires->js_call_amd('local_mentor_core/importcsv', 'init', $params);

// Output content.
$out = '';

echo $OUTPUT->header();
echo $OUTPUT->heading($title);

// Anchor directly to the import report.
$anchorurl = new moodle_url('/local/user/pages/importcsv.php', ['entityid' => $entityid], 'import-reports');

// Import CSV form.
$csvmform    = new importcsv_form($anchorurl->out(), ['entityid' => $entityid]);
$csvformdata = $csvmform->get_data();

// Import users form.
$importusersform     = new importusers_form([], $url);
$importusersformdata = $importusersform->get_data();

// Import users with validated data.
if (null !== $importusersformdata) {

    // List of users to import.
    $users = json_decode($importusersformdata->users, true);

    // List of users to reactivate.
    $userstoreactivate = json_decode($importusersformdata->userstoreactivate, true);

    // Import users.
    local_mentor_core_create_users_csv($users, $userstoreactivate, $entityid, $importusersformdata->addtoentity);
}

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

        $out .= $OUTPUT->heading(get_string('importusers', 'local_user'));

        // Errors array.
        $errors = [
            'list' => [], // Errors list.
        ];

        // Warnings array.
        $warnings = [
            'list' => [], // Errors list.
        ];

        // Preview array.
        $preview = [
            'list'                 => [], // Cleaned list of accounts.
            'validlines'           => 0, // Number of lines without error.
            'validforcreation'     => 0, // Number of lines that will create an account.
            'validforreactivation' => [], // Valid accounts for reactivation.
        ];

        // Other data.
        $other = [
            'entityid'    => $entity->id,
            'addtoentity' => $csvformdata->addtoentity
        ];

        // Build preview and errors array.
        $hasfatalerrors = local_mentor_core_validate_users_csv($content, $csvformdata->delimiter_name, null, $preview,
            $errors, $warnings, $other);

        // No fatal error, so we display the preview.
        if (false === $hasfatalerrors) {
            // Preview table.
            $out .= html_writer::tag('h5', get_string('preview_table', 'local_mentor_core'), ['class' => 'report-title']);

            // Building preview table.
            $previewstable       = new html_table();
            $previewstable->head = [
                get_string('csv_line', 'local_mentor_core'),
                get_string('lastname'),
                get_string('firstname'),
                get_string('email', 'local_mentor_core')
            ];
            $previewstable->data = array_slice($preview['list'], 0, 10);
            $out                 .= html_writer::table($previewstable);

            // Add validated data into the import users form.
            $importusersform = new importusers_form($preview['list'], $preview['validforreactivation'], $addtoentity, $url);
        }

        // Preview report.
        $out .= html_writer::tag('h5', get_string('preview_report', 'local_mentor_core'), ['class' => 'report-title']);

        // Display the report.
        $out .= html_writer::alist([
            get_string('identified_users', 'local_mentor_core', $preview['validlines']),
            get_string('account_creation_number', 'local_mentor_core', $preview['validforcreation']),
            get_string('account_reactivation_number', 'local_mentor_core', count($preview['validforreactivation'])),
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

        // Display warnings bloc.
        if (count($warnings['list']) !== 0) {
            // Errors detected notification.
            \core\notification::warning(get_string('warnings_detected', 'local_mentor_core'));

            // Building errors report table.
            $warningstable       = new html_table();
            $warningstable->head = ['Ligne', get_string('warning', 'local_mentor_core')];
            $warningstable->data = $warnings['list'];
            $out                 .= html_writer::table($warningstable);
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
