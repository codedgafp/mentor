define(['jquery', 'jqueryui', 'local_mentor_core/mentor'], function ($, ui, mentor) {
    return {
        init: function () {
            var isValid = this.checkBrowser();

            // Check if the current browser match the requirements.
            if (!isValid) {
                this.displayErrorPopup();
            }

        },
        /**
         * Display an error popup
         */
        displayErrorPopup: function () {
            mentor.dialog('<div id="browserinvalid">' + M.util.get_string('invalidbrowseralert', 'theme_mentor') + '</div>', {
                dialogClass: "no-close",
                title: M.util.get_string('invalidbrowsertitle', 'theme_mentor'),
                width: 720,
                height: 300,
                closeOnEscape: false,
                open: function (event, ui) {
                    $(".ui-dialog-titlebar-close").hide();
                }
            });
        },
        /**
         * Check if the browser match the requirements
         *
         * @returns {boolean}
         */
        checkBrowser: function () {
            var browser = this.getBrowser();

            var requirements = {
                "Edge": 79,
                "Chrome": 66,
                "Firefox": 78,
                "Safari": 15
            };

            for (var browserName in requirements) {
                var version = requirements[browserName];

                if (browser.name == browserName && browser.version >= version) {
                    return true;
                }
            }

            return false;
        }
        ,

        /**
         * Get browser data
         *
         * @returns {{name: string, version: string}|{name: (*|string), version: number}|{name: string, version: string | string}}
         */
        getBrowser: function () {
            var ua = navigator.userAgent, tem, M = ua.match(/(opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*(\d+)/i) || [];
            if (/trident/i.test(M[1])) {
                tem = /\brv[ :]+(\d+)/g.exec(ua) || [];
                return {name: 'IE', version: (tem[1] || '')};
            }
            if (M[1] === 'Chrome') {
                tem = ua.match(/\bOPR|Edge\/(\d+)/)
                if (tem != null) {
                    return {name: 'Opera', version: tem[1]};
                }
            }
            M = M[2] ? [M[1], M[2]] : [navigator.appName, navigator.appVersion, '-?'];
            if ((tem = ua.match(/version\/(\d+)/i)) != null) {
                M.splice(1, 1, tem[1]);
            }
            return {
                name: M[0],
                version: parseInt(M[1])
            };
        }
    };
});