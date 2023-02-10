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
 * Display page for preview a training
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
require_once($CFG->dirroot . '/local/mentor_core/api/catalog.php');

// Require login.
require_login();

// Get optional params.
$trainingid = optional_param('trainingid', null, PARAM_INT);

// Get training.
$training = local_trainings\training_controller::get_training($trainingid);

// Get available sessions.
$sessions = \local_mentor_core\catalog_api::get_sessions_template_by_training($trainingid);

// Set title page.
$title = $training->name . ' (' . get_string('preview', 'local_trainings') . ')';

// Get training template.
$trainingtemplate          = $training->convert_for_template();
$trainingtemplate->preview = true;
$trainingtemplate->title   = $title;

// Create false available session if is empty.
if (empty($sessions)) {
    $falsesession                   = new stdClass();
    $falsesession->id               = 0;
    $falsesession->isenrol          = false;
    $falsesession->placesnotlimited = true;
    $falsesession->isinprogress     = true;
    $falsesession->fullname         = $training->name;
    $falsesession->onlinesession    = "xxh";
    $falsesession->presencesession  = "xxh";
    $falsesession->sessionpermanent = true;
    $falsesession->sessionenddate   = false;

    $sessions[] = $falsesession;
}

foreach ($sessions as $session) {
    $session->preview = true;
}

// Set context Page.
$url = new moodle_url('/local/trainings/pages/preview.php', ['trainingid' => $trainingid]);

// Set Page config.
$PAGE->set_context($training->get_context());
$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);

// Set navbar.
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(ucfirst(get_string('preview', 'local_trainings')));
$PAGE->navbar->add($training->name, $url);

echo $OUTPUT->header();

$renderer = $PAGE->get_renderer('local_catalog', 'training');
echo $renderer->display($trainingtemplate, $sessions);

echo $OUTPUT->footer();
