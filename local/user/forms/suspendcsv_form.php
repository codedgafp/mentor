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
 * Suspend users by CSV form
 *
 * @package    local_user
 * @copyright  2022 Edunao SAS (contact@edunao.com)
 * @author     adrien <adrien.jamot@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/lib/csvlib.class.php');

class suspendcsv_form extends moodleform {

    /**
     * Maximum bytes of uploaded file
     */
    private const _MAXBYTES = 512000;

    public $entityid;

    /**
     * import csv constructor.
     *
     * @param string $action
     * @param \stdClass $data
     */
    public function __construct($action, $data) {

        if (isset($data['entityid'])) {
            $this->entityid = $data['entityid'];
        }

        parent::__construct($action);
    }

    /**
     * Define form fields
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'importcsvheader', get_string('upload'));

        $url = new moodle_url('/local/user/data/example_suspension.csv');

        $link = html_writer::link($url, 'example_suspension.csv');
        $mform->addElement('static', 'examplecsv', get_string('examplecsv', 'tool_uploaduser'), $link);
        $mform->addHelpButton('examplecsv', 'examplecsv', 'tool_uploaduser');

        $mform->addElement('filepicker', 'userscsv', get_string('file'), null,
            ['maxbytes' => self::_MAXBYTES, 'accepted_types' => 'text/csv']);
        $mform->addRule('userscsv', get_string('required', 'local_mentor_core'), 'required');

        $this->add_action_buttons(false, get_string('continue_import', 'local_mentor_core'));
    }
}
