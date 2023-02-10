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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/mentor_core/lib.php');
require_once($CFG->dirroot . '/local/mentor_core/api/library.php');
require_once($CFG->dirroot . '/local/mentor_specialization/classes/database_interface.php');
require_once($CFG->dirroot . '/local/mentor_specialization/classes/models/mentor_library.php');

/**
 * Plugin library
 *
 * @package    local_library
 * @copyright  2022 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Init library config.
 *
 * @return void
 * @throws coding_exception
 * @throws dml_exception
 * @throws moodle_exception
 */
function local_library_init_config() {
    global $DB;

    local_library_set_library();

    $newroleid = local_mentor_core_create_role(
        get_string('viewrolename', 'local_library'),
        get_string('viewroleshortname', 'local_library'),
        [CONTEXT_COURSECAT]
    );

    $newrole = $DB->get_record('role', ['id' => $newroleid]);
    local_mentor_core_add_capability($newrole, 'local/library:view');
}

/**
 * Setting library entity.
 *
 * @return void
 */
function local_library_set_library() {
    global $USER;

    // Upgrade with command line.
    $user = $USER;
    $USER = get_admin();

    try {
        // Create library if not exit.
        // Set library id to config.
        // After, exception trigger if "get" doesn't work.
        $library = \local_mentor_core\library_api::get_or_create_library();

        // Set hidden library config.
        $dbi = \local_mentor_specialization\database_interface::get_instance();
        $dbi->update_entity_visibility($library->id, \local_mentor_specialization\mentor_library::HIDDEN);
        mtrace('Set config library : OK');
    } catch (\Exception $e) {
        mtrace('Set config library : NOT OK');
    }

    $USER = $user;
}

/**
 * Extend the course navigation
 *
 * @param $settingsnav
 * @param $context
 * @throws coding_exception
 * @throws moodle_exception
 */
function local_library_extend_settings_navigation($settingsnav, $context) {
    global $PAGE;

    // Only add this settings item on non-site course pages.
    if (!$PAGE->course or $PAGE->course->id == 1) {
        return;
    }

    // User must be able to share the training.
    if (
        !is_siteadmin()
    ) {
        return;
    }

    // If the course is not linked to a training then return.
    if (!$training = \local_mentor_core\training_api::get_training_by_course_id($PAGE->course->id)) {
        return;
    }

    // Add a link to the training sheet.
    if ($settingnode = $settingsnav->find('courseadmin', navigation_node::TYPE_COURSE)) {

        if ($training->status === \local_mentor_core\training::STATUS_ELABORATION_COMPLETED) {
            $name         = get_string('publishtraininglibrary', 'local_library');
            $url          = new \moodle_url('/local/library/pages/publication.php', array('trainingid' => $training->id));
            $workflownode = navigation_node::create(
                $name,
                $url,
                navigation_node::NODETYPE_LEAF,
                'trainingtolibrary',
                'trainingtolibrary',
                new pix_icon('book', $name, 'local_library')
            );
            $settingnode->add_node($workflownode, 'questionbank');
        }
    }
}
