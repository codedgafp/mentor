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
 * Mentor log store lang strings.
 *
 * @package    logstore_mentor
 * @copyright  2021 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Mentor logstore';
$string['pluginname_desc'] = 'A log plugin stores log entries in a Moodle database table.';

$string['privacy:metadata:log'] = 'A collection of past events';
$string['privacy:metadata:log:userlogid'] = 'Table user log reference';
$string['privacy:metadata:log:sessionlogid'] = 'Table session log reference';
$string['privacy:metadata:log:timecreated'] = 'First time when user see session course today';
$string['privacy:metadata:log:lastview'] = 'Last time when user see session course today';
$string['privacy:metadata:log:numberview'] = 'Number of times the user has been on the session';
$string['privacy:metadata:log:completed'] = 'Tells if the user has completed the course';

$string['privacy:metadata:loghistory'] = 'A history of collection of past events';
$string['privacy:metadata:loghistory:userlogid'] = 'Table user log reference';
$string['privacy:metadata:loghistory:sessionlogid'] = 'Table session log reference';
$string['privacy:metadata:loghistory:timecreated'] = 'First time when user see session course today';
$string['privacy:metadata:loghistory:lastview'] = 'Last time when user see session course today';
$string['privacy:metadata:loghistory:numberview'] = 'Number of times the user has been on the session';
$string['privacy:metadata:loghistory:completed'] = 'Tells if the user has completed the course';

$string['privacy:metadata:logcollection'] = 'A collection of past collection events';
$string['privacy:metadata:logcollection:name'] = 'Collection name';
$string['privacy:metadata:logcollection:sessionlogid'] = 'Table session log reference';

$string['privacy:metadata:logsession'] = 'A collection of past events session data';
$string['privacy:metadata:logsession:sessionid'] = 'Session id';
$string['privacy:metadata:logsession:space'] = 'Main entity session';
$string['privacy:metadata:logsession:status'] = 'Session status';

$string['privacy:metadata:loguser'] = 'A collection of past events session data';
$string['privacy:metadata:loguser:userid'] = 'User id';
$string['privacy:metadata:loguser:entity'] = 'Main entity user';
$string['privacy:metadata:loguser:trainer'] = 'If is session trainer';

$string['taskcleanup'] = 'Log table cleanup';
