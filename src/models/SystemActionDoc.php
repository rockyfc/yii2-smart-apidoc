<?php

namespace smart\apidoc\models;

use yii\rest\ActiveController;

/**
 * 获取系统接口文档，系统接口即Controller中得actions函数中包含的接口
 * @package smart\apidoc\models
 */
class SystemActionDoc extends ActionDoc
{
    public $strComment;

    public function __construct(ActiveController $controller, $actionId, $strComment = '')
    {
        parent::__construct($controller, $actionId);
        $this->strComment = $strComment;
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
        $conf = ['GET' => '获取','POST'=>'', 'PUT' => '更新', 'PATCH' => '更新', 'DELETE' => '删除', 'HEAD' => '获取', 'OPTIONS' => 'OPTIONS'];
        //$conf = ['get' => '获取', 'post' => '', 'delete' => ''];
        $act = ['index' => '列表', 'view' => '详情', 'create' => '创建', 'update' => '更新', 'delete' => '删除'];

        $title = '';
        $method = $this->getMethod();
        if (is_array($method)) {
            $title .= $conf[$method[0]];
        }elseif ($method) {
            $title .= $conf[$method];
        }
        if (isset($act[$this->actionId])) {
            $title .= $act[$this->actionId];
            return $title;
        }

        //对于自定义函数，获取注释内的title标签内容
        $arr = $this->parseComment($this->strComment, '@title');
        $str = implode(',', $arr);

        if ($str) {
            return $str;
        }


        return $this->actionId;
    }

    /**
     * 获取action的说明文字
     * @return string
     */
    public function getDescription()
    {
        if ($this->isSystemAction() and method_exists($this->controller,'systemActionDescription')) {
            $desc = $this->controller->systemActionDescription();
            if (isset($desc[$this->actionId])) {
                return $desc[$this->actionId];
            }
        }

        return $this->getTitle();
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
     * @return null|\yii\db\ActiveRecord
     */
    private function getModel()
    {
        $scenario = $this->getScenario();
        $actions = $this->controller->actions();
        if (isset($actions[$this->actionId])
            and isset($actions[$this->actionId]['modelClass'])
            and !empty($actions[$this->actionId]['modelClass'])) {
            return (new $actions[$this->actionId]['modelClass']([
                'scenario' => $scenario
            ]));
        }

        $modelClass = $this->actionId . 'ModelClass';
        if (!isset($actions[$this->actionId])
            and isset($this->controller->$modelClass)
            and !empty($this->controller->$modelClass)) {
            return (new $this->controller->$modelClass([
                'scenario' => $scenario
            ]));
        }

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
    private function getScenario()
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

    /**
     * 获取输出参数
     * @return array
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


}






