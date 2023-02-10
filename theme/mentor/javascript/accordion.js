// Collapse event
$(document).ready(function () {

    // Set aria-expanded to all card-header on the page.
    $('.card-header').each(function () {
        if ($(this).hasClass('opened')) {
            $(this).attr('aria-expanded', "true");
        } else {
            $(this).attr('aria-expanded', "false");
        }
    });

    // Set collapse position with set browser mobile size.
    $('body .mentor-accordion .card-header').each(function(index, element) {
        // When Collapse isn't in the atto snippet editor
        if (!$(element).parents('.editor_atto_content').length) {

            // Get target collapse.
            var collapseTarget = element.parentElement.parentElement;

            // Get target collapse element.
            var contentTarget = $(element.nextElementSibling, collapseTarget);

            // Get collapse mobile size data when page is loaded.
            var mobileSize = contentTarget.data('collapseMobileSize');

            // Is not mobile size.
            if(window.innerWidth > mobileSize) {
                return null;
            }

            // Get collapse mobile action data when page is loaded.
            var mobileAction = contentTarget.data('collapseMobileAction');

            // Collapse is close when browser has mobile size.
            if (mobileAction === 'close') {

                // Hidden target element.
                contentTarget.removeClass('show');

                // Remove 'opened' class to target collapse element header.
                $(element).removeClass('opened');

                // Set aria expanded to false.
                $(element).attr('aria-expanded', "false");
                $(element).find('button').attr('aria-expanded', "false");

                // Change header right target collapse element to close.
                $('.header-right', element).html('+');
            }

            // Collapse is open when browser has mobile size.
            if(mobileAction === 'open') {
                // Show target element.
                contentTarget.addClass('show');

                // Add 'opened' class to target collapse element header.
                contentTarget.addClass('opened');

                // Set aria expanded to true.
                $(element).attr('aria-expanded', "true");
                $(element).find('button').attr('aria-expanded', "true");

                // Change header right target collapse element indicator to open.
                $('.header-right', element).html('-');
            }
        }
    });

    $('body').on('click', '.card-header', function (event) {

        // When Collapse isn't in the atto snippet editor
        if (!$(event.currentTarget).parents('.editor_atto_content').length) {

            // Get target collapse
            var collapseTarget = event.currentTarget.parentElement.parentElement;

            // Get target collapse element
            var contentTarget = $(event.currentTarget.nextElementSibling, collapseTarget);

            // When target element is opened
            if (contentTarget.hasClass('show')) {

                // Hidden target element
                contentTarget.collapse('hide');

                // Remove 'opened' class to target collapse element header
                $(event.currentTarget).removeClass('opened');

                // Set aria expanded to false.
                $(event.currentTarget).attr('aria-expanded', "false");
                $(event.currentTarget).find('button').attr('aria-expanded', "false");

                // Change header right target collapse element to close
                $('.header-right', event.currentTarget).html('+');
            } else {// When target element is closed
                contentTarget.collapse('show');

                // Change header right target collapse element indicator to open
                $('.header-right', event.currentTarget).html('-');

                // Add 'opened' class to target collapse element header
                if (!$(event.currentTarget).hasClass('opened')) {
                    $(event.currentTarget).addClass('opened');

                    // Set aria expanded to true.
                    $(event.currentTarget).attr('aria-expanded', "true");
                    $(event.currentTarget).find('button').attr('aria-expanded', "true");
                }

                // Check all element in target collapse
                $('.collapse', collapseTarget).each(function (index, content) {

                    // Check if is not target collapse element
                    if (!$(content).is(contentTarget)) {

                        // Hidden other collapse element
                        $(content).collapse('hide');

                        // Change header right other collapse element indicator to open
                        $('.header-right', $(content).prev()).html('+');

                        // Remove 'opened' to class other collapse element header
                        if ($(content).prev().hasClass('opened')) {
                            $(content).prev().removeClass('opened').attr('aria-expanded', "false");
                        }
                    }
                });

            }
        }
    });
});
