/* ================================ */
/*        INPUT TYPE: SEARCH        */
/* ================================ */
thusPiAssign('ui.input.search', class {
	constructor($input) {
		this.$input   = $input;
		this.$wrapper = $input.parents('.input-wrapper.input-wrapper-search').first();
		this.$results = this.$wrapper.find('.input-search-results').first();

		this.$wrapper.on('click', (e) => {e.stopPropagation()});
		
		this.$input.on('click', (e) => {this.focusEvent(e)});
		this.$input.on('input', (e) => {this.inputEvent(e)});
		this.$input.on('change', (e) => {this.changeEvent(e)});

		this.$results.on('click', '.input-search-result', (e) => {this.resultFocusEvent(e)});

		$(document).on('click', (e) => {this.focusOutEvent(e)});
	}

	focusEvent(e) {
		this.$wrapper.addClass('active');
		this.resultsToggleAll(true);
		this.resultsToggle(true);
	}

	focusOutEvent(e) {
		this.$wrapper.removeClass('active');
		this.resultsToggle(false);
	}

	inputEvent(e) {
		e.preventDefault();
		if(this.$input.attr('filter-on') != 'change') {
			this.resultsFilter(this.$input.val());
		}
	}

	changeEvent(e) {
		e.preventDefault();
		if(this.$input.attr('filter-on') == 'change') {
			this.resultsFilter(this.$input.val());
		}
	}

	resultsToggle(show = true) {
		this.$results.toggleClass('show', show);
	}

	resultsToggleAll(show = true) {
		this.$results.find('.input-search-result[data-search-match]').toggleClass('show', show);
	}

	resultsFilter(query, caseSensitive = false) {
		if(!caseSensitive) {
			query = query.toLowerCase(); 
		}

		this.$results.find('.input-search-result[data-search-match]').each(function(i, result) {
			let $result = $(result);
			let match   = $result.attr('data-search-match');

			if(!caseSensitive) {
				match = match.toLowerCase();
			}
			
			$result.toggleClass('show', match.includes(query));
		})

		this.$results.attr('data-search-matches', this.$results.find('.input-search-result[data-search-match].show').length);
	}

	resultFocusEvent(e) {
		let $result    = $(e.target).closest('.input-search-result');
		let value      = $result.attr('value');
		let shownValue = $result.attr('data-shown-value');

		
		this.$results.find('.input-search-result.active').removeClass('active');
		$result.addClass('active');

		this.$wrapper.removeClass('active');
		
		if(this.$wrapper.attr('data-select-type') != 'multiple') {
			this.resultsToggle(false);
		}

		if(this.$input.value() == value) {
			return false;
		}
		
		this.$input.value(value, shownValue);
		this.$input.trigger('thusPi.change');

	}
})

$.fn.value = function(value = undefined, shownValue = undefined) {
	let $input = this;
	let input  = $input.get(0);

	if(typeof value == 'undefined') {
		if($input.hasClass('input-search')) {
			return $input.closest('.input-wrapper-search').attr('data-value');
		} else if(typeof input.value != 'undefined') {
			return $input.value;
		} else if(typeof $input.attr('data-value') != 'undefined') {		
			return $input.attr('data-value');
		} else if($input.attr('contenteditable') == 'true') {
			return $input.text();
		}

		return undefined;
	} else {
		if($input.hasClass('input-search')) {
			$input.closest('.input-wrapper-search').attr('data-value', value);
			$input.closest('input').attr('value', shownValue || value).val(shownValue || value);
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
$(document).on('thusPi.load', function() {
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
		drag: function() {
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
		stop: function(event, ui) {
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

			setTimeout(() => {
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

$(document).on('thusPi.load', function() {
	$('.input-wrapper-search input').each(function() {
		$(this).data('input', new thusPi.ui.input.search($(this)));
	})
})