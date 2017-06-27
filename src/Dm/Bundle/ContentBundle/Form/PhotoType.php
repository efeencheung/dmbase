<?php

namespace Dm\Bundle\ContentBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class PhotoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, array(
                'label'=>'资讯标题',
                'attr'=>array(
                    'placeholder'=>'请输入资讯标题'
                ),
            ))
            ->add('tags', EntityType::class, array(
                'label'=>'资讯标签',
                'class'=>'DmContentBundle:Tag',
                'choice_label'=>'name',
                'multiple'=>TRUE,
                'expanded'=>FALSE,
                'attr'=>array(
                    'help'=>'请选择资讯标签，标签可以用作资讯的分类，一篇资讯可以有多个标签',
                ),
            ))
            ->add('publishedAt', DateTimeType::class, array(
                'label'=>'发布时间', 
                'widget'=>'single_text',
                'attr'=>array(
                    'help'=>'请选择资讯的发布时间，默认为及时发布，如需要定时发布，可在此设定',
                ),
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
            'data_class' => 'Dm\Bundle\ContentBundle\Entity\Article'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'dm_bundle_contentbundle_article';
    }
}
