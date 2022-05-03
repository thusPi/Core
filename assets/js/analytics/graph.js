thusPiAssign('analytics.graph', {
	setup($graph, analyticId, maxRows = undefined) {
		this.$graph     = $graph;
		this.analyticId = analyticId;
		this.maxRows    = maxRows || Math.round($graph.outerWidth());

		this.refresh();

		$(document).on('mouseenter mouseleave mousemove touchmove', '.graph-body', function(e) {this.eventMouseMove(e)});
		$(document).on('mousedown touchstart', '.graph-body', function(e) {this.eventMouseDown(e)});
		$(document).on('mouseup touchend', '.graph-body', function(e) {this.eventMouseUp(e)});
		
		return this;
	},
	
	eventMouseDown(e) {
		this.selection        = [];
		this.selection_points = [];
	},

	eventMouseUp(e) {
		if(Object.keys(this.selection).length == 4 && 
		   Object.keys(this.selection_points).length == 4) {
			this.paint(true);
		}
	},

	eventMouseMove(e) {
		let relativeX = clamp(0, thusPi.data.pointer.x - this.$graph.offset().left, this.$graph.outerWidth());
		let relativeY = clamp(0, thusPi.data.pointer.y - this.$graph.offset().top, this.$graph.outerHeight());
		
		this.moveCrosshairs(relativeX, relativeY);
		this.moveTooltip(relativeX, relativeY);

		if(thusPi?.data?.pointer?.down?.primary === true) {
			console.log('selecting!');
			if(this?.selection?.length < 4) {
				this.paintSelection(relativeX, relativeY, relativeX, relativeY);
			} else {
				this.paintSelection(this?.selection?.x0, this?.selection?.y0, relativeX, relativeY);
			}
		}

		return this;
	},

	refresh() {
		this.$graphBody         = this.$graph.closest('.graph-body');
		this.$graphWrapper      = this.$graph.closest('.graph-wrapper');
		this.$graphContainer    = this.$graph.closest('.graph-container');
		this.$graphSelection    = this.$graphContainer.find('.graph-selection');
		this.$graphStepsHor     = this.$graphContainer.find('.graph-area-hor .graph-axis-steps');
		this.$graphStepsVer     = this.$graphContainer.find('.graph-area-ver .graph-axis-steps');
		this.$graphTooltip      = this.$graphContainer.find('.graph-tooltip');
		this.$graphLegend       = this.$graphContainer.find('.graph-legend');
		this.$graphCrosshairHor = this.$graphContainer.find('.graph-crosshair-hor');
		this.$graphCrosshairVer = this.$graphContainer.find('.graph-crosshair-ver');

		return this;
	},

	moveCrosshairs(relativeX, relativeY) {
		this.$graphCrosshairVer.css('left', relativeX);
		this.$graphCrosshairHor.css('top', relativeY);

		return this;
	},

	moveTooltip(relativeX, relativeY) {
		this.$graphTooltip.css({'left': relativeX, 'top': relativeY})
	},

	paint(debug = false) {
		this.clear();

		this.paintInfo(debug).then(function() {
			this.paintGraph();
		}).catch(function(err) {
			console.error(err);
			this.setStatus('error');
		})

		return this;
	},

	paintInfo(debug) {
		console.log(this.selection_points);
		return new Promise(function(resolve, reject) {
			thusPi.api.call('analytic-json', {
				'id': this.analyticId,
				'max_rows': this.maxRows,
				'selection': this.selection_points,
				'debug': debug
			}).then(function(response) {
				try {
					console.log(response.data);
					this.rows      = response.data.rows;
					this.size      = response.data.size;
					this.manifest  = response.data.manifest;
					
					let horAxisTitle = `${this.manifest.axes.x.title}` + (this.manifest.axes.x.unit.length > 0 ? ` (${this.manifest.axes.x.unit})` : '');
					let verAxisTitle = `${this.manifest.axes.y.title}` + (this.manifest.axes.x.unit?.length > 0 ? ` (${this.manifest.axes.y.unit})` : '');

					thusPi.analytics.graph.paintAxisTitles(horAxisTitle, verAxisTitle);

					thusPi.analytics.graph.paintAxisSteps();

					thusPi.analytics.graph.paintTooltipItem(response.data.manifest.axes.x.title, 'var(--text)', 'x', response.data.manifest.axes.x.unit, false);

					$.each(response.data.manifest.columns, function(column, info) {
						let axis = column.substring(0, 1);

						thusPi.analytics.graph.paintLegendItem(info.title, info.color, column);
						thusPi.analytics.graph.paintTooltipItem(info.title, info.color, column, response.data.manifest.axes[axis].unit);

						resolve(this);
					})
				} catch(err) {
					console.error(err);
				}
			}).catch(function(err) {
				console.error(err);
				reject(this);
			})
		})
	},

	paintSelection(x0 = null, y0 = null, x1 = null, y1 = null) {
		x0 = clamp(0, x0, this.$graph.outerWidth());
		y0 = clamp(0, y0, this.$graph.outerHeight());
		x1 = clamp(0, x1, this.$graph.outerWidth());
		y1 = clamp(0, y1, this.$graph.outerHeight());

		x0_relative = round(x0 / this.$graph.outerWidth() * 100, 5);
		y0_relative = round(y0 / this.$graph.outerHeight() * 100, 5);
		x1_relative = round(x1 / this.$graph.outerWidth() * 100, 5);
		y1_relative = round(y1 / this.$graph.outerHeight() * 100, 5);
		
		let xmin_relative = Math.min(x0_relative, x1_relative);
		let ymin_relative = Math.min(y0_relative, y1_relative);
		let xmax_relative = Math.max(x0_relative, x1_relative);
		let ymax_relative = Math.max(y0_relative, y1_relative);

		this.$graphSelection.css({
			'top':    ymin_relative + '%',
			'left':   xmin_relative + '%',
			'height': ymax_relative - ymin_relative + '%',
			'width':  xmax_relative - xmin_relative + '%'
		});

		if(x0 == null || y0 == null || x1 == null || y1 == null) {
			return false;
		}

		this.selection = {'x0': x0, 'y0': y0, 'x1': x1, 'y1': y1};
		this.selection_points = {
			'x0': (xmin_relative / 100 * this?.size?.dif_x) + this?.size?.min_x || null,
			'y0': (100 - ymin_relative) / 100 * this?.size?.dif_y + this?.size?.min_y || null,
			'x1': (xmax_relative / 100 * this?.size?.dif_x) + this?.size?.min_x || null,
			'y1': (100 - ymax_relative) / 100 * this?.size?.dif_y + this?.size?.min_y || null,
		}
		console.log('Changing selection!', this.selection_points);
	},

	clearSelection() {
		this.selection        = [];
		this.selection_points = [];

		this.paintSelection();
	},

	toggleView() {
		let createPopup     = !this.$graphBody.hasClass('popup');
 
		if(createPopup) {
			this.$graphBody.popupCreate();
		} else {
			this.$graphBody.popupDismantle();
		}

		thusPi.analytics.graph.paint();

		return this;
	},

	toggleStepsVisibility() {
		let newVisible      = (this.$graphContainer.attr('data-steps-visible') == 'false' ? true : false);
		this.$graphContainer.attr('data-steps-visible', newVisible);
		return this;
	},

	paintGraph() {
		this.setStatus('loading');

		return new Promise(function(resolve, reject) {
			thusPi.api.call('analytic-graph', {
				'id': this.analyticId,
				'max_rows': this.maxRows,
				'selection': this.selection_points
			}).then(function(response) {
				this.setContent(response.data.svg);

				$.each(response.data.manifest.columns, function(column, info) {
					$(`.graph-line[data-column="${column}"]`).css({
						'stroke': info.color,
						'fill': info.color
					});
				})
				
				this.setStatus('loaded').refresh();
			}).catch(function(err) {
				console.error(err);
				this.setStatus('error');
			})
		})
	},

	paintAxisSteps() {
		let rowsAmount         = this.rows.length;
		let graphStepsHorHTML  = '';
		let graphStepsVerHTML  = '';
		
		let graphStepsHorAmount = Math.max(Math.floor(this.$graphStepsHor.outerWidth() / 50), 3);
		let graphStepsVerAmount = Math.max(Math.floor(this.$graphStepsVer.outerHeight() / 50), 3);

		// Generate horizontal steps
		for (let i = 0; i < graphStepsHorAmount; i++) {
			let rowIndex = Math.floor(i / graphStepsHorAmount * rowsAmount);
			if(typeof this.rows[rowIndex]?.x_formatted == 'undefined') {
				continue;
			}

			let stepContent = this.rows[rowIndex]['x_formatted'];
			graphStepsHorHTML += `<span class="graph-axis-step text-monospace text-vertical">${stepContent}</span>`;
		}

		this.$graphStepsHor.html(graphStepsHorHTML);

		// Generate vertical steps
		let decimals =  3;
		if(typeof this.manifest?.axes?.y?.decimals != 'undefined') {
			decimals = this.manifest?.axes?.y?.decimals;
		}

		for (let i = 0; i < graphStepsVerAmount; i++) {
			let stepContent = ((i / (graphStepsVerAmount - 1) * this?.selection?.dif_y) + this?.selection?.min_y).toFixed(decimals);
			graphStepsVerHTML += `<span class="graph-axis-step text-monospace">${stepContent}</span>`;
		}

		this.$graphStepsVer.html(graphStepsVerHTML);

		return this;
	},

	paintAxisTitles(horAxisTitle = undefined, verAxisTitle = undefined) {
		let $graphContainer = $('.graph-container');
		if(typeof horAxisTitle != 'undefined') {
			$graphContainer.find('.graph-area-hor .graph-axis-title').text(horAxisTitle);
		}

		if(typeof verAxisTitle != 'undefined') {
			$graphContainer.find('.graph-area-ver .graph-axis-title').text(verAxisTitle);
		}

		return this;
	},

	paintTooltipItem(title, color, column, unit, sortable = true) {
		let $graphTooltipItem = $('.graph-tooltip-item.template').clone().removeClass('template');

		$graphTooltipItem.find('.graph-tooltip-item-column').text(title);
		$graphTooltipItem.find('.graph-tooltip-item-value').css('color', color);
		$graphTooltipItem.find('.graph-tooltip-item-unit').text(unit);
		$graphTooltipItem.attr('data-column', column);
		$graphTooltipItem.attr('data-sortable', sortable).toggleClass('text-muted', !sortable);

		$graphTooltipItem.appendTo(this.$graphTooltip);
	},

	paintLegendItem(title, color, column) {
		return true;

		let $graphLegend     = $('.graph-legend');
		let $graphLegendItem = $('.graph-legend-item.template').clone().removeClass('template');

		$graphLegendItem.find('.graph-legend-item-icon').css('background', color);
		$graphLegendItem.find('.graph-legend-item-main').text(title);
		$graphLegendItem.attr('data-column', column);

		$graphLegendItem.appendTo($graphLegend);
	},

	setContent(content) {
		this.$graph.html(content);
		return this;
	},

	setStatus(status) {
		this.$graphContainer.attr('data-status', status);
		return this;
	},

	clear() {
		this.setContent('');
		this.clearSelection();
		this.$graphTooltip.find('.graph-tooltip-item:not(.template)').remove();
		this.$graphLegend.find('.graph-legend-item:not(.template)').remove();
		return this;
	},

	// data(key, value = undefined) {
	// 	let $graph = $('.graph');
	// 	if(typeof value == 'undefined') {
	// 		return $graph.data(key);
	// 	} else {
	// 		return $graph.data(key, value);
	// 	}
	// }
})

$(document).on('close dismantle', '.graph-container.popup', function() {
	$('.graph-sidebar .btn[data-graph-action="toggle_view"]').removeClass('active');
})

$(document).on('change', '.graph-legend-item', function() {
	return true;
	let $graph           = $('.graph');
	let $graphLegendItem = $(this);
	let column           = $graphLegendItem.attr('data-column');
	let active           = $graphLegendItem.hasClass('active');

	$graph.find(`[data-column="${column}"]`).toggleClass('inactive', !active);
})

$(document).on('mouseenter mouseleave mousemove touchmove', '.graph-body', function(e) {
	return true;
	let $graphContainer    = $('.graph-container');
	let $graph             = $('.graph');
	let $graphTooltip      = $('.graph-tooltip');
	let $graphTarget       = $graphContainer.find('.graph-target');
	let size               = thusPi.analytics.graph.data('size');
	let rows               = thusPi.analytics.graph.data('rows');
	let manifest           = thusPi.analytics.graph.data('manifest');

	let mouseTop  = clamp(0, mouseY - $graph.offset().top, $graph.outerHeight());
	let mouseLeft = clamp(0, mouseX - $graph.offset().left, $graph.outerWidth())

	// Paint selection if user is pressing left mouse button
	if(mouseDown) {
		try {
			let selection = thusPi.analytics.graph.data('selection');

			if(typeof selection == 'undefined' || selection.length < 4) {
				thusPi.analytics.graph.paintSelectionFrame(mouseLeft, mouseTop, mouseLeft, mouseTop);
			} else {
				thusPi.analytics.graph.paintSelectionFrame(selection.x0, selection.y0, mouseLeft, mouseTop);
			}
		} catch(err) {
			console.error(err);
		}
	}

	// Move crosshairs
	$graphCrosshairHor.css('top', mouseTop);
	$graphCrosshairVer.css('left', mouseLeft);

	// Calculate mouse position in percentages, relative to the graph itself
	let mousePos = {
		'x': clamp(0, (mouseX - $graph.offset().left) / $graph.outerWidth() * 100, 100),
		'y': clamp(0, (mouseY - $graph.offset().top) / $graph.outerHeight() * 100, 100)
	}

	if(typeof size == 'undefined' || typeof rows == 'undefined') {
		return false;
	}

	let xValue = size.min_x + ((mousePos.x / 100) * size.dif_x);

	// Obtain values
	let values = null;
	let i = 0;
	let len = rows.length;
    while (i < len) {
		if(typeof rows[i+1] != 'undefined') {
			if(xValue >= rows[i]['x'] && xValue <= rows[i+1]['x']) {
				values = rows[i];
			}
		}

        ++i;
    }

	if(values === null) {
		return;
	}

	let targetTop  = (1 - (Math.max(...values.y) - size.min_y) / size.dif_y) * $graph.outerHeight();
	let targetLeft = (values.x - size.min_x) / size.dif_x * $graph.outerWidth();

	$graphTarget.css({'top': targetTop, 'left': targetLeft});

	$graphTooltip.find('.graph-tooltip-item:not(.template)').each(function() {
		let $graphTooltipItem = $(this);
		let column            = $graphTooltipItem.attr('data-column');
		let axis              = column.substring(0, 1);
		column                = column.substring(1);
		let valueStr;

		let decimals =  3;
		if(typeof manifest?.axes[axis]?.decimals != 'undefined') {
			decimals = manifest?.axes[axis]?.decimals;
		}

		if(axis == 'x') {
			valueStr = values['x_formatted'];
		} else {
			if(typeof values[axis][column] == 'undefined') {
				return true;
			}

			valueStr = values[axis][column].toFixed(decimals);
		}

		$graphTooltipItem.attr('data-value', valueStr).find('.graph-tooltip-item-value').text(valueStr);
	})

	// Change order of tooltip items depending on value
	$graphTooltip.find('.graph-tooltip-item:not([data-sortable="false"])').sort(function(a, b) {
		if($(a).attr('data-value') == $(b).attr('data-value')) {
			return ($(a).find('.graph-tooltip-item-column').text() > $(b).find('.graph-tooltip-item-column').text()) ? 1 : -1;
		} else {
			return ($(a).attr('data-value') < $(b).attr('data-value')) ? 1 : -1;
		}
	}).appendTo($graphTooltip);
})

$(document).on('thusPi.load', function() {
	if(thusPi.page.current() == 'analytics/graph') {
		let analyticId = urlParam('id');
		thusPi.analytics.graph.setup($('.graph'), analyticId).paint();
	}
})