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
 * Training catalog page
 *
 * @package    local_catalog
 * @copyright  2022 Edunao SAS (contact@edunao.com)
 * @author     Remi Colet <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Include config.php.
require_once('../../../config.php');

// Includes.
require_once($CFG->dirroot . '/local/mentor_core/api/training.php');
require_once($CFG->dirroot . '/local/mentor_core/api/catalog.php');

$notification = \core\notification::fetch();

$trainingid = required_param('trainingid', PARAM_INT);

$url = new moodle_url('/local/catalog/pages/training.php', ["trainingid" => $trainingid]);

$context = context_system::instance();

// Settings first element page.
$PAGE->set_url($url);
$PAGE->set_context($context);

$renderer = $PAGE->get_renderer('local_catalog', 'training');

try {
    $training = \local_mentor_core\training_api::get_training($trainingid);
    $sessions = \local_mentor_core\catalog_api::get_sessions_template_by_training($trainingid);
} catch (\Exception $e) {
    $sessions = false;
}

if ($sessions !== false) {
    $templaterederer = $renderer->display($training->convert_for_template(), $sessions);
    $title           = $training->name;
} else {
    $title           = get_string('notaccesstraining', 'local_catalog');
    $templaterederer = $renderer->not_access(get_string('notaccesstraining', 'local_catalog'));
}

$PAGE->set_title($title);
$PAGE->set_heading($title);

// Set navbar.
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('catalogtitle', 'local_catalog'), new moodle_url('/local/catalog/index.php'));
$PAGE->navbar->add($title, $url);

echo $OUTPUT->header();

if (!empty($notification)) {
    foreach ($notification as $notif) {
        if ($notif->get_message_type() === 'success' && $notif->get_message() === get_string('youenrolledincourse', 'enrol')) {
            echo $OUTPUT->notification(get_string('courseaccesssoon', 'local_catalog'), 'success');
        } else {
            echo $OUTPUT->notification($notif->get_message(), $notif->get_message_type());
        }
    }
}

echo $templaterederer;

echo $OUTPUT->footer();
