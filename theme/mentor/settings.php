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
 * @package   theme_mentor
 * @copyright 2021 Edunao SAS (contact@edunao.com)
 * @author    mounir <mounir.ganem@edunao.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    // Texte d'information en début de footer.
    $textinfofootersetting = new admin_setting_configtext(
            'theme_mentor/textinfofooter',
            get_string('textinfofooter', 'theme_mentor'),
            get_string('textinfofooter_desc', 'theme_mentor'),
            get_string('textinfofooterdefault', 'theme_mentor'),
            PARAM_TEXT
    );

    // About link.
    $aboutsetting = new admin_setting_configtext(
            'theme_mentor/about',
            get_string('about', 'theme_mentor'),
            get_string('about_desc', 'theme_mentor'),
            $CFG->wwwroot . '/local/staticpage/view.php?page=ensavoirplus',
            PARAM_URL
    );

    // Legal notice link.
    $legalnoticesetting = new admin_setting_configtext(
            'theme_mentor/legalnotice',
            get_string('legalnotice', 'theme_mentor'),
            get_string('legalnotice_desc', 'theme_mentor'),
            $CFG->wwwroot . '/local/staticpage/view.php?page=mentionslegales',
            PARAM_URL
    );

    // FAQ.
    $faqsetting = new admin_setting_configtext(
            'theme_mentor/faq',
            get_string('faq', 'theme_mentor'),
            get_string('faq_desc', 'theme_mentor'),
            $CFG->wwwroot . '/local/staticpage/view.php?page=faq',
            PARAM_URL
    );

    // Liens extérieur à Mentor.
    $externallinkssetting = new admin_setting_configtext(
            'theme_mentor/externallinks',
            get_string('externallinks', 'theme_mentor'),
            get_string('externallinks_desc', 'theme_mentor'),
            'legifrance.gouv.fr|gouvernement.fr|service-public.fr|data.gouv.fr',
            PARAM_TEXT
    );

    // Mentor version number.
    $versionnumbersetting = new admin_setting_configtext(
            'theme_mentor/versionnumber',
            get_string('versionnumber', 'theme_mentor'),
            get_string('versionnumber_desc', 'theme_mentor'),
            '',
            PARAM_TEXT
    );

    // Mentor copyright.
    $mentorlicencesetting = new admin_setting_configtext(
            'theme_mentor/mentorlicence',
            get_string('mentorlicence', 'theme_mentor'),
            get_string('mentorlicence_desc', 'theme_mentor'),
            get_string('mentorlicencedefault', 'theme_mentor'),
            PARAM_RAW
    );

    // Site map link.
    $sitemapsetting = new admin_setting_configtext(
            'theme_mentor/sitemap',
            get_string('sitemap', 'theme_mentor'),
            get_string('sitemap_desc', 'theme_mentor'),
            $CFG->wwwroot . '/local/staticpage/view.php?page=plandusite',
            PARAM_URL
    );

    // Accessibility link.
    $accessibilitysetting = new admin_setting_configtext(
            'theme_mentor/accessibility',
            get_string('accessibility', 'theme_mentor'),
            get_string('accessibility_desc', 'theme_mentor'),
            '',
            PARAM_URL
    );

    // Personal data link.
    $personaldatasetting = new admin_setting_configtext(
            'theme_mentor/personaldata',
            get_string('personaldata', 'theme_mentor'),
            get_string('personaldata_desc', 'theme_mentor'),
            $CFG->wwwroot . '/local/staticpage/view.php?page=donneespersonnelles',
            PARAM_URL
    );

    // Plugin agent connect identifier.
    $agentconnectidentifiersetting = new admin_setting_configtext(
            'theme_mentor/agentconnectidentifier',
            get_string('agentconnectidentifier', 'theme_mentor'),
            get_string('agentconnectidentifier_desc', 'theme_mentor'),
            'oauth2',
            PARAM_TEXT
    );

    $settings->add($textinfofootersetting);
    $settings->add($aboutsetting);
    $settings->add($legalnoticesetting);
    $settings->add($faqsetting);
    $settings->add($externallinkssetting);
    $settings->add($versionnumbersetting);
    $settings->add($mentorlicencesetting);
    $settings->add($sitemapsetting);
    $settings->add($accessibilitysetting);
    $settings->add($personaldatasetting);
    $settings->add($agentconnectidentifiersetting);
}
