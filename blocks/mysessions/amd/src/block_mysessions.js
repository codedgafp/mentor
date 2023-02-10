/**
 * Javascript containing function of the block mysessions
 */
define([
    'jquery',
    'local_mentor_core/pagination',
    "format_edadmin/format_edadmin"
], function ($, pagination, format_edadmin) {
    var block_mysessions = {
        /**
         * Init JS
         */
        init: function (showsessioncompleted) {

            // Replace session list header title in training sheet
            $('.block_mysessions .training-sheet .list-sessions .target-header-title')
                .text(M.util.get_string('session', 'block_mysessions'));

            // Init session block pagination : 12 sessions per page
            // If showsessioncompleted is :
            // - True : Show all session.
            // - False : Not show session completed.
            this.initPagination(showsessioncompleted ? '.block-session-tile' : '.block-session-tile:not(.is-completed)');

            // Redirect link event.
            $('.block_mysessions .session-more-information').on('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                var sessionId = $(event.currentTarget).data().sessionId;
                window.location.href = M.cfg.wwwroot + '/local/trainings/pages/training.php?sessionid=' + sessionId;
            });

            $('#input-show-completed-session')
                .click(function (e) {
                    // Update user preference.
                    $.ajax({
                        url: M.cfg.wwwroot + '/local/user/ajax/ajax.php',
                        data: {
                            controller: 'user',
                            action: 'set_user_preference',
                            preferencename: 'block_mysessions_completed',
                            value: $(e.target).is(':checked') ? '1' : '0'
                        }
                    });

                    // Reset CSS display value.
                    $('.block_mysessions .block-session-tile').hide();
                    $('#mysessions-pagination').show();

                    // Reset session block pagination : 12 sessions per page
                    // If .is(':checked') is :
                    // - True : Show all session.
                    // - False : Not show session completed.
                    block_mysessions.initPagination(
                        $(e.target).is(':checked') ? '.block-session-tile' : '.block-session-tile:not(.is-completed)'
                    );
                })
                // Set checkbox value with user preference.
                .prop('checked', showsessioncompleted);

            // Favourite action
            $('.block_mysessions .fav').on('click', function (event) {
                event.preventDefault();
                event.stopPropagation();

                var currentTarget = $(event.currentTarget);
                var sessionId = currentTarget.parent().data('sessionId');
                if ($(currentTarget).hasClass('fa-star-o')) {
                    block_mysessions.addFavourite(currentTarget, sessionId);
                } else {
                    block_mysessions.removeFavourite(currentTarget, sessionId);
                }
            });
        },
        /**
         * Add images into trainings tiles
         */
        addImages: function () {
            $('.block_mysessions .block-session-tile:visible').each(function () {
                // Adding image.
                var thumbnailDiv = $(this).find('div.session-tile-thumbnail-resize');
                var thumbnailUrl = thumbnailDiv.attr('data-thumbnail-url');
                thumbnailDiv.css('background-image', 'url(' + thumbnailUrl + ')');
            });
        },
        /**
         * Init session block pagination.
         *
         * @param {string} elementBlock
         */
        initPagination: function (elementBlock) {
            pagination.initPagination(
                $('.block_mysessions ' + elementBlock), $('#mysessions-pagination'), 12, true, this.addImages
            );
        },
        /**
         * Add session to user's favourite
         *
         * @param {jquery} currentTarget
         * @param {int} sessionId
         */
        addFavourite: function (currentTarget, sessionId) {
            // Call add session to user's favourite function
            format_edadmin.ajax_call({
                url: M.cfg.wwwroot + '/blocks/mysessions/ajax/ajax.php',
                plugintype: 'blocks',
                controller: 'session_favourite',
                action: 'add_favourite',
                format: 'json',
                sessionid: sessionId,
                callback: function (response) {

                    response = JSON.parse(response);

                    if (!response.success) {
                        console.error(response.message);
                        return;
                    }

                    var responseData = response.message;

                    if (responseData) {
                        // Hidden add favourite button
                        $(currentTarget).addClass('hidden');

                        // Display remove favourite button
                        $(currentTarget).parent().find('.fa-star').removeClass('hidden');
                    }
                }
            });
        },
        /**
         * Add session to user's favourite
         *
         * @param {jquery} currentTarget
         * @param {int} sessionId
         */
        removeFavourite: function (currentTarget, sessionId) {
            // Call remove session to user's favourite function
            format_edadmin.ajax_call({
                url: M.cfg.wwwroot + '/blocks/mysessions/ajax/ajax.php',
                plugintype: 'blocks',
                controller: 'session_favourite',
                action: 'remove_favourite',
                format: 'json',
                sessionid: sessionId,
                callback: function (response) {

                    response = JSON.parse(response);

                    if (!response.success) {
                        console.error(response.message);
                        return;
                    }

                    var responseData = response.message;

                    if (responseData) {
                        // Hidden remove favourite button
                        $(currentTarget).addClass('hidden');

                        // Display add favourite button
                        $(currentTarget).parent().find('.fa-star-o').removeClass('hidden');
                    }
                }
            });
        }
    };

    //add object to window to be called outside require
    window.block_mysessions = block_mysessions;
    return block_mysessions;
});
