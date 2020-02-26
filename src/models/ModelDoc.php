<?php

namespace smart\apidoc\models;

use yii\base\Model;
use yii\db\ActiveRecord;

class ModelDoc
{
    public $model;
    public function __construct(ActiveRecord $model)
    {
        $this->model = $model;

    }

    public static function getTitle(Model $model){
        $ref = new \ReflectionClass($model);
        $comment = (String)$ref->getDocComment();
        $comment = new Comment($comment);
        return $comment->getSummary();
    }

    public static function comment(Model $model)
    {
        $doc = new static($model);
        $fields = $doc->fields();
        $fields['expand'] = $doc->expand();
        return $fields;
    }

    public function expand()
    {
        return AttributeRules::rules($this->model,'expand');
    }

    public function fields()
    {
        $attributes = $this->model->fields();

        if (!$attributes) {
            return [];
        }

        $output = [];
        foreach ($attributes as $k => $attribute) {
            if (is_array($attribute) or $attribute instanceof \Closure) {
                $attribute = $k;
            }
            $output[$attribute] = AttributeRules::rules($this->model,$attribute);
            unset($output[$attribute]['required']);
        }

        /*$relationModels = $this->model->extraFields();
        if (is_array($relationModels) and !empty($relationModels)) {
            foreach ($relationModels as $k => $relationModelAlias) {


            }
        }*/

        return $output;
    }



}






