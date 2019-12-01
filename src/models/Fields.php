<?php

namespace smart\apidoc\models;


class Fields
{
    /**
     * 变量名称
     * @var string
     */
    public $variableName;
    /**
     * 数据类型
     * @var
     */
    public $type;

    /**
     * 是否必须
     * @var
     */
    public $required;

    /**
     * 可选值
     * @var
     */
    public $range;

    /**
     * 默认值
     * @var
     */
    public $default;

    /**
     * 字段注释
     * @var
     */
    public $comment; 
}