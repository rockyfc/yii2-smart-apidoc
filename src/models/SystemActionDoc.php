<?php

namespace smart\apidoc\models;

use smart\apidoc\exceptions\DocException;
use yii\base\Model;
use yii\rest\ActiveController;

/**
 * 获取系统接口文档，系统接口即Controller中得actions函数中包含的接口
 * @package smart\apidoc\models
 */
class SystemActionDoc extends ActionDoc
{

    public function __construct(ActiveController $controller, $actionId, $moduleId)
    {
        parent::__construct($controller, $actionId, $moduleId);
    }

    /**
     * 获取当前action的文档内容
     * @return array
     */
    public function doc()
    {
        $model = $this->getModel();
        if (!$model) {
            return null;
        }
        return parent::doc();

    }


    /**
     * 获取接口标题
     * @return string
     */
    public function getTitle()
    {
        $conf = ['GET' => '获取', 'POST' => '', 'PUT' => '更新', 'PATCH' => '更新', 'DELETE' => '删除', 'HEAD' => '获取', 'OPTIONS' => 'OPTIONS'];
        //$conf = ['get' => '获取', 'post' => '', 'delete' => ''];
        $act = ['index' => '列表', 'view' => '详情', 'create' => '创建', 'update' => '更新', 'delete' => '删除'];

        $title = '';
        $method = $this->getMethod();
        if (is_array($method)) {
            $title .= $conf[$method[0]];
        } elseif ($method) {
            $title .= $conf[$method];
        }
        if (isset($act[$this->actionId])) {
            $title .= $act[$this->actionId];
            return $title;
        }

        return $this->actionId;
    }

    /**
     * 获取action的说明文字
     * @return string
     */
    public function getDescription()
    {
        if ($this->isSystemAction() and method_exists($this->controller, 'systemActionDescription')) {
            $desc = $this->controller->systemActionDescription();
            if (isset($desc[$this->actionId])) {
                return $desc[$this->actionId];
            }
        }

        return $this->getTitle();
    }


    /**
     * @return bool
     */
    public function isDeprecated()
    {
        // TODO: Implement isDeprecated() method.
        return false;
    }


    /**
     * @return bool
     */
    public function isDisabled()
    {
        // TODO: Implement isDisabled() method.
        return false;
    }


    /**
     * 获取action路由
     * @return string
     */
    public function getRoute()
    {

        if (\Yii::$app->id == $this->moduleId) {
            $route = ($this->controller->id . '/' . $this->actionId . '');
        } else {
            $route = ($this->controller->getUniqueId() . '/' . $this->actionId . '');
        }

        if ($this->isUpdate() or $this->isDelete() or $this->isView()) {
            $route .= '?id={xx}';
        }

        return $route;

    }

    /**
     * 获取http 请求实体内的输入参数
     * @return array
     */
    public function getInput()
    {

        if (!$this->hasInputBody()) {
            return [];
        }

        $model = $this->getModel();
        $scenarios = $model->scenarios();
        if (!isset($scenarios[$model->scenario])) {
            return [];
        }
        //return $scenarios[$model->scenario];
        $attributes = $scenarios[$model->scenario];

        $input = [];
        if (is_array($attributes))
            foreach ($attributes as $k => $attribute) {

                if (!is_int($k)) {
                    $index = $k;
                } elseif ($this->getModel()->getScenario() === 'default') {
                    $index = 'filter[' . $attribute . ']';
                } else {
                    $index = $attribute;
                }

                $input[$index] = $this->getAttributeRules($attribute);

            }

        if ($this->getModel()->getScenario() === 'default') {
            $input['fields'] = $this->getAttributeRules('fields');

            $expand = $this->getAttributeRules('expand');
            if ($expand['range']) {
                $input['expand'] = $expand;

            }
        }

        return $input;
    }

    /**
     * 获取query参数
     * @return array
     */
    public function getQueryInput()
    {
        $model = $this->getModel();
        $scenarios = $model->scenarios();

        $input = [];

        if ($this->isIndex() or $this->isOther()) {

            if (isset($scenarios[$model->scenario])) {

                $attributes = $scenarios[$model->scenario];

                if (is_array($attributes))
                    foreach ($attributes as $k => $attribute) {
                        $index = 'filter[' . $attribute . ']';
                        $input[$index] = $this->getAttributeRules($attribute);
                        continue;
                    }
            }
        }

        if ($this->isDelete() or $this->isUpdate() or $this->isView()) {
            $attribute = $this->getPk();
            $input['id'] = $this->getAttributeRules($attribute);
        }

        //如果当前接口是系统的index接口或者view接口，则给出fields参数显示在文档中
        if ($this->isIndex() or $this->isView()) {
            $input['fields'] = $this->getAttributeRules('fields');
        }

        //如果当前接口是系统的index接口或者view接口，则给出expand参数显示在文档中
        if ($this->isIndex() or $this->isView()) {
            $expand = $this->getAttributeRules('expand');
            if ($expand['range']) {
                $input['expand'] = $expand;
            }
        }


        if ($this->isOther()) {
            //$input['fields'] = $this->getAttributeRules('fields');
            $expand = $this->getAttributeRules('expand');
            if ($expand['range']) {
                $input['expand'] = $expand;
            }
        }

        //}

        return $input;
    }

    /**
     * @return mixed
     * @throws DocException
     */
    protected function getModel()
    {
        $scenario = $this->getScenario();
        $actions = $this->controller->actions();
        if (isset($actions[$this->actionId])
            and isset($actions[$this->actionId]['modelClass'])
            and !empty($actions[$this->actionId]['modelClass'])) {

            $modelClass = $actions[$this->actionId]['modelClass'];

            if (!class_exists($modelClass)) {
                throw new DocException('没有找到' . $this->getControllerName() . '::$modelClass中设置的' . $modelClass . '。');
            }

            if ($modelClass) {
                $model = new $modelClass();
            }

            if (!($model instanceof Model)) {
                throw new DocException($this->getControllerName() . '::$modelClass 中设置的' . $modelClass . '类，必须是 yii\base\Model的子类，否则不能生成文档。');
            }

            $model->setScenario($scenario);

            return $model;
        }

        /*$modelClass = $this->actionId . 'ModelClass';
        if (!isset($actions[$this->actionId])
            and isset($this->controller->$modelClass)
            and !empty($this->controller->$modelClass)) {
            return (new $this->controller->$modelClass([
                'scenario' => $scenario
            ]));
        }*/

        /*if (!isset($actions[$this->actionId])
            and isset($this->controller->modelClass)
            and !empty($this->controller->modelClass)) {
            return (new $this->controller->modelClass([
                'scenario' => $scenario
            ]));
        }*/

    }

    /**
     * @return string[]
     */
    public function getPk()
    {
        $model = $this->getModel();
        return $model::primaryKey();

    }


    /**
     * 获取当前action所使用的场景
     * @return string
     */
    protected function getScenario()
    {

        $actions = $this->controller->actions();
        if (isset($actions[$this->actionId])
            and isset($actions[$this->actionId]['scenario'])
            and !empty($actions[$this->actionId]['scenario'])) {

            return $actions[$this->actionId]['scenario'];
        }

        if ($this->isIndex() or $this->isView() or $this->isDelete()) {
            return 'default';
        } else {
            return $this->actionId;
        }

    }


}






