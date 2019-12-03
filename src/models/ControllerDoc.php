<?php

namespace smart\apidoc\models;

use yii\rest\ActiveController;

class ControllerDoc
{

    public function __construct(ActiveController $Controller)
    {
        $this->_ActiveController = $Controller;
    }

    /* public function modules()
     {
         $tmp = [];
         foreach (\Yii::$app->getModules() as $moduleId => $model) {
             $tmp[] = $moduleId;
         }

         return $tmp;

     }*/

    public function title()
    {
        $ref = new \ReflectionClass($this->_ActiveController);
        $strComment = (String)$ref->getDocComment();

        if(empty($strComment)){
            return '';
        }
        $comment = new Comment($strComment);
        return $comment->getSummary();


    }

    public function doc()
    {
        //$ref = new \ReflectionClass($this->_ActiveController);

        //$sysActions = [];
        //var_dump($customActions);

        //获取所有的actionid
        //$actionsAll = array_merge($sysActions, $customActions);

        $actionDocs = [];

        $sysActions = $this->getSysActions();
        foreach ($sysActions as $actionId) {
            $ActionDoc = new SystemActionDoc($this->_ActiveController, $actionId);
            $actionDocs[$actionId] = $ActionDoc->doc();


        }

        $customActions = $this->getCustomActions();
        foreach ($customActions as $actionId=>$strComment) {

            $modelClass = $actionId.'ModelClass';
            if(isset($this->_ActiveController->$modelClass)){
                $ActionDoc = new SystemActionDoc($this->_ActiveController, $actionId,$strComment);
                $actionDocs[$actionId] = $ActionDoc->doc();
                continue;
            }



            $ActionDoc = new NormalActionDoc($this->_ActiveController, $actionId,$strComment);
            $actionDocs[$actionId] = $ActionDoc->doc();


        }

        //print_r($actionDocs);
        return $actionDocs;
    }

    /**
     * 获取
     * @return array
     */
    private function getSysActions()
    {
        $keys = array_keys($this->_ActiveController->actions());
        return array_combine($keys,$keys);

        print_r($a);exit;
    }

    /**
     * 获取用户自定义action
     */
    private function getCustomActions()
    {
        $ref = new \ReflectionClass($this->_ActiveController);

        $data = [];
        $methods = $ref->getMethods(\ReflectionMethod::IS_PUBLIC);
        if ($methods) {
            foreach ($methods as $method) {
                if (!preg_match("/^action/", $method->name)
                    //or $method->name === 'actionClientValidate'
                    or $method->name === 'actions'
                ) {
                    //echo $method->name."<br/>";
                    continue;
                } else {
                    $actionId = Tools::convertToActionId($method->name);

                    $data[$actionId] = $method->getDocComment();
                }
            }
        }

        return $data;
    }

    private function parseComment($str)
    {

        $arr = explode('\n', $str);

        foreach ($arr as $comment) {
            $pos = stripos($comment, '@title');
            if ($pos > 0) {
                $str = substr($comment, $pos + 6);
                $endPos = stripos($str, '*');
                if ($endPos <= 0) {
                    $endPos = stripos($str, ' ');
                }
                $str = substr($str, 0, ($endPos - 1));
                return trim($str);
            }
            /*else{
                return $arr[0];
            }*/

        }

        //exit;

    }


}






