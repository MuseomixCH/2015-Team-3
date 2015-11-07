/*global google */
(function ($, google, GRES) {
	var map;

	$.getJSON('/escape.json/' + GRES.escapeId, function(mapSettings) {
		console.log(mapSettings);
	});

})(jQuery, google, GRES);
