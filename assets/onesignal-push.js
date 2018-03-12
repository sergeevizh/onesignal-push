(function() {
	var parent = document.querySelector('.onesignal-push');

	if(parent === null || typeof onesignal_push_id === 'undefined')
		return false;

	OneSignal = window.OneSignal || [];

    // local storage item marker
    var mark = 'onesignal-push';

	var init = function() {
		var onesignal = document.createElement('script');
		onesignal.type = 'text/javascript';
		onesignal.async = true;
		onesignal.src = 'https://cdn.onesignal.com/sdks/OneSignalSDK.js';

		var s = document.getElementsByTagName('script')[0];
		s.parentNode.insertBefore(onesignal, s);
	}

	var check = function() {
		var ls = localStorage.getItem(mark);

 		if(typeof Notification === 'undefined' || Notification.permission !== "default")
			return false;

		if(typeof ls === "string" && Number(ls) < Date.now())
			return true;

		if(ls === null)
			localStorage.setItem(mark, Date.now());

		return false;
	}

	document.addEventListener('DOMContentLoaded', function() {
		if(check() === false)
			return false;


		OneSignal.push(["init", {
			appId: onesignal_push_id,
			autoRegister: false,
			welcomeNotification: {
				disable: true
			}
		}]);


		OneSignal.push(function() {
			// Trigger on close button click
			parent.querySelector('.onesignal-push__close').addEventListener('click', function() {
				// 2 weeks
				var future = 86400 * 1000 * 14;

				localStorage.setItem(mark, Date.now() + future);

				return parent.classList.add('onesignal-push--hide');
			});

			// Trigger on subscribe button click
			parent.querySelector('.onesignal-push__button').addEventListener('click', function() {
				return OneSignal.registerForPushNotifications();
			});

			// Hide message on permission change
			OneSignal.on('notificationPermissionChange', function(permission) {
				return parent.classList.add('onesignal-push--hide');
			});

			// Check whether push notifications supported to show popup
			if(OneSignal.isPushNotificationsSupported())
				return parent.classList.remove('onesignal-push--hide');
		});


		return init();

	}, false);
})();
