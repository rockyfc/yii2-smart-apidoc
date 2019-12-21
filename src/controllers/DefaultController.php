<?php

namespace smart\apidoc\controllers;

use smart\apidoc\models\ApiDoc;
use yii\web\Controller;

/**
 * Default controller for the `v1` module
 */
class DefaultController extends Controller
{
    public $modelClass = '';

    public $layout = 'main';


    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
    }


    public function actionIndex()
    {
        /** @var $module \smart\apidoc\Module */
        $module = $this->module;

        $doc = new \smart\apidoc\models\Doc;

        $doc->skipModulesId = $module->skipModulesId;

        //获取用户定义的README.md
        $readmeFileContent = '';
        $readmeFile = \Yii::getAlias($module->readMeFilePath);
        if ($readmeFile and file_exists($readmeFile)) {
            $readmeFileContent = file_get_contents($readmeFile);
        }

        //文档插件内置的README.md文件
        $smartReadmeFile = '';
        if($module->loadSmartReadmeFile){
            $smartReadmeFile = file_get_contents(dirname(__FILE__).'/../Smart_README.md');
        }

        //获取实体对象列表
        $modelsDoc = [];
        if (!empty($module->entitiesNamespace)) {
            $params = explode('\\', $module->entitiesNamespace);
            array_shift($params);
            $entitiesPath = \Yii::getAlias('@app') . '/' . implode('/', $params);
            $modelsDoc = $doc->getAllModelsDoc($module->entitiesNamespace, $entitiesPath);
        }


        return $this->render('index.php', [
            'smartReadmeFile' => $smartReadmeFile,
            'readmeFileContent' => $readmeFileContent,
            'apiList' => $doc->start(),
            'errors' => $doc->error,
            'modelsDoc' => $modelsDoc,
        ]);
    }


}
