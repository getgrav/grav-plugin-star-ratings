jQuery(function() {
    jQuery('[data-star-rating]').each(function(index, element) {
        element = $(element);

        var data = element.data('star-rating'),
            globalOptions = window.StarRatingsOptions ? window.StarRatingsOptions : {},
            options = jQuery.extend(data.options, globalOptions, {
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

        if (options.readOnly) {
            element.addClass('disabled');
        }
        element.starRating(options);
    });
});
