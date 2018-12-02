# Описание
Готовый сервис создания из PDF файла веб презентации доступной локально (не требуется
подлючение к интернету). На выходе создает zip архив распаковав который и открыв файл
`index.html` можно локально запустить презентацию.
Ограничения по умолчанию:
* Ориентация – портретная;
* Количество страниц – не больше 20 (можно изменить в файле `/config/params.php`);
* Размер файла – не более 50 Мб (можно изменить в файлах `/config/params.php` и `/web/.user.ini`).

Для интеграции с другими сервисами есть REST api метод `/api/pdf/<:id>` для получения списка ссылок изображений
конкретного слайдера (по его id).

# Требования

## Системное окружение:
1. Ununtu 16.04 LTS
1. php7.2
1. nginx
1. composer
1. poppler-utils
1. ghostscript

## Порядок развернывание окружения
Все приведенные шаги применимы после установки Ununtu 16.04 LTS и могут
использоваться как есть. Если системное окружение у вас уже есть, то
настройку следует выполнить исходя из него. При это следует внимательно прочесть
приведенные команды и решить, какие из них применимы в вашем случае.

1. Устанавливаем окружение:
    ```bash
    sudo apt-get install software-properties-common
    sudo add-apt-repository ppa:ondrej/php
    sudo add-apt-repository ppa:ondrej/nginx-mainline
    sudo apt update
    sudo apt-get install php7.2 php7.2-fpm nginx git curl poppler-utils ghostscript
    sudo apt-get install php7.2-curl php7.2-cli php7.2-zip php7.2-mbstring
    ```
1. Создаем локальную площадку `website.local`:
    ```bash
    sudo mkdir -p /var/www/website.local
    sudo chown -R $USER:www-data /var/www/website.local
    sudo chmod 2775 /var/www/website.local
    sudo sh -c "echo '127.0.0.1 website.local' >> /etc/hosts"
    ```
1. Создаем конфигурационный файл для nginx:
    ```nginx
    # Это записать в файл /etc/nginx/sites-enabled/website.local.conf
    # после чего выполнить sudo /etc/init.d/nginx restart
    server {
        charset utf-8;
        client_max_body_size 128M;

        listen 127.0.0.1:80;
        server_name website.local;
        root        /var/www/website.local/web;
        index       index.php;

        access_log  /var/www/website.local/runtime/access.log;
        error_log   /var/www/website.local/runtime/error.log;

        location / {
            try_files $uri $uri/ /index.php$is_args$args;
        }
        location ~ \.(txt|js|css|png|jpg|gif|swf|ico|pdf|mov|fla|zip|rar)$ {
            try_files $uri =404;
        }
        location ~ ^/assets/.*\.php$ {
            deny all;
        }

        location ~* /\. {
            deny all;
        }

        location ~ \.php$ {
            fastcgi_pass  127.0.0.1:9009;
            fastcgi_index index.php;

            include fastcgi_params;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        }
    }
    ```
1. Создаем конфигурационный файл для php7.2-fpm:
    ```ini
    # Это записать в /etc/php/7.2/fpm/pool.d/website.local.conf
    # после чего выполнить sudo /etc/init.d/php7.2-fpm restart
    [website.local]
    listen                 = 127.0.0.1:9009
    listen.backlog         = 2
    listen.allowed_clients = 127.0.0.1

    user  = $USER # вписать вместо $USER вывод команды: id -un
    group = $GROUP # вписать вместо $GROUP вывод команды: id -gn

    pm              = static
    pm.max_children = 2
    ```
1. Устанавливаем `composer`:
    ```bash
    curl -sS https://getcomposer.org/installer | php7.2
    sudo mv composer.phar /usr/bin/composer
    # Задаем OAuth GitHub token как это описано в https://getcomposer.org/doc/articles/troubleshooting.md#api-rate-limit-and-oauth-tokens
    composer global require hirak/prestissimo # ускорение загрузки пакетов
    ```
1. Клонируем проект и устанавливаем зависимости:
    ```bash
    git clone https://github.com/alekciy/pdf2image /var/www/website.local
    cd /var/www/website.local
    composer install
    ```
1. Подлючаем автодополнение для yii команд и их действий в консоле:
    ```bash
    sudo curl -L https://raw.githubusercontent.com/yiisoft/yii2/master/contrib/completion/bash/yii -o /etc/bash_completion.d/yii && source ~/.bashrc
    ```
    Теперь `./yii [TAB][TAB]` покажет список доступных команд. А при начале набора сам дополнит. Пример: `./yii mig[TAB]` автоматически дополнит до `./yii migrate`
1. Открываем в браузере страницу [website.local](http://website.local)

