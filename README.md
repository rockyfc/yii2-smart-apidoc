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
        
        //是否载入yii2-smart-apidoc文档提供的README.md文件，默认是true
        'loadSmartReadmeFile' => true,
        
        //当前api项目中的自定义的README.md文件绝对路径
        //'readMeFilePath' => '@webroot/../README.md',
        
        
        //要屏蔽掉的Module模块
        'skipModulesId' => ['debug', 'gii', 'doc'],
        
        //数据对象的命名空间。
        //对应着文档中的对象列表，这个配置关乎着是否在接口文档中体现对象列表
        'entitiesNamespace' => [
            //'api\common\models'
        ]
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
        
                    //是否载入yii2-smart-apidoc文档提供的README.md文件，默认是true
                    'loadSmartReadmeFile' => true,
                            
                    //当前api项目中的自定义的README.md文件绝对路径
                    //'readMeFilePath' => '@webroot/../README.md',
        
                    //要屏蔽掉的Module模块
                    'skipModulesId' => ['debug', 'gii','doc'],
        
                    //数据对象的命名空间。
                    //对应着文档中的对象列表，这个配置关乎着是否在接口文档中体现对象列表
                    'entitiesNamespace' => [
                        //'api\common\models'
                    ]
                ],
        
        //other code ...
    ],
```


生成文档要求你的接口代码符合以下几个条件：

1. 推荐你尽可能使用Yii2框架在`yii\rest\ActiveController::actions()`提供的系统接口 index、view、create、delete、update接口;
2. 或者你也可以通过继承IndexAction、ViewAction、CreateAction、UpdateAction、DeleteAction类来实现自定义的Action类，并在actions函数里面引用他。
3. 该接口文档推荐按照Module的方式来书写接口。
4. 你也可以在`yii\rest\ActiveController`的子类里面自定义接口。但是需要在接口的注释里面添加一个`@modelClass`标签来告诉文档系统，你自己写的接口引用的是哪个model类。就像以下代码：

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
注意：@modelClass 请写一个带有完整命名空间的model类。例如：\api\modules\v1\models\TestModel
当然，你也可以不写`@modelClass`标签，如果不写的话，会取当前Controller类中设置的`$modelClass`。
并且，在接口的注释里面，你还应该添加一个`@scenario`标签，来告诉文档，当前接口用的是什么场景，如果不写的话，取`yii\base\Model::SCENARIO_DEFAULT`设置的默认场景。
```php
class ExampleController extends yii\rest\ActiveController{
        
       //other code ...
    
       /**
        * 我是一个接口示例。
        *
        * @modelClass \api\modules\v1\models\TestModel 
        * @scenario test
        * @return mixed
        */
       public function actionTest(){
           $model = new \api\modules\v1\models\TestModel([
               'scenario' => 'test'//设置场景
           ]);
           $model->load(\Yii::$app->request->post(),'');
           $model->save(true);
           return $model;
       }
    
    //other code ...
    
}
```
如果某一个接口类或者接口类中某一个action不想在接口文档中呈现，请在类的注释中添加"@disabled"标签。比如：

```php

/**
 * 我是一个测试接口类
 * @disabled
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
    * 我是一个测试接口
    * @disabled
    */
    public function actionTest(){
    
    }
    
    //code ...
}

```

请看下面的完整示例：
```php

    /**
     * 我是一个接口示例
     *
     * 这样的接口写法虽不推荐，但是日常开发过程中，不可避免的会采用这种写法，所以，如果遇到开发者自己开发action接口的话，注释内容便成了生成
     * 文档的关键。所以，对于一个接口请求，定位资源的url里面如果有参数的话，请在action函数添加上相应的参数，并且参数的注释内容按照如下格
     * 式定义即可：
     * range(1,2)表示status字段的可选值；
     * default(1)表示如果用户不传参数的话，status字段取默认值1。
     * required关键字表示此字段必填。
     * 调用此接口的url可能会这样写 "http://xxx.com/v1/activity/edit-data?id=xx&status=1"
     *
     * @param int $status 状态值，range(1,2)，default(1)
     * @param int $userId 用户ID，required
     *
     * @modelClass \api\modules\v1\models\ActivitySearch
     * @scenario edit
     *
     * @return null|\api\modules\v1\models\ActivitySearch
     */
    public function actionEditData(int $status = 1, int $userId)
    {
        $model = \api\modules\v1\models\ActivitySearch::findOne([
            'status' => $status,
            'user_id' => $userId
        ]);
        $model->setScenario('edit'); //如果不设置，默认使用default场景
        $model->load(\Yii::$app->request->post(), '');
        $model->save(true);
        return $model;
    }

```

对于一些get请求，如果用户需要输入一些查询条件才能获取到数据的话，为了保持与actions方法中定义的index接口或者view接口的url相一致的写法，可以在注释中添加@options标签。
比如自定义一个list接口
```php
  /**
     * 我是一个接口示例
     *
     * 为了让自定义的接口也支持fields、filter、expand写法，可以在注释中添加@scenario标签，标签可选值为fields、filter、expand
     * 调用此接口的url可能会这样写 "http://api.localhost/content/index?fields=username,id&filter[status]=1&filter[user_id]=xxx&expand=user,messages"
     *
     * @modelClass \api\modules\v1\models\ActivitySearch
     * @scenario list
     * @options filter,expand,fields
     * @return null|\api\modules\v1\models\ActivitySearch
     */
    public function actionList()
    {
        $model = new \api\modules\v1\models\ActivitySearch();
        $model->setScenario('list'); //如果不设置，默认使用default场景
        $model->load(\Yii::$app->request->post(), 'filter');
        $model->search();
        return $model;
    }

```

对于本文档的使用，以及yii2 restful接口的详细用法，可以参见视频：
Yii2 Restful Api视频：https://edu.csdn.net/course/detail/26600