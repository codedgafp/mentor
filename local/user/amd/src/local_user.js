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
    var local_user = {
        /**
         * Create user admin DataTable
         *
         * @param {Object} params
         */
        init: function (params) {
            this.cohortid = params.cohortid;
            this.entityid = params.entityid;
            this.ismanager = params.ismanager;
            this.isadmin = params.isadmin;
            this.entityshortname = params.entityshortname;

            this.cookieName = 'user_filter_entity' + this.entityid;

            this.createUserTable();
        },
        /**
         * Initial user admin table
         */
        createUserTable: function () {
            var that = this;
            //table course edadmin user admin
            M.table = $('#user-admin-table').DataTable({
                ajax: {
                    //Call data members cohort
                    url: M.cfg.wwwroot + '/local/user/ajax/ajax.php',
                    data: function (d) {// GET HTTP data setting
                        d.controller = 'entity';
                        d.action = 'get_members';
                        d.entityid = that.entityid;
                        d.format = 'json';
                    },
                    dataSrc: 'message'
                },
                oLanguage: {
                    sUrl: M.cfg.wwwroot + '/local/mentor_core/datatables/lang/' + M.util.get_string('langfile', 'local_user') + ".json"
                },
                order: [[3, "asc"]],
                columns: [
                    {data: 'lastname'},
                    {data: 'firstname'},
                    {data: 'email'},
                    {
                        data: 'lastconnexion',
                        render: function (data, type, row, meta) {
                            if (row.lastconnection) {
                                return that.getDateTimeFromTimestamp(row.lastconnection);
                            } else {
                                return M.util.get_string('neverconnected', 'local_user');
                            }
                        }
                    },
                    {
                        //create two button action per user (link profil and  remove from cohort)
                        data: 'id',
                        className: 'user-admin-table-actions',
                        render: function (data, type, row, meta) {
                            if (type !== 'display') {
                                return data;
                            }
                            return '<div class="user-admin-button-action">' +
                                '<div id="user-profil" class="button-action">' +
                                '<a href="' + row.profileurl + '&returnto=' + encodeURI(window.location.href) + '">' +
                                '<img src="' + M.util.image_url('gear', 'local_user') + '" alt="adminuserprofil">' +
                                '</a>' +
                                '</div>' +
                                '</div>';
                        }
                    }
                ],
                //To create header buttons
                dom: 'Bfrtip',
                //Header buttons
                buttons: [
                    {
                        text: M.util.get_string('adduser', 'local_user'),
                        className: 'btn btn-primary',
                        attr: {
                            id: 'addusers',
                        },
                        action: function (e, dt, node, config) {
                            window.location.href = M.cfg.wwwroot + '/local/profile/pages/editadvanced.php?id=-1&mainentity=' + that.entityid + '&returnto=' + encodeURI(window.location.href);
                        }
                    }, {
                        text: M.util.get_string('importusersglobal', 'local_mentor_core'),
                        "className": 'btn btn-primary',
                        attr: {
                            id: 'importusers'
                        },
                        action: function (e, dt, node, config) {
                            window.location.href = M.cfg.wwwroot + '/local/user/pages/importcsv.php?entityid=' + that.entityid;
                        }
                    },
                    {
                        text: '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i>' + M.util.get_string('mergeusers', 'local_mentor_core'),
                        "className": 'btn btn-primary',
                        attr: {
                            id: 'mergeusers',
                            title: M.util.get_string('mergeusers_help', 'local_mentor_core')
                        },
                        action: function (e, dt, node, config) {
                            window.location.href = M.cfg.wwwroot + '/admin/tool/mergeusers/index_mentor.php';
                        }
                    }
                ]
            });
        },

        getDateTimeFromTimestamp: function (unixTimeStamp) {
            var dateobject = new Date(unixTimeStamp * 1000);
            return ('0' + dateobject.getDate()).slice(-2) + '/' + ('0' + (dateobject.getMonth() + 1)).slice(-2) + '/' + dateobject.getFullYear() + ' ' + ('0' + dateobject.getHours()).slice(-2) + ':' + ('0' + dateobject.getMinutes()).slice(-2);
        }
    };

    //add object to window to be called outside require
    window.local_user = local_user;
    return local_user;
});
