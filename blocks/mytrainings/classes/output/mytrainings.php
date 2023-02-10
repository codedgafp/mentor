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
 * Class containing data for mytrainings block.
 *
 * @package    block_mytrainings
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     rcolet <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_mytrainings\output;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/mentor_core/api/session.php');

use local_mentor_core\specialization;
use renderable;
use renderer_base;
use templatable;

/**
 * Class containing data for mytrainings block.
 *
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     rcolet <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mytrainings implements renderable, templatable {

    /**
     * @var object An object containing the configuration information for the current instance of this block.
     */
    protected $config;

    /**
     * Constructor.
     *
     * @param object $config An object containing the configuration information for the current instance of this block.
     */
    public function __construct($config) {
        $this->config = $config;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return \stdClass
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function export_for_template(renderer_base $output) {
        global $CFG, $USER;

        // Get data for the block.
        $trainingsuserdesigner = \local_mentor_core\training_api::get_trainings_user_designer($USER, true);

        // Create data for the template block.
        $templateparams = new \stdClass();

        $templateparams->trainings      = [];
        $templateparams->trainingsheets = [];

        foreach ($trainingsuserdesigner as $traininguserdesigner) {
            // Keep only sessions if enroll user is enabled.
            if (!$traininguserdesigner->hasenrollenabled) {
                continue;
            }

            $templateparams->trainings[]                               = $traininguserdesigner;
            $templateparams->trainingsheets[$traininguserdesigner->id] = $traininguserdesigner;
        }

        $templateparams->trainingscount         = count($templateparams->trainingsheets);
        $templateparams->catalogurl             = $CFG->wwwroot . '/local/catalog/index.php';
        $templateparams->trainingssheettemplate = 'local_mentor_core/sheet/training-sheet';

        // Return data for the template block.
        return $templateparams;
    }
}
