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
 * Training catalog renderer
 *
 * @package    local_catalog
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_catalog\output;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/catalog/lib.php');

class training_renderer extends \plugin_renderer_base {

    /**
     * First enter to render
     *
     * @param \stdClass $training
     * @param \stdClass[] $sessions
     * @return string
     * @throws \moodle_exception
     */
    public function display($training, $sessions) {

        // Get all collections.
        $collectionsnames  = local_mentor_specialization_get_collections();
        $collectionscolors = local_mentor_specialization_get_collections('color');

        // Build collection tiles.
        $training->collectiontiles = [];
        foreach (explode(',', $training->collection) as $collection) {
            // If a collection is missing, we skip.
            if (!isset($collectionsnames[$collection])) {
                continue;
            }

            $tile                        = new \stdClass();
            $tile->name                  = $collectionsnames[$collection];
            $tile->color                 = $collectionscolors[$collection];
            $training->collectiontiles[] = $tile;
        }

        $training->hasproducerorganization = false;

        if (!empty($training->producingorganizationlogo) || !empty($training->producingorganization) || !empty
            ($training->contactproducerorganization)) {
            $training->hasproducerorganization = true;
        }

        $training->sessions           = $sessions;
        $training->available_sessions = json_encode($sessions);

        // Init and call JS.
        $this->page->requires->strings_for_js([
            'cancel',
        ], 'format_edadmin');
        $this->page->requires->strings_for_js([
            'register',
            'access',
            'toconnect',
            'nologginsessionaccess',
            'registrationsession',
        ], 'local_catalog');
        $this->page->requires->strings_for_js([
            'copylinktext',
            'copylinkerror',
            'enrolmentpopuptitle'
        ], 'local_mentor_core');

        // JS init.
        $this->page->requires->js_call_amd('local_catalog/training_catalog', 'init');

        // Call template.
        return $this->render_from_template(\local_mentor_core\catalog_api::get_training_template('local_catalog/training'),
            $training);
    }

    /**
     * Not Access notification render
     *
     * @return string
     * @throws \moodle_exception
     */
    public function not_access($message) {
        \core\notification::error($message);

        return '';
    }
}
