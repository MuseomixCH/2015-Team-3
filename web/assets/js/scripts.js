/*global google */
(function ($, google, GRES) {
	var map,
			mapContainer,
			url,
			lineCoordinates = [];

	mapContainer = $('#map');
	if (mapContainer.length > 0) {
		url = mapContainer.attr('data-url');

		$.getJSON(url, function(mapSettings) {
			map = new google.maps.Map(mapContainer.get(0), {
				center: mapSettings.centerCoordinates,
				zoom: mapSettings.zoom,
				mapTypeId: google.maps.MapTypeId.TERRAIN
			});

			var bounds = new google.maps.LatLngBounds();
			mapSettings.tweets.forEach(function (tweet) {

				var marker = new google.maps.Marker({
					position: tweet.coordinates,
					title: tweet.name
				});

				bounds.extend(marker.getPosition());
				lineCoordinates.push(marker.getPosition());

				marker.setMap(map);
			});

			// Draws road line between tweets.
			var roadPath = new google.maps.Polyline({
				path: lineCoordinates,
				geodesic: true,
				strokeColor: mapSettings.lineColor,
				strokeOpacity: 1.0,
				strokeWeight: 2
			});

			roadPath.setMap(map);

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
