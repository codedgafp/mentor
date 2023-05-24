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
 * Plugin observers
 *
 * @package    local_mentor_specialization
 * @copyright  2021 Edunao SAS (contact@edunao.com)
 * @author     adrien <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/mentor_core/api/entity.php');

class local_mentor_specialization_observer {

    /**
     * Assign reflocalnonediteur role on the main entity when the referentlocal role is assigned on a sub entity
     *
     * @param \core\event\role_assigned $event
     * @throws Exception
     */
    public static function assign_reflocalnonediteur(\core\event\role_assigned $event) {
        global $DB;
        $contextlevel = $event->contextlevel;

        // The assignment is not on a coursecat context level.
        if ($contextlevel != CONTEXT_COURSECAT) {
            return false;
        }

        $userid = $event->relateduserid;
        $roleid = $event->objectid;
        $entityid = $event->contextinstanceid;

        try {
            $entity = \local_mentor_core\entity_api::get_entity($entityid);
        } catch (\Exception $e) {
            return false;
        }

        // Not assign "reflocal non editeur" role to user if is main entity.
        if ($entity->is_main_entity()) {
            return false;
        }

        $reflocal = $DB->get_record('role', ['shortname' => 'referentlocal']);

        if (!$reflocal || ($reflocal->id != $roleid)) {
            return false;
        }

        $reflocalnonediteur = $DB->get_record('role', ['shortname' => 'reflocalnonediteur']);

        $parent = $entity->get_main_entity();

        if ($entity->parentid != $entityid) {
            role_assign($reflocalnonediteur->id, $userid, $parent->get_context()->id);
        }

        return true;
    }

    /**
     * Unassign reflocalnonediteur role on the main entity when the referentlocal role is unassigned on all sub entities
     *
     * @param \core\event\role_assigned $event
     * @throws Exception
     */
    public static function unassign_reflocalnonediteur(\core\event\role_unassigned $event) {
        global $DB;

        $contextlevel = $event->contextlevel;

        // The assignment is not on a coursecat context level.
        if ($contextlevel != CONTEXT_COURSECAT) {
            return false;
        }

        $userid = $event->relateduserid;
        $roleid = $event->objectid;
        $entityid = $event->contextinstanceid;

        $reflocal = $DB->get_record('role', ['shortname' => 'referentlocal']);

        if (!$reflocal || ($reflocal->id != $roleid)) {
            return false;
        }

        $reflocalnonediteur = $DB->get_record('role', ['shortname' => 'reflocalnonediteur']);

        $entity = \local_mentor_core\entity_api::get_entity($entityid);

        $parent = $entity->get_main_entity();

        // Check if we are in a sub entity.
        if ($parent->id == $entity->id) {
            return false;
        }

        // User has not the reflocalnonediteur role.
        if (!user_has_role_assignment($userid, $reflocalnonediteur->id, $parent->get_context()->id)) {
            return false;
        }

        $subentities = $parent->get_sub_entities();

        foreach ($subentities as $subentity) {
            // User has role referentlocal on an other subentity, so do nothing.
            if (($subentity->id != $entityid) && user_has_role_assignment($userid, $roleid, $subentity->get_context()->id)) {
                return false;
            }
        }

        // Unsassign the reflocalnonediteur on the parent entity.
        role_unassign($reflocalnonediteur->id, $userid, $parent->get_context()->id);

        return true;
    }

    /**
     * Delete the user from LDAP
     *
     * @param \core\event\user_deleted $event
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function delete_user(\core\event\user_deleted $event) {
        global $CFG;

        $pluginlib = $CFG->dirroot . '/auth/ldap_syncplus/auth.php';

        // Check if the ldap_syncplus plugin exists.
        if (!is_file($pluginlib)) {
            return;
        }

        require_once($CFG->dirroot . '/auth/ldap_syncplus/auth.php');

        $config = get_config('auth_ldap_syncplus', 'contexts');

        // Check if the ldap_syncplus plugin has been configured.
        if (empty($config)) {
            return;
        }

        // Get the user email.
        $email = $event->other['username'];

        try {
            // Open an ldap connection.
            $auth = new auth_plugin_ldap_syncplus();
            $con = $auth->ldap_connect();

            $dn = 'cn=' . $email . ',' . $config;

            // Delete the user.
            ldap_delete($con, $dn);

            // Close the ldap connection.
            $auth->ldap_close();
        } catch (Exception $e) {
            print_error($e->getMessage());
        }
    }

    /**
     *
     * Sync profile into ldap when role assigned
     *
     * @param \core\event\role_assigned $event
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function sync_profile_role_assigned_ldap(\core\event\role_assigned $event) {
        // LDAP profile sync.
        $userid = $event->relateduserid;

        $profile = \local_mentor_core\profile_api::get_profile($userid);

        $profile->set_highestrole_into_profile();
    }

    /**
     *
     * Sync profile into ldap when role assigned
     *
     * @param \core\event\role_assigned $event
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function sync_profile_role_unassigned_ldap(\core\event\role_unassigned $event) {
        // LDAP profile sync.

        $userid = $event->relateduserid;

        $profile = \local_mentor_core\profile_api::get_profile($userid);

        $profile->set_highestrole_into_profile();
    }

    /**
     *
     * Sync profile into ldap when role assigned
     *
     * @param \core\event\role_assigned $event
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function sync_profile_role_login_ldap(\core\event\user_loggedin $event) {
        // LDAP profile sync.

        $userid = $event->userid;

        $profile = \local_mentor_core\profile_api::get_profile($userid);

        $profile->set_highestrole_into_profile();
    }

    /**
     *
     * Remove required user info data if is empty when user is created
     *
     * @param \core\event\user_created $event
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function remove_required_user_info_data_if_empty(\core\event\user_created $event) {
        global $DB;

        $requireddatafields = $DB->get_records_sql('
            SELECT uid.*
            FROM {user_info_data} uid
            JOIN {user_info_field} uif ON uif.id = uid.fieldid
            WHERE uif.required = 1 AND uid.userid = :userid',
            array('userid' => $event->relateduserid)
        );

        foreach ($requireddatafields as $requireddatafield) {
            if (!empty($requireddatafield->data)) {
                continue;
            }

            $DB->delete_records('user_info_data', array('id' => $requireddatafield->id));
        }
    }

    /**
     * Create and send a mail if the entity manager changes
     * the user's primary and/or secondary entity.
     *
     * @param \core\event\user_updated $event
     * @return int|false
     * @throws Exception
     */
    public static function manager_change_user_entities_notification(\core\event\user_updated $event) {
        global $CFG, $USER;

        require_once($CFG->dirroot . '/local/profile/lib.php');

        // Check if other data exist.
        if (!isset($event->other) || is_null($event->other)) {
            return false;
        }

        // Decode other data.
        $otherdata = json_decode($event->other);

        // Check if old and new user data exist.
        if (!isset($otherdata->old) || !isset($otherdata->new)) {
            return false;
        }

        // Get other and new user data.
        $olddatauser = $otherdata->old;
        $newdatauser = $otherdata->new;

        // Get old main user entitiy.
        if (!$entity = \local_mentor_core\entity_api::get_entity_by_name($olddatauser->profile_field_mainentity)) {
            return false;
        }

        // Check if local user is entity manager and is different with the user it modifies.
        if ($USER->id === $olddatauser->id || !has_capability('local/entities:manageentity', $entity->get_context())) {
            return false;
        }

        // Get old and new secondary entities.
        $oldsecondaryentities = $olddatauser->profile_field_secondaryentities;
        $newsecondaryentities = explode(', ', $newdatauser->profile_field_secondaryentities);

        // Check if old and new entities (main and secondary) are different.
        if (
            $olddatauser->profile_field_mainentity === $newdatauser->profile_field_mainentity &&
            empty(array_diff($oldsecondaryentities, $newsecondaryentities)) &&
            empty(array_diff($newsecondaryentities, $oldsecondaryentities))
        ) {
            return false;
        }

        // Create and send the email.

        // Get recipient and sender.
        $creator = \core_user::get_user($olddatauser->id);
        $supportuser = \core_user::get_support_user();

        // Create content.
        $content = '';

        // If it exist, add new main entity to mail.
        if (!empty($newdatauser->profile_field_mainentity)) {
            $mainentity = \local_mentor_core\entity_api::get_entity_by_name($newdatauser->profile_field_mainentity);
            $content .= "Entité principale : " . $mainentity->name . "\n";
        }

        // If they exist, add all secondary entities to mail.
        if (!empty($newdatauser->profile_field_secondaryentities)) {
            $content .= "Entité(s) secondaire(s) : \n";
            $secondaryentities = explode(', ', $newdatauser->profile_field_secondaryentities);

            foreach ($secondaryentities as $secondaryentityname) {
                $secondaryentity = \local_mentor_core\entity_api::get_entity_by_name($secondaryentityname);
                $content .= "  - " . $secondaryentity->name . "\n";
            }
        }

        // Finally mail content.
        $content = get_string('email_user_entity_update_content', 'local_mentor_specialization', array(
            'entitylist' => $content,
            'wwwroot' => $CFG->wwwroot
        ));
        $contenthtml = text_to_html($content, false, false, true);

        // Send mail.
        email_to_user(
            $creator,
            $supportuser,
            get_string('email_user_entity_update_object', 'local_mentor_specialization'),
            $content,
            $contenthtml
        );
    }

    /**
     * Assign "visiteurbiblio" role on the library entity when entity admin role is given to the user
     *
     * @param \core\event\role_assigned $event
     * @throws Exception
     */
    public static function assign_visiteurbiblio(\core\event\role_assigned $event) {
        global $DB;
        $contextlevel = $event->contextlevel;

        // The assignment is not on a coursecat context level.
        if ($contextlevel != CONTEXT_COURSECAT) {
            return false;
        }

        // Get user id.
        $userid = $event->relateduserid;

        // Check if user has already library access.
        if (\local_mentor_core\library_api::user_has_access($userid)) {
            return true;
        }

        // Get role allow.
        $roleid = $event->objectid;

        $dbi = \local_mentor_specialization\database_interface::get_instance();

        // Get "visiteurbiblio" role.
        $rolevisiteurbiblio = $dbi->get_role_by_name(\local_mentor_specialization\mentor_library::ROLE_VISITEURBIBLIO);

        // Get library.
        $library = \local_mentor_core\library_api::get_library();

        // Checks if the given role is a role that allows access to the library.
        foreach (\local_mentor_specialization\mentor_library::ROLES_ACCESS as $rolehortname) {
            if ($DB->get_record('role', array('id' => $roleid, 'shortname' => $rolehortname))) {
                role_assign($rolevisiteurbiblio->id, $userid, $library->get_context()->id);
                return true;
            }
        }

        return false;
    }

    /**
     * Unassigned "visiteurbiblio" role on the library entity
     * when all administrator roles on the entity are removed from the user.
     *
     * @param \core\event\role_unassigned $event
     * @throws Exception
     */
    public static function unassigned_visiteurbiblio(\core\event\role_unassigned $event) {
        $contextlevel = $event->contextlevel;

        // The assignment is not on a coursecat context level.
        if ($contextlevel != CONTEXT_COURSECAT) {
            return false;
        }

        // Get user id.
        $userid = $event->relateduserid;

        // Check if user not has library access.
        if (!\local_mentor_core\library_api::user_has_access($userid)) {
            return true;
        }

        // Checks if the given role is a role that allows access to the library.
        if (local_mentor_specialization_check_if_user_has_role_to_access_the_library($userid)) {
            return false;
        }

        $dbi = \local_mentor_specialization\database_interface::get_instance();

        // Get "visiteurbiblio" role.
        $rolevisiteurbiblio = $dbi->get_role_by_name(\local_mentor_specialization\mentor_library::ROLE_VISITEURBIBLIO);

        // Get library.
        $library = \local_mentor_core\library_api::get_library();

        // Unassign "visiteurbiblio" role to user.
        role_unassign($rolevisiteurbiblio->id, $userid, $library->get_context()->id);
        return true;
    }

    /**
     * Check user role assign and enrol type use
     * to session to know if the mail is sent
     *
     * @param \core\event\role_assigned $event
     * @return bool
     * @throws Exception
     */
    public static function enrol_session_send_mail(\core\event\role_assigned $event) {
        global $DB;

        $userid = $event->relateduserid;
        $roleid = $event->objectid;
        $courseid = $event->contextinstanceid;
        $contextlevel = $event->contextlevel;

        $rolecondition = [
            'participant',
            'formateur',
            'tuteur'
        ];

        $enrolscondition = [
            'manual',
            'sirh'
        ];

        // Check if is course context.
        if ($contextlevel !== CONTEXT_COURSE) {
            return false;
        }

        // Check if is right role assign for send mail.
        $roleassign = $DB->get_record('role', ['id' => $roleid]);

        if (!in_array($roleassign->shortname, $rolecondition)) {
            return false;
        }

        // Check if is session.
        $dbi = \local_mentor_specialization\database_interface::get_instance();

        if (!$sessiondata = $dbi->get_session_by_course_id($courseid)) {
            return false;
        }

        // Check if last enrol link with user and course id is right type.
        // INFO : There is no link between enrol and role assignment.
        $lastenrol = $DB->get_record_sql('
            SELECT e.*
            FROM {enrol} e
            JOIN {user_enrolments} ue ON ue.enrolid = e.id
            WHERE ue.userid = :userid AND
                e.courseid = :courseid
            ORDER BY ue.timemodified DESC
            ', [
            'userid' => $userid,
            'courseid' => $courseid
        ], IGNORE_MULTIPLE);

        if (!in_array($lastenrol->enrol, $enrolscondition)) {
            return false;
        }

        // Data for message.
        $session = \local_mentor_core\session_api::get_session($sessiondata->id);
        $user = core_user::get_user($userid);
        $infodata = new \stdClass();
        $infodata->firstname = $user->firstname;
        $infodata->lastname = $user->lastname;
        $infodata->rolename = strtolower($roleassign->name);
        $infodata->fullname = $session->fullname ?: $session->trainingname;
        $sessionstartdate = $session->sessionstartdate;
        $dtz = new \DateTimeZone('Europe/Paris');
        $startdate = new \DateTime("@$sessionstartdate");
        $startdate->setTimezone($dtz);
        $infodata->startdate = $startdate->format('d/m/Y');
        $infodata->dashboardurl = (new \moodle_url('/my'))->out(false);

        // Message text.
        $messagetext = $session->status !== \local_mentor_core\session::STATUS_IN_PREPARATION ?
            get_string('email_enrol_user_session_content', 'local_mentor_specialization', $infodata) :
            get_string('email_enrol_user_session_content_no_start_date', 'local_mentor_specialization', $infodata);

        // Message subject.
        $subject = get_string('email_enrol_user_session_object', 'local_mentor_specialization', $infodata->fullname);

        // Message HTML.
        $messagehtml = text_to_html($messagetext, false, false, true);

        // Send a report email to user.
        $supportuser = \core_user::get_support_user();
        email_to_user($user, $supportuser, $subject, $messagetext, $messagehtml);
        return true;
    }
}
