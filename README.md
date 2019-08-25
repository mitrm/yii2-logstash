Отправка данных в Logstash.
==========================================================================

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist mitrm/yii2-logstash "*"
```

or add

```
"mitrm/yii2-logstash": "*"
```

to the require section of your `composer.json` file.


Usage
-----

В components добавить

Для отправки через tcp
```php
        'logstash' => [
            'class' => \mitrm\logstash\LogstashSend::class,
            'config' => [
                'class' => \mitrm\logstash\transport\TcpTransport::class,
                'socket' => 'tcp://localhost:5000'
            ],
        ],
```

Для отправки через http
```php
        'logstash' => [
            'class' => \mitrm\logstash\LogstashSend::class,
            'config' => [
                'class' => \mitrm\logstash\transport\HttpTransport::class,
                'port' => 5001,
                'host' => 'http://localhost'
            ],
        ],
```

Для отправки данных 

```php
Yii::$app->logstash->sendLog(['event' => 'orderPay', 'userId' => Yii::$app->user->id]);

```

Для отправки логов Yii в logstash

```php
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \mitrm\logstash\LogstashTarget::class,
                    'levels' => ['error', 'warning'],
                    'logVars' => ['_GET', '_POST', '_SESSION', '_SERVER'],
                    'clientOptions' => [
                        'release' => $params['release_app'] ?? null,
                    ],
                    'isLogUser' => true, // Добавить в лог ID пользователя
                    'isLogContext' => false, 
                    'extraCallback' => function ($message, $extra) {
                        $extra['app_id'] = Yii::$app->id;
                        return $extra;
                    },
                    'except' => ['order'],
                ],
            ],
        ],

```