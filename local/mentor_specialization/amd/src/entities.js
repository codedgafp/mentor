/**
 * Javascript containing function of the admin entities
 */

define([
    'jquery',
    'local_entities/local_entities',
    'local_mentor_core/select2',
    'local_mentor_core/mentor',
    'jqueryui',
], function ($, local_entities, select2, mentor) {

    local_entities.create_entity_modal = function (event, issubentity) {
        event.preventDefault();

        // Element list
        var inputspacenameid = issubentity ? 'sub-entities-form-name' : 'entities-form-name';
        var inputresponsible = issubentity ? 'entities-form-parent-entity' : 'entities-form-email-responsible';
        var inputreflocal = 'entities-form-ref-local-entity';
        var warningclass = 'entities-form-warning-none';
        var formtemplate = issubentity ? '#sub-entities-form-template' : '#entities-form-template';

        $('.entities-form-maxlength').addClass('entities-form-warning-none');

        // Modal params
        var buttons = [// Modal buttons
            {
                text: M.util.get_string('save', 'format_edadmin'),
                class: 'btn-primary',
                id: 'saveentityform',
                click: function () {
                    //If name space input is not empty
                    var serializeddata = issubentity ? $('#sub-entities-form').serialize() : $('#entities-form').serialize();

                    var modalthis = $(this);

                    var spacename = $('#' + inputspacenameid).val();

                    // Check entity name length.
                    if (spacename.length > 200) {
                        $('#saveentityform').attr("disabled",false);
                        $('.entities-form-maxlength').removeClass('entities-form-warning-none');
                        return;
                    } else {
                        $('.entities-form-maxlength').addClass('entities-form-warning-none');
                    }

                    var ajaxcallparams = {
                        url: M.cfg.wwwroot + '/local/entities/ajax/ajax.php?' + serializeddata,
                        controller: 'entity',
                        action: 'create_entity',
                        format: 'json',
                        callback: function (response) {

                            response = JSON.parse(response);

                            if (response.success) {
                                modalthis.dialog('destroy');
                                M.table.ajax.reload();
                            } else {//If user not exist
                                console.log(response);
                                $('#saveentityform').attr("disabled",false);
                                $(formtemplate + ' .entities-form-warning').removeClass(warningclass).html(response.message);
                            }
                        }
                    };

                    if (issubentity) {
                        if ($('#' + inputresponsible).select2('data').length <= 0 || $('#sub-entities-form').serializeArray()[1].value === "") {
                            $(formtemplate + ' .entities-form-warning').removeClass(warningclass).html(M.util.get_string('requiredfields', 'local_mentor_core'));
                            return;
                        }
                        // Check if sub entity name is empty.
                        if ($('#sub-entities-form-name').val().trim().length === 0) {
                            $(formtemplate + ' .entities-form-warning').removeClass(warningclass).html(M.util.get_string('requiredfields', 'local_mentor_core'));
                            return;
                        }

                        if ($('#' + inputreflocal).select2('data').length > 0) {
                            ajaxcallparams.reflocalid = $('#' + inputreflocal).select2('data')[0].id;
                        }

                        ajaxcallparams.parentid = $('#' + inputresponsible).select2('data')[0].id;
                    } else {

                        if ($('#' + inputspacenameid).val().trim().length === 0 || $('#entities-form-shortname').val().trim().length === 0) {
                            $(formtemplate + ' .entities-form-warning').removeClass(warningclass).html(M.util.get_string('requiredfields', 'local_mentor_core'));
                            return;
                        }

                        ajaxcallparams.userid = 0
                        if ($('#' + inputresponsible).select2('data').length > 0) {
                            ajaxcallparams.userid = $('#' + inputresponsible).select2('data')[0].id
                        }
                    }

                    $('#saveentityform').attr("disabled","disabled");
                    format_edadmin.ajax_call(ajaxcallparams);
                    $(formtemplate + ' .entities-form-warning').addClass(warningclass);
                }
            },
            {
                text: M.util.get_string('cancel', 'format_edadmin'),
                class: 'btn-secondary',
                click: function () {//Just close the modal
                    $(formtemplate + ' .entities-form-warning').addClass(warningclass);
                    $(this).dialog("destroy");
                }
            }
        ];

        // Display add entity dialog.
        mentor.dialog(formtemplate, {
            width: 650,
            title: issubentity ? M.util.get_string('addsubentity', 'local_entities') : M.util.get_string('addentity', 'local_entities'),
            buttons: buttons,
            close: function (dialogEvent, ui) {
                $(formtemplate + ' .entities-form-warning').addClass(warningclass);
                $(this).dialog("destroy");
            },
            open: function () {
                $('#entities-form')[0].reset();
                $('#entities-form-regionid').select2();
                $('#' + inputresponsible).val(null).trigger("change");
                $('#' + inputspacenameid).val(null).trigger("change");
                $('#' + inputreflocal).val(null).trigger("change");
            }
        });

        // Manage entity manager dropdown list.
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
                                    text: item.shortname,
                                    id: item.id
                                }
                            })
                        }
                    }
                }
            }).data('select2').$container.addClass("custom-select");

            $('#' + inputreflocal).select2({
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
            }).data('select2').$container.addClass("custom-select");
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
            }).data('select2').$container.addClass("custom-select");
        }
    };

    return local_entities;
});
