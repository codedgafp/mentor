/**
 * Javascript containing function of the admin trainings
 */

define([
    'jquery',
    'format_edadmin/format_edadmin',
    'local_trainings/local_trainings',
    'local_mentor_core/select2',
    'local_mentor_core/cookie',
    'local_mentor_core/url',
    'core/templates',
    'jqueryui',
    'local_mentor_core/datatables',
    'local_mentor_core/datatables-buttons',
    'local_mentor_core/jszip',
    'local_mentor_core/buttons.html5',
], function ($, format_edadmin, local_trainings, select2, cookie, url, templates) {

    /**
     * Create and init table and element's table
     *
     * @param {int} entityid
     */
    local_trainings.createAdminTable = function (entityid) {
        var that = this;

        that.cookieName = 'trainings_filter_entity_' + entityid;
        that.subEntityId = url.getParam('subentityid');

        // Init select filter
        $('#filter-actions .custom-select').select2();

        that.initFilterData();

        // Intialize filter event
        $('#filter-apply').click(function () {
            // Set data filter
            that.applyFilter();
        });

        // Reset filter event
        $("#filter-reset").click(function () {
            if (that.subEntityId) {
                that.initParams();
            }
            $('#filter-actions .custom-select').val(null).trigger('change');
            M.table.search('');
            that.applyFilter();
            that.setSearchCookieFilter();
        });

        // Export the table as csv.
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
                // Call edadmin course list.
                url: M.cfg.wwwroot + '/local/trainings/ajax/ajax.php',
                data: function (d) { // GET HTTP data setting.
                    d.controller = 'training';
                    d.action = 'get_trainings_by_entity';
                    d.format = 'json';
                    d.entityid = entityid;
                    d.filter = that.filter; // Filters data
                    d.onlymainentity = 0; // 1 (true) or 0 (false)
                },
            },
            fnDrawCallback: function () {
                $.ajax({
                    url: M.cfg.wwwroot + '/local/entities/ajax/ajax.php',
                    data: {
                        entityid: entityid,
                        action: 'has_sub_entities',
                        controller: 'entity',
                        format: 'json'
                    },
                }).done(function (response) {
                    response = JSON.parse(response);
                    if (response.message) {
                        // If has sus-entity
                        $('.subentity-data').css({'display': 'table-cell'});
                        $('.header-sub-entity').css({'display': 'table-cell'});
                        $('#sub-entity-filter').css({'display': 'flex'});
                    } else {
                        // If has not sus-entity
                        $('.subentity-data').css({'display': 'none'});
                        $('.header-sub-entity').css({'display': 'none'});
                        $('#sub-entity-filter').css({'display': 'none'});
                    }
                });
            },
            oLanguage: {
                sUrl: M.cfg.wwwroot + '/local/mentor_core/datatables/lang/' + M.util.get_string('langfile', 'local_trainings') + ".json"
            },
            serverSide: true,//For use Ajax
            processing: true,
            pageLength: 50,
            ordering: true,
            order: [],
            dom: 'Blfrtip',
            fixedColumns: true,
            oSearch: {
                "sSearch": that.filter.search
            },
            rowCallback: function (row, data) {
                that.actionsRenderPromise(data.actions, data.id, data.name, data.shortname)
                    .then(function (menuData) {
                        templates
                            .renderForPromise('local_mentor_core/custom_menu', menuData)
                            .then(function (_ref) {
                                $('td.action-admin-trainings', row).html(_ref.html);
                            });
                    });
            },
            buttons: [
                {
                    extend: 'csvHtml5',
                    charset: 'utf-8',
                    fieldBoundary: '',
                    fieldSeparator: ';',
                    bom: true,
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5],
                        format: {
                            body: function (data, row, column, node) {
                                // Replace br by \r\n.
                                if (column === 1) {
                                    return decodeURIComponent(data.replace(/<br\s*\/?>/ig, "."));
                                }
                                return decodeURIComponent(data.replace(/<.*?>/ig, ""));
                            }
                        }
                    },
                    stripNewlines: false,
                    text: 'Export CSV',
                    className: 'btn btn-secondary csvexport',
                    action: newexportaction
                }
            ],
            columns: [
                {
                    className: 'subentity-data',
                    data: 'subentityname'
                },
                {
                    className: 'collection-data',
                    data: function (data, type, row, meta) {
                        if (data.collectionstr === null) {
                            return '';
                        }

                        if (type === 'display') {
                            return data.collectionstr.replace(/;/g, '<br>');
                        }

                        return data.collectionstr.replace(/;/g, ' ');
                    }
                },
                {
                    className: 'training-name-data',
                    data: function (data, type, row, meta) {
                        if (type === 'display') {
                            return '<a href="' + data.url + '">' + data.name + '</a>';
                        }

                        return data.name;
                    }
                },
                {
                    className: 'training-sessions-data dt-body-center',
                    sortable: false,
                    data: function (data, type, row, meta) {
                        return (parseInt(data.sessions) === 0) ? '' : '<a href="' + data.urlsessions + '">' + data.sessions + '</a>';
                    }
                },
                {
                    className: 'idsirh-data',
                    data: 'idsirh'
                },
                {
                    className: 'training-status-data',
                    sortable: false,
                    data: function (data, type, row, meta) {
                        return '<span class="training-status" data-status="' + data.status + '">' + M.util.get_string(data.status, 'local_trainings') + '</span>';
                    }
                },
                {
                    searchable: false,
                    orderable: false,
                    className: 'action-admin-trainings',
                    width: '90px',
                    autoWidth: false,
                    data: function (data, type, row, meta) {
                        return '';
                    }
                }
            ]
        });

        M.table.on('search.dt', function () {
            that.setSearchCookieFilter();
        });
    };

    /**
     * Init filter's table data
     */
    local_trainings.initFilterData = function () {
        $cookieDateFilter = JSON.parse(cookie.read(this.cookieName));

        if (this.subEntityId) {
            if ($cookieDateFilter) {
                $cookieDateFilter.subentity = [this.subEntityId];
            } else {
                $cookieDateFilter = {
                    subentity: [this.subEntityId],
                    collection: [],
                    status: [],
                    search: ''
                };
            }
        }

        if ($cookieDateFilter) {
            this.filter = $cookieDateFilter;
            $('#sub-entity-select').val(this.filter.subentity).trigger('change');
            $('#collection-select').val(this.filter.collection).trigger('change');
            $('#status-select').val(this.filter.status).trigger('change');
            return;
        }

        this.filter = {
            subentity: '',
            collection: '',
            status: '',
            search: ''
        };
    };

    /**
     * Apply new data filter to table and set data to cookie's filter data
     */
    local_trainings.applyFilter = function () {

        var subEntitySelect = $('#sub-entity-select').val();

        if (subEntitySelect.length === 1) {
            if (subEntitySelect[0] !== this.subEntityId) {
                this.initParams();
            }
        } else {
            this.initParams();
        }

        // Set data filter
        this.filter.subentity = subEntitySelect;
        this.filter.collection = $('#collection-select').val();
        this.filter.status = $('#status-select').val();

        // Set cookie's filter data
        cookie.create(this.cookieName, JSON.stringify(this.filter));
        M.table.ajax.reload();
    };

    /**
     * Initialize parameters
     */
    local_trainings.initParams = function () {
        // Initialize url params
        url.removeParam('subentityid');

        // Initialize subEntityId param
        this.subEntityId = false;
    };

    /**
     * Set search data to cookie's filter
     */
    local_trainings.setSearchCookieFilter = function () {
        // If cookie's filter not exist
        if (!(JSON.parse(cookie.read(this.cookieName)) || M.table.search())) {
            return;
        }

        this.filter.search = M.table.search();
        cookie.create(this.cookieName, JSON.stringify(this.filter));
    };

    /**
     * Create Promise to rendering of the action buttons
     *
     * @param {Object[]} actions
     * @param {int} trainingid
     * @param {string} trainingname
     * @param {string} trainingshortname
     * @returns {Promise}
     */
    local_trainings.actionsRenderPromise = function (actions, trainingid, trainingname, trainingshortname) {

        return new Promise(function (resolve, reject) {

            if (typeof trainingname === 'undefined') {
                trainingname = '';
            }

            if (typeof trainingshortname === 'undefined') {
                trainingshortname = '';
            }

            var menuData = {};
            menuData.id = trainingid;
            menuData.items = [];

            var nbrActions = Object.keys(actions).length;

            $.each(actions, function (index, element) {
                var item = {};
                switch (index) {
                    case 'duplicatetraining' :
                        item = {
                            'id': index + trainingid,
                            'class': 'cursor-image-training-admin-trainings',
                            'onclick': 'local_trainings.duplicateTraining(' + trainingid + ');return false;',
                            'text': '<img src="' + M.util.image_url(index, 'local_trainings') + '" alt="' + index + '" title="' + M.util.get_string(index + 'tooltip', 'local_trainings') + '"> ' + M.util.get_string(index + 'tooltip', 'local_trainings')
                        };
                        break;
                    case 'createsessions' :
                        trainingname = trainingname.replace(/'/g, "\\'");
                        trainingshortname = trainingshortname.replace(/'/g, "\\'");
                        item = {
                            'id': index + trainingid,
                            'class': 'cursor-image-training-admin-trainings',
                            'onclick': 'local_trainings.createSession(' + trainingid + ',\'' + trainingname + '\',\'' + trainingshortname + '\');return false;',
                            'text': '<img src="' + M.util.image_url(index, 'local_trainings') + '" alt="' + index + '" onclick="local_trainings.createSession(' + trainingid + ',\'' + trainingname + '\',\'' + trainingshortname + '\')"> ' + M.util.get_string(index + 'tooltip', 'local_trainings')
                        };
                        break;
                    case 'deletetraining' :
                        item = {
                            'id': index + trainingid,
                            'class': 'cursor-image-training-admin-trainings',
                            'onclick': 'local_trainings.removeTraining(' + trainingid + ');return false;',
                            'text': '<img src="' + M.util.image_url(index, 'local_trainings') + '" alt="' + index + '"> ' + M.util.get_string(index + 'tooltip', 'local_trainings')
                        };
                        break;
                    case 'trainingsheet' :
                        item = {
                            'id': index + trainingid,
                            'href': element.url,
                            'text': '<img src="' + M.util.image_url(index, 'local_trainings') + '" alt="' + element.tooltip + '"> ' + M.util.get_string(index + 'tooltip', 'local_trainings')
                        };
                        break;
                    case 'movetraining' :
                        item = {
                            'id': index + trainingid,
                            'class': 'cursor-image-training-admin-trainings',
                            'onclick': 'local_trainings.moveTraining(' + trainingid + ');return false;',
                            'text': '<img src="' + M.util.image_url(index, 'local_trainings') + '" alt="' + index + '"> ' + M.util.get_string(index + 'tooltip', 'local_trainings')
                        };
                        break;
                    default :
                        item = {
                            'id': index + trainingid,
                            'href': actions[index],
                            'text': '<img src="' + M.util.image_url(index, 'local_trainings') + '" alt="' + index + '""> ' + M.util.get_string(index + 'tooltip', 'local_trainings')
                        };
                        break;
                }

                menuData.items.push(item);

                if (menuData.items.length === nbrActions) {
                    resolve(menuData);
                }
            });
        });
    };

    return local_trainings;
});
