var mouseDown = false;
var screen_orientation = (screen.orientation || screen.mozOrientation || screen.msOrientation);
var html = $('html');

function toggleDragSelection(enable = true) {
	if(!enable) {
		if(window.getSelection) {
			var selection = window.getSelection ();
			selection.deleteFromDocument ();

			if (!selection.isCollapsed) {
				var selRange = selection.getRangeAt (0);
				selRange.deleteContents ();
			}

			if (selection.anchorNode) {
				selection.collapse (selection.anchorNode, selection.anchorOffset);
			}
		}
	}

	$('body').attr('data-select', enable);
}

function isAlphaNumeric(str, allowSpecialChars = []) {
	let specialCharsList, code, i, len;

	if(allowSpecialChars == true) {
		specialCharsList = [32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47];
	} else {
		specialCharsList = allowSpecialChars;
	}

	for (i = 0, len = str.length; i < len; i++) {
		code = str.charCodeAt(i);
		if (
			!(code > 47 && code < 58) &&
			!(code > 64 && code < 91) &&
			!(code > 96 && code < 123) &&
			!(specialCharsList.includes(code))
		) {
			return false;
		}
	}
	return true;
}

function togglePassword(button, input) {
	if(input.attr('type') == 'password') {
		input.attr('type', 'text');
		button.find('.show-password').show();
		button.find('.hide-password').hide();
	} else {
		input.attr('type', 'password');
		button.find('.show-password').hide();
		button.find('.hide-password').show();
	}
}

function openDialog(title = translate('popup.dialog.title'), description = translate('popup.dialog.description'), yes_text = translate('state.yes'), yes_function, no_text = translate('state.no'), no_function = '') {
	$('body').find('.popup.popup-dialog').remove();
	$('.popup-shield').addClass('show');
	var dialog = `<div class="popup popup-dialog"><h3 class="tile-title">${title}</h3><div class="text-muted mb-2">${description}</div><div class="row"><div class="col-auto mr-auto px-0"><div type_button class="btn-sm bg-tertiary btn-primary">${yes_text}</div></div><div class="col-auto ml-auto px-0"><div type_button class="btn-sm bg-tertiary btn-primary" onclick="hidePopup($(this)); ${no_function}">${no_text}</div></div></div></div>`;
	$('.body-inner').append(dialog);
	setTimeout(function() {
		$('.body-inner').find('.popup.popup-dialog').addClass('show');
	}, 1);
}

$(document).ready(function() {
	if($('body').find('.header-top').length > 0) {
		let height = $('body').find('.header-top').outerHeight();
		$('body').css('padding-top', `${height}px`);
	}
})

function clamp(min, val, max) {
	if(val < min) {
		return min;
	} else if(val > max) {
		return max;
	} else {
		return val;
	}
}

$(document).on('mousedown touchstart', function(e) {
	switch(e.which) {
		case 1:
			thusPiAssign('data.pointer.down.primary', true);
			break;
		case 2:
			thusPiAssign('data.pointer.down.middle', true);
			break;
		case 3:
			thusPiAssign('data.pointer.down.secondary', true);
			break;
	}
})

$(document).on('mouseup touchend', function(e) {
	switch(e.which) {
		case 1:
			thusPiAssign('data.pointer.down.primary', null);
			break;
		case 2:
			thusPiAssign('data.pointer.down.middle', null);
			break;
		case 3:
			thusPiAssign('data.pointer.down.secondary', null);
			break;
	}
})

$(document).on('mousedown mousemove mouseup touchstart touchmove touchend', function(e) {
	if(typeof e.pageX == 'undefined') { // Touch devices
		thusPiAssign('data.pointer.x', e.changedTouches[0].pageX);
		thusPiAssign('data.pointer.y', e.changedTouches[0].pageY);
	} else {
		thusPiAssign('data.pointer.x', e.pageX);
		thusPiAssign('data.pointer.y', e.pageY);
	}
})