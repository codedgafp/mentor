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
 * Class containing data for mysessions block.
 *
 * @package    block_mysessions
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     rcolet <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_mysessions\output;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/mentor_core/api/session.php');

use local_mentor_core\specialization;
use renderable;
use renderer_base;
use templatable;

/**
 * Class containing data for mysessions block.
 *
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     rcolet <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mysessions implements renderable, templatable {

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
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function export_for_template(renderer_base $output) {
        global $USER, $CFG;

        // Get data for the block.
        $sessionsenrol = \local_mentor_core\session_api::get_user_sessions($USER->id, true);

        // Data for the training and session sheet.
        $trainings     = [];
        $finalsessions = [];

        // If there is at least one full session.
        $hassessioncompleted = false;

        foreach ($sessionsenrol as $sessionenrol) {

            // Skip archived sessions or enrol user is enabled.
            if (
                $sessionenrol->status == \local_mentor_core\session::STATUS_ARCHIVED ||
                !$sessionenrol->hasenrollenabled
            ) {
                continue;
            }

            // Default session is not completed.
            $sessionenrol->iscompleted = false;

            // Is completed if session has completed status or user has finished his progress.
            if (
                (isset($sessionenrol->completed) && $sessionenrol->completed) ||
                $sessionenrol->progress === 100
            ) {
                $sessionenrol->iscompleted = true;
                $hassessioncompleted       = true;
            }

            $finalsessions[] = $sessionenrol;

            if (!isset($trainings[$sessionenrol->trainingid])) {
                $trainings[$sessionenrol->trainingid] = \local_mentor_core\training_api::get_training($sessionenrol->trainingid)
                    ->convert_for_template();
            }
            $trainings[$sessionenrol->trainingid]->sessions   = [];
            $trainings[$sessionenrol->trainingid]->sessions[] = $sessionenrol;
        }

        // Create data for the template block.
        $templateparams                      = new \stdClass();
        $templateparams->trainings           = $trainings;
        $templateparams->sessions            = $finalsessions;
        $templateparams->sessionscount       = count($finalsessions);
        $templateparams->catalogurl          = $CFG->wwwroot . '/local/catalog/index.php';
        $templateparams->hassessioncompleted = $hassessioncompleted;

        // Return data for the template block.
        return $templateparams;
    }
}
