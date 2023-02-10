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
 * Publication library page.
 *
 * @package    local_library
 * @copyright  2022 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->dirroot . '/local/mentor_core/api/training.php');
require_once($CFG->dirroot . '/local/mentor_core/api/library.php');
require_once($CFG->dirroot . '/local/library/forms/publication_form.php');

// Get params.
$trainingid = required_param('trainingid', PARAM_INT);

// Get training selected.
$training = \local_mentor_core\training_api::get_training($trainingid);

// Check access.
if (
    $training->status !== \local_mentor_core\training::STATUS_ELABORATION_COMPLETED ||
    !is_siteadmin()
) {
    print_error('Permission denied');
}

// Set data.
$context   = $training->get_context();
$title     = get_string('publicationtraininglibrarytitle', 'local_library', $training->name);
$url       = new \moodle_url('/local/library/pages/publication.php', array('trainingid' => $trainingid));
$course    = $training->get_course();
$courseurl = $training->get_url();

// Set page config.
$PAGE->set_context($context);
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_url($url);
$PAGE->set_course($course);

// Set navbar.
$PAGE->navbar->ignore_active();
$PAGE->navbar->add($training->shortname, $training->get_url());
$PAGE->navbar->add(get_string('publicationtraininglibrary', 'local_library'), $url);

// Set form.
$form = new \local_library\publication_form($url, $training);

// When form is submitted.
if ($data = $form->get_data()) {
    \local_mentor_core\library_api::publish_to_library($trainingid);

    // Set JS Success.
    $PAGE->requires->strings_for_js(['publicationtraininglibrarymodal'], 'local_library');
    $PAGE->requires->strings_for_js(['close', 'confirmation'], 'local_mentor_core');
    $PAGE->requires->js_call_amd('local_library/local_library', 'publicationSuccess', array(
        $courseurl->out(false)
    ));
} else if ($form->is_cancelled()) {
    // Redirection when form is cancelled.
    redirect($courseurl);
}

// Display the form.
echo $OUTPUT->header();

// Display the form.
$form->display();

echo $OUTPUT->footer();
