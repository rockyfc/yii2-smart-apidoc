<?php

namespace smart\apidoc\models;

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
class NormalActionDoc extends ActionDoc
{
    public $strComment;

    /**
     * NormalActionDoc constructor.
     * @param ActiveController $controller
     * @param $actionId
     * @param $strComment
     */
    public function __construct(ActiveController $controller, $actionId, $strComment)
    {
        parent::__construct($controller, $actionId);
        $this->strComment = $strComment;
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
     * 获取action参数
     * @return Fields[]
     */
/*    public function getInput()
    {
        $input = [];
        $columns = $this->parseComment($this->strComment, '@input');
        if ($columns)
            foreach ($columns as $colInfo) {

                $param = explode(' ', $colInfo);

                $field = new Fields();
                $field->type = @$param[0];
                $field->comment = @$param[2];
                $field->required = (bool)preg_match('/required/', $colInfo);

                preg_match('/range\(([\s\S]*?)\)/', $colInfo, $rs);
                $field->range = (Array)@$rs[1];

                $name = trim($param[1], '$');
                $input[$name] = (Array)$field;
            }
        return $input;
    }*/

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

        //if ($this->getModel()->getScenario() === 'default') {
            $input['fields'] = $this->getAttributeRules('fields');

            $expand = $this->getAttributeRules('expand');
            if ($expand['range']) {
                $input['expand'] = $expand;
            }
        //}

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
     * 获取返回值
     * @return Fields[]
     */
    /*public function getOutput()
    {
        $output = [];
        $columns = $this->parseComment($this->strComment, '@output');
        if ($columns)
            foreach ($columns as $colInfo) {

                $param = explode(' ', $colInfo);

                $field = new Fields();
                $field->type = @$param[0];
                $field->comment = @$param[2];
                $field->required = (bool)preg_match('/required/', $colInfo);

                preg_match('/range\(([\s\S]*?)\)/', $colInfo, $rs);
                $field->range = (Array)@$rs[1];

                $name = trim($param[1], '$');
                $output[$name] = (Array)$field;
            }
        return $output;
    }*/


    /**
     * @return mixed
     * @throws \Exception
     */
    protected function getModel()
    {
        $scenario = $this->getScenario();

        $modelClass = $this->parseComment($this->strComment, '@modelClass');

        if (empty($modelClass)) {
            throw new NotFoundModelClassException('没有找到' . $this->getActionName() . '接口所使用的modelClass，请检查该接口注释里面是否标注了@modelClass标签');
        }
        $modelClass = trim($modelClass[0]);
        if ($modelClass) {
            return new $modelClass([
                'scenario' => $scenario
            ]);
        }

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






