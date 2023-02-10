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
 * Specialised backup for local_trainings
 *
 * @package    local_trainings
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     adrien <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class backup_local_trainings_plugin
 */
class backup_local_trainings_plugin extends backup_format_plugin {

    /**
     * Returns the format information to attach to course element
     */
    protected function define_course_plugin_structure() {

        // Define the virtual plugin element with the condition to fulfill.
        $plugin = $this->get_plugin_element(null, null, null);

        $pluginwrapper = new backup_nested_element('images', array('id'), null);

        // Add training sheet files.
        $pluginwrapper->annotate_files('local_trainings', 'thumbnail', null);
        $pluginwrapper->annotate_files('local_trainings', 'producerorganizationlogo', null);
        $pluginwrapper->annotate_files('local_trainings', 'teaserpicture', null);

        $plugin->add_child($pluginwrapper);

        // Don't need to annotate ids nor files.
        return $plugin;
    }
}
