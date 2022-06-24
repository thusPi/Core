<?php 
	$new_user = true;
	$properties = [];

	if(isset($_GET['uuid'])) {
		$user = @new \thusPi\Users\User($_GET['uuid']);

		// Check if a user with the specified uuid actually exists
		$properties = $user->getProperties();
		if(isset($properties) && !empty($properties)) {
			$new_user = false;
		}
	}

	$uuid = $new_user ? generate_uuid() : $_GET['uuid'];
?>
<form class="form transition-slide-top" method="POST">
	<!-- Name -->
	<div class="form-group col-12 col-md-6">
		<h3 class="form-group-title"><?php echo(\thusPi\Locale\translate('admin.users.manage_user.name.title')); ?></h3>
		<span class="form-group-description"><?php echo(\thusPi\Locale\translate('admin.users.manage_user.name.description')); ?></span>
		<input name="name" class="form-group-input" type="text" placeholder="<?php echo(\thusPi\Locale\translate('admin.users.manage_user.name.title')); ?>" value="<?php echo($properties['name'] ?? ''); ?>" />
	</div>
	<!-- Username -->
	<div class="form-group col-12 col-md-6">
		<h3 class="form-group-title"><?php echo(\thusPi\Locale\translate('admin.users.manage_user.username.title')); ?></h3>
		<span class="form-group-description"><?php echo(\thusPi\Locale\translate('admin.users.manage_user.username.description')); ?></span>
		<input name="username" class="form-group-input" type="text" placeholder="<?php echo(\thusPi\Locale\translate('admin.users.manage_user.username.title')); ?>" value="<?php echo($properties['username'] ?? ''); ?>" />
	</div>
	<!-- Flags -->
	<div class="form-group col-12 col-md-6">
		<h3 class="form-group-title"><?php echo(\thusPi\Locale\translate('admin.users.manage_user.flags.title')); ?></h3>
		<span class="form-group-description"><?php echo(\thusPi\Locale\translate('admin.users.manage_user.flags.description')); ?></span>
		<a href="#/admin/users/manage_flags?uuid=<?php echo($uuid); ?>" class="btn bg-primary btn-blue"><?php echo(\thusPi\Locale\translate('generic.action.modify')); ?></a>
	</div>
	<!-- Empty -->
	<div class="form-group col-12 col-md-6"></div>
	<!-- Password -->
	<div class="form-group col-12 col-md-6">
		<h3 class="form-group-title"><?php echo(\thusPi\Locale\translate('admin.users.manage_user.password.title')); ?></h3>
		<span class="form-group-description"><?php echo(\thusPi\Locale\translate('admin.users.manage_user.password.description')); ?></span>
		<div class="btn-row">
			<button class="btn bg-primary btn-red"><?php echo(\thusPi\Locale\translate('generic.action.reset')); ?></button>
		</div>
	</div>
	<!-- Manage -->
	<div class="form-group col-12 col-md-6">
		<h3 class="form-group-title"><?php echo(\thusPi\Locale\translate('admin.users.manage_user.manage.title')); ?></h3>
		<span class="form-group-description"><?php echo(\thusPi\Locale\translate('admin.users.manage_user.manage.description')); ?></span>
		<div class="btn-row">
			<button class="btn bg-primary btn-red"><?php echo(\thusPi\Locale\translate('generic.action.delete')); ?></button>
			<button class="btn bg-primary btn-yellow"><?php echo(\thusPi\Locale\translate('generic.action.sign_out')); ?></button>
		</div>
	</div>
</form>