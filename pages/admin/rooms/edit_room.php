<?php 
	$room_fallback = ['name' =>\thusPi\Locale\translate('state.unknown')];

	if(!($rooms = @file_get_json("{$d['data']}/memory/rooms.json"))) {
		exit(json_output("Failed to load rooms."));
	}

	if(isset($_GET['room_key'])) {
		$new_room = false; 
		$room_key = $_GET['room_key']; 

		if(!isset($rooms[$room_key])) {
			exit(json_output("Room {$room_key} was not found."));
		}
		$room = $rooms[$room_key];
	} else {
		$new_room = true;
		while (!isset($room_key) || isset($rooms[$room_key])) {
			$room_key = bin2hex(random_bytes(2));
		}
	}
?>
<form method="POST" action="../_handlers/edit_room.php" id="form-edit-room" class="tile transition-slide-top" data-room-key="<?php echo($_GET['room_key']); ?>">
	<div class="row">
		<div class="col-12 col-md-6 mb-2">
			<h3 class="tile-title"><?php \thusPi\Locale\translate('admin.rooms.edit_room.name.title', true); ?></h3>
			<input name="name" placeholder="<?php \thusPi\Locale\translate('admin.rooms.edit_room.name.title', true); ?>" value="<?php echo($room['name']); ?>" type="text" />
		</div>
		<div class="col-12 col-md-6 mb-2">
			<h3 class="tile-title"><?php \thusPi\Locale\translate('admin.rooms.edit_room.icon.title', true); ?></h3>
			<?php echo(input_iconpicker(['var(--info)' => [
				'mi.tv', 'mi.weekend', 'mi.chair', '_ROW_',
				'mi.king_bed', 'far.bed', 'mi.boy', 'mi.girl', '_ROW_',
				'mi.kitchen', 'mi.blender', 'mi.microwave', 'mi.countertops', '_ROW_',
				'mi.door_front', 'mi.door_back', 'mi.door_sliding', 'mi.garage', 'mi.fence', '_ROW_',
				'mi.fireplace', 'mi.shower', 'far.flower-tulip', 'mi.window', 'mi.balcony', '_ROW_',
				'mi.videocam', 'mi.camera_indoor', 'mi.camera_outdoor'
			]])); ?>
		</div>
		<div class="col-12 col-md-6 mb-2 room-options">
			<h3 class="tile-title"><?php \thusPi\Locale\translate('admin.rooms.edit_room.options.title', true); ?></h3>
			<div class="room-options-inner">
				<input type="hidden" name="room_key" value="<?php echo($_GET['room_key']); ?>">
			</div>
			<div class="row rounded overflow-hidden room-option-template mb-1" style="display: none;">
				<div class="col-12 col-md-6 px-0 border-right border-secondary">
					<div class="option-key bg-tertiary text-muted input rounded-0 text-overflow-ellipsis"></div>
				</div>
				<div class="col-12 col-md-6 px-0">
					<input class="option-value rounded-0" type="text">
				</div>
			</div>
		</div>
	</div>
</form>