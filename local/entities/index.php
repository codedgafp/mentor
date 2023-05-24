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
 * Entities plugin
 *
 * @package    local
 * @subpackage entities
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/local/entities/lib.php');
require_once($CFG->dirroot . '/course/format/edadmin/lib.php');
require_once($CFG->dirroot . '/local/entities/classes/controllers/entity_controller.php');

$context = context_system::instance();
$site = get_site();

$title = get_string('entitymanagementtitle', 'local_entities');

require_login();

// Settings first element page.
$PAGE->set_url('/local/entities/index.php');
$PAGE->set_context($context);
$PAGE->set_title($title);
$PAGE->set_pagelayout('standard');

// Set navbar.
$PAGE->navbar->add($title);

// Check if the current user can manage any entity.
if (empty(\local_entities\entity_controller::count_managed_entities(null, false))) {
    print_error('nopermissions', 'error', '', 'local_entities');
}

// Call renderer.
$renderer = $PAGE->get_renderer('local_entities', 'admin');

$PAGE->requires->jquery_plugin('ui-css');

// Setting header page.
$PAGE->set_heading($title);
echo $OUTPUT->header();
echo $OUTPUT->skip_link_target();

// Displays renderer content.
echo $renderer->display();

// Display footer.
echo $OUTPUT->footer();
