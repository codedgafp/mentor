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
 * This file contains main class for the admin edadmin
 *
 * @package    local_entities
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/course/format/edadmin/lib.php');

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
 * @throws moodle_exception
 * @throws require_login_exception
 */
function local_entities_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    require_login();

    if ($context->contextlevel != CONTEXT_COURSECAT) {
        return false;
    }

    // Check file areas.
    $areas = array(
        'logo'
    );
    // The only readable files are logos and attachments.
    if (!in_array($filearea, $areas)) {
        return false;
    }

    $fs           = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath     = "/$context->id/local_entities/$filearea/$relativepath";
    if (!$file = $fs->get_file_by_hash(sha1($fullpath))) {
        return false;
    }

    send_stored_file($file, 0, 0, $forcedownload, $options);
}
