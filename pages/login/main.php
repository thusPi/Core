<div class="tile transition-slide-top" id="login-tile">
	<form id="login-form" method="POST">
		<div class="mb-3">
			<h3 class="tile-title"><?php echo(\thusPi\Locale\translate('login.placeholder.username')); ?></h3>
			<input name="username" type="text" placeholder="<?php echo(\thusPi\Locale\translate('login.placeholder.username')); ?>"/>
		</div>
		<div class="mb-3">
			<h3 class="tile-title"><?php echo(\thusPi\Locale\translate('login.placeholder.password')); ?></h3>
			<div class="position-relative">
				<input name="password" type="password" placeholder="<?php echo(\thusPi\Locale\translate('login.placeholder.password')); ?>"/>
				<div class="toggle-password" onclick="togglePassword($(this), $(this).siblings('input').first())">
					<?php echo(icon_html('mi.visibility', 'show-password text-info', 'display: none;')); ?>
					<?php echo(icon_html('mi.visibility_off', 'hide-password text-info')); ?>
				</div>
			</div>
		</div>
		<button class="btn btn-primary bg-tertiary" role="submit">
			<?php echo(\thusPi\Locale\translate('generic.action.sign_in')); ?>
		</button>
	</form>
</div>