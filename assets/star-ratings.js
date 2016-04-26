jQuery(function() {
    jQuery('[data-star-rating]').each(function(index, element) {
        element = $(element);
        var data = element.data('star-rating');

        element.starRating({
            starSize: 25,
            totalStars: data.total,
            disableAfterRate: false,
            initialRating: data.stars,
            callback: function(currentRating, element) {
                $.post(data.uri, { id: data.id, rating: currentRating })
                 .done(function() {
                    console.log('success');
                 })
                 .fail(function() {
                    console.log('fail');
                 });
            }
        });
    });
});
