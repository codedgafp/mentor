/**
 * Add all events to the selection on the card
 *
 * @param {jQuery} addCard
 */
var cardEvents = function (addCard) {

    // Add card
    $(addCard).on('click', function(eventClick) {
        var cardMentor = $(eventClick.target).parent();
        $(cardMentor.clone()).insertAfter(cardMentor);
    });

    // Remove card
    var closeCard = $(addCard).next();
    $(closeCard).on('click', function(eventClick) {
        var cardMentor = $(eventClick.target).parent();
        cardMentor.remove();
    });
};

// Wait DOM load
window.addEventListener('load', function() {
    // Check if is atto editor page
    if (document.getElementById('id_introeditoreditable')) {
        // Add events in cards existingg
        cardEvents('.add-card');
        document.getElementById('id_introeditoreditable').addEventListener("DOMNodeInserted", function (event) {
            // Add events in new card when is add with atto snippet
            if ($(event.target).hasClass('cards-mentor')) {
                cardEvents(event.target.firstElementChild.firstElementChild);
            }

            // Add events in new card when is add with card button
            if ($(event.target).hasClass('card-mentor')) {
                cardEvents(event.target.firstElementChild);
            }
        });
    }
});
