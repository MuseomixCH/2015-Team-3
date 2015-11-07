/*global google */
(function ($, google, GRES) {
	var map;

	$.getJSON('/escape.json/' + GRES.escapeId, function(mapSettings) {
		console.log(mapSettings);
	});

	var textAreaId = '#tweet-body';
	var textCountId = '#tweet-count';
	if($(textAreaId)) {
		var text_max = $(textAreaId).attr('maxlength');

		$(textCountId).html(text_max);

		$(textAreaId).keyup(function () {
			var text_length = $(this).val().length;
			var text_remaining = text_max - text_length;
			$(textCountId).html(text_remaining);
		});
	}

})(jQuery, google, GRES);
