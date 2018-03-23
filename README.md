# Wordpress plugin for OneSignal API #

Webpush sending with OneSignal in wordpress

### Description ###

Plugin adds popup to all site pages with subscribe suggestion to webpushes.
Showing push sending form in admin side post pages.

### Installation ###

1. Upload unpacked folder `onesignal-push` into plugins directory `/wp-content/plugins/`
2. Activate plugin in dashboard
3. Update or add `manifest.json` file according OneSignal requirements
4. Add keys on plugin settings page `/wp-admin/options-general.php?page=onesignal-push`
5. Copy files from sdk folder to root directory or set rules in nginx this way:

```
location ~* ^/OneSignalSDK(.*?).js$ {
    add_header Content-Type application/x-javascript;

    return 200 "importScripts('https://cdn.onesignal.com/sdks/OneSignalSDK.js');";
}
```

### Help ###

You can ask your question or send pull request on Github

https://github.com/antonlukin/onesignal-push/issues
