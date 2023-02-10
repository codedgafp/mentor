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
 * Create an entity presentation course
 *
 * @package    local_entities
 * @copyright  2021 Edunao SAS (contact@edunao.com)
 * @author     adrien <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Include config.php.
require_once('../../../config.php');
require_once($CFG->dirroot . '/local/mentor_core/lib.php');

require_login();

$entityid = required_param('entityid', PARAM_INT);

$entity = \local_mentor_core\entity_api::get_entity($entityid);

if (!$entity->is_manager($USER)) {
    print_error('Permission denied');
}

if (!$course = $entity->create_presentation_page()) {
    print_error('Course already existing');
}

redirect($CFG->wwwroot . '/course/view.php?id=' . $course->id);

