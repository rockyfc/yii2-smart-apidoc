<?php

namespace smart\apidoc\models;


use yii\base\Model;

class AttributeRules
{
    public $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public static function rules($model,$attribute)
    {
        $rules = new static($model);
        return $rules->getAttributeRules($attribute);
    }


    public function getDefaultComment()
    {
        return [
            'type' => null,//数据类型
            'required' => false,//是否必须
            'range' => null,//可选值
            'default' => null,//默认值
            'comment' => null
        ];
    }

    public function parseMutiAttributes($attribute)
    {
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

    public function parseFields()
    {
        $fields = $this->model->fields();
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

    public function parseExpand()
    {
        $fields = $this->model->extraFields();
        $expand = [];
        if ($fields) foreach ($fields as $k => $field) {
            if (is_array($field) or $field instanceof \Closure or $field === null) {
                $expand[] = $k;
            } else {
                $expand[] = "<a href='#".md5($field)."'>".$field."</a>";
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

    public function parseOther($attribute)
    {
        $docRules = $this->getDefaultComment();
        $docRules['comment'] = @$this->model->attributeLabels()[$attribute];
        //$validators = new ArrayObject();
        foreach ($this->model->rules() as $rule) {
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
                            $flag = in_array($this->model->getScenario(), $rule['on']);
                        }

                        if (is_string($rule['on'])) {
                            $arr = explode(',', $rule['on']);
                            $flag = in_array($this->model->getScenario(), $arr);
                        }

                        if ($flag) {
                            $docRules['required'] = true;
                        }


                    } else {


                        if ($this->model->getScenario() != 'default') {
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
                } elseif (in_array($rule[1], ['boolean', 'date', 'datetime', 'time', 'double', 'email', 'integer', 'number', 'string', 'array','file'])) {
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
     * 获取字段的介绍信息
     * @param $attribute
     * @return array
     */
    public function getAttributeRules($attribute)
    {
        //当这个属性是数组的时候，说明数组内部多个元素共同组成了一个属性，
        //目前这种情况只出现在了联合主键中，所以，当$attribute是数组的时候，可以认为是主键
        if (is_array($attribute)) {
           return $this->parseMutiAttributes($attribute);
        }


        //if (in_array($scenario, ['default']) and $attribute === 'fields') {
        if ($attribute === 'fields') {
            return $this->parseFields();
        }

        //if (in_array($scenario, ['default', 'view']) and $attribute === 'expand') {
        if ( $attribute === 'expand') {
           return $this->parseExpand();
        }


        return $this->parseOther($attribute);
    }


}