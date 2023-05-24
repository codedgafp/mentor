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
 * Class that handle the HTTP request before send response to the client
 *
 * @package    tool_adhoctasksqueue
 * @copyright  2022 Edunao SAS (contact@edunao.com)
 * @author     Julien Buabent <julien.buabent@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_adhoctasksqueue;

class adhoctasks_api {

    /**
     * Handle the request, parse action and params, and call the good methods with its params
     * An undefined method fall in the __callStatic PHP magic method that return a 400 HTTP status code
     *
     * @return void
     */
    public static function run(): void {

        // Check if user is admin.
        if (is_siteadmin() === false) {
            self::send(403, 'Forbidden');
        }

        // Check and set requested action.
        $action = optional_param('action', null, PARAM_ALPHAEXT);
        if ($action === null) {
            self::send(400, 'Bad request : action required.');
        } else if ($action === '') {
            self::send(400, 'Bad request : action value is not valid.');
        } else if ($action === 'run') {
            self::send(403, 'Bad request : forbidden action.');
        }

        // Set other params.
        $params = $_GET;
        unset($params['action']);

        // Check if id exists and if is numeric.
        if (isset($params['id']) && is_numeric($params['id']) === false) {
            self::send(400, 'Bad request : id must be a numeric value');
        }

        $params = array_values($params);

        // Run the good method.
        if (count($params) === 0) {
            // Without params.
            self::$action();
        } else {
            // With params.
            self::$action(...$params);
        }

    }

    /**
     * Fetch adhoc tasks from DB and send them if ok.
     *
     * @return void
     */
    private static function get_tasks(): void {

        global $DB;
        $tasks = [];

        try {
            // Get the whole adhoc tasks.
            $tasks = $DB->get_records_sql("
                        SELECT
                             t.id,
                             t.component,
                             t.classname,
                             t.nextruntime,
                             t.faildelay,
                             t.userid,
                             CONCAT(u.firstname, ' ', u.lastname) AS userfullname,
                             t.blocking
                        FROM {task_adhoc} t
                        LEFT JOIN {user} u
                        ON t.userid = u.id
                        ");

        } catch (moodle_exception $e) {
            // Catch system or DB error.
            self::send(500, 'Internal error : ' . $e->errorcode);
        }

        // Send response back to the client.
        self::send(200, 'Ad hoc tasks list', ['tasks' => array_values($tasks)]);
    }

    /**
     * Fetch customdata task field from DB and send it if ok.
     *
     * @param int $id id of the task
     * @return void
     */
    private static function get_customdata(int $id): void {

        global $DB;
        $customdata = '';

        // Check if task exists.
        if ($DB->record_exists('task_adhoc', ['id' => $id]) === false) {
            self::send(400, "Bad request : task doesn't exists.");
        }

        try {
            // Get the customdata field of a task.
            $record = $DB->get_record('task_adhoc', ['id' => $id], 'customdata');
        } catch (moodle_exception $e) {
            // Catch system or DB error.
            self::send(500, 'Internal error : ' . $e->errorcode);
        }

        // Send response back to the client.
        self::send(200, 'Custom data', ['customdata' => $record->customdata]);
    }

    /**
     * Delete a task by its id
     *
     * @param int $id id of the task to delete
     * @return void
     * @throws dml_exception
     */
    private static function delete_task(int $id): void {

        global $DB;

        // Check if task exists.
        if ($DB->record_exists('task_adhoc', ['id' => $id]) === false) {
            self::send(400, "Bad request : task doesn't exists.");
        }

        try {
            // Delete the requested task.
            $DB->delete_records('task_adhoc', ['id' => $id]);
        } catch (moodle_exception $e) {
            // Catch system or DB error.
            self::send(500, 'Internal error : ' . $e->errorcode);
        }

        // Check if task has correctly been deleted.
        if ($DB->record_exists('task_adhoc', ['id' => $id]) === true) {
            self::send(500, "Internal server error : task has not been deleted");
        } else {
            self::send(200, "Task $id deleted");
        }
    }

    /**
     * Build the response data before serialize it and return to the client.
     *
     * @param int $httpstatus HTTP status code
     * @param string $message A global information about the response
     * @param array $payload data
     * @return void
     */
    private static function send(int $httpstatus, string $message, array $payload = []): void {
        // Change the content type and status HTTP header.
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($httpstatus);

        // Build mandatory datas.
        $generic = [];
        $generic['status'] = $httpstatus;
        $generic['message'] = $message;

        // Add other data if necessary.
        $response = array_merge($generic, $payload);

        // Return the json.
        exit(json_encode($response));
    }

    /**
     * This magic method is called by PHP when the called method doesn't exist
     *
     * @param $name called method name
     * @param $arguments called arguments
     * @return void
     */
    public static function __callStatic($name, $arguments): void {
        self::send(400, 'Bad request : action unknown.');
    }

}








