# yii2-smart-apidoc
yii2 api项目根据逻辑生成文档

Installation
------------

你可以通过 [composer](http://getcomposer.org/download/)进行安装.

执行安装：

```
php composer.phar require --prefer-dist rockyfc/yii2-smart-apidoc "*"
```

或者把如下代码加到composer.json文件中去，然后再执行安装

```
"rockyfc/yii2-smart-apidoc": "*"
```



使用方法
-----

你可以将如下代码放到main-local.php中去：

```php
if (!YII_ENV_TEST) {
 
    //other code ...

    $config['modules']['doc'] = [
        'class' => 'smart\apidoc\Module',

        //当前api项目中得README.md文件路径
        'readMeFile'=>'@webroot/../README.md',

        //要屏蔽掉的Module模块
        'skipModulesId' => ['debug', 'gii','doc'],

        //数据对象的命名空间。
        //对应着文档中的对象列表，这个配置关乎着是否在接口文档中体现对象列表
        'entitiesNamespace' => 'api\common\models'
    ];
    
    //other code ...
}

```


或者，你也可以把他直接放到main.php中去，但是不建议这么做，除非接口文档可以对外公开：

```php
'modules' => [
        //other code ...
        
        //文档模块的配置
        'doc' => [
                    'class' => 'smart\apidoc\Module',
        
                    //当前api项目中得README.md文件路径
                    'readMeFile'=>'@webroot/../README.md',
        
                    //要屏蔽掉的Module模块
                    'skipModulesId' => ['debug', 'gii','doc'],
        
                    //数据对象的命名空间。
                    //对应着文档中的对象列表，这个配置关乎着是否在接口文档中体现对象列表
                    'entitiesNamespace' => 'api\common\models'
                ],
        
        //other code ...
    ],
```


生成文档要求你的接口代码符合以下几个条件：

1. 推荐你尽可能使用Yii2框架在``yii\rest\ActiveController::actions()``提供的系统接口 index、view、create、delete、update接口;
2. 或者你也可以通过继承IndexAction、ViewAction、CreateAction、UpdateAction、DeleteAction类来实现自定义的Action类，并在actions函数里面引用他。
3. 该接口文档推荐按照Module的方式来书写接口。
4. 你也可以在``yii\rest\ActiveController``的子类里面直接写接口。但是需要在注释里面添加一个``@modelClass``标签来告诉文档系统，你自己写的接口引用的是哪个model类。就像以下代码：

```php
class ExampleController extends yii\rest\ActiveController{
        
       //other code ...
    
       /**
        * 我是一个接口示例。
        *
        * @modelClass \api\modules\v1\models\TestModel
        * @return mixed
        */
       public function actionTest(){
           $model = new \api\modules\v1\models\TestModel;
           $model->load(\Yii::$app->request->post(),'');
           $model->save(true);
           return $model;
       }
    
    //other code ...
    
}
```

如果某一个接口类或者接口类中某一个action不想在接口文档中呈现，请在类的注释中添加"未使用"或者"已弃用"字样。比如：

```php

/**
 * 我是一个测试接口类 - 未使用
 */
class DefaultController extends Controller
{
    //code ...
}

```
或者
```php

/**
 * 我是一个测试接口类 - 已弃用
 */
class DefaultController extends Controller
{
    //code ...
}

```

或者

```php

/**
 * 我是一个测试接口类
 */
class DefaultController extends Controller
{
    //code ...
    
   /**
    * 我是一个测试接口 - 已弃用
    * 
    */
    public function actionTest(){
    
    }
    
    //code ...
}

```
或者

```php

/**
 * 我是一个测试接口类
 */
class DefaultController extends Controller
{
    //code ...
    
   /**
    * 我是一个测试接口 - 未使用
    * 
    */
    public function actionTest(){
    
    }
    
    //code ...
}

```