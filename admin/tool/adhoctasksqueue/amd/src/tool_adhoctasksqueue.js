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
 * JS code for the tool_adhoctasksqueue plugin
 *
 * @package    tool_adhoctasksqueue
 * @copyright  2022 Edunao SAS (contact@edunao.com)
 * @author     Julien Buabent <julien.buabent@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Probleme : attention à la compatibilité IE11 ES6+ (???) *
 */

define([
    'jquery',
    'jqueryui',
    'core/modal_factory',
    'core/templates',
    'core/modal_events',
    'tool_adhoctasksqueue/datatables',
    'tool_adhoctasksqueue/datatables-buttons',
    'tool_adhoctasksqueue/datatables-select',
    'tool_adhoctasksqueue/datatables-checkboxes'
], function ($, ui, ModalFactory, Templates, ModalEvents) {
    var tool_adhoc = {

        // Set main attributes
        api_url: M.cfg.wwwroot + '/admin/tool/adhoctasksqueue/ajax/ajax.php',
        modalElement: $('#modal'),
        tableElement: $('#taskslist'),
        loadingSpinner: $("#loading-spinner"),

        /**
         * Get the task in the API the draw the table
         * Init function is called automatically by the plugin
         */
        init: function () {
            // Hold the current context in global var because JQuery take 'this' :(.
            var self = this;

            // Load all tasks and render the datatable
            this.loadTasks();

            // Customdata event.
            this.tableElement.on('click', '.see-customdata', function () {
                self.customData($(this).data('id'));
            });

            // Delete task event.
            this.tableElement.on('click', '.deletetask i', function () {
                self.confirmDeleteTask($(this).data('id'));
            });
        },

        /**
         * Load tasks from the API then draw the table
         */
        loadTasks: function () {
            var self = this;

            // Run the big async call to get all tasks.
            this.tableElement.on('xhr.dt', function (e, settings, json, xhr) {
                // Display error if http response code is not ok
                if (xhr.status < 200 || xhr.status >= 300) {
                    // Set modal data.
                    let modalcontent = {};
                    modalcontent.type = ModalFactory.types.DEFAULT;
                    modalcontent.title = (xhr.responseJSON) ? 'Error ' + xhr.responseJSON.status : xhr.status;
                    modalcontent.body = (xhr.responseJSON) ? xhr.responseJSON.message : xhr.statusText;

                    // Build modal.
                    ModalFactory.create(modalcontent, this.modalElement).done(function (modal) {
                        modal.show();
                    });
                }
            }).DataTable({
                oLanguage: {
                    sUrl: M.cfg.wwwroot + '/admin/tool/adhoctasksqueue/datatables/lang/' + M.util.get_string('langfile', 'tool_adhoctasksqueue') + '.json'
                },
                ajax: {
                    url: this.api_url + '?action=get_tasks',
                    dataSrc: 'tasks'
                },
                columns: [
                    {data: 'id'},
                    {data: 'component'},
                    {data: 'classname'},
                    {
                        data: 'nextruntime',
                        render: function (data, type) {
                            // Format timestamp in human locale date.
                            if (type === 'display') {
                                return new Date(data * 1000).toLocaleString();
                            }
                            return data;
                        }
                    },
                    {data: 'faildelay'},
                    {
                        data: 'id',
                        orderable: false,
                        render: function (data, type) {
                            // Display an icon to get the customdata specific field.
                            // A click run another async call to get data.
                            return '<i class="fa fa-search see-customdata" data-id="' + data + '"></i>';
                        }
                    },
                    {
                        data: 'userid',
                        render: function (data, type, row) {
                            // Display user's link or null.
                            if (row.userid === null) {
                                return '<i class="fa fa-ban"></i>';
                            } else {
                                return "<a href='" + M.cfg.wwwroot + "/user/profile.php?id=" + row.userid + "'>" + row.userfullname + "</a>";
                            }
                        }
                    },
                    {data: 'blocking'},
                    {
                        data: 'id',
                        className: "deletetask",
                        orderable: false,
                        render: function (data, type, row, meta) {
                            // Display the icon to delete a task.
                            // A click call another async request.
                            return '<i class="fa fa-remove removetask" data-id="' + data + '" aria-hidden="true"></i>';
                        }
                    }
                ],
                "drawCallback": function (settings) {
                    self.loadingSpinner.hide();
                }
            });
        },

        /**
         * Get the customdata of a task, then display it in a modal
         * @param id task id
         * @return {Promise<void>}
         */
        customData: async function (id) {
            var self = this;

            // Display loading spinner for long loading time
            this.loadingSpinner.show();

            // Fetch customdata by task id.
            $.getJSON(this.api_url + '?action=get_customdata&id=' + id, function (result) {
                // Api return a 2xx response.

                // Set the modal datas.
                let modalcontent = {};
                modalcontent.type = ModalFactory.types.DEFAULT;

                // Set the customdata var from JSON stored in DB
                let customdata;
                try {
                    // Parse JSON
                    customdata = JSON.parse(result.customdata);
                } catch (err) {
                    // Get just string if it's not a valid JSON
                    customdata = result.customdata;
                }

                if (typeof customdata === 'object') {
                    modalcontent.title = self.getString('customdatacontent');
                    if (customdata === null) {
                        // Customdata is empty.
                        modalcontent.body = self.getString('nocustomdata');
                    } else {
                        // Customdata is a valid json.
                        // Build a recusrsive html list based on the JSON customdata.
                        let htmllist = '';
                        htmllist += self.render(customdata);
                        modalcontent.body = htmllist;
                    }
                } else {
                    //Customdata is not a valid json
                    modalcontent.title = self.getString('customdatacontentnojson');
                    modalcontent.body = customdata;
                }

                // Build and display the modal.
                ModalFactory.create(modalcontent, self.modalElement).done(function (modal) {
                    self.loadingSpinner.hide();
                    modal.show();
                });

            }).fail(function (result) {
                // Error returned from the backend (http status is not 2xx).
                // Build and display the modal.
                ModalFactory.create({
                    type: ModalFactory.types.DEFAULT,
                    title: self.getString('error') + ' ' + result.status,
                    body: result.responseJSON.message
                }, self.modalElement).done(function (modal) {
                    self.loadingSpinner.hide();
                    modal.show();
                });
            })
        },

        /**
         * Display the modal to confirm the task deletion
         * @param id task id
         * @return {Promise<void>}
         */
        confirmDeleteTask: async function (id) {
            var self = this;

            // Build the modal.
            ModalFactory.create({
                type: ModalFactory.types.SAVE_CANCEL,
                title: this.getString('deletetask'),
                body: this.getString('deletetaskquestion', id)
            }, this.modalElement).done(function (modal) {
                // Display the modal.
                modal.show();

                // Listen to the save event.
                modal.getRoot().on(ModalEvents.save, async function (e) {
                    // Delete task
                    self.deleteTask(id, modal);
                });
            });
        },

        /**
         * Delete a task by its id
         * @param id task id
         * @return {Promise<void>}
         */
        deleteTask: async function (id, modal) {
            var self = this;

            // Display loading spinner.
            this.loadingSpinner.show();

            // Run the deletion process.
            $.getJSON(this.api_url + '?action=delete_task&id=' + id, function (result) {
                // Task deleted correctly
                modal.hide();

                // Reload all tasks.
                self.tableElement.DataTable().ajax.reload(function () {
                    self.loadingSpinner.hide();
                });

            }).fail(function (result) {
                // Display returned error
                ModalFactory.create({
                    type: ModalFactory.types.DEFAULT,
                    title: self.getString('error') + ' ' + result.status,
                    body: self.getString('deletetaskerror', id) + '<br>(' + result.responseJSON.message + ')'
                }, self.modalElement).done(function (modal) {
                    // Modal for error
                    modal.show();

                    // Reload all tasks "au cas ou"
                    self.tableElement.DataTable().ajax.reload(function () {
                        self.loadingSpinner.hide();
                    });
                });
            })
        },

        /**
         * Recursive rendering: build an html list based on the customdata json.
         * @param datas tree
         * @return String html list
         */
        render: function (datas) {
            let str = '<ul>';
            for (const key in datas) {
                const value = datas[key];
                str += '<li>' + key + ' :';
                if (typeof value === 'object') {
                    str += this.render(value);
                } else {
                    str += value;
                }
                str += '</li>';
            }
            str += '</ul>';
            return str;
        },

        /**
         * Get an intl string from language plugin file
         *
         * @param str Identifier of the required string
         * @param params An optional data to include in the string
         * @return string
         */
        getString: function (str, params) {
            if (typeof M.str.tool_adhoctasksqueue[str] !== 'string') {
                return str + ': Intl error';
            }
            return M.util.get_string(str, 'tool_adhoctasksqueue', params);
        }
    };

    // Add object to window to be called outside require.
    window.tool_adhoc = tool_adhoc;
    return tool_adhoc;
});
