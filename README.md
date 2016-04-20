# CSCart API Documentation Generator & Displayer

### Предварительная настройка и требования
* Должен быть установлен PHP 5.6. PHP7 пока не поддерживается, потому что используемая
библиотека phpDocumentor не поддерживает эту версию PHP.
* Клонируем репозиторий
* Запускаем команду `composer install` и ждём завершения установки зависимостей
* Устанавливаем в систему MongoDB >= 3.2, пароль устанавливать не надо


### Сбор документации
В папке с репозиторием выполняем команду:

```
./bin/cscart-apidoc build -s ../../vhosts/cs-cart/ --ver=4.3.5
```

Параметры запуска:

* `-s` - путь до каталога, в котором находится распакованный дистрибутив CS-Cart;
* `--ver` - версия CS-Cart, для которой собирается информация.

Это запустит процесс сбора информации о функциях хуках, классах, их методах и свойств.
Затем собранные данные будут сохранены в хранилище MongoDB.


### Отображение документации

Нужно настроить свой веб-сервер (Nginx или Apache) так, чтобы каталог "public"
в репозитории являлся document root. Затем в браузере нужно открыть адрес хоста, который был указан ранее в настройках сервера.

Конфигурационный файл для Nginx:

```
server {
    listen 80;
    server_name cscart.apidoc;

    index index.php;

    error_log off;
    access_log off;

    root /vagrant/repos/cscart-apidoc/public;
    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    location ~ \.php {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param SCRIPT_NAME $fastcgi_script_name;
        fastcgi_index index.php;
        fastcgi_pass 127.0.0.1:9000;
    }
}
```

.htaccess-файл для Apache:

```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]
```
