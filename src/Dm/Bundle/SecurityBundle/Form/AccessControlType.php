<?php

/*
 * 基于MIT开源协议发布
 *
 * (c) Efeen Cheung <261969254@qq.com>
 *
 * 有事请联系QQ:261969254, 微信:efeencheung, Github:efeencheung
 */

namespace Dm\Bundle\SecurityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AccessControlType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('label'=>'访问控制名称'))
            ->add('roles', 'entity', array(
                'label'=>'授权角色',
                'class' => 'Dm\Bundle\SecurityBundle\Entity\Role',
                'choice_label'=>function($role){
                    $placeStrNum = ($role->getLvl() + 1) * 2;
                    return '|' . str_repeat('-', $placeStrNum) . $role->getName();
                },
                'query_builder'=>function($repo){
                    $qb = $repo->createQueryBuilder('r');
                    $qb->orderBy('r.lft', 'ASC');

                    return $qb;
                },
                'label_attr'=>array('data-parsley-mincheck'=>'1', 'required'=>TRUE),
                'multiple'=>TRUE,
                'expanded'=>TRUE,
            ))
            ->add('path', 'text', array('label'=>'URI匹配模式', 'attr'=>array(
                'help'=>'请输入一个需要授权才能访问的URI匹配模式的正则表达式(例如: "^/admin/user")')
            ))
            ->add('ip', 'text', array(
                'label'=>'URI对应IP', 
                'required'=>FALSE, 
                'attr'=>array(
                    'pattern'=>'/^((25[0-5]|2[0-4]\d|[01]?\d\d?)($|(?!\.$)\.)){4}$/',
                    'help'=>'请输入一个符合格式的IPv4地址'
                )
            ))
            ->add('host', 'text', array('label'=>'URI对应HOST', 'required'=>FALSE))
            ->add('methods', 'choice', array(
                'choices' => array('GET'=>'GET', 'POST'=>'POST', 'PUT'=>'PUT', 'DELETE'=>'DELETE'),
                'label'=>'URI对应HTTP方法', 
                'multiple'=>TRUE,
                'expanded'=>TRUE,
                'required'=>FALSE
            ))
        ;

        $builder->get('methods')
            ->addModelTransformer(new CallbackTransformer(
                function($string) {
                    return explode(',', $string);
                },
                function($array) {
                    return implode(',', $array);
                }
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
            'data_class' => 'Dm\Bundle\SecurityBundle\Entity\AccessControl'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'dm_bundle_securitybundle_accesscontrol';
    }
}
