<?php

/*
 * 基于MIT开源协议发布
 *
 * (c) Efeen Cheung <261969254@qq.com>
 *
 * 有事请联系QQ:261969254, 微信:efeencheung, Github:efeencheung
 */

namespace Dm\Bundle\GeneratorBundle\Generator;

use Doctrine\ORM\Tools\EntityGenerator as BaseEntityGenerator;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class EntityGenerator extends BaseEntityGenerator
{
    /**
     * 在变量注释中添加一行中文名称
     *
     * @param array             $fieldMapping
     * @param ClassMetadataInfo $metadata
     *
     * @return string
     */
    protected function generateFieldMappingPropertyDocBlock(array $fieldMapping, ClassMetadataInfo $metadata)
    {
        $doc = parent::generateFieldMappingPropertyDocBlock($fieldMapping, $metadata);

        if (isset($fieldMapping['chineseName'])) {
            $lines = explode("\n", $doc);
            // 将第一行注释(/**)删除
            array_shift($lines); 

            $insertLines[] = $this->spaces . "/**";
            $insertLines[] = $this->spaces . " * " . $fieldMapping['chineseName']; 
            $insertLines[] = $this->spaces . " *";

            $newLines = array_merge($insertLines, $lines);

            $doc = implode("\n", $newLines);
        }

        return $doc;
    }
}
