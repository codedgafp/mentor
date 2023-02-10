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
 * PLugin library
 *
 * @package    local_trainings
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     Nabil Hamdi <nabil.hamdi@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/format/edadmin/classes/output/interface_renderer.php');
require_once($CFG->dirroot . '/local/trainings/classes/controllers/training_controller.php');
require_once($CFG->dirroot . '/local/mentor_core/api/entity.php');
require_once($CFG->dirroot . '/local/mentor_core/api/training.php');

/**
 * Moodle mandatory function to manage the plugin file permissions
 *
 * @param $course
 * @param $cm
 * @param $context
 * @param $filearea
 * @param $args
 * @param $forcedownload
 * @param array $options
 * @return bool
 * @throws coding_exception
 * @throws dml_exception
 * @throws moodle_exception
 * @throws require_login_exception
 */
function local_trainings_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {

    if (!in_array($filearea, local_trainings_list_filearea_user_access_without_logged_in())) {
        require_login();
    }

    // All training files must be in a course context.
    if ($context->contextlevel != CONTEXT_COURSE) {
        return false;
    }

    $fs           = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath     = "/$context->id/local_trainings/$filearea/$relativepath";
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

    send_stored_file($file, 0, 0, $forcedownload, $options);
}

/**
 * List of file area user access without logged in
 *
 * @return string[]
 */
function local_trainings_list_filearea_user_access_without_logged_in() {
    return [
        'thumbnail',
        'teaserpicture',
        'producerorganizationlogo'
    ];
}

/**
 * Extend the course navigation
 *
 * @param $settingsnav
 * @param $context
 * @throws coding_exception
 * @throws moodle_exception
 */
function local_trainings_extend_settings_navigation($settingsnav, $context) {
    global $PAGE;

    // Only add this settings item on non-site course pages.
    if (!$PAGE->course or $PAGE->course->id == 1) {
        return;
    }

    // User must be able to update the training.
    if (!has_capability('local/trainings:update', $context)) {
        return;
    }

    // If the course is not linked to a training then return.
    if (!$training = \local_mentor_core\training_api::get_training_by_course_id($PAGE->course->id)) {
        return;
    }

    // Add a link to the training sheet.
    if ($settingnode = $settingsnav->find('courseadmin', navigation_node::TYPE_COURSE)) {

        $name         = get_string('fichetraining', 'local_trainings');
        $url          = $training->get_sheet_url() . '&returnto=' . $PAGE->url->out();
        $workflownode = navigation_node::create(
            $name,
            $url,
            navigation_node::NODETYPE_LEAF,
            'training',
            'training',
            new pix_icon('i/settings', $name)
        );
        $settingnode->add_node($workflownode, 'editsettings');
    }
}
