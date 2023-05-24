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
 * A two column layout for the mentor theme.
 *
 * @package   theme_mentor
 * @copyright 2016 Damyon Wiese
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

user_preference_allow_ajax_update('drawer-open-nav', PARAM_ALPHA);
require_once($CFG->libdir . '/behat/lib.php');

// Close the nav drawer on every pages.
$navdraweropen = false;

$extraclasses = [];
if ($navdraweropen) {
    $extraclasses[] = 'drawer-open-left';
}
$bodyattributes = $OUTPUT->body_attributes($extraclasses);
$blockshtml = $OUTPUT->blocks('side-pre');
$hasblocks = strpos($blockshtml, 'data-block=') !== false;
$buildregionmainsettings = !$PAGE->include_region_main_settings_in_header_actions();
// If the settings menu will be included in the header then don't add it here.
$regionmainsettingsmenu = $buildregionmainsettings ? $OUTPUT->region_main_settings_menu() : false;
$templatecontext = [
        'sitename' => format_string($SITE->shortname, true,
                ['context' => context_course::instance(SITEID), "escape" => false]),
        'output' => $OUTPUT,
        'sidepreblocks' => $blockshtml,
        'hasblocks' => $hasblocks,
        'bodyattributes' => $bodyattributes,
        'navdraweropen' => $navdraweropen,
        'regionmainsettingsmenu' => $regionmainsettingsmenu,
        'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu)
];

$nav = $PAGE->flatnav;
$templatecontext['flatnavigation'] = $nav;
$templatecontext['firstcollectionlabel'] = $nav->get_collectionlabel();

$templatecontext['hasprevbutton'] = 0;

if ($PAGE->has_set_url()) {

    // Back to the course for activity.
    if (strpos($this->page->url, '/mod/') !== false && $PAGE->course->format != 'singleactivity') {
        $templatecontext['hasprevbutton'] = 1;

        $templatecontext['prevstepurl'] = (new moodle_url('/course/view.php',
                ['id' => $PAGE->course->id, 'section' => $PAGE->cm->sectionnum]
        ))->out();
        $templatecontext['prevstetitle'] = get_string('prevstep', 'theme_mentor');
    }

    // Back to the catalog for trainong catalog.
    if (strpos($this->page->url, '/local/catalog/pages/') !== false) {
        $templatecontext['hasprevbutton'] = 1;
        $templatecontext['prevstepurl'] = (new moodle_url('/local/catalog/index.php'))->out();
        $templatecontext['prevstetitle'] = get_string('prevstepcatalog', 'theme_mentor');
    }

    // Back to the dashboard for training sheet.
    if (strpos($this->page->url, '/local/trainings/pages/training.php') !== false) {
        $templatecontext['hasprevbutton'] = 1;
        $templatecontext['prevstepurl'] = (new moodle_url('/'))->out();
        $templatecontext['prevstetitle'] = get_string('prevstepdashboard', 'theme_mentor');
    }

    // Back to the training sheet page.
    if (strpos($this->page->url, '/local/trainings/pages/preview.php') !== false) {
        $trainingid = required_param('trainingid', PARAM_INT);
        $templatecontext['hasprevbutton'] = 1;
        $templatecontext['prevstepurl'] = (new moodle_url('/local/trainings/pages/update_training.php',
                ['trainingid' => $trainingid]))->out();
        $templatecontext['prevstetitle'] = get_string('closetrainingpreview', 'local_trainings');
    }

    // Back to the library page.
    if (strpos($this->page->url, '/local/library/pages/training.php') !== false) {
        $templatecontext['hasprevbutton'] = 1;
        $templatecontext['prevstepurl'] = (new moodle_url('/local/library/index.php'))->out();
        $templatecontext['prevstetitle'] = get_string('libraryreturn', 'theme_mentor');
    }
}
echo $OUTPUT->render_from_template('theme_boost/columns2', $templatecontext);

