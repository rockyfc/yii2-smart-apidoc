<?php

namespace smart\apidoc\models;

use smart\apidoc\exceptions\DocException;


/**
 * 生成接口文档的主要逻辑类
 * @package smart\apidoc\models
 */
class Doc
{
    /**
     * 生成接口文档的时候，需要屏蔽掉的moduleId
     * @var array
     */
    public $skipModulesId = ['debug', 'gii', 'doc'];

    /**
     * @var array 所有的moduleId
     */
    public $moduleIds;

    /**
     * @var array 记录文档中的异常或错误
     */
    public $error = [];

    /**
     * 获取全部文档信息
     * @return array
     */
    public function start()
    {
        $controllers = $this->getControllers();

        $data = [];
        foreach ($controllers as $moduleId => $items) {
            $list[] = $moduleId;
            if (!$items) {
                continue;
            }
            foreach ($items as $controllerClass) {

                try {

                    $controllerDoc = $this->getControllerDoc($controllerClass, $moduleId);
                    if ($moduleId == \Yii::$app->id) {
                        $str = '';
                    } else {
                        $str = $moduleId . '/';
                    }
                    $data[$str . $controllerDoc->controller->id] = $controllerDoc->doc();

                } catch (DocException $e) {
                    $this->error[] = $e->getMessage();
                }
            }
        }
        return $data;
    }


    /**
     * 获取当前系统所有的moduleId
     * @return array
     */
    public function getModuleIds()
    {
        if ($this->moduleIds !== null) {
            return $this->moduleIds;
        }

        $this->moduleIds = [\Yii::$app->id];
        foreach (\Yii::$app->getModules() as $moduleId => $model) {
            if (in_array($moduleId, $this->skipModulesId)) {
                continue;
            }
            $this->moduleIds[] = $moduleId;
        }
        return $this->moduleIds;
    }

    /**
     * 获取当前系统所有模块下的所有Controller类
     * @return array
     */
    public function getControllers()
    {
        $controllers = [];
        $moduleIds = $this->getModuleIds();
        foreach ($moduleIds as $moduleId) {
            $controllers[$moduleId] = $this->getControllersByModuleId($moduleId);
        }
        return $controllers;
    }

    /**
     *
     * 获取某个模块下的所有Controller类
     * @param $moduleId
     * @return array
     */
    public function getControllersByModuleId($moduleId)
    {

        $controllersList = [];
        if (\Yii::$app->id == $moduleId) {
            $Module = \Yii::$app;
        } else {
            $Module = \Yii::$app->getModule($moduleId, true);
        }

        if (!$Module) {
            return $controllersList;
        }

        $namespace = $Module->controllerNamespace;
        $path = $Module->getControllerPath();
        $controllers = glob($path . '/*Controller.php');

        if ($controllers)
            foreach ($controllers as $ctrl) {
                $ctrlName = basename(trim($ctrl, '.php'));
                $controllersList[] = $namespace . '\\' . $ctrlName;
            }
        return $controllersList;

    }

    /**
     * 获取指定Controller的注释信息
     * @param $controllerClass
     * @param $moduleId
     * @return ControllerDoc
     */
    public function getControllerDoc($controllerClass, $moduleId)
    {
        $controller = $this->createControllerInstance($controllerClass, $moduleId);
        return new ControllerDoc($controller, $moduleId);
    }


    /**
     * 创建一个controller实例
     * @param $controllerClass
     * @param $moduleId
     * @return object
     */
    private function createControllerInstance($controllerClass, $moduleId)
    {
        $classArr = explode('\\', $controllerClass);
        $ctrlName = end($classArr);
        $controllerId = Tools::convertToControllerId($ctrlName);

        return \Yii::createObject(
            $controllerClass,
            [$controllerId, \Yii::$app->getModule($moduleId, true)]
        );
    }


    /**
     * 获取model对象列表
     * @param $namespace
     * @param null $modelPath
     * @return array
     * @throws DocException
     */
    public function getAllModelsDoc($namespace, $modelPath = null)
    {
        if (empty($modelPath)) {
            $modelPath = \Yii::getAlias('@app') . '/common/models';
        }

        if (!is_dir($modelPath)) {
            throw new DocException($modelPath . '不存在');
        }

        $modelsDoc = [];

        $files = glob($modelPath . '/*.php');
        if ($files) {
            foreach ($files as $classFile) {
                $basename = basename($classFile);
                $basename = substr($basename, 0, -4);
                $model = $namespace . '\\' . $basename;
                $model = new $model;
                $modelsDoc[] = [
                    'title' => '',
                    'name' => lcfirst($basename),
                    'comment' => ModelDoc::comment($model)
                ];
            }
        }
        return $modelsDoc;
    }


}