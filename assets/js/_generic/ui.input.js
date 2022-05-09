/* ================================ */
/*        INPUT TYPE: SEARCH        */
/* ================================ */
thusPiAssign('ui.input.search', class {
	constructor($input) {
		this.$input = $input;

		// Wrap input
		this.$input.wrap('<div class="input-wrapper input-wrapper-search"></div>');

		// Store wrapper
		this.$wrapper = $input.parents('.input-wrapper.input-wrapper-search').first();

		// Create results list
		this.$wrapper.append('<ul class="input-search-results btn-column"></ul>');

		// Store results list
		this.$results = this.$wrapper.find('.input-search-results').first();
	
		// Find existing results list
		const $existingResults = $(`ul.input-search-results[for="${this.$input.attr('name')}"]`);
		if($existingResults.length > 0) {
			$existingResults.find('li').each((i, result) => {
				const $result = $(result);

				const value = $result.attr('value');
				const text = $result.text();

				this.addResult({
					value: value,
					description: $result.attr('data-description'),
					text: text, 
					href: $result.attr('href'), 
					onclick: $result.attr('onclick'),
					match: $result.attr('data-match'),
					iconHTML: $result.attr('data-icon-html')
				});

				if(isSet($result.attr('selected'))) {
					this.$input.value(value, text);
				}
			})

			$existingResults.remove();
		}

		this.$wrapper.on('click', function(e) {e.stopPropagation()});
		
		this.$input.on('click', (e) => {this._focusEvent(e)});
		this.$input.on('input', (e) => {this._inputEvent(e)});
		this.$input.on('change', (e) => {this._changeEvent(e)});

		this.$results.on('click', '.input-search-result', (e) => {this._resultClickEvent(e)});

		$(document).on('click', (e) => {this._focusOutEvent(e)});
	}

	addResults(results) {
		let html = '';
		$.each(results, function(i, result) {
			if(!isSet(result.value)) {
				return true;
			}

			result.text  = result.text || result.value;
			result.match = result.match || result.text;
			
			let $result;
			const elType = isSet(result.href) ? 'a' : 'li';

			if(isSet(result.description) || isSet(result.thumbnail) || isSet(result.iconHTML)) {
				$result = $(`<${elType} class="input-search-result input-search-result-rich btn bg-tertiary btn-secondary flex-row" value="${result.value}" data-match="${result.match}" data-shown-value="${result.text}"></${elType}>`);
				
				$result.append(`<span class="input-search-result-title">${result.text}</span>`);

				if(isSet(result.description)) {
					$result.append(`<span class="input-search-result-description flex-row">${result.description}</span>`);
				}

				if(isSet(result.thumbnail)) {
					$result.append(`<span class="input-search-result-thumbnail"><img src="${result.thumbnail}"></span>`);
				} else if(isSet(result.iconHTML)) {
					$result.append(`<span class="input-search-result-thumbnail">${result.iconHTML}</span>`);
				}

			} else {
				// A plain search result
				$result = $(`<${elType} class="input-search-result btn bg-tertiary btn-secondary" value="${result.value}" data-match="${result.match}" data-shown-value="${result.text}">${result.text}</${elType}>`);
			}

			// Set custom attributes if specified
			if(isSet(result.href)) { $result.attr('href', result.href); }          // href
			if(isSet(result.onclick)) { $result.attr('onclick', result.onclick); } // onclick
			if(isSet(result.match)) { $result.attr('data-match', result.match); }  // data-match

			html += $result.prop('outerHTML');
		})

		// Append the results
		this.$results.append(html);

		// Refresh results list
		this._resultsFilter(this.$input.val());
	}

	addResult(result) {
		return this.addResults([result]);
	}

	_focusEvent(e) {
		// Close all wrappers and results
		$('.input-wrapper').removeClass('active');
		$('.input-search-results').removeClass('show');

		this.$wrapper.addClass('active');
		this._resultsToggleAll(true);
		this._resultsToggle(true);
	}

	_focusOutEvent(e) {
		this.$wrapper.removeClass('active');
		this._resultsToggle(false);
	}

	_inputEvent(e) {
		e.preventDefault();
		if(this.$input.attr('filter-on') != 'change') {
			this._resultsFilter(this.$input.val());
		}
	}

	_changeEvent(e) {
		e.preventDefault();
		if(this.$input.attr('filter-on') == 'change') {
			this._resultsFilter(this.$input.val());
		}
	}

	_resultsToggle(show = true) {
		this.$results.toggleClass('show', show);
	}

	_resultsToggleAll(show = true) {
		this.$results.find('.input-search-result[data-match]').toggleClass('show', show);
	}

	_resultsFilter(query, caseSensitive = false) {
		if(!caseSensitive) {
			query = query.toLowerCase(); 
		}

		this.$results.find('.input-search-result[data-match]').each(function(i, result) {
			let $result = $(result);
			let match   = $result.attr('data-match');

			if(!caseSensitive) {
				match = match.toLowerCase();
			}
			
			$result.toggleClass('show', match.includes(query));
		})

		this.$results.attr('data-search-matches', this.$results.find('.input-search-result[data-match].show').length);
	}

	_resultClickEvent(e) {
		const $result = $(e.target).closest('.input-search-result');
		const value   = $result.attr('value');
		const text    = $result.attr('data-shown-value');

		this.$results.find('.input-search-result.active').removeClass('active');
		$result.addClass('active');

		this.$wrapper.removeClass('active');
		
		if(this.$wrapper.attr('data-select-type') != 'multiple') {
			this._resultsToggle(false);
		}

		if(isSet($result.attr('href'))) {
			return;
		}

		if(this.$input.value() == value) {
			return false;
		}
		
		this.$input.value(value, text);
		this.$input.trigger('thusPi.change');

	}
})

$.fn.value = function(value = undefined, shownValue = undefined) {
	const $input = this;
	const input  = $input.get(0);
	
	shownValue = shownValue || value;

	if(typeof value == 'undefined') {
		if($input.attr('data-type') == 'search') {
			return $input.attr('data-value');
		} else if(typeof input.value != 'undefined') {
			return $input.value;
		} else if(typeof $input.attr('data-value') != 'undefined') {		
			return $input.attr('data-value');
		} else if($input.attr('contenteditable') == 'true') {
			return $input.text();
		}

		return undefined;
	} else {
		if($input.attr('data-type') == 'search') {
			$input.attr('data-value', value);
			$input.attr('value', shownValue).val(shownValue);
			$input.siblings('.input-search-results').find('.input-search-result').removeClass('active');
			$input.siblings('.input-search-results').find(`.input-search-result[value="${value}"]`).addClass('active');
		} else if(typeof input.value != 'undefined') {
			$input.value = value;
		} else if(typeof $input.attr('data-value') != 'undefined') {		
			$input.attr('data-value', value);
		} else if($input.attr('contenteditable') == 'true') {
			$input.text(value);
		}
	}

	return $input;
}

$(document).on('click', '.input[data-type="checkbox"]', function() {
	let checkbox = $(this);
	
	if(checkbox.attr('value') == 'on') {
		checkbox.attr('value', 'off');
	} else {
		checkbox.attr('value', 'on');
	}
})

// Make range thumbs draggable
$(document).on('thuspi.load', function() {
	$(document).find('.input[data-type="range"]').each(function() {
		let $input = $(this);

		if($input.children().length === 0) {
			$input.html('<div class="range-thumb"></div><div class="range-track"></div><div class="range-tooltip"></div>');
		}
	})

	$(document).find('.input[data-type="range"] .range-thumb').each(function() {
		let $thumb   = $(this);
		let $wrapper = $thumb.parent();
		let values   = numRange($wrapper.attr('data-min') || 0, $wrapper.attr('data-max') || 1, $wrapper.attr('data-step') || 1);
		let value    = $wrapper.value() ? $wrapper.value() : parseFloat($wrapper.attr('data-min'));
		
		moveRangeThumb($thumb, value, 0);
	})

	$(document).find('.input[data-type="range"] .range-thumb').draggable({
		axis: 'x',
		containment: 'parent',
		drag() {
			let $thumb   = $(this);
			let $wrapper = $thumb.parent();
			let $tooltip = $thumb.siblings('.range-tooltip');
			let values   = numRange($wrapper.attr('data-min') || 0, $wrapper.attr('data-max') || 100, $wrapper.attr('data-step') || 1);
			let perc     = Math.round((($thumb.position().left*1.01 + ($thumb.outerWidth() * Math.round($thumb.position().left / $wrapper.outerWidth() * 10) / 10)) / $wrapper.outerWidth() * 100) * 1000) / 1000;
			let closest  = closestArrayItem(perc, values);

			$tooltip.text(closest);
			$tooltip.css('left', $thumb.position().left + $thumb.outerWidth()/2 - $tooltip.outerWidth()/2);

			$wrapper.attr('data-value', closest);
			$wrapper.trigger('input');
		},
		stop(event, ui) {
			let $thumb   = $(this);
			let $wrapper = $thumb.parent();
			let $tooltip = $thumb.siblings('.range-tooltip');
			let values   = numRange($wrapper.attr('data-min') || 0, $wrapper.attr('data-max') || 100, $wrapper.attr('data-step') || 1);
			let closest  = closestArrayItem(parseFloat($tooltip.text()), values);

			$wrapper.attr('data-value', closest);
			$wrapper.trigger('change');
		}
	})
})

document.addEventListener('mousewheel', function(e) {
    let action    = e.wheelDelta > 0 ? 'increase' : 'decrease';
	let $wrappers = $('.input[data-type="range"]');

	$wrappers.each(function() {
		let $wrapper = $(this);
		if(!$wrapper.is(':hover') && !$wrapper.is(':focus')) {
			return true;
		}

		e.preventDefault();

		let $thumb   = $wrapper.find('.range-thumb');
		let value    = $wrapper.value();
		let min      = parseFloat($wrapper.attr('data-min')) || 0;
		let max      = parseFloat($wrapper.attr('data-max')) || 100;
		let step     = parseFloat($wrapper.attr('data-step')) || 1;
		let newvalue = value;

		if(action == 'decrease') {
			newvalue = Math.max(value - step, min);
		} else if(action == 'increase') {
			newvalue = Math.min(value + step, max);
		}

		if(newvalue != value) {
			moveRangeThumb($thumb, newvalue, 0);
			$wrapper.attr('data-value', newvalue);
			$wrapper.trigger('input');

			setTimeout(function() {
				if(newvalue == $wrapper.value()) {
					$wrapper.trigger('change');
				}
			}, 500);
		}

		// End loop
		return false;
	})
}, { passive: false });

// Change range input value on click at position
$(document).on('click', '.input[data-type="range"]', function(e) {
	let $wrapper = $(this);
	let $thumb   = $wrapper.find('.range-thumb');
	let mouseX   = e.pageX;
	let perc     = (mouseX-$wrapper.offset().left-($thumb.outerWidth()/2))/$wrapper.outerWidth()*100;
	let values   = numRange($wrapper.attr('data-min') || 0, $wrapper.attr('data-max') || 100, $wrapper.attr('data-step') || 1);
	let closest  = closestArrayItem(perc, values);

	moveRangeThumb($thumb, closest);
	$wrapper.attr('data-value', closest);
	$wrapper.trigger('change');
})

// Move range slider thumbs
function moveRangeThumb($thumb, value, dur = 200) {
	let $wrapper = $thumb.parent();
	let $tooltip = $thumb.siblings('.range-tooltip');
	let newleft = ($wrapper.outerWidth()-$thumb.outerWidth()) / ($wrapper.attr('data-max') || 100) * value;

	$thumb.parents('.device').attr('data-value', value);
	$thumb.animate({
		'left': newleft
	}, dur);
	$tooltip.text(value);
	$tooltip.animate({
		'left': newleft + $thumb.outerWidth() / 2 - $tooltip.outerWidth() / 2
	}, dur);
}

// Toggle input 
$(document).on('click', '.input[data-type="toggle"]', function() {
	$(this).attr('data-value', $(this).attr('data-value') == 'on' ? 'off' : 'on').trigger('change');
})

$(document).on('thuspi.load', function() {
	$('input[data-type="search"]').each(function() {
		$(this).data('input', new thusPi.ui.input.search($(this)));
	})
})