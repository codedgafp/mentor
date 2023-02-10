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
 * User admin roles renderer
 *
 * @package    local_user
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     adrien <adrien.jamot@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_user\output;

require_once(__DIR__ . '/../../../../config.php');
require_once($CFG->dirroot . '/local/user/lib.php');
require_once($CFG->dirroot . '/local/mentor_core/api/entity.php');

// Require login.
require_login();

use local_mentor_core\profile_api;
use \local_user;

class roles_renderer extends \plugin_renderer_base {

    protected $dbinterface;

    /**
     * First enter to render
     *
     * @return string
     * @throws \moodle_exception
     */
    public function display() {
        global $CFG;
        require_once($CFG->dirroot . '/local/mentor_core/api/profile.php');

        // Load language strings for JavaScript.
        $this->page->requires->strings_for_js(array(
            'lastname',
            'firstname',
            'email',
            'connectingentity',
            'region',
            'neverconnected',
            'langfile'
        ), 'local_user');

        $this->page->requires->js_call_amd('local_user/roles', 'init');

        return $this->render_from_template('local_user/roles', []);
    }
}
