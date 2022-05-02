$(document).on('click', '.admin-user-flag [data-setting]', function() {
	$(this).attr('data-value', !($(this).attr('data-value') === 'true'));
})

$(document).on('click', '.admin-user-flag[data-flag="is_admin"] [data-setting]', function() {
	updateFlagsInactive(this);
})

function updateFlagsInactive(elem) {
	if($(elem).attr('data-setting') == 'true') {
		$('.admin-user-flag:not([data-flag="is_admin"])').addClass('flag-inactive');
	} else {
		$('.admin-user-flag').removeClass('flag-inactive');
	}
}