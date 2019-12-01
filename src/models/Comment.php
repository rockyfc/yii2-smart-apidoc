<?php

namespace smart\apidoc\models;


use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;

/**
 * Class Comment
 * @package smart\apidoc\models
 */
class Comment
{

    /**
     * @var DocBlock $docblock
     */
    private $docblock;

    /**
     * @param String $docComment
     */
    public function __construct($docComment)
    {
        $factory = DocBlockFactory::createInstance();
        $this->docblock = $factory->create($docComment);
    }

    /**
     * 获取注释中的标题信息
     * @return string
     */
    public function getSummary()
    {
        return $this->docblock->getSummary();
    }

    /**
     * 获取标题中的描述信息
     * @return string
     */
    public function getDescription()
    {
        return $this->docblock->getDescription()->render();
    }


    /**
     * @return DocBlock\Tag[]
     */
    public function getTagSee()
    {
        if ($this->docblock->hasTag('see')) {
            $seeTags = $this->docblock->getTagsByName('see');

            return $seeTags;
        }
    }

    /**
     * 获取@param标记的参数
     * @return Fields[]
     */
    public function getParamTag()
    {
        if ($this->docblock->hasTag('param')) {
            $tags = $this->docblock->getTagsByName('param');

            if (!$tags) {
                return [];
            }

            $response = [];

            /** @var \phpDocumentor\Reflection\DocBlock\Tags\Param $param */
            foreach ($tags as $param) {

                $field = new Fields();
                $field->type = (String)$param->getType();
                $field->comment = $param->getDescription()->render();
                //$field->required = (bool)preg_match('/required/', $colInfo);

                //preg_match('/range\(([\s\S]*?)\)/', $colInfo, $rs);
                //$field->range = (Array)@$rs[1];
                $field->variableName = $param->getVariableName();

                $response[] = $field;
            }

            return $response;

        }
    }

}