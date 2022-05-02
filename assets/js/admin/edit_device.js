$(document).on('submit', 'form#form-edit-device', function(e) {
	e.preventDefault();

	let options = submitAndCreateDevice();
})

function submitAndCreateDevice() {
	let options = {};
	let inputs = $('input[data-setting], .input[data-setting]');

	inputs.each(function(key, input) {
		input = $(input);
		let option = input.attr('data-setting');
		options[option] = input.val();
	})
}

function updateDeviceSettings(handler_manifest, overwrite = false) {
	if('group' in handler_manifest) {
		setDropdownSearchValue('device_group', handler_manifest['group'], overwrite);
	}

	if('analytics_support' in handler_manifest && handler_manifest['analytics_support'] == true) {
		$('.device-setting[data-setting="draw_graphs"]').removeClass('hidden');
	} else {
		$('.device-setting[data-setting="draw_graphs"]').addClass('hidden');
	}
}

function loadHandlerOptions(handler, reloadOtherSettings = false) {
	if(handler != 'none') {
		handler_manifest_api = `/api/get/device_handler_manifest.php?handler=${handler}`;
		$.get(handler_manifest_api, function(response) {
			try {
				response = JSON.parse(response);
				if(response['success'] === true) {
					let handler_manifest = JSON.parse(response['message'])

					let device_options_elem  = $('.device-setting[data-setting="device_options"]');
					let device_options_inner = device_options_elem.find('.device-options-inner');
					let device_option_template = device_options_elem.find('.device-option-template');

					device_options_elem.addClass('hidden');

					if('options' in handler_manifest) {
						device_options_inner.find('.device-option').remove();

						$.each(handler_manifest['options'], function(option_name, option_data) {
							device_options_elem.removeClass('hidden');
							let device_option_entry = device_option_template.clone();
							device_options_inner.append(device_option_entry);
							
							let option_key = device_option_entry.find('.option-key span');
							let tooltip = device_option_entry.find('.option-key .tooltip');

							if(!('note' in option_data)) {
								if('type' in option_data) {
									let length = min = max = '';
									if('length' in option_data) {
										length = ` (${option_data['length']} digits long)`;
									}

									if('min' in option_data && 'max' in option_data) {
										min = ` between ${option_data['min']}`;
										max = ` and ${option_data['max']}`;
									} else if('min' in option_data) {
										min = ` above ${option_data['min']}`;
									} else if('max' in option_data) {
										max = ` below ${option_data['max']}`;
									}

									switch(option_data['type']) {
										case 'integer':
											option_data['note'] = `An integer${length}${min}${max}.`;
											break;
										case 'number':
											option_data['note'] = `A number${length}${min}${max}.`;
											break;
										case 'ip':
											option_data['note'] = `A valid IP address`;
											break;
										case 'alphanumeric':
											if(option_name == 'filter') {
												option_data['note'] = 'Used for filtering the data wanted. Multiple filters can be seperated with an ampersand (&).';
											}
									}
								}
							}

							if('note' in option_data) {
								tooltip.attr('data-tooltip', option_data['note']).css({'display': ''});
							} else {
								tooltip.removeAttr('data-tooltip').hide();
							}
							
							let option_value = device_option_entry.find('.option-value');
							option_key.text(option_name);
							option_value.attr('placeholder', option_data['default']).attr('data-type', option_data['type']);
	
							if('min' in option_data) option_value.attr('data-min', option_data['min']);
							if('max' in option_data) option_value.attr('data-max', option_data['max']);
							if('length' in option_data) option_value.attr('data-length', option_data['length']);
	
							device_option_entry.removeClass('device-option-template').addClass('device-option').show();
						})
					}

					if(reloadOtherSettings == true) {
						updateDeviceSettings(handler_manifest, true);
					} 
				} else {
					throw 'Api returned invalid response'
				}
			} catch(err) {
				console.error('Something went wrong while refreshing handler options.', err);
			}
		})
	}
}