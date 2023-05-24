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
 * Add training page
 *
 * @package    local_trainings
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Include config.php.
require_once('../../../config.php');

// Include lib.php.
require_once(__DIR__ . '/../lib.php');

// Require login.
require_login();

$entityid = required_param('entityid', PARAM_INT);

if (!$entityid) {
    throw new \coding_exception('The entity ID must be set.');
}

// Get entity infos.
$entity = \local_mentor_core\entity_api::get_entity($entityid);

// Check capabilities.
require_capability('local/trainings:create', $entity->get_context());

// Set page url.
$url = new moodle_url('/local/trainings/pages/add_training.php', array('entityid' => $entityid));

// Set navbar.
$PAGE->navbar->add(get_string('managespaces', 'local_trainings'), new moodle_url('/local/entities/index.php'));
$PAGE->navbar->add($entity->name);

$trainingcourse = $entity->get_edadmin_courses('trainings');
$PAGE->navbar->add(get_string('managetrainings', 'local_trainings'), $trainingcourse['link']);

$PAGE->navbar->add(get_string('addtraining', 'local_trainings'), $url);

$cancelurl = $trainingcourse['link'];

// Set page url.
$PAGE->set_url($url);

// Fetch context.
$context = $entity->get_context();

// Set page context.
$PAGE->set_context($context);

// Set page title.
$PAGE->set_heading($entity->name);
$PAGE->set_title($entity->name . ': ' . get_string('addtraining', 'local_trainings'));

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

$forminfos = new stdClass();
$forminfos->entity = $entity;
$forminfos->logourl = $logourl;
$forminfos->actionurl = $url->out();

$form = \local_mentor_core\training_api::get_training_form($url->out(), $forminfos);

// When form is submitted.
if ($data = $form->get_data()) {
    try {
        // Add new training.
        $training = local_trainings\training_controller::add_training($data, $form);
    } catch (\moodle_exception $e) {
        debugging('Cannot add or update a training: ' . $e->getMessage(), DEBUG_DEVELOPER);
    }

    // Just submit.
    if (isset($data->submitbutton)) {
        // Redirect to the training.
        redirect($training->get_url());
    }

    // Redirect to the training preview page.
    redirect(new moodle_url('/local/trainings/pages/preview.php', ['trainingid' => $training->id]));
} else if ($form->is_cancelled()) {
    // Redirection when form is cancelled.

    redirect($cancelurl);
}

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('addtraining', 'local_trainings'));

echo '<p id="warningsinfo">' . get_string('warningsinfo', 'local_trainings') . '</p>';

// Display form.
echo $form->display();

echo $OUTPUT->footer();
