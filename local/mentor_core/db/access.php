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
 * trainings local caps.
 *
 * @package    local_trainings
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     Nabil Hamdi <nabil.hamdi@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(

    'local/mentor_core:changefullname' => array(
        'riskbitmask' => RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
        ),
        'clonepermissionsfrom' => 'moodle/course:update'
    ),

    'local/mentor_core:changeshortname' => array(
        'riskbitmask' => RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
        ),
        'clonepermissionsfrom' => 'moodle/course:update'
    ),

    'local/mentor_core:changecontent' => array(
        'riskbitmask' => RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
        ),
        'clonepermissionsfrom' => 'moodle/course:update'
    ),
    'local/mentor_core:changesessionfullname' => array(
        'riskbitmask' => RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
        ),
        'clonepermissionsfrom' => 'moodle/course:update'
    ),

    'local/mentor_core:changesessionshortname' => array(
        'riskbitmask' => RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
        ),
        'clonepermissionsfrom' => 'moodle/course:update'
    ),

    'local/mentor_core:changesessionopentoexternal' => array(
        'riskbitmask' => RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSECAT,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
        ),
        'clonepermissionsfrom' => 'moodle/course:update'
    ),

    'local/mentor_core:sharetrainings' => array(
        'riskbitmask' => RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSECAT,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),

        'clonepermissionsfrom' => 'moodle/category:manage'
    ),

    'local/mentor_core:sharetrainingssubentities' => array(
        'riskbitmask' => RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSECAT,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),

        'clonepermissionsfrom' => 'moodle/category:manage'
    ),

    'local/mentor_core:movetrainings' => array(
        'riskbitmask' => RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSECAT,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),

        'clonepermissionsfrom' => 'moodle/category:manage'
    ),

    'local/mentor_core:movetrainingsinotherentities' => array(
        'riskbitmask' => RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSECAT,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),

        'clonepermissionsfrom' => 'moodle/category:manage'
    ),

    'local/mentor_core:movesessions' => array(
        'riskbitmask' => RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSECAT,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),

        'clonepermissionsfrom' => 'moodle/category:manage'
    ),

    'local/mentor_core:movesessionsinotherentities' => array(
        'riskbitmask' => RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSECAT,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),

        'clonepermissionsfrom' => 'moodle/category:manage'
    ),

    'local/mentor_core:importusers' => array(
        'riskbitmask' => RISK_SPAM,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
    ),

    'local/mentor_core:suspendusers' => array(
        'riskbitmask' => RISK_SPAM,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSECAT,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
    ),

    'local/mentor_core:duplicatesessionintotraining' => array(
        'riskbitmask' => RISK_SPAM,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
    ),

    'local/mentor_core:changetraininggoal' => array(
        'riskbitmask' => RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
        ),
        'clonepermissionsfrom' => 'moodle/course:update'
    ),

    'local/mentor_core:changetrainingstatus' => array(
        'riskbitmask' => RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
        ),
        'clonepermissionsfrom' => 'moodle/course:update'
    ),
    'local/mentor_core:changethumbnail' => array(
        'riskbitmask' => RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
        ),
        'clonepermissionsfrom' => 'moodle/course:update'
    ),
);
