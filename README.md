# yii2-smart-apidoc
yii2 api项目根据逻辑生成文档

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist rockyfc/yii2-smart-apidoc "*"
```

or add

```
"rockyfc/yii2-smart-apidoc": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your config file by  :

```php
'modules' => [
        code ...
        
        'doc' => [
            'class' => 'smart\apidoc\Module',
        ],
        
        code ...
    ],
```