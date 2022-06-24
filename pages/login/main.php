<div class="tile transition-slide-top">
	<div class="tile-content">
		<form id="login-form" method="POST">
			<div class="mb-3">
				<h3 class="tile-title"><?php echo(\thusPi\Locale\translate('login.placeholder.username')); ?></h3>
				<input name="username" type="text" placeholder="<?php echo(\thusPi\Locale\translate('login.placeholder.username')); ?>"/>
			</div>
			<div class="mb-3">
				<h3 class="tile-title"><?php echo(\thusPi\Locale\translate('login.placeholder.password')); ?></h3>
				<div class="input-group position-relative">
					<input name="password" type="password" placeholder="<?php echo(\thusPi\Locale\translate('login.placeholder.password')); ?>"/>
					<div class="toggle-password" onclick="togglePassword($(this), $(this).siblings('input').first())">
						<?php echo(create_icon(['icon' => 'mi.visibility', 'scale' => 'sm', 'classes' => ['show-password', 'text-blue'], 'styles' => ['display' => 'none']])); ?>
						<?php echo(create_icon(['icon' => 'mi.visibility_off', 'scale' => 'sm', 'classes' => ['hide-password', 'text-blue']])); ?>
					</div>
				</div>
			</div>
			<button class="btn btn-blue bg-primary" role="submit">
				<?php echo(\thusPi\Locale\translate('generic.action.sign_in')); ?>
			</button>
		</form>
	</div>
</div>