<?php

namespace smart\apidoc\models;

use yii\rest\ActiveController;
use yii\web\ServerErrorHttpException;

/**
 * 获取Action的注释内容，包括标题，详情，请求方式，路由，查询参数，输入参数，输出参数
 * @package smart\apidoc\models
 */
abstract class ActionDoc
{
    /**
     * 当前action所属的Controller
     * @var ActiveController
     */
    public $controller;

    /**
     * 当前actionId
     * @var string
     */
    public $actionId;

    /**
     * ActionDoc constructor.
     * @param ActiveController $Controller
     * @param $actionId
     */
    public function __construct(ActiveController $Controller, $actionId)
    {
        $this->controller = $Controller;
        $this->actionId = $actionId;
    }

    /**
     * 获取当前action的文档内容
     * @return array
     */
    public function doc()
    {
        return [
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'method' => $this->getMethod(),
            'route' => $this->getRoute(),
            'queryParams' => $this->getQueryInput(),
            'input' => $this->getInput(),
            'output' => $this->getOutput(),
        ];

    }

    /**
     * 获取action标题
     * @return string
     */
    abstract public function getTitle();

    /**
     * 获取action描述
     * @return string
     */
    abstract public function getDescription();

    /**
     * 获取action的请求方（将请求方式都转换成大写再返回）
     * @return string
     * @throws \Exception
     */
    public function getMethod()
    {

        $verbs = $this->controller->verbs();
        if (!isset($verbs[$this->actionId]) ) {
            return ['GET'];
        }

        if(is_string($verbs[$this->actionId])){
            return strtoupper($verbs[$this->actionId]);
        }

        if(is_array($verbs[$this->actionId])){
            foreach($verbs[$this->actionId] as &$val){
                $val = strtoupper($val);
            }
            return $verbs[$this->actionId];
        }


        $controllerId = $this->controller->getUniqueId();
        $controllerName = ucwords($this->controller->id) . 'Controller';
        $str = $controllerId . '/' . $this->actionId;
        throw new \Exception('没有获取到【' . $str . '】的请求方式，请检查' . $controllerName . '::verbs()函数');
    }


    /**
     * 检查当前请求是否是POST、PUT或者PATCH提交
     * @return bool
     */
    public function hasInputBody()
    {
        $method = $this->getMethod();
        if(in_array('POST',$method)
        OR in_array('PUT',$method)
        OR in_array('PATCH',$method) ){
            return true;
        }
    }

    /**
     * 获取action路由
     * @return string
     */
    public function getRoute()
    {
        $actions = $this->controller->actions();
        if (isset($actions[$this->actionId]) and isset($actions[$this->actionId]['class'])) {
            $class = $actions[$this->actionId]['class'];

            if (preg_match('/UpdateAction|DeleteAction|ViewAction/', $class)) {
                return ($this->controller->getUniqueId() . '/' . $this->actionId . '?id={xx}');
            }
        }

//        if($this->actionId=='update' or $this->actionId=='view'){
//            return ($this->controller->getUniqueId() . '/' . $this->actionId.'?id={xx}');
//        }
        return ($this->controller->getUniqueId() . '/' . $this->actionId . '');
    }

    /**
     * 获取一个action实例对象
     * @return null|object
     */
    private function getActionInstance()
    {
        $actions = $this->controller->actions();
        if (isset($actions[$this->actionId]) and isset($actions[$this->actionId]['class'])) {
            return \Yii::createObject($actions[$this->actionId], [$this->actionId, $this->controller]);
            //return $actions[$this->actionId]['class'];
            //return $actions[$this->actionId];
        }
        return null;
    }


    public function isUpdate()
    {
        $actionObject = $this->getActionInstance();
        $parentAction = \Yii::createObject(
            ['class' => 'yii\rest\UpdateAction', 'modelClass' => ''],
            ['update', $this->controller]);

        if ($actionObject instanceof $parentAction) {
            return true;
        }


        /*$class = $this->getActionInstance();

        if (preg_match('/UpdateAction/', $class)) {
            return true;
        }*/

    }

    public function isCreate()
    {
        $actionObject = $this->getActionInstance();
        $parentAction = \Yii::createObject(
            ['class' => 'yii\rest\CreateAction', 'modelClass' => ''],
            ['create', $this->controller]);

        if ($actionObject instanceof $parentAction) {
            return true;
        }

        /*if (preg_match('/CreateAction/', $class)) {
            return true;
        }*/

    }

    /**
     *
     * @return bool
     */
    public function isIndex()
    {
        $actionObject = $this->getActionInstance();//

        $parentAction = \Yii::createObject(['class' => 'yii\rest\IndexAction', 'modelClass' => ''], ['index', $this->controller]);
        if ($actionObject instanceof $parentAction) {
            return true;
        }

        /*if (preg_match('/IndexAction/', $class)) {
            return true;
        }*/

    }


    /**
     * 判断当前action是否是系统函数
     * @return bool
     */
    public function isSystemAction()
    {
        return (!$this->isOther());
    }

    /**
     *
     * @return bool
     */
    public function isView()
    {
        $actionObject = $this->getActionInstance();
        $parentAction = \Yii::createObject(['class' => 'yii\rest\ViewAction', 'modelClass' => ''], ['view', $this->controller]);

        if ($actionObject instanceof $parentAction) {
            return true;
        }


        /*$class = $this->getActionInstance();

        if (preg_match('/ViewAction/', $class)) {
            return true;
        }*/
    }

    public function isDelete()
    {
        $actionObject = $this->getActionInstance();
        $parentAction = \Yii::createObject(['class' => 'yii\rest\DeleteAction', 'modelClass' => ''], ['delete', $this->controller]);

        if ($actionObject instanceof $parentAction) {
            return true;
        }


        /*$class = $this->getActionInstance();

        if (preg_match('/DeleteAction/', $class)) {
            return true;
        }*/
    }

    /**
     * 如果是用户自定义的action，则返回true
     * @return bool
     */
    public function isOther()
    {
        if (!$this->isDelete()
            and !$this->isUpdate()
            and !$this->isView()
            and !$this->isIndex()
            and !$this->isUpdate()
            and !$this->isCreate()) {
            return true;
        }
    }

    public function parseComment($str, $tag)
    {

        $arr = explode("\n", $str);

        $items = [];
        foreach ($arr as $comment) {
            $pos = stripos($comment, $tag);
            if ($pos > 0) {
                $str = substr($comment, $pos + strlen($tag));
                /*$endPos = stripos($str, '*');
                if ($endPos <= 0) {
                    $endPos = stripos($str, ' ');
                }*/
                $str = substr($str, 0);
                $items[] = trim($str);
            }

        }
        return $items;
    }


    abstract public function getInput();

    abstract public function getQueryInput();

    abstract public function getOutput();
}






