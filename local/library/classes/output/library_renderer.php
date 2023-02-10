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
 * Library renderer
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

class library_renderer extends \plugin_renderer_base {

    /**
     * First enter to render
     *
     * @return string
     * @throws \moodle_exception
     */
    public function display() {
        // Init and call JS to library introduction.
        $this->page->requires->js_call_amd('local_library/local_library', 'initIntroductionLibrary', []);

        $paramsrenderer = \local_mentor_core\library_api::get_params_renderer();

        if ($paramsrenderer->trainingscount > 0) {
            // Add strings to JS.
            $this->page->requires->strings_for_js([
                'pleaserefresh',
            ], 'format_edadmin');

            $this->page->requires->strings_for_js([
                'training_found',
                'trainings_found',
                'no_training_found',
            ], 'local_library');

            // Init and call JS to library.
            $jsparams              = new \stdClass();
            $jsparams->collections = local_mentor_specialization_get_collections();
            $this->page->requires->js_call_amd('local_library/local_library', 'init', $jsparams);
        }

        // Call template.
        return $this->render_from_template('local_library/library', $paramsrenderer);
    }
}
