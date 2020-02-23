<?php

namespace smart\apidoc\models;

use smart\apidoc\exceptions\DocException;
use yii\base\Module;
use yii\rest\ActiveController;


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
     * @var array
     */
    public $extraControllers = [];

    /**
     * @var array 记录文档中的异常或错误
     */
    public $error = [];

    /**
     * 获取全部文档信息
     * @return array
     * @throws \ReflectionException
     * @throws \yii\base\InvalidConfigException
     */
    public function start()
    {
        $data = [];
        $controllers = $this->getControllers();
        foreach ($controllers as $controllers) {

            try {
                $controllerDoc = $this->getControllerDoc($controllers, $controllers->module);

                if ($controllerDoc->isDisabled()) {
                    continue;
                }

                $data[] = $controllerDoc->doc();

            } catch (DocException $e) {
                $this->error[] = $e->getMessage();
            }
        }
        return $data;
    }


    /**
     * @var ActiveController[]
     */
    private $_controllersList = [];


    /**
     * 获取当前系统所有模块下的所有Controller类
     * @return ActiveController[]
     * @throws \yii\base\InvalidConfigException
     */
    public function getControllers()
    {
        $this->loadModules(\Yii::$app);
        foreach ($this->_modules as $module) {
            $this->getControllersByModule($module);
        }
        return $this->_controllersList;
    }

    /**
     * @var Module[]
     */

    /**
     * @var Module[]
     */
    private $_modules = [];

    /**
     * 载入子模块
     * @param Module $module
     * @throws \yii\base\InvalidConfigException
     */
    public function loadModules(Module $module)
    {
        $modules = $module->getModules();
        if (!$modules) {
            return;
        }
        foreach ($modules as $moduleId => $subModule) {
            if (!($subModule instanceof Module)) {
                $subModule = \Yii::createObject($subModule, [$moduleId, $module]);
            }
            $this->_modules[] = $subModule;
            $this->loadModules($subModule);
        }
    }

    /**
     * 获取某个模块下的所有Controller类
     * @param Module $Module
     * @throws \yii\base\InvalidConfigException
     */
    public function getControllersByModule(Module $module)
    {
        $controllersList = [];
        $namespace = $module->controllerNamespace;
        $path = $module->getControllerPath();
        $controllers = glob($path . '/*Controller.php');

        if ($controllers)
            foreach ($controllers as $ctrl) {

                $ctrlName = basename(trim($ctrl, '.php'));

                $class = $namespace . '\\' . $ctrlName;

                $controllerInstance = $this->createControllerInstance($class, $module);

                if (!$controllerInstance or !($controllerInstance instanceof ActiveController)) {
                    continue;
                }

                $controllersList[] = $controllerInstance;
            }

        $controllersList = array_merge($controllersList, $this->getControllersByModuleControllerMap($module));

        $this->_controllersList = array_merge($controllersList, $this->_controllersList);        //return $controllersList;

    }

    /*public function getModule($moduleId)
    {
        if (\Yii::$app->id == $moduleId) {
            $Module = \Yii::$app;
        } else {
            $Module = \Yii::$app->getModule($moduleId, true);
        }
        return $Module;
    }*/

    /**
     * 获取module中指定controllerMap属性中的设置
     * @param Module $module
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function getControllersByModuleControllerMap(Module $module)
    {

        $controllersList = [];

        if ($module->controllerMap) foreach ($module->controllerMap as $controllerId => $class) {
            if (empty($class)) {
                continue;
            }

            if (is_array($class) and (!isset($class['class']) or empty($class['class']))) {
                continue;
            }

            if (is_array($class)) {
                $controllerClassName = $class['class'];
            }

            if (is_string($class)) {
                $controllerClassName = $class;
            }

            $controllerInstance = $this->createControllerInstance($controllerClassName, $module, $controllerId);
            if (!$controllerInstance or !($controllerInstance instanceof ActiveController)) {
                continue;
            }

            $controllersList[] = $controllerInstance;

        }

        return $controllersList;
    }

    /**
     * 获取指定Controller的注释信息
     * @param ActiveController $controller
     * @param Module $module
     * @return ControllerDoc
     * @throws \ReflectionException
     */
    public function getControllerDoc(ActiveController $controller, Module $module)
    {
        return new ControllerDoc($controller, $module);
    }

    private $_controllerInstances = [];

    /**
     * 创建一个controller实例
     * @param $controllerClass
     * @param Module $module
     * @param null $controllerId
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    private function createControllerInstance($controllerClass, Module $module, $controllerId = null)
    {
        $classArr = explode('\\', $controllerClass);
        $ctrlName = end($classArr);

        if (!$controllerId) {
            $controllerId = Tools::convertToControllerId($ctrlName);
        }

        $id = $controllerClass;
        if (isset($this->_controllerInstances[$id])) {
            return $this->_controllerInstances[$id];
        }

        $this->_controllerInstances[$id] = \Yii::createObject(
            $controllerClass,
            [$controllerId, $module]
        );

        return $this->_controllerInstances[$id];
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