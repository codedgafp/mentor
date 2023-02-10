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

defined('MOODLE_INTERNAL') || die();

/**
 * My entity block.
 *
 * @package    block_myentity
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     adrien jamot <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_myentity extends block_base {

    /**
     * Set the block title.
     *
     * @throws coding_exception
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_myentity');
    }

    /**
     * Which page types this block may appear on.
     *
     * @return array page-type prefix => true/false.
     * @throws moodle_exception
     */
    public function applicable_formats() {
        global $CFG, $USER;

        // For unit test initialization.
        if ($USER->id) {
            require_once($CFG->dirroot . '/local/mentor_core/api/profile.php');

            // Check if user has a main entity.
            $mainentity = \local_mentor_core\profile_api::get_user_main_entity();

            // If he does not have one, he does not have access to the block.
            if (!$mainentity) {
                return array();
            }
        }

        return array('my' => true);
    }

    /**
     * Are you going to allow multiple instances of each block?
     *
     * @return boolean
     */
    public function instance_allow_multiple() {
        return false;
    }

    /**
     * header will be shown.
     *
     * @return bool
     */
    public function hide_header() {
        return false;
    }

    /**
     * Get block content.
     *
     * @return stdClass
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function get_content() {
        global $CFG;
        require_once($CFG->dirroot . '/local/mentor_core/api/profile.php');

        $mainentity = \local_mentor_core\profile_api::get_user_main_entity();

        // If user does not have main entity, nothing is diplayed.
        if (!$mainentity) {
            return new \stdClass();
        }

        $output = '';

        // Entity logo.
        $logo = $mainentity->get_logo();

        if ($logo) {
            $url    = moodle_url::make_pluginfile_url(
                    $logo->get_contextid(),
                    $logo->get_component(),
                    $logo->get_filearea(),
                    $logo->get_itemid(),
                    $logo->get_filepath(),
                    $logo->get_filename()
            );
            $alt    = get_string('entitylogoalt', 'block_myentity');
            $output .= '<div id="entity-logo"><img src="' . $url . '" alt="' . $alt . '" title="' . $alt . '"/></div>';
        }

        // Presentation course.
        $presentationcourseurl = $mainentity->get_presentation_page_url();

        if ($presentationcourseurl) {
            $output .= '<div id="presentationcourse">' .
                       '<a href="' . $presentationcourseurl . '" class="fr-fi-arrow-right-line">' .
                       get_string('seemore', 'block_myentity') .
                       '</a></div>';
        }

        // Contact course.
        if ($mainentity->contact_page_is_initialized()) {
            $contactcourseurl = $mainentity->get_contact_page_url();

            if ($contactcourseurl) {
                $output .= '<div id="contactcourse"><a href="' . $contactcourseurl . '" class="fr-fi-arrow-right-line">' .
                           get_string('contact', 'block_myentity')
                           . '</a></div>';
            }
        }

        // Add an empty caracter to display the block even if the content is empty.
        if (strlen($output) == 0) {
            $output .= ' ';
        }

        // Create content for the block.
        $this->content         = new stdClass();
        $this->content->text   = $output;
        $this->content->footer = '';

        // Return content block.
        return $this->content;
    }
}
