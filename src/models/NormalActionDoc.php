<?php

namespace smart\apidoc\models;

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
    public function getInput()
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
    public function getOutput()
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
    }


}






