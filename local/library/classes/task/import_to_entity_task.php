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
 * Ad hoc task for import training library to entity
 *
 * @package    local_library
 * @copyright  2022 Edunao SAS (contact@edunao.com)
 * @author     adrien <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_library\task;

class import_to_entity_task extends \core\task\adhoc_task {

    /**
     * Execute the task
     *
     * @return \local_mentor_core\training
     * @throws \dml_exception
     * @throws \moodle_exception
     * @throws \restore_controller_exception
     */
    public function execute() {
        global $CFG, $USER;

        require_once($CFG->dirroot . '/local/mentor_core/api/training.php');

        $USER = get_admin();

        $data = $this->get_custom_data();

        // Define all required custom data fields.
        $requiredfields = ['trainingid', 'trainingshortname', 'destinationentity'];

        // Check all required fields.
        foreach ($requiredfields as $requiredfield) {
            if (!isset($data->{$requiredfield})) {
                throw new \coding_exception('Field ' . $requiredfield . ' is missing in custom data');
            }
        }

        // Trim the training name.
        $data->trainingshortname = trim($data->trainingshortname);

        // Get the training.
        $oldtraining = \local_mentor_core\training_api::get_training($data->trainingid);

        // Duplicate the training.
        $newtraining = $oldtraining->duplicate($data->trainingshortname, $data->destinationentity);

        // Get recipient and sender.
        $creator     = \core_user::get_user($this->get_userid());
        $supportuser = \core_user::get_support_user();

        // Get the content of the email.
        $content     = get_string('import_to_entity_email', 'local_library', array(
            'newtrainingurlsheet'  => $newtraining->get_sheet_url()->out(false),
            'newtrainingfullname'  => $newtraining->name,
            'newtrainingshortname' => $newtraining->shortname,
            'oldtrainingurlsheet'  => (new \moodle_url(
                '/local/library/pages/training.php',
                array('trainingid' => $oldtraining->id)
            ))->out(false),
            'oldtrainingfullname'  => $oldtraining->name,
        ));
        $contenthtml = text_to_html($content, false, false, true);

        // Send the email.
        email_to_user($creator, $supportuser,
            get_string('import_to_entity_object_email', 'local_library', $newtraining->name), $content,
            $contenthtml);

        return $newtraining;
    }
}

