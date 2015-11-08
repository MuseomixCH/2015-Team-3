/*global google */
(function ($, google, GRES) {
	var map,
			mapContainer,
			url,
			lineCoordinates = [],
			bounds,
			asideContainer;

	asideContainer = $('.l-aside');
	mapContainer = $('#map');

	var adaptLayout = function () {
		if ($(window).innerWidth() > 767) {
			asideContainer.height($(window).height());
			mapContainer.height($(window).height());
		} else {
			asideContainer.height('');
			mapContainer.height('');
		}
	};

	if (mapContainer.length > 0) {
		url = mapContainer.attr('data-url');

		$.getJSON(url, function(mapSettings) {
			map = new google.maps.Map(mapContainer.get(0), {
				center: mapSettings.centerCoordinates,
				zoom: mapSettings.zoom,
				mapTypeId: google.maps.MapTypeId.TERRAIN
			});

			bounds = new google.maps.LatLngBounds();
			mapSettings.artefacts.forEach(function (artefact) {

				artefact.tweets.forEach(function (tweet) {
					var marker,
							roadPath;

					marker = new google.maps.Marker({
						position: tweet.coordinates,
						title: tweet.name
					});
					bounds.extend(marker.getPosition());

					// Draws road line between tweets.
					lineCoordinates.push(marker.getPosition());
					roadPath = new google.maps.Polyline({
						path: lineCoordinates,
						geodesic: true,
						strokeColor: artefact.lineColor,
						strokeOpacity: 1.0,
						strokeWeight: 2
					});

					marker.setMap(map);
					roadPath.setMap(map);
				});
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

	$(window).resize(function() {
		adaptLayout();
	});

	adaptLayout();

})(jQuery, google, GRES);
