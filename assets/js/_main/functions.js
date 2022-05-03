$.fn.hasAttr = function(name) {  
   return this.attr(name) !== undefined;
}

function round(num, decimals = 2) {
	let multiplier = Math.pow(10, decimals);
	return Math.round(num * multiplier) / multiplier;
}

function showPopup(elem) {
	if(!elem.hasClass('popup')) {
		elem = elem.parents('.popup');
	}

	elem.addClass('show');
	$('.popup-shield').addClass('show');
}

function isSet(variable) {
	return (typeof variable != 'undefined' && variable !== null);
}

function trim(str, chars = ' ') {
    let start = 0, 
        end = str.length;

    while(start < end && chars.indexOf(str[start]) >= 0)
        ++start;

    while(end > start && chars.indexOf(str[end - 1]) >= 0)
        --end;

    return (start > 0 || end < str.length) ? str.substring(start, end) : str;
}

function closestArrayItem(needle, values) {
	let closest = values.reduce(function(a, b) {
		return Math.abs(b - needle) < Math.abs(a - needle) ? b : a;
	});

	return closest;
}

function naturalJoin(list, between = ', ', end = 'and') {
	let result = false;

	if(list.length > 1) {
		let last = list[list.length - 1];
		list.splice(list.length - 1, 1);
		
		result = list.join(between) + end + last;

	} else {
		result = list[0];
	}

	return result;
}

function hidePopup(elem) {
	if(!elem.hasClass('popup')) {
		elem = elem.parents('.popup');
	}

	elem.removeClass('show');
	if($('.popup.show').length == 0) {
		$('.popup-shield').removeClass('show');
	}
}

$.fn.showLoading = function(hideAfter = 0) {
	if(this.find('> .loading-icon').length == 0) {
		this.append('<div class="loading-icon pl-1"><i class="far fa-circle-notch fa-spin loading-icon-inner text-category"></i></div>');
		
		if(hideAfter > 0) {
			setTimeout(function() {
				this.hideLoading();
			}, hideAfter);
		}
	}
}

$.fn.hideLoading = function() {
	if(this.find('> .loading-icon').length >= 0) {
		this.find('> .loading-icon').remove();
	}
}

$(document).ready(function() {
	$('.popup-shield').click(function() {
		hidePopup($('.popup.show, .popup-dialog.show').last());
	})

	$(document).on('keydown', function(e) {
		if(e.key == 'Escape') {
			$('.popup-shield').trigger('click');
		}
	})

	$('.popup-close').click(function() {
		$('.popup-shield').trigger('click');
	})
})

String.prototype.replaceAll = function(search, replace) {
	if (replace === undefined) {
		return this.toString();
	}
	return this.split(search).join(replace);
}

function numRange(min, max, step = 1) {
	min  = parseFloat(min);
	max  = parseFloat(max);
	step = parseFloat(step);

	let range = [];
	for(let i = min; i <= max; i += step){
		range.push(i);
	}
	return range;
}

function throttle(callback, delay) {
	let timeout;
	return function(e) {
		if (timeout) return;

		timeout = setTimeout(function() {
			callback(e), timeout=undefined
		}, delay);
	}
}

function debounce(callback, delay) {
  	let timeout;
	return function(...args) {
		clearTimeout(timeout);

		timeout = setTimeout(function() {
			callback(...args);
		}, delay)
	}
}

function arrayColumn(array, columnKey, indexKey = undefined) {
    if(typeof indexKey == 'undefined') {
        return array.map(function(item, index) {
            return item[columnKey];
        })
    } else {
        let output = [];

		array.forEach(function(item, index) {
			if(typeof item == 'undefined' || typeof item[columnKey] == 'undefined') {
                return true;
            }

            output[item[columnKey]] = item[indexKey];
		});

        return output;
    }
}

function sendMessage(translation_key, replace = [], isError = false , duration = 4000) {
	message = translate(translation_key, replace)
	let id = 'message-' + Date.now();

	let message_elem = $(`<div id="${id}" class="message animate-in"><div class="message-inner">${message}</div></div>`);
	message_elem.appendTo('.message-area');

	if(isError == true) {
		message_elem.addClass('is-error');
	}

	if(!userPrefersReducedMotion()) {
		setTimeout(function() {
			message_elem.removeClass('animate-in');
			setTimeout(function() {
				message_elem.addClass('animate-out');
				setTimeout(function() {
					message_elem.remove();
				}, 600);
			}, duration);
		}, 10);
	} else {
		setTimeout(function() {
			message_elem.remove();
		}, duration);
	}
}

function urlParam(key) {
	let url  = String(window.location).split('?');
	queryStr = '?' + url[url.length - 1];

	let urlParams = new URLSearchParams(queryStr);
	return urlParams.get(key);
}

function saveState(key, value, uuid = null) {
	return new Promise(function(resolve, reject) {
		thusPi.api.call('user-set-setting', {'key': key, 'value': value, 'uuid': uuid}).then(function() {
			resolve();
		}).catch(function() {
			reject();
		})
	})
}

function isArray(a) {
    return (!!a) && (a.constructor === Array);
}

function isObject(a) {
    return (!!a) && (a.constructor === Object);
}