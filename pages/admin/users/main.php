<div class="btn-column">
	<?php 
		$users = \thusPi\Users\get_all();
		
		foreach ($users as $user) { ?>
			<?php 
				$user_class = new \thusPi\Users\User($user['uuid']);
				$profile_picture = $user_class->getProfilePicture();

				if(isset($profile_picture)) {
					$tile_icon_html = "<img class=\"tile-icon icon icon-scale-xl\" src=\"{$profile_picture}\">";
				} else {
					$tile_icon_html = create_icon(['icon' => 'far.user', 'scale' => 'xl', 'classes' => ['tile-icon text-blue']]);
				}
			?>
			<a 
				class="btn btn-tertiary tile transition-fade-order"
				data-page-search="<?php echo($user['name']); ?>"
				href="#/admin/users/manage/?uuid=<?php echo($user['uuid']); ?>">
					<?php echo($tile_icon_html); ?>
					<h3 class="tile-title"><?php echo($user['name']); ?></h3>
					<div class="tile-content">
						<span class="text-muted"><?php echo($user['uuid']); ?></span>
					</div>
			</a>
	<?php } ?>
</div>
<a class="btn bg-secondary btn-blue btn-floating transition-slide-right" href="#/admin/users/manage/">
	<?php echo(create_icon('far.plus', 'xl', ['icon-square'])); ?>
</a>