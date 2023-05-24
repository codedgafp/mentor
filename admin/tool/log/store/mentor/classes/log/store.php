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
 * Mentor log repport reader/writer.
 *
 * @package    logstore_mentor
 * @copyright  2021 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace logstore_mentor\log;

defined('MOODLE_INTERNAL') || die();

use core\log\stdClass;

require_once($CFG->dirroot . '/local/mentor_core/api/session.php');
require_once($CFG->dirroot . '/local/mentor_core/api/profile.php');
require_once($CFG->dirroot . '/admin/tool/log/store/mentor/classes/model/user.php');
require_once($CFG->dirroot . '/admin/tool/log/store/mentor/classes/model/session.php');
require_once($CFG->dirroot . '/admin/tool/log/store/mentor/classes/model/collection.php');
require_once($CFG->dirroot . '/admin/tool/log/store/mentor/classes/model/log.php');

class store implements \tool_log\log\writer {
    use \tool_log\helper\store,
        \tool_log\helper\buffered_writer,
        \tool_log\helper\reader {
    }

    /** @var string $logguests true if logging guest access */
    protected $logguests;

    /**
     * List of allowed events
     *
     * @var string[]
     */
    private $eventallowedlist
        = [
            '\core\event\course_viewed',
            '\core\event\course_completed'
        ];

    public function __construct(\tool_log\log\manager $manager) {
        $this->helper_setup($manager);
    }

    /**
     * Should the event be ignored (== not logged)?
     *
     * @param \core\event\base $event
     * @return bool
     * @throws \coding_exception
     */
    protected function is_event_ignored(\core\event\base $event) {
        return !$this->is_in_event_allowed_list($event->eventname);
    }

    /**
     * Finally store the events into the database.
     *
     * @param array $evententries raw event data
     * @throws \ReflectionException
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    protected function insert_event_entries($evententries) {

        foreach ($evententries as $event) {

            // Check if not event is in event list or course is session course.
            if (!$this->is_in_event_allowed_list($event['eventname']) ||
                !\local_mentor_core\session_api::is_session_course($event['courseid'])) {
                continue;
            }

            // Get session object.
            $session = \local_mentor_core\session_api::get_session_by_course_id($event['courseid']);
            $training = $session->get_training();

            $session->get_progression($event['userid']);

            // Skip the event if it's not a session event.
            if (!$session) {
                continue;
            }

            // Get user's main entity.
            $mainentity = \local_mentor_core\profile_api::get_user_main_entity($event['userid']);

            // User main entity is not defined.
            if (!$mainentity) {
                continue;
            }

            // Create data to log store.
            $data = array(
                'userid' => $event['userid'],
                'entity' => $mainentity->id,
                'trainer' => !$session->is_participant($event['userid']) && $session->is_trainer($event['userid']),
                'sessionid' => $session->id,
                'shared' => $session->is_shared(),
                'space' => $session->get_entity()->id,
                'status' => $session->status,
                'completed' => 0
            );

            // Get or create user log store record.
            $userlog = new \logstore_mentor\models\user($data);
            $data['userlogid'] = $userlog->get_or_create_record();

            // Get or create session log store record.
            $sessionlog = new \logstore_mentor\models\session($data);

            $param = ['collections' => $training->collection];
            $data['sessionlogid'] = $sessionlog->get_or_create_record('session', $param);

            // Get or create collection log store record.
            foreach (explode(',', $training->collection) as $itemcollection) {
                $data['name'] = $itemcollection;
                $collectionlog = new \logstore_mentor\models\collection($data);
                $collectionlog->get_or_create_record();
            }

            // Get or create log store record.
            $log = new \logstore_mentor\models\log($data);
            $log->get_or_create_record();

        }
    }

    /**
     * Checks that the event is contained in the allowed list
     *
     * @param string $eventname
     * @return bool
     */
    public function is_in_event_allowed_list($eventname) {
        return in_array($eventname, $this->eventallowedlist);
    }

    /**
     * Get the name of the log table
     *
     * @return string
     */
    public function get_internal_log_table_name() {
        return 'logstore_mentor_log';
    }

    /**
     * Are the new events appearing in the reader?
     *
     * @return bool true means new log events are being added, false means no new data will be added
     */
    public function is_logging() {
        // Only enabled stpres are queried,
        // this means we can return true here unless store has some extra switch.
        return true;
    }

    public function get_config($name, $default = null) {
        // TODO: Implement get_config() method.
    }

    /**
     * Fetch records using given criteria.
     *
     * @param string $selectwhere
     * @param array $params
     * @param string $sort
     * @param int $limitfrom
     * @param int $limitnum
     * @return \core\event\base[]
     */
    public function get_events_select($selectwhere, array $params, $sort, $limitfrom, $limitnum) {
        // TODO: Implement get_events_select() method.
    }

    /**
     * Return number of events matching given criteria.
     *
     * @param string $selectwhere
     * @param array $params
     * @return int
     */
    public function get_events_select_count($selectwhere, array $params) {
        // TODO: Implement get_events_select_count() method.
    }

    /**
     * Fetch records using the given criteria returning an traversable list of events.
     *
     * Note that the returned object is Traversable, not Iterator, as we are returning
     * EmptyIterator if we know there are no events, and EmptyIterator does not implement
     * Countable {@link https://bugs.php.net/bug.php?id=60577} so valid() should be checked
     * in any case instead of a count().
     *
     * Also note that the traversable object contains a recordset and it is very important
     * that you close it after using it.
     *
     * @param string $selectwhere
     * @param array $params
     * @param string $sort
     * @param int $limitfrom
     * @param int $limitnum
     * @return \Traversable|\core\event\base[] Returns an iterator containing \core\event\base objects.
     */
    public function get_events_select_iterator($selectwhere, array $params, $sort, $limitfrom, $limitnum) {
        // TODO: Implement get_events_select_iterator() method.
    }

    /**
     * Returns an event from the log data.
     *
     * @param stdClass $data Log data
     * @return \core\event\base
     */
    public function get_log_event($data) {
        // TODO: Implement get_log_event() method.
    }
}
