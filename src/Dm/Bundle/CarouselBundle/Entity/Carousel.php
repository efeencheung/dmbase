<?php

namespace Dm\Bundle\CarouselBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Carousel
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Carousel
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
     * 链接
     *
     * @var string
     *
     * @ORM\Column(name="link", type="string", length=255, nullable=true)
     */
    private $link;

    /**
     * 排序
     *
     * @var integer
     *
     * @ORM\Column(name="sequence", type="integer")
     */
    private $sequence;

    /** 
     * 轮播组
     *  
     * @var \Dm\Bundle\CarouselBundle\Entity\CarouselGroup
     *
     * @ORM\ManyToOne(targetEntity="\Dm\Bundle\CarouselBundle\Entity\CarouselGroup")
     * @ORM\JoinColumn(name="carouselgroup_id", referencedColumnName="id")
     */
    private $carouselGroup;

    /**
     * 图片
     *
     * @var \Dm\Bundle\AttachmentBundle\Entity\Picture
     * 
     * @ORM\ManyToOne(targetEntity="\Dm\Bundle\AttachmentBundle\Entity\Picture", cascade={"all"})
     * @ORM\JoinColumn(name="picture_id", referencedColumnName="id")
     */
    private $picture;

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
     * Set link
     *
     * @param string $link
     *
     * @return Carousel
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set sequence
     *
     * @param string $sequence
     *
     * @return Carousel
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;

        return $this;
    }

    /**
     * Get sequence
     *
     * @return string
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * Set carouselGroup
     *
     * @param \Dm\Bundle\CarouselBundle\Entity\CarouselGroup $carouselGroup
     *
     * @return Carousel
     */
    public function setCarouselGroup(\Dm\Bundle\CarouselBundle\Entity\CarouselGroup $carouselGroup = null)
    {
        $this->carouselGroup = $carouselGroup;

        return $this;
    }

    /**
     * Get carouselGroup
     *
     * @return \Dm\Bundle\CarouselBundle\Entity\CarouselGroup
     */
    public function getCarouselGroup()
    {
        return $this->carouselGroup;
    }

    /**
     * Set picture
     *
     * @param \Dm\Bundle\AttachmentBundle\Entity\Picture $picture
     *
     * @return Carousel
     */
    public function setPicture(\Dm\Bundle\AttachmentBundle\Entity\Picture $picture = null)
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * Get picture
     *
     * @return \Dm\Bundle\AttachmentBundle\Entity\Picture
     */
    public function getPicture()
    {
        return $this->picture;
    }
}
