<?php

/*
 * 基于MIT开源协议发布
 *
 * (c) Efeen Cheung <261969254@qq.com>
 *
 * 有事请联系QQ:261969254, 微信:efeencheung, Github:efeencheung
 */

namespace Dm\Bundle\GeneratorBundle\Command;

class Validators
{
    public static function validateBundleNamespace($namespace, $requireVendorNamespace = true)
    {
        if (!preg_match('/Bundle$/', $namespace)) {
            throw new \InvalidArgumentException('命名空间必须以Bundle为结尾.');
        }

        $namespace = strtr($namespace, '/', '\\');
        if (!preg_match('/^(?:[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*\\\?)+$/', $namespace)) {
            throw new \InvalidArgumentException('命名空间包含非法字符.');
        }

        // 验证保留字
        $reserved = self::getReservedWords();
        foreach (explode('\\', $namespace) as $word) {
            if (in_array(strtolower($word), $reserved)) {
                throw new \InvalidArgumentException(sprintf('命名空间包含PHP保留单词 ("%s").', $word));
            }
        }

        // 命名空间至少是两层
        if ($requireVendorNamespace && false === strpos($namespace, '\\')) {
            // language is (almost) duplicated in GenerateBundleCommand
            $msg = array();
            $msg[] = sprintf('命名空间必须以一个自定义的名称开头 (e.g. "VendorName\%s" 代替简单的 "%s").', $namespace, $namespace);
            $msg[] = '如果你指定一个自定义的名称作为命名空间, 不要忘记对它使用双引号 (init:bundle "Acme\BlogBundle")?';

            throw new \InvalidArgumentException(implode("\n\n", $msg));
        }

        return $namespace;
    }

    public static function validateBundleName($bundle)
    {
        if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $bundle)) {
            throw new \InvalidArgumentException('Bundle名称包含非法字符.');
        }

        if (!preg_match('/Bundle$/', $bundle)) {
            throw new \InvalidArgumentException('Bundle名称必须以 <comment>Bundle</comment> 结尾.');
        }

        return $bundle;
    }

    public static function validateChineseName($chineseName)
    {
        if ($chineseName == '') {
            throw new \InvalidArgumentException('中文名称不能为空');
        }

        return $chineseName;
    }

    public static function validateControllerName($controller)
    {
        try {
            self::validateEntityName($controller);
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Controller名称必须包含一个冒号(:) (类似于AcmeBlogBundle:Post, 而不是 %s)',
                    $controller
                )
            );
        }

        return $controller;
    }

    public static function validateTargetDir($dir, $bundle, $namespace)
    {
        // add trailing / if necessary
        return '/' === substr($dir, -1, 1) ? $dir : $dir.'/';
    }

    public static function validateFormat($format)
    {
        $format = strtolower($format);

        if (!in_array($format, array('php', 'xml', 'yml', 'annotation'))) {
            throw new \RuntimeException(sprintf('"%s" 是一个不支持的格式.', $format));
        }

        return $format;
    }

    public static function validateEntityName($entity)
    {
        if (false === strpos($entity, ':')) {
            throw new \InvalidArgumentException(sprintf('Entity 名称必须包含一个冒号(:) ("%s" given, 例如这样的格式 AcmeBlogBundle:Blog/Post)', $entity));
        }

        return $entity;
    }

    public static function getReservedWords()
    {
        return array(
            'abstract',
            'and',
            'array',
            'as',
            'break',
            'callable',
            'case',
            'catch',
            'class',
            'clone',
            'const',
            'continue',
            'declare',
            'default',
            'do',
            'else',
            'elseif',
            'enddeclare',
            'endfor',
            'endforeach',
            'endif',
            'endswitch',
            'endwhile',
            'extends',
            'final',
            'finally',
            'for',
            'foreach',
            'function',
            'global',
            'goto',
            'if',
            'implements',
            'interface',
            'instanceof',
            'insteadof',
            'namespace',
            'new',
            'or',
            'private',
            'protected',
            'public',
            'static',
            'switch',
            'throw',
            'trait',
            'try',
            'use',
            'var',
            'while',
            'xor',
            'yield',
            '__CLASS__',
            '__DIR__',
            '__FILE__',
            '__LINE__',
            '__FUNCTION__',
            '__METHOD__',
            '__NAMESPACE__',
            '__TRAIT__',
            '__halt_compiler',
            'die',
            'echo',
            'empty',
            'exit',
            'eval',
            'include',
            'include_once',
            'isset',
            'list',
            'require',
            'require_once',
            'return',
            'print',
            'unset',
        );
    }
}
