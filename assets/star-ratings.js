jQuery(function() {
    jQuery('[data-star-rating]').each(function(index, element) {
        element = $(element);

        var data = element.data('star-rating'),
            globalOptions = window.StarRatingsOptions ? window.StarRatingsOptions : {},
            options = jQuery.extend(data.options, globalOptions, {
                callback: function(currentRating, element) {
                    $.post(data.uri, { id: data.id, rating: currentRating })
                     .done(function(response) {
                         response = JSON.parse(response);

                         var message = response.status ? response.message : 'Error: ' + response.message;
                         element.attr('data-tooltip', message).addClass('hover');

                         if (response.status) {
                            var parent = element.parent('.star-ratings');
                            parent.find('.star-score').text(response.data.score);
                            parent.find('.star-count span').text(response.data.count);
                         }

                         setTimeout(function() {
                             element.removeClass('hover');
                         }, 3000);
                     })
                     .fail(function(status, error, title) {
                         element.attr('data-tooltip', title).addClass('hover');
                         setTimeout(function() {
                             element.removeClass('hover');
                         }, 5000);
                     });
                }
            });

        if (element.data('voted')) {
            options.readOnly = true;
        }

        if (options.readOnly) {
            element.addClass('disabled');
        }

        element.starRating(options);
    });
});
