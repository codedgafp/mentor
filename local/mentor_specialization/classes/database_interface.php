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
 * Database Interface
 *
 * @package    local_mentor_specialization
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     adrien jamot <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mentor_specialization;

use core\notification;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/mentor_core/classes/database_interface.php');
require_once($CFG->dirroot . '/local/mentor_specialization/lib.php');

class database_interface extends \local_mentor_core\database_interface {

    private $skills;

    /**
     * Create a singleton of the class
     *
     * @return database_interface
     */
    public static function get_instance() {

        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get category options from category_options table
     *
     * @param int $categoryid
     * @return \stdClass[]
     * @throws \dml_exception
     */
    public function get_category_options($categoryid) {
        return $this->db->get_records('category_options', array('categoryid' => $categoryid));
    }

    /**
     * Get category option from category_options table
     *
     * @param int $categoryid
     * @param string $optionname
     * @return \stdClass
     * @throws \dml_exception
     */
    public function get_category_option($categoryid, $optionname) {
        return $this->db->get_record('category_options', array('categoryid' => $categoryid, 'name' => $optionname));
    }

    /**
     * Update the entity regions
     *
     * @param int $entityid
     * @param int|array $regionsid
     * @return bool
     * @throws \dml_exception
     */
    public function update_entity_regions($entityid, $regionsid) {

        if (is_array($regionsid)) {
            $regionsid = implode(',', $regionsid);
        }

        // Update category options.
        if ($categoryoptions = $this->get_category_option($entityid, 'regionid')) {
            $categoryoptions->value = $regionsid;
            $this->db->update_record('category_options', $categoryoptions);
        } else {
            // Create category options.
            $categoryoptions = new \stdClass();
            $categoryoptions->categoryid = $entityid;
            $categoryoptions->name = 'regionid';
            $categoryoptions->value = $regionsid;
            $this->db->insert_record('category_options', $categoryoptions);
        }
        return true;
    }

    /**
     * Update the visibility of an entity
     *
     * @param int $entityid
     * @param int $hidden 1 for an hidden entity
     * @return bool
     * @throws \dml_exception
     */
    public function update_entity_visibility($entityid, $hidden) {

        if ($categoryoptions = $this->db->get_record('category_options', ['categoryid' => $entityid, 'name' => 'hidden'])) {
            // Update visibility.
            $categoryoptions->value = $hidden;
            $this->db->update_record('category_options', $categoryoptions);
        } else {
            // Create category options.
            $categoryoptions = new \stdClass();
            $categoryoptions->categoryid = $entityid;
            $categoryoptions->name = 'hidden';
            $categoryoptions->value = $hidden;
            $this->db->insert_record('category_options', $categoryoptions);
        }
        return true;
    }

    /**
     * Update entity sirh list
     *
     * @param int $entityid
     * @param string|array $sirhlist
     * @return bool
     * @throws \dml_exception
     */
    public function update_entity_sirh_list($entityid, $sirhlist) {

        if (is_array($sirhlist)) {
            $sirhlist = implode(',', $sirhlist);
        }

        // Update category options.
        if ($categoryoptions = $this->get_category_option($entityid, 'sirhlist')) {
            $categoryoptions->value = $sirhlist;
            $this->db->update_record('category_options', $categoryoptions);
        } else {
            // Create category options.
            $categoryoptions = new \stdClass();
            $categoryoptions->categoryid = $entityid;
            $categoryoptions->name = 'sirhlist';
            $categoryoptions->value = $sirhlist;
            $this->db->insert_record('category_options', $categoryoptions);
        }
        return true;
    }

    /**
     * Update entity sirh list
     *
     * @param int $entityid
     * @param string $canbemainentity
     * @throws \dml_exception
     */
    public function update_can_be_main_entity($entityid, $canbemainentity) {

        // Update category options.
        if ($categoryoptions = $this->get_category_option($entityid, 'canbemainentity')) {
            $categoryoptions->value = $canbemainentity;
            $this->db->update_record('category_options', $categoryoptions);
        } else {
            // Create category options.
            $categoryoptions = new \stdClass();
            $categoryoptions->categoryid = $entityid;
            $categoryoptions->name = 'canbemainentity';
            $categoryoptions->value = $canbemainentity;
            $this->db->insert_record('category_options', $categoryoptions);
        }
    }

    /**
     * Get cohort by region
     *
     * @param int $regionid
     * @return \stdClass[]
     * @throws \dml_exception
     */
    public function get_cohorts_by_region($regionid) {
        global $CFG;

        // Find id in regions list.
        if ($CFG->dbtype == 'pgsql') {
            $compare = "'" . $regionid . "' = ANY (string_to_array(uo.value,','))";
        } else {
            $compare = "find_in_set('" . $regionid . "',uo.value) <> 0";
        }

        return $this->db->get_records_sql("
            SELECT co.id
            FROM {category_options} uo
            JOIN {course_categories} cca ON cca.id = uo.categoryid
            JOIN {context} cnt ON cnt.instanceid = cca.id
            JOIN {cohort} co ON co.contextid = cnt.id
            WHERE
                uo.name = :name
                AND
                " . $compare
                , array(
                        'name' => 'regionid',
                ));
    }

    /**
     * Get all regions sorted by name ASC
     *
     * @return \stdClass[]
     * @throws \dml_exception
     */
    public function get_all_regions() {
        return $this->db->get_records('regions', null, 'name ASC');
    }

    /**
     * Get all users by regions id
     *
     * @param array $regionsid
     * @return \stdClass[]
     * @throws \dml_exception
     */
    public function get_users_by_regions($regionsid) {

        // Region id is empty ?
        if (empty($regionsid)) {
            return [];
        }

        $or = '(';

        $params = ['fieldname' => 'region'];

        $i = 1;
        foreach ($regionsid as $regionid) {
            $or .= 'r.id = :regionid' . $i . ' OR ';

            $params['regionid' . $i] = $regionid;
            $i++;
        }

        $or = substr($or, 0, -4);

        $or .= ')';

        return $this->db->get_records_sql('
            SELECT u.*
            FROM {user} u
            JOIN {user_info_data} uid ON u.id = uid.userid
            JOIN {user_info_field} uif ON uif.id = uid.fieldid
            JOIN {regions} r ON r.name = uid.data
            WHERE
                uif.shortname = :fieldname
                AND
                ' . $or
                , $params);
    }

    /**
     * Get all skills of the platform
     *
     * @return array idnumber => shortname
     * @throws \dml_exception
     */
    public function get_skills() {

        // Load all competencies from database.
        if (empty($this->skills)) {
            // Get all competencies without domain.
            $competencies = $this->db->get_records_sql('
                SELECT *
                FROM {competency} c
                WHERE c.parentid != 0
                ORDER BY c.parentid, c.sortorder
            ');

            $this->skills = [];

            // Index competencies by idnumber.
            foreach ($competencies as $competency) {
                $this->skills[$competency->idnumber] = $competency->shortname;
            }
        }

        return $this->skills;
    }

    /***************************** SESSIONS ******************************/

    /**
     * Get all sessions by entity id
     *
     * @param \stdClass $data
     * @return \stdClass[]
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_sessions_by_entity_id($data) {

        // Get the session data + the number of participants of the session.
        $request = 'SELECT DISTINCT s.id, (
                        SELECT count(DISTINCT(ra.userid))
                        FROM {role_assignments} ra
                        JOIN {role} r ON ra.roleid = r.id
                        WHERE (ra.contextid = con.id OR ra.contextid = con2.id)
                        AND (r.shortname = :participant OR r.shortname = :participantnonediteur)
                    ) as numberparticipants,
                    s.maxparticipants,
                    co.fullname,
                    co.shortname,
                    co.id as courseid,
                    s.status,
                    s.sessionstartdate,
                    s.sessionnumber,
                    cc4.name
                FROM {session} s
                JOIN {training} t ON s.trainingid = t.id
                JOIN {course} co ON co.shortname = s.courseshortname
                JOIN {course} co2 ON co2.shortname = t.courseshortname
                JOIN {course_categories} cc ON cc.id = co.category
                JOIN {context} con ON con.instanceid = co.id
                JOIN {course} co3 ON co3.shortname = s.courseshortname
                LEFT JOIN {course_categories} cc3 ON cc3.id = co3.category
                LEFT JOIN {course_categories} cc4 ON cc4.id = cc3.parent
                LEFT JOIN {course_categories} cc5 ON cc5.id = cc4.parent
                JOIN {context} con2 ON con2.instanceid = co3.id
                WHERE
                    (cc.parent = :entityid OR cc5.parent = :entityid2)
                    AND (con.contextlevel = :contextlevel OR con2.contextlevel = :contextlevel2)';

        $params = array(
                'participant' => 'participant',
                'participantnonediteur' => 'participantnonediteur',
                'entityid' => $data->entityid,
                'entityid2' => $data->entityid,
                'contextlevel' => CONTEXT_COURSE,
                'contextlevel2' => CONTEXT_COURSE
        );

        // Filters.
        $request .= $this->generate_sessions_by_entity_id_filter($data, $params);

        // Generate research part request.
        $request .= $this->generate_sessions_by_entity_id_search($data, $params);

        $request .= ' GROUP BY
            s.id,
            cc4.name,
            s.sessionnumber,
            con.id,
            con2.id,
            co.fullname,
            co.shortname,
            co.id,
            s.sessionstartdate';

        // Sort order.
        if ($data->order) {
            switch ($data->order['column']) {
                case 0:
                    $request .= " ORDER BY cc4.name " . $data->order['dir'];
                    break;
                case 1:
                    $request .= " ORDER BY t.fullname " . $data->order['dir'];
                    break;
                case 2:
                    $request .= " ORDER BY co.fullname " . $data->order['dir'];
                    break;
                case 3:
                    $request .= " ORDER BY co.shortname " . $data->order['dir'];
                    break;
                case 4:
                    $request .= " ORDER BY s.sessionnumber " . $data->order['dir'];
                    break;
                case 5:
                    $request .= " ORDER BY s.sessionstartdate " . $data->order['dir'];
                    break;
                case 6:
                    $request .= " ORDER BY 2 " . $data->order['dir'];
                    break;
                default:
                    break;
            }
        }

        return $this->db->get_records_sql(
                $request,
                $params,
                $data->start,
                $data->length
        );
    }

    /**
     * Count session by entity id
     *
     * @param \stdClass $data
     * @return int
     * @throws \dml_exception
     */
    public function count_sessions_by_entity_id($data) {

        // Get the session data + the number of participants of the session.
        $request = '
                SELECT count(DISTINCT(s.id))
                FROM {session} s
                JOIN {training} t ON s.trainingid = t.id
                JOIN {course} co ON co.shortname = s.courseshortname
                JOIN {course} co2 ON co2.shortname = t.courseshortname
                JOIN {course_categories} cc ON cc.id = co.category
                JOIN {context} con ON con.instanceid = co.id
                JOIN {course} co3 ON co3.shortname = s.courseshortname
                LEFT JOIN {course_categories} cc3 ON cc3.id = co3.category
                LEFT JOIN {course_categories} cc4 ON cc4.id = cc3.parent
                LEFT JOIN {course_categories} cc5 ON cc5.id = cc4.parent
                JOIN {context} con2 ON con2.instanceid = co3.id
                WHERE
                    (cc.parent = :entityid OR cc5.parent = :entityid2)
                    AND (con.contextlevel = :contextlevel OR con2.contextlevel = :contextlevel2)';

        $params = array(
                'participant' => 'participant',
                'participantnonediteur' => 'participantnonediteur',
                'entityid' => $data->entityid,
                'entityid2' => $data->entityid,
                'contextlevel' => CONTEXT_COURSE,
                'contextlevel2' => CONTEXT_COURSE
        );

        // Filters.
        $request .= $this->generate_sessions_by_entity_id_filter($data, $params);

        // Generate research part request.
        $request .= $this->generate_sessions_by_entity_id_search($data, $params);

        return $this->db->count_records_sql(
                $request,
                $params
        );
    }

    /**
     * Generate sessions by entity id filter part request.
     *
     * @param \stdClass $data
     * @param array $params
     * @return string
     */
    public function generate_sessions_by_entity_id_filter($data, &$params) {
        $request = '';

        // Filters.
        if (isset($data->filters) && !empty($data->filters)) {
            // Sub-entities filter.
            if (isset($data->filters['subentity']) && !empty($data->filters['subentity'])) {
                $request .= ' AND (';
                foreach ($data->filters['subentity'] as $key => $subentityid) {
                    if ($key) {
                        $request .= ' OR ';
                    }
                    $request .= ' cc4.id = :subentityid' . $key;
                    $params['subentityid' . $key] = $subentityid;
                }
                $request .= ' ) ';
            }

            // Collections filter.
            if (isset($data->filters['collection']) && !empty($data->filters['collection'])) {
                $request .= ' AND (';
                foreach ($data->filters['collection'] as $key => $collection) {
                    if ($key) {
                        $request .= ' OR ';
                    }
                    $request .= $this->db->sql_like('t.collection', ':collection' . $key, false, false);
                    $params['collection' . $key] = '%' . $this->db->sql_like_escape($collection) . '%';
                }
                $request .= ' ) ';
            }

            // Status filter.
            if (isset($data->filters['status']) && !empty($data->filters['status'])) {
                $request .= ' AND (';
                foreach ($data->filters['status'] as $key => $status) {
                    if ($key) {
                        $request .= ' OR ';
                    }
                    $request .= ' s.status = :status' . $key;
                    $params['status' . $key] = $status;
                }
                $request .= ' ) ';
            }

            // Date filter.
            if (isset($data->filters['startdate']) && !empty($data->filters['startdate'])) {
                $request .= ' AND s.sessionstartdate > :startdate ';
                $params['startdate'] = $data->filters['startdate'];
            }
            if (isset($data->filters['enddate']) && !empty($data->filters['enddate'])) {
                $request .= ' AND s.sessionstartdate < :enddate ';
                $params['enddate'] = $data->filters['enddate'];
            }
        }

        return $request;
    }

    /**
     * Generate sessions by entity id search part request.
     *
     * @param \stdClass $data
     * @param array $params
     * @return string
     * @throws \coding_exception
     */
    public function generate_sessions_by_entity_id_search($data, &$params) {
        $request = '';

        // Generate research part request.
        if ($data->search && $data->search['value'] && mb_strlen(trim($data->search['value'])) > 1) {
            // Condition is closed after status and collection checks.
            $request .= ' AND ( ';

            // Do not execute strict search by default.
            $strictsearch = false;

            // Search exact value if '"' have been submitted.
            if (mb_substr($data->search['value'], -5) === '&#34;'
                && mb_substr($data->search['value'], 0, 5) === '&#34;'
                && mb_strlen(trim($data->search['value'])) > 11) {

                // Activate strict search.
                $strictsearch = true;

                // Get real search value.
                $searchvalue = trim(mb_substr($data->search['value'], 5, -5));
                $searchvalue = str_replace("&#39;", "'", $searchvalue);

                // Get part of query with params.
                list($querypartsql, $querypartparams) = $this->generate_session_sql_search_exact_expression(trim($searchvalue));

                // Assign response.
                $params = array_merge($params, $querypartparams);
                $request .= $querypartsql;
            } else {
                $searchvalue = $data->search['value'];
                $searchvalue = str_replace("&#39;", "\'", $searchvalue);
                $listsearchvalue = explode(" ", $searchvalue);

                $firstloop = true;

                foreach ($listsearchvalue as $key => $partsearchvalue) {
                    if (!$partsearchvalue) {
                        continue;
                    }

                    if ($firstloop) {
                        $request .= ' ( ';
                        $firstloop = false;
                    } else {
                        $request .= ' AND ( ';
                    }
                    $request .= $this->db->sql_like('co2.fullname', ':trainingname' . $key, false, false);
                    $params['trainingname' . $key] = '%' . $this->db->sql_like_escape($partsearchvalue) . '%';
                    $request .= ' OR ' .
                                $this->db->sql_like('s.courseshortname', ':courseshortname' . $key, false,
                                        false);
                    $params['courseshortname' . $key] = '%' . $this->db->sql_like_escape($partsearchvalue) . '%';
                    $request .= ' OR ' .
                                $this->db->sql_like('cc4.name', ':entityname' . $key, false,
                                        false) .
                                ' ) ';
                    $params['entityname' . $key] = '%' . $this->db->sql_like_escape($partsearchvalue) . '%';
                }
            }

            // Get part of query with params for status.
            list($querystatuspartsql, $querystatuspartparams) = $this->generate_session_sql_search_by_status($searchvalue,
                    $strictsearch);
            // Assign response.
            $params = array_merge($params, $querystatuspartparams);
            $request .= $querystatuspartsql;

            // Get part of query with params for collection.
            list($querycollectionpartsql, $querycollectionpartparams)
                    = $this->generate_session_sql_search_by_collection($searchvalue,
                    $strictsearch);
            // Assign response.
            $params = array_merge($params, $querycollectionpartparams);
            $request .= $querycollectionpartsql;

            // Closes 'AND' parenthese.
            $request .= ' ) ';
        }

        return $request;
    }

    /**
     * Generate piece of SQL request for exact collection search
     *
     * @param string $searchvalue
     * @param bool $strictsearch
     * @return array
     * @throws \coding_exception
     */
    public function generate_session_sql_search_by_collection($searchvalue, $strictsearch = false) {

        $searchvalue = str_replace("\'", "'", $searchvalue);

        $request = '';
        $params = [];

        // Get list collection and there string.
        $listcollection = local_mentor_specialization_get_collections();
        $listcollectionsearch = [];

        // Search if "searchvalue" is in string collection.
        $listcollectionsearchtmp = [];
        foreach ($listcollection as $keycollection => $collectionstring) {
            if ((!$strictsearch && strpos(strtolower($collectionstring), strtolower($searchvalue)) !== false)
                || ($strictsearch && strtolower($collectionstring) === strtolower($searchvalue))
            ) {
                $listcollectionsearchtmp[$keycollection] = strtolower($collectionstring);
            }
        }

        if (empty($listcollectionsearch)) {
            $listcollectionsearch = $listcollectionsearchtmp;
        } else {
            $listcollectionsearch = array_intersect($listcollectionsearchtmp, $listcollectionsearch);
        }

        // If search collection is true, add conditional request.
        if (!empty($listcollectionsearch)) {
            foreach ($listcollectionsearch as $key => $collectionstring) {
                $request .= ' OR ';
                $request .= $this->db->sql_like('t.collection', ':collection' . $key, false, false);
                $params['collection' . $key] = '%' . $this->db->sql_like_escape($key) . '%';
            }
        }

        return [$request, $params];
    }

    /**
     * Generate piece of SQL request for exact status search
     *
     * @param string $searchvalue
     * @param bool $strictsearch
     * @return array
     * @throws \coding_exception
     */
    public function generate_session_sql_search_by_status($searchvalue, $strictsearch = false) {
        $request = '';
        $params = [];

        // Get list status and there string.
        $liststatus = \local_mentor_core\session_api::get_status_list();
        $lisstatusstring = array_map(function($status) {
            return strtolower(get_string($status, 'local_mentor_core'));
        }, $liststatus);
        $liststatussearch = [];

        // Search if "searchvalue" is in string status.
        $liststatussearchtmp = [];
        foreach ($lisstatusstring as $keystatus => $statusstring) {
            if ((!$strictsearch && strpos(str_replace("é", "e", $statusstring), str_replace("é", "e", strtolower($searchvalue)))
                                   !== false)
                || ($strictsearch && $statusstring === strtolower($searchvalue))
            ) {
                $liststatussearchtmp[$keystatus] = $statusstring;
            }
        }
        if (empty($liststatussearch)) {
            $liststatussearch = $liststatussearchtmp;
        } else {
            $liststatussearch = array_intersect($liststatussearchtmp, $liststatussearch);
        }

        // If search status is true, add conditional request.
        if (!empty($liststatussearch)) {
            foreach ($liststatussearch as $key => $statusstring) {
                $request .= ' OR ';
                $request .= 's.status = :status' . $key;
                $params['status' . $key] = $key;
            }
        }

        return [$request, $params];
    }

    /**
     * Generate piece of SQL request for exact expression search
     *
     * @param string $expression
     * @return array
     */
    public function generate_session_sql_search_exact_expression($expression) {
        $request = '';
        $params = [];

        if (mb_strlen($expression) === 0) {
            return [$request, $params];
        }

        $request .= $this->db->sql_equal('t.courseshortname', ':trainingname', false, false);
        $params['trainingname'] = $expression;
        $request .= ' OR ' .
                    $this->db->sql_equal('s.courseshortname', ':courseshortname', false,
                            false);
        $params['courseshortname'] = $expression;
        $request .= ' OR ' .
                    $this->db->sql_equal('cc4.name', ':entityname', false,
                            false);
        $params['entityname'] = $expression;

        return [$request, $params];
    }

    /**
     * Convert a course role to another in a course
     *
     * @param int $courseid
     * @param string $fromroleshortname
     * @param string $endroleshortname
     * @return bool success or not
     * @throws \dml_exception
     */
    public function convert_course_role($courseid, $fromroleshortname, $endroleshortname) {
        // Get the start role.
        if (!$fromrole = $this->db->get_record('role', ['shortname' => $fromroleshortname])) {
            return false;
        }

        // Get the end role.
        if (!$endrole = $this->db->get_record('role', ['shortname' => $endroleshortname])) {
            return false;
        }

        // Update enrolment methods.
        try {
            $test2 = $this->db->execute('
                UPDATE {enrol}
                SET roleid = :newroleid
                WHERE courseid = :courseid AND roleid = :oldroleid',
                    [
                            'newroleid' => $endrole->id,
                            'courseid' => $courseid,
                            'oldroleid' => $fromrole->id
                    ]
            );
        } catch (\dml_exception $e) {
            \core\notification::error("ERROR : Update enrolment methods!!!\n" . $e->getMessage());
            return false;
        }

        $context = \context_course::instance($courseid);

        // Update role assignments.
        try {
            $test = $this->db->execute('
                UPDATE {role_assignments}
                SET roleid = :newroleid
                WHERE contextid = :contextid AND roleid = :oldroleid',
                    [
                            'newroleid' => $endrole->id,
                            'contextid' => $context->id,
                            'oldroleid' => $fromrole->id
                    ]
            );
        } catch (\dml_exception $e) {
            \core\notification::error("ERROR : Update role assignments!!!\n" . $e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Disable all course mods by modname
     *
     * @param int $courseid
     * @param string $modname
     * @throws \dml_exception
     */
    public function disable_course_mods($courseid, $modname) {
        // Fetch all modules.
        $mods = $this->db->get_records_sql('
            SELECT cm.id
            FROM {course_modules} cm
            JOIN {modules} m ON cm.module = m.id
            WHERE
                m.name = :modname
                AND
                cm.course = :courseid
        ', ['modname' => $modname, 'courseid' => $courseid]);

        // Hide all modules.
        foreach ($mods as $mod) {
            $mod->visible = 0;
            $this->db->update_record('course_modules', $mod);
        }
    }

    /**
     * Check if user is participant
     *
     * @param int $userid
     * @param int $contextid
     * @return bool
     * @throws \dml_exception
     */
    public function is_participant($userid, $contextid) {
        $sql = "SELECT ra.*
                FROM {role_assignments} ra
                JOIN {role} r ON r.id = ra.roleid
                WHERE ra.userid = :userid AND ra.contextid = :contextid
                    AND (r.shortname = :participant OR r.shortname = :participantnonediteur)";

        return $this->db->record_exists_sql($sql, array(
                'userid' => $userid,
                'contextid' => $contextid,
                'participant' => \local_mentor_specialization\mentor_profile::ROLE_PARTICIPANT,
                'participantnonediteur' => \local_mentor_specialization\mentor_profile::ROLE_PARTICIPANTNONEDITEUR
        ));
    }

    /**
     * Get course participants
     *
     * @param int $contextid
     * @return array
     * @throws \dml_exception
     */
    public function get_course_participants($contextid) {
        $sql = '
            SELECT DISTINCT(u.id), u.*
            FROM {user} u
            JOIN {role_assignments} ra ON ra.userid = u.id
            JOIN {role} r ON r.id = ra.roleid
            WHERE
                ra.contextid = :contextid
                AND (r.shortname = :participant OR r.shortname = :participantnonediteur)
        ';

        return $this->db->get_records_sql($sql, [
                'contextid' => $contextid,
                'participant' => \local_mentor_specialization\mentor_profile::ROLE_PARTICIPANT,
                'participantnonediteur' => \local_mentor_specialization\mentor_profile::ROLE_PARTICIPANTNONEDITEUR
        ]);
    }

    /**
     * Get all trainings by entity id
     *
     * @param int|\stdClass $data
     * @param boolean $onlymainentity - default true
     * @return \stdClass[]
     * @throws \dml_exception
     */
    public function get_trainings_by_entity_id($data, $onlymainentity = true) {

        // Return just trainings into main entity.
        if ($onlymainentity) {
            return parent::get_trainings_by_entity_id(is_object($data) ? $data->entityid : $data);
        }

        // Initialize request.
        $request = 'SELECT
                    t.*
                FROM
                    {training} t
                JOIN
                    {course} co ON co.shortname = t.courseshortname
                LEFT JOIN
                    {course_categories} cc ON cc.id = co.category
                LEFT JOIN
                    {course} co2 ON co2.shortname = t.courseshortname
                LEFT JOIN
                    {course_categories} cc2 ON cc2.id = co2.category
                LEFT JOIN
                    {course_categories} cc3 ON cc3.id = cc2.parent
                LEFT JOIN
                    {course_categories} cc4 ON cc4.id = cc3.parent
                WHERE
                    (cc.parent = :entityid OR cc4.parent = :entityid2)';

        // Get resultat request without condition and filter.
        if (!is_object($data)) {
            return $this->db->get_records_sql($request,
                    array(
                            'entityid' => $data,
                            'entityid2' => $data
                    )
            );
        }

        // Intitialize params to request.
        $params = array(
                'entityid' => $data->entityid,
                'entityid2' => $data->entityid
        );

        // Filters.
        $request .= $this->generate_trainings_by_entity_id_filter($data, $params);

        // Generate reseach part request.
        $request .= $this->generate_trainings_by_entity_id_search($data, $params);

        // Sort order.
        if ($data->order && isset($data->order['column'])) {
            switch ($data->order['column']) {
                case 0: // Sub-entity name.
                    $request .= " ORDER BY cc3.name " . $data->order['dir'];
                    break;
                case 1: // Collection.
                    $request .= " ORDER BY t.collection " . $data->order['dir'];
                    break;
                case 2: // Training shortname.
                    $request .= " ORDER BY co.fullname " . $data->order['dir'];
                    break;
                case 3: // Id SIRH.
                    $request .= " ORDER BY t.idsirh " . $data->order['dir'];
                    break;
                default: // Default : sort by id.
                    $request .= " ORDER BY t.id DESC";
                    break;
            }
        } else {
            // Default : sort by id.
            $request .= " ORDER BY t.id DESC";
        }

        // Execute request with conditions and filters.
        return $this->db->get_records_sql(
                $request,
                $params,
                $data->start,
                $data->length
        );
    }

    /**
     * Count all trainings by entity id
     *
     * @param int|\stdClass $data
     * @param boolean $onlymainentity - default true
     * @return int
     * @throws \dml_exception
     */
    public function count_trainings_by_entity_id($data, $onlymainentity = true) {

        // Return just trainings into main entity.
        if ($onlymainentity) {
            return parent::count_trainings_by_entity_id(is_object($data) ? $data->entityid : $data);
        }

        // Initialize request.
        $request = 'SELECT
                    count(DISTINCT t.id)
                FROM
                    {training} t
                JOIN
                    {course} co ON co.shortname = t.courseshortname
                LEFT JOIN
                    {course_categories} cc ON cc.id = co.category
                LEFT JOIN
                    {course} co2 ON co2.shortname = t.courseshortname
                LEFT JOIN
                    {course_categories} cc2 ON cc2.id = co2.category
                LEFT JOIN
                    {course_categories} cc3 ON cc3.id = cc2.parent
                LEFT JOIN
                    {course_categories} cc4 ON cc4.id = cc3.parent
                WHERE
                    (cc.parent = :entityid OR cc4.parent = :entityid2)';

        // Get resultat request without condition and filter.
        if (!is_object($data)) {
            return $this->db->count_records_sql($request,
                    array(
                            'entityid' => $data,
                            'entityid2' => $data
                    )
            );
        }

        // Intitialize params to request.
        $params = array(
                'entityid' => $data->entityid,
                'entityid2' => $data->entityid
        );

        // Filters.
        $request .= $this->generate_trainings_by_entity_id_filter($data, $params);

        // Generate reseach part request.
        $request .= $this->generate_trainings_by_entity_id_search($data, $params);

        // Execute request with conditions and filters.
        return $this->db->count_records_sql(
                $request,
                $params
        );
    }

    /**
     * Generate trainings by entity id filter part request.
     *
     * @param \stdClass $data
     * @param array $params
     * @return string
     */
    public function generate_trainings_by_entity_id_filter($data, &$params) {
        $request = '';

        if (isset($data->filters) && !empty($data->filters)) {
            // Sub-entities filter.
            if (isset($data->filters['subentity']) && !empty($data->filters['subentity'])) {
                $request .= ' AND (';
                foreach ($data->filters['subentity'] as $key => $subentityid) {
                    if ($key) {
                        $request .= ' OR ';
                    }
                    $request .= ' cc3.id = :subentityid' . $key;
                    $params['subentityid' . $key] = $subentityid;
                }
                $request .= ' ) ';
            }

            // Collections filter.
            if (isset($data->filters['collection']) && !empty($data->filters['collection'])) {
                $request .= ' AND (';
                foreach ($data->filters['collection'] as $key => $collection) {
                    if ($key) {
                        $request .= ' OR ';
                    }
                    $request .= $this->db->sql_like('t.collection', ':collection' . $key, false, false);
                    $params['collection' . $key] = '%' . $this->db->sql_like_escape($collection) . '%';
                }
                $request .= ' ) ';
            }

            // Status filter.
            if (isset($data->filters['status']) && !empty($data->filters['status'])) {
                $request .= ' AND (';
                foreach ($data->filters['status'] as $key => $status) {
                    if ($key) {
                        $request .= ' OR ';
                    }
                    $request .= ' t.status = :status' . $key;
                    $params['status' . $key] = $status;
                }
                $request .= ' ) ';
            }
        }

        return $request;
    }

    /**
     * Generate trainings by entity id search part request.
     *
     * @param \stdClass $data
     * @param array $params
     * @return string
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function generate_trainings_by_entity_id_search($data, &$params) {
        $request = '';

        if (isset($data->search) && $data->search && $data->search['value'] && mb_strlen(trim($data->search['value'])) > 1) {
            // Condition is closed after status and collection checks.
            $request .= ' AND ( ';

            // Do not execute strict search by default.
            $strictsearch = false;

            // Get list status and there string.
            $liststatus = \local_mentor_core\training_api::get_status_list();
            $lisstatusstring = array_map(function($status) {
                return strtolower(get_string($status, 'local_mentor_specialization'));
            }, $liststatus);

            // Get list collection and there string.
            $listcollection = local_mentor_specialization_get_collections();

            // Search exact value if '"' have been submitted.
            if (mb_substr(trim($data->search['value']), -5) === '&#34;'
                && mb_substr(trim($data->search['value']), 0, 5) === '&#34;'
                && mb_strlen(trim($data->search['value'])) > 11) {

                // Activate strict search.
                $strictsearch = true;

                // Get real search value.

                $searchvalue = trim(mb_substr(trim($data->search['value']), 5, -5));
                $searchvalue = str_replace("&#39;", "'", $searchvalue);

                $searchvaluestatus = $this->get_status_search_value_trainings($lisstatusstring, $searchvalue, true);

                $searchvaluecollection = $this->get_collection_search_value_trainings($listcollection, $searchvalue, true);

                // Get part of query with params.
                list($querypartsql, $querypartparams) = $this->generate_training_sql_search_exact_expression($searchvalue,
                        $searchvaluestatus);

                // Assign response.
                $params = array_merge($params, $querypartparams);
                $request .= $querypartsql;
            } else {
                $strreplace = str_replace("&#39;", "\'", $data->search['value']);
                $listsearchvalue = explode(" ", $strreplace);

                $firstloop = true;
                foreach ($listsearchvalue as $key => $partsearchvalue) {
                    if (!$partsearchvalue) {
                        continue;
                    }

                    $searchvaluestatus = $this->get_status_search_value_trainings($lisstatusstring, $partsearchvalue);

                    $searchvaluecollection = $this->get_collection_search_value_trainings($listcollection, $partsearchvalue);

                    if ($firstloop) {
                        $request .= ' ( ';
                        $firstloop = false;
                    } else {
                        $request .= ' AND ( ';
                    }

                    $request .= $this->db->sql_like('cc3.name', ':subentityname' . $key, false,
                            false);
                    $params['subentityname' . $key] = '%' . $this->db->sql_like_escape($partsearchvalue) . '%';
                    $request .= ' OR ';
                    $request .= $this->db->sql_like('t.courseshortname', ':trainingname' . $key, false,
                            false);
                    $params['trainingname' . $key] = '%' . $this->db->sql_like_escape($partsearchvalue) . '%';
                    $request .= ' OR ';
                    $request .= $this->db->sql_like('co.fullname', ':trainingnameco' . $key, false,
                            false);
                    $params['trainingnameco' . $key] = '%' . $this->db->sql_like_escape($partsearchvalue) . '%';
                    $request .= ' OR ';
                    $request .= $this->db->sql_like('co2.fullname', ':trainingnameco2' . $key, false,
                            false);
                    $params['trainingnameco2' . $key] = '%' . $this->db->sql_like_escape($partsearchvalue) . '%';
                    $request .= ' OR ';
                    $request .= $this->db->sql_like('t.idsirh', ':idsirh' . $key, false,
                                    false) .
                                ' ) ';
                    $params['idsirh' . $key] = '%' . $this->db->sql_like_escape($partsearchvalue) . '%';
                }
            }

            // Get part of query with params for status.
            list($querystatuspartsql, $querystatuspartparams)
                    = $this->generate_training_sql_search_by_status($searchvaluestatus,
                    $strictsearch);
            // Assign response.
            $params = array_merge($params, $querystatuspartparams);
            $request .= $querystatuspartsql;

            // Get part of query with params for collection.
            list($querycollectionpartsql, $querycollectionpartparams)
                    = $this->generate_training_sql_search_by_collection($searchvaluecollection, $strictsearch);
            // Assign response.
            $params = array_merge($params, $querycollectionpartparams);
            $request .= $querycollectionpartsql;

            // Closes 'AND' parenthese.
            $request .= ' ) ';
        }

        return $request;
    }

    /**
     * Get status depending on search
     *
     * @param string[] $lisstatusstring
     * @param string $partsearchvalue
     * @param bool $strictsearch
     * @return array|int|string
     */
    public function get_status_search_value_trainings($lisstatusstring, $partsearchvalue, $strictsearch = false) {
        $liststatussearch = [];

        // Search if "searchvalue" is in string status.
        $liststatussearchtmp = [];

        foreach ($lisstatusstring as $keystatus => $statusstring) {
            if (!$strictsearch && strpos($statusstring, strtolower($partsearchvalue)) !== false) {
                $liststatussearchtmp[$keystatus] = $statusstring;
            } else if ($strictsearch && strtolower($statusstring) === strtolower($partsearchvalue)) {
                return $keystatus;
            }
        }

        if (empty($liststatussearch)) {
            $liststatussearch = $liststatussearchtmp;
        } else {
            $liststatussearch = array_intersect($liststatussearchtmp, $liststatussearch);
        }

        return $liststatussearch;
    }

    /**
     * Get collection depending on search
     *
     * @param array $listcollection
     * @param string $partsearchvalue
     * @param bool $strictsearch
     * @return array|int|string
     */
    public function get_collection_search_value_trainings($listcollection, $partsearchvalue, $strictsearch = false) {
        $partsearchvalue = str_replace("\'", "'", $partsearchvalue);

        $listcollectionsearch = [];

        // Search if "searchvalue" is in string collection.
        $listcollectionsearchtmp = [];

        foreach ($listcollection as $keycollection => $collectionstring) {
            if (!$strictsearch && strpos(strtolower($collectionstring), strtolower($partsearchvalue)) !== false) {
                $listcollectionsearchtmp[$keycollection] = $collectionstring;
            } else if ($strictsearch && strtolower($collectionstring) === strtolower($partsearchvalue)) {
                return $keycollection;
            }
        }

        if (empty($listcollectionsearch)) {
            $listcollectionsearch = $listcollectionsearchtmp;
        } else {
            $listcollectionsearch = array_intersect($listcollectionsearchtmp, $listcollectionsearch);
        }

        return $listcollectionsearch;
    }

    /**
     * Generate piece of SQL request for exact expression search
     *
     * @param string $expression
     * @return array
     */
    public function generate_training_sql_search_exact_expression($expression) {
        $request = '';
        $params = [];

        if (mb_strlen($expression) === 0) {
            return [$request, $params];
        }

        $request .= $this->db->sql_equal('cc3.name', ':subentityname', false,
                false);
        $params['subentityname'] = $this->db->sql_like_escape($expression);
        $request .= ' OR ';
        $request .= $this->db->sql_equal('t.courseshortname', ':trainingname', false,
                false);
        $params['trainingname'] = $this->db->sql_like_escape($expression);
        $request .= ' OR ';
        $request .= $this->db->sql_equal('co.fullname', ':trainingnameco', false,
                false);
        $params['trainingnameco'] = $this->db->sql_like_escape($expression);
        $request .= ' OR ';
        $request .= $this->db->sql_equal('co2.fullname', ':trainingnameco2', false,
                false);
        $params['trainingnameco2'] = $this->db->sql_like_escape($expression);
        $request .= ' OR ';
        $request .= $this->db->sql_equal('t.idsirh', ':idsirh', false,
                false);
        $params['idsirh'] = $this->db->sql_like_escape($expression);

        return [$request, $params];
    }

    /**
     * Generate SQL query for training status search
     *
     * @param string $searchvalue
     * @param bool $strictsearch
     * @return array
     */
    public function generate_training_sql_search_by_status($searchvalue, $strictsearch = false) {
        $request = '';
        $params = [];

        // If search status is true, add conditional request.
        if (!empty($searchvalue)) {
            $cptstatussearch = 0;
            if ($strictsearch) {
                $request .= ' OR ';
                $request .= 't.status = :statussearch';
                $params['statussearch'] = (is_array($searchvalue)) ? key($searchvalue) : $searchvalue;
            } else {
                foreach ($searchvalue as $key => $statusstring) {
                    $request .= ' OR ';
                    $request .= 't.status = :statussearch' . $cptstatussearch;
                    $params['statussearch' . $cptstatussearch] = $key;
                    $cptstatussearch++;
                }
            }
        }

        return [$request, $params];
    }

    /**
     * Generate SQL query for sql collection search
     *
     * @param string $searchvalue
     * @param bool $strictsearch
     * @return array
     */
    public function generate_training_sql_search_by_collection($searchvalue, $strictsearch = false) {
        $request = '';
        $params = [];

        // If search collection is true, add conditional request.
        if (!empty($searchvalue)) {
            if ($strictsearch) {
                $request .= ' OR ';
                $request .= $this->db->sql_like('t.collection', ':collectionsearch');
                $params['collectionsearch'] = '%' . $this->db->sql_like_escape($searchvalue) . '%';
            } else {
                foreach ($searchvalue as $key => $collectionstring) {
                    $request .= ' OR ';
                    $request .= $this->db->sql_like('t.collection', ':collectionsearch' . $key, false,
                            false);
                    $params['collectionsearch' . $key] = '%' . $this->db->sql_like_escape($key) . '%';
                }
            }
        }

        return [$request, $params];
    }

    /**
     * Get the max sessionnumber from training sessions
     *
     * @param int $trainingid
     * @return int
     * @throws \dml_exception
     */
    public function get_max_training_session_index($trainingid) {
        $result = $this->db->get_record_sql('
            SELECT MAX(sessionnumber) as max
            FROM {session}
            WHERE trainingid = :trainingid
        ', ['trainingid' => $trainingid]);

        return $result ? $result->max : 0;
    }

    /**
     * Get sirh instances for a given course
     *
     * @param int $courseid
     * @return array
     * @throws \dml_exception
     */
    public function get_sirh_instances($courseid) {
        return $this->db->get_records('enrol', ['courseid' => $courseid, 'enrol' => 'sirh']);
    }

    /**
     * Get library training
     *
     * @return \stdClass[]
     * @throws \dml_exception
     */
    public function get_library_trainings() {
        return $this->db->get_records_sql('
                SELECT
                    t.*
                FROM
                    {training} t
                JOIN
                    {course} co ON co.shortname = t.courseshortname
                JOIN
                    {course_categories} cc ON cc.id = co.category
                JOIN
                    {library} l ON l.trainingid = t.id
                WHERE
                    cc.parent = :entityid
                ORDER BY
                    l.timemodified DESC, co.fullname ASC',
                array('entityid' => \local_mentor_core\library_api::get_library_id())
        );
    }

    /**
     * Remove the entity as the main entity from all users.
     *
     * @param int $entityid
     * @return void
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function remove_main_entity_to_all_user($entityid) {
        $entity = \core_course_category::get($entityid);
        $userinfofield = $this->db->get_record('user_info_field', ['shortname' => 'mainentity']);
        $this->db->delete_records_select(
                'user_info_data',
                'fieldid = :fieldid AND ' . $this->db->sql_compare_text('data') . ' = ' . $this->db->sql_compare_text(':data'),
                ['fieldid' => $userinfofield->id, 'data' => $entity->name]
        );
    }
}
