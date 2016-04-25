$(function() {
	$('.star-rating-container').starRating({
	    starSize: 25,
	    callback: function(currentRating, $el) {
	    	var id = $el.closest('.star-rating-container').data('id');
	        $.post('/star_rating', { id: id, rating: currentRating })
	         .done(function() {
	         	console.log('success');
	         })
	         .fail(function() {
	         	console.log('fail');
	         });
	    }
	});
});
