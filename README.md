Symfony
=======

REQUIREMENTS
------------

Для работы необходим RabbitMQ + [rabbitmq_delayed_message_exchange](https://www.rabbitmq.com/blog/2015/04/16/scheduling-messages-with-rabbitmq/)

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