<?php 
	if(($userdata_all = file_get_json("{$d['data']}/memory/users.json")) === false) {
		return;
	}
?>
<?php foreach ($userdata_all as $loop_uid => $loop_userdata) : ?>
	<?php 
		if(!isset($loop_userdata['name'])) { $loop_userdata['name'] = ''; }
		if(!isset($loop_userdata['username'])) { $loop_userdata['username'] = ''; }
	?>
	<a onclick="loadPage('admin', 'users>edit_user', {'user_uid': '<?php echo($loop_uid); ?>'});" class="btn btn-tertiary tile mb-2 select-user-entry transition-fade-order">
		<div class="row">
			<div class="col-12 col-md-6 col-lg-4 text-overflow-ellipsis">
				<?php echo($loop_userdata['name']); ?>
			</div>
			<div class="col-12 col-md-6 col-lg-3 text-muted text-overflow-ellipsis text-md-right text-lg-left">
				<?php echo($loop_userdata['username']); ?>
			</div>
			<div class="col-12 col-lg-5 text-muted text-overflow-ellipsis text-lg-right d-none d-md-block font-monospace parent2-hover-hide">
				<?php echo($loop_uid); ?>
			</div>
		</div>
	</a>
<?php endforeach; ?>
<a class="btn btn-tertiary tile mb-2 transition-fade-order" onclick="loadPage('admin', 'users>edit_user');">
	<?php \thusPi\Locale\translate('admin.users.add_user', true); ?>
</a>