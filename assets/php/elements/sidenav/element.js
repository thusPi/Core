$(document).ready(function() {
	$('#module-sidenav').on('mouseenter', function() {
		sidenav = $(this);
		sidenav.addClass('expand');
		setTimeout(() => {
			if(sidenav.hasClass('expand')) {
				sidenav.addClass('expanded');
			}
		}, 300);
	}).on('mouseleave', function() {
		sidenav = $(this);
		sidenav.removeClass('expand expanded');
	})

	$('#module-sidenav .sidenav-link').click(function() {
		var page = $(this).attr('data-page');
		loadPage(page);
	})
})