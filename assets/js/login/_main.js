$(document).on('submit', 'form#login-form', function(e) {
	e.preventDefault();

	let $form = $(this);
	$form.find('button').showLoading();

	let username = $form.find('[name="username"]').val();
	let password = $form.find('[name="password"]').val();

	thusPi.api.call('login-verify', {'username': username, 'password': password}).then(function(response) {
		$form.find('button').hideLoading();
		thusPi.page.load(urlParam('redirect') || 'home/main', true);
	}).catch(function() {
		$form.find('button').hideLoading();
		thusPi.message.error(thusPi.locale.translate('login.invalid_credentials'));
	}) 
})