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
 * local_mentor_specialization settings
 *
 * @package    local_mentor_specialization
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     Mounir <mounir.ganem@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

// Ensure the configurations for this site are set.
if ($hassiteconfig) {
    // Create the new settings page.
    $settings = new admin_settingpage('local_mentor_specialization', get_string('pluginname', 'local_mentor_specialization'));

    if ($ADMIN->fulltree) {

        // Add collections field.
        $settings->add(new admin_setting_configtextarea(
                'local_mentor_specialization/collections',
                get_string('collections', 'local_mentor_specialization'),
                get_string('collections_help', 'local_mentor_specialization'),
                '',
                PARAM_TEXT
        ));

        // Add video field.
        $settings->add(new admin_setting_configtextarea(
                'local_mentor_specialization/videodomains',
                get_string('videodomains', 'local_mentor_specialization'),
                get_string('videodomains_help', 'local_mentor_specialization'),
                'https://video.mentor.gouv.fr',
                PARAM_TEXT
        ));
    }

    $ADMIN->add('localplugins', $settings);

    // Add link to ldap cleanup.
    $ADMIN->add('server', new admin_externalpage('clear_ldap',
            'Nettoyage du LDAP',
            $CFG->wwwroot . '/local/mentor_specialization/pages/clear_ldap.php'));
}
