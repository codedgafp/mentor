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
 * My sessions block.
 *
 * @package    block_mysessions
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     rcolet <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_mysessions extends block_base {

    /**
     * Set the block title.
     *
     * @throws coding_exception
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_mysessions');
    }

    /**
     * Which page types this block may appear on.
     *
     * @return array page-type prefix => true/false.
     */
    public function applicable_formats() {
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
        return true;
    }

    /**
     * Allows the block to load any JS it requires into the page.
     */
    public function get_required_javascript() {
        global $USER;

        parent::get_required_javascript();

        $this->page->requires->strings_for_js([
            'next',
            'previous',
            'copylinktext'
        ], 'local_mentor_core');

        $this->page->requires->strings_for_js([
            'session',
        ], 'block_mysessions');

        $params = [
            // Get show completed session user preference.
            // If equals 1 or false (no preference register) is true.
            'showsessioncompleted' => \local_mentor_core\profile_api::get_user_preference($USER->id,
                    'block_mysessions_completed')
                                      !== '0'
        ];

        $this->page->requires->js_call_amd('block_mysessions/block_mysessions', 'init', $params);
    }

    /**
     * Get block content.
     *
     * @return stdClass
     * @throws coding_exception
     */
    public function get_content() {

        // Get instance of page renderer.
        $renderer = $this->page->get_renderer('block_mysessions');

        // Get template with data rendarable.
        $renderable = new \block_mysessions\output\mysessions($this->config);

        // Create content for the block.
        $this->content = new stdClass();
        $this->content->text = '<h2>' . $this->title . '</h2>';
        $this->content->text .= $renderer->render($renderable);
        $this->content->footer = '';

        // Return content block.
        return $this->content;
    }
}
