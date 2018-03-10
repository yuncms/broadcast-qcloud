# QCloud CMQ Topic for YUNCMS

适用于 YUNCMS 的 QCloud CMQ Topic。使用了DI实现的，可自行继承扩展。

[![Latest Stable Version](https://poser.pugx.org/yuncms/broadcast-qcloud/v/stable.png)](https://packagist.org/packages/yuncms/broadcast-qcloud)
[![Total Downloads](https://poser.pugx.org/yuncms/broadcast-qcloud/downloads.png)](https://packagist.org/packages/yuncms/broadcast-qcloud)
[![License](https://poser.pugx.org/yuncms/broadcast-qcloud/license.svg)](https://packagist.org/packages/yuncms/broadcast-qcloud)

Installation
------------

Next steps will guide you through the process of installing  using [composer](http://getcomposer.org/download/). Installation is a quick and easy three-step process.

### Step 1: Install component via composer

Either run

```
composer require --prefer-dist yuncms/broadcast-qcloud
```

or add

```json
"yuncms/broadcast-qcloud": "~1.0.0"
```

to the `require` section of your composer.json.

### Step 2: Configuring your application

Add following lines to your main configuration file:

```php
'components' => [
    'broadcast' => [
        'class' => 'yuncms\broadcast\qcloud\Broadcast',
        'endPoint' => 'http://cmq-topic-sh.api.qcloud.com/',
        'topicName' => 'abc',
        'secretId' => '',
        'secretKey' => '',
    ],
],
```

### Use 

使用方式非常简单

```php
$broadcast = Yii::$app->broadcast;


$res = $broadcast->send([
    'Key'=>'value',
    //etc ...
]);

var_dump($res);