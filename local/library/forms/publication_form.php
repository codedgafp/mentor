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

namespace local_library;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

/**
 * Publication form
 *
 * @package    local_library
 * @copyright  2022 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class publication_form extends \moodleform {

    public $training;

    /**
     * publication_form constructor.
     *
     * @param string $action
     * @param \local_mentor_core\training $data
     * @throws \moodle_exception
     */
    public function __construct($action, $data) {

        // Init training object.
        $this->training = $data;

        parent::__construct($action, null, 'post', '', array('id' => 'mform_publish_library'));
    }

    /**
     * Define form fields
     *
     * @throws \coding_exception
     * @throws \dml_exception
     */
    protected function definition() {
        $mform = $this->_form;

        // Training name.
        $mform->addElement('text', 'trainingname', get_string('trainingfullname', 'local_mentor_core'), [
            'disabled' => 'disabled',
            'value'    =>
                $this->training->name
        ]);
        $mform->setType('trainingname', PARAM_NOTAGS);

        // Training shortname.
        $mform->addElement('text', 'trainingshortname', get_string('trainingshortname', 'local_library'), [
            'disabled' => 'disabled',
            'value'    =>
                get_string('nametrainingpublish', 'local_library', $this->training->shortname)
        ]);
        $mform->setType('trainingshortname', PARAM_NOTAGS);

        $this->add_action_buttons(true, get_string('confirm', 'local_library'));
    }
}
