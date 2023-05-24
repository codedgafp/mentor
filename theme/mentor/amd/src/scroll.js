define(['jquery', 'jqueryui', 'theme_mentor/headroom'], function ($, ui, headroom) {
    return {
        init: function () {

            var myElement = document.querySelector(".navbar");

            // Construct an instance of Headroom, passing the element
            if (myElement) {
                headroom.options.tolerance = {
                    up: 0,
                    down: 50
                };

                var hr = new headroom(myElement);
                // initialise
                hr.init();
            }
        }
    };
});
