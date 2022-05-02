$(document).on('click', '.btn', function() {
	var button = $(this);
	var button_array = button.parents('.btn-array, .btn-list').first();
	if(!button.hasClass('no-active')) {
		if(button_array.length > 0) {
			var type = button_array.attr('data-type');
			if(type == 'single') {
				if(button.hasClass('active')) {
					button.removeClass('active').trigger('change');
				} else {
					button_array.find('.btn').removeClass('active');
					button.addClass('active').trigger('change');
				}
			} else {
				button.toggleClass('active').trigger('change');
			}
		} else {
			button.toggleClass('active').trigger('change');
		}
	}
})