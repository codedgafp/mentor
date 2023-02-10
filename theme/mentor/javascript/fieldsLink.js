/**
 * Extend the Array prototype with an asyncForEach method
 */
Object.defineProperty(Array.prototype, "asyncForEach", {
    enumerable: false,
    value: function (task) {
        return new Promise((resolve, reject) => {
            this.forEach(function (item, index, array) {
                task(item, index, array);
                if (Object.is(array.length - 1, index)) {
                    resolve({status: 'finished', count: array.length});
                }
            });
        });
    }
});

// Save last field2 select by field1
var lastfield2SelectByField1 = [];

var field2InitValue = '';

/**
 * Change field2 select by field1 and last field2 select by this field1
 *
 * @param {String} field1Select
 * @param {jQuery} field2
 * @param {Array} arrayLink
 */
var changefield2ElementSelectOption = function (field1Select, field2, arrayLink) {

    // Remove all option field2 element
    field2.empty();

    if (field1Select in arrayLink) {

        var options = JSON.parse(JSON.stringify(arrayLink[field1Select]));

        if (arrayLink[field1Select].length > 1) {
            field2.append(new Option('Choisir...', ''));
        }

        // Add field2 option include in field1 select
        options.asyncForEach(function (newOption) {
            field2.append(new Option(newOption, newOption));
        }).then(function () {

            if (Object.values(options).includes(field2InitValue) && field2InitValue.length > 0) {
                // Select default value.
                field2.val(field2InitValue);
            } else {
                // Select first field2 in option list when no field2 has already been selected
                var firstOptionField2ElementSelect = field2.find(':first').val();

                // Set last field2 select with first option
                lastfield2SelectByField1[field1Select] = firstOptionField2ElementSelect;

                // Get field2 option value with first option
                field2.val(firstOptionField2ElementSelect);
            }
        });
    }

};

/**
 * Link field1 and field2 with array link filter
 * When you change field1, function looks at the elements to be displayed
 * in the field2 with the link array
 *
 * @param {jQuery} field1
 * @param {jQuery} field2
 * @param {Array} arrayLink
 */
var fieldsLink = function (field1, field2, arrayLink) {
    // Get field1 value
    var field1DefaultSelect = field1.val();

    field2InitValue = field2.val();

    // Check if value is not null
    if (field1DefaultSelect != '' && field1DefaultSelect.length === 0) {

        field1DefaultSelect = field1.find(':first').next().val();
        field1.val(field1DefaultSelect);

        // Get default field2 option select when page init
        lastfield2SelectByField1[field1DefaultSelect] = field2.val();
    }

    // Set field2 when page init to see only field2 include to field1
    changefield2ElementSelectOption(field1DefaultSelect, field2, arrayLink);

    // Set change event to field2 to change the visible field2 otpion
    field1.change(function () {
        field2InitValue = '';
        changefield2ElementSelectOption(field1.val(), field2, arrayLink);
    });

    // Set last field2 select by this field1 when change field2 select
    field2.change(function () {
        lastfield2SelectByField1[field1.val()] = field2.val();
    });
}