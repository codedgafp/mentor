/**
 * Javascript containing function of the admin trainings
 */

define([
    'jquery',
    'format_edadmin/format_edadmin',
    'local_mentor_core/mentor',
    'jqueryui',
    'local_mentor_core/datatables',
    'local_mentor_core/datatables-buttons',
    'local_mentor_core/jszip',
    'local_mentor_core/buttons.html5'
], function ($, format_edadmin, mentor) {
    var local_trainings = {
        /**
         * Init JS
         */
        init: function (params) {
            this.params = params;
            var that = this;

            //Initial and create admin space datatable
            this.createAdminTable(params.entityid);

            $('#add-training-button-subentities').on('click', function () {
                that.createTrainingPopup();
            });

            that.setSubentitySelector();
            $('#destinationentity').on('change', function () {
                that.setSubentitySelector();
            })

        },

        /**
         * Display a create training popup
         *
         * @param entity
         */
        createTrainingPopup: function () {
            var that = this;

            mentor.dialog('#create-training-popup', {
                width: 590,
                title: M.util.get_string('creattrainingpopuptitle', 'local_trainings'),
                buttons: [
                    {
                        text: M.util.get_string('tocreate', 'format_edadmin'),
                        class: "btn btn-primary",
                        click: function () {
                            var dialog = $(this);
                            $('.ui-dialog-buttonset button').attr("disabled", "disabled");

                            var entity = $(this).find('#entity').val();

                            window.location.href = M.cfg.wwwroot + '/local/trainings/pages/add_training.php?entityid=' + entity;
                        }
                    },
                    {
                        // Cancel button
                        text: M.util.get_string('cancel', 'format_edadmin'),
                        class: "btn btn-secondary",
                        click: function () {//Just close the modal
                            $(this).dialog("destroy");
                        }
                    }
                ]
            });
        },

        setSubentitySelector: function () {
            var selectedentity = $('#destinationentity').length ? $('#destinationentity').val() : this.params.entityid;
            var parentid = $('#destinationsubentity-container').data('parentid');

            if ($(this).find('#destinationsubentity > option').length === 1) {
                $('#destinationsubentity-container').hide();
                return;
            }

            if (selectedentity == parentid) {
                $('#destinationsubentity-container').show();
            } else {
                $('#destinationsubentity-container').hide();
            }
        },
        /**
         * Init and create training admin table
         *
         * @param {int} entityid
         */
        createAdminTable: function (entityid) {
            var that = this;

            function newexportaction(e, dt, button, config) {
                var self = this;
                var oldStart = dt.settings()[0]._iDisplayStart;
                dt.one('preXhr', function (e, s, data) {
                    // Just this once, load all data from the server...
                    data.start = 0;
                    data.length = 2147483647;
                    dt.one('preDraw', function (e, settings) {
                        // Call the original action function
                        if (button[0].className.indexOf('buttons-copy') >= 0) {
                            $.fn.dataTable.ext.buttons.copyHtml5.action.call(self, e, dt, button, config);
                        } else if (button[0].className.indexOf('buttons-excel') >= 0) {
                            $.fn.dataTable.ext.buttons.excelHtml5.available(dt, config) ?
                                $.fn.dataTable.ext.buttons.excelHtml5.action.call(self, e, dt, button, config) :
                                $.fn.dataTable.ext.buttons.excelFlash.action.call(self, e, dt, button, config);
                        } else if (button[0].className.indexOf('buttons-csv') >= 0) {
                            $.fn.dataTable.ext.buttons.csvHtml5.available(dt, config) ?
                                $.fn.dataTable.ext.buttons.csvHtml5.action.call(self, e, dt, button, config) :
                                $.fn.dataTable.ext.buttons.csvFlash.action.call(self, e, dt, button, config);
                        } else if (button[0].className.indexOf('buttons-pdf') >= 0) {
                            $.fn.dataTable.ext.buttons.pdfHtml5.available(dt, config) ?
                                $.fn.dataTable.ext.buttons.pdfHtml5.action.call(self, e, dt, button, config) :
                                $.fn.dataTable.ext.buttons.pdfFlash.action.call(self, e, dt, button, config);
                        } else if (button[0].className.indexOf('buttons-print') >= 0) {
                            $.fn.dataTable.ext.buttons.print.action(e, dt, button, config);
                        }
                        dt.one('preXhr', function (e, s, data) {
                            // DataTables thinks the first item displayed is index 0, but we're not drawing that.
                            // Set the property to what it was before exporting.
                            settings._iDisplayStart = oldStart;
                            data.start = oldStart;
                        });
                        // Reload the grid with the original page. Otherwise, API functions like table.cell(this) don't work properly.
                        setTimeout(dt.ajax.reload, 0);
                        // Prevent rendering of the full data to the DOM
                        return false;
                    });
                });
                // Requery the server with the new one-time export settings
                dt.ajax.reload();
            };

            // Admin training table creation
            M.table = $('#trainings-table').DataTable({
                ajax: {
                    //Call edadmin course list
                    url: M.cfg.wwwroot + '/local/trainings/ajax/ajax.php',
                    data: function (d) {// GET HTTP data setting
                        d.controller = 'training';
                        d.action = 'get_trainings_by_entity';
                        d.format = 'json';
                        d.entityid = entityid;
                    },
                    dataSrc: 'message'
                },
                oLanguage: {
                    sUrl: M.cfg.wwwroot + '/local/mentor_core/datatables/lang/' + M.util.get_string('langfile', 'local_trainings') + ".json"
                },
                dom: 'Blfrtip',
                buttons: [
                    {
                        extend: 'csvHtml5',
                        charset: 'utf-8',
                        fieldBoundary: '',
                        fieldSeparator: ';',
                        bom: true,
                        exportOptions: {
                            format: {
                                body: function (data, row, column, node) {
                                    // Replace br by \r\n.
                                    if (column === 0) {
                                        return decodeURIComponent(data.replace(/<br\s*\/?>/ig, "."));
                                    }
                                    return decodeURIComponent(data.replace(/<.*?>/ig, ""));
                                }
                            }
                        },
                        stripNewlines: false,
                        text: 'Export CSV',
                        className: 'btn-secondary csvexport',
                        action: newexportaction
                    }
                ],
                pageLength: 25,
                order: [[0, 'asc']],
                columns: [
                    {
                        data: function (data, type, row, meta) {

                            if (type === 'display') {
                                return '<a href="' + data.url + '">' + data.data.name + '</a>';
                            }

                            return data.data.name;

                        }
                    },
                    {
                        data: function (data, type, row, meta) {
                            return '<span class="training-status" data-status="' + data.data.status + '">' + M.util.get_string(data.data.status, 'local_trainings') + '</span>';
                        }
                    },
                    {
                        searchable: false,
                        orderable: false,
                        className: 'action-admin-trainings',
                        data: function (data, type, row, meta) {
                            return that.actionsRender(data.actions, data.data.id, data.data.name);
                        }
                    }
                ]
            });
        },
        /**
         * Create a rendering of the action buttons
         *
         * @param {Object[]} actions
         * @param {int} trainingid
         * @returns {string}
         */
        actionsRender: function (actions, trainingid, trainingname, trainingshortname) {
            var render = '';

            if (typeof trainingname === 'undefined') {
                trainingname = '';
            }

            if (typeof trainingshortname === 'undefined') {
                trainingshortname = '';
            }

            $.each(actions, function (index, element) {
                render += '<div class="btn-' + index + '">';

                switch (index) {
                    case 'duplicatetraining' :
                        render += '<img class="cursor-image-training-admin-trainings" src="' + M.util.image_url(index, 'local_trainings') + '" alt="' + index + '" onclick="local_trainings.duplicateTraining(' + trainingid + ')" title="' + M.util.get_string(index + 'tooltip', 'local_trainings') + '">';
                        break;
                    case 'createsessions' :
                        trainingname = trainingname.replace(/'/g, "\\'");
                        trainingshortname = trainingshortname.replace(/'/g, "\\'");
                        render += '<img class="cursor-image-training-admin-trainings" src="' + M.util.image_url(index, 'local_trainings') + '" alt="' + index + '" onclick="local_trainings.createSession(' + trainingid + ',\'' + trainingname + '\',\'' + trainingshortname + '\')" title="' + M.util.get_string(index + 'tooltip', 'local_trainings') + '">';
                        break;
                    case 'deletetraining' :
                        render += '<img class="cursor-image-training-admin-trainings" src="' + M.util.image_url(index, 'local_trainings') + '" alt="' + index + '" onclick="local_trainings.removeTraining(' + trainingid + ')"' +
                            ' title="' + M.util.get_string(index + 'tooltip', 'local_trainings') + '">';
                        break;
                    case 'trainingsheet' :
                        render += '<a href="' + element.url + '" title="' + M.util.get_string(index + 'tooltip', 'local_trainings') + '"><img src="' + M.util.image_url(index, 'local_trainings') + '" alt="' + element.tooltip + '"></a>';
                        break;
                    case 'movetraining' :
                        render += '<img class="cursor-image-training-admin-trainings" src="' + M.util.image_url(index, 'local_trainings') + '" alt="' + index + '" onclick="local_trainings.moveTraining(' + trainingid + ')" title="' + M.util.get_string(index + 'tooltip', 'local_trainings') + '">';
                        break;
                    default :
                        render += '<a href="' + actions[index] + '" title="' + M.util.get_string(index + 'tooltip', 'local_trainings') + '"><img src="' + M.util.image_url(index, 'local_trainings') + '" alt="' + index + '""></a>';
                        break;
                }

                render += '</div>';
            });

            return render;

        },
        /**
         * Ajax call for training duplication
         *
         * @param {int} trainingid
         */
        duplicateTraining: function (trainingid) {
            var that = this;

            format_edadmin.ajax_call({
                url: M.cfg.wwwroot + '/local/trainings/ajax/ajax.php',
                controller: 'training',
                action: 'get_next_available_training_name',
                format: 'json',
                trainingid: trainingid,
                callback: function (response) {
                    response = JSON.parse(response);

                    var trainingname = response.message;

                    that.duplicateTrainingPopup(trainingid, trainingname);
                }
            });
        },

        /**
         * Display the duplicate training popup
         *
         * @param trainingid
         */
        duplicateTrainingPopup: function (trainingid, trainingname) {
            var that = this;

            $('#trainingshortname').val(trainingname);

            mentor.dialog('#duplicate-training-popup', {
                width: 800,
                title: M.util.get_string('duplicatetrainingdialogtitle', 'local_trainings'),
                create: function (event, ui) {
                    // Select default training entity
                    if ($(this).find('#destinationsubentity').length) {
                        trainingdata = M.table.data().toArray().find(function (x) {
                            return parseInt(x.id) === trainingid;
                        });
                        $(this).find('#destinationsubentity').val(trainingdata.entityid);
                    }

                    if ($(this).find('#destinationsubentity > option').length === 1) {
                        $('#destinationsubentity-container').hide();
                    }
                },
                buttons: [
                    {
                        // Remove button
                        text: M.util.get_string('confirm', 'format_edadmin'),
                        id: 'confirm-duplicate-training',
                        class: "btn btn-primary",
                        click: function (e) {
                            var dialog = $(this);
                            $('.ui-dialog-buttonset button').attr("disabled", "disabled");
                            $('#confirm-duplicate-training').css('cursor', 'wait').html(M.util.get_string('pleasewait', 'local_trainings'));

                            var trainingshortname = $('#trainingshortname').val();
                            var destinationentity = $('#destinationentity').length ? $('#destinationentity').val() : that.params.entityid;

                            var destinationsubentity = '';

                            if ($('.ui-dialog #destinationsubentity').length > 0) {
                                destinationsubentity = $('.ui-dialog #destinationsubentity').val();

                                if ($('.ui-dialog #destinationsubentity').length === 1) {
                                    var selectedentity = $('#destinationentity').length ? $('#destinationentity').val() : that.params.entityid;
                                    if (selectedentity == that.params.entityid) {
                                        destinationentity = destinationsubentity;
                                    }
                                } else {
                                    if ($('.ui-dialog #destinationsubentity-container').css('display') !== 'none' && destinationsubentity != '') {
                                        destinationentity = destinationsubentity;
                                    }
                                }
                            } else {
                                if ($('#destinationsubentity-container').data('subentity')) {
                                    destinationentity = $('#destinationsubentity-container').data('subentity')
                                }
                            }

                            format_edadmin.ajax_call({
                                url: M.cfg.wwwroot + '/local/trainings/ajax/ajax.php',
                                controller: 'training',
                                action: 'duplicate_training',
                                format: 'json',
                                trainingid: trainingid,
                                trainingshortname: trainingshortname,
                                destinationentity: destinationentity,
                                callback: function (ajaxResponse) {
                                    ajaxResponse = JSON.parse(ajaxResponse);

                                    if (!ajaxResponse.success) {
                                        // Duplication fail
                                        format_edadmin.error_modal(ajaxResponse.message);
                                    } else {
                                        if (ajaxResponse.message !== -1) {

                                            $('#training-name-used').css('display', 'none');
                                            dialog.dialog("destroy");

                                            // Add duplicate training to adhoc success
                                            mentor.dialog("#duplicate-training-message", {
                                                title: M.util.get_string('confirmation', 'admin'),
                                                width: 400,
                                                height: 270,
                                                buttons: [
                                                    {
                                                        text: M.util.get_string('closebuttontitle', 'moodle'),
                                                        class: "btn btn-primary",
                                                        click: function () {
                                                            $(this).dialog("destroy");
                                                        }
                                                    }
                                                ]
                                            });
                                        } else {
                                            // Session name used
                                            $(e.target).removeAttr("disabled").html(M.util.get_string('confirm', 'format_edadmin')).css('cursor', 'pointer');
                                            $(e.target).next().removeAttr("disabled");
                                            $('#training-name-used').css('display', 'block');
                                        }
                                    }
                                }
                            });
                        }
                    },
                    {
                        // Cancel button
                        text: M.util.get_string('cancel', 'format_edadmin'),
                        class: "btn btn-secondary",
                        click: function () {//Just close the modal
                            $(this).dialog("destroy");
                        }
                    }
                ],
                close: function (event, ui) {
                    $(this).dialog("destroy");
                }
            });
        },

        /**
         * Ajax call for moving a training into another entity
         *
         * @param {int} trainingid
         */
        moveTraining: function (trainingid) {

            mentor.dialog('#move-training-popup', {
                width: 710,
                title: M.util.get_string('movetrainingdialogtitle', 'local_trainings'),
                create: function (event, ui) {
                    // Select default training entity
                    trainingdata = M.table.data().toArray().find(function (x) {
                        return parseInt(x.id) === trainingid
                    });
                    $(this).find('#entity').val(trainingdata.entityid);
                },
                buttons: [
                    {
                        // Remove button
                        text: M.util.get_string('move', 'local_trainings'),
                        id: 'confirm-move-training',
                        class: "btn btn-primary",
                        click: function (e) {
                            var that = this;
                            var destentity = $('.ui-dialog #move-training-popup #entity').val();

                            format_edadmin.ajax_call({
                                url: M.cfg.wwwroot + '/local/trainings/ajax/ajax.php',
                                controller: 'training',
                                action: 'move_training',
                                format: 'json',
                                trainingid: trainingid,
                                destinationentity: destentity,
                                callback: function (response) {

                                    response = JSON.parse(response);

                                    if (response.success) {
                                        // Reload the table on success.
                                        M.table.ajax.reload();
                                    } else {
                                        console.log(response.message);
                                    }

                                    // Just close the modal.
                                    $(that).dialog("destroy");
                                }
                            });
                        }
                    },
                    {
                        // Cancel button
                        text: M.util.get_string('cancel', 'format_edadmin'),
                        class: "btn btn-secondary",
                        click: function () {
                            // Just close the modal.
                            $(this).dialog("destroy");
                        }
                    }
                ]
            });
        },

        createSession: function (trainingid, trainingname, trainingshortname) {

            var that = this;

            format_edadmin.ajax_call({
                url: M.cfg.wwwroot + '/local/session/ajax/ajax.php',
                controller: 'session',
                action: 'get_next_training_session_index',
                format: 'json',
                trainingname: trainingid,
                callback: function (response) {

                    response = JSON.parse(response);

                    // Check response
                    if (response.success) {

                        $('#createsessiondialogcontent').html(M.util.get_string('createsessiondialogcontent', 'local_trainings', trainingname));
                        $('#name-session-create-admin-session').val(trainingshortname + ' ' + response.message);
                        $('#session-name-used').html(M.util.get_string('sessionanmeused', 'local_trainings', trainingname));
                        $('#create-session-message').html('');

                        mentor.dialog('#create-session-popup', {
                            width: 710,
                            title: M.util.get_string('createsessiondialogtitle', 'local_trainings'),
                            create: function (event, ui) {
                                // Select default training entity
                                trainingdata = M.table.data().toArray().find(function (x) {
                                    return parseInt(x.id) === trainingid
                                });

                                var entity = $(this).find('#entity');

                                // Select the training entity if it's available.
                                if (entity.find('option[value=' + trainingdata.entityid + ']').length > 0) {
                                    entity.val(trainingdata.entityid);
                                }

                            },
                            buttons: [
                                {
                                    // Confirm button.
                                    text: M.util.get_string('tocreate', 'format_edadmin'),
                                    id: 'confirm-duplicate-training',
                                    class: "btn btn-primary",
                                    click: function (e) {
                                        var dialog = $(this);

                                        var sessionname = $('.ui-dialog #name-session-create-admin-session').val();

                                        // Check if the session name is empty.
                                        if (sessionname == '') {
                                            $('#create-session-message').html('Le nom abrégé de la session est obligatoire.');
                                            return;
                                        }

                                        $(e.target).attr("disabled", "disabled").html(M.util.get_string('pleasewait', 'local_trainings')).css('cursor', 'wait');

                                        // Check the main entity id in html data
                                        var entityid = $("#trainings-table").data('entityid');
                                        if ($('.ui-dialog #entity').length > 0 && $('.ui-dialog #entity').val() != '') {
                                            entityid = $('.ui-dialog #entity').val();
                                        }

                                        // Check subentity id in html
                                        if (Number(that.params.entityid) === entityid &&
                                            $('#destinationsubentity-container').length &&
                                            $('#destinationsubentity-container').data('subentity')) {
                                            entityid = $('#destinationsubentity-container').data('subentity');
                                        }

                                        format_edadmin.ajax_call({
                                            url: M.cfg.wwwroot + '/local/session/ajax/ajax.php',
                                            controller: 'session',
                                            action: 'create_session',
                                            format: 'json',
                                            trainingid: trainingid,
                                            sessionname: sessionname,
                                            entityid: entityid,
                                            callback: function (response) {

                                                response = JSON.parse(response);

                                                // Check response
                                                if (response.success) {
                                                    if (response.message !== -1) {
                                                        $('#session-name-used').css('display', 'none');
                                                        dialog.dialog("destroy");
                                                        // Add duplicate training to adhoc successsession
                                                        mentor.dialog('<div class="text-center"><span>' +
                                                            M.util.get_string('createtoaddhoc', 'local_trainings', trainingname) +
                                                            '</div>', {
                                                            title: M.util.get_string('confirmation', 'admin'),
                                                            width: 400,
                                                            height: 300,
                                                            buttons: [
                                                                {
                                                                    text: M.util.get_string('closebuttontitle', 'moodle'),
                                                                    class: "btn btn-primary",
                                                                    click: function () {
                                                                        $(this).dialog("destroy");
                                                                    }
                                                                }
                                                            ]
                                                        });
                                                    } else {
                                                        // Session name used
                                                        $(e.target).removeAttr("disabled").html(M.util.get_string('tocreate', 'format_edadmin')).css('cursor', 'pointer');
                                                        $('#session-name-used').css('display', 'block');
                                                    }
                                                } else {
                                                    // Duplication fail
                                                    format_edadmin.error_modal(response.message);
                                                    $('#session-name-used').css('display', 'none');
                                                }
                                            }
                                        });
                                    }
                                },
                                {
                                    // Cancel button
                                    text: M.util.get_string('cancel', 'format_edadmin'),
                                    class: "btn btn-secondary",
                                    click: function () {//Just close the modal
                                        $('#session-name-used').css('display', 'none');
                                        $(this).dialog("destroy");
                                    }
                                }
                            ],
                            close: function (event, ui) {
                                $('#session-name-used').css('display', 'none');
                                $(this).dialog("destroy");
                            },
                        });
                    } else {
                        // Response fail
                        format_edadmin.error_modal(response.message);
                    }
                }
            });
        },
        /**
         * Ajax call for training removal
         *
         * @param {int} trainingid
         */
        removeTraining: function (trainingid) {

            mentor.dialog('<div class="text-center"><span>' + M.util.get_string('removetrainingdialogcontent', 'local_trainings') + '</span></div>', {
                height: 300,
                width: 560,
                title: M.util.get_string('removetrainingdialogtitle', 'local_trainings'),
                buttons: [
                    {
                        // Remove button
                        text: M.util.get_string('confirm', 'format_edadmin'),
                        id: 'confirm-remove-training',
                        class: "btn btn-primary",
                        click: function (e) {
                            var that = this;
                            $(e.target).attr("disabled", "disabled");
                            format_edadmin.ajax_call({
                                url: M.cfg.wwwroot + '/local/trainings/ajax/ajax.php',
                                controller: 'training',
                                action: 'remove_training',
                                format: 'json',
                                trainingid: trainingid,
                                callback: function (response) {

                                    response = JSON.parse(response);

                                    // Check response
                                    if (response.success) {
                                        M.table.ajax.reload();
                                        $(that).dialog("destroy");
                                    } else {
                                        // Remove fail
                                        format_edadmin.error_modal(response.message);
                                    }
                                }
                            });
                        }
                    },
                    {
                        // Cancel button
                        text: M.util.get_string('cancel', 'format_edadmin'),
                        class: "btn btn-secondary",
                        click: function () {//Just close the modal
                            $(this).dialog("destroy");
                        }
                    }
                ],
                close: function (event, ui) {
                    $(this).dialog("destroy");
                },
            });
        }
    }

    //add object to window to be called outside require
    window.local_trainings = local_trainings;
    return local_trainings;
});
