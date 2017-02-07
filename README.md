Symfony
=======

REQUIREMENTS
------------

Для работы необходим RabbitMQ

INSTALLATION
------------

Загрузка библиотек
~~~
composer install
~~~

Создание таблиц в базе
~~~
php bin/console doctrine:schema:update --force
~~~


Создание пользователей admin и demo
~~~
php bin/console doctrine:migrations:migrate
~~~

После установки желательно запустить тесты
~~~
phpunit
~~~

За рассылку почты и SMS отвечают два воркера, которые можно повесить к примеру на upstart
~~~
php bin/console rabbitmq:consumer -w email
~~~
~~~
php bin/console rabbitmq:consumer -w sms
~~~
