<?php

namespace Dm\Bundle\AttachmentBundle\Entity;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface UploadAbleInterface
{
    /**
     * 获取表单提交的文件对象
     *
     * @return Symfony\Component\HttpFoundation\File\UploadedFile
     */
    public function getFile();


    /**
     * 设置提交的文件对象
     *
     * @return UploadAbleInterface
     */
    public function setFile(UploadedFile $file);

    /**
     * 获取上传附件类型，根据类型名称存入不同的子目录
     *
     * @return string
     */
    public function getTypeName();

    /**
     * 获取文件的Web访问路径
     *
     * @return string
     */
    public function getWebPath();

    /**
     * 获取文件名
     *
     * @return string
     */
    public function getFilename();

    /**
     * 设置文件名
     *
     * @return UploadAbleInterface
     */
    public function setFilename($filename);

    /**
     * 获取修改前的文件名
     *
     * @return string
     */
    public function getTempFilename();
}
