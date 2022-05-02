<?php global $c, $d, $f, $userdata; ?>
<div class="btn-list" data-type="single">
	<?php
		if($rooms = @file_get_json("{$d['data']}/memory/rooms.json")) {
			foreach($rooms as $room_key => $room) { ?>
				<div class="btn btn-tertiary bg-secondary d-block mb-2 transition-fade-order" onclick="loadPage('admin', 'rooms>edit_room', {'room_key': '<?php echo($room_key); ?>'});">
					<div class="row">
						<div class="col-auto d-none d-sm-flex pl-0 pr-2">
							<?php echo(icon_html($room['icon'], 'icon-scale-lg', "color: {$room['color']};")); ?>
						</div>
						<div class="col">
							<h3 class="tile-title"><?php echo($room['name']); ?></h3>
							<div class="row w-100">
								<div class="text-muted d-none d-sm-flex" style="padding-bottom: 0.1rem;"><?php \thusPi\Locale\translate('admin.page.rooms.amount_devices', true, [count($room['devices'])]); ?></div>
							</div>
						</div>
					</div>
				</div> <?php
			}
		} else {
			exit(json_output('Failed to load rooms.'));
		}
	?>
	<div class="btn btn-tertiary bg-secondary d-block mb-2 transition-fade-order" onclick="loadPage('admin', 'rooms>edit_room');">
	New room
	</div>
</div>