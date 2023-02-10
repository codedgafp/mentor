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

require_once($CFG->dirroot . '/blocks/moodleblock.class.php');

/**
 * Archived sessions block.
 *
 * @package    block_archivedsessions
 * @copyright  2022 Edunao SAS (contact@edunao.com)
 * @author     adrien <adrien.jamot@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_archivedsessions extends block_base {

    /**
     * Add a class to hide the block
     *
     * @return array
     */
    public function html_attributes() {
        $attributes          = parent::html_attributes();
        $attributes['class'] .= ' hidden-block';
        return $attributes;
    }

    /**
     * Set the block title.
     *
     * @throws coding_exception
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_archivedsessions');
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
            'session',
            'showmore',
            'showless'
        ], 'block_archivedsessions');

        $this->page->requires->js_call_amd('block_archivedsessions/block_archivedsessions', 'init');
    }

    /**
     * Get block content.
     *
     * @return stdClass
     * @throws coding_exception
     */
    public function get_content() {

        // Get instance of page renderer.
        $renderer = $this->page->get_renderer('block_archivedsessions');

        // Get template with data rendarable.
        $renderable = new \block_archivedsessions\output\archivedsessions($this->config);
        $content    = $renderer->render($renderable);

        // Create content for the block.
        $this->content = new stdClass();
        $this->content->text = '';

        if (!empty($content)) {
            $this->content->text = '<h2>' . $this->title . '</h2>';
            $this->content->text .= '<div class="open-block"><span class="txt">' .
                                    get_string('showmore', 'block_archivedsessions')
                                    . '</span>+</div>';
            $this->content->text .= $this->help_button();
        }

        $this->content->text .= $content;

        $this->content->footer = '';

        // Return content block.
        return $this->content;
    }

    /**
     * Add an help button
     *
     * @return string
     */
    public function help_button() {

        $text    = get_string('helpbuttontext', 'block_archivedsessions', get_config('theme_mentor', 'faq'));
        $helptxt = get_string('helpbuttontexttitle', 'block_archivedsessions');

        $output = '<a class="btn btn-link p-0 help_button" role="button" data-container="body" ' .
                  'data-toggle="popover" data-placement="right" data-content="';
        $output .= '<div class=&quot;no-overflow&quot;><p>' . $text . '</p></div>"';
        $output .= ' data-html="true" tabindex="0" data-trigger="focus" data-original-title="" title="">';
        $output .= '<i class="icon fa fa-question-circle text-info fa-fw " title="' . $helptxt . '" aria-label="' . $helptxt .
                   '"></i>';
        $output .= '</a>';

        return $output;
    }
}
