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
 * Class library
 *
 * @package    local_mentor_core
 * @copyright  2022 Edunao SAS (contact@edunao.com)
 * @author     adrien <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mentor_specialization;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/local/mentor_core/classes/model/model.php');
require_once($CFG->dirroot . '/local/mentor_core/classes/model/library.php');

class mentor_library extends \local_mentor_core\library {

    public const HIDDEN = 1;

    /**
     * library constructor.
     *
     * @param int $id id is an entity id
     * @throws \moodle_exception
     */
    public function __construct($id) {
        parent::__construct($id);
        $dbi = \local_mentor_specialization\database_interface::get_instance();
        $dbi->update_entity_visibility($this->id, self::HIDDEN);
    }

    /**
     * Get library trainings
     *
     * @return \stdClass[]
     * @throws \dml_exception
     */
    public function get_trainings() {
        $db = database_interface::get_instance();
        return $db->get_library_trainings();
    }
}
