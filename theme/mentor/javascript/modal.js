// Set default option.
$.extend($.ui.dialog.prototype.options, {
    height: "auto",
    my: "center",
    at: "center",
    modal: true,
    draggable: false,
    dialogClass: 'confirm-dialog',
    open: function () {
        $('.ui-dialog-titlebar-close')
            .html('')
            .attr('title', M.util.get_string('closebuttontitle', 'moodle'));
    },
});

$.widget("ui.dialog", $.extend({}, $.ui.dialog.prototype, {
    // Replace "span" modal title element by "h1" element.
    _title: function (title) {
        var attrs = { };
        var titleText = this.options.title;

        $.each(title[0].attributes, function(idx, attr) {
            attrs[attr.nodeName] = attr.nodeValue;
        });

        title.replaceWith(function () {
            attrs.text = titleText;
            return $("<h1 />", attrs);
        });
    },
}));

var oldcr = $.ui.dialog.prototype._create;
$.ui.dialog.prototype._create = function(){
    oldcr.apply(this,arguments);
};