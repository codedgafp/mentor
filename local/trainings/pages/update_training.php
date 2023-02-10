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
 * Display page for updating a training
 *
 * @package    local_trainings
 * @copyright  nabil 2020 Edunao SAS (contact@edunao.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Include config.php.
require_once('../../../config.php');

global $CFG;

// Include lib.php.
require_once(__DIR__ . '/../lib.php');

require_once($CFG->dirroot . '/local/mentor_core/api/library.php');

// Require login.
require_login();

$trainingid = required_param('trainingid', PARAM_INT);
$returnto   = optional_param('returnto', null, PARAM_LOCALURL);

if (!$trainingid) {
    throw new \coding_exception('The entity ID must be set.');
}

$training = local_trainings\training_controller::get_training($trainingid);

// Get entity infos.
$entity = $training->get_entity();

$context = $training->get_context();

// Check capabilities.
if (!has_capability('local/trainings:update', $context) && !has_capability('local/trainings:view', $context)) {
    print_error('Permission denied');
}

// Set page url.
$url = new moodle_url('/local/trainings/pages/update_training.php', ['trainingid' => $trainingid]);

// Set navbar.
$PAGE->navbar->add($entity->name);

if (has_capability('local/trainings:manage', $entity->get_context())) {
    $trainingcourse = $entity->get_edadmin_courses('trainings');
    $PAGE->navbar->add(get_string('trainingmanagement', 'local_trainings'), $trainingcourse['link']);
}

$PAGE->navbar->add($training->name, $training->get_url());
$PAGE->navbar->add(get_string('updatetraining', 'local_trainings'), $url);

// Set page url.
$PAGE->set_url($url);

// Fetch context.
$context = $training->get_context();

// Set page context.
$PAGE->set_context($context);

// Set page title.
$PAGE->set_heading($entity->name);
$PAGE->set_title($entity->name . ': ' . get_string('updatetraining', 'local_trainings'));

$logo = $entity->get_logo();

$logourl = '';
if ($logo) {
    $logourl = moodle_url::make_pluginfile_url(
        $logo->get_contextid(),
        $logo->get_component(),
        $logo->get_filearea(),
        $logo->get_itemid(),
        $logo->get_filepath(),
        $logo->get_filename()
    );
}

$forminfos            = new stdClass();
$forminfos->training  = $training;
$forminfos->entity    = $entity;
$forminfos->logourl   = $logourl;
$forminfos->publish    = \local_mentor_core\library_api::get_library_publication($training->id);
$forminfos->actionurl = $url->out();
$forminfos->returnto  = empty($returnto) ? $training->get_url() : $returnto;

$form = \local_mentor_core\training_api::get_training_form($url->out(), $forminfos);

// When form is submitted.
if ($data = $form->get_data()) {
    try {

        // Update a training.
        $training = local_trainings\training_controller::update_training($data, $form);
    } catch (\moodle_exception $e) {
        debugging(get_string('debugaddupdatetraining', 'local_trainings', $e->getMessage()), DEBUG_DEVELOPER);
    }

    // Just submit.
    if (isset($data->submitbutton)) {
        // Redirect to the training.
        redirect($data->returnto);
    }

    // Redirect to the training preview page.
    redirect(new moodle_url('/local/trainings/pages/preview.php', ['trainingid' => $trainingid]));
} else if ($form->is_cancelled()) {
    // Redirection when form is cancelled.

    redirect($forminfos->returnto);
}

// Show form for updating training.
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('updatetraining', 'local_trainings'));

echo '<p id="warningsinfo">' . get_string('warningsinfo', 'local_trainings') . '</p>';

// Prepare initial form data.
$edittraining = $training->prepare_edit_form();

$form->set_data($edittraining);

// Display form.
echo $form->display();

echo $OUTPUT->footer();
