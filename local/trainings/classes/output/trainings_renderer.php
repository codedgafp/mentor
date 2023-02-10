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
 * Trainings renderer
 *
 * @package    local_user
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     adrien <adrien.jamot@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_trainings\output;

use local_mentor_core\catalog_api;
use local_mentor_core\entity_api;
use local_mentor_core\training_api;

require_once(__DIR__ . '/../../../../config.php');
require_once($CFG->dirroot . '/course/format/edadmin/classes/output/interface_renderer.php');
require_once($CFG->dirroot . '/local/mentor_core/api/training.php');

require_login();

class trainings_renderer extends \plugin_renderer_base implements \format_edadmin\output\edadmin_renderer {

    protected $dbinterface;

    /**
     * First enter to render
     *
     * @param $course
     * @param $section
     * @return string
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function display($course, $section) {
        global $CFG;

        // Init var.
        $entityid = $course->category;

        $currententity      = entity_api::get_entity($entityid);
        $trainingcategoryid = $currententity->get_entity_formation_category();
        $context            = $currententity->get_context();

        // Load language strings for JavaScript.
        $this->page->requires->strings_for_js(
            array(
                'langfile',
                'draft',
                'template',
                'elaboration_completed',
                'archived',
                'assignuserstooltip',
                'duplicatetrainingtooltip',
                'move',
                'movetrainingtooltip',
                'trainingsheettooltip',
                'createsessionstooltip',
                'deletetrainingtooltip',
                'removetrainingdialogtitle',
                'removetrainingdialogcontent',
                'duplicatetrainingdialogtitle',
                'movetrainingdialogtitle',
                'duplicatetrainingdialogcontent',
                'createsessiondialogtitle',
                'createsessiondialogcontent',
                'pleasewait',
                'createtoaddhoc',
                'sessionanmeused',
                'creattrainingpopuptitle'
            ),
            'local_trainings'
        );

        $this->page->requires->strings_for_js(
            array(
                'cancel',
                'confirm',
                'tocreate',
            ),
            'format_edadmin'
        );

        $this->page->requires->strings_for_js([
            'choose'
        ],
            'local_mentor_core'
        );

        // Initialize params to JS and template.
        $params           = new \stdClass();
        $params->entityid = $entityid;

        // Get subentities.
        $subentities            = $currententity->get_sub_entities();
        $params->hassubentities = count($subentities) > 0;
        $params->subentities    = [];

        $cancreate = false;

        // Add the main entity into the selector.
        if (has_capability('local/trainings:create', $context)) {
            $subentitystd          = new \stdClass();
            $subentitystd->id      = $currententity->id;
            $subentitystd->name    = $currententity->get_entity_path();
            $params->subentities[] = $subentitystd;
            $cancreate             = true;
        }

        // Add the sub entities into the selector.
        foreach ($subentities as $subentity) {
            if (has_capability('local/trainings:create', $subentity->get_context())) {
                $subentitystd          = new \stdClass();
                $subentitystd->id      = $subentity->id;
                $subentitystd->name    = $subentity->name;
                $params->subentities[] = $subentitystd;
                $cancreate             = true;
            }
        }

        // Check if the user can create trainings.
        if ($cancreate) {
            $params->addtraininglink = $CFG->wwwroot . '/local/trainings/pages/add_training.php';
        }

        // Check if the user can view the category recycle bin.
        $trainingcategorycontext = \context_coursecat::instance($trainingcategoryid);

        if (has_capability('tool/recyclebin:viewitems', $trainingcategorycontext)) {
            $params->recyclebinlink = new \moodle_url('/local/trainings/pages/recyclebin_trainings.php', array(
                'entityid' =>
                    $currententity->id
            ));
        }

        // Get the right JS to use and call it.
        $js = training_api::get_trainings_javascript('local_trainings/local_trainings');
        $this->page->requires->js_call_amd($js, 'init', array($params));

        // Get the right training table template.
        $template = training_api::get_trainings_template('local_trainings/local_trainings');

        $params->entities          = [];
        $params->entitiesduplicate = [];
        $params->subentities       = [];
        $params->subentitiesfilter = [];
        $params->moveentities      = [];

        $params->canmove = false;

        // Move trainings.
        if (has_capability('local/mentor_core:movetrainings', $context)) {

            // Add current entity if user can manage it.
            if (has_capability('local/trainings:create', $currententity->get_context())) {
                $ent                    = new \stdClass();
                $ent->id                = $currententity->id;
                $ent->name              = $currententity->shortname;
                $params->moveentities[] = $ent;
            }

            // Check in subentities.
            foreach ($subentities as $subentity) {

                if (!has_capability('local/trainings:create', $subentity->get_context())) {
                    continue;
                }

                $ent              = new \stdClass();
                $ent->id          = $subentity->id;
                $ent->name        = $subentity->name;
                $ent->issubentity = true;

                $params->moveentities[] = $ent;
                $params->canmove        = true;
            }
        }

        // Set shareable entities.
        if (has_capability('local/mentor_core:sharetrainings', $context)) {
            $params->cansharetrainings = true;

            // Main admin can see hidden entities.
            $includehidden = is_siteadmin();

            $entities = entity_api::get_all_entities(true, [], false, null, $includehidden);

            $params->entities = [];

            foreach ($entities as $entity) {
                $ent       = new \stdClass();
                $ent->id   = $entity->id;
                $ent->name = $entity->shortname;

                // Select the current entity.
                if ($entity->id == $currententity->id) {
                    $ent->sel = 1;
                }

                $params->entities[]          = $ent;
                $params->entitiesduplicate[] = $ent;
            }
        }

        $entnone                     = new \stdClass();
        $entnone->id                 = $currententity->id;
        $entnone->name               = get_string('none', 'local_trainings');
        $params->subentitiesfilter[] = $entnone;

        if ($params->hassubentities && has_capability('local/mentor_core:sharetrainingssubentities', $context)) {
            $params->cansharetrainings = true;

            // Add the parent entity.
            if (!has_capability('local/mentor_core:sharetrainings', $context)) {
                $ent                = new \stdClass();
                $ent->id            = $currententity->id;
                $ent->name          = $currententity->shortname;
                $params->entities[] = $ent;
            }

            // Set an empty option to create the training into the main entity.
            if (has_capability('local/trainings:create', $context)) {
                $params->subentities[] = $entnone;
            }

            // Add subentities.
            foreach ($subentities as $subentity) {

                if (!$subentity->is_trainings_manager()) {
                    continue;
                }

                $ent              = new \stdClass();
                $ent->id          = $subentity->id;
                $ent->name        = $subentity->name;
                $ent->issubentity = true;

                $params->subentitiesfilter[] = $ent;
                $params->subentities[]       = $ent;
                $params->canmove             = true;
            }

            if (count($params->subentities) === 1) {
                $params->subentity = array_shift($params->subentities)->id;
            }

        }

        // Add other entities in move training action.
        if (has_capability('local/mentor_core:movetrainingsinotherentities', $context) && count($params->entities) > 1) {
            $params->moveentities = array_merge($params->moveentities,
                array_filter($params->entitiesduplicate, function($entityobject) use ($currententity) {
                    if ($entityobject->id !== $currententity->id) {
                        $entity = \local_mentor_core\entity_api::get_entity($entityobject->id);
                        return $entity->is_trainings_manager();
                    }
                    return false;
                }));

            $params->canmove = true;
        }

        if (count($params->entitiesduplicate) > 1) {
            $params->hasmultipleentitiesduplicate = true;
        }

        $params->hassubentitiestocreatesession = count($params->subentities) > 0;

        // Specialization.
        $params = training_api::get_training_template_params($params);

        // Return template renderer.
        return $this->render_from_template($template, $params);

    }
}
