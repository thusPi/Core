thusPiAssign('recordings.graph', {
    setup($graph, recordingId, maxRows) {
        this.$graph           = $graph;
        this.$graphContainer  = $graph.closest('.graph-container');
        this.$selection       = this.$graphContainer.find('.graph-selection');
        this.$crosshairHor    = this.$graphContainer.find('.graph-crosshair.graph-crosshair-hor');
        this.$crosshairVer    = this.$graphContainer.find('.graph-crosshair.graph-crosshair-ver');
        this.$tooltip         = this.$graphContainer.find('.graph-tooltip');
        this.$highlight       = this.$graphContainer.find('.graph-highlight');
        this.$targetHighlight = this.$graphContainer.find('.graph-target');
        this.recordingId      = recordingId;
        this.maxRows          = maxRows;
        this.curveSmoothing   = 0.1;

        this._fetchRows().then(() => {
            this._paintRows(this.rows);
        })
    },

    drawInterval(interval) {
        this._fetchRows(interval).then(() => {
            this._paintRows(this.rows);
        })
    },

    selectPeriod(period) {
        const now = new Date();

        switch(period) {
            case 'hour':
                return this.drawPeriod(
                    new Date().setHours(now.getHours(), 0, 0, 0),
                    new Date().setHours(now.getHours() + 1, 0, 0, 0)
                );
            case 'day':
                return this.drawPeriod(
                    new Date(new Date().setDate(now.getDate())).setHours(0, 0, 0),
                    new Date(new Date().setDate(now.getDate() + 1)).setHours(0, 0, 0)
                );
            case 'week':
                return this.drawPeriod(
                    new Date(new Date().setDate(now.getDate() - now.getDay())).setHours(0, 0, 0),
                    new Date(new Date().setDate(now.getDate() - now.getDay() + 6)).setHours(0, 0, 0)
                );
            case 'month':
                return this.drawPeriod(
                    new Date(new Date().setMonth(now.getMonth())).setHours(0, 0, 0),
                    new Date(new Date().setMonth(now.getMonth() + 1)).setHours(0, 0, 0)
                );
            case 'year':
                return this.drawPeriod(
                    new Date(new Date().setFullYear(now.getFullYear(), 0, 1)).setHours(0, 0, 0),
                    new Date(new Date().setFullYear(now.getFullYear() + 1, 0, 1)).setHours(0, 0, 0)
                );
        }
    },

    drawPeriod(timeStart, timeEnd) {
        // Time in seconds since epoch
        const xStart = Math.floor(timeStart / 1000);
        const xEnd   = Math.floor(timeEnd / 1000);

        let rows = [];

        // Loop all rows to determine if the row is present in the selected period
        const rowCount = this.rows.length;

        let rowIndex = 0;
        while (rowIndex < rowCount) {
            if(this.rows[rowIndex].x < xStart || this.rows[rowIndex].x > xEnd) {
                ++rowIndex;
                continue;
            }

            rows.push(this.rows[rowIndex]);
            
            ++rowIndex;
        }
        
        console.log(rows);
    },

    // drawSelection(pointerX, pointerY) {
    //     // Calculate x in pixels relative to the graph
    //     const xPx = this._calculateRelativePixelsFromPixels(pointerX, 'x');

    //     this._updateSelection(xPx);
    // },

    // _updateSelection(x) {
    //     if(!isSet(this.selection) || !isSet(this.selection.x0) || !isSet(this.selection.x1)) {
    //         this.selection = {x0: x, x1: x};
    //     } else {
    //         this.selection = {
    //             x0: Math.min(this.selection.x0, x),
    //             x1: Math.max(this.selection.x1, x)
    //         }
    //     }

    //     return this;
    // },

    moveCrosshairs(pointerX, pointerY) {
        // Calculate x and y in pixels relative to the graph
        const xPx = this._calculateRelativePixelsFromPixels(pointerX, 'x');
        const yPx = this._calculateRelativePixelsFromPixels(pointerY, 'y');

        // Move the crosshairs
        this.$crosshairVer.css('left', xPx+'px');
        this.$crosshairHor.css('top', yPx+'px');
    },

    updateTooltip(pointerX, pointerY) {
        if(!isSet(this.size)) {
            return;
        }

        // Calculate x and y in pixels relative to the graph
        const xPx = this._calculateRelativePixelsFromPixels(pointerX, 'x');
        const yPx = this._calculateRelativePixelsFromPixels(pointerY, 'y');

        // Calculate xValue
        const xValue = this._calculateValueFromRelativePixels(xPx, 'x');
        
        // Loop all rows the find the row corresponding with xValue (while loop is fastest)
        const rowCount = this.rows.length;
        let selectedRowIndex;

        let rowIndex = 0;
        while(rowIndex < rowCount) {
            if(!isSet(this.rows[rowIndex+1])) {
                ++rowIndex;
                continue;
            }

            if(xValue < this.rows[rowIndex]['x'] || xValue > this.rows[rowIndex+1]['x']) {
                ++rowIndex;
				continue;
			}

            // This is the row
            selectedRowIndex = rowIndex;
            break;
        }

        // Return if the corresponding row has not been found
        if(!isSet(selectedRowIndex)) {
            this.$tooltip.hide();
            return;
        }

        const selectedRow     = this.rows[selectedRowIndex];
        const selectedRowNext = this.rows[selectedRowIndex+1];


        // Draw a highlight from the start and to the end of period if an interval was selected
        this._drawHighlight(
            this._calculateRelativePixelsFromValue(selectedRow['x'], 'x'),
            this._calculateRelativePixelsFromValue(selectedRowNext['x'], 'x')
        );

        // Clear the tooltip
        this.$tooltip.empty();

        // Append the columns to the tooltip
        $.each(selectedRow.y, (colIndex, value) => {
            const $tooltipItem = thusPi.template.get('.graph-tooltip-item');

            // // Mirror the opposite value if it was specified as mirrorred
            // let shownValue = (value * (isSet(this.manifest?.columns['y'+colIndex]?.mirrored) && this.manifest.columns['y'+colIndex].mirrored === true ? -1 : 1)).toFixed(this.manifest.axes.y.decimals);
            const shownValue = value.toFixed(this.manifest.axes['y'].decimals);

            $tooltipItem.find('.graph-tooltip-item-column').text(this.manifest.columns['y'+colIndex].title).css('color', this.manifest.columns['y'+colIndex].color);
            $tooltipItem.find('.graph-tooltip-item-value').text(shownValue);
            $tooltipItem.find('.graph-tooltip-item-unit').text(this.manifest.axes['y'].unit);

            $tooltipItem.appendTo(this.$tooltip);
        })

        // Prepend the timestamp / period to the tooltip
        const $tooltipItem = thusPi.template.get('.graph-tooltip-item');

        $tooltipItem.find('.graph-tooltip-item-column').text(this.manifest.axes.x.title);

        console.log(selectedRow);

        // Show the formatted x-value of the selected point otherwise
        $tooltipItem.find('.graph-tooltip-item-value').text(selectedRow['x_formatted']);

        $tooltipItem.prependTo(this.$tooltip);

        // Move and show the tooltip
        this.$tooltip.css({'top': yPx+'px', 'left': (xPx < this.$graph.outerWidth() / 2 ? xPx : xPx - this.$tooltip.outerWidth()) +'px'}).show();
        this.$tooltip.toggleClass('tooltip-align-right', );
    },
    
    _drawHighlight(xRelative1, xRelative2) {
        this.$highlight.css({
            left: Math.min(xRelative1, xRelative2),
            width: Math.abs(xRelative2 - xRelative1)
        })
    },

    async _fetchRows(interval = 'recording') {
        this._clear();
        this._setStatus('loading');

        return new Promise((resolve, reject) => {           
            thusPi.api.call('recordings/json', {
                id: this.recordingId,
                max_rows: this.maxRows,
                interval: interval
            }, true).then(response => {
                this.rows     = response.data.rows;
                this.manifest = response.data.manifest;
                
                resolve();
            }).catch((err) => {
                console.log(err);
                this._setStatus('error');
            })
        })
    },

    _paintRows(rows) {
        const rowCount = rows.length;

        let svgContent = '';
        let paths = {};
        let size = {min_x: null, max_x: null, min_y: null, max_y: null};
        
        // Loop all rows to find min and max of graph (while loop is fastest)
        let i = 0;
        while (i < rowCount) {
            // Check if x is lower than the current min x
            if(size.min_x === null || this.rows[i].x < size.min_x) {
                size.min_x = rows[i].x;
            }

            // Check if x is higher than the current max x
            if(size.max_x === null || this.rows[i].x > size.max_x) {
                size.max_x = rows[i].x;
            }

            // Check if lowest y of this row is lower than the current min y
            const minY = Math.min(...rows[i].y);
            if(size.min_y === null || minY < size.min_y) {
                size.min_y = minY;
            }

            // Check if highest y of this row is higher than the current max y
            const maxY = Math.max(...rows[i].y);
            if(size.max_y === null || maxY > size.max_y) {
                size.max_y = maxY;
            }
            
            ++i;
        }

        // Calculate difference in sizes
        size.dif_x = size.max_x - size.min_x;
        size.dif_y = size.max_y - size.min_y;

        this.size = size;

        // Loop all rows to generate SVG paths (while loop is fastest)
        let rowIndex = 0;
        while (rowIndex < rowCount) {
            const colCount = rows[rowIndex].y.length;

            // Loop all columns (while loop is fastest)
            let colIndex = 0;
            while(colIndex < colCount) {
                if(rowIndex === 0){
                    const start = this._calculatePointCoordinates(rowIndex, colIndex, size);
                    paths[colIndex] = `M ${start.x} ${start.y}`;

                    ++colIndex;
                    continue;
                }

                if(!isSet(paths[colIndex])) {
                    ++colIndex;
                    continue;
                }

                paths[colIndex] += this._calculateBezierPath(rowIndex, colIndex, size);

                ++colIndex;
            }

            ++rowIndex;
        }

        // Loop all SVG paths to transform into an HTML element
        $.each(paths, (colIndex, path) => {
            const stroke = this.manifest.columns['y'+colIndex]?.color || null;
            svgContent += `<path class="graph-line" ${isSet(stroke) ? `style="stroke: ${stroke};"  ` : ''}data-column-index="${colIndex}" d="${path.trim()}" fill="transparent"></path>`; 
        })

        this.$graph.html(`<svg width="100%" height="100%" viewBox="0 0 1000 1000" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">${svgContent}</svg>`);
        
        // Wait until the HTML has been changed
        setTimeout(() => {
            this._setStatus('ready');
        }, 0);
    },

    _calculateLineProperties(pointA, pointB) {
        const lengthX = pointB.x - pointA.x;
        const lengthY = pointB.y - pointA.y;

        return {
            length: Math.sqrt(Math.pow(lengthX, 2) + Math.pow(lengthY, 2)),
            angle: Math.atan2(lengthY, lengthX)
        }
    },

    _calculateBezierPath(rowIndex, colIndex, size) {
        const points = [
            this._calculatePointCoordinates(rowIndex - 2, colIndex, size), // -2 (One before previous)
            this._calculatePointCoordinates(rowIndex - 1, colIndex, size), // -1 (Previous)
            this._calculatePointCoordinates(rowIndex, colIndex, size),     // 0  (Current)
            this._calculatePointCoordinates(rowIndex + 1, colIndex, size), // 1  (Next)
        ];

        // Start control point
        const cpStart = this._calculateControlPoint(points[0], points[1], points[2]);

        // End control point
        const cpEnd = this._calculateControlPoint(points[1], points[2], points[3], true)
        return `C ${cpStart.x},${cpStart.y} ${cpEnd.x},${cpEnd.y} ${points[2].x},${points[2].y}`
    },

    _calculateControlPoint(previous, current, next, reverse) {
        // If current is the first or last point,
        // 'previous' or 'next' is not set.
        // Fall back to 'current'
        previous = previous || current;
        next = next || current;

        // Properties of the opposed line
        const opposedLine = this._calculateLineProperties(previous, next)

        // If is end-control-point, add PI to the angle to go backward
        const angle  = opposedLine.angle + (reverse ? Math.PI : 0)
        const length = opposedLine.length * this.curveSmoothing;

        // The control point position is relative to the current point
        const x = current.x + Math.cos(angle) * length
        const y = current.y + Math.sin(angle) * length

        return {x: x, y: y}
    },

    _calculatePointCoordinates(rowIndex, colIndex, size) {
        const row = this.rows[rowIndex];
        if(!isSet(row)) { return undefined; }

        const x = row.x;
        if(!isSet(x)) { return undefined; }

        const y = row.y[colIndex];
        if(!isSet(y)) { return undefined; }

        return {
            x: (x - size.min_x) / size.dif_x * 1000,
            y: 1000 - (y - size.min_y) / size.dif_y * 1000,
        }
    },

    _calculateRelativePixelsFromPixels(pixels, type) {
        if(type == 'x') {
            return clamp(0, pixels - this.$graph.offset().left, this.$graph.outerWidth());
        } else if(type == 'y') {
            return clamp(0, pixels - this.$graph.offset().top, this.$graph.outerHeight());
        }

        return null;
    },

    _calculatePixelsFromRelativePixels(relativePixels, type) {
        if(type == 'x') {
            return this.$graph.offset().left + relativePixels;
        } else if(type == 'y') {
            return this.$graph.offset().top + relativePixels;
        }

        return null;
    },

    _calculateValueFromRelativePixels(relativePixels, type) {
        if(!isSet(this.size)) {
            return null;
        }
        
        if(type == 'x') {
            return this.size.min_x + (relativePixels / this.$graph.outerWidth()) * this.size.dif_x;
        } else if(type == 'y') {
            return this.size.min_y + (relativePixels / this.$graph.outerHeight()) * this.size.dif_y;
        }

        return null;
    },

    _calculateRelativePixelsFromValue(value, type) {
        if(!isSet(this.size)) {
            return null;
        }

        if(type == 'x') {
            return clamp(0, (value - this.size.min_x) / this.size.dif_x * this.$graph.outerWidth());
        } else if(type == 'y') {
            return clamp(0, (value - this.size.min_y) / this.size.dif_y * this.$graph.outerHeight());
        }

        return null;
    },

    _setStatus(status) {
        this.$graphContainer.attr('data-status', status);
		return this;
    },

    _clear() {
     	this.$graph.html('');
		return this;
    }
})

$(document).on('thuspi.ready', function() {
    if(thusPi.page.current() != 'recordings/graph') {
        return false;
    }

    // 500 rows or more, depending on the width of the graph container
    const maxRows = Math.max(500, $('.graph').outerWidth() * 1.5);

    thusPi.recordings.graph.setup($('.graph'), urlParam('id'), maxRows);
})

$(document).on('mousenter mouseleave mousemove', '.graph', throttle(function() {
    thusPi.recordings.graph.moveCrosshairs(thusPi.data.pointer.x, thusPi.data.pointer.y);
    thusPi.recordings.graph.updateTooltip(thusPi.data.pointer.x, thusPi.data.pointer.y);

    if(thusPi.data.pointer.btnDown.primary) {
        thusPi.recordings.graph.drawSelection(thusPi.data.pointer.x, thusPi.data.pointer.y);
    }
}, 10))