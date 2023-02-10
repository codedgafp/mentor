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
 * User log table Class
 *
 * @package    logstore_mentor/models
 * @copyright  2021 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace logstore_mentor2\models;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/admin/tool/log/store/mentor2/classes/model/abstractlog.php');

class user extends abstractlog {

    public function get_required_fields() {
        return [
                'userid',
                'entitylogid',
                'trainer',
                'status',
                'category',
                'regionlogid',
                'department'
        ];
    }
}
