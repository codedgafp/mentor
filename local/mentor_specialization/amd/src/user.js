/**
 * Javascript containing the utils function of local user plugin
 */

define([
    'jquery',
    'format_edadmin/format_edadmin',
    'local_user/local_user',
    'local_mentor_core/mentor',
    'local_mentor_core/cookie',
    'local_mentor_core/select2',
    'jqueryui',
    'local_mentor_core/datatables',
    'local_mentor_core/datatables-buttons',
    'local_mentor_core/datatables-select',
    'local_mentor_core/datatables-checkboxes',
    'local_mentor_core/buttons.html5',
], function ($, format_edadmin, local_user, mentor, cookie, select2) {

    /**
     * Init filter's table data
     */
    local_user.initFilterData = function () {
        $cookieDateFilter = JSON.parse(cookie.read(this.cookieName));

        if ($cookieDateFilter) {
            this.filter = $cookieDateFilter;
            this.status = this.filter.status;
            $('#status').val(this.filter.status).trigger('change');
            return;
        }

        this.filter = {
            status: ''
        };
    };

    /**
     * Create table
     */
    local_user.createUserTable = function () {
        var that = this;

        that.status = 'all';

        that.initFilterData();

        // Add user button.
        $('#addusers').on('click', function (e) {
            e.preventDefault();
            that.addUserPopup();
            return false;
        });

        // Intialize filter event
        $('#filter-apply').click(function () {
            // Set data filter
            that.applyFilters();
        });

        // Reset filter event
        $("#filter-reset").click(function () {
            that.resetFilters();
        });

        //table course edadmin user admin
        M.table = $('#user-admin-table').DataTable({
            ajax: {
                //Call data members cohort
                url: M.cfg.wwwroot + '/local/user/ajax/ajax.php',
                data: function (d) {// GET HTTP data setting
                    d.controller = 'entity';
                    d.action = 'get_members';
                    d.entityid = that.entityid;
                    d.status = that.status;
                    d.mainonly = true;
                    d.format = 'json';
                },
                dataSrc: 'message'
            },
            oLanguage: {
                sUrl: M.cfg.wwwroot + '/local/mentor_core/datatables/lang/' + M.util.get_string('langfile', 'local_user') + ".json"
            },
            order: [[3, "asc"]],
            pageLength: 50,
            dom: 'Blfrtip',
            buttons: [
                {
                    extend: 'csvHtml5',
                    charset: 'utf-8',
                    fieldBoundary: '',
                    fieldSeparator: ';',
                    bom: true,
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4],
                        format: {
                            body: function (data, row, column, node) {
                                // Replace br by \r\n.
                                // Remove span element.
                                if (column === 1) {
                                    return decodeURIComponent(data.replace(/<br\s*\/?>/ig, ".").replace(/<\/?[^>]*>/ig, ""));
                                }
                                return decodeURIComponent(data.replace(/<.*?>/ig, "").replace(/<\/?[^>]*>/ig, ""));
                            }
                        }
                    },
                    stripNewlines: false,
                    text: M.util.get_string('exportlistusers', 'local_user'),
                    title: 'export_utilisateurs_' + this.entityshortname + '_' + $.datepicker.formatDate('yy-mm-dd', new Date()),
                    className: 'btn btn-secondary csvexport',
                    action: local_user.newexportaction
                }
            ],
            columns: [
                {
                    data: 'lastname',
                    render: function (data, type, row, meta) {
                        if (row.suspended == 1) {
                            return '<span class="user-suspended">' + data + '</span>';
                        }
                        return '<a href="' + M.cfg.wwwroot + '/user/profile.php?id=' + row.id + '">' + data + '</a>';
                    }
                },
                {
                    data: 'firstname',
                    render: function (data, type, row, meta) {
                        if (row.suspended == 1) {
                            return '<span class="user-suspended">' + data + '</span>';
                        }
                        return data;
                    }
                },
                {
                    data: 'email',
                    render: function (data, type, row, meta) {
                        if (row.suspended == 1) {
                            return '<span class="user-suspended">' + data + '</span>';
                        }
                        return data;
                    }
                },
                {
                    data: 'entityshortname',
                    render: function (data, type, row, meta) {
                        if (row.suspended == 1) {
                            return '<span class="user-suspended">' + data + '</span>';
                        }
                        return data;
                    }
                },
                {
                    data: 'lastconnection',
                    render: {
                        _: function (data, type, row, meta) {
                            var result = row.lastconnection.timestamp != 0 ? row.lastconnection.display : M.util.get_string('neverconnected', 'local_user');

                            if (row.suspended == 1) {
                                result = '<span class="user-suspended">' + result + '</span>';
                            }
                            return result;
                        },
                        sort: 'timestamp'
                    }
                },
                {
                    //create two button action per user (link profil and  remove to cohort)
                    data: 'id',
                    className: 'user-admin-table-actions',
                    render: function (data, type, row, meta) {
                        if (row.hasconfigaccess) {
                            if (type !== 'display') {
                                return data;
                            }
                            return '<div class="user-admin-button-action">' +
                                '<div class="btn-userprofile button-action">' +
                                '<a href="' + row.profileurl + '&returnto=' + encodeURI(window.location.href) + '">' +
                                '<img src="' + M.util.image_url('gear', 'local_user') + '" alt="adminuserprofil">' +
                                '</a>' +
                                '</div>' +
                                '</div>';
                        }

                        return '';
                    }
                }
            ]
        });
    };

    /**
     * Apply filters on user table
     */
    local_user.applyFilters = function () {
        this.status = $('#status').val();

        // Set data filter
        this.filter.status = $('#status').val();

        // Set cookie's filter data
        cookie.create(this.cookieName, JSON.stringify(this.filter));

        M.table.ajax.reload();
    };

    /**
     * Reset table filters
     */
    local_user.resetFilters = function () {
        $('#status').val('').trigger('change');
        this.applyFilters();
    };

    /**
     * Create add user modal
     */
    local_user.addUserPopup = function () {

        var thatBis = this;

        var warningMessageDisplayClass = 'user-admin-form-warning-none';

        // Get all main entity data select.
        var formMainEntityList = $.map($('#user-admin-form-add-entity').find('option'), function (opt) {
            return {value: opt.value, text: opt.text};
        });

        // Get all secondary entity data select.
        var formSecondaryEntityList = $.map($('#user-admin-form-add-secondary-entities').find('option'), function (opt) {
            return {value: opt.value, text: opt.text};
        });

        mentor.dialog('#user-admin-add', {
            width: 600,
            title: M.util.get_string('adduser', 'local_user'),
            buttons: [
                {
                    text: M.util.get_string('confirm', 'format_edadmin'),
                    id: 'confirm-add-user',
                    class: "btn-primary",
                    click: function (e) {
                        var that = $(this);
                        var dataFrom = $('#user-admin-form-add').serializeArray();

                        if (dataFrom[0].value === '' || dataFrom[1].value === '' || dataFrom[2].value === '') {
                            $('.user-admin-form-warning').removeClass(warningMessageDisplayClass).html(M.util.get_string('requiredfields', 'local_mentor_core'));
                        } else {
                            $('.user-admin-form-warning').addClass(warningMessageDisplayClass).html('');

                            format_edadmin.ajax_call({
                                url: M.cfg.wwwroot + '/local/user/ajax/ajax.php?' + $('#user-admin-form-add').serialize(),
                                controller: 'user',
                                action: 'create_and_add_user',
                                format: 'json',
                                callback: function (response) {
                                    response = JSON.parse(response);
                                    if (response === true) {
                                        M.table.ajax.reload();
                                        $('.user-admin-form-warning').addClass(warningMessageDisplayClass).html('');
                                        $('#user-admin-form-add')[0].reset();
                                        thatBis.secondaryEntitiesFilterForm(formSecondaryEntityList);
                                        thatBis.mainEntityFilterForm(formMainEntityList);
                                        that.dialog("destroy");
                                    } else {
                                        var warningMessage = '';
                                        if (response === -1) { // Email is used
                                            warningMessage = M.util.get_string('erroremailused', 'local_mentor_core');
                                        } else if (response === -2) { // Email is not allowed
                                            warningMessage = M.util.get_string('invalidemail', 'local_mentor_core');
                                        } else { // Other problem
                                            warningMessage = M.util.get_string('erroreother', 'local_mentor_core');
                                        }
                                        $('.user-admin-form-warning').removeClass(warningMessageDisplayClass).html(warningMessage);
                                    }
                                }
                            });
                        }
                    }
                },
                {
                    // Cancel button
                    text: M.util.get_string('cancel', 'format_edadmin'),
                    class: "btn-secondary",
                    click: function (e) {//Just close the modal
                        $('.user-admin-form-warning').addClass(warningMessageDisplayClass).html('');
                        $('#user-admin-form-add')[0].reset();
                        thatBis.secondaryEntitiesFilterForm(formSecondaryEntityList);
                        thatBis.mainEntityFilterForm(formMainEntityList);
                        $(this).dialog("destroy");
                    }
                }
            ],
            close: function (event, ui) {
                $('.user-admin-form-warning').addClass(warningMessageDisplayClass).html('');
                $('#user-admin-form-add')[0].reset();
                thatBis.secondaryEntitiesFilterForm(formSecondaryEntityList);
                thatBis.mainEntityFilterForm(formMainEntityList);
                $(this).dialog("destroy");
            },
        });

        // Create select2 input.
        $('#user-admin-form-add-secondary-entities').select2();

        // When user change main entity select.
        $('#user-admin-form-add-entity').change(function () {
            thatBis.secondaryEntitiesFilterForm(formSecondaryEntityList);
        });

        // When user change secondary entities select.
        $('#user-admin-form-add-secondary-entities').change(function () {
            thatBis.mainEntityFilterForm(formMainEntityList);
        });
        /**
         * Setting main entity select input.
         *
         * @param {Array} formEntityList
         */
        local_user.mainEntityFilterForm = function (formEntityList) {

            // Get secondary entities select.
            var mainEntitySelect = $('#user-admin-form-add-entity').val();

            // Get secondary entities select.
            var secondaryEntitiesSelect = $('#user-admin-form-add-secondary-entities').val();

            // Remove entity already selected.
            var filterMainEntityFormList = formEntityList.filter(function (opt) {
                return !secondaryEntitiesSelect.includes(opt.value);
            });

            // Create new list option select.
            var $select = $('#user-admin-form-add-entity');
            $select.empty(); // Remove old options.
            $.each(filterMainEntityFormList, function (key, value) {
                $select.append($("<option></option>")
                    .attr("value", value.value).text(value.text));
            });
            // Set older element select.
            $select.val(mainEntitySelect);
        };
        /**
         * Setting secondary entities select input.
         *
         * @param {Array} formEntityList
         */
        local_user.secondaryEntitiesFilterForm = function (formEntityList) {

            // Get main entity select.
            var mainEntitySelect = $('#user-admin-form-add-entity').val();

            // Get secondary entities select.
            var secondaryEntitiesSelect = $('#user-admin-form-add-secondary-entities').val();

            // Remove entity already selected.
            var filterSecondaryEntitiesFormList = formEntityList.filter(function (opt) {
                return mainEntitySelect !== opt.value;
            });

            // Create new list option select.
            var $select = $('#user-admin-form-add-secondary-entities');
            $select.empty(); // Remove old options.
            $.each(filterSecondaryEntitiesFormList, function (key, value) {
                var optionHtml = "<option></option>";
                $select.append($(optionHtml)
                    .attr("value", value.value).text(value.text));
            });
            // Set older elements select.
            $select.val(secondaryEntitiesSelect).trigger('change');
        };
    };

    // Export the table as csv.
    local_user.newexportaction = function (e, dt, button, config) {
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

    return local_user;

});
