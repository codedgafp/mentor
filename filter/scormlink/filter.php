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
 * This filter update scorm links used in labels
 *
 * @package    filter
 * @subpackage scormlink
 * @copyright  2022 Edunao SAS (contact@edunao.com)
 * @author     adrien <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class filter_scormlink extends moodle_text_filter {

    public function filter($text, array $options = array()) {
        global $DB;

        // The filter is applicable in courses only.
        $coursectx = $this->context->get_course_context(false);
        if (!$coursectx) {
            return $text;
        }

        $courseid = $coursectx->instanceid;

        $regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";

        // The text does not contain any hyperlinks.
        if (!preg_match_all("/$regexp/siU", $text, $matches, PREG_SET_ORDER)) {
            return $text;
        }

        // Iterate on each hyperlinks.
        foreach ($matches as $match) {

            // If the link is not a scorm activity link, continue.
            if (strpos($match[2], '/mod/scorm/view.php') == false) {
                continue;
            }

            // Extract cmid.
            $query = parse_url($match[2], PHP_URL_QUERY);
            parse_str($query, $params);
            $cmid = $params['id'];

            // Get the scorm instance.
            $scorm = $DB->get_record_sql('
                    SELECT s.*
                    FROM {scorm} s
                    JOIN {course_modules} cm ON cm.instance = s.id
                    WHERE
                    cm.id = :cmid
                ', ['cmid' => $cmid]);

            // Check if the scorm instance exists.
            if (!$scorm) {
                continue;
            }

            // Check if the scorm must be opened in a new tab.
            if ($scorm->popup != 1) {
                continue;
            }

            $newmatch = $match[0];
            $newurl   = $match[2];

            // Replace href by onclick.
            $replacehref = str_replace('href="', 'href="#" onclick="window.open(\'', $newmatch);
            $newmatch    = str_replace($newmatch, $replacehref, $newmatch);

            // Add JS at the end of the URL.
            $newmatch = str_replace($match[2], $match[2] . "','_blank').focus();return false;", $newmatch);

            // Replace link.
            $replaceurl = str_replace("/mod/scorm/view.php?id=", "/local/mentor_core/pages/scorm.php?cmid=", $newurl);
            $newmatch   = str_replace($newurl, $replaceurl, $newmatch);

            // Replace occurency in the global text.
            $text = str_replace($match[0], $newmatch, $text);

        }

        return $text;
    }
}
