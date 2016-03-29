<?php

/*
 * 基于MIT开源协议发布
 *
 * (c) Efeen Cheung <261969254@qq.com>
 *
 * 有事请联系QQ:261969254, 微信:efeencheung, Github:efeencheung
 */

namespace Dm\Bundle\GeneratorBundle\Generator;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class DoctrineFormGenerator extends Generator
{
    private $filesystem;
    private $className;
    private $classPath;

    /**
     * Constructor.
     *
     * @param Filesystem $filesystem 一个Filesystem的实例
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function getClassPath()
    {
        return $this->classPath;
    }

    /**
     * 生成一个Entity对应的表单文件
     *
     * @param BundleInterface   $bundle   The bundle in which to create the class
     * @param string            $entity   The entity relative class name
     * @param ClassMetadataInfo $metadata The entity metadata class
     */
    public function generate(BundleInterface $bundle, $entity, ClassMetadataInfo $metadata)
    {
        $parts       = explode('\\', $entity);
        $entityClass = array_pop($parts);

        $this->className = $entityClass.'Type';
        $dirPath         = $bundle->getPath().'/Form';
        $this->classPath = $dirPath.'/'.str_replace('\\', '/', $entity).'Type.php';

        if (file_exists($this->classPath)) {
            throw new \RuntimeException(sprintf('无法生成 %s Form类, 因为它已经存在 %s', $this->className, $this->classPath));
        }

        if (count($metadata->identifier) > 1) {
            throw new \RuntimeException('Form生成器不支持多个主键的Entity类.');
        }

        $parts = explode('\\', $entity);
        array_pop($parts);

        $this->renderFile('form/FormType.php.twig', $this->classPath, array(
            'fields'           => $this->getFieldsFromMetadata($metadata),
            'namespace'        => $bundle->getNamespace(),
            'entity_namespace' => implode('\\', $parts),
            'entity_class'     => $entityClass,
            'bundle'           => $bundle->getName(),
            'form_class'       => $this->className,
            'form_type_name'   => strtolower(str_replace('\\', '_', $bundle->getNamespace()).($parts ? '_' : '').implode('_', $parts).'_'.substr($this->className, 0, -4)),
        ));
    }

    /**
     * 返回一个所有字段的数组. 
     * 字段可以是属性字段，也可以是关联字段
     * 
     * 获取中文名比较粗暴，暂时先用
     *
     * @param  ClassMetadataInfo $metadata
     * @return array             $fields
     */
    private function getFieldsFromMetadata(ClassMetadataInfo $metadata)
    {
        $fields = (array) $metadata->fieldMappings;
        $className = $metadata->name;

        //dump($className); die();
        foreach ($fields as $key => $field) {
            if (isset($field['id']) && $field['id'] === true) {
                unset($fields[$key]);
                continue;
            }

            $reflProperty = new \ReflectionProperty($className, $field['fieldName']);
            $fieldDoc = $reflProperty->getDocComment();
            $docLines = explode ("\n", $fieldDoc);
            $fields[$key]['chineseName'] = trim(str_replace('*', '',$docLines[1]));
        }

        return $fields;
    }
}
