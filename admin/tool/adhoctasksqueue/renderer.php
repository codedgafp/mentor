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
 * Renderer for the tool manager front template
 *
 * @package    tool_adhoctasksqueue
 * @copyright  2022 Edunao SAS (contact@edunao.com)
 * @author     Julien Buabent <julien.buabent@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_adhoctasksqueue_renderer extends plugin_renderer_base {

    public function adhoc_tasks_table() {
        return $this->render_from_template('tool_adhoctasksqueue/adhoctaskslist', []);
    }

}
