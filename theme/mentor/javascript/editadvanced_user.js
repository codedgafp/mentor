// Get region field element
var regionElementSelect = $('#id_profile_field_region');

// Get if field1 element select exist
if (regionElementSelect.length) {
    // Get department field element
    var departmentElementSelect = $('#id_profile_field_department');
    // Get list region/department link
    var regions = $.parseJSON($('#regions').html());

    // Link region and department with regions filter
    fieldsLink(regionElementSelect, departmentElementSelect, regions);
}

// Force ldap_sync auth.
if ($('#id_auth option[value="ldap_syncplus"]').length > 0) {
    $('#id_auth').val('ldap_syncplus').css('pointer-events', 'none').css('background', '#ccc');
}

/**
 *  Setting main entity select input.
 *
 * @param {Array} formList
 */
var profileMainEntityFilter = function (formList) {

    // Get secondary entities select.
    var secondaryEntitySelect = $('#id_profile_field_secondaryentities').val();

    // Get main entity select.
    var mainEntitySelect = $('#id_profile_field_mainentity').val();

    // Remove entity already selected.
    var filterMainEntityFormList = formList.filter(function (opt) {
        return !secondaryEntitySelect.includes(opt);
    });

    // Create new list option select.
    var $select = $('#id_profile_field_mainentity');
    $select.empty(); // Remove old options.
    $.each(filterMainEntityFormList, function (key, value) {
        $select.append($("<option></option>")
            .attr("value", value).text(value));
    });
    // Set older element select.
    $select.val(mainEntitySelect);
};

/**
 * Setting secondary entities select input.
 *
 * @param formList
 */
var profileSecondaryEntitiesFilter = function (formList) {

    // Get secondary entities select.
    var secondaryEntitySelect = $('#id_profile_field_secondaryentities').val();

    // Get main entity select.
    var mainEntitySelect = $('#id_profile_field_mainentity').val();

    // Remove entity already selected.
    // "slice(1)" -> remove "...choose" choice
    var filterSecondaryEntityFormList = formList.slice(1).filter(function (opt) {
        return mainEntitySelect !== opt;
    });

    // Create new list option select.
    var $select = $('#id_profile_field_secondaryentities');
    $select.empty(); // Remove old options.
    $.each(filterSecondaryEntityFormList, function (key, value) {
        var optionHtml = "<option></option>";

        // Mark the elements already selected.
        if (secondaryEntitySelect.includes(value)) {
            optionHtml = "<option selected></option>";
        }

        $select.append($(optionHtml)
            .attr("value", value).text(value));
    });
};

// Check if input exist.
if ($('#id_profile_field_mainentity').length && $('#id_profile_field_secondaryentities').length) {

    // Get all entity data select.
    var formList = $.map($('#id_profile_field_mainentity').find('option'), function (opt) {
        return opt.text;
    });

    profileMainEntityFilter(formList);

    profileSecondaryEntitiesFilter(formList);

    // When main entity input change.
    $('#id_profile_field_mainentity').change(function () {
        profileSecondaryEntitiesFilter(formList);
    });

    // When secondary entities input change.
    $('#id_profile_field_secondaryentities').change(function () {
        profileMainEntityFilter(formList);
    });
}