# Webpush manage #

Webpush sendig with OneSignal in wordpress

### Description ###

Plugin adds popup to all site pages with subscribe suggestion to webpushes.
Showing push sending form in admin side post pages.

### Installation ###

1. Upload unpacked folder `knife-push` into plugins directory `/wp-content/plugins/`
2. Activate plugin in dashboard
3. Update or add manifest.json acording OneSignal requirements
4. Add keys on plugin setting page `/wp-admin/options-general.php?page=knife-push`
5. Copy files from sdk folder to root directory or set rules in nginx this way:

```
location = /OneSignalSDKUpdaterWorker.js {
	root /srv/http/knife.media/plugins/knife-push/sdk;
}

location = /OneSignalSDKWorker.js {
	root /srv/http/knife.media/plugins/knife-push/sdk;
}
```

### Help ###

You can ask your question or send pull request on Github

https://github.com/antonlukin/knife-push/issues
