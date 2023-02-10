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

define('CLI_SCRIPT', true);

require_once('../../../../../config.php');
require_once($CFG->dirroot . '/local/mentor_core/api/session.php');
require_once($CFG->dirroot . '/local/mentor_core/api/profile.php');
require_once($CFG->dirroot . '/admin/tool/log/store/mentor2/classes/model/user.php');
require_once($CFG->dirroot . '/admin/tool/log/store/mentor2/classes/model/session.php');
require_once($CFG->dirroot . '/admin/tool/log/store/mentor2/classes/model/collection.php');
require_once($CFG->dirroot . '/admin/tool/log/store/mentor2/classes/model/region.php');
require_once($CFG->dirroot . '/admin/tool/log/store/mentor2/classes/model/entity.php');
require_once($CFG->dirroot . '/admin/tool/log/store/mentor2/classes/model/log.php');
require_once($CFG->dirroot . '/admin/tool/log/store/mentor2/db/upgrade.php');

core_php_time_limit::raise(60 * 60); // 1 hour should be enough.
raise_memory_limit(MEMORY_HUGE);

logstore_mentor_2_clean_logs();
logstore_mentor_2_migrate_logs('logstore_mentor_history_log');
logstore_mentor_2_migrate_logs('logstore_mentor_log');

echo 'Conversion du logstore mentor vers mentor2 terminée.';
echo '<div>Ancien logtore : <a href="' . $CFG->wwwroot . '/admin/tool/log/store/mentor/database.php">' . $CFG->wwwroot .
     '/admin/tool/log/store/mentor/database.php' . '</a></div>';
echo '<div>Nouveau logtore : <a href="' . $CFG->wwwroot . '/admin/tool/log/store/mentor2/database.php">' . $CFG->wwwroot .
     '/admin/tool/log/store/mentor2/database.php' . '</a></div>';
