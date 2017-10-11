# Управление пушами #

Отправка пушей через OneSignal

### Описание ###

Плагин добавляет попап на все страницы блога с предложением подписаться на пуши. 
В админке на странице записи появляется форма отправки пуша для каждой статьи

### Установка ###

1. Загрузите папку `knife-push` в директорию `/wp-content/plugins/`
2. Активируйте плагин в панели администратора
3. Обновите или добавьте manifest.json в соотвествии с настройками OneSignal
4. Добавьте ключи в настройках плагина `/wp-admin/options-general.php?page=knife-push`
5. Перенесите файлы из папки sdk в корень проекта, либо настройте правила nginx по следующему принципу

```
location = /OneSignalSDKUpdaterWorker.js {
	root /srv/http/knife.media/plugins/knife-push/sdk;  
}

location = /OneSignalSDKWorker.js {
	root /srv/http/knife.media/plugins/knife-push/sdk;  
}  
```  

### Дополительная помощь ###

Задайте свой вопрос через форму на Github

https://github.com/antonlukin/knife-push/issues
