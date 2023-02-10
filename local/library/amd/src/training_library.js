/**
 * Javascript containing function of the training library
 */

define([
    'jquery',
    'jqueryui',
    'local_mentor_core/mentor',
    'format_edadmin/format_edadmin',
    'local_mentor_core/select2',
], function ($, ui, mentor, format_edadmin, select2) {

    var trainingLibrary = {
        /**
         * Init JS
         */
        init: function (trainingId) {
            this.trainingId = trainingId;

            this.initTrainingObjective();

            this.enrolTrainingLibrary();

            this.importToEntityDialog();
        },
        /**
         * Init training catalog objective event show/hide text.
         */
        initTrainingObjective: function () {

            var initialHeight = $('.training-goal').height();

            // 100px is nearly equals to 4 text lines.
            if (initialHeight > 130) {
                $('.training-goal').addClass('truncate');
                $('#gradientmask').removeClass('hidden');
                $('.show-more').removeClass('hidden');
            }

            $('.show-more, #gradientmask').click(function (e) {
                $('.show-more').addClass('hidden');
                $('#gradientmask').addClass('hidden');
                $('.show-less').removeClass('hidden');
                $('.training-goal').removeClass('truncate');
            });

            $('.show-less').click(function (e) {
                $('.show-less').addClass('hidden');
                $('.show-more').removeClass('hidden');
                $('#gradientmask').removeClass('hidden');
                $('.training-goal').addClass('truncate');
            });

            $(window).on('resize', function () {
                $('.show-more:before').width($('#training-objective').width());
            });
        },
        /**
         * Enrol current user to training library.
         */
        enrolTrainingLibrary: function () {
            $('#demonstration-action').on('click', function () {
                format_edadmin.ajax_call({
                    url: M.cfg.wwwroot + '/local/library/ajax/ajax.php',
                    controller: 'library',
                    action: 'enrol_current_user',
                    format: 'json',
                    trainingid: trainingLibrary.trainingId,
                    callback: function (response) {
                        response = JSON.parse(response);

                        if (response.success) {
                            window.open(response.message, '_blank').focus();
                        } else {
                            format_edadmin.error_modal(response.message)
                        }
                    }
                });
            });
        },
        /**
         * Show import training library to entity modal
         * when user click to #import-action button
         */
        importToEntityDialog: function () {
            $('#import-action').on('click', function (e) {
                // If user click to info.
                if ($(e.target).hasClass('text-info')) {
                    return null;
                }

                $('#form-import-entity').select2()
                    .data('select2').$container.addClass("custom-select");

                format_edadmin.ajax_call({
                    url: M.cfg.wwwroot + '/local/library/ajax/ajax.php',
                    controller: 'training',
                    action: 'get_next_available_training_name',
                    format: 'json',
                    trainingid: trainingLibrary.trainingId,
                    callback: function (response) {
                        response = JSON.parse(response);

                        var trainingname = response.message;

                        trainingLibrary.showModalImportToEntity(trainingname);
                    }
                });
            });
        },
        /**
         * Show modal import to entity.
         *
         * @param {string} nextTrainingShortname
         */
        showModalImportToEntity: function(nextTrainingShortname) {
            // Set shortname training to form.
            $('#form-import-shortname').val(nextTrainingShortname);

            mentor.dialog('#import-to-entity', {
                width: 700,
                title: M.util.get_string('importoentity', 'local_library'),
                close: function () {
                    // Reset all form.
                    $('#popup-waring-message').html('');
                    $('#form-import-shortname').val(nextTrainingShortname);
                    $('#form-import-entity').val($('#form-import-entity option')[0].value).trigger('change');

                    // Close dialog.
                    $(this).dialog("destroy");
                },
                buttons: [
                    {
                        text: M.util.get_string('confirm', 'moodle'),
                        class: "btn btn-primary",
                        click: function () {
                            var wariningElement = $('#popup-waring-message');

                            wariningElement.html('');

                            // Get form data.
                            var formData = $('form').serializeArray();
                            var warning = false;

                            // Check if training shortname data is not empty.
                            var trainingShortname = formData[0].value;
                            if (!trainingShortname.length) {
                                wariningElement.append(
                                    '<p>' + M.util.get_string('trainingshortnamenotempty', 'local_library') + '</p>'
                                )
                                warning = true;
                            }

                            // Check if entity is select.
                            var entityId = formData[1].value
                            if (entityId === '0') {
                                wariningElement.append(
                                    '<p>' + M.util.get_string('entitymustbeselected', 'local_library') + '</p>'
                                )
                                warning = true;
                            }

                            // Check if warning message exist.
                            if (warning) {
                                return null;
                            }

                            // Call import request.
                            trainingLibrary.importToEntity(trainingShortname, entityId);
                        }
                    },
                    {
                        // Cancel button
                        text: M.util.get_string('cancel', 'moodle'),
                        class: "btn btn-secondary",
                        click: function () {//Just close the modal
                            $(this).dialog('close');
                        }
                    }
                ]
            });
        },
        /**
         * Call import training library to entity request.
         *
         * @param trainingShortname
         * @param entityId
         */
        importToEntity: function (trainingShortname, entityId) {
            format_edadmin.ajax_call({
                url: M.cfg.wwwroot + '/local/library/ajax/ajax.php',
                controller: 'library',
                action: 'import_to_entity',
                format: 'json',
                trainingid: trainingLibrary.trainingId,
                trainingshortname: trainingShortname,
                entityid: entityId,
                callback: function (response) {
                    response = JSON.parse(response);

                    if (!response.success) {
                        // Import fail.
                        format_edadmin.error_modal(response.message);
                    } else {
                        // Check if training shortname is used.
                        if (response.message === -1) {
                            $('#popup-waring-message').append(
                                '<p>' + M.util.get_string('trainingnameused', 'local_library') + '</p>'
                            )
                            return null;
                        }

                        // Import is OK.
                        $('#import-to-entity').dialog('destroy');

                        // Open confirm dialog.
                        trainingLibrary.confirmImport();
                    }
                }
            });
        },
        /**
         * Open import confirm dialog.
         */
        confirmImport: function () {
            mentor.dialog('<div class="text-center">' + M.util.get_string('confirmimport', 'local_library') + '</div>', {
                width: 500,
                title: M.util.get_string('confirmation', 'local_library'),
                buttons: [
                    {
                        // Cancel button
                        text: M.util.get_string('closebuttontitle', 'core'),
                        class: "btn btn-primary",
                        click: function () {//Just close the modal
                            $(this).dialog("close");
                        }
                    }
                ]
            });
        }
    };

    // Add object to window to be called outside require.
    window.trainingLibrary = trainingLibrary;
    return trainingLibrary;
});
