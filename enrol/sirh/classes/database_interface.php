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
 * @package    enrol_sirh
 * @copyright  2022 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_sirh;

defined('MOODLE_INTERNAL') || die();

class database_interface {

    /**
     * @var \moodle_database
     */
    protected $db;

    /**
     * @var self
     */
    protected static $instance;

    public function __construct() {

        global $DB;

        $this->db = $DB;
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

    /**
     * Get user object with this email.
     *
     * @param false|string $email
     */
    public function get_user_by_email($email) {
        return $this->db->get_record('user', array('email' => $email));
    }

    /**
     * Get enrolment instance users
     *
     * @param int $instanceid
     * @return \stdClass[]
     * @throws \dml_exception
     */
    public function get_instance_users_sirh($instanceid) {
        global $DB;

        return $DB->get_records_sql('
            SELECT u.*
            FROM {user} u
            JOIN {user_enrolments} ue ON ue.userid = u.id
            WHERE
                ue.enrolid = :instanceid
        ', ['instanceid' => $instanceid]);
    }

    /**
     * Return enrol SIRH instance id.
     *
     * @param int $courseid
     * @param string $sirh
     * @param string $sirhtraining
     * @param string $sirhsession
     * @return false|\stdClass
     * @throws \dml_exception
     */
    public function get_instance_sirh($courseid, $sirh, $sirhtraining, $sirhsession) {
        return $this->db->get_record_sql('
            SELECT e.*
            FROM {enrol} e
            JOIN {course} c ON c.id = e.courseid
            JOIN {session} s ON s.courseshortname = c.shortname
            WHERE e.courseid = :courseid
                AND e.customchar1 = :sirh
                AND e.customchar2 = :sirhtraining
                AND e.customchar3 = :sirhsession
        ', array(
            'courseid'     => $courseid,
            'sirh'         => $sirh,
            'sirhtraining' => $sirhtraining,
            'sirhsession'  => $sirhsession,
        ));
    }

    /**
     * Get SIRH instance object.
     *
     * @param int $instanceid
     * @return false|mixed
     * @throws \dml_exception
     */
    public function get_instance_sirh_by_id($instanceid) {
        return $this->db->get_record_sql('
            SELECT *
            FROM {enrol} e
            WHERE e.id = :instanceid
        ', ['instanceid' => $instanceid]);
    }

    /**
     * Get course group object by name.
     *
     * @param int $courseid
     * @param string $groupname
     * @return false|mixed
     * @throws \dml_exception
     */
    public function get_course_group_by_name($courseid, $groupname) {
        // Return group object if there exist.
        return $this->db->get_record_sql('
        SELECT g.*
        from {groups} g
        WHERE g.courseid = :courseid AND
            ' . $this->db->sql_like('g.name', ':defaultname')
            , array(
                'courseid' => $courseid,
                'defaultname' => $this->db->sql_like_escape($groupname)
            ));
    }

    /**
     * Check if user enrolment exist.
     *
     * @param int $instanceid
     * @param int $userid
     * @return bool
     * @throws \dml_exception
     */
    public function user_enrolment_exist($instanceid, $userid) {
        return $this->db->record_exists(
            'user_enrolments',
            array('enrolid' => $instanceid, 'userid' => $userid)
        );
    }

    /**
     * Return all instance SIRH object.
     *
     * @return \stdClass[];
     * @throws \dml_exception
     */
    public function get_all_instance_sirh() {
        return $this->db->get_records_sql('
            SELECT e.*, c.id courseid, s.courseshortname sessionname
            FROM {session} s
            JOIN {course} c ON s.courseshortname = c.shortname
            JOIN {enrol} e ON c.id = e.courseid
            WHERE
                e.enrol = :sirh
        ', ['sirh' => 'sirh']);
    }
}
