# Encode Url Rule for Yii2

This extension enables the creation of urls with parameters that can contain array of data.
This is achieved by flatenning the array into a json string and then encoded and added as query string.

[![Latest Stable Version](https://poser.pugx.org/locustv2/yii2-encode-url-rule/v/stable)](https://packagist.org/packages/locustv2/yii2-encode-url-rule)
[![Total Downloads](https://poser.pugx.org/locustv2/yii2-encode-url-rule/downloads)](https://packagist.org/packages/locustv2/yii2-encode-url-rule)
[![Latest Unstable Version](https://poser.pugx.org/locustv2/yii2-encode-url-rule/v/unstable)](https://packagist.org/packages/locustv2/yii2-encode-url-rule)
[![License](https://poser.pugx.org/locustv2/yii2-encode-url-rule/license)](https://packagist.org/packages/locustv2/yii2-encode-url-rule)


## Installation

The preferred way to install the library is through [composer](https://getcomposer.org/download/).

Either run
```
php composer.phar require --prefer-dist locustv2/yii2-encode-url-rule
```

or add
```json
{
    "require": {
        "locustv2/yii2-encode-url-rule": "~1.0.0"
    }
}
```
to your `composer.json` file.


### Configuring EncodeUrlRule
To use EncodeUrlRule, add this to your config.
```php
'UrlManager' => [
    'ruleConfig' => [
        'class' => 'weblement\components\EncodeUrlRule,
        'paramName' => 'enc',
        'autoEncodeParams' => [
            'page',
            'userId'
        ],
    ],
],
```

### Using EncodeUrlRule
When you are creating urls in yii, you can pass an array or parameters to the key `EncodeUrlRule::$paramName` that you set.
All parameters that are in `EncodeUrlRule::$autoEncodeParams` will also be encoded and assigned to the key in your url.
For example:
```php
// /site/url-test/?id=123&key1=value1&enc=a2V5Mj0lMjJ2YWx1ZTIlMjImdXNlcklkPTQ1NiZwYWdlPTI%253D
echo Url::to([
    '/site/url-test',
    'id' => 123,
    'key1' => 'value1',
    'userId' => 456,
    'page' => 2,
    'enc' => [
        'key2' => 'value2'
    ],
]);
```
In your controller action you can get the query parameters as follows
```php
public function actionUrlTest($id, $userId, $key2)
{
    var_dump($id); // 123
    var_dump($userId); // 456
    var_dump($key2); // value2
    var_dump(Yii::$app->request->get('key1')); // value1
    var_dump(Yii::$app->request->get('page')); // 2
    var_dump(Yii::$app->request->get()); // contains all get query  parameters including `enc`
}
```

### Further Examples

You are able to also pass arrays as follows:
```php
Url::to([
    '/site/url-test',
    'enc' => [
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
        ]
    ],
]);

Url::to([
    '/site/url-test',
    'enc' => [
        'user' => User::find()->asArray()->one(),
    ],
]);
```

However these parameters should will not work if you are trying to assign a controller action parameter.

```php
// will not work
public function actionUrlTest($user)
{
    var_dump($user);
}

// will work
public function actionUrlTest()
{
    var_dump(Yii::$app->request->get('user'));
}
```


## To do
 - Add unit tests

# Contributing
Feel free to send pull requests.


## License

For license information check the [LICENSE](LICENSE.md)-file.
