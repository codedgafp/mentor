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
 * admin renderer
 *
 * @package    local
 * @subpackage entities
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_entities\output;

use lang_string;
use local_mentor_core\entity_api;
use moodle_page;

class admin_renderer extends \plugin_renderer_base {

    /**
     * Render the entities list
     *
     * @return string
     * @throws \moodle_exception
     */
    public function display() {

        // Add strings for JS.
        $this->page->requires->strings_for_js(array(
            'add',
        ), 'moodle');

        $this->page->requires->strings_for_js(array(
            'pleaserefresh',
            'save',
            'cancel'
        ), 'format_edadmin');

        $this->page->requires->strings_for_js(array(
            'requiredfields',
            'entitynamelimit'
        ), 'local_mentor_core');

        $this->page->requires->strings_for_js(array(
            'pluginname',
            'spacename',
            'responsiblename',
            'errorentityexisttitle',
            'errorentityexist',
            'member',
            'members',
            'langfile',
            'addentity',
            'addsubentity',
            'viewroles'
        ), 'local_entities');

        $listtypename = \format_edadmin::get_all_type_name();

        // Puts the course of the entity's parameters at the end.
        $movetypeid = array_search('entities', $listtypename);
        if ($movetypeid) {
            $movetype = $listtypename[$movetypeid];
            unset($listtypename[$movetypeid]);
            array_push($listtypename, $movetype);
        }

        // Init and call JS.
        $params               = new \stdClass();
        $params->listtypename = array_values($listtypename);
        $params->isadmin      = is_siteadmin();

        // Get the right JS to use and call it.
        $js = entity_api::get_entity_javascript('local_entities/local_entities');
        $this->page->requires->js_call_amd($js, 'init', array('params' => $params));

        // Convert list type name to list lang string type name.
        $stringlisttypename = array_map(function($typename) {
            return new lang_string('edadmin' . $typename . 'coursetype', 'local_' . $typename);
        }, $listtypename);

        $options = (object) array(
            'listtypename' => array_values($stringlisttypename)
        );

        // Add html code of the entity form to be displayed in a popup.
        $render = $this->render_from_template('local_entities/admin_entities', $options);
        $render .= entity_api::get_new_entity_form();
        $render .= entity_api::get_new_sub_entity_form();
        return $render;
    }
}
