var updateValuesTimeout;

thusPiAssign('devices', {
	updateValuesLoop(interval = 2500) {
		if(thusPi.page.current() != 'devices/main') {
			return false;
		}

		thusPi.devices.updateValues();
		
		updateValuesTimeout = setTimeout(function() {
			thusPi.devices.updateValuesLoop(interval);
		}, interval);
	},

	updateValues() {
		thusPi.api.call('devices-get-values', {}).then(function(response) {
			$.each(response.data, function(deviceId, properties) {
				const $device = $(`.device[data-id="${deviceId}"]`);

				if($device.length == 0) {
					return true;
				}

				if($device.attr('data-value') == properties['value']) {
					return true;
				}

				if($device.attr('data-value-updating-disabled') == 'true') {
					return true;
				}

				$device.find('.device-control').value(properties.value, properties.shown_value);
				$device.find('.device-name').text(properties.name);
				$device.attr('data-value', properties.value);
			})
		})
	}
})

$(document).on('thusPi.ready', function() {
	if(thusPi.page.current() != 'devices/main') {
		console.log(updateValuesTimeout);
		clearTimeout(updateValuesTimeout);
	}

	thusPi.devices.updateValuesLoop()
});

$(document).ready(function() {
	$('.range-thumb').on('touchstart', function() {
		$(this).siblings('.range-tooltip').css('opacity', 1);
	}).on('touchend', function() {
		$(this).siblings('.range-tooltip').css('opacity', 0);
	})
})

$(document).on('input', '.device-control[data-type="range"]', function() {
	let $wrapper = $(this);
	$wrapper.parents('.device').attr('data-value-updating-disabled', 'true');
});

function loadDeviceSearchResults($device) {
	let $input  = $device.find('.input[data-type="search"]');
	let handler = $device.attr('data-search-handler');
	let term    = $input.val();
	let url     = `${thusPi.data.webroot}/assets/device_handlers/public/${handler}/search.php?id=${$device.attr('data-id')}&value=${term}`;

	$input.parent().showLoading();
	$.get(url, function(response) {
		$input.parent().hideLoading();
		try {
			response = JSON.parse(response);
			if(response['success'] == true) {
				$input.clearResults();
				$.each(response['results'], function(i, result) {
					$input.appendResult(result['value'], result['title'], result['title'], result['description'], result['thumbnail']);
				})
			} else {
				throw response['message'];
			}
		} catch (err) {
			sendMessage(l('generic.error'), [], true);
			console.error(err);
		}
	})
}

$(document).on('click', '.categories-list-row', function() {
	let inactiveCategories = [];
	
	$('.categories-list-row').find('.category-button').each(function() {
		const $btn = $(this);

		if(!$btn.hasClass('active')) {
			inactiveCategories.push($btn.attr('data-category'));
		}
	})

	thusPi.users.currentUser.setSetting('devices_inactive_categories', inactiveCategories).then(function() {
		thusPi.page.reload();
	}).catch();
})

$(document).on('thusPi.search_value_change', '.device .input[data-type="search"]', function() {
	let $input  = $(this);
	let $device = $input.parents('.device');

	setDeviceValue($device, $input.getValue(), $input.getShownValue());
})

$(document).on('change', '.device .device-control:not([data-type="search"])', function() {
	let $control = $(this);
	let $device  = $control.parents('.device');
	let value    = $control.value();

	setDeviceValue($device, value, value);
})

function setDeviceValue($device, value, shownValue = undefined) {
	console.log(`Trying to set value of device ${$device.attr('data-id')} to ${value}.`);
	
	$device.attr('data-value-updating-disabled', 'true');

	let controlType = $device.attr('data-control-type');
	let $deviceName = $device.find('.device-name');
	let deviceName  = $deviceName.text();
	let id          = $device.attr('data-id');
	let handler     = $device.attr('data-handler');

	$deviceName.showLoading();

	if(typeof shownValue == 'undefined') {
		shownValue = value;
	}

	let data = {
		'id':          id,
		'value':       value,
		'shown_value': shownValue,
		'force_set':   $device.attr('data-force-set')
	};
	
	thusPi.api.call('device-set-value', data).then(function() {
		$device.attr('data-value-updating-disabled', 'false');
		$deviceName.hideLoading();

		console.log('Device value changed!');

		if(controlType == 'toggle') {
			thusPi.message.send(thusPi.locale.translate(`devices.message.toggled_${value}_success`, [deviceName]));
		} else {
			thusPi.message.send(thusPi.locale.translate(`devices.message.changed_success`, [deviceName, shownValue]));
		}

	}).catch(function() {
		$device.attr('data-value-updating-disabled', 'false');
		$deviceName.hideLoading();

		console.error('Failed to change device value.');

		if(controlType == 'toggle') {
			thusPi.message.error(thusPi.locale.translate(`devices.error.toggled_${value}_error`, [deviceName]));
		} else {
			thusPi.message.error(thusPi.locale.translate(`devices.error.changed_error`, [deviceName, shownValue]));
		}
	})
}

function toggleFavorite($device) {
	if($device.attr('data-favorite') == 'true') {
		$device.attr('data-favorite', 'false');
	} else {
		$device.attr('data-favorite', 'true');
	}

	saveFavoriteDevicesList();
}

function saveFavoriteDevicesList() {
	let favorites = [];
	$('.device[data-favorite="true"]').each(function() {
		favorites.push($(this).attr('data-id'));
	})
	favorites = favorites.join(',');

	saveState('devices_favorite', favorites);
}