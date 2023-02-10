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
 * Plugin library
 *
 * @package    theme_mentor
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     adrien <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Get the scss content of the theme
 *
 * @param $theme
 * @return string
 */
function theme_mentor_get_main_scss_content($theme) {

    // Include the scss of the boost theme.
    $scss = theme_boost_get_main_scss_content($theme);

    return $scss;
}

/**
 * Initialize the page
 *
 * @param moodle_page $page
 * @throws coding_exception
 * @throws moodle_exception
 */
function theme_mentor_page_init(moodle_page $page) {
    global $CFG;

    // Init header display on scroll.
    $page->requires->js_call_amd('theme_mentor/scroll', 'init');
    $page->requires->js_call_amd('theme_mentor/logout', 'init');

    if ($page->context->contextlevel == CONTEXT_COURSE ||
        $page->context->contextlevel == CONTEXT_MODULE) {

        $disabledformats = ['site', 'edadmin'];

        // Add a css class on mentor pages (ex : contact page).
        if (!in_array($page->course->format, $disabledformats) && $page->category->name == 'Pages') {

            $page->add_body_class('mentor-page');

            require_once($CFG->dirroot . '/local/mentor_core/api/entity.php');

            $entity = \local_mentor_core\entity_api::get_entity($page->category->parent);

            // Add a body class for entity managers.
            if (has_capability('local/entities:manageentity', $entity->get_context())) {
                $page->add_body_class('mentor-editing');
            }
        }
    }
}

/**
 * Check browser compatibility.
 * $CFG->browserrequirements must be defined in config.php
 * ex :
 * $CFG->browserrequirements = [
    "Edge"    => 79,
    "Chrome"  => 66,
    "Firefox" => 78,
    "Safari"  => 15,
    "Opera"   => 53
    ];
 *
 * @return boolean
 */
function theme_mentor_check_browser_compatible() {
    global $CFG;

    $browserinformations = get_browser();

    if (!isset($CFG->browserrequirements)) {
        return true;
    }

    if (!array_key_exists($browserinformations->browser, $CFG->browserrequirements)) {
        return false;
    }

    return $CFG->browserrequirements[$browserinformations->browser] <= intval($browserinformations->version) ||
           $browserinformations->version === '0.0';
}
