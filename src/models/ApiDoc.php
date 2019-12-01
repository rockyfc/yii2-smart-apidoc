<?php

namespace smart\apidoc\models;


class ApiDoc
{
    public $skipModulesId = ['debug', 'gii'];

    static $data = [];

    public function getAllApiDoc()
    {
        $this->getControllers(function ($controller) {
            // if($controller instanceof ActiveController){
            $key = $controller->module->id . '/' . $controller->id;
            $ControllerDoc = new ControllerDoc($controller);
            $title = $ControllerDoc->title();
            if (preg_match('/未使用/', $title) or preg_match('/已弃用/', $title)) {
                return;
            }
            static::$data[$key]['title'] = $title;
            static::$data[$key]['actionList'] = $ControllerDoc->doc();
            //}
            //static::$data[] = $controller->id;
        });

        return static::$data;

    }

    public function getAllModelsDoc($namespace, $modelPath = null)
    {
        if ($modelPath == null) {
            $modelPath = \Yii::getAlias('@app') . '/common/models';
        }

        $modelsDoc = [];

        $files = glob($modelPath . '/*.php');
        if ($files) {
            foreach ($files as $classFile) {
                $basename = basename($classFile);
                $basename = substr($basename, 0, -4);
                $model = $namespace . '\\' .$basename ;
                $model = new $model;
                $modelsDoc[] = [
                    'title' => '',
                    'name'=>lcfirst($basename),
                    'comment' => ModelDoc::comment($model)
                ];
            }
        }
        return $modelsDoc;
    }

    private function getControllers(callable $callback = null)
    {

        foreach (\Yii::$app->getModules() as $moduleId => $model) {
            if (in_array($moduleId, $this->skipModulesId)) {
                //echo $moduleId;
                continue;
            }

            $this->getModuleControllers($moduleId, $callback);
            //$ctrls = $this->getModuleControllers($moduleId,$callback);
            //$this->_controllers = array_merge($this->_controllers, $ctrls);
            //$this->_modulesId[] = $moduleId;
        }
        //return $this->_controllers;
    }

    private function getModuleControllers($moduleId, callable $callback)
    {
        $list = [];
        if ($Module = \Yii::$app->getModule($moduleId, true)) {

            $namespace = $Module->controllerNamespace;
            if (!preg_match('/^app.*/', $namespace) and !preg_match('/^api.*/', $namespace)) {
                return [];
            }

            $path = $Module->getControllerPath();
            $controllers = glob($path . '/*Controller.php');

            if ($controllers)
                foreach ($controllers as $ctrl) {
                    $ctrlName = basename(trim($ctrl, '.php'));
                    $class = $namespace . '\\' . $ctrlName;

                    $controllerId = Tools::convertToControllerId($ctrlName);


                    $controller = \Yii::createObject(
                        $class,
                        [$controllerId, $Module]
                    );

                    call_user_func($callback, $controller);


                }

        }
        //return $list;
    }


}