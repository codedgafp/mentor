var CSS = {
        INPUTURL: 'atto_video_urlentry',
        INPUTSUBMIT: 'atto_video_urlentrysubmit',
    },
    COMPONENTNAME = 'atto_video',
    SELECTORS = {
        INPUTURL: '.' + CSS.INPUTURL
    },
    TEMPLATE = '' +
        '<form class="atto_form">' +
        '<div class="mb-1">' +
        '<input class="form-control fullwidth {{CSS.INPUTURL}}" type="url" ' +
        'id="{{elementid}}_{{CSS.INPUTURL}}" size="32"/>' +
        '</div>' +
        '<div id="attovideo-error"></div><br/>' +
        // Add the submit button and close the form.
        '<button class="btn btn-secondary {{CSS.INPUTSUBMIT}}" type="submit">{{get_string "save" component}}</button>' +
        '</div>' +
        '</form>',
    VIDEOTEMPLATE = '' +
        '<div class="mentor-video embed-responsive embed-responsive-16by9">' +
        '<iframe class="embed-responsive-item" sandbox="allow-same-origin allow-scripts allow-popups" src="{{url}}"' +
        ' frameborder="0" allowfullscreen>' +
        '</iframe>' +
        '</div>';

Y.namespace('M.atto_video').Button = Y.Base.create('button', Y.M.editor_atto.EditorPlugin, [], {


    /**
     * A reference to the current selection at the time that the dialogue
     * was opened.
     *
     * @property _currentSelection
     * @type Range
     * @private
     */
    _currentSelection: null,

    _allowedDomains: null,

    initializer: function () {

        this._allowedDomains = this.get('alloweddomains');

        this.addButton({
            icon: 'icon',
            iconComponent: 'atto_video',
            callback: this._displayDialogue,
            tags: 'img',
            tagMatchRequiresAll: false
        });
    },

    _displayDialogue: function () {

        // Store the current selection.
        this._currentSelection = this.get('host').getSelection();
        if (this._currentSelection === false) {
            return;
        }

        var title = M.util.get_string('pluginname', 'atto_video');


        if (this._allowedDomains != '') {
            title += '<a class="btn btn-link p-0"  role="button" data-container="body" data-toggle="popover"' +
                ' data-placement="right" data-content="<div>' + M.util.get_string('allowed_domain', 'atto_video') + ' : ' + this._allowedDomains + '</div>" data-html="true"' +
                ' tabindex="0"' +
                ' data-trigger="focus"><i' +
                ' class="icon fa fa-question-circle text-info fa-fw"></i></a>';
        }

        var dialogue = this.getDialogue({
            headerContent: title,
            width: 'auto',
            focusAfterHide: true,
            focusOnShowSelector: SELECTORS.INPUTURL
        });

        // Set a maximum width for the dialog. This will prevent the dialog width to extend beyond the screen width
        // in cases when the uploaded image has larger width.
        dialogue.get('boundingBox').setStyle('maxWidth', '90%');
        // Set the dialogue content, and then show the dialogue.
        dialogue.set('bodyContent', this._getDialogueContent())
            .show();
    },

    /**
     * Return the dialogue content for the tool, attaching any required
     * events.
     *
     * @method _getDialogueContent
     * @return {Node} The content to place in the dialogue.
     * @private
     */
    _getDialogueContent: function () {
        var template = Y.Handlebars.compile(TEMPLATE),
            content = Y.Node.create(template({
                elementid: this.get('host').get('elementid'),
                CSS: CSS,
                component: COMPONENTNAME
            }));

        this._form = content;

        this._form.one('.' + CSS.INPUTSUBMIT).on('click', this._setVideo, this);

        return content;
    },

    /**
     * Update the video in the contenteditable.
     *
     * @method _setImage
     * @param {EventFacade} e
     * @private
     */
    _setVideo: function (e) {
        var form = this._form,
            videourl = form.one('.' + CSS.INPUTURL).get('value'),
            host = this.get('host');

        e.preventDefault();

        // CHeck if url field is empty.
        if (videourl == '') {
            form.one('#attovideo-error').setHTML(M.util.get_string('erroremptyurl', 'atto_video'));
            return;
        }

        // Check domain name.
        if (this._allowedDomains != '') {
            var found = false;
            for (var i = 0; i < this._allowedDomains.length; i++) {
                var url = this._allowedDomains[i];

                if (videourl.startsWith(url)) {
                    found = true;
                    break;
                }
            }

            if (!found) {
                form.one('#attovideo-error').setHTML(M.util.get_string('errorallowedurl', 'atto_video'));
                return;
            }
        }

        // Convert the URL to an embed url.
        videourl = videourl.replaceAll('/watch/', '/embed/');
        videourl = videourl.replaceAll('/w/', '/videos/embed/');

        // Add settings at the end of the url.
        if (videourl.indexOf('?') == -1) {
            videourl += '?title=0&warningTitle=0&peertubeLink=0';
        } else {
            videourl += '&title=0&warningTitle=0&peertubeLink=0';
        }

        // Get the video template.
        var template = Y.Handlebars.compile(VIDEOTEMPLATE),
            content = template({
                url: videourl,
            });

        // Focus on the editor in preparation for inserting the video.
        host.focus();

        host.setSelection(this._currentSelection);

        // Insert the html content into the editor.
        this.get('host').insertContentAtFocusPoint(content);

        this.markUpdated();

        // Close the dialogue.
        this.getDialogue({
            focusAfterHide: null
        }).hide();

    }
}, {
    ATTRS: {
        alloweddomains: {
            value: {}
        }
    }
});
