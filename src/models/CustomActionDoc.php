<?php

namespace smart\apidoc\models;

use smart\apidoc\exceptions\DocException;
use smart\apidoc\exceptions\NotFoundModelClassException;
use yii\base\Model;
use yii\rest\ActiveController;

/**
 * 获取常规写法的action接口，自定义的action接口写法如下
 *
 *
 * ```php
 *
 * Class DefaultController extends ActiveController{
 *
 *      //code...
 *
 *      public function actionTest(){
 *
 *      }
 *
 *      //code...
 * }
 * ```
 * @package smart\apidoc\models
 */
class CustomActionDoc extends ActionDoc
{
    public $strComment;

    /**
     * CustomActionDoc constructor.
     * @param ActiveController $controller
     * @param $actionId
     * @param $moduleId
     */
    public function __construct(ActiveController $controller, $actionId,$moduleId)
    {
        parent::__construct($controller, $actionId,$moduleId);
        $this->strComment = $this->getCustomActionComment();
    }


    private function getCustomActionComment()
    {
        $ref = new \ReflectionClass($this->controller);
        $methodStr = Tools::convertToMethodName($this->actionId);
        return $ref->getMethod($methodStr)->getDocComment();

    }


    /**
     * 获取action标题
     * @return string
     */
    public function getTitle()
    {
        $Comment = new Comment($this->strComment);
        $title = $Comment->getSummary();

        if (!empty($title)) {
            return $title;
        }

        return $this->actionId;
    }

    /**
     * 获取action详情
     * @return string
     */
    public function getDescription()
    {
        $Comment = new Comment($this->strComment);
        $desc = $Comment->getDescription();

        if (!empty($desc)) {
            return $desc;
        }
    }


    /**
     * 判断当前action是否已过期，
     * @return bool
     */
    public function isDeprecated(){
        if(empty($this->strComment)){
            return false;
        }
        $Comment = new Comment($this->strComment);
        return (bool)$Comment->hasTag('deprecated');
    }

    /**
     * 判断当前action是否已禁用
     * @return bool
     */
    public function isDisabled(){
        if(empty($this->strComment)){
            return false;
        }
        $Comment = new Comment($this->strComment);
        return (bool)$Comment->hasTag('disabled');
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

        $params = array_keys($this->getQueryInput());

        if ($params) {
            foreach ($params as $arg) {
                $tmp[] = $arg.'={xx}';
            }
            $route .= '?'.implode('&',$tmp);

        }

        return $route;
    }



    /**
     * 获取http 请求实体内的输入参数
     * @return array
     */
    public function getInput()
    {

        if(!$this->hasInputBody()){
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
                $input[$attribute] = $this->getAttributeRules($attribute);
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
     * @return Fields[]
     */
    public function getQueryInput()
    {
        $input = [];

        $Comment = new Comment($this->strComment);
        $columns = $Comment->getParamTag();

        //print_r($columns);
        if (!$columns) {
            return [];
        }

        /** @var Fields $field */
        foreach ($columns as $field) {
            $input[$field->variableName] = (Array)$field;
        }
        //print_r($input);
        return $input;
    }



    /**
     * @return mixed
     * @throws DocException
     */
    protected function getModel()
    {
        $scenario = $this->getScenario();

        $modelClass = $this->parseComment($this->strComment, '@modelClass');

        if (!empty($modelClass)) {
            $modelClass = trim($modelClass[0]);
        } else {
            $modelClass = $this->controller->modelClass;
        }

        if (empty($modelClass)) {
            throw new DocException($this->getControllerName().'::$modelClass'.' 不能为空。');
        }

        if( !class_exists($modelClass) ){
            throw new DocException($this->getControllerName().'::$modelClass的值'.$modelClass.'没有找到。');
        }

        if ($modelClass) {
            $model = new $modelClass();
        }

        if(!($model instanceof Model)){
            throw new DocException($this->getControllerName().'::$modelClass的值'.$modelClass.'必须继承自 yii\base\Model类，否则不能生成文档。');
        }

        $model->setScenario($scenario);

        return $model;

    }


    /**
     * 获取当前接口的场景，如果想自定义场景，请在接口的注释里添加上@scenario标签，告诉文档系统，当前接口所使用的场景。
     * 如果用户没有写这个标签的话，取 yii\base\Model::SCENARIO_DEFAULT 的默认场景值
     * @return array|string
     */
    protected function getScenario()
    {
        $scenario = $this->parseComment($this->strComment, '@scenario');

        if (empty($scenario)) {
            return Model::SCENARIO_DEFAULT;
        }
        $scenario = trim($scenario[0]);
        return $scenario;
    }

}






