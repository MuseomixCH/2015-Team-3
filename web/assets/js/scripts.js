/*global google */
(function ($, google, GRES) {
	var map,
			mapContainer;

	mapContainer = $('#map');
	if (mapContainer.length > 0) {
		$.getJSON('/escape.json/' + GRES.escapeId, function(mapSettings) {
			map = new google.maps.Map(mapContainer.get(0), {
				center: mapSettings.centerCoordinates,
				zoom: mapSettings.zoom
			});
		});
	}

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
