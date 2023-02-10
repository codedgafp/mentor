/**
 * Javascript containing function of the admin space
 */

define([
    'jquery',
    'format_edadmin/format_edadmin',
    'local_mentor_core/select2',
    'local_mentor_core/mentor',
    'jqueryui',
    'local_mentor_core/datatables',
    'local_mentor_core/datatables-buttons',
], function ($, format_edadmin, select2, mentor) {
    var local_entities = {
        /**
         * Init JS
         */
        init: function (params) {
            this.params = params;

            // Initial and create admin space datatable
            this.createAdminTable(params.listtypename);

        },
        createAdminTable: function (listtypename) {
            var that = this;

            // Add entity button only for admins
            var buttons = [];

            if (that.params.isadmin) {
                buttons = [
                    // Add entities button
                    {// Create entity
                        text: M.util.get_string('addentity', 'local_entities'),
                        attr: {
                            id: 'adddentities',
                            class: 'btn btn-primary'
                        },
                        action: function (e, dt, node, config) {
                            that.create_entity_modal(e, false);
                        }
                    }
                ];
            }
            buttons.push({// Create sub entity
                text: M.util.get_string('addsubentity', 'local_entities'),
                attr: {
                    id: 'adddsubentities',
                    class: 'btn btn-primary'
                },
                action: function (e, dt, node, config) {
                    that.create_entity_modal(e, true);
                }
            });

            // View roles
            if (that.params.isadmin) {
                buttons.push({
                    text: M.util.get_string('viewroles', 'local_entities'),
                    attr: {
                        id: 'viewroles',
                        class: 'btn btn-primary'
                    },
                    action: function (e, dt, node, config) {
                        window.location.href = M.cfg.wwwroot + '/local/user/pages/roles.php';
                    }
                });
            }

            // Admin space table
            var columns = [
                {
                    data: 'entitypath',
                    render: function (data, type, row, meta) {
                        if (typeof row.ishidden !== 'undefined') {

                            if (row.ishidden == 1) {
                                return '<span style="color: #888;">' + data + '</span>';
                            }
                        }
                        return data;
                    }
                }
            ];

            listtypename.forEach(function (typename) {
                columns.push({
                    data: typename,
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row, meta) {
                        return that.rows_render_generate(data, type, typename);
                    }
                });
            });

            M.table = $('#entities-table').DataTable({
                "columnDefs": [{
                    "targets": 'no-sort',
                    "orderable": false,
                }],
                processing: true,
                serverSide: true,//For use Ajax
                ordering: true,
                ajax: {
                    // Call edadmin course list
                    url: M.cfg.wwwroot + '/local/entities/ajax/ajax.php',
                    data: function (d) {// GET HTTP data setting
                        d.controller = 'entity';
                        d.action = 'get_managed_entities';
                        d.format = 'json';
                    }
                },
                oLanguage: {
                    sUrl: M.cfg.wwwroot + '/local/mentor_core/datatables/lang/' + M.util.get_string('langfile', 'local_entities') + ".json"
                },
                dom: 'Bfrtip',
                pageLength: 50,
                columns: columns,
                //Header buttons
                buttons: buttons
            });
        },
        /**
         * Generate html row renderer
         *
         * @param data
         * @param type
         * @param typename
         * @returns {string|*}
         */
        rows_render_generate: function (data, type, typename) {

            if (typeof data.name !== 'undefined') {
                if (type === 'display') {
                    return '<a href="' + data.link + '" class="type-' + typename + '" title="' + data.name + '"><img alt="' + typename + '" src="' + M.util.image_url('icon', 'local_' + typename) + '" width="20px"></a>';
                }
                return data.name;
            }
            return '';
        },
        /**
         * Create new entity modal
         *
         * @param event
         * @param issubentity
         */
        create_entity_modal: function (event, issubentity) {
            event.preventDefault();

            // Element list
            var inputspacenameid = issubentity ? 'sub-entities-form-name' : 'entities-form-name';
            var inputresponsible = issubentity ? 'entities-form-parent-entity' : 'entities-form-email-responsible';

            // Modal params
            var buttons = [// Modal buttons
                {
                    text: M.util.get_string('save', 'format_edadmin'),
                    class: 'btn btn-primary',
                    click: function () {//Just close the modal
                        //If name space input is not empty
                        var serializeddata = issubentity ? $('#sub-entities-form').serialize() : $('#entities-form').serialize();

                        var modalthis = $(this);

                        var spacename = $('#' + inputspacenameid).val();

                        if (spacename.length !== 0) {

                            var ajaxcallparams = {
                                url: M.cfg.wwwroot + '/local/entities/ajax/ajax.php?' + serializeddata,
                                controller: 'entity',
                                action: 'create_entity',
                                format: 'json',
                                callback: function (response) {

                                    modalthis.dialog('destroy');

                                    response = JSON.parse(response);

                                    if (response.success) {
                                        M.table.ajax.reload();
                                    } else {//If user not exist
                                        format_edadmin.error_modal(response.message);
                                    }
                                }
                            };

                            if (issubentity) {
                                ajaxcallparams.parentid = 0
                                if ($('#' + inputresponsible).select2('data').length > 0) {
                                    ajaxcallparams.parentid = $('#' + inputresponsible).select2('data')[0].id;
                                }
                            } else {
                                ajaxcallparams.userid = 0
                                if ($('#' + inputresponsible).select2('data').length > 0) {
                                    ajaxcallparams.userid = $('#' + inputresponsible).select2('data')[0].id
                                }
                            }

                            format_edadmin.ajax_call(ajaxcallparams);
                        }
                    }
                },
                {
                    text: M.util.get_string('cancel', 'format_edadmin'),
                    class: 'btn btn-secondary',
                    click: function () {//Just close the modal
                        $(this).dialog("destroy");
                    }
                }
            ];

            var formtemplate = issubentity ? '#sub-entities-form-template' : '#entities-form-template';

            mentor.dialog(formtemplate, {
                width: 620,
                title: issubentity ? M.util.get_string('addsubentity', 'local_entities') : M.util.get_string('addentity', 'local_entities'),
                buttons: buttons,
                close: function (dialogEvent, ui) {
                    $(this).dialog("destroy");
                },
                open: function () {
                    $('#entities-form')[0].reset();
                    $('#' + inputresponsible).val(null).trigger("change");
                    $('#' + inputspacenameid).val(null).trigger("change");
                }
            });

            if (issubentity) {
                $('#' + inputresponsible).select2({
                    ajax: {
                        url: M.cfg.wwwroot + '/local/entities/ajax/ajax.php',
                        dataType: 'json',
                        data: function (params) {
                            return {
                                controller: 'entity',
                                action: 'search_main_entities',
                                format: 'json',
                                searchtext: params.term
                            };
                        },
                        processResults: function (data) {
                            return {
                                results: $.map(data.message, function (item) {
                                    return {
                                        text: item.name,
                                        id: item.id
                                    }
                                })
                            }
                        }
                    }
                })
            } else {
                $('#' + inputresponsible).select2({
                    ajax: {
                        url: M.cfg.wwwroot + '/local/entities/ajax/ajax.php',
                        dataType: 'json',
                        data: function (params) {
                            return {
                                controller: 'user',
                                action: 'search_users',
                                format: 'json',
                                searchtext: params.term
                            };
                        },
                        processResults: function (data) {
                            return {
                                results: $.map(data.message, function (item) {
                                    return {
                                        text: item.firstname + ' ' + item.lastname + ' - ' + item.email,
                                        id: item.id
                                    }
                                })
                            }
                        }
                    }
                })
            }
        }
    };

    //add object to window to be called outside require
    window.local_entities = local_entities;
    return local_entities;
});
