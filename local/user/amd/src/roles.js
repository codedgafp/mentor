/**
 * Javascript containing the utils function of local user plugin
 */

define([
    'jquery',
    'format_edadmin/format_edadmin',
    'local_mentor_core/datatables',
    'local_mentor_core/datatables-buttons',
    'local_mentor_core/datatables-select',
    'local_mentor_core/datatables-checkboxes'
], function ($, format_edadmin) {
    var roles = {
        /**
         * Create user admin DataTable
         *
         * @param {Object} params
         */
        init: function () {
            this.createRolesTable();
        },
        /**
         * Initial user admin table
         */
        createRolesTable: function () {
            var that = this;
            //table course edadmin user admin
            M.table = $('#roles-table').DataTable({
                ajax: {
                    //Call data members cohort
                    url: M.cfg.wwwroot + '/local/user/ajax/ajax.php',
                    data: function (d) {// GET HTTP data setting
                        d.controller = 'user';
                        d.action = 'get_roles';
                        d.format = 'json';
                        d.filter = that.filter; // Filters data
                    }
                },
                serverSide: true,//For use Ajax
                processing: true,
                pageLength: 100,
                ordering: true,
                order: [],
                fixedColumns: true,
                columnDefs: [
                    {
                        targets: 0,
                        render: function (data, type, row, meta) {
                            if (type === 'display') {
                                data = '<a href="' +
                                    M.cfg.wwwroot + '/user/profile.php?id=' + row[7] + '">' + row[0] + '</a>';
                            }
                            return data;
                        }
                    },
                    {
                        targets: 5,
                        render: function (data, type, row, meta) {
                            if (type === 'display') {
                                if (!isNaN(data)) {
                                    // Convert time to dd/mm/yyyy
                                    data = roles.getDateTimeFromTimestamp(data)
                                }
                            }
                            return data;
                        }
                    }
                ],
                oLanguage: {
                    sUrl: M.cfg.wwwroot + '/local/mentor_core/datatables/lang/' + M.util.get_string('langfile', 'local_user') + ".json"
                }
            });
        },
        /**
         * Convert timestamp to dd/mm/yyy date format
         *
         * @param unixTimeStamp
         * @returns {string}
         */
        getDateTimeFromTimestamp: function (unixTimeStamp) {
            var dateobject = new Date(unixTimeStamp * 1000);
            return ('0' + dateobject.getDate()).slice(-2) + '/' + ('0' + (dateobject.getMonth() + 1)).slice(-2) + '/' + dateobject.getFullYear();
        }
    };

    //add object to window to be called outside require
    window.roles = roles;
    return roles;
});
