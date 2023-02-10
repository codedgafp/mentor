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
 * Library training sheet renderer.
 *
 * @package    local_library
 * @copyright  2022 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_library\output;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/catalog/lib.php');
require_once($CFG->dirroot . '/local/mentor_core/api/library.php');

class training_renderer extends \plugin_renderer_base {

    /**
     * First enter to render
     *
     * @param \local_mentor_core\training $training
     * @return string
     * @throws \moodle_exception
     */
    public function display($training) {

        // Get data renderer.
        $paramsrenderer                = \local_mentor_core\library_api::get_training_renderer($training);

        // Get entities to import.
        $listeentities                         = \local_mentor_core\entity_api::get_entities_can_import_training_library_object(
            null, true, false);
        $paramsrenderer->hasimportcapabilities = count($listeentities) > 0;
        $paramsrenderer->singleentity          = count($listeentities) === 1;
        $paramsrenderer->listeentities         = array_values($listeentities);

        // Add strings to JS.
        $this->page->requires->strings_for_js([
            'importoentity',
            'trainingshortnamenotempty',
            'entitymustbeselected',
            'trainingnameused',
            'trainingnameused',
            'confirmimport',
            'confirmation'
        ], 'local_library');

        // Add strings to JS.
        $this->page->requires->js_call_amd(
            'local_library/training_library',
            'init',
            ['trainingid' => $training->id]
        );

        // Call template.
        return $this->render_from_template('local_library/training', $paramsrenderer);
    }
}
