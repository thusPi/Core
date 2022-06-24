<?php 
    if(!isset($_GET['id'])) {
        return false;
    }

    $recording_id = $_GET['id'];

    $recording = new \thusPi\Recordings\Recording($recording_id);
	$device   = new \thusPi\Devices\Device($recording_id);
?>
<div class="graph-container transition-slide-top transparent-selection flex-row" data-status="loading">
	<div class="graph-body tile col">
		<canvas class="graph-canvas"></canvas>
		<!-- <div class="graph-area-ver">
			<div class="graph-axis-title-wrapper text-vertical mt-auto text-muted">
				<span class="graph-axis-title text-overflow-ellipsis"></span>
				<i class="graph-axis-icon far fa-arrow-up text-vertical"></i>
			</div>
			<div class="graph-axis-steps"></div>
		</div>
		<div class="graph-area-hor">
			<div class="graph-axis-steps"></div>
			<div class="graph-axis-title-wrapper ml-auto text-muted">
				<span class="graph-axis-title text-overflow-ellipsis"></span>
				<i class="graph-axis-icon far fa-arrow-right"></i>
			</div>
		</div>
		<div class="graph-wrapper">
			<div class="graph"></div>
			<div class="graph-overlay">
				<div class="graph-selection"></div>
				<div class="graph-highlight"></div>
				<div class="graph-crosshair graph-crosshair-hor"></div>
				<div class="graph-crosshair graph-crosshair-ver"></div>
				<div class="graph-target"></div>
				<div class="graph-tooltip tile tile-small"></div>
				<div class="graph-status graph-status-loading">
					<i class="far fa-spinner-third fa-spin fa-2x text-blue"></i>
					<span class="d-block px-2" style="font-size: 2rem;"><?php echo(\thusPi\Locale\translate('generic.loading')); ?></span>
				</div>
				<div class="graph-status graph-status-error">
					<i class="far fa-exclamation-circle fa-2x text-red"></i>
					<span class="d-block px-2" style="font-size: 2rem;"><?php echo(\thusPi\Locale\translate('generic.error')); ?></span>
				</div>
			</div>
		</div> -->
	</div>
	<!-- <div class="graph-sidebar tile col-auto h-100">
		<div class="tile-content flex-column">
			<h3 class="tile-title"><?php echo(\thusPi\Locale\Translate('recordings.graph.sidebar.item.view.title')); ?></h3>
			<div class="tile-item">
				<?php echo(create_icon([
					'icon'       => 'far.layer-plus', 
					'scale'      => 'md', 
					'classes'    => ['tile-item-icon', 'text-blue'],
					'attributes' => [
						'data-tooltip'          => \thusPi\Locale\Translate('recordings.graph.sidebar.item.add_layer.tooltip'),
						'data-tooltip-position' => 'below'
					]
				])); ?>
				<div class="tile-item-content">
					<input type="text">
				</div>
			</div> -->
			<!-- Interval -->
			<!-- <div class="tile-item">
				<?php echo(create_icon([
					'icon'       => 'far.arrows-h', 
					'scale'      => 'md', 
					'classes'    => ['tile-item-icon', 'text-blue'],
					'attributes' => [
						'data-tooltip'          => \thusPi\Locale\Translate('recordings.graph.sidebar.item.interval.tooltip'),
						'data-tooltip-position' => 'below'
					]
				])); ?>
				<div class="tile-item-content flex-column">
					<input type="text" data-type="search" name="graph_interval">
					<ul class="input-search-results" for="graph_interval">
						<li class="input-search-result" value="recording" selected><?php echo(\thusPi\Locale\translate('recordings.graph.sidebar.item.interval.per_recording')); ?></li>
						<li class="input-search-result" value="hour" onclick="thusPi.recordings.graph.drawInterval('hour');"><?php echo(\thusPi\Locale\translate('generic.interval.hour_per')); ?></li>
						<li class="input-search-result" value="day" onclick="thusPi.recordings.graph.drawInterval('day');"><?php echo(\thusPi\Locale\translate('generic.interval.day_per')); ?></li>
						<li class="input-search-result" value="week" onclick="thusPi.recordings.graph.drawInterval('week');"><?php echo(\thusPi\Locale\translate('generic.interval.week_per')); ?></li>
						<li class="input-search-result" value="month" onclick="thusPi.recordings.graph.drawInterval('month');"><?php echo(\thusPi\Locale\translate('generic.interval.month_per')); ?></li>
						<li class="input-search-result" value="year" onclick="thusPi.recordings.graph.drawInterval('year');"><?php echo(\thusPi\Locale\translate('generic.interval.year_per')); ?></li>
					</ul>
				</div>
			</div> -->
			<!-- Period -->
			<!-- <div class="tile-item">
				<?php echo(create_icon([
					'icon'       => 'far.clock', 
					'scale'      => 'md', 
					'classes'    => ['tile-item-icon', 'text-blue'],
					'attributes' => [
						'data-tooltip'          => \thusPi\Locale\Translate('recordings.graph.sidebar.item.period.tooltip'),
						'data-tooltip-position' => 'below'
					]
				])); ?>
				<div class="tile-item-content flex-column">
					<input type="text" data-type="search" name="graph_period">
					<ul class="input-search-results" for="graph_period">
						<li class="input-search-result" value="hour" onclick="thusPi.recordings.graph.selectPeriod('hour');"><?php echo(\thusPi\Locale\translate('generic.period.hour')); ?></li>
						<li class="input-search-result" value="day" onclick="thusPi.recordings.graph.selectPeriod('day');"><?php echo(\thusPi\Locale\translate('generic.period.day')); ?></li>
						<li class="input-search-result" value="week" onclick="thusPi.recordings.graph.selectPeriod('week');"><?php echo(\thusPi\Locale\translate('generic.period.week')); ?></li>
						<li class="input-search-result" value="month" onclick="thusPi.recordings.graph.selectPeriod('month');"><?php echo(\thusPi\Locale\translate('generic.period.month')); ?></li>
						<li class="input-search-result" value="year" onclick="thusPi.recordings.graph.selectPeriod('year');"><?php echo(\thusPi\Locale\translate('generic.period.year')); ?></li>
						<li class="input-search-result" value="other" selected><?php echo(\thusPi\Locale\translate('generic.state.all')); ?></li>
					</ul>
					<div class="flex-row">
						<button class="btn btn-scale-sm btn-no-focus bg-tertiary btn-primary mr-auto">
							<?php echo(create_icon([
								'icon'     => 'far.chevron-left',
								'scale'    => 'xs',
								'classes'  => ['text-blue']
							])); ?>
						</button>
						<input type="date" value="2021-08-29">
						<button class="btn btn-scale-sm btn-no-focus bg-tertiary btn-primary ml-auto">
							<?php echo(create_icon([
								'icon'     => 'far.chevron-right',
								'scale'    => 'xs',
								'classes'  => ['text-blue']
							])); ?>
						</button>
					</div>
				</div>
			</div>
			<div class="tile-item">
				<?php echo(create_icon([
					'icon'       => 'far.wave-sine', 
					'scale'      => 'md', 
					'classes'    => ['tile-item-icon', 'text-blue'],
					'attributes' => [
						'data-tooltip'          => \thusPi\Locale\Translate('recordings.graph.sidebar.item.line_smoothing.tooltip'),
						'data-tooltip-position' => 'below'
					]
				])); ?>
				<div class="tile-item-content">
					<div class="input" data-type="range" data-min="0" data-step="1" data-max="10" data-value="0"></div>
				</div>
			</div>
		</div> -->
		<!-- <div class="graph-sidebar-items-group">
			<h3 class="tile-title"><?php echo(\thusPi\Locale\Translate('recordings.graph.sidebar.item.tools.title')); ?></h3>
			<div class="graph-sidebar-item btn-list" data-type="single">
				<div class="btn btn-md-square bg-tertiary btn-primary no-hover" data-graph-action="line_total">
					<?php echo(create_icon([
						'icon'    => 'far.sigma',
						'scale'   => 'md',
						'classes' => ['text-blue']
					])); ?>
				</div>
				<div class="btn btn-md-square bg-tertiary btn-primary no-hover" data-graph-action="line_average">
					<?php echo(create_icon([
						'icon'    => 'far.times',
						'scale'   => 'md',
						'classes' => ['text-blue']
					])); ?>
				</div>
			</div>
			<div class="graph-sidebar-item btn-list" data-type="multiple">
				<div class="btn btn-md-square bg-tertiary btn-primary no-hover" data-graph-action="toggle_view" onchange="thusPi.recordings.graph.toggleView();">
					<?php echo(create_icon([
						'icon'    => 'far.expand',
						'scale'   => 'md',
						'classes' => ['text-blue']
					])); ?>
				</div>
				<div class="btn btn-md-square bg-tertiary btn-primary no-hover active" data-graph-action="toggle_steps" onchange="thusPi.recordings.graph.toggleStepsVisibility();">
					<?php echo(create_icon([
						'icon'    => 'far.grip-lines',
						'scale'   => 'md',
						'classes' => ['text-blue']
					])); ?>
				</div>
			</div>
		</div> -->
		<!-- <div class="graph-sidebar-items-group mt-auto">
			<h3 class="tile-title"><?php echo(\thusPi\Locale\Translate('recordings.graph.sidebar.item.legend.title')); ?></h3>
			<div class="graph-sidebar-item">
				<div class="graph-sidebar-item-main">
					<div class="graph-legend">
						<div class="graph-legend-item template btn btn-sm bg-tertiary btn-primary no-hover active">
							<div class="graph-legend-item-icon rounded"></div>
							<span class="graph-legend-item-main"></span>
						</div>
					</div>
				</div>
			</div>
		</div> -->
	<!-- </div> -->
</div>

<!-- Templates -->
<div class="graph-tooltip-item template">
	<span class="graph-tooltip-item-column"></span>
	<span class="graph-tooltip-item-value text-monospace"></span>
	<span class="graph-tooltip-item-unit text-monospace text-muted"></span>
</div>