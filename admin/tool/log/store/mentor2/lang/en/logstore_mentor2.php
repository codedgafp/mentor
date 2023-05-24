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

$string['pluginname'] = 'Mentor logstore 2';
$string['pluginname_desc'] = 'A log plugin stores log entries in a Moodle database table.';

$string['privacy:metadata:log'] = 'A collection of past events';
$string['privacy:metadata:log:userlogid'] = 'Table user log reference';
$string['privacy:metadata:log:sessionlogid'] = 'Table session log reference';
$string['privacy:metadata:log:timecreated'] = 'First time when user see session course today';
$string['privacy:metadata:log:lastview'] = 'Last time when user see session course today';
$string['privacy:metadata:log:numberview'] = 'Number of times the user has been on the session';

$string['privacy:metadata:loghistory'] = 'A history of collection of past events';
$string['privacy:metadata:loghistory:userlogid'] = 'Table user log reference';
$string['privacy:metadata:loghistory:sessionlogid'] = 'Table session log reference';
$string['privacy:metadata:loghistory:timecreated'] = 'First time when user see session course today';
$string['privacy:metadata:loghistory:lastview'] = 'Last time when user see session course today';
$string['privacy:metadata:loghistory:numberview'] = 'Number of times the user has been on the session';

$string['privacy:metadata:logcollection'] = 'A collection of past collection events';
$string['privacy:metadata:logcollection:name'] = 'Collection name';

$string['privacy:metadata:logregion'] = 'A collection of past region events';
$string['privacy:metadata:logregion:name'] = 'Region name';

$string['privacy:metadata:logentity'] = 'A collection of past entity names';
$string['privacy:metadata:logentity:entityid'] = 'Entity id';
$string['privacy:metadata:logentity:name'] = 'Entity name';

$string['privacy:metadata:entityreg'] = 'A collection of past entity regions';
$string['privacy:metadata:entityreg:entitylogid'] = 'Table entity log reference';
$string['privacy:metadata:entityreg:regionlogid'] = 'Table region log reference';

$string['privacy:metadata:sesscoll'] = 'A collection of past sessions collections';
$string['privacy:metadata:sesscoll:sessionlogid'] = 'Table session log reference';
$string['privacy:metadata:sesscoll:collectionlogid'] = 'Table collection log reference';

$string['privacy:metadata:logsession'] = 'A collection of past events session data';
$string['privacy:metadata:logsession:sessionid'] = 'Table session reference';
$string['privacy:metadata:logsession:entitylogid'] = 'Table entity log reference';
$string['privacy:metadata:logsession:status'] = 'Session status';
$string['privacy:metadata:logsession:shared'] = 'Is the session shared to other entities';
$string['privacy:metadata:logsession:trainingentitylogid'] = 'Table entity log reference for the session training';
$string['privacy:metadata:logsession:trainingsubentitylogid'] = 'Table entity log reference for the session training';
$string['privacy:metadata:logsession:subentitylogid'] = 'Table entity log reference';

$string['privacy:metadata:loguser'] = 'A collection of past events session data';
$string['privacy:metadata:loguser:userid'] = 'User id';
$string['privacy:metadata:loguser:entitylogid'] = 'Table entity log reference';
$string['privacy:metadata:loguser:trainer'] = 'If is session trainer';
$string['privacy:metadata:loguser:status'] = 'User status';
$string['privacy:metadata:loguser:category'] = 'User category';
$string['privacy:metadata:loguser:regionlogid'] = 'Table region log reference';
$string['privacy:metadata:loguser:department'] = 'User department';

$string['taskcleanup'] = 'Log table cleanup';
