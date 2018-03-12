 <div class="wrap">
	<form action="options.php" method="post">
	<?php
		settings_fields('onesignal-push-settings');
		do_settings_sections('onesignal-push-settings');
		submit_button();
	?>
	</form>
</div>
