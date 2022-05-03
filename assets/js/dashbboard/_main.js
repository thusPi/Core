$(document).on('thuspi.ready', function() {
	if(thusPi.page.current() != 'dashboard/main') {
		return;
	}

	if(typeof thusPi?.data?.dashboard?.widgets == 'undefined') {
		return;
	}

	$.each(thusPi.data.dashboard.widgets, function(widgetId, widget) {
		// Find widget element
		const elem = document.querySelector(`[data-widget-id="${widgetId}"]`);

		// Load the widget
		try {
			new widget(elem);
		} catch(err) {
			console.error(`An error occured while loading widget ${widgetId}:`, err);
		}
	})
})