/*global google */
(function ($, google, GRES) {
	var map,
			mapContainer;

	mapContainer = $('#map');
	if (mapContainer.length > 0) {
		$.getJSON('/escape-map.json/' + GRES.escapeId, function(mapSettings) {
			map = new google.maps.Map(mapContainer.get(0), {
				center: mapSettings.centerCoordinates,
				zoom: mapSettings.zoom
			});

			var bounds = new google.maps.LatLngBounds();
			mapSettings.tweets.forEach(function (tweet) {
				console.log(tweet);
				var marker = new google.maps.Marker({
					position: tweet.coordinates,
					title: tweet.name
				});

				bounds.extend(marker.getPosition());
				marker.setMap(map);
			});

			// Adapt the viewport of the map so we see all locations.
			map.fitBounds(bounds);
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
