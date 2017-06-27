<?php

namespace Dm\Bundle\ContentBundle\Util;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Filesystem\Filesystem;

class TextareaImageUtil
{
    protected $webPath;

    protected $filesystem;

    protected $em;

    public function __construct($webPath, Filesystem $filesystem)
    {
        $this->webPath = $webPath;
        $this->filesystem = $filesystem;
    }

    /**
     * 替换文档中Base64编码的图片为路径
     *
     * @param string $document 待替换的文档
     * @param string $webPath Web路径
     */
    public function replaceImageString($document)
    {
        $self = $this;

        $document = preg_replace_callback("~data:image/([a-zA-Z]*);base64,([a-zA-Z0-9+/=]*)~", 
            function($matches) use ($self) {
                $filename = sha1(uniqid(mt_rand(), true)) . '.' . $matches[1]; // 生成一个唯一文件名称
                $webPath = '/upload/image/' . substr($filename, 0, 2); // Web路径
                $filePath = realpath($self->webPath) . $webPath; // 文件系统路径

                if (!$self->filesystem->exists($filePath)) {
                    $self->filesystem->mkdir($filePath);
                }

                $file = $filePath . '/' . $filename;
                $fileContent = base64_decode($matches[2]);
                file_put_contents($file, $fileContent);

                return $webPath . '/' . $filename;
            }, 
            $document
        );

        return $document;
    }
}
