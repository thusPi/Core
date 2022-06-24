<div class="log-group-buttons transition-slide-top position-relative btn-row mb-2">
	<?php 
		// Get list of all log groups
		$log_groups = \thusPi\Config\get(null, 'generic/log_groups');

		// Get list of inactive groups for current user
		$log_inactive_groups = \thusPi\Users\CurrentUser::getSetting('log_inactive_groups') ?? [];

		foreach ($log_groups as $name => $log_group) { 
			if(!isset($log_group['icon']) || !isset($log_group['color'])) continue;
			$color = ($log_group['color'] ?? '#000000');
		?>
			<div 
				tabindex="0" 
				class="btn bg-tertiary btn-no-hover flex-row<?php echo(!in_array($name, $log_inactive_groups) ? ' active' : ''); ?>"
				style="--background-active: <?php echo($color); ?>"
				data-group="<?php echo($name); ?>"
				data-color="<?php echo($color); ?>"
				onclick="toggleLogCategory('<?php echo($name); ?>');">
				<?php echo(create_icon(['icon' => $log_group['icon'], 'scale' => 'md'])); ?>
				<?php echo(\thusPi\Locale\translate("admin.log.message_type.{$name}")); ?>
			</div>
		<?php 
		} ?>
</div>
<div class="log-items flex-column scrollbar-visible">
	<div class="template log-item" data-category="success" data-at="0" data-index="0">
		<span class="log-item-time"></span>
		<span class="log-item-content-wrapper">
			<span class="log-item-title"></span>
			<span class="log-item-content"></span>
		</span>
	</div>
	<div class="intersection-observer-trigger" onchange="requestMoreLogs();"></div>
</div>