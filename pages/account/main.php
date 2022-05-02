<?php global $c, $user_config, $d, $f, $userdata; ?>
<form action="save_new_details.php" method="POST" autocomplete="off">
	<div class="tile mb-2">
		<h3 class="tile-title"><?php \thusPi\Locale\translate('account.account_credentials.name.title', true); ?></span>
		<input type="text" name="name" value="<?php echo($_SESSION['thusPi']['name']); ?>">
	</div>
	<div class="tile mb-2">
		<h3 class="tile-title"><?php \thusPi\Locale\translate('account.account_credentials.username.title', true); ?></span>
		<input type="text" name="username" value="<?php echo($userdata['username']); ?>">
	</div>
	<div class="tile mb-2">
		<h3 class="tile-title"><?php \thusPi\Locale\translate('account.account_credentials.new_password.title', true); ?></span>
		<input class="mb-2" type="text" name="new_password" value="" placeholder="<?php \thusPi\Locale\translate('account.account_credentials.new_password.placeholder', true); ?>">
		<input class="mb-2" type="text" name="new_password_confirm" value="" placeholder="<?php \thusPi\Locale\translate('account.account_credentials.confirm_new_password.placeholder', true); ?>">
		<input class="mb-2" type="text" name="current_password" value="" placeholder="<?php \thusPi\Locale\translate('account.account_credentials.current_password.placeholder', true); ?>">
	</div>
</form>