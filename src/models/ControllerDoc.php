<?php

namespace smart\apidoc\models;

use yii\rest\ActiveController;

class ControllerDoc
{
    /**
     * @var ActiveController
     */
    public $controller;

    /**
     * @var 模块ID
     */
    public $moduleId;

    /**
     * @var string
     */
    public $comment;

    /**
     * ControllerDoc constructor.
     * @param ActiveController $controller
     */
    public function __construct(ActiveController $controller,$moduleId)
    {
        $this->controller = $controller;
        $this->moduleId = $moduleId;

        $ref = new \ReflectionClass($this->controller);
        $this->comment = (String)$ref->getDocComment();
    }


    /**
     * 获取当前controller的相关信息
     * @return array
     */
    public function doc()
    {
        return [
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'isDeprecated' => $this->isDeprecated(),
            'isDisabled' => $this->isDisabled(),
            'actions' => $this->getActions(),
            'actionsDoc' => $this->getActionsDoc(),
        ];
    }

    /**
     * 获取当前controller标题信息
     * @return null|string
     */
    public function getTitle()
    {
        if (empty($this->comment)) {
            return '';
        }
        $comment = new Comment($this->comment);
        return $comment->getSummary();
    }


    /**
     * 获取Controller描述信息
     * @return string
     */
    public function getDescription()
    {
        if (empty($this->comment)) {
            return '';
        }
        $Comment = new Comment($this->comment);
        return $Comment->getDescription();
    }

    /**
     * 判断当前controller是否已过期
     * @return bool
     */
    public function isDeprecated()
    {
        if (empty($this->comment)) {
            return false;
        }
        $Comment = new Comment($this->comment);
        return (bool)$Comment->hasTag('deprecated');
    }

    /**
     * 判断当前controller是否已禁用
     * @return bool
     */
    public function isDisabled()
    {
        if (empty($this->comment)) {
            return false;
        }
        $Comment = new Comment($this->comment);
        return (bool)$Comment->hasTag('disabled');
    }


    /**
     * 获取当前controller里面所有的接口
     * @return array
     */
    public function getActions()
    {
        return array_merge($this->getSysActions(), $this->getCustomActions());
    }


    /**
     * 获取当前controller里面所有的接口的文档信息
     * @return array
     */
    public function getActionsDoc()
    {
        return array_merge($this->getSysActionsDoc(), $this->getCustomActionsDoc());
    }

    /**
     * 获取系统接口文档，即：在Controller::actions()中定义的接口的接口文档
     * @return array
     */
    public function getSysActionsDoc()
    {
        $actionDocs = [];
        $sysActions = $this->getSysActions();
        foreach ($sysActions as $actionId) {
            $ActionDoc = new SystemActionDoc($this->controller, $actionId,$this->moduleId);
            $actionDocs[$actionId] = $ActionDoc->doc();
        }
        return $actionDocs;
    }

    /**
     * 获取用户自定义的接口的接口文档
     * @return array
     */
    public function getCustomActionsDoc()
    {
        $actionDocs = [];
        $customActions = $this->getCustomActions();
        foreach ($customActions as $actionId) {
            $ActionDoc = new CustomActionDoc($this->controller, $actionId,$this->moduleId);
            $actionDocs[$actionId] = $ActionDoc->doc();
        }
        return $actionDocs;
    }

    /**
     * 获取系统接口，即：在Controller::actions()中定义的接口
     * @return array
     */
    private function getSysActions()
    {
        $keys = array_keys($this->controller->actions());
        return array_combine($keys, $keys);

    }

    /**
     * 获取用户自定义的接口
     * @return array
     */
    private function getCustomActions()
    {
        $ref = new \ReflectionClass($this->controller);
        $sysActions = $this->getSysActions();
        $actions = [];
        $methods = $ref->getMethods(\ReflectionMethod::IS_PUBLIC);
        if ($methods) {
            foreach ($methods as $method) {
                if (!preg_match("/^action/", $method->name) or $method->name === 'actions') {
                    continue;
                } else {
                    $actionId = Tools::convertToActionId($method->name);

                    //如果系统接口已经存在，则用户自定义的同名接口是无效的
                    if (in_array($actionId, $sysActions)) {
                        continue;
                    }
                    $actions[$actionId] = $actionId;
                }
            }
        }

        return $actions;
    }

}






