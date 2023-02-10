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
 * Library main page
 *
 * @package    local_library
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/local/library/lib.php');
require_once($CFG->dirroot . '/local/mentor_core/api/library.php');

// Check if the user can view the library.
if (!\local_mentor_core\library_api::user_has_access()) {
    throw new \moodle_exception('librarynotaccessible', 'local_library');
}

$trainingid = optional_param('trainingid', null, PARAM_INT);

// Redirect to the training sheet page.
if (!is_null($trainingid)) {
    redirect($CFG->wwwroot . '/local/library/pages/training.php?trainingid=' . $trainingid);
}

$library = \local_mentor_core\library_api::get_library();
$context = $library->get_context();

$site = get_site();

// Settings first element page.
$PAGE->set_url('/local/library/index.php');
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_library'));
$PAGE->set_pagelayout('standard');

// Set navbar.
$PAGE->navbar->add(get_string('pluginname', 'local_library'));

// Call renderer.
$renderer = $PAGE->get_renderer('local_library', 'library');

$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

// Setting header page.
$PAGE->set_heading(get_string('pluginname', 'local_library'));
echo $OUTPUT->header();
echo $OUTPUT->skip_link_target();

// Displays renderer content.
echo $renderer->display();

// Display footer.
echo $OUTPUT->footer();
