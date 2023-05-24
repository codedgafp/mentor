// Assign roles page event.
define(['jquery', 'jqueryui', 'local_mentor_core/mentor'], function ($, ui, mentor) {
    return {
        init: function (exceptRolesName, isAdmin) {
            // Get page admin roles assign element.
            var assignRolePage = $('#page-admin-roles-assign');

            // Check if context page is sub-entity.
            if (!assignRolePage.hasClass('sub-entity') || isAdmin) {
                return;
            }

            // Get table element.
            var tableElement = $('#assignrole tbody tr');

            // Remove except role to table.
            tableElement.each(function (index, e) {
                // Get element role name.
                var elementRoleName = $(e).find('td:first-child a')[0].innerHTML;

                // Check if element role name is in role except  array.
                if (exceptRolesName.includes(elementRoleName)) {
                    $(e).remove();
                }
            });
        }
    };
});
