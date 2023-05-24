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
 * Upgrade file
 *
 * @package    local_mentor_specialization
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     Nabil Hamdi <nabil.hamdi@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Database install
 *
 * @return bool
 * @throws coding_exception
 * @throws ddl_exception
 * @throws ddl_table_missing_exception
 * @throws dml_exception
 */
function xmldb_local_mentor_specialization_install() {
    global $CFG, $DB;

    require_once($CFG->libdir . '/db/upgradelib.php'); // Core Upgrade-related functions.
    require_once($CFG->dirroot . '/local/profile/lib.php');
    require_once($CFG->dirroot . '/local/mentor_specialization/lib.php');

    $dbman = $DB->get_manager();

    // Training table.
    $trainingtable = new xmldb_table('training');

    // Training table fields.
    $trainingfields = [
            'teaser' => [XMLDB_TYPE_CHAR, '255', true, false, false],
            'prerequisite' => [XMLDB_TYPE_CHAR, '255', true, false, false],
            'collection' => [XMLDB_TYPE_CHAR, '455', true, false, false],
            'creativestructure' => [XMLDB_TYPE_CHAR, '255', true, false, false],
            'idsirh' => [XMLDB_TYPE_CHAR, '45', null, null, null],
            'licenseterms' => [XMLDB_TYPE_CHAR, '255', null, null, null],
            'typicaljob' => [XMLDB_TYPE_CHAR, '255', null, null, null],
            'skills' => [XMLDB_TYPE_CHAR, '255', null, null, null],
            'certifying' => [XMLDB_TYPE_INTEGER, '1', null, null, null],
            'catchphrase' => [XMLDB_TYPE_CHAR, '255', null, null, null],
            'presenceestimatedtime' => [XMLDB_TYPE_INTEGER, '10', null, null, null],
            'remoteestimatedtime' => [XMLDB_TYPE_INTEGER, '10', null, null, null],
            'trainingmodalities' => [XMLDB_TYPE_CHAR, '45', null, null, null],
            'producingorganization' => [XMLDB_TYPE_CHAR, '255', null, null, null],
            'producerorganizationlogo' => [XMLDB_TYPE_CHAR, '255', null, null, null],
            'designers' => [XMLDB_TYPE_CHAR, '255', null, null, null],
            'contactproducerorganization' => [XMLDB_TYPE_CHAR, '255', null, null, null],
            'producerorganizationshortname' => [XMLDB_TYPE_CHAR, '255', null, null, null],
            'timecreated' => [XMLDB_TYPE_INTEGER, '10', null, null, null],
            'status' => [XMLDB_TYPE_CHAR, '45', null, null, null],
            'teaserpicture' => [XMLDB_TYPE_CHAR, '255', true, false, false],
    ];

    // Adding fields to database.
    foreach ($trainingfields as $name => $definition) {

        $trainingfield = new xmldb_field($name, $definition[0], $definition[1], $definition[2], $definition[3], $definition[4]);

        if (!$dbman->field_exists($trainingtable, $trainingfield)) {
            $dbman->add_field($trainingtable, $trainingfield);
        }
    }

    // Session table.
    $sessiontable = new xmldb_table('session');

    // Session table fields.
    $sessionfields = [
            'trainingname' => [XMLDB_TYPE_CHAR, '255', true, false, false],
            'trainingshortname' => [XMLDB_TYPE_CHAR, '255', true, false, false],
            'trainingcontent' => [XMLDB_TYPE_TEXT, null, null, null, null],
            'teaser' => [XMLDB_TYPE_CHAR, '255', true, false, false],
            'prerequisite' => [XMLDB_TYPE_CHAR, '255', true, false, false],
            'collection' => [XMLDB_TYPE_CHAR, '455', true, false, false],
            'creativestructure' => [XMLDB_TYPE_CHAR, '255', true, false, false],
            'traininggoal' => [XMLDB_TYPE_TEXT, '255', null, null, null],
            'idsirh' => [XMLDB_TYPE_CHAR, '45', null, null, null],
            'typicaljob' => [XMLDB_TYPE_CHAR, '255', null, null, null],
            'skills' => [XMLDB_TYPE_CHAR, '255', null, null, null],
            'certifying' => [XMLDB_TYPE_INTEGER, '1', null, null, null],
            'catchphrase' => [XMLDB_TYPE_CHAR, '255', null, null, null],
            'presenceestimatedtime' => [XMLDB_TYPE_INTEGER, '10', null, null, null],
            'remoteestimatedtime' => [XMLDB_TYPE_INTEGER, '10', null, null, null],
            'trainingmodalities' => [XMLDB_TYPE_CHAR, '45', null, null, null],
            'producingorganization' => [XMLDB_TYPE_CHAR, '255', null, null, null],
            'producerorganizationlogo' => [XMLDB_TYPE_CHAR, '255', null, null, null],
            'contactproducerorganization' => [XMLDB_TYPE_CHAR, '255', null, null, null],
            'producerorganizationshortname' => [XMLDB_TYPE_CHAR, '255', null, null, null],
            'thumbnail' => [XMLDB_TYPE_CHAR, '255', null, null, null],
            'teaserpicture' => [XMLDB_TYPE_CHAR, '255', true, false, false],
            'designers' => [XMLDB_TYPE_CHAR, '255', null, null, null],
            'timecreated' => [XMLDB_TYPE_INTEGER, '10', null, null, null],
            'status_training' => [XMLDB_TYPE_CHAR, '45', null, null, null],
            'licenseterms' => [XMLDB_TYPE_CHAR, '255', null, null, null],
            'status' => [XMLDB_TYPE_INTEGER, '10', null, null, null],
            'publiccible' => [XMLDB_TYPE_CHAR, '255', true, false, false],
            'termsregistration' => [XMLDB_TYPE_CHAR, '255', true, false, false],
            'termsregistrationdetail' => [XMLDB_TYPE_TEXT, null, true, false, false],
            'onlinesessionestimatedtime' => [XMLDB_TYPE_INTEGER, '10', true, false, false],
            'presencesessionestimatedtime' => [XMLDB_TYPE_INTEGER, '10', true, false, false],
            'sessionpermanent' => [XMLDB_TYPE_INTEGER, '1', null, null, null],
            'sessionstartdate' => [XMLDB_TYPE_INTEGER, '10', null, null, null],
            'sessionenddate' => [XMLDB_TYPE_INTEGER, '10', null, null, null],
            'sessionmodalities' => [XMLDB_TYPE_CHAR, '255', true, false, false],
            'accompaniment' => [XMLDB_TYPE_CHAR, '255', true, false, false],
            'placesavailable' => [XMLDB_TYPE_CHAR, '255', true, false, false],
            'numberparticipants' => [XMLDB_TYPE_CHAR, '255', true, false, false],
            'location' => [XMLDB_TYPE_CHAR, '255', true, false, false],
            'organizingstructure' => [XMLDB_TYPE_CHAR, '255', true, false, false],
            'sessionnumber' => [XMLDB_TYPE_INTEGER, '10', null, null, null],
    ];

    // Adding fields to database.
    foreach ($sessionfields as $name => $definition) {

        $sessionfield = new xmldb_field($name, $definition[0], $definition[1], $definition[2], $definition[3], $definition[4]);

        if (!$dbman->field_exists($sessiontable, $sessionfield)) {
            $dbman->add_field($sessiontable, $sessionfield);
        }
    }

    local_mentor_specialization_init_config();

    return true;
}
