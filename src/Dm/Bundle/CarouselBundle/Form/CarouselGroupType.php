<?php

namespace Dm\Bundle\CarouselBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CarouselGroupType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array(
                'label'=>'组名称',
                'attr'=>array(
                    'help'=>'用2-4个字标识该轮播图组的信息，如“首页”，“首页顶部”这样的词语'
                )
            ))
            ->add('description', TextType::class, array(
                'required'=>false,
                'label'=>'组描述',
                'attr'=>array(
                    'help'=>'轮播图组信息的详细描述，如所在页面，呈现位置等信息'
                )
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
            'data_class' => 'Dm\Bundle\CarouselBundle\Entity\CarouselGroup'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'dm_bundle_carouselbundle_carouselgroup';
    }
}
