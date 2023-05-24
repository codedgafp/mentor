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
 * mentor_specialization local caps.
 *
 * @package    local_mentor_specialization
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     Nabil Hamdi <nabil.hamdi@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(

        'local/mentor_specialization:changefullname' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),

        'local/mentor_specialization:changeshortname' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),

        'local/mentor_specialization:changecontent' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),

        'local/mentor_specialization:changecollection' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),

        'local/mentor_specialization:changeidsirh' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),

        'local/mentor_specialization:changeskills' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),

    // Access for session.
        'local/mentor_specialization:changesessionfullname' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),

        'local/mentor_specialization:changesessionshortname' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),

        'local/mentor_specialization:changesessionopento' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),

        'local/mentor_specialization:changesessionpubliccible' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),

        'local/mentor_specialization:changesessiononlinetime' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),

        'local/mentor_specialization:changesessionpresencetime' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),

        'local/mentor_specialization:changesessionpermanentsession' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),

        'local/mentor_specialization:changesessionstartdate' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),

        'local/mentor_specialization:changesessionenddate' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),

        'local/mentor_specialization:changesessionaccompaniment' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),

        'local/mentor_specialization:changesessionsessionmodalities' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),

        'local/mentor_specialization:changesessiontermsregistration' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),

        'local/mentor_specialization:changesessionmaxparticipants' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),

        'local/mentor_specialization:changesessionlocation' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),

        'local/mentor_specialization:changesessionorganizingstructure' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),

        'local/mentor_specialization:changeentityname' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),

        'local/mentor_specialization:changeentityregion' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),

        'local/mentor_specialization:changeentitylogo' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),
        'local/mentor_specialization:changecontactproducerorganization' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),
        'local/mentor_specialization:changeproducerorganizationshortname' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),
        'local/mentor_specialization:changeproducingorganization' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),
        'local/mentor_specialization:changetypicaljob' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),
        'local/mentor_specialization:changeteaser' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),
        'local/mentor_specialization:changedesigners' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),
        'local/mentor_specialization:changecertifying' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),
        'local/mentor_specialization:changelicenseterms' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),
        'local/mentor_specialization:changeprerequisite' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),
        'local/mentor_specialization:changepresenceestimatedtimehours' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),
        'local/mentor_specialization:changeremoteestimatedtimehours' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),
        'local/mentor_specialization:changetrainingmodalities' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),
        'local/mentor_specialization:changeproducerorganizationlogo' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),
        'local/mentor_specialization:changeteaserpicture' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),
        'local/mentor_specialization:changecatchphrase' => array(
                'riskbitmask' => RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(
                        'manager' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/course:update'
        ),
);
