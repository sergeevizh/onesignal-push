<div id="knife-push" class="knife-push">
	<button class="knife-push__button">
		<svg class="knife-push__icon" viewBox="0 0 768 768">
	    	<path d="M384 703.5c-36 0-64.5-28.5-64.5-63h127.5c0 37.256-27.919 63-63 63zM576 352.5v159l64.5 64.5v31.5h-513v-31.5l64.5-64.5v-159c0-99 52.5-180 144-202.5v-22.5c0-27 21-48 48-48s48 21 48 48v22.5c91.5 22.5 144 105 144 202.5zM639 336c-4.5-85.5-48-159-112.5-205.5l45-45c76.5 58.5 127.5 148.5 132 250.5h-64.5zM243 130.5c-66 46.5-109.5 120-114 205.5h-64.5c4.5-102 55.5-192 132-250.5z"></path>
		</svg>

		<div class="knife-push__title"><?php echo $button_text ?></div>
	</button>
	<div class="knife-push__promo"><?php echo $promo_text ?></div>
	<button class="knife-push__close"></button>
</div>


<script>
	var OneSignal = window.OneSignal || [];

	OneSignal.push(["init", {
		appId: "b4bd70cc-e734-44ae-86ab-21425564d620",
		autoRegister: false,
		welcomeNotification: {
			disable: true
		}
	}]);

	OneSignal.push(function() {
		if(!OneSignal.isPushNotificationsSupported())
			return false;

		OneSignal.isPushNotificationsEnabled(function(enabled) {
			if(enabled)
				return false;

			document.querySelector(".knife-push__button").addEventListener('click', function() {
				OneSignal.push(["registerForPushNotifications"]);
			});
		})
	});
</script>
