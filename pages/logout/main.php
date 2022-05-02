<?php 
	if(!\thusPi\Users\CurrentUser::signOut()) {
		\thusPi\Response\error('error_signing_out', 'Failed to sign out.');
	}
?>
<script>
	thusPi.page.load('login/main', true);
</script>