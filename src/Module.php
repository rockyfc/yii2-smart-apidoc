<?php

namespace smart\apidoc;
/**
 * v1 module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'smart\apidoc\controllers';

    /**
     * 要屏蔽的module
     * @var array
     */
    public $skipModulesId = ['debug', 'gii','doc'];

    /**
     * 对象列表的命名空间
     * @var string
     */
    public $entitiesNamespace = 'api\common\models';


    /**
     * @var string README.md文件的全路径
     */
    public $readMeFilePath;

    /**
     * @var bool 是否加载smart-api-doc组件的模板README.md文件
     */
    public $loadSmartReadmeFile = true;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}
