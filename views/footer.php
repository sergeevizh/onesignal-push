<div id="push-me" style="width: 100px; height: 100px; position: fixed; top: 20px; right: 30px; background: #f0f; z-index: 999;"></div>

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

		document.getElementById("push-me").addEventListener('click', function() {
			OneSignal.push(["registerForPushNotifications"]);
		});
	})
}); 
</script>
