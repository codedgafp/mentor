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

require_once($CFG->dirroot . '/local/mentor_core/api/profile.php');

/**
 * My trainings block.
 *
 * @package    block_mytrainings
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     rcolet <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_mytrainings extends block_base {

    public $blockopen;

    /**
     * Set the block title.
     *
     * @throws coding_exception
     */
    public function init() {
        global $USER;

        $this->blockopen = false;

        if (isset($USER->id) && is_numeric($USER->id)) {
            $profile = \local_mentor_core\profile_api::get_profile($USER->id);

            // Get user preference for block opening.
            if (!is_null($profile)) {
                $this->blockopen = $profile->get_preference('block_mytrainings_open');
            }
        }

        if ($this->blockopen === false) {
            $this->blockopen = 1;
        }

        $this->title = get_string('pluginname', 'block_mytrainings');
    }

    /**
     * Add a class to hide the block
     *
     * @return array
     */
    public function html_attributes() {
        $attributes = parent::html_attributes();

        if ((int) $this->blockopen == 0) {
            $attributes['class'] .= ' hidden-block';
        }
        return $attributes;
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
        parent::get_required_javascript();
        $this->page->requires->strings_for_js([
            'next',
            'previous',
            'copylinktext'
        ], 'local_mentor_core');
        $this->page->requires->strings_for_js([
            'showmore',
            'showless'
        ], 'block_mytrainings');

        $this->page->requires->js_call_amd('block_mytrainings/block_mytrainings', 'init', ['blockopen' => $this->blockopen]);
    }

    /**
     * Get block content.
     *
     * @return stdClass
     * @throws coding_exception
     */
    public function get_content() {

        // Get instance of page renderer.
        $renderer = $this->page->get_renderer('block_mytrainings');

        // Get template with data rendarable.
        $renderable = new \block_mytrainings\output\mytrainings($this->config);
        $content    = $renderer->render($renderable);

        // Create content for the block.
        $this->content = new stdClass();
        $this->content->text = '';

        if (!empty($content)) {
            $this->content->text = '<h2>' . $this->title . '</h2>';

            $button = (int) $this->blockopen == 1 ? '<div class="open-block"><span class="txt">' .
                                                    get_string('showless', 'block_mytrainings')
                                                    . '</span>-</div>' : '<div class="open-block"><span class="txt">' .
                                                                         get_string('showmore', 'block_mytrainings')
                                                                         . '</span>+</div>';

            $this->content->text .= $button;
        }

        $this->content->text .= $content;

        $this->content->footer = '';

        // Return content block.
        return $this->content;
    }
}
