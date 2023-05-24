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
 * @package    local_mentor_core
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mentor_core;

use core\event\course_category_updated;
use core_course_category;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/mentor_core/classes/model/session.php');

class database_interface {

    /**
     * @var \moodle_database
     */
    protected $db;

    protected $entites;

    protected $mainentity;

    protected $courses;

    protected $sessions;

    protected $courseshortnames;

    protected $roles;

    protected $users;

    /**
     * @var self
     */
    protected static $instance;

    public function __construct() {

        global $DB;

        $this->db = $DB;

        $this->entities = $this->get_all_entities(false);
        $this->mainentity = $this->get_all_main_categories(false);
    }

    /**
     * Create a singleton
     *
     * @return database_interface
     */
    public static function get_instance() {

        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;

    }

    /*****************************FILES****************************/

    /**
     * Get a file record from database
     *
     * @param int $contextid
     * @param string $component
     * @param string $filearea
     * @param int $itemid
     * @return mixed
     * @throws \dml_exception
     */
    public function get_file_from_database($contextid, $component, $filearea, $itemid) {
        return $this->db->get_record_sql("
            SELECT *
            FROM {files}
            WHERE
                filename != '.'
                AND
                contextid = :contextid
                AND
                component = :component
                AND
                filearea = :filearea
                AND
                itemid = :itemid
        ", ['contextid' => $contextid, 'component' => $component, 'filearea' => $filearea, 'itemid' => $itemid]);
    }

    /*****************************USER*****************************/

    /**
     * Get user by email
     *
     * @param string $useremail
     * @return bool|\stdClass
     * @throws \dml_exception
     */
    public function get_user_by_email($useremail) {
        return \core_user::get_user_by_email($useremail);
    }

    /**
     * Get user by id
     *
     * @param int $userid
     * @param bool $forcerefresh
     * @return \stdClass
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function get_user_by_id($userid, $forcerefresh = false) {

        // Fetch the user in database and cache it.
        if ($forcerefresh || !isset($this->users[$userid])) {

            // Check if user exists.
            if (!$user = \core_user::get_user($userid)) {
                throw new \moodle_exception('unknownusererror', 'local_user', '', $userid);
            }

            $this->users[$userid] = $user;
        }

        return $this->users[$userid];
    }

    /**
     * Get user by username
     *
     * @return \stdClass|bool
     * @throws \dml_exception
     */
    public function get_user_by_username($username) {
        return $this->db->get_record_sql('
            SELECT *
            FROM {user}
            WHERE username = ?',
            array($username)
        );
    }

    /**
     * Search among users
     *
     * @param string $searchtext
     * @param array $exceptions
     * @return array
     */
    public function search_users($searchtext, $exceptions) {
        return search_users(0, 0, $searchtext, '', $exceptions);
    }

    /*****************************ENTITY***************************/

    /**
     * Update entity
     *
     * @param entity $entity
     * @return bool
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function update_entity($entity) {
        if (!isset($entity->id)) {
            throw new \moodle_exception('missingid');
        }

        if (isset($entity->shortname)) {
            $entity->idnumber = $entity->shortname;
        }

        unset($this->entities[$entity->id]);
        $coursecat = core_course_category::get($entity->id);
        $coursecat->update(get_object_vars($entity));
        return true;
    }

    /**
     * Search among main entities
     *
     * @param string $searchtext
     * @param bool $includehidden
     * @return array
     * @throws \dml_exception
     */
    public function search_main_entities($searchtext, $includehidden = true) {

        $and = '';

        // Exclude hidden entities.
        if (!$includehidden) {
            $and = " AND cc.id NOT IN (SELECT categoryid FROM {category_options} WHERE value = '1' AND name='hidden')";
        }

        return $this->db->get_records_sql('
            SELECT cc.*, cc.idnumber as shortname
            FROM {course_categories} cc
            WHERE
                parent = 0
                AND
                name LIKE \'%' . $searchtext . '%\'
                ' . $and . '
            ORDER BY shortname ASC');
    }

    /**
     * Search among main entities user managed
     *
     * @param string $searchtext
     * @param int $userid
     * @param string $roleshortname
     * @param bool $includehidden
     * @return array
     * @throws \dml_exception
     */
    public function search_main_entities_user_managed($searchtext, $userid, $roleshortname, $includehidden = true) {

        $and = '';

        // Exclude hidden entities.
        if (!$includehidden) {
            $and = " AND cc.id NOT IN (SELECT categoryid FROM {category_options} WHERE value = '1' AND name='hidden')";
        }

        return $this->db->get_records_sql('
            SELECT cc.*, cc.idnumber as shortname
            FROM {course_categories} cc
            JOIN {context} c ON c.instanceid = cc.id
            JOIN {role_assignments} ra ON ra.contextid = c.id
            JOIN {role} r ON r.id = ra.roleid
            WHERE cc.parent = 0 AND
                  ra.userid = :userid AND
                  r.shortname = :roleshortname AND
                  c.contextlevel = :contextlevel
                ' . $and . '
            AND cc.name LIKE \'%' . $searchtext . '%\'',
            array(
                'userid' => $userid,
                'roleshortname' => $roleshortname,
                'contextlevel' => CONTEXT_COURSECAT
            )
        );
    }

    /**
     * Get all users by mainentity
     *
     * @param string $mainentity
     * @return \stdClass[]
     * @throws \dml_exception
     */
    public function get_users_by_mainentity($mainentity) {

        return $this->db->get_records_sql('
            SELECT u.*
            FROM {user} u
            JOIN {user_info_data} uid ON u.id = uid.userid
            JOIN {user_info_field} uif ON uif.id = uid.fieldid
            WHERE
                uif.shortname = :fieldname
                AND
                uid.data = :data
        ', array('fieldname' => 'mainentity', 'data' => $mainentity));
    }

    /**
     * Get all users by mainentity
     *
     * @param string $secondaryentity
     * @return \stdClass[]
     * @throws \dml_exception
     */
    public function get_users_by_secondaryentity($secondaryentity) {

        $users = [];

        $usersdata = $this->db->get_records_sql('
            SELECT u.id, u.*, uid.data
            FROM {user} u
            JOIN {user_info_data} uid ON u.id = uid.userid
            JOIN {user_info_field} uif ON uif.id = uid.fieldid
            WHERE uif.shortname = :fieldname
            AND (' . $this->db->sql_like('uid.data', ':data', false, false) . '
            OR uid.data = :data2)
        ', array(
            'fieldname' => 'secondaryentities',
            'data' => '%' . $this->db->sql_like_escape($secondaryentity) . '%',
            'data2' => $secondaryentity
        ));

        foreach ($usersdata as $userdata) {
            $secondaryentities = explode(', ', $userdata->data);
            $key = array_search($secondaryentity, $secondaryentities);

            if ($key === false) {
                continue;
            }

            unset($userdata->data);
            $users[$userdata->id] = $userdata;
        }

        return $users;
    }

    /**
     * Get all user entities
     *
     * @param int $userid
     * @return \stdClass[]
     * @throws \dml_exception
     */
    public function get_user_entities($userid) {
        return $this->db->get_records_sql('
            SELECT cc.*, cc.idnumber as shortname
            FROM {course_categories} cc
            JOIN {context} c ON c.instanceid = cc.id
            JOIN {cohort} coh ON coh.contextid = c.id
            JOIN {cohort_members} cm ON cm.cohortid = coh.id
            WHERE
                c.contextlevel = 40
                AND
                cc.depth = 1
                AND
                cm.userid = :userid
        ', ['userid' => $userid]);
    }

    /*****************************ROLE*****************************/

    /**
     * Return the role in a stdClass with there name
     *
     * @param string $rolename
     * @return \stdClass|false
     * @throws \dml_exception
     */
    public function get_role_by_name($rolename) {

        if (empty($this->roles[$rolename])) {
            $this->roles[$rolename] = $this->db->get_record('role', array('shortname' => $rolename));
        }

        return $this->roles[$rolename];
    }

    /**
     * Get user roles in course
     *
     * @param int $userid
     * @param int $courseid
     * @return array
     * @throws \dml_exception
     */
    public function get_user_course_roles($userid, $courseid) {
        return $this->db->get_records_sql('
            SELECT r.id, r.shortname, r.name
            FROM
                {role} r
            JOIN {role_assignments} ra ON r.id = ra.roleid
            JOIN {context} c on ra.contextid = c.id
            WHERE
                c.contextlevel = :contextlevel
                AND
                ra.userid = :userid
                AND
                c.instanceid = :instanceid
        ', ['contextlevel' => CONTEXT_COURSE, 'userid' => $userid, 'instanceid' => $courseid]);
    }

    /***********************COURSE_CATEGORY************************/

    /**
     * Create a course category
     *
     * @param string $entityname name of the category
     * @param int $parent
     * @param string $idnumber - optional default empty
     * @return \core_course_category
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function create_course_category($entityname, $parent = 0, $idnumber = '') {
        $data = new \stdClass();
        $data->name = $entityname;
        $data->idnumber = $idnumber;
        $data->parent = $parent;
        $data->description = '';
        $category = \core_course_category::create($data);

        // Refresh entities cache.
        $this->get_all_entities(true);

        return $category;
    }

    /**
     * Get all main categories
     * if $refresh is true, refresh main entities cache
     *
     * @param bool $refresh refresh data from database default false
     * @param bool $includehidden - optional default true
     * @return \stdClass[]
     * @throws \dml_exception
     */
    public function get_all_main_categories($refresh = false, $includehidden = true, $filter = null) {
        if ($refresh || empty($this->mainentities)) {

            $and = '';

            // Do not retrieve hidden categories.
            if (!$includehidden) {
                $and = " AND cc.id NOT IN (SELECT categoryid FROM {category_options} WHERE value = '1' AND name='hidden')";
            }

            $request = '
                SELECT cc.*, cc.idnumber as shortname
                FROM {course_categories} cc
                WHERE depth = 1
                ' . $and;

            if (is_null($filter)) {
                // Default filter.
                $request .= ' ORDER BY shortname ASC, name ASC';
            } else {
                // Check order by filter.
                if (isset($filter->order)) {
                    $request .= ' ORDER BY ' . $filter->order['column'] . ' ' . $filter->order['dir'];
                }
            }

            $this->mainentities = $this->db->get_records_sql($request);
        }

        return $this->mainentities;
    }

    /**
     * Get all entities
     * if $refresh is true, refresh entities cache
     *
     * @param bool $refresh refresh data from database default false
     * @param null|\stdClass $filter
     * @param bool $includehidden - optional default true
     * @return \stdClass[]
     * @throws \dml_exception
     */
    public function get_all_entities($refresh = false, $filter = null, $includehidden = true) {
        if ($refresh || empty($this->entities)) {
            $and = '';

            // Do not retrieve hidden categories.
            if (!$includehidden) {
                $and = " AND cc.id NOT IN (SELECT categoryid FROM {category_options} WHERE value = '1' AND name='hidden')";
            }

            if (is_null($filter)) {
                $this->entities = $this->db->get_records_sql('
                SELECT cc.*, cc2.parent as parentid, cc2.name AS parentname, cc.idnumber as shortname
                FROM {course_categories} cc
                LEFT JOIN {course_categories} cc2 ON cc2.id = cc.parent
                WHERE (cc.depth = 1
                OR cc2.name = :subentitycategory)
                ' . $and . '
                ORDER BY shortname ASC, name ASC'
                    , array('subentitycategory' => \local_mentor_core\entity::SUB_ENTITY_CATEGORY));
            } else {
                $request = '
            SELECT cc.*, cc2.parent as parentid, cc2.name AS parentname, cc.idnumber as shortname
                FROM {course_categories} cc
                LEFT JOIN {course_categories} cc2 ON cc2.id = cc.parent
                LEFT JOIN {course_categories} cc3 ON cc2.parent = cc3.id
                WHERE (cc.depth = 1
                OR cc2.name = :subentitycategory)
                ' . $and;

                $params = ['subentitycategory' => \local_mentor_core\entity::SUB_ENTITY_CATEGORY];

                if (isset($filter->search) && !is_null($filter->search['value'])) {
                    $request .= 'AND (' .
                        $this->db->sql_like('cc.name', ':search1', false, false) . ' OR ' .
                        $this->db->sql_like('cc3.name', ':search2', false, false) . ' OR ' .
                        $this->db->sql_like('cc.idnumber', ':search3', false, false) . ' OR ' .
                        $this->db->sql_like('cc3.idnumber', ':search4', false, false) .
                        ')';

                    $likeescape = $this->db->sql_like_escape($filter->search['value']);
                    $params += [
                        'search1' => '%' . $likeescape . '%',
                        'search2' => '%' . $likeescape . '%',
                        'search3' => '%' . $likeescape . '%',
                        'search4' => '%' . $likeescape . '%'
                    ];
                }

                $request .= 'ORDER BY CONCAT(COALESCE(cc3.name, \'\'), cc.name) ' . $filter->order['dir'] .
                    ', CONCAT(COALESCE(cc3.idnumber, \'\'), cc.idnumber) ' . $filter->order['dir'];

                $this->entities = $this->db->get_records_sql(
                    $request,
                    $params
                );
            }
        }

        return $this->entities;
    }

    /**
     * Get sub entities of the entity
     *
     * @param int $entityid
     * @return mixed
     * @throws \dml_exception
     */
    public function get_sub_entities($entityid) {
        return $this->entities = $this->db->get_records_sql('
                SELECT cc.*, cc2.parent as parentid, cc2.name AS parentname, cc.idnumber as shortname
                FROM {course_categories} cc
                LEFT JOIN {course_categories} cc2 ON cc2.id = cc.parent
                WHERE cc2.name = :subentitycategory AND
                      cc2.parent = :entityid
                ORDER BY name ASC'
            , array(
                'subentitycategory' => \local_mentor_core\entity::SUB_ENTITY_CATEGORY,
                'entityid' => $entityid
            ));
    }

    /**
     * Get a course category by name
     *
     * @param string $categoryname
     * @param bool $refresh refresh or not the entities list before the check - default false
     * @param bool $mainonly - include only main entities. optional default false
     * @return \stdClass|false
     * @throws \dml_exception
     */
    public function get_course_category_by_name($categoryname, $refresh = false, $mainonly = false) {

        // Refresh entities cache.
        if ($refresh) {
            $this->get_all_entities(true);
        }

        // Check in class cache.
        foreach ($this->entities as $entity) {

            // We are looking for a main category.
            if ($mainonly && $entity->parentid != 0) {
                continue;
            }

            if (strtolower($entity->name) == strtolower($categoryname)) {
                return $entity;
            }
        }

        // Not found in cache, refresh entities cache again.
        $this->get_all_entities(true);

        foreach ($this->entities as $entity) {

            // We are looking for a main category.
            if ($mainonly && $entity->parentid != 0) {
                continue;
            }

            if (strtolower($entity->name) == strtolower($categoryname)) {
                return $entity;
            }
        }

        // Category not found.
        return false;
    }

    /**
     * Get a main entity by name
     *
     * @param string $categoryname
     * @param bool $refresh refresh or not the entities list before the check
     * @return \stdClass|false
     * @throws \dml_exception
     */
    public function get_main_entity_by_name($categoryname, $refresh = false) {

        // Refresh entities cache.
        if ($refresh) {
            $this->get_all_main_categories(true);
        }

        // Check in class cache.
        foreach ($this->mainentities as $entity) {
            if (strtolower($entity->name) == strtolower($categoryname)) {
                return $entity;
            }
        }

        // Refresh entities cache again.
        $this->get_all_main_categories(true);

        foreach ($this->mainentities as $entity) {
            if (strtolower($entity->name) == strtolower($categoryname)) {
                return $entity;
            }
        }

        // Main entity not found.
        return false;
    }

    /**
     * Check if a category shortname exists
     *
     * @param string $shortname
     * @param int $ignorecategoryid default 0
     * @return bool
     * @throws \dml_exception
     */
    public function entity_shortname_exists($shortname, $ignorecategoryid = 0) {
        return $this->db->record_exists_sql('
            SELECT *
            FROM {course_categories}
            WHERE idnumber = :idnumber AND id != :ignorecategoryid
        ', ['idnumber' => $shortname, 'ignorecategoryid' => $ignorecategoryid]);
    }

    /**
     * get library object.
     *
     * @return \stdClass|false
     * @throws \dml_exception
     */
    public function get_library_object() {
        return $this->db->get_record_sql('
            SELECT *
            FROM {course_categories}
            WHERE idnumber = :idnumber AND name = :name
        ', [
            'idnumber' => \local_mentor_core\library::SHORTNAME,
            'name' => \local_mentor_core\library::NAME
        ]);
    }

    /**
     * Get sub entity by name
     *
     * @param string $entityname
     * @param int $parentid
     * @return bool|mixed
     * @throws \dml_exception
     */
    public function get_sub_entity_by_name($entityname, $parentid) {
        $subentities = $this->get_sub_entities($parentid);

        foreach ($subentities as $subentity) {
            if (strtolower($subentity->name) == strtolower($entityname)) {
                return $subentity;
            }
        }

        return false;
    }

    /**
     * Get a course category by parent id and name
     *
     * @param int $parentid
     * @param string $name
     * @return mixed
     * @throws \dml_exception
     */
    public function get_course_category_by_parent_and_name($parentid, $name) {
        return $this->db->get_record_sql('
            SELECT cc.*, cc.idnumber as shortname
            FROM {course_categories} cc
            WHERE
                parent = :parent
                AND
                name = :name',
            ['parent' => $parentid, 'name' => $name]
        );
    }

    /**
     * Get a course category by id
     * if $refresh is true, refresh entities cache
     *
     * @param int $categoryid
     * @param bool $refresh default false . True to refresh the cached data.
     * @return \stdClass
     * @throws \dml_exception
     */
    public function get_course_category_by_id($categoryid, $refresh = false) {

        // Refresh entities cache.
        if ($refresh) {
            $this->get_all_entities(true);
        }

        // Fetch the data in database if it's not already in cache.
        if (!isset($this->entities[$categoryid])) {
            $this->entities[$categoryid] = $this->db->get_record_sql('
                SELECT cc.*, cc2.parent as parentid, cc2.name AS parentname, cc.idnumber as shortname
                FROM {course_categories} cc
                LEFT JOIN {course_categories} cc2 ON cc2.id = cc.parent
                WHERE cc.id = :id',
                array('id' => $categoryid), MUST_EXIST);
        }

        return $this->entities[$categoryid];
    }

    /**
     * Get a cohort by context id
     *
     * @param int $contextid
     * @return \stdClass|false
     * @throws \dml_exception
     */
    public function get_cohort_by_context_id($contextid) {
        return $this->db->get_record_sql('
            SELECT id, contextid, name
            FROM {cohort}
            WHERE contextid = :contextid
        ', ['contextid' => $contextid]);
    }

    /*****************************COURSE*****************************/

    /**
     * Get a course by id
     *
     * @param int $courseid
     * @param bool $forcerefresh
     * @return \stdClass
     * @throws \dml_exception
     */
    public function get_course_by_id($courseid, $forcerefresh = false) {

        if ($forcerefresh || !isset($this->courses[$courseid])) {
            $this->courses[$courseid] = get_course($courseid);
        }
        return $this->courses[$courseid];

    }

    /**
     * Get a course by shortname
     *
     * @param string $shortname
     * @param bool $refresh
     * @return \stdClass|false
     * @throws \dml_exception
     */
    public function get_course_by_shortname($shortname, $refresh = false) {

        if ($refresh || !isset($this->courseshortnames[$shortname])) {

            $course = $this->db->get_record('course', array('shortname' => $shortname));

            if (!$course) {
                return false;
            }
            $this->courses[$course->id] = $course;
            $this->courseshortnames[$shortname] = $course->id;
        }

        return $this->get_course_by_id($this->courseshortnames[$shortname]);

    }

    /**
     * Return edadmin courses linked with the id category
     *
     * @param int $categoryid
     * @return \stdClass[]
     * @throws \dml_exception
     */
    public function get_edadmin_courses_by_category($categoryid) {

        return $this->db->get_records_sql('
            SELECT c.id, c.fullname, c.shortname, cfp.value AS formattype
            FROM {course} c
            JOIN {course_format_options} cfp ON cfp.courseid = c.id
            WHERE
                c.category = :category
                AND c.format = :format
                AND cfp.name = :type',
            array(
                'category' => $categoryid,
                'format' => 'edadmin',
                'type' => 'formattype'
            )
        );
    }

    /**
     * Rename a course
     *
     * @param int $courseid
     * @param string $coursename
     * @param string|null $fullname
     * @return bool
     * @throws \dml_exception
     */
    public function update_course_name($courseid, $coursename, $fullname = null) {
        $course = new \stdClass();
        $course->id = $courseid;

        if ($fullname) {
            $course->fullname = $fullname;
        } else {
            $course->fullname = $coursename;
        }

        $course->shortname = $coursename;

        // Remove old course from class cache.
        if (isset($this->courses[$courseid])) {
            unset($this->courses[$courseid]);
        }

        return $this->db->update_record('course', $course);
    }

    /**
     * Check if a course exists in recyclebin
     *
     * @param string $shortname
     * @return bool
     * @throws \dml_exception
     */
    public function course_exists_in_recyclebin($shortname) {
        return $this->db->record_exists('tool_recyclebin_category', array('shortname' => $shortname));
    }

    /**
     * Check if shortname exists for courses.
     *
     * @param string $shortname
     * @return bool
     * @throws \dml_exception
     */
    public function course_shortname_exists($shortname) {

        // Shortname is empty.
        if (empty($shortname)) {
            return false;
        }

        // Check in course table.
        if ($this->course_exists($shortname)) {
            return true;
        }

        // Check in recyclebin.
        if ($this->course_exists_in_recyclebin($shortname)) {
            return true;
        }

        // Check if the session name exists.
        $tasksadhoc = $this->get_tasks_adhoc('\local_mentor_core\task\create_session_task');
        foreach ($tasksadhoc as $taskadhoc) {
            $customdata = json_decode($taskadhoc->customdata);
            if ($customdata->sessionname === $shortname) {
                return true;
            }
        }

        // Check if the training is already in an ad hoc task.
        $tasksadhoc = $this->get_tasks_adhoc('\local_mentor_core\task\duplicate_training_task');
        foreach ($tasksadhoc as $taskadhoc) {
            $customdata = json_decode($taskadhoc->customdata);

            if ($customdata->trainingshortname === $shortname) {
                return true;
            }
        }

        // Check if the training is already in an ad hoc task.
        $tasksadhoc = $this->get_tasks_adhoc('\local_mentor_core\task\duplicate_session_as_new_training_task');
        foreach ($tasksadhoc as $taskadhoc) {
            $customdata = json_decode($taskadhoc->customdata);

            if ($customdata->trainingshortname === $shortname) {
                return true;
            }
        }

        // Check if the training is already in an ad hoc task.
        $tasksadhoc = $this->get_tasks_adhoc('\local_library\task\import_to_entity_task');
        foreach ($tasksadhoc as $taskadhoc) {
            $customdata = json_decode($taskadhoc->customdata);

            if ($customdata->trainingshortname === $shortname) {
                return true;
            }
        }

        // The course shortname does not exists anywhere.
        return false;
    }

    /*********************COURSE_FORMAT_OPTION*********************/

    /**
     * Get edadmin course format options by
     *
     * @param int $courseid
     * @param bool $forcerefresh - true to fetch the result in database
     * @param string $format - course format to retrieve, default edadmin
     * @return \stdClass[]
     * @throws \dml_exception
     */
    public function get_course_format_options_by_course_id($courseid, $forcerefresh = false, $format = 'edadmin') {

        if ($forcerefresh || !isset($this->courseformatoptions[$courseid])) {
            $this->courseformatoptions[$courseid] = $this->db->get_records('course_format_options', array(
                'courseid' => $courseid,
                'format' => $format
            ));
        }

        return $this->courseformatoptions[$courseid];
    }

    /**
     * Set course format options
     *
     * @param int $courseid
     * @param string $format
     * @param array $options
     * @return void
     */
    public function set_course_format_options($courseid, $format, $options) {
        $this->db->delete_records('course_format_options', ['courseid' => $courseid, 'format' => $format]);

        foreach ($options as $option) {
            $insert = new \stdClass();
            $insert->courseid = $courseid;
            $insert->format = $format;
            $insert->sectionid = $option->sectionid;
            $insert->name = $option->name;
            $insert->value = $option->value;

            $this->add_course_format_option($insert);
        }
    }

    /**
     * Insert a new course format option
     *
     * @param \stdClass $formatoption
     * @return int $courseformatoptionid
     * @throws \dml_exception
     */
    public function add_course_format_option($formatoption) {
        $courseformatoptionid = $this->db->insert_record('course_format_options', $formatoption);

        // Remove cached data.
        if (isset($this->courseformatoptions[$formatoption->courseid])) {
            unset($this->courseformatoptions[$formatoption->courseid]);
        }

        return $courseformatoptionid;
    }

    /**
     * Get a course format option
     *
     * @param int $courseid
     * @param string $option
     * @return mixed
     * @throws \dml_exception
     */
    public function get_course_format_option($courseid, $option) {
        return $this->db->get_field('course_format_options', 'value', ['courseid' => $courseid, 'name' => $option]);
    }

    /*****************************COHORT*****************************/

    /**
     * Get cohort by id
     *
     * @param int $cohortid
     * @param bool $forcerefresh
     * @return \stdClass|boolean
     * @throws \dml_exception
     */
    public function get_cohort_by_id($cohortid, $forcerefresh = false) {

        if ($forcerefresh || !isset($this->cohorts[$cohortid])) {
            $this->cohorts[$cohortid] = $this->db->get_record('cohort', array('id' => $cohortid), 'id, name, contextid, visible');
        }

        return $this->cohorts[$cohortid];
    }

    /**
     * Get cohort by name
     *
     * @param int $cohortname
     * @return array
     * @throws \dml_exception
     */
    public function get_cohorts_by_name($cohortname) {
        return $this->db->get_records('cohort', array('name' => $cohortname), 'id, name, contextid, visible');
    }

    /**
     * Get all members cohort by cohort id
     *
     * @param int $cohortid
     * @param string $status default all. options : all, active, suspended
     * @return \stdClass[]
     * @throws \dml_exception
     */
    public function get_cohort_members_by_cohort_id($cohortid, $status) {

        $cohort = $this->get_cohort_by_id($cohortid);

        $sqlfilter = '';

        if ($status == 'active') {
            $sqlfilter = ' AND u.suspended = 0';
        } else if ($status == 'suspended') {
            $sqlfilter = ' AND u.suspended = 1';
        }

        $cohort->members = $this->db->get_records_sql('
            SELECT u.*, (SELECT uid.data
                         FROM {user_info_data} uid
                         JOIN {user_info_field} uif ON uif.id = uid.fieldid
                         WHERE uid.userid = u.id
                                AND uif.shortname = ?
                             ) as mainentity
            FROM {user} u
            INNER JOIN {cohort_members} cohortm
                ON cohortm.userid = u.id
            WHERE
                cohortm.cohortid = ?
                AND u.deleted = 0
                ' . $sqlfilter
            , array('mainentity', $cohortid));

        return $cohort->members;

    }

    /**
     * Update cohort
     *
     * @param \stdClass $cohort
     * @return bool
     * @throws \dml_exception
     */
    public function update_cohort($cohort) {
        return $this->db->update_record('cohort', $cohort);
    }

    /**
     * Get cohorts by userid
     *
     * @param int $userid
     * @return \stdClass[]
     * @throws \dml_exception
     */
    public function get_user_cohorts($userid) {

        return $this->db->get_records_sql('
            SELECT cm.*
            FROM {cohort_members} cm
            JOIN {cohort} coh ON coh.id = cm.cohortid
            JOIN {context} cnt ON cnt.id = coh.contextid
            JOIN {course_categories} cca ON cca.id = cnt.instanceid
            WHERE
                cnt.contextlevel = :contextlevel
                AND
                cca.depth = :dept
                AND
                cm.userid = :userid
        ', array('contextlevel' => 40, 'dept' => 1, 'userid' => $userid));
    }

    /**
     * Check if a user is member of a given cohort
     *
     * @param int $userid
     * @param int $cohortid
     * @return bool
     * @throws \dml_exception
     */
    public function check_if_user_is_cohort_member($userid, $cohortid) {
        return $this->db->record_exists('cohort_members',
            array(
                'userid' => $userid,
                'cohortid' => $cohortid
            )
        );
    }

    /**
     * Add cohort member
     *
     * @param int $cohortid
     * @param int $userid
     * @return bool
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function add_cohort_member($cohortid, $userid) {

        if (!$this->check_if_user_is_cohort_member($userid, $cohortid)) {
            cohort_add_member($cohortid, $userid);
        }

        return true;
    }

    /**
     * Remove cohort member
     *
     * @param int $cohortid
     * @param int $userid
     * @return bool
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function remove_cohort_member($cohortid, $userid) {

        if ($this->check_if_user_is_cohort_member($userid, $cohortid)) {
            cohort_remove_member($cohortid, $userid);
        }

        return true;
    }

    /****************************TRAINING**************************/

    /**
     * Add a new training
     *
     * @param \stdClass $training
     * @return bool|int
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function add_training($training) {

        // Check if the courseshortname is not missing.
        if (!isset($training->courseshortname) || empty($training->courseshortname)) {
            throw new \moodle_exception('missingshortname');
        }

        // Check if the training course exists.
        if (!$this->db->record_exists('course', ['shortname' => $training->courseshortname])) {
            throw new \moodle_exception('missingcourse');
        }

        // Insert the training.
        return $this->db->insert_record('training', $training);
    }

    /**
     * Update a training
     *
     * @param \stdClass $training
     * @return bool|int
     * @throws \dml_exception
     */
    public function update_training($training) {
        return $this->db->update_record('training', $training);
    }

    /**
     * Update a session
     *
     * @param \stdClass $session
     * @return bool|int
     * @throws \dml_exception
     */
    public function update_session($session) {
        unset($this->sessions[$session->id]);
        return $this->db->update_record('session', $session);
    }

    /**
     * Delete a session
     *
     * @param session $session
     * @throws \moodle_exception
     */
    public function delete_session($session) {
        if (!delete_course($session->courseid, false)) {
            throw new \moodle_exception('errorremovecourse', 'local_mentor_core');
        }

        unset($this->sessions[$session->id]);
    }

    /**
     * Delete a session sheet by course shortname
     *
     * @param string $shortname
     * @throws \dml_exception
     */
    public function delete_session_sheet($shortname) {
        $this->db->delete_records('session', ['courseshortname' => $shortname]);
    }

    /**
     * Delete a training sheet by course shortname
     *
     * @param string $shortname
     * @throws \dml_exception
     */
    public function delete_training_sheet($shortname) {
        $this->db->delete_records('training', ['courseshortname' => $shortname]);
    }

    /**
     * Get session sharing entities.
     *
     * @param int $sessionid
     * @return stdClass[]
     * @throws \dml_exception
     */
    public function get_opento_list($sessionid) {
        return $this->db->get_records_sql('
            SELECT
                coursecategoryid
            FROM
                {session_sharing} ss
            JOIN
                {course_categories} cc ON cc.id = coursecategoryid
            WHERE
                ss.sessionid = :sessionid
        ', array('sessionid' => $sessionid));
    }

    /**
     * Update session sharing entities.
     *
     * @param int $sessionid
     * @param array $entitiesid List of entities id
     * @return bool
     * @throws \Exception
     */
    public function update_session_sharing($sessionid, $entitiesid) {
        try {
            $this->db->delete_records('session_sharing', array('sessionid' => $sessionid));
            foreach ($entitiesid as $entity) {
                $sessionshare = new \stdClass();
                $sessionshare->sessionid = $sessionid;
                $sessionshare->coursecategoryid = $entity;
                $this->db->insert_record('session_sharing', $sessionshare);
            }

            return true;

        } catch (\Exception $e) {
            throw new \Exception('updatesessionsharingerror');
        }
    }

    /**
     * Remove sharing session data
     *
     * @param $sessionid
     * @return bool
     * @throws \dml_exception
     */
    public function remove_session_sharing($sessionid) {
        return $this->db->delete_records('session_sharing', ['sessionid' => $sessionid]);
    }

    /**
     * Get training by id
     *
     * @param int $trainingid
     * @return array
     * @throws \dml_exception
     */
    public function get_training_by_id($trainingid) {
        return $this->db->get_record_sql('
            SELECT
                t.*,co.fullname as name,
                co.shortname,
                co.summary as content,
                co.id as courseid, co.format as courseformat,
                con.id as contextid
            FROM
                {training} t
            JOIN
                {course} co ON co.shortname = t.courseshortname
            JOIN
                {context} con ON con.instanceid = co.id
            WHERE
                t.id = :id AND con.contextlevel = :contextlevel
        ', array('id' => $trainingid, 'contextlevel' => CONTEXT_COURSE), MUST_EXIST);
    }

    /**
     * Get a training by course shortname
     *
     * @param int $courseid
     * @return \stdClass
     * @throws \dml_exception
     */
    public function get_training_by_course_id($courseid) {
        return $this->db->get_record_sql('
            SELECT
                t.*, co.fullname as name, co.shortname, co.summary as content
            FROM
                {training} t
            JOIN
                {course} co ON co.shortname = t.courseshortname
            WHERE
                co.id = :courseid
        ', array('courseid' => $courseid));
    }

    /**
     * Get all trainings by entity id
     *
     * @param int $entityid
     * @return \stdClass[]
     * @throws \dml_exception
     */
    public function get_trainings_by_entity_id($entityid) {
        return $this->db->get_records_sql('
                SELECT
                    t.*
                FROM
                    {training} t
                JOIN
                    {course} co ON co.shortname = t.courseshortname
                JOIN
                    {course_categories} cc ON cc.id = co.category
                WHERE
                    cc.parent = :entityid',
            array('entityid' => $entityid)
        );
    }

    /**
     * Count all trainings by entity id
     *
     * @param int $entityid
     * @return int
     * @throws \dml_exception
     */
    public function count_trainings_by_entity_id($entityid) {
        return $this->db->count_records_sql('
                SELECT
                    count(DISTINCT t.id)
                FROM
                    {training} t
                JOIN
                    {course} co ON co.shortname = t.courseshortname
                JOIN
                    {course_categories} cc ON cc.id = co.category
                WHERE
                    cc.parent = :entityid',
            array('entityid' => $entityid)
        );
    }

    /**
     * Get an entity child category by name
     *
     * @param int $entityid
     * @param string $categoryname
     * @return false|\stdClass
     * @throws \dml_exception
     */
    public function get_entity_category_by_name($entityid, $categoryname) {
        return $this->db->get_record_sql('
            SELECT cc.*, cc.idnumber as shortname
            FROM {course_categories} cc
            WHERE
                parent = :parent
                AND
                name = :name
            ', ['parent' => $entityid, 'name' => $categoryname]);
    }

    /**
     * Get a category course by idnumber
     *
     * @param int $categoryid
     * @param string $idnumber
     * @return \stdClass
     * @throws \dml_exception
     */
    public function get_category_course_by_idnumber($categoryid, $idnumber) {
        return $this->db->get_record('course', ['category' => $categoryid, 'idnumber' => $idnumber]);
    }

    /**
     * Update the main entity name in all user profiles
     *
     * @param string $oldname
     * @param string $newname
     * @return bool
     * @throws \dml_exception
     */
    public function update_main_entities_name($oldname, $newname) {
        // Check if the mainentity profile field exists.
        if (!$mainentityfield = $this->db->get_record('user_info_field', ['shortname' => 'mainentity'])) {
            return false;
        }

        // Update all users mainentity fields.
        try {
            $this->db->execute('
            UPDATE
                {user_info_data}
            SET
                data = :newname
            WHERE
                data = :oldname
            AND
                fieldid=' . $mainentityfield->id,
                ['newname' => $newname, 'oldname' => $oldname]);
        } catch (\dml_exception $e) {
            \core\notification::error("ERROR : Update all users mainentity fields!!!\n" . $e->getMessage());
        }

        return true;
    }

    /**
     * Update the secondary entity name in all user profiles
     *
     * @param string $oldname
     * @param string $newname
     * @return bool
     * @throws \dml_exception
     */
    public function update_secondary_entities_name($oldname, $newname) {
        // Check if the secondary profile field exists.
        if (!$secondaryentityfield = $this->db->get_record('user_info_field', ['shortname' => 'secondaryentities'])) {
            return false;
        }

        $usersdatafield = $this->db->get_records_sql('
            SELECT uid.*
            FROM {user_info_data} uid
            WHERE uid.fieldid = :fieldid
            AND (' . $this->db->sql_like('uid.data', ':data', false, false) . '
            OR uid.data = :data2)
        ', array(
            'fieldid' => $secondaryentityfield->id,
            'data' => '%' . $this->db->sql_like_escape($oldname) . '%',
            'data2' => $oldname
        ));

        foreach ($usersdatafield as $userdatafield) {
            $secondaryentities = explode(', ', $userdatafield->data);
            $key = array_search($oldname, $secondaryentities);
            if ($key !== false) {
                $secondaryentities[$key] = $newname;
                $userdatafield->data = implode(', ', $secondaryentities);
                $this->db->update_record('user_info_data', $userdatafield);
            }
        }

        return true;
    }

    /**
     * Get the main category id of a given course
     *
     * @param int $courseid
     * @return int main category id (=entityid)
     * @throws \dml_exception
     */
    public function get_course_main_category_id($courseid) {
        return $this->db->get_field_sql(
            'SELECT
                    cc.parent
                FROM
                    {course_categories} cc
                JOIN
                    {course} c ON cc.id = c.category
                WHERE
                    c.id = :courseid
        ', ['courseid' => $courseid], MUST_EXIST);
    }

    /**
     * Check if a course exists by shortname
     *
     * @param string $courseshortname
     * @return bool
     * @throws \dml_exception
     */
    public function course_exists($courseshortname) {
        return $this->db->record_exists('course', ['shortname' => $courseshortname]);
    }

    /**
     * Check if a course category exists
     *
     * @param int $id
     * @return bool
     * @throws \dml_exception
     */
    public function course_category_exists($id) {
        return $this->db->record_exists('course_categories', ['id' => $id]);
    }

    /****************************SESSION***************************/

    /**
     * Add a new session
     *
     * @param \stdClass $session
     * @return bool|int
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function add_session($session) {

        // Check if the courseshortname is not missing.
        if (!isset($session->courseshortname) || empty($session->courseshortname)) {
            throw new \moodle_exception('missingshortname');
        }

        // Check if the course exists.
        if (!$this->db->record_exists('course', ['shortname' => $session->courseshortname])) {
            throw new \moodle_exception('missingcourse');
        }

        // Check if the trainingid is not missing.
        if (!isset($session->trainingid) || empty($session->trainingid)) {
            throw new \moodle_exception('missingshortname');
        }

        // Check if the course exists.
        if (!$this->db->record_exists('training', ['id' => $session->trainingid])) {
            throw new \moodle_exception('missingtraining');
        }

        // Reset the sessions cache.
        $this->sessions = [];

        return $this->db->insert_record('session', $session);
    }

    /**
     * Update session status
     *
     * @param int $sessionid
     * @param string $newstatus
     * @return bool
     * @throws \dml_exception
     */
    public function update_session_status($sessionid, $newstatus) {
        $session = new \stdClass();
        $session->id = $sessionid;
        $session->status = $newstatus;
        return $this->db->update_record('session', $session);
    }

    /**
     * Check if a session exists by shortname
     *
     * @param string $courseshortname
     * @return bool
     * @throws \dml_exception
     */
    public function session_exists($courseshortname) {
        return $this->db->record_exists('session', ['courseshortname' => $courseshortname]);
    }

    /**
     * Get session record by id
     *
     * @param int $sessionid
     * @return false|mixed
     * @throws \dml_exception
     */
    public function get_session_by_id($sessionid) {

        // Get the session if it's not found in class cache.
        if (!isset($this->sessions[$sessionid])) {

            $this->sessions[$sessionid] = $this->db->get_record_sql('
                SELECT
                    s.*, co.fullname, co.shortname, co.timecreated, co.id as courseid, con.id as contextid
                FROM
                    {session} s
                JOIN
                    {training} t ON t.id = s.trainingid
                JOIN
                    {course} co ON co.shortname = s.courseshortname
                JOIN
                    {context} con ON con.instanceid = co.id
                WHERE
                    s.id = :id AND con.contextlevel = :contextlevel
            ', array('id' => $sessionid, 'contextlevel' => CONTEXT_COURSE), MUST_EXIST);
        }

        return $this->sessions[$sessionid];
    }

    /**
     * Get all sessions by entity id
     *
     * @param \stdClass $data must contain at least : entityid, start, length. Optional fields are : status, dateto, datefrom.
     * @return \stdClass[]
     * @throws \dml_exception
     */
    public function get_sessions_by_entity_id($data) {

        $requiredfields = [
            'entityid', 'start', 'length'
        ];

        // Check if data contains all required fields.
        foreach ($requiredfields as $requiredfield) {
            if (!isset($data->{$requiredfield})) {
                throw new coding_exception('Missing field ' . $requiredfield);
            }
        }

        $request = '
                SELECT s.*
                FROM {session} s
                JOIN {training} t ON t.id = s.trainingid
                JOIN {course} co ON co.shortname = s.courseshortname
                LEFT JOIN {course_categories} cc ON cc.id = co.category
                LEFT JOIN {course} co2 ON co2.shortname = s.courseshortname
                LEFT JOIN {course_categories} cc2 ON cc2.id = co2.category
                LEFT JOIN {course_categories} cc3 ON cc3.id = cc2.parent
                LEFT JOIN {course_categories} cc4 ON cc4.id = cc3.parent
                WHERE cc.parent = :entityid OR cc4.parent = :entityid2';

        $params = array(
            'entityid' => $data->entityid,
            'entityid2' => $data->entityid
        );

        // Filter on session status.
        if ($data->status) {
            $request .= ' AND s.status = :status';
            $params['status'] = $data->status;
        }

        // Filter on end date.
        if ($data->dateto) {
            $request .= ' AND (co.timecreated > :dateto OR co2.timecreated > :dateto2)';
            $params['dateto'] = $data->dateto;
            $params['dateto2'] = $data->dateto;
        }

        // Filter on start date.
        if ($data->datefrom) {
            $request .= ' AND (co.timecreated < :datefrom OR co2.timecreated < :datefrom2)';
            $params['datefrom'] = $data->datefrom;
            $params['datefrom2'] = $data->datefrom;
        }

        return $this->db->get_records_sql(
            $request,
            $params,
            $data->start,
            $data->length
        );
    }

    /**
     * Count sessions record by entity id
     *
     * @param \stdClass $data - must contain at least an entityid field.
     * @return int
     * @throws \dml_exception
     */
    public function count_sessions_by_entity_id($data) {

        // Check required entityid field.
        if (!isset($data->entityid)) {
            throw new coding_exception('Missing field entityid');
        }

        $request = '
                SELECT count(s.id)
                FROM {session} s
                JOIN {training} t ON t.id = s.trainingid
                JOIN {course} co ON co.shortname = s.courseshortname
                LEFT JOIN {course_categories} cc ON cc.id = co.category
                LEFT JOIN {course} co2 ON co2.shortname = s.courseshortname
                LEFT JOIN {course_categories} cc2 ON cc2.id = co2.category
                LEFT JOIN {course_categories} cc3 ON cc3.id = cc2.parent
                LEFT JOIN {course_categories} cc4 ON cc4.id = cc3.parent
                WHERE cc.parent = :entityid OR cc4.parent = :entityid2';

        $params = array(
            'entityid' => $data->entityid,
            'entityid2' => $data->entityid
        );

        if ($data->status) {
            $request .= ' AND s.status = :status';
            $params['status'] = $data->status;
        }

        if ($data->dateto) {
            $request .= ' AND (co.timecreated > :dateto OR co2.timecreated > :dateto2)';
            $params['dateto'] = $data->dateto;
            $params['dateto2'] = $data->dateto;
        }

        if ($data->datefrom) {
            $request .= ' AND (co.timecreated < :datefrom OR co2.timecreated < :datefrom2)';
            $params['datefrom'] = $data->datefrom;
            $params['datefrom2'] = $data->datefrom;
        }

        return $this->db->count_records_sql(
            $request,
            $params
        );
    }

    /**
     * Get a session by course id
     *
     * @param int $courseid
     * @return \stdClass|bool
     * @throws \dml_exception
     */
    public function get_session_by_course_id($courseid) {
        return $this->db->get_record_sql('
            SELECT
                s.*, co.fullname as name, co.shortname, co.summary as content
            FROM
                {session} s
            JOIN
                {training} t ON t.id = s.trainingid
            JOIN
                {course} co ON co.shortname = s.courseshortname
            WHERE
                co.id = :courseid
        ', array('courseid' => $courseid));
    }

    /**
     * Get a sessions by training id.
     *
     * @param int $trainingid
     * @param string $orderby - optional default empty to skip order by.
     * @return \stdClass|bool
     * @throws \dml_exception
     */
    public function get_sessions_by_training_id($trainingid, $orderby = '') {
        $request = 'SELECT
                s.*, co.fullname as name, co.shortname, co.summary as content
            FROM
                {session} s
            JOIN
                {training} t ON t.id = s.trainingid
            JOIN
                {course} co ON co.shortname = s.courseshortname
            WHERE
                t.id = :trainingid';

        if (!empty($orderby)) {
            $request .= 'ORDER BY ' . $orderby;
        }

        return $this->db->get_records_sql($request, array('trainingid' => $trainingid));
    }

    /**
     * Check if the course is a session course
     *
     * @param int $courseid
     * @return bool
     * @throws \dml_exception
     */
    public function is_session_course($courseid) {
        return $this->db->record_exists_sql('
            SELECT
                s.id
            FROM
                {session} s
            JOIN
                {training} t ON t.id = s.trainingid
            JOIN
                {course} co ON co.shortname = s.courseshortname
            WHERE
                co.id = :courseid
        ', array('courseid' => $courseid));
    }

    /**
     * Count all session record
     *
     * @param int $entityid
     * @return int
     * @throws \dml_exception
     */
    public function count_session_record($entityid) {
        return $this->db->count_records_sql('
                SELECT count(DISTINCT s.id)
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
                    AND (con.contextlevel = :contextlevel OR con2.contextlevel = :contextlevel2)',
            [
                'entityid' => $entityid,
                'entityid2' => $entityid,
                'contextlevel' => CONTEXT_COURSE,
                'contextlevel2' => CONTEXT_COURSE,
            ]);
    }

    /**
     * Get the max sessionnumber from training sessions
     *
     * @param int $trainingid
     * @return mixed
     * @throws \dml_exception
     */
    public function get_max_training_session_index($trainingid) {
        return $this->db->count_records('session', ['trainingid' => $trainingid]);
    }

    /**
     * Get all sessions if the user is an admin.
     * Return false if user is not admin.
     *
     * @param $userid
     * @return array|false
     * @throws \dml_exception
     */
    public function get_all_admin_sessions($userid) {

        // Check if the user is admin.
        if (!is_siteadmin($userid)) {
            return false;
        }

        $results = $this->db->get_records_sql("
                SELECT s.*, c.id as courseid, con.id as contextid, c.fullname, c.shortname, c.timecreated
                FROM {session} s
                JOIN {training} t ON t.id = s.trainingid
                JOIN {course} c ON s.courseshortname = c.shortname
                JOIN {context} con ON c.id = con.instanceid AND con.contextlevel = :contextlevel
                WHERE
                    (s.status = :openedregistration OR s.status = :inprogress)
                    AND
                    s.opento != 'not_visible'
                GROUP BY
                    s.id, c.id, con.id, c.fullname, c.shortname, c.timecreated
            ", [
            'openedregistration' => session::STATUS_OPENED_REGISTRATION, 'inprogress' => session::STATUS_IN_PROGRESS,
            'contextlevel' => CONTEXT_COURSE
        ]);

        return $results;
    }

    /**
     * Get sessions shared to all entities.
     *
     * @return array
     * @throws \dml_exception
     */
    public function get_sessions_shared_to_all_entities() {
        return $this->db->get_records_sql("
            SELECT s.*, c.id as courseid, con.id as contextid, c.fullname, c.shortname, c.timecreated
            FROM {session} s
            JOIN {training} t ON t.id = s.trainingid
            JOIN {course} c ON s.courseshortname = c.shortname
            JOIN {context} con ON con.instanceid = c.id AND con.contextlevel = :contextlevel
            WHERE
                s.opento = 'all'
                AND
                (s.status = :openedregistration OR s.status = :inprogress)
            GROUP BY s.id, c.id, con.id, c.fullname, c.shortname, c.timecreated
        ", [
            'openedregistration' => session::STATUS_OPENED_REGISTRATION, 'inprogress' => session::STATUS_IN_PROGRESS,
            'contextlevel' => CONTEXT_COURSE
        ]);
    }

    /**
     * Get entities sessions.
     *
     * @param array $entities - [entityid => entityobject]
     * @param bool $opentoall
     * @return array
     * @throws \dml_exception
     */
    public function get_entities_sessions($entities, $opentoall = true) {

        // Check if the session path contain entity.
        $like = '(';
        foreach ($entities as $entityid => $entity) {
            $like .= ' cc.path LIKE \'%/' . $entityid . '/%\' OR';
        }
        $like = substr($like, 0, -3);
        $like .= ')';

        $request = "
            SELECT DISTINCT s.*, c.id as courseid, con.id as contextid, c.fullname, c.shortname, c.timecreated
            FROM {session} s
            JOIN {training} t ON t.id = s.trainingid
            JOIN {course} c ON s.courseshortname = c.shortname
            JOIN {course_categories} cc ON c.category = cc.id
            JOIN {context} con ON con.instanceid = c.id AND con.contextlevel = :contextlevel
            WHERE
                " . $like . "
                AND
                (s.status = :openedregistration OR s.status = :inprogress)
                AND
                s.opento != 'not_visible' ";

        if (!$opentoall) {
            $request .= "AND s.opento != 'all' ";
        }

        $request .= "GROUP BY s.id, c.id, con.id, c.fullname, c.shortname, c.timecreated";

        return $this->db->get_records_sql($request, [
            'openedregistration' => session::STATUS_OPENED_REGISTRATION,
            'inprogress' => session::STATUS_IN_PROGRESS,
            'contextlevel' => CONTEXT_COURSE
        ]);
    }

    /**
     * Get sessions shared to entities.
     *
     * @param array|string $entitiesid - Can be an array or a string of ids separated by commas.
     * @return array
     * @throws \dml_exception
     */
    public function get_sessions_shared_to_entities($entitiesid) {

        if (is_array($entitiesid)) {
            $entitiesid = implode(',', $entitiesid);
        }

        return $this->db->get_records_sql("
            SELECT DISTINCT s.*, c.id as courseid, con.id as contextid, c.fullname, c.shortname, c.timecreated
            FROM {session} s
            JOIN {training} t ON t.id = s.trainingid
            JOIN {course} c ON s.courseshortname = c.shortname
            JOIN {session_sharing} ss ON ss.sessionid = s.id
            JOIN {context} con ON con.instanceid = c.id AND con.contextlevel = :contextlevel
            WHERE
                ss.coursecategoryid IN (" . $entitiesid . ")
                AND
                (s.status = :openedregistration OR s.status = :inprogress)
                AND
                s.opento != 'not_visible'
            GROUP BY s.id, c.id, con.id, c.fullname, c.shortname, c.timecreated
        ", [
            'openedregistration' => session::STATUS_OPENED_REGISTRATION,
            'inprogress' => session::STATUS_IN_PROGRESS,
            'contextlevel' => CONTEXT_COURSE
        ]);
    }

    /**
     * Get all available sessions for a given user
     *
     * @param int $userid
     * @return \stdClass[]
     * @throws \dml_exception
     */
    public function get_user_available_sessions($userid) {

        // Get all sessions if the user is an admin.
        $allsessionsforuseradmin = $this->get_all_admin_sessions($userid);

        // False if user is not admin.
        if ($allsessionsforuseradmin !== false) {
            return $allsessionsforuseradmin;
        }

        // Get sessions shared to all entities.
        $sharedtoallsessions = $this->get_sessions_shared_to_all_entities();

        // User is not logged in.
        if (!isloggedin()) {
            return $sharedtoallsessions;
        }

        $entities = $this->get_user_entities($userid);

        // Ex 1,2,10.
        $entitiesid = implode(',', array_keys($entities));

        // The user has no entities.
        if (empty($entitiesid)) {
            return [];
        }

        // Get user entities sessions.
        $userentitiessessions = $this->get_entities_sessions($entities, false);

        // Get other entities sessions shared to user entities.
        $sharedtousersessions = $this->get_sessions_shared_to_entities($entitiesid);

        return array_merge($sharedtoallsessions, $userentitiessessions, $sharedtousersessions);
    }

    /**
     * Get number of session of a given training.
     *
     * @param int $trainingid
     * @return int
     * @throws \dml_exception
     */
    public function get_session_number($trainingid) {
        return $this->db->count_records_sql('
            SELECT
                COUNT(s.id)
            FROM
                {session} s
            JOIN
                {training} t ON t.id = s.trainingid
            JOIN
                {course} c ON s.courseshortname = c.shortname
            WHERE
                s.trainingid = :trainingid
        ', array('trainingid' => $trainingid), MUST_EXIST);
    }

    /**
     * Get number of availables sessions of a given training.
     *
     * @param int $trainingid
     * @return false|mixed
     * @throws \dml_exception
     */
    public function get_availables_sessions_number($trainingid) {
        return $this->db->get_record_sql('
            SELECT
                count(s.id) as sessionumber
            FROM
                {session} s
            JOIN
                {training} t ON t.id = s.trainingid
            WHERE
                s.trainingid = :trainingid
            AND
                (
                    s.status = :inpreparation
                    OR
                    s.status = :openedregistration
                    OR
                    s.status = :inprogress
                )
        ',
            [
                'trainingid' => $trainingid,
                'inpreparation' => \local_mentor_core\session::STATUS_IN_PREPARATION,
                'openedregistration' => \local_mentor_core\session::STATUS_OPENED_REGISTRATION,
                'inprogress' => \local_mentor_core\session::STATUS_IN_PROGRESS
            ],
            MUST_EXIST
        );
    }

    /**
     * Returns list of courses user is enrolled into.
     *
     * @param int $userid
     * @param string|null $sort
     * @return \stdClass[]
     * @throws \dml_exception
     */
    public function get_user_courses($userid, $sort = null) {
        return enrol_get_users_courses($userid, false, null, $sort);
    }

    /*****************************ENROL****************************/

    /**
     * Update enrolment instance record
     *
     * @param \stdClass $data
     * @return bool
     * @throws \dml_exception
     */
    public function update_enrolment($data) {
        return $this->db->update_record('enrol', $data);
    }

    /*****************************BACKUP**************************/

    /**
     * Get a course backup file
     *
     * @param int $contextid
     * @param string $component
     * @param string $filearea
     * @return false|mixed
     * @throws \dml_exception
     */
    public function get_course_backup($contextid, $component, $filearea) {
        return $this->db->get_record_sql('
                        SELECT
                            *
                        FROM
                            {files}
                        WHERE
                            contextid = :contextid
                            AND
                            component = :component
                            AND
                            filearea = :filearea
                            AND
                             filename != :filename
                        ORDER BY id DESC LIMIT 1',
            array(
                'contextid' => $contextid,
                'component' => $component,
                'filearea' => $filearea,
                'filename' => '.'
            )
        );
    }

    /**
     * Check if a training has sessions
     *
     * @param int $trainingid
     * @return bool
     * @throws \dml_exception
     */
    public function training_has_sessions($trainingid) {
        return $this->db->record_exists_sql('
           SELECT s.*
           FROM {session} s
           JOIN {course} c ON c.shortname = s.courseshortname
           WHERE s.trainingid = :trainingid
        ', array('trainingid' => $trainingid));
    }

    /**
     * Check if a training has sessions in recycle bin
     *
     * @param int $trainingid
     * @return bool
     * @throws \dml_exception
     */
    public function training_has_sessions_in_recycle_bin($trainingid) {
        return $this->db->record_exists_sql('
           SELECT s.*
           FROM {session} s
           JOIN {tool_recyclebin_category} trc ON trc.shortname = s.courseshortname
           WHERE s.trainingid = :trainingid
        ', array('trainingid' => $trainingid));
    }

    /**
     * Check if a training exists
     *
     * @param string $courseshortname
     * @return bool
     * @throws \dml_exception
     */
    public function training_exists($courseshortname) {
        return $this->db->record_exists('training', ['courseshortname' => $courseshortname]);
    }

    /**
     * Get next available training name
     *
     * @param string $trainingname
     * @return string
     * @throws \dml_exception
     */
    public function get_next_available_training_name($trainingname) {

        $nameok = false;
        $i = 1;

        $createsessiontasks = $this->get_tasks_adhoc('\local_mentor_core\task\create_session_task');
        $duplicatetrainingtasks = $this->get_tasks_adhoc('\local_mentor_core\task\duplicate_training_task');
        $duplicatesessiontasks = $this->get_tasks_adhoc('\local_mentor_core\task\duplicate_session_as_new_training_task');
        $importtoentitytasks = $this->get_tasks_adhoc('\local_library\task\import_to_entity_task');

        while (!$nameok) {

            $nameok = true;

            // Increment the shortname index.
            $shortname = $trainingname . ' ' . $i;

            // Check if the shortname already exists.
            if ($this->db->record_exists('course', ['shortname' => $shortname])) {
                $nameok = false;
            }

            if ($nameok) {
                // Check in create session task.
                foreach ($createsessiontasks as $taskadhoc) {
                    $customdata = json_decode($taskadhoc->customdata);
                    if ($customdata->sessionname === $shortname) {
                        $nameok = false;
                    }
                }
            }

            if ($nameok) {
                // Check in duplicate training task.
                foreach ($duplicatetrainingtasks as $taskadhoc) {
                    $customdata = json_decode($taskadhoc->customdata);
                    if ($customdata->trainingshortname === $shortname) {
                        $nameok = false;
                    }
                }
            }

            if ($nameok) {
                // Check in duplicate session as new training task.
                foreach ($duplicatesessiontasks as $taskadhoc) {
                    $customdata = json_decode($taskadhoc->customdata);
                    if ($customdata->trainingshortname === $shortname) {
                        $nameok = false;
                    }
                }
            }

            if ($nameok) {
                // Check in import to entity as new training task.
                foreach ($importtoentitytasks as $taskadhoc) {
                    $customdata = json_decode($taskadhoc->customdata);
                    if ($customdata->trainingshortname === $shortname) {
                        $nameok = false;
                    }
                }
            }

            $i++;
        }

        return $shortname;
    }

    /**
     * Get next sessionnumber index for a given training
     *
     * @param int $trainingid
     * @return int
     * @throws \dml_exception
     */
    public function get_next_sessionnumber_index($trainingid) {
        return $this->db->get_field_sql('
            SELECT MAX(sessionnumber)
            FROM {session}
            WHERE trainingid = :trainingid',
                ['trainingid' => $trainingid]) + 1;
    }

    /**
     * Get next available training name
     *
     * @param string $trainingname
     * @return string
     * @throws \dml_exception
     */
    public function get_next_training_session_index($trainingname) {

        $nameok = false;
        $i = 1;

        $createsessiontasks = $this->get_tasks_adhoc('\local_mentor_core\task\create_session_task');
        $duplicatetrainingtasks = $this->get_tasks_adhoc('\local_mentor_core\task\duplicate_training_task');
        $duplicatesessiontasks = $this->get_tasks_adhoc('\local_mentor_core\task\duplicate_session_as_new_training_task');

        while (!$nameok) {

            $nameok = true;

            // Increment the shortname index.
            $shortname = $trainingname . ' ' . $i;

            // Check if the shortname already exists.
            if ($this->db->record_exists('course', ['shortname' => $shortname])) {
                $nameok = false;
            }

            if ($nameok) {
                // Check in create session task.
                foreach ($createsessiontasks as $taskadhoc) {
                    $customdata = json_decode($taskadhoc->customdata);
                    if ($customdata->sessionname === $shortname) {
                        $nameok = false;
                    }
                }
            }

            if ($nameok) {
                // Check in duplicate training task.
                foreach ($duplicatetrainingtasks as $taskadhoc) {
                    $customdata = json_decode($taskadhoc->customdata);
                    if ($customdata->trainingshortname === $shortname) {
                        $nameok = false;
                    }
                }
            }

            if ($nameok) {
                // Check in duplicate session as new training task.
                foreach ($duplicatesessiontasks as $taskadhoc) {
                    $customdata = json_decode($taskadhoc->customdata);
                    if ($customdata->trainingshortname === $shortname) {
                        $nameok = false;
                    }
                }
            }

            if ($nameok) {
                // Check if shortname exists in recycle bin.
                if ($this->course_exists_in_recyclebin($shortname)) {
                    $nameok = false;
                }
            }

            $i++;
        }

        return $i - 1;
    }


    /*****************************SESSION_SHARING**************************/

    /**
     * Get all specific entities to which the session is shared
     *
     * @param int $sessionid
     * @return array
     * @throws \dml_exception
     */
    public function get_session_sharing_by_session_id($sessionid) {
        return $this->db->get_records('session_sharing', ['sessionid' => $sessionid]);
    }

    /**
     * Get list tasks adhoc
     *
     * @param null $classname - specify an ad hoc class name
     * @return array
     * @throws \dml_exception
     */
    public function get_tasks_adhoc($classname = null) {
        return $classname ?
            $this->db->get_records('task_adhoc', array('classname' => $classname)) :
            $this->db->get_records('task_adhoc');
    }

    /**
     * Get component files ordered by filearea
     *
     * @param int $contextid
     * @param string $component
     * @param int $itemid
     * @return \stdClass[]
     * @throws \dml_exception
     */
    public function get_files_by_component_order_by_filearea($contextid, $component, $itemid) {
        return $this->db->get_records_sql("
            SELECT f.filearea, f.*
            FROM {files} f
            WHERE
                filename != '.'
                AND
                contextid = :contextid
                AND
                component = :component
                AND
                itemid = :itemid
            ORDER BY filearea
        ", ['contextid' => $contextid, 'component' => $component, 'itemid' => $itemid]);
    }

    /**
     * Check if a course section exists and is visible
     *
     * @param int $courseid
     * @param int $sectionindex
     * @return bool
     * @throws \dml_exception
     */
    public function is_course_section_visible($courseid, $sectionindex) {
        return $this->db->record_exists('course_sections', ['course' => $courseid, 'section' => $sectionindex, 'visible' => 1]);
    }

    /**
     * Return user highest role object
     *
     * @param int $userid
     * @return \stdClass
     * @throws \dml_exception
     */
    public function get_highest_role_by_user($userid) {

        // Check if user is admin.
        if (is_siteadmin($userid)) {
            return (object) [
                'id' => 0,
                'name' => 'Administrateur pilote',
                'shortname' => 'admin'
            ];
        }

        return $this->db->get_record_sql('
            SELECT DISTINCT r.*
            FROM {role} r
            JOIN {role_assignments} ra ON ra.roleid = r.id
            WHERE ra.userid = :userid
            ORDER BY r.sortorder',
            array('userid' => $userid),
            IGNORE_MULTIPLE
        );
    }

    /**
     * Get all admins
     *
     * @param \stdClass $data
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function get_all_admins($data) {
        // Get all admins.
        $adminsid = get_config('moodle', 'siteadmins');

        // Initial resquest.
        $adminrequest = "
            SELECT
                u.id,
                '-' as categoryid,
                '-' as name,
                '-' as parentid,
                'Administrateur pilote' as rolename,
                u.firstname,
                u.lastname,
                u.id as userid,
                u.email,
                uid.data as mainentity,
                '-' as timemodified,
                u.lastaccess
            FROM
                {user} u
            JOIN
                {user_info_data} uid ON u.id = uid.userid
            JOIN
                {user_info_field} uif ON uid.fieldid = uif.id
            WHERE
                u.deleted = 0
                AND
                uif.shortname = :mainentity
                AND
                u.id IN (" . $adminsid . ")
        ";

        $params = ['mainentity' => 'mainentity'];

        // Manage search in admin roles.
        if (is_array($data->search) && $data->search['value']) {

            // Clean searched value.
            $cleanedsearch = str_replace(
                ["'", '"'],
                [" ", " "],
                $data->search['value']);

            $listsearchvalue = explode(" ", $cleanedsearch);

            // Generate the search part of the request.
            foreach ($listsearchvalue as $key => $searchvalue) {
                if (!$searchvalue) {
                    continue;
                }

                $adminrequest .= ' AND ( ';

                $adminrequest .= $this->db->sql_like('u.firstname', ':firstname' . $key, false, false);
                $params['firstname' . $key] = '%' . $this->db->sql_like_escape($searchvalue) . '%';
                $adminrequest .= ' OR ';

                $adminrequest .= $this->db->sql_like('u.lastname', ':lastname' . $key, false, false);
                $params['lastname' . $key] = '%' . $this->db->sql_like_escape($searchvalue) . '%';
                $adminrequest .= ' OR ';

                $adminrequest .= $this->db->sql_like('u.email', ':email' . $key, false, false);
                $params['email' . $key] = '%' . $this->db->sql_like_escape($searchvalue) . '%';
                $adminrequest .= ' OR ';

                $adminrequest .= $this->db->sql_like('uid.data', ':mainentity' . $key, false, false);
                $params['mainentity' . $key] = '%' . $this->db->sql_like_escape($searchvalue) . '%';
                $adminrequest .= ' OR ';

                $adminrequest .= "position('" . $searchvalue . "' IN 'Administrateur pilote') > 0";

                $adminrequest .= ' ) ';
            }
        }

        // Execute request with conditions and filters.
        return $this->db->get_records_sql(
            $adminrequest,
            $params
        );
    }

    /**
     * Get all users category
     *
     * @param \stdClass $data
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function get_all_category_users($data) {
        // Initial request.
        $request = '
            SELECT
                ra.id,
                cc.id as categoryid,
                cc.name,
                cc.parent as parentid,
                r.name as rolename,
                u.firstname,
                u.lastname,
                u.id as userid,
                u.email,
                uid.data as mainentity,
                ra.timemodified,
                u.lastaccess
            FROM
                {user} u
            JOIN
               {role_assignments} ra ON ra.userid = u.id
            JOIN
                {role} r on ra.roleid = r.id
            JOIN
                {role_context_levels} rcl ON r.id = rcl.roleid
            JOIN
                {context} c ON ra.contextid = c.id
            JOIN
                {course_categories} cc ON c.instanceid = cc.id
            LEFT JOIN
                {user_info_data} uid ON u.id = uid.userid
            INNER JOIN
                {user_info_field} uif ON uid.fieldid = uif.id
            WHERE
                rcl.contextlevel = :contextlevel
                AND
                uif.shortname = :mainentity
                AND
                deleted = 0
        ';

        // Set default params.
        $params = ['contextlevel' => CONTEXT_COURSECAT, 'mainentity' => 'mainentity'];

        // Manage search in category roles.
        if (is_array($data->search) && $data->search['value']) {
            $listsearchvalue = explode(" ", $data->search['value']);

            // Generate the search part of the request.
            foreach ($listsearchvalue as $key => $searchvalue) {
                if (!$searchvalue) {
                    continue;
                }

                $request .= ' AND ( ';

                $request .= $this->db->sql_like('u.firstname', ':firstname' . $key, false, false);
                $params['firstname' . $key] = '%' . $this->db->sql_like_escape($searchvalue) . '%';
                $request .= ' OR ';

                $request .= $this->db->sql_like('u.lastname', ':lastname' . $key, false, false);
                $params['lastname' . $key] = '%' . $this->db->sql_like_escape($searchvalue) . '%';
                $request .= ' OR ';

                $request .= $this->db->sql_like('u.email', ':email' . $key, false, false);
                $params['email' . $key] = '%' . $this->db->sql_like_escape($searchvalue) . '%';
                $request .= ' OR ';

                $request .= $this->db->sql_like('uid.data', ':mainentity' . $key, false, false);
                $params['mainentity' . $key] = '%' . $this->db->sql_like_escape($searchvalue) . '%';
                $request .= ' OR ';

                $request .= $this->db->sql_like('r.name', ':rolename' . $key, false, false);
                $params['rolename' . $key] = '%' . $this->db->sql_like_escape($searchvalue) . '%';
                $request .= ' OR ';

                $request .= $this->db->sql_like('cc.name', ':name' . $key, false, false);
                $params['name' . $key] = '%' . $this->db->sql_like_escape($searchvalue) . '%';

                $request .= ' ) ';
            }

        }

        // Execute request with conditions and filters.
        $userswithmainentities = $this->db->get_records_sql(
            $request,
            $params
        );

        // Users without main entities.

        // Initial request.
        $request = "
            SELECT
                ra.id,
                cc.id as categoryid,
                cc.name,
                cc.parent as parentid,
                r.name as rolename,
                u.firstname,
                u.lastname,
                u.id as userid,
                u.email,
                '-' as mainentity,
                ra.timemodified,
                u.lastaccess
            FROM
                {user} u
            JOIN
               {role_assignments} ra ON ra.userid = u.id
            JOIN
                {role} r on ra.roleid = r.id
            JOIN
                {role_context_levels} rcl ON r.id = rcl.roleid
            JOIN
                {context} c ON ra.contextid = c.id
            JOIN
                {course_categories} cc ON c.instanceid = cc.id
            WHERE
                rcl.contextlevel = :contextlevel
                AND
                deleted = 0
                AND
                u.id NOT IN (
                    SELECT uid2.userid
                    FROM {user_info_data} uid2
                    JOIN
                        {user_info_field} uif2 ON uid2.fieldid = uif2.id
                    WHERE
                        uif2.shortname = :mainentity
                )
        ";

        // Set default params.
        $params = ['contextlevel' => CONTEXT_COURSECAT, 'mainentity' => 'mainentity'];

        // Manage search in category roles.
        if (is_array($data->search) && $data->search['value']) {
            $listsearchvalue = explode(" ", $data->search['value']);

            // Generate the search part of the request.
            foreach ($listsearchvalue as $key => $searchvalue) {
                if (!$searchvalue) {
                    continue;
                }

                $request .= ' AND ( ';

                $request .= $this->db->sql_like('u.firstname', ':firstname' . $key, false, false);
                $params['firstname' . $key] = '%' . $this->db->sql_like_escape($searchvalue) . '%';
                $request .= ' OR ';

                $request .= $this->db->sql_like('u.lastname', ':lastname' . $key, false, false);
                $params['lastname' . $key] = '%' . $this->db->sql_like_escape($searchvalue) . '%';
                $request .= ' OR ';

                $request .= $this->db->sql_like('u.email', ':email' . $key, false, false);
                $params['email' . $key] = '%' . $this->db->sql_like_escape($searchvalue) . '%';
                $request .= ' OR ';

                $request .= $this->db->sql_like('r.name', ':rolename' . $key, false, false);
                $params['rolename' . $key] = '%' . $this->db->sql_like_escape($searchvalue) . '%';
                $request .= ' OR ';

                $request .= $this->db->sql_like('cc.name', ':name' . $key, false, false);
                $params['name' . $key] = '%' . $this->db->sql_like_escape($searchvalue) . '%';

                $request .= ' ) ';
            }

        }

        // Execute request with conditions and filters.
        $userswithoutmainentities = $this->db->get_records_sql(
            $request,
            $params
        );

        return array_merge($userswithmainentities, $userswithoutmainentities);
    }

    /**
     * Get roles of course context
     *
     * @return \stdClass[]
     * @throws \dml_exception
     */
    public function get_course_roles() {
        return $this->db->get_records_sql('
            SELECT r.*
            FROM {role} r
            JOIN {role_context_levels} rcl ON r.id = rcl.roleid
            WHERE
                rcl.contextlevel = :coursecontext
        ', ['coursecontext' => CONTEXT_COURSE]);
    }

    /**
     * Unassign roles from a context
     *
     * @param int $contextid
     * @param array $rolesid
     * @throws \dml_exception
     */
    public function unassign_roles($contextid, $rolesid) {

        $roles = $this->db->get_records_sql('
            SELECT
                id
            FROM
                {role_assignments}
            WHERE
                contextid = :contextid
                AND
                roleid IN (' . implode(',', $rolesid) . ')
        ', ['contextid' => $contextid]);

        foreach ($roles as $role) {
            $this->db->delete_records('role_assignments', ['id' => $role->id]);
        }
    }

    /**
     * Set a profile field value
     *
     * @param int $userid
     * @param string $rolename
     * @param string $value
     * @return bool
     * @throws \dml_exception
     */
    public function set_profile_field_value($userid, $rolename, $value) {

        $profilefieldvalue = $this->db->get_record_sql('
            SELECT uid.*
            FROM {user_info_data} uid
            JOIN {user_info_field} uif ON uif.id = uid.fieldid
            WHERE
                uid.userid = :userid
                AND
                uif.shortname = :shortname
        ', ['userid' => $userid, 'shortname' => $rolename]);

        // Value already exists.
        if ($profilefieldvalue && $profilefieldvalue->data != $value) {
            $profilefieldvalue->data = $value;
            $this->db->update_record('user_info_data', $profilefieldvalue);
        } else if (!$profilefieldvalue) {
            // Value does not exist.
            $profilefield = $this->db->get_record('user_info_field', ['shortname' => $rolename]);
            $profilefielddata = new \stdClass();
            $profilefielddata->userid = $userid;
            $profilefielddata->fieldid = $profilefield->id;
            $profilefielddata->data = $value;

            $this->db->insert_record('user_info_data', $profilefielddata);
        }

        return true;
    }

    /**
     * get a profile field value
     *
     * @param int $userid
     * @param string $name
     * @return string|bool
     * @throws \dml_exception
     */
    public function get_profile_field_value($userid, $name) {
        // Get all user data profile.
        $profileuserrecord = profile_user_record($userid);

        // Check if user's field data exist.
        if (!property_exists($profileuserrecord, $name)) {
            return false;
        }

        return $profileuserrecord->$name;
    }

    /**
     * Get role assignments on a context
     *
     * @param int $contextid
     * @return array
     * @throws \dml_exception
     */
    public function get_role_assignments($contextid) {
        return $this->db->get_records('role_assignments', ['contextid' => $contextid]);
    }

    /**
     * Get all training files
     *
     * @param int $contextid
     * @return array
     * @throws \dml_exception
     */
    public function get_all_training_files($contextid) {
        return $this->db->get_records_sql('
            SELECT
                *
            FROM
                {files}
            WHERE
                filename != ?
                AND component = ?
                AND contextid = ?
        ', ['.', 'local_trainings', $contextid]);
    }

    /**
     * Check if session is sharing to the entity.
     *
     * @param int $sessionid
     * @param int $entityid
     * @return bool
     * @throws \dml_exception
     */
    public function is_shared_to_entity_by_session_id($sessionid, $entityid) {
        return $this->db->record_exists('session_sharing', ['sessionid' => $sessionid, 'coursecategoryid' => $entityid]);
    }

    /**
     * Get all available sessions catalog for a given entity
     *
     * @param int $entityid
     * @return \stdClass[]
     * @throws \dml_exception
     */
    public function get_available_sessions_to_catalog_by_entity($entityid) {

        // Get sessions shared to all entities.
        $sharedtoall = $this->get_sessions_shared_to_all_entities();

        // Get entities sessions.
        $entitiessessions = $this->get_entities_sessions([$entityid => $entityid]);

        // Get other entities sessions shared to entities.
        $sharedsessions = $this->get_sessions_shared_to_entities([$entityid]);

        return array_merge($sharedtoall, $entitiessessions, $sharedsessions);
    }

    /**
     * Get the type of a singleactivity course
     *
     * @param int $courseid
     * @return mixed
     * @throws \dml_exception
     */
    public function get_course_singleactivity_type($courseid) {
        return $this->db->get_field('course_format_options', 'value', [
            'courseid' => $courseid,
            'format' => 'singleactivity',
            'name' => 'activitytype'
        ]);
    }

    /**
     * Get all session group.
     *
     * @param int $sessionid
     * @return array
     * @throws \dml_exception
     */
    public function get_all_session_group($sessionid) {
        return $this->db->get_records_sql('
            SELECT g.*
            FROM {groups} g
            JOIN {course} c ON g.courseid = c.id
            JOIN {session} s ON s.courseshortname = c.shortname
            WHERE s.id = :sessionid
        ', array('sessionid' => $sessionid));
    }

    /**
     * Add new user favourite.
     *
     * @param string $component
     * @param string $itemtype
     * @param int $itemid
     * @param int $contextid
     * @param int $userid
     * @return bool|int
     * @throws \dml_exception
     */
    public function add_user_favourite($component, $itemtype, $itemid, $contextid, $userid) {
        $favourite = new \stdClass();
        $favourite->component = $component;
        $favourite->itemtype = $itemtype;
        $favourite->itemid = $itemid;
        $favourite->contextid = $contextid;
        $favourite->userid = $userid;
        $favourite->timecreated = time();
        $favourite->timemodified = time();

        return $this->db->insert_record('favourite', $favourite);
    }

    /**
     * Remove user favourite.
     *
     * @param string $component
     * @param string $itemtype
     * @param int $itemid
     * @param int $contextid
     * @param int $userid
     * @return bool
     * @throws \dml_exception
     */
    public function remove_user_favourite($component, $itemtype, $itemid, $contextid, $userid) {
        $favourite = [];
        $favourite['component'] = $component;
        $favourite['itemtype'] = $itemtype;
        $favourite['itemid'] = $itemid;
        $favourite['contextid'] = $contextid;
        $favourite['userid'] = $userid;

        return $this->db->delete_records('favourite', $favourite);
    }

    /**
     * Check if favourite exist.
     *
     * @param string $component
     * @param string $itemtype
     * @param int $itemid
     * @param int $contextid
     * @param int $userid
     * @return bool
     * @throws \dml_exception
     */
    public function is_user_favourite($component, $itemtype, $itemid, $contextid, $userid) {
        $favourite = [];
        $favourite['component'] = $component;
        $favourite['itemtype'] = $itemtype;
        $favourite['itemid'] = $itemid;
        $favourite['contextid'] = $contextid;
        $favourite['userid'] = $userid;

        return $this->db->record_exists('favourite', $favourite);
    }

    /**
     * Get user favourite data.
     *
     * @param string $component
     * @param string $itemtype
     * @param int $itemid
     * @param int $contextid
     * @param int $userid
     * @return false|\stdClass
     * @throws \dml_exception
     */
    public function get_user_favourite($component, $itemtype, $itemid, $contextid, $userid) {
        $favourite = [];
        $favourite['component'] = $component;
        $favourite['itemtype'] = $itemtype;
        $favourite['itemid'] = $itemid;
        $favourite['contextid'] = $contextid;
        $favourite['userid'] = $userid;

        return $this->db->get_record('favourite', $favourite);
    }

    /**
     * Add a training to the user's preferred designs.
     *
     * @param int $itemid
     * @param int $contextid
     * @param int $userid
     * @return bool|int
     * @throws \dml_exception
     */
    public function add_trainings_user_designer_favourite($itemid, $contextid, $userid = null) {
        global $USER;

        if (is_null($userid)) {
            $userid = $USER->id;
        }

        return $this->add_user_favourite(
            'local_trainings',
            \local_mentor_core\training::FAVOURITE_DESIGNER,
            $itemid,
            $contextid,
            $userid
        );
    }

    /**
     * Remove a training to the user's preferred designs.
     *
     * @param int $itemid
     * @param int $contextid
     * @param int $userid
     * @return bool
     * @throws \dml_exception
     */
    public function remove_trainings_user_designer_favourite($itemid, $contextid, $userid = null) {
        global $USER;

        if (is_null($userid)) {
            $userid = $USER->id;
        }

        return $this->remove_user_favourite(
            'local_trainings',
            \local_mentor_core\training::FAVOURITE_DESIGNER,
            $itemid,
            $contextid,
            $userid
        );
    }

    /**
     * Check if the user has chosen this training in these preferred designs.
     *
     * @param int $itemid
     * @param int $contextid
     * @param int $userid
     * @return bool
     */
    public function is_training_user_favourite_designer($itemid, $contextid, $userid = null) {
        global $USER;

        if (is_null($userid)) {
            $userid = $USER->id;
        }

        return $this->is_user_favourite(
            'local_trainings',
            \local_mentor_core\training::FAVOURITE_DESIGNER,
            $itemid,
            $contextid,
            $userid
        );
    }

    /**
     * Get user preferred designs data.
     *
     * @param int $itemid
     * @param int $contextid
     * @param int $userid
     * @return \stdClass|false
     */
    public function get_training_user_favourite_designer_data($itemid, $contextid, $userid = null) {
        global $USER;

        if (is_null($userid)) {
            $userid = $USER->id;
        }

        return $this->get_user_favourite(
            'local_trainings',
            \local_mentor_core\training::FAVOURITE_DESIGNER,
            $itemid,
            $contextid,
            $userid
        );
    }

    /**
     * Add session to user's favourite.
     *
     * @param int $itemid
     * @param int $contextid
     * @param null|int $userid
     * @return bool|int
     * @throws \dml_exception
     */
    public function add_user_favourite_session($itemid, $contextid, $userid = null) {
        global $USER;

        if (is_null($userid)) {
            $userid = $USER->id;
        }

        return $this->add_user_favourite(
            'local_session',
            \local_mentor_core\session::FAVOURITE,
            $itemid,
            $contextid,
            $userid
        );
    }

    /**
     * Remove session to user's favourite.
     *
     * @param int $itemid
     * @param int $contextid
     * @param null|int $userid
     * @return bool
     * @throws \dml_exception
     */
    public function remove_user_favourite_session($itemid, $contextid, $userid = null) {
        global $USER;

        if (is_null($userid)) {
            $userid = $USER->id;
        }

        return $this->remove_user_favourite(
            'local_session',
            \local_mentor_core\session::FAVOURITE,
            $itemid,
            $contextid,
            $userid
        );
    }

    /**
     * Get session to user's favourite data.
     *
     * @param int $itemid
     * @param int $contextid
     * @param null|int $userid
     * @return \stdClass|false
     */
    public function get_user_favourite_session_data($itemid, $contextid, $userid = null) {
        global $USER;

        if (is_null($userid)) {
            $userid = $USER->id;
        }

        return $this->get_user_favourite(
            'local_session',
            \local_mentor_core\session::FAVOURITE,
            $itemid,
            $contextid,
            $userid
        );
    }

    /**
     * Get user preference
     *
     * @param int $userid
     * @param string $preferencename
     * @return false|mixed|\stdClass
     * @throws \dml_exception
     */
    public function get_user_preference($userid, $preferencename) {
        $record = $this->db->get_record('user_preferences', ['userid' => $userid, 'name' => $preferencename]);
        return $record ? $record->value : false;
    }

    /**
     * Set user preference
     *
     * @param int $userid
     * @param string $preferencename
     * @param mixed $value
     * @return bool
     * @throws \dml_exception
     */
    public function set_user_preference($userid, $preferencename, $value) {
        if ($preference = $this->db->get_record('user_preferences', ['userid' => $userid, 'name' => $preferencename])) {
            $preference->value = $value;
            $this->db->update_record('user_preferences', $preference);
        } else {
            $preference = new \stdClass();
            $preference->userid = $userid;
            $preference->name = $preferencename;
            $preference->value = $value;
            $this->db->insert_record('user_preferences', $preference);
        }

        return true;
    }

    /**
     * Check if user has a specific context role
     *
     * @param int $userid
     * @param string $rolename
     * @param int $contextid
     * @return bool
     * @throws \dml_exception
     */
    public function user_has_role_in_context($userid, $rolename, $contextid) {
        return $this->db->record_exists_sql('
            SELECT
                ra.id
            FROM
                {role_assignments} ra
            JOIN
                {role} r ON ra.roleid = r.id
            WHERE
                userid = :userid
                AND contextid = :contextid
                AND r.shortname = :rolename
        ', ['userid' => $userid, 'contextid' => $contextid, 'rolename' => $rolename]);
    }

    /**
     * Check if enrol user by course is enabled.
     *
     * @param int $courseid
     * @param int $userid
     * @return bool
     * @throws \dml_exception
     */
    public function has_enroll_user_enabled($courseid, $userid) {
        return $this->db->record_exists_sql('
            SELECT ue.*
            FROM {user_enrolments} ue
            JOIN {enrol} e ON e.id =  ue.enrolid
            WHERE
                e.courseid = :courseid
                AND ue.userid = :userid
                AND ue.status = 0
        ', array('courseid' => $courseid, 'userid' => $userid));
    }

    /**
     * Get library publication object by training id or original training id.
     *
     * @param int $trainingid
     * @return false|\stdClass
     * @throws \dml_exception
     */
    public function get_library_publication($trainingid, $by = 'originaltrainingid') {
        // Accepted column search.
        $acceptedby = [
            'originaltrainingid',
            'trainingid'
        ];

        // Not accepted column.
        if (!in_array($by, $acceptedby)) {
            return false;
        }

        return $this->db->get_record('library', array($by => $trainingid));
    }

    /**
     * Add or update training/library link.
     *
     * @param int $trainingid
     * @param int $originaltrainingid
     * @param int $userid
     * @return bool|int
     * @throws \dml_exception
     */
    public function publish_to_library($trainingid, $originaltrainingid, $userid) {
        // If link exist.
        if ($traininglibrary = $this->get_library_publication($originaltrainingid)) {
            $traininglibrary->trainingid = $trainingid;
            $traininglibrary->timemodified = time();
            $traininglibrary->userid = $userid;
            $this->db->update_record('library', $traininglibrary);
            return $traininglibrary->id;
        }

        // Add new link.
        $data = new \stdClass();
        $data->trainingid = $trainingid;
        $data->originaltrainingid = $originaltrainingid;
        $data->timecreated = time();
        $data->timemodified = time();
        $data->userid = $userid;
        return $this->db->insert_record('library', $data);
    }

    /**
     * get recyclebin category item by shortname.s
     *
     * @param string $shortname
     * @return bool|\stdClass
     * @throws \dml_exception
     */
    public function get_recyclebin_category_item($shortname) {
        return $this->db->get_record('tool_recyclebin_category', array('shortname' => $shortname));
    }

    /**
     * Checks if the user is already enrolled.
     *
     * @param int $instanceid
     * @param int $userid
     * @return bool
     * @throws \dml_exception
     */
    public function has_enrol_by_instance_id($instanceid, $userid) {
        global $DB;

        return $DB->record_exists('user_enrolments', array('userid' => $userid, 'enrolid' => $instanceid));
    }

    /**
     * Get course tutors
     *
     * @param int $contextid
     * @return array
     * @throws \dml_exception
     */
    public function get_course_tutors($contextid) {
        $sql = '
            SELECT DISTINCT(u.id), u.*
            FROM {user} u
            JOIN {role_assignments} ra ON ra.userid = u.id
            JOIN {role} r ON r.id = ra.roleid
            WHERE
                ra.contextid = :contextid
                AND r.shortname = :concepteur
        ';

        return $this->db->get_records_sql($sql, [
            'contextid' => $contextid,
            'concepteur' => \local_mentor_specialization\mentor_profile::ROLE_TUTEUR,
        ]);
    }

    /**
     * Get course formateurs
     *
     * @param int $contextid
     * @return array
     * @throws \dml_exception
     */
    public function get_course_formateurs($contextid) {
        $sql = '
            SELECT DISTINCT(u.id), u.*
            FROM {user} u
            JOIN {role_assignments} ra ON ra.userid = u.id
            JOIN {role} r ON r.id = ra.roleid
            WHERE
                ra.contextid = :contextid
                AND r.shortname = :concepteur
        ';

        return $this->db->get_records_sql($sql, [
            'contextid' => $contextid,
            'concepteur' => \local_mentor_specialization\mentor_profile::ROLE_FORMATEUR,
        ]);
    }

    /**
     * Get course demonstrateurs
     *
     * @param int $contextid
     * @return array
     * @throws \dml_exception
     */
    public function get_course_demonstrateurs($contextid) {
        $sql = '
            SELECT DISTINCT(u.id), u.*
            FROM {user} u
            JOIN {role_assignments} ra ON ra.userid = u.id
            JOIN {role} r ON r.id = ra.roleid
            WHERE
                ra.contextid = :contextid
                AND r.shortname = :concepteur
        ';

        return $this->db->get_records_sql($sql, [
            'contextid' => $contextid,
            'concepteur' => \local_mentor_specialization\mentor_profile::ROLE_PARTICIPANTDEMONSTRATION,
        ]);
    }

    /**
     * Delete all H5P owners in database.
     *
     * @param int $contextid
     * @return void
     * @throws \dml_exception
     */
    public function remove_user_owner_h5p_file($contextid) {
        $this->db->execute('
            UPDATE {files}
            SET userid = null
            WHERE id IN (
                SELECT f.id
                FROM {files} f
                         JOIN {context} c ON c.id = f.contextid
                WHERE (c.id = ' . $contextid . ' OR c.path like \'%/' . $contextid . '/%\') AND
                    f.mimetype like \'application/zip.h5p\'
            )'
        );
    }
}
