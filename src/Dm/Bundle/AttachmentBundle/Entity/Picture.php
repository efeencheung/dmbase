<?php

namespace Dm\Bundle\AttachmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Picture
 *
 * @ORM\Table()
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Picture implements UploadAbleInterface
{
    /**
     * ID
     *
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * 文件名
     *
     * @var string
     *
     * @ORM\Column(name="filename", type="string", length=255, nullable=true)
     */
    private $filename;

    /**
     * 描述
     *
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @Assert\Image(maxSize="2M", maxSizeMessage="图片大小不能超过2M" )
     */
	private $file;

    private $tempFilename;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * {@inheritDoc}
     */
    public function getFile()
    {
        return $this->file;
    }

	/**
     * {@inheritDoc}
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;

        /**
         * 由表单内File内容改变，触发filename这个映射关系的值改变，只有entity的某个值改变，才能触发LifecycleEvent
         */
        if (isset($this->filename)) {
            $this->tempFilename = $this->filename;
            $this->filename = null;
        } else {
            $this->filename = 'init';
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getTypeName()
    {
        return 'image';
    }

    /**
     * {@inheritDoc}
     */
    public function getWebPath()
    {
        return '/upload/image/' . substr($this->filename, 0, 2) . '/' . $this->filename;
    }

    /**
     * {@inheritDoc}
     */
    public function getTempFilename()
    {
        return $this->tempFilename;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Picture
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}
