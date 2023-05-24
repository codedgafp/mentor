/**
 * Javascript containing function of the block archivedsessions
 */
define([
    'jquery',
    'local_mentor_core/pagination',
], function ($, pagination) {
    var block_archivedsessions = {
        /**
         * Init JS
         */
        init: function (params) {
            var that = this;
            this.params = params;

            // Replace session list header title in training sheet
            $('.block_archivedsessions .training-sheet .list-sessions .target-header-title').text(M.util.get_string('session', 'block_archivedsessions'));

            // Init session block pagination
            // 12 sessions per page
            pagination.initPagination($('.block-archivedsessions .block-session-tile'), $('#archivedsessions-pagination'), 12, true, this.addImages);

            // Redirect link event.
            $('.block-archivedsessions .session-more-information').on('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                var sessionId = $(event.currentTarget).data().sessionId;
                window.location.href = M.cfg.wwwroot + '/local/trainings/pages/training.php?sessionid=' + sessionId;
            });

            // Event for open or close trainings block
            $('.block_archivedsessions .open-block').on('click', function (e) {
                var openButton = $(e.currentTarget);
                var blockArchivedSessions = $(e.currentTarget).closest('.block_archivedsessions')[0];
                if (blockArchivedSessions.classList.contains('hidden-block')) {
                    // Open block
                    blockArchivedSessions.classList.remove('hidden-block');
                    openButton.html('<span class="txt">' + M.util.get_string('showless', 'block_archivedsessions') + '</span>-');
                    $('.block-archivedsessions').show();

                    // The block must be visible to add images.
                    that.addImages();
                } else {
                    // Close block
                    blockArchivedSessions.classList.add('hidden-block');
                    openButton.html('<span class="txt">' + M.util.get_string('showmore', 'block_archivedsessions') + '</span>+');
                    $('.block-archivedsessions').hide();
                }
            });

        },
        /**
         * Add images into trainings tiles
         */
        addImages: function () {
            $('.block_archivedsessions .block-session-tile:visible').each(function () {
                // Adding image.
                var thumbnailDiv = $(this).find('div.session-tile-thumbnail-resize');
                var thumbnailUrl = thumbnailDiv.attr('data-thumbnail-url');
                thumbnailDiv.css('background-image', 'url(' + thumbnailUrl + ')');
            });
        },
    };

    //add object to window to be called outside require
    window.block_archivedsessions = block_archivedsessions;
    return block_archivedsessions;
});
