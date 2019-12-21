<?php

namespace smart\apidoc\models;

use smart\apidoc\exceptions\DocException;
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
     * 当前controller的moduleId
     * @var
     */
    public $moduleId;

    /**
     * 当前actionId
     * @var string
     */
    public $actionId;

    /**
     * ActionDoc constructor.
     * @param ActiveController $Controller
     * @param $actionId
     * @param $moduleId
     */
    public function __construct(ActiveController $Controller, $actionId,$moduleId)
    {
        $this->controller = $Controller;
        $this->actionId = $actionId;
        $this->moduleId = $moduleId;
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
            'isDeprecated' => $this->isDeprecated(),
            'isDisabled' => $this->isDisabled(),
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
     * 判断当前action是否已过期
     * @return bool
     */
    abstract public function isDeprecated();

    /**
     * 判断当前action是否已禁用
     * @return bool
     */
    abstract public function isDisabled();



    /**
     * 获取action的请求方（将请求方式都转换成大写再返回）
     * @return string
     * @throws DocException
     */
    public function getMethod()
    {

        $verbs = $this->getVerbs();
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
        throw new DocException('没有获取到【' . $str . '】的请求方式，请检查' . $controllerName . '::verbs()函数');
    }


    /**
     * 获取接口的访问方式
     * @return array
     * @throws DocException
     */
    private function getVerbs()
    {
        $ref = new \ReflectionClass($this->controller);

        if( !$ref->getMethod('verbs')->isPublic() ){
            throw new DocException('请将'.$this->getControllerName().'::verbs()方法或者父类中的verbs()方法访问权限定义为public。');
        }

        return $this->controller->verbs();

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


    /**
     * 判断当前action是否是系统函数
     * @return bool
     */
    public function isSystemAction()
    {
        return (!$this->isOther());
    }


    /**
     * 判断当前请求是否是系统默认的update接口请求
     * @return bool
     */
    public function isUpdate()
    {
        $actionObject = $this->getActionInstance();
        $parentAction = \Yii::createObject(
            ['class' => 'yii\rest\UpdateAction', 'modelClass' => ''],
            ['update', $this->controller]);

        if ($actionObject instanceof $parentAction) {
            return true;
        }

    }

    /**
     * 判断当前请求是否是系统默认的create接口请求
     * @return bool
     */
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
     * 判断当前请求是否是系统默认的index接口请求
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
     * 判断当前请求是否是系统默认的view接口请求
     * @return bool
     */
    public function isView()
    {
        $actionObject = $this->getActionInstance();
        $parentAction = \Yii::createObject(['class' => 'yii\rest\ViewAction', 'modelClass' => ''], ['view', $this->controller]);

        if ($actionObject instanceof $parentAction) {
            return true;
        }

    }

    /**
     * 判断当前请求是否是系统默认的delete接口请求
     * @return bool
     */
    public function isDelete()
    {
        $actionObject = $this->getActionInstance();
        $parentAction = \Yii::createObject(['class' => 'yii\rest\DeleteAction', 'modelClass' => ''], ['delete', $this->controller]);

        if ($actionObject instanceof $parentAction) {
            return true;
        }

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


    /**
     * 获取request body里面的输入参数
     * @return mixed
     */
    abstract public function getInput();


    /**
     * 获取当前接口的url参数
     * @return mixed
     */
    abstract public function getQueryInput();

    /**
     * 获取当前接口的返回值
     * @return mixed
     */
    public function getOutput()
    {
        $model = $this->getModel();
        $attributes = $model->fields();

        if (!$attributes) {
            return [];
        }

        $output = [];
        foreach ($attributes as $k => $attribute) {
            if (is_array($attribute) or $attribute instanceof \Closure) {
                $attribute = $k;
            }
            $output[$attribute] = $this->getAttributeRules($attribute);
        }


        /*$relationModels = $model->extraFields();
        if (is_array($relationModels) and !empty($relationModels)) {
            foreach ($relationModels as $k => $relationModelAlias) {
                //$fields = $model->$relationModelAlias;
                $output[$relationModelAlias] =  [
                    'type' => $relationModelAlias,//数据类型
                    'required' => false,//是否必须
                    'range' => null,//可选值
                    'default' => null,//默认值
                    'comment' => $relationModelAlias.'对象'
                ];


            }
        }*/

        return $output;
    }



    /**
     * 获取字段的介绍信息
     * @param $attribute
     * @return array
     */
    public function getAttributeRules($attribute)
    {
        $rules = new AttributeRules($this->getModel());
        return $rules->getAttributeRules($attribute);


        $docRules = [
            'type' => null,//数据类型
            'required' => false,//是否必须
            'range' => null,//可选值
            'default' => null,//默认值
            'comment' => @$this->getModel()->attributeLabels()[$attribute]
        ];

        $scenario = $this->getModel()->getScenario();

        //当这个属性是数组的时候，说明数组内部多个元素共同组成了一个属性，
        //目前这种情况只出现在了联合主键中，所以，当$attribute是数组的时候，可以认为是主键
        if (is_array($attribute)) {
            $docRules = [
                'type' => 'mixed',//数据类型
                'required' => true,//是否必须
                'range' => null,//可选值
                'default' => null,//默认值
                'comment' => '数据对象的ID'
            ];

            if (count($attribute) == 1) {
                $docRules = $this->getAttributeRules($attribute[0]);
                $docRules['required'] = true;
                $docRules['comment'] = $attribute[0];
            } else {
                $docRules['comment'] = '联合主键，由';
                foreach ($attribute as $v) {
                    $docRules['comment'] .= $v . ' ';
                }
                $docRules['comment'] .= '的值组成，用逗号分隔。';
            }

            return $docRules;
        }


        //if (in_array($scenario, ['default']) and $attribute === 'fields') {
        if ($attribute === 'fields') {

            $fields = $this->getModel()->fields();
            if ($fields) foreach ($fields as $k => &$field) {
                if (is_array($field) or $field instanceof \Closure) {
                    $field = $k;
                }
            }
            return [
                'type' => 'string',//数据类型
                'required' => false,//是否必须
                'range' => $fields,//可选值
                'default' => '*',//默认值
                'comment' => '要查询的字段，推荐按需查询。可以多选，多个字段用逗号分隔。'
            ];
        }

        //if (in_array($scenario, ['default', 'view']) and $attribute === 'expand') {
        if ($attribute === 'expand') {

            $fields = $this->getModel()->extraFields();
            $expand = [];
            if ($fields) foreach ($fields as $k => $field) {
                if (is_array($field) or $field instanceof \Closure or $field === null) {
                    $expand[] = $k;
                } else {
                    $expand[] = $field;
                }
            }

            return [
                'type' => 'string',//数据类型
                'required' => false,//是否必须
                'range' => $expand,//可选值
                'default' => null,//默认值
                'comment' => '要关联查询的其他对象，推荐按需查询。可以多选，多个字段用逗号分隔。'
            ];
        }

        //$validators = new ArrayObject();
        foreach ($this->getModel()->rules() as $rule) {
            if (is_array($rule) && isset($rule[0], $rule[1])) { // attributes, validator type
                $rule[0] = (array)$rule[0];

                if (!in_array($attribute, $rule[0])) {
                    continue;
                }

                if ($rule[1] == 'default') {
                    $docRules['default'] = $rule['value'];
                } elseif ($rule[1] == 'required') {

                    if (isset($rule['on'])) {
                        $flag = false;
                        if (is_array($rule['on'])) {
                            $flag = in_array($this->getModel()->getScenario(), $rule['on']);
                        }

                        if (is_string($rule['on'])) {
                            $arr = explode(',', $rule['on']);
                            $flag = in_array($this->getModel()->getScenario(), $arr);
                        }

                        if ($flag) {
                            $docRules['required'] = true;
                        }


                    } else {


                        if ($this->getModel()->getScenario() != 'default') {
                            $docRules['required'] = true;
                        }

                    }
                } elseif ($rule[1] == 'in') {
                    $docRules['range'] = $rule['range'];
                } elseif (in_array($rule[1], ['string'])) {
                    $docRules['type'] = $rule[1];
                    if (isset($rule['max'])) {
                        $docRules['type'] .= " (" . $rule['max'] . ")";
                    }
                } elseif (in_array($rule[1], ['boolean', 'date', 'datetime', 'time', 'double', 'email', 'integer', 'number', 'string', 'array', 'file'])) {
                    $docRules['type'] = $rule[1];
                } elseif ($rule[1] == 'safe' or $rule[1] == 'filter') {
                    //
                } else {
                    $docRules['comment'] .= '<br/><br/>验证规则：' . $rule[1];
                }
            }
        }

        return $docRules;
    }



    /**
     * 获取当前接口的场景
     * @return mixed
     */
    abstract protected function getScenario();

    /**
     * 获取当前接口对应的Model类
     * @return mixed
     */
    abstract protected function getModel();


    /**
     * 获取Controller名称
     * @return string
     */
    public function getControllerName()
    {
        return  ucwords($this->controller->id) . 'Controller';
    }

    /**
     * 获取action名称
     * @return string
     */
    public function getActionName(){
        $controllerId = $this->controller->getUniqueId();
        return $controllerId . '/' . $this->actionId;
    }
}






