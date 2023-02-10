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
 * Catalog renderer
 *
 * @package    local
 * @subpackage catalog
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_catalog\output;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/catalog/lib.php');

class catalog_renderer extends \plugin_renderer_base {

    /**
     * @var array
     */
    private $collections = [];

    /**
     * @var array
     */
    private $entities = [];

    /**
     * First enter to render
     *
     * @param array $trainings
     * @return string
     * @throws \moodle_exception
     */
    public function display($trainings = []) {
        global $CFG;
        require_once($CFG->dirroot . '/local/mentor_specialization/lib.php');

        // Add strings to JS.
        $this->page->requires->strings_for_js([
            'training_found',
            'trainings_found',
            'no_training_found',
            'not_found',
            'filter_button',
            'filter_button_all',
        ], 'local_catalog');
        $this->page->requires->strings_for_js([
            'next',
            'previous',
        ], 'local_mentor_core');

        // Init and call JS.
        $this->page->requires->js_call_amd('local_catalog/local_catalog', 'init');

        // Parameters for the template.
        $paramsrenderer = new \stdClass();

        // Get all collections.
        $collectionsnames  = local_mentor_specialization_get_collections();
        $collectionscolors = local_mentor_specialization_get_collections('color');

        // Fill entities and collections list.
        foreach ($trainings as $idx => $training) {
            if ('' !== $training->entityname) {
                $this->entities[$training->entityname] = [
                    'id'   => $training->entityid,
                    'name' => $training->entityname,
                ];
            }

            foreach (explode(';', $training->collectionstr) as $collection) {
                if ('' !== $collection) {
                    $this->collections[$collection] = $collection;
                }
            }

            // Build collection tiles.
            $trainings[$idx]->collectiontiles = [];
            foreach (explode(',', $training->collection) as $collection) {
                // If a collection is missing, we skip.
                if (!isset($collectionsnames[$collection])) {
                    continue;
                }

                $tile                               = new \stdClass();
                $tile->name                         = $collectionsnames[$collection];
                $tile->color                        = $collectionscolors[$collection];
                $trainings[$idx]->collectiontiles[] = $tile;
            }
        }

        // Collections list.
        sort($this->collections);
        $paramsrenderer->collections = array_values($this->collections);

        // Entities list.
        uksort($this->entities, 'strcasecmp');
        $paramsrenderer->entities = array_values($this->entities);

        // Trainings list.
        $paramsrenderer->trainings      = array_values($trainings);
        $paramsrenderer->trainingscount = count($trainings);

        // Json encode amd data.
        $paramsrenderer->available_trainings   = json_encode($trainings);
        $paramsrenderer->trainings_dictionnary = json_encode(local_catalog_get_dictionnary($trainings));

        // Call template.
        return $this->render_from_template('local_catalog/catalog', $paramsrenderer);
    }
}
