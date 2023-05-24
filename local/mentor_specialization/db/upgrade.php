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
 * Upgrade function called by Moodle
 *
 * @param int $oldversion
 * @return bool
 * @throws coding_exception
 * @throws ddl_exception
 * @throws ddl_table_missing_exception
 * @throws dml_exception
 */
function xmldb_local_mentor_specialization_upgrade($oldversion) {
    global $CFG, $DB;

    require_once($CFG->libdir . '/db/upgradelib.php'); // Core Upgrade-related functions.
    require_once($CFG->dirroot . '/local/profile/lib.php');
    require_once($CFG->dirroot . '/local/mentor_specialization/lib.php');

    $dbman = $DB->get_manager();

    if ($oldversion < 2020112601) {
        // Define table 'favourite' to be created.
        $table = new xmldb_table('regions');

        // Adding fields to table regions.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('code', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table regions.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for regions.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);

            $regions = local_mentor_specialization_get_regions();

            // Insert list of regions to table.
            foreach ($regions as $key => $value) {
                $region = new stdClass();
                $region->code = $key;
                $region->name = $value;

                $region->id = $DB->insert_record('regions', $region);
            }
        }

        // Add profile fields.
        $fields = local_mentor_specialization_get_profile_fields_values();
        foreach ($fields as $value) {
            $field = local_mentor_specialization_create_field_object_to_use($value);
            local_profile_remove_user_info_field_if_exist($field->shortname);
            $field->id = $DB->insert_record('user_info_field', $field);
        }
    }

    if ($oldversion < 2020112602) {
        $regions = local_mentor_specialization_get_regions();

        // Insert list of regions to table.
        foreach ($regions as $key => $value) {
            $region = new stdClass();
            $region->code = $key;
            $region->name = $value;

            if (!$DB->record_exists('regions', array('name' => $region->name))) {
                $DB->insert_record('regions', $region);
            }
        }
    }

    if ($oldversion < 2020122201) {
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
                'presenceestimatedtime' => [XMLDB_TYPE_INTEGER, '10', null, null, null],
                'remoteestimatedtime' => [XMLDB_TYPE_INTEGER, '10', null, null, null],
                'trainingmodalities' => [XMLDB_TYPE_CHAR, '45', null, null, null],
                'producingorganization' => [XMLDB_TYPE_CHAR, '255', null, null, null],
                'producerorganizationlogo' => [XMLDB_TYPE_CHAR, '255', null, null, null],
                'designers' => [XMLDB_TYPE_CHAR, '255', null, null, null],
                'contactproducerorganization' => [XMLDB_TYPE_CHAR, '255', null, null, null],
                'timecreated' => [XMLDB_TYPE_INTEGER, '10', null, null, null],
                'teaserpicture' => [XMLDB_TYPE_CHAR, '255', true, false, false],
        ];

        // Adding fields to database.
        foreach ($trainingfields as $name => $definition) {
            $trainingfield = new xmldb_field($name, $definition[0], $definition[1], $definition[2], $definition[3], $definition[4]);
            if (!$dbman->field_exists($trainingtable, $trainingfield)) {
                $dbman->add_field($trainingtable, $trainingfield);
            }
        }
    }

    if ($oldversion < 2020122900) {
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
                'presenceestimatedtime' => [XMLDB_TYPE_INTEGER, '10', null, null, null],
                'remoteestimatedtime' => [XMLDB_TYPE_INTEGER, '10', null, null, null],
                'trainingmodalities' => [XMLDB_TYPE_CHAR, '45', null, null, null],
                'producingorganization' => [XMLDB_TYPE_CHAR, '255', null, null, null],
                'producerorganizationlogo' => [XMLDB_TYPE_CHAR, '255', null, null, null],
                'contactproducerorganization' => [XMLDB_TYPE_CHAR, '255', null, null, null],
                'thumbnail' => [XMLDB_TYPE_CHAR, '255', null, null, null],
                'teaserpicture' => [XMLDB_TYPE_CHAR, '255', true, false, false],
                'designers' => [XMLDB_TYPE_CHAR, '255', null, null, null],
                'timecreated' => [XMLDB_TYPE_INTEGER, '10', null, null, null],
                'status_training' => [XMLDB_TYPE_CHAR, '45', null, null, null],
                'licenseterms' => [XMLDB_TYPE_CHAR, '255', null, null, null],
                'publiccible' => [XMLDB_TYPE_CHAR, '255', true, false, false],
                'termsregistration' => [XMLDB_TYPE_CHAR, '255', true, false, false],
                'termsregistrationdetail' => [XMLDB_TYPE_CHAR, '255', true, false, false],
                'onlinesessionestimatedtime' => [XMLDB_TYPE_INTEGER, '10', true, false, false],
                'presencesessionestimatedtime' => [XMLDB_TYPE_INTEGER, '10', true, false, false],
                'sessionpermanent' => [XMLDB_TYPE_INTEGER, '1', null, null, null],
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
    }

    if ($oldversion < 2021022400) {
        $sessiontable = new xmldb_table('session');

        $sessionfield = new xmldb_field('termsregistrationdetail', XMLDB_TYPE_TEXT, null, null, null, null);
        $dbman->change_field_type($sessiontable, $sessionfield);
    }

    if ($oldversion < 2021031700) {
        $userfieldid = $DB->get_field('user_info_field', 'id', ['shortname' => 'department']);
        $userdataparmentfieldata = $DB->get_records('user_info_data', ['fieldid' => $userfieldid]);
        $deparmentlist = local_mentor_specialization_get_departments();

        foreach ($userdataparmentfieldata as $userdataparmentfiel) {
            if (array_key_exists($userdataparmentfiel->data, $deparmentlist)) {
                $userdataparmentfiel->data = $deparmentlist[$userdataparmentfiel->data];
                $DB->update_record_raw('user_info_data', $userdataparmentfiel);
            }
        }
    }

    if ($oldversion < 2021042101) {
        $oldcapability = 'local/mentor_specialization:changetraininggoal';
        $olddatabasecapability = $DB->get_record('capabilities', ['name' => $oldcapability]);
        $newcapability = 'local/mentor_core:changetraininggoal';

        if ($olddatabasecapability) {
            if ($DB->record_exists('capabilities', ['name' => $newcapability])) {
                if ($DB->record_exists('capabilities', ['name' => $oldcapability])) {
                    $DB->delete_records('capabilities', ['name' => $oldcapability]);
                }
            } else {
                $olddatabasecapability->name = $newcapability;
                $olddatabasecapability->component = 'local_mentor_core';
                $DB->update_record('capabilities', $olddatabasecapability);
            }
        }

        $DB->delete_records('role_capabilities', ['capability' => $oldcapability]);
    }

    if ($oldversion < 2021042201) {
        $sessiontable = new xmldb_table('session');

        $maxparticipantsfield = new xmldb_field('maxparticipants', XMLDB_TYPE_CHAR, 255, true, false, false);
        $dbman->change_field_type($sessiontable, $maxparticipantsfield);
    }

    if ($oldversion < 2021112600 || $oldversion < 2022010502) {
        $regions = local_mentor_specialization_get_regions();

        // Insert list of regions to table.
        foreach ($regions as $key => $value) {
            $region = new stdClass();
            $region->code = $key;
            $region->name = $value;

            if (!$DB->record_exists('regions', array('name' => $region->name))) {
                $DB->insert_record('regions', $region);
            }
        }

        $userfieldid = $DB->get_field('user_info_field', 'id', ['shortname' => 'department']);
        $userdataparmentfieldata = $DB->get_records('user_info_data', ['fieldid' => $userfieldid]);
        $deparmentlist = local_mentor_specialization_get_departments();

        foreach ($userdataparmentfieldata as $userdataparmentfiel) {
            if (array_key_exists($userdataparmentfiel->data, $deparmentlist)) {
                $userdataparmentfiel->data = $deparmentlist[$userdataparmentfiel->data];
                $DB->update_record_raw('user_info_data', $userdataparmentfiel);
            }
        }
    }

    // Update mail visibility.
    if ($oldversion < 2022011801) {
        $oldusers = $DB->get_records('user', ['maildisplay' => 2]);

        foreach ($oldusers as $olduser) {
            $olduser->maildisplay = 0;
            $DB->update_record('user', $olduser);
        }
    }

    // Remove snippet video.
    if ($oldversion < 2022011900) {
        set_config('snippetcount', 18, 'atto_snippet');

        unset_config('snippetname_19', 'atto_snippet');
        unset_config('snippetkey_19', 'atto_snippet');
        unset_config('snippetinstructions_19', 'atto_snippet');
        unset_config('defaults_19', 'atto_snippet');
        unset_config('snippet_19', 'atto_snippet');
    }

    // Invisible hidden activity course.
    if ($oldversion < 2022021100) {
        $courseformatoptionhiddentsections = $DB->get_records('course_format_options', ['name' => 'hiddensections']);

        foreach ($courseformatoptionhiddentsections as $courseformatoptionhiddentsection) {
            $courseformatoptionhiddentsection->value = 1;
            $DB->update_record('course_format_options', $courseformatoptionhiddentsection);
        }
    }

    if ($oldversion < 2022030100) {

        // Setting new field.
        $field = new xmldb_field('catchphrase', XMLDB_TYPE_CHAR, '255', null, null, null);

        // Add new field to training table.
        $trainingtable = new xmldb_table('training');
        if (!$dbman->field_exists($trainingtable, $field)) {
            $dbman->add_field($trainingtable, $field);
        }

        // Add new field to session table.
        $sessiontable = new xmldb_table('session');
        if (!$dbman->field_exists($sessiontable, $field)) {
            $dbman->add_field($sessiontable, $field);
        }
    }

    if ($oldversion < 2022030101) {

        // Setting new field.
        $field = new xmldb_field('producerorganizationshortname', XMLDB_TYPE_CHAR, '255', null, null, null);

        // Add new field to training table.
        $trainingtable = new xmldb_table('training');
        if (!$dbman->field_exists($trainingtable, $field)) {
            $dbman->add_field($trainingtable, $field);
        }

        // Add new field to session table.
        $sessiontable = new xmldb_table('session');
        if (!$dbman->field_exists($sessiontable, $field)) {
            $dbman->add_field($sessiontable, $field);
        }
    }

    if ($oldversion < 2022030200) {
        // Setting config.
        local_mentor_core_set_moodle_config('externallinks', 'legifrance.gouv.fr|gouvernement.fr|service-public.fr|data.gouv.fr',
                'theme_mentor');
        local_mentor_core_set_moodle_config('about', $CFG->wwwroot . '/local/staticpage/view.php?page=ensavoirplus',
                'theme_mentor');
    }

    if ($oldversion < 2022030301) {
        // Change data to training.
        $alltraining = $DB->get_records('training');
        foreach ($alltraining as $training) {
            if ($training->prerequisite !== get_string('noprerequisite', 'local_trainings')) {
                continue;
            }

            // Change prerequisite data.
            $training->prerequisite = '';
            $DB->update_record('training', $training);
        }
    }

    if ($oldversion < 2022042800) {

        $options = $DB->get_records('category_options');

        $categoryoptionstable = new xmldb_table('category_options');

        // Empty the table.
        $DB->delete_records('category_options');

        // Remove regionid field.
        $regionidfield = new xmldb_field('regionid', XMLDB_TYPE_INTEGER, 10, null, null, null);
        $dbman->drop_field($categoryoptionstable, $regionidfield);

        // Add name field.
        $field = new xmldb_field('name', XMLDB_TYPE_CHAR, '100', null, null, null);
        if (!$dbman->field_exists($categoryoptionstable, $field)) {
            $dbman->add_field($categoryoptionstable, $field);
        }

        // Add value field.
        $field = new xmldb_field('value', XMLDB_TYPE_TEXT, null, null, null, null);
        if (!$dbman->field_exists($categoryoptionstable, $field)) {
            $dbman->add_field($categoryoptionstable, $field);
        }

        // Populate the table.
        foreach ($options as $option) {
            $record = new stdClass();
            $record->categoryid = $option->categoryid;
            $record->name = 'regionid';
            $record->value = $option->regionid;

            $DB->insert_record('category_options', $record);
        }
    }

    if ($oldversion < 2022053100) {

        // Mentor role specified order.
        $roleorder = [
                'admindedie',
                'respformation',
                'referentlocal',
                'reflocalnonediteur',
                'concepteur',
                'formateur',
                'tuteur',
                'participant',
                'participantnonediteur',
                'dpo',
                'coursecreator',
                'editingteacher',
                'teacher',
                'user',
                'frontpage',
                'guest',
        ];

        local_mentor_specialization_set_role_order($roleorder);
    }

    if ($oldversion < 2022060100) {
        // Set session dates into course table.
        $courses = $DB->get_records_sql('
            SELECT
                c.id, s.sessionstartdate as startdate, s.sessionenddate as enddate
            FROM
                {session} s
            JOIN
                {course} c ON s.courseshortname = c.shortname
            WHERE
                s.sessionstartdate != c.startdate
                OR
                s.sessionenddate != c.enddate
        ');

        foreach ($courses as $course) {
            $course->startdate = is_null($course->startdate) ? 0 : $course->startdate;
            $course->enddate = is_null($course->enddate) ? 0 : $course->enddate;
            $DB->update_record('course', $course);
        }
    }

    // Fix empty collection logs.
    if ($oldversion < 2022060101) {
        $tablelogstorementorsession = new xmldb_table('logstore_mentor_session');
        $tablelogstorementorcollection = new xmldb_table('logstore_mentor_collection');

        if ($dbman->table_exists($tablelogstorementorsession) && $dbman->table_exists($tablelogstorementorcollection)) {

            $emptylogs = $DB->get_records_sql("
            SELECT
                lms.id, t.collection
            FROM
                {training} t
            JOIN
                {session} s ON s.trainingid = t.id
            JOIN
                {logstore_mentor_session} lms ON s.id = lms.sessionid
            JOIN
                {logstore_mentor_collection} lmc ON lmc.sessionlogid = lms.id
            WHERE
                lmc.name = ''
        ");

            foreach ($emptylogs as $emptylog) {
                $coll = new stdClass();
                $coll->sessionlogid = $emptylog->id;

                $collections = explode(',', $emptylog->collection);

                $DB->delete_records('logstore_mentor_collection', ['sessionlogid' => $emptylog->id, 'name' => '']);

                foreach ($collections as $collection) {
                    $coll->name = $collection;
                    $DB->insert_record('logstore_mentor_collection', $coll);
                }
            }
        }
    }

    // Update email to send course welcome message to self enrol.
    if ($oldversion < 2022061501) {
        try {
            $DB->execute("
            UPDATE {enrol}
            SET customint4 = 3
            WHERE enrol = 'self'
        ");
        } catch (\dml_exception $e) {
            mtrace('ERROR : Update email to send course welcome message to self enrol!!!');
        }
    }

    // Update category names encoding.
    if ($oldversion < 2022082400) {
        $categories = $DB->get_records('course_categories');
        foreach ($categories as $category) {
            $category->name = str_replace('&#39;', "'", $category->name);
            $category->idnumber = str_replace('&#39;', "'", $category->idnumber);

            $DB->update_record('course_categories', $category);
        }
    }

    // Add cohort enrol to all main entity contat page.
    if ($oldversion < 2022114001) {
        local_mentor_core_set_enrol_plugins_enabled();

        $allentity = \local_mentor_core\entity_api::get_all_entities(true, [], true);
        foreach ($allentity as $entity) {
            $entity->create_cohort_enrol_page_contact();
        }
    }

    // Check access to the library.
    if ($oldversion < 2022121600) {
        local_mentor_specialization_check_access_to_the_library_for_all_users();
    }

    // Check if 'Custom welcome message' to self enrol exist.
    // Else no send course welcome message.
    if ($oldversion < 2022122000) {
        $selfenrols = $DB->get_records('enrol', array('enrol' => 'self'));
        foreach ($selfenrols as $selfenrol) {
            if (empty($selfenrol->customtext1) || is_null($selfenrol->customtext1)) {
                $selfenrol->customint4 = 0;
                $DB->update_record('enrol', $selfenrol);
            }
        }
    }

    local_mentor_specialization_init_config();

    return true;
}
