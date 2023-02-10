/**
 * Event for check iframe url and change if this is peertube url
 */

function eventPath(evt) {
    var path = (evt.composedPath && evt.composedPath()) || evt.path,
        target = evt.target;

    if (path != null) {
        // Safari doesn't include Window, but it should.
        return (path.indexOf(window) < 0) ? path.concat(window) : path;
    }

    if (target === window) {
        return [window];
    }

    function getParents(node, memo) {
        memo = memo || [];
        var parentNode = node.parentNode;

        if (!parentNode) {
            return memo;
        }
        else {
            return getParents(parentNode, memo.concat(parentNode));
        }
    }

    return [target].concat(getParents(target), window);
}

// Wait DOM load
window.addEventListener('load', function() {

    // Get all atto editors.
    var editors = document.getElementsByClassName('editor_atto_content');

    // Check if there are atto editors page.
    if (editors.length > 0) {

        for(var i = 0; i < editors.length; i++) {

            var editor = editors[i];

            // Check when an element is added in editor.
            editor.addEventListener("DOMNodeInserted", function (event) {

                var path = eventPath(event);

                // Is mentor snippet
                if($(path[0]).hasClass('mentor-video')) {
                    var iframe = $(path[0]).children()[0];
                    // Is Peertube watch page ?
                    if(iframe.src.startsWith("https://video.mentor.gouv.fr/") || iframe.src.startsWith("https://video-qua.mentor.gouv.fr/")) {
                        // Change the url to the url of the Peertube embed
                        iframe.src = iframe.src.replaceAll('/watch/', '/embed/');
                        iframe.src = iframe.src.replaceAll('/w/', '/videos/embed/');
                    }
                }
            });
        }

    }
});
