<?php

namespace Dm\Bundle\ContentBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Dm\Bundle\AttachmentBundle\Form\PictureType;

class ArticleType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, array(
                'label'=>'图文资讯标题',
                'attr'=>array(
                    'placeholder'=>'请输入图文资讯标题'
                ),
            ))
            ->add('content', TextareaType::class, array(
                'label'=>'图文资讯内容',
                'attr'=>array(
                    'class'=>'summernote-full',
                    'placeholder'=>'输入图文资讯内容',
                    'help'=>'回车默认为输入段落，如需换行，使用“shift+回车”',
                ),
            ))
            ->add('author', TextType::class, array(
                'required'=>false,
                'label'=>'作者',
                'attr'=>array(
                    'class'=>'input-medium',
                    'placeholder'=>'请输入作者(选填)',
                ),
            ))
            ->add('tags', EntityType::class, array(
                'label'=>'图文资讯标签',
                'class'=>'DmContentBundle:Tag',
                'choice_label'=>'name',
                'multiple'=>TRUE,
                'expanded'=>FALSE,
                'attr'=>array(
                    'help'=>'请选择图文资讯标签，标签可以用作图文资讯的分类，一篇图文资讯可以有多个标签',
                ),
            ))
            ->add('picture', PictureType::class, array(
                'cascade_validation'=>true,
                'label'=>'封面图片',
                'attr'=>array(
                    'help'=>'请选择图文资讯的封面，部分JPEG图片会出现90度翻转，可保存成PNG格式再上传',
                    'placeholder'=>'封面图片'
                )
            ))
            ->add('publishedAt', DateTimeType::class, array(
                'label'=>'发布时间', 
                'widget'=>'single_text',
                'attr'=>array(
                    'help'=>'请选择图文资讯的发布时间，默认为及时发布，如需要定时发布，可在此设定',
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
