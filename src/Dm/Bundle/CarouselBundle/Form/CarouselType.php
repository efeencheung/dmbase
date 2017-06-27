<?php

namespace Dm\Bundle\CarouselBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Dm\Bundle\AttachmentBundle\Form\PictureType;

class CarouselType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('carouselGroup', EntityType::class, array(
                'label'=>'轮播组',
                'class'=>'Dm\Bundle\CarouselBundle\Entity\CarouselGroup',
                'choice_label'=>'name',
                'placeholder'=>'请选择轮播图组',
                'attr'=>array('help'=>'选择一个轮播图所属的图组')
            ))
            ->add('sequence', IntegerType::class, array(
                'label'=>'排序',
                'attr'=>array('help'=>'输入一个数字，值越大排序越大排序越靠前')
            ))
            ->add('picture', PictureType::class, array(
                'cascade_validation' => true,
                'label'=>'图片',
                'attr'=>array('help'=>'部分JPEG图片会出现90度翻转，可保存成PNG格式再上传。宽度或高度大于1920的大尺寸照片会被自动压缩，可直接上传数码相机拍摄的高清照片')
            ))
            ->add('link', TextType::class, array(
                'required'=>false,
                'label'=>'跳转链接',
                'attr'=>array('help'=>'轮播图跳转链接，选填')
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'attr' => array('data-parsley-validate'=>'data-parsley-validate'),
            'data_class' => 'Dm\Bundle\CarouselBundle\Entity\Carousel'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'dm_bundle_carouselbundle_carousel';
    } 
}
