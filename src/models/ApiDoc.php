<?php

namespace smart\apidoc\models;


/**
 * 生成接口文档的主要逻辑类
 * @package smart\apidoc\models
 */
class ApiDoc
{
    /**
     * 生成接口文档的时候，需要屏蔽掉的module
     * @var array
     */
    public $skipModulesId = ['debug', 'gii'];

    static $data = [];


    /**
     * 获取api列表
     * @return array
     */
    public function getAllApiDoc()
    {
        $this->getControllers(function ($controller) {
            // if($controller instanceof ActiveController){
            $key = $controller->module->id . '/' . $controller->id;
            $ControllerDoc = new ControllerDoc($controller);
            $title = $ControllerDoc->title();
            if ((preg_match('/未使用/', $title) or preg_match('/已弃用/', $title))) {
                return;
            }
            static::$data[$key]['title'] = $title;
            static::$data[$key]['actionList'] = $ControllerDoc->doc();
            //}
            //static::$data[] = $controller->id;
        });



        return static::$data;

    }

    /**
     * 获取model对象列表
     * @param $namespace
     * @param null $modelPath
     * @return array
     * @throws \Exception
     */
    public function getAllModelsDoc($namespace, $modelPath = null)
    {
        if (empty($modelPath)) {
            $modelPath = \Yii::getAlias('@app') . '/common/models';
        }

        if(!is_dir($modelPath)){
            throw new \Exception($modelPath.'不存在');
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


    /**
     * 获取所有的controller
     * @param callable|null $callback
     */
    private function getControllers(callable $callback = null)
    {

        foreach (\Yii::$app->getModules() as $moduleId => $model) {
            if (in_array($moduleId, $this->skipModulesId)) {
                //echo $moduleId;
                continue;
            }

            $this->getModuleControllers($moduleId, $callback);

        }
    }


    /**
     * 根据moduleId获取相应得controllers
     * @param $moduleId
     * @param callable $callback
     */
    private function getModuleControllers($moduleId, callable $callback)
    {
        $list = [];
        if ($Module = \Yii::$app->getModule($moduleId, true)) {

            $namespace = $Module->controllerNamespace;


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