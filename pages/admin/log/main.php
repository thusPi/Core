<div class="log-items-category-filter btn-row mb-2">
	<?php 
		$log_groups = [
			'success' => 'success', 
			'info'    => 'info', 
			'warning' => 'warning', 
			'error'   => 'danger', 
			'debug'   => 'debug'
		];

		$log_inactive_groups = \thusPi\Users\CurrentUser::getSaved('log_inactive_groups') ?? [];

		foreach ($log_groups as $namespace => $color) { 
		?>
			<div 
				tabindex="0" 
				class="btn bg-tertiary btn-<?php echo($color); ?> btn-no-hover mx-1<?php echo(!in_array($namespace, $log_inactive_groups) ? ' active' : ''); ?>" 
				data-category="<?php echo($namespace); ?>"
				onclick="toggleLogCategory('<?php echo($namespace); ?>');">
				<?php echo(\thusPi\Locale\translate("admin.log.message_type.{$namespace}")); ?>
			</div>
		<?php 
		} ?>
</div>
<div class="log-items scrollbar-visible">
	<div class="log-item" id="log-item-template" data-category="debug" data-at="0" data-index="0">
		<div class="log-item-border"></div>
		<div class="log-item-time text-overflow-ellipsis"></div>
		<div class="log-item-content-wrapper">
			<span class="log-item-title"></span>
			<span class="log-item-content"></span>
		</div>
	</div>
	<div class="intersection-observer-trigger" onchange="requestMoreLogs();"></div>
</div>