// thusPiAssign('recordings.graph', {
// 	setup($graph, recordingId, maxRows = undefined) {
// 		this.$graph      = $graph;
// 		this.recordingId = recordingId;
// 		this.maxRows     = maxRows || Math.round($graph.outerWidth());

// 		this._refresh();

// 		$(document).on('mouseenter mouseleave mousemove touchmove', '.graph-body', (e) => {this._eventMouseMove(e)});
// 		$(document).on('mousedown touchstart', '.graph-body', (e) => {this._eventMouseDown(e)});
// 		$(document).on('mouseup touchend', '.graph-body', (e) => {this._eventMouseUp(e)});
		
// 		return this;
// 	},

// 	paint(debug = false) {
// 		this._clearContent();

// 		this._paintInfo(debug).then(() => {
// 			this._paintGraph(debug);
// 		}).catch((err) => {
// 			console.error(err);
// 			this._setStatus('error');
// 		})

// 		return this;
// 	},
	
// 	_eventMouseDown(e) {
// 		this.selection        = [];
// 		this.selectedPoints = [];
// 	},

// 	_eventMouseUp(e) {
// 		if(Object.keys(this.selection).length == 2 && 
// 		   Object.keys(this.selectedPoints).length == 2) {
// 			this.paint(true);
// 		}
// 	},

// 	_eventMouseMove(e) {
// 		let relativeX = clamp(0, thusPi.data.pointer.x - this.$graph.offset().left, this.$graph.outerWidth());
// 		let relativeY = clamp(0, thusPi.data.pointer.y - this.$graph.offset().top, this.$graph.outerHeight());
		
// 		this._moveCrosshairs(relativeX, relativeY);
// 		this._moveTooltip(relativeX, relativeY);

// 		if(thusPi?.data?.pointer?.down?.primary === true) {
// 			console.log('selecting!');
// 			if(this?.selection?.length < 4) {
// 				// Start of the selection, draw a dot
// 				this._paintSelection(relativeX, relativeX);
// 			} else {
// 				// Expand the existing selection
// 				this._paintSelection(this?.selection?.x0, relativeX);
// 			}
// 		}

// 		return this;
// 	},

// 	_refresh() {
// 		this.$graphBody         = this.$graph.closest('.graph-body');
// 		this.$graphWrapper      = this.$graph.closest('.graph-wrapper');
// 		this.$graphContainer    = this.$graph.closest('.graph-container');
// 		this.$graphSelection    = this.$graphContainer.find('.graph-selection');
// 		this.$graphStepsHor     = this.$graphContainer.find('.graph-area-hor .graph-axis-steps');
// 		this.$graphStepsVer     = this.$graphContainer.find('.graph-area-ver .graph-axis-steps');
// 		this.$graphTooltip      = this.$graphContainer.find('.graph-tooltip');
// 		this.$graphLegend       = this.$graphContainer.find('.graph-legend');
// 		this.$graphCrosshairHor = this.$graphContainer.find('.graph-crosshair-hor');
// 		this.$graphCrosshairVer = this.$graphContainer.find('.graph-crosshair-ver');

// 		return this;
// 	},

// 	_moveCrosshairs(relativeX, relativeY) {
// 		this.$graphCrosshairVer.css('left', relativeX);
// 		this.$graphCrosshairHor.css('top', relativeY);

// 		return this;
// 	},

// 	_moveTooltip(relativeX, relativeY) {
// 		this.$graphTooltip.css({'left': relativeX, 'top': relativeY})
// 	},

// 	_paintInfo(debug) {
// 		console.log(this.selectedPoints);
// 		return new Promise((resolve, reject) => {
// 			thusPi.api.call('recordings/json', {
// 				'id': this.recordingId,
// 				'max_rows': this.maxRows,
// 				'selection': this.selectedPoints,
// 				'debug': debug
// 			}).then((response) => {
// 				try {
// 					// The data the graph is constructed from
// 					this.rows      = response.data.rows;

// 					// The size of the graph
// 					this.size      = response.data.size;

// 					// The manifest
// 					this.manifest  = response.data.manifest;
					
// 					const horAxisTitle = `${this.manifest.axes.x.title}` + (this.manifest.axes.x.unit.length > 0 ? ` (${this.manifest.axes.x.unit})` : '');
// 					const verAxisTitle = `${this.manifest.axes.y.title}` + (this.manifest.axes.x.unit?.length > 0 ? ` (${this.manifest.axes.y.unit})` : '');

// 					thusPi.recordings.graph._paintAxisTitles(horAxisTitle, verAxisTitle);

// 					thusPi.recordings.graph._paintAxisSteps();

// 					thusPi.recordings.graph._paintTooltipItem(response.data.manifest.axes.x.title, 'var(--text)', 'x', response.data.manifest.axes.x.unit, false);

// 					$.each(response.data.manifest.columns, (column, info) => {
// 						const axis = column.substring(0, 1);

// 						this._paintLegendItem(info.title, info.color, column);
// 						this._paintTooltipItem(info.title, info.color, column, response.data.manifest.axes[axis].unit);

// 						resolve(this);
// 					})
// 				} catch(err) {
// 					console.error(err);
// 				}
// 			}).catch((err) => {
// 				console.error(err);
// 				reject(this);
// 			})
// 		})
// 	},

// 	_paintSelection(x0 = null, x1 = null) {
// 		if(!isSet(x0) || !isSet(x1)) {
// 			return false;
// 		}

// 		x0 = clamp(0, x0, this.$graph.outerWidth());
// 		x1 = clamp(0, x1, this.$graph.outerWidth());

// 		// Calculate the position in percentages
// 		x0Relative = round(x0 / this.$graph.outerWidth() * 100, 5);
// 		x1Relative = round(x1 / this.$graph.outerWidth() * 100, 5);
		
// 		// Calculate the smallest and biggest x
// 		const xminRelative = Math.min(x0Relative, x1Relative);
// 		const xmaxRelative = Math.max(x0Relative, x1Relative);

// 		this.$graphSelection.css({
// 			'left': xminRelative + '%',
// 			'width':  xmaxRelative - xminRelative + '%'
// 		});

// 		this.selection = {'x0': x0, 'x1': x1};
// 		this.selectedPoints = {
// 			'x0': (xminRelative / 100 * this?.size?.dif_x) + this?.size?.min_x || null,
// 			'x1': (xmaxRelative / 100 * this?.size?.dif_x) + this?.size?.min_x || null,
// 		}
// 		console.log('Changing selection!', this.selectedPoints);
// 	},

// 	_clearSelection() {
// 		this.selection        = [];
// 		this.selectedPoints = [];

// 		this._paintSelection();
// 	},

// 	_toggleView() {
// 		let createPopup     = !this.$graphBody.hasClass('popup');
 
// 		if(createPopup) {
// 			this.$graphBody.popupCreate();
// 		} else {
// 			this.$graphBody.popupDismantle();
// 		}

// 		thusPi.recordings.graph.paint();

// 		return this;
// 	},

// 	_toggleStepsVisibility() {
// 		let newVisible      = (this.$graphContainer.attr('data-steps-visible') == 'false' ? true : false);
// 		this.$graphContainer.attr('data-steps-visible', newVisible);
// 		return this;
// 	},

// 	_paintGraph(debug = false) {
// 		this._setStatus('loading');

// 		return new Promise((resolve, reject) => {
// 			thusPi.api.call('recordings/graph', {
// 				'id': this.recordingId,
// 				'max_rows': this.maxRows,
// 				'selection': this.selectedPoints,
// 				'debug': debug				
// 			}).then((response) => {
// 				console.log(response);
// 				this._setContent(response);
// 				return true;

// 				$.each(response.data.manifest.columns, function(column, info) {
// 					$(`.graph-line[data-column="${column}"]`).css({
// 						'stroke': info.color,
// 						'fill': info.color
// 					});
// 				})
				
// 				this._setStatus('loaded')._refresh();
// 			}).catch((err) => {
// 				console.error(err);
// 				this._setContent(err);
// 				// this._setStatus('error');
// 			})
// 		})
// 	},

// 	_paintAxisSteps() {
// 		let rowsAmount         = this.rows.length;
// 		let graphStepsHorHTML  = '';
// 		let graphStepsVerHTML  = '';
		
// 		let graphStepsHorAmount = Math.max(Math.floor(this.$graphStepsHor.outerWidth() / 50), 3);
// 		let graphStepsVerAmount = Math.max(Math.floor(this.$graphStepsVer.outerHeight() / 50), 3);

// 		// Generate horizontal steps
// 		for (let i = 0; i < graphStepsHorAmount; i++) {
// 			const rowIndex = Math.floor(i / graphStepsHorAmount * rowsAmount);
// 			if(typeof this.rows[rowIndex]?.x_formatted == 'undefined') {
// 				continue;
// 			}

// 			const stepContent = this.rows[rowIndex]['x_formatted'];
// 			graphStepsHorHTML += `<span class="graph-axis-step text-vertical">${stepContent}</span>`;
// 		}

// 		this.$graphStepsHor.html(graphStepsHorHTML);

// 		// Generate vertical steps
// 		let decimals =  3;
// 		if(typeof this.manifest?.axes?.y?.decimals != 'undefined') {
// 			decimals = this.manifest?.axes?.y?.decimals;
// 		}

// 		for (let i = 0; i < graphStepsVerAmount; i++) {
// 			const stepContent = ((i / (graphStepsVerAmount - 1) * this?.size?.dif_y) + this?.size?.min_y).toFixed(decimals);
// 			graphStepsVerHTML += `<span class="graph-axis-step">${stepContent}</span>`;
// 		}

// 		this.$graphStepsVer.html(graphStepsVerHTML);

// 		return this;
// 	},

// 	_paintAxisTitles: (horAxisTitle = undefined, verAxisTitle = undefined) => {
// 		let $graphContainer = $('.graph-container');
// 		if(typeof horAxisTitle != 'undefined') {
// 			$graphContainer.find('.graph-area-hor .graph-axis-title').text(horAxisTitle);
// 		}

// 		if(typeof verAxisTitle != 'undefined') {
// 			$graphContainer.find('.graph-area-ver .graph-axis-title').text(verAxisTitle);
// 		}

// 		return this;
// 	},

// 	_paintTooltipItem(title, color, column, unit, sortable = true) {
// 		let $graphTooltipItem = $('.graph-tooltip-item.template').clone().removeClass('template');

// 		$graphTooltipItem.find('.graph-tooltip-item-column').text(title);
// 		$graphTooltipItem.find('.graph-tooltip-item-value').css('color', color);
// 		$graphTooltipItem.find('.graph-tooltip-item-unit').text(unit);
// 		$graphTooltipItem.attr('data-column', column);
// 		$graphTooltipItem.attr('data-sortable', sortable).toggleClass('text-muted', !sortable);

// 		$graphTooltipItem.appendTo(this.$graphTooltip);
// 	},

// 	_paintLegendItem(title, color, column) {
// 		return true;

// 		let $graphLegend     = $('.graph-legend');
// 		let $graphLegendItem = $('.graph-legend-item.template').clone().removeClass('template');

// 		$graphLegendItem.find('.graph-legend-item-icon').css('background', color);
// 		$graphLegendItem.find('.graph-legend-item-main').text(title);
// 		$graphLegendItem.attr('data-column', column);

// 		$graphLegendItem.appendTo($graphLegend);
// 	},

// 	_setContent(content) {
// 		this.$graph.html(content);
// 		return this;
// 	},

// 	_setStatus(status) {
// 		this.$graphContainer.attr('data-status', status);
// 		return this;
// 	},

// 	_clearContent() {
// 		this._setContent('');
// 		this.$graphTooltip.find('.graph-tooltip-item:not(.template)').remove();
// 		this.$graphLegend.find('.graph-legend-item:not(.template)').remove();
// 		return this;
// 	},

// 	// data: (key, value = undefined) => {
// 	// 	let $graph = $('.graph');
// 	// 	if(typeof value == 'undefined') {
// 	// 		return $graph.data(key);
// 	// 	} else {
// 	// 		return $graph.data(key, value);
// 	// 	}
// 	// }
// })

// $(document).on('close dismantle', '.graph-container.popup', function() {
// 	$('.graph-sidebar .btn[data-graph-action="toggle_view"]').removeClass('active');
// })

// $(document).on('change', '.graph-legend-item', function() {
// 	return true;
// 	let $graph           = $('.graph');
// 	let $graphLegendItem = $(this);
// 	let column           = $graphLegendItem.attr('data-column');
// 	let active           = $graphLegendItem.hasClass('active');

// 	$graph.find(`[data-column="${column}"]`).toggleClass('inactive', !active);
// })

// $(document).on('mouseenter mouseleave mousemove touchmove', '.graph-body', function(e) {
// 	return true;
// 	let $graphContainer    = $('.graph-container');
// 	let $graph             = $('.graph');
// 	let $graphTooltip      = $('.graph-tooltip');
// 	let $graphTarget       = $graphContainer.find('.graph-target');
// 	let size               = thusPi.recordings.graph.data('size');
// 	let rows               = thusPi.recordings.graph.data('rows');
// 	let manifest           = thusPi.recordings.graph.data('manifest');

// 	let mouseTop  = clamp(0, mouseY - $graph.offset().top, $graph.outerHeight());
// 	let mouseLeft = clamp(0, mouseX - $graph.offset().left, $graph.outerWidth())

// 	// Paint selection if user is pressing left mouse button
// 	if(mouseDown) {
// 		try {
// 			let selection = thusPi.recordings.graph.data('selection');

// 			if(typeof selection == 'undefined' || selection.length < 4) {
// 				thusPi.recordings.graph.paintSelectionFrame(mouseLeft, mouseTop, mouseLeft, mouseTop);
// 			} else {
// 				thusPi.recordings.graph.paintSelectionFrame(selection.x0, selection.y0, mouseLeft, mouseTop);
// 			}
// 		} catch(err) {
// 			console.error(err);
// 		}
// 	}

// 	// Move crosshairs
// 	$graphCrosshairHor.css('top', mouseTop);
// 	$graphCrosshairVer.css('left', mouseLeft);

// 	// Calculate mouse position in percentages, relative to the graph itself
// 	let mousePos = {
// 		'x': clamp(0, (mouseX - $graph.offset().left) / $graph.outerWidth() * 100, 100),
// 		'y': clamp(0, (mouseY - $graph.offset().top) / $graph.outerHeight() * 100, 100)
// 	}

// 	if(typeof size == 'undefined' || typeof rows == 'undefined') {
// 		return false;
// 	}

// 	let xValue = size.min_x + ((mousePos.x / 100) * size.dif_x);

// 	// Obtain values
// 	let values = null;
// 	let i = 0;
// 	let len = rows.length;
//     while (i < len) {
// 		if(typeof rows[i+1] != 'undefined') {
// 			if(xValue >= rows[i]['x'] && xValue <= rows[i+1]['x']) {
// 				values = rows[i];
// 			}
// 		}

//         ++i;
//     }

// 	if(values === null) {
// 		return;
// 	}

// 	let targetTop  = (1 - (Math.max(...values.y) - size.min_y) / size.dif_y) * $graph.outerHeight();
// 	let targetLeft = (values.x - size.min_x) / size.dif_x * $graph.outerWidth();

// 	$graphTarget.css({'top': targetTop, 'left': targetLeft});

// 	$graphTooltip.find('.graph-tooltip-item:not(.template)').each(function() {
// 		let $graphTooltipItem = $(this);
// 		let column            = $graphTooltipItem.attr('data-column');
// 		let axis              = column.substring(0, 1);
// 		column                = column.substring(1);
// 		let valueStr;

// 		let decimals =  3;
// 		if(typeof manifest?.axes[axis]?.decimals != 'undefined') {
// 			decimals = manifest?.axes[axis]?.decimals;
// 		}

// 		if(axis == 'x') {
// 			valueStr = values['x_formatted'];
// 		} else {
// 			if(typeof values[axis][column] == 'undefined') {
// 				return true;
// 			}

// 			valueStr = values[axis][column].toFixed(decimals);
// 		}

// 		$graphTooltipItem.attr('data-value', valueStr).find('.graph-tooltip-item-value').text(valueStr);
// 	})

// 	// Change order of tooltip items depending on value
// 	$graphTooltip.find('.graph-tooltip-item:not([data-sortable="false"])').sort(function(a, b) {
// 		if($(a).attr('data-value') == $(b).attr('data-value')) {
// 			return ($(a).find('.graph-tooltip-item-column').text() > $(b).find('.graph-tooltip-item-column').text()) ? 1 : -1;
// 		} else {
// 			return ($(a).attr('data-value') < $(b).attr('data-value')) ? 1 : -1;
// 		}
// 	}).appendTo($graphTooltip);
// })

// $(document).on('thuspi.load', function() {
// 	if(thusPi.page.current() != 'recordings/graph') {
// 		return false;
// 	}

// 	let recordingId = urlParam('id');
// 	thusPi.recordings.graph.setup($('.graph'), recordingId).paint();
// })