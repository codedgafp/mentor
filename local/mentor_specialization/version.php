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
 * Plugin version file
 *
 * @package    local_mentor_specialization
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     adrien <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version  = 2022114003;       // The current module version (Date: YYYYMMDDXX).
$plugin->requires = 2014051219;       // Requires this Moodle version.
/** @var String $plugin */
$plugin->component    = 'local_mentor_specialization';       // Full name of the plugin (used for diagnostics).
$plugin->dependencies = array(
    'local_mentor_core'         => 2020112700,
    'enrol_sirh'                => 2022042700,
    'profilefield_autocomplete' => 2022071900
);
