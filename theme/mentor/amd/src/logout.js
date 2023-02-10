define(['jquery', 'local_mentor_core/cookie'], function ($, cookie) {
    return {
        init: function () {
            var that = this;

            // Clear cookies and session storage on user logout.
            $('.usermenu a[data-title="logout,moodle"]').on('click', function (e) {
                sessionStorage.setItem('mentor_local_catalog_selected_training', []);

                cookie.erase('catalogFilters');
                cookie.erase('catalogSearch');

                cookie.erase('libraryFilters');
                cookie.erase('librarySearch');
            });
        }
    };
});
