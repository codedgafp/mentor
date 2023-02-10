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
 * Plugin lib
 *
 * @package    atto_video
 * @copyright  2021 Edunao SAS (contact@edunao.com)
 * @author     adrien <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Initialise the strings required for js
 */
function atto_video_strings_for_js() {
    global $PAGE;

    $strings = array(
            'save',
            'fillurl',
            'inserturl',
            'errorvalidurl',
            'errorallowedurl',
            'erroremptyurl',
            'allowed_domain'
    );

    $PAGE->requires->strings_for_js($strings, 'atto_video');
}

/**
 * Set JS params
 *
 * @param $elementid
 * @param $options
 * @param $fpoptions
 * @return array
 * @throws dml_exception
 */
function atto_video_params_for_js($elementid, $options, $fpoptions) {
    $alloweddomains = explode("\r\n", get_config('atto_video', 'alloweddomains'));

    return array('alloweddomains' => $alloweddomains);
}
