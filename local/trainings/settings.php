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
 * local_trainings settings
 *
 * @package    local_trainings
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     Nabil Hamdi <nabil.hamdi@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

// Ensure the configurations for this site are set.
if ($hassiteconfig) {

    // Create the new settings page.
    $settings = new admin_settingpage('local_trainings', get_string('pluginname', 'local_trainings'));

    if ($ADMIN->fulltree) {
        $settings->add(new admin_setting_configtext('local_trainings/rime_link', get_string('rime_link', 'local_trainings'),
            get_string('ref_link_help', 'local_trainings'),
            'https://www.fonction-publique.gouv.fr/biep/repertoire-interministeriel-des-metiers-de-letat'));

        $settings->add(new admin_setting_configtext('local_trainings/rmm_link', get_string('rmm_link', 'local_trainings'),
            get_string('ref_link_help', 'local_trainings'),
            'https://www.economie.gouv.fr/files/repertoire-metiers-ministeriels.pdf'));
    }

    // Create.
    $ADMIN->add('localplugins', $settings);

}


