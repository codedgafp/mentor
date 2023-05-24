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
 *
 *
 * @package    local_trainings
 * @copyright  2022 Edunao SAS (contact@edunao.com)
 * @author     Remi Colet <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Include config.php.
require_once('../../../config.php');

// Include lib.php.
require_once(__DIR__ . '/../lib.php');

// Includes.
require_once($CFG->dirroot . '/local/mentor_core/api/training.php');
require_once($CFG->dirroot . '/local/mentor_core/api/session.php');
require_once($CFG->dirroot . '/local/mentor_core/api/catalog.php');

// Require login.
require_login();

// Get optional params.
$trainingid = optional_param('trainingid', null, PARAM_INT);
$sessionid = optional_param('sessionid', null, PARAM_INT);

// Set context Page.
$context = context_system::instance();
$PAGE->set_context($context);

// Init renderer.
$renderer = $PAGE->get_renderer('local_catalog', 'training');

try {
    // Check if params has training data.
    if (!is_null($trainingid)) {
        $training = \local_mentor_core\training_api::get_training($trainingid);

        // Check if user has access to training sheet.
        if (
            has_capability('local/trainings:manage', $training->get_entity()->get_context()) ||
            is_enrolled($training->get_context())
        ) {
            $trainingtemplate = $training->convert_for_template();

            $trainingtemplate->accesscourse = true;
            $templaterederer = $renderer->display($trainingtemplate, false);

            // Set data Page.
            $title = $training->name;
            $url = new moodle_url('/local/trainings/pages/training.php', ["trainingid" => $trainingid]);
        }
    }

    // Check if params has session data.
    if (!is_null($sessionid)) {
        $session = \local_mentor_core\session_api::get_session($sessionid);

        // Check if the user has access to the session to access the training sheet.
        if (has_capability('local/session:manage', $session->get_entity()->get_context()) || is_enrolled($session->get_context())) {
            $training = $session->get_training();
            $trainingtemplate = $training->convert_for_template();
            $trainingtemplate->hasonesession = true;

            if (
                has_capability('local/trainings:manage', $training->get_entity()->get_context()) ||
                is_enrolled($training->get_context())
            ) {
                $trainingtemplate->accesscourse = true;
            }

            $templaterederer = $renderer->display(
                $trainingtemplate,
                [$session->convert_for_template()]
            );

            // Set data Page.
            $title = $training->name;
            $url = new moodle_url('/local/trainings/pages/training.php', ["sessionid" => $sessionid]);
        }
    }

    // Check if renderer is setting.
    // If not, user not have data access.
    if (!isset($templaterederer)) {
        $title = get_string('notaccesstraining', 'local_catalog');
        $templaterederer = $renderer->not_access(get_string('notaccesstraining', 'local_catalog'));
        $url = new moodle_url('/local/trainings/pages/training.php');
    }
} catch (\Exception $e) {
    // An error catch when recovering data.
    $title = get_string('notaccesstraining', 'local_catalog');
    $templaterederer = $renderer->not_access(get_string('notaccesstraining', 'local_catalog'));
    $url = new moodle_url('/local/trainings/pages/training.php');
}

$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);

// Set navbar.
$PAGE->navbar->ignore_active();
$PAGE->navbar->add($title, $url);

echo $OUTPUT->header();

echo $templaterederer;

echo $OUTPUT->footer();
