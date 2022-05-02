<?php 
    if(!isset($_GET['id'])) {
        return false;
    }

    $analytic_id = $_GET['id'];

    $analytic = new \thusPi\Analytics\Analytic($analytic_id);
	$device   = new \thusPi\Devices\Device($analytic_id);
?>
<div class="graph-container transition-slide-top transparent-selection" data-status="loading">
	<div class="p-1 h-100 w-100">
		<div class="graph-body tile">
			<div class="graph-area-ver">
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
					<div class="graph-crosshair graph-crosshair-hor"></div>
					<div class="graph-crosshair graph-crosshair-ver"></div>
					<div class="graph-target"></div>
					<div class="graph-tooltip tile tile-small">
						<div class="graph-tooltip-item template">
							<span class="graph-tooltip-item-column text-overflow-ellipsis"></span>
							<span class="graph-tooltip-item-value text-monospace"></span>
							<span class="graph-tooltip-item-unit text-muted"></span>
						</div>
					</div>
					<div class="graph-status graph-status-loading">
						<i class="far fa-spinner-third fa-spin fa-2x text-info"></i>
						<span class="d-block px-2" style="font-size: 2rem;"><?php echo(\thusPi\Locale\translate('generic.state.loading')); ?></span>
					</div>
					<div class="graph-status graph-status-error">
						<i class="far fa-exclamation-circle fa-2x text-danger"></i>
						<span class="d-block px-2" style="font-size: 2rem;"><?php echo(\thusPi\Locale\translate('generic.state.error')); ?></span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="p-1">
		<div class="graph-sidebar tile">
			<div class="graph-sidebar-items-group">
				<h3 class="tile-title"><?php echo(\thusPi\Locale\Translate('analytics.graph.sidebar.item.view.title')); ?></h3>
				<div class="graph-sidebar-item">
					<?php echo(create_icon([
						'icon'       => 'far.layer-plus', 
						'scale'      => 'md', 
						'classes'    => ['graph-sidebar-item-icon', 'text-info'],
						'attributes' => [
							'data-tooltip'          => \thusPi\Locale\Translate('analytics.graph.sidebar.item.add_layer.tooltip'),
							'data-tooltip-position' => 'below'
						]
					])); ?>
					<div class="graph-sidebar-item-main">
						<input type="text">
					</div>
				</div>
				<div class="graph-sidebar-item">
					<?php echo(create_icon([
						'icon'       => 'far.wave-sine', 
						'scale'      => 'md', 
						'classes'    => ['graph-sidebar-item-icon', 'text-info'],
						'attributes' => [
							'data-tooltip'          => \thusPi\Locale\Translate('analytics.graph.sidebar.item.line_smoothing.tooltip'),
							'data-tooltip-position' => 'below'
						]
					])); ?>
					<div class="graph-sidebar-item-main">
						<div class="input" data-type="range" data-min="0" data-step="1" data-max="10" data-value="0"></div>
					</div>
				</div>
			</div>
			<div class="graph-sidebar-items-group">
				<h3 class="tile-title"><?php echo(\thusPi\Locale\Translate('analytics.graph.sidebar.item.tools.title')); ?></h3>
				<div class="graph-sidebar-item btn-list" data-type="single">
					<div class="btn btn-md-square bg-tertiary btn-primary no-hover" data-graph-action="line_total">
						<?php echo(create_icon([
							'icon'    => 'far.sigma',
							'scale'   => 'md',
							'classes' => ['text-info']
						])); ?>
					</div>
					<div class="btn btn-md-square bg-tertiary btn-primary no-hover" data-graph-action="line_average">
						<?php echo(create_icon([
							'icon'    => 'far.times',
							'scale'   => 'md',
							'classes' => ['text-info']
						])); ?>
					</div>
				</div>
				<div class="graph-sidebar-item btn-list" data-type="multiple">
					<div class="btn btn-md-square bg-tertiary btn-primary no-hover" data-graph-action="toggle_view" onchange="thusPi.analytics.graph.toggleView();">
						<?php echo(create_icon([
							'icon'    => 'far.expand',
							'scale'   => 'md',
							'classes' => ['text-info']
						])); ?>
					</div>
					<div class="btn btn-md-square bg-tertiary btn-primary no-hover active" data-graph-action="toggle_steps" onchange="thusPi.analytics.graph.toggleStepsVisibility();">
						<?php echo(create_icon([
							'icon'    => 'far.grip-lines',
							'scale'   => 'md',
							'classes' => ['text-info']
						])); ?>
					</div>
				</div>
			</div>
			<div class="graph-sidebar-items-group mt-auto">
				<h3 class="tile-title"><?php echo(\thusPi\Locale\Translate('analytics.graph.sidebar.item.legend.title')); ?></h3>
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
			</div>
		</div>
	</div>
</div>
	<!-- <div class="row m-0 row1">
		<div class="graph-axis-ver col-auto">
			<div class="axis-info text-muted"></div>
			<div class="comments"></div>
		</div>
		<div class="graph col">
			<div class="graph-inner"></div>
			<div class="graph-selection"></div>
			<div class="graph-marker graph-marker-ver"></div>
			<div class="graph-marker graph-marker-hor"></div>
			<div class="graph-toolbar tile-small bg-secondary">
				<div class="btn btn-md-square btn-primary no-hover bg-tertiary text-info" onclick="toggleBodySize(this);">
					<?php echo(create_icon('mdi.arrow-expand-horizontal', 'md', ['icon-enable'])); ?>
					<?php echo(create_icon('mdi.arrow-collapse-horizontal', 'md', ['icon-disable'], ['display' => 'none'])); ?>
				</div>
				<a class="btn btn-md-square btn-primary bg-tertiary text-info no-active" href="./api/get/analytics-download.php?analytics_id=<?php echo($_GET['analytics_id']); ?>" download>
					<?php echo(create_icon('mi.file_download', 'md')); ?>
				</a>
				<div class="btn btn-md-square btn-primary bg-tertiary text-info no-active" onclick="addGraphLayer()">
					<?php echo(create_icon('far.layer-plus', 'md')); ?>
				</div>
				<div class="graph-add-layer-search tile-small bg-secondary">
					<span class="input" data-type="search" id="graph-add-layer-search-input"></span>
				</div>
			</div>
			<div class="info-bar tile">
				<div class="info-bar-inner row flex-nowrap">
					
				</div>
			</div>
		</div>
	</div>
	<div class="row m-0">
		<div class="graph-axis-hor col">
			<div class="axis-info text-muted"></div>
			<div class="comments"></div>
		</div>
	</div> -->
<!-- <div class="graph-options tile row my-2">
	<div class="legend mb-2 mb-md-0 col-12 col-md pl-0 pr-2">
		<div class="col-auto legend-item" id="legend-item-template" style="display: none;">
			<div class="legend-icon"></div>
			<span class="legend-name"></span>
		</div>
	</div>
</div> -->