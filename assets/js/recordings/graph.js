thusPiAssign('recordings.graph', {
    setup($graph, recordingId, maxRows) {
        this.$graph         = $graph;
        this.recordingId    = recordingId;
        this.maxRows        = maxRows;
        this.curveSmoothing = 0;

        this._fetchRows(undefined);
    },

    _fetchRows(selection = undefined) {
        thusPi.api.call('recordings/json', {id: this.recordingId, max_rows: this.maxRows}).then(response => {
            this.rows     = response.data.rows;
            this.size     = response.data.size;
            this.manifest = response.data.manifest;

            console.log(this.manifest);
            
            this._paintRows();
        })
    },

    _paintRows() {
        $('.graph-overlay').remove();

        let svgContent = '';
        let paths = {};
        $.each(this.rows, (rowIndex, row) => {
            $.each(row.y, (colIndex, y) => {
                if(rowIndex === 0){
                    const start = this._calculatePointCoordinates(rowIndex, colIndex);
                    paths[colIndex] = `M ${start.x} ${start.y}`;
                    return true;
                }

                if(!isSet(paths[colIndex])) {
                    return true;
                }

                paths[colIndex] += this._calculateBezierPath(rowIndex, colIndex);
            })
        })

        $.each(paths, (colIndex, path) => {
            svgContent += `<path d="${path.trim()}" stroke="${this.manifest.columns[`y${colIndex}`].color || 'red'}" fill="transparent"></path>`; 
        })

        this.$graph.html(`<svg width="100%" height="100%" viewBox="0 0 1000 1000" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">${svgContent}</svg>`);
    },

    _calculateLineProperties(pointA, pointB) {
        const lengthX = pointB.x - pointA.x;
        const lengthY = pointB.y - pointA.y;

        return {
            length: Math.sqrt(Math.pow(lengthX, 2) + Math.pow(lengthY, 2)),
            angle: Math.atan2(lengthY, lengthX)
        }
    },

    _calculateBezierPath(rowIndex, colIndex) {
        const points = [
            this._calculatePointCoordinates(rowIndex - 2, colIndex), // -2 (One before previous)
            this._calculatePointCoordinates(rowIndex - 1, colIndex), // -1 (Previous)
            this._calculatePointCoordinates(rowIndex, colIndex),     // 0  (Current)
            this._calculatePointCoordinates(rowIndex + 1, colIndex), // 1  (Next)
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

    _calculatePointCoordinates(rowIndex, colIndex) {
        const row = this.rows[rowIndex];
        if(!isSet(row)) { return undefined; }

        const x = row.x;
        if(!isSet(x)) { return undefined; }

        const y = row.y[colIndex];
        if(!isSet(y)) { return undefined; }

        return {
            x: (x - this.size.min_x) / this.size.dif_x * 1000,
            y: 1000 - (y - this.size.min_y) / this.size.dif_y * 1000,
        }
    }
})

$(document).on('thuspi.ready', function() {
    if(thusPi.page.current() != 'recordings/graph') {
        return false;
    }

    thusPi.recordings.graph.setup($('.graph'), urlParam('id'), 500);
})
