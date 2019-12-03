<?php

namespace smart\apidoc\models;

/**
 * 工具类
 * @package smart\apidoc\models
 */
class Tools
{

    /**
     * 将controller 名称转化成路由形式
     * @param $controllerName
     * @return string
     */
    public static function convertToControllerId($controllerName)
    {

        $str = preg_replace("/(?=[A-Z])/", '-', $controllerName);
        $str = trim($str, '-');
        $str = strtolower($str);
        $arr = explode('-', $str);
        $last = count($arr) - 1;
        if ($arr[$last] === 'controller') {
            unset($arr[$last]);
        }

        return implode('-', $arr);
    }


    /**
     * 将action的名称转化成路由形式
     * @param $actionName
     * @return string
     */
    public static function convertToActionId($actionName)
    {
        /*
        preg_match_all("/([a-zA-Z]{1}[a-z]*)?[^A-Z]/",$str,$array);
        */
        $str = preg_replace("/(?=[A-Z])/", '-', $actionName);
        $str = strtolower($str);
        $arr = explode('-', $str);
        if ($arr[0] === 'action') {
            array_shift($arr);
        }

        return implode('-', $arr);
    }

}






