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
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RoleType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('label'=>'角色名称'))
            ->add('role', 'text', array('label'=>'角色标识'))
            ->add('parent', 'entity', array(
                'label'=>'上级角色',
                'required'=>FALSE,
                'class'=>'Dm\Bundle\SecurityBundle\Entity\Role',
                'choice_label'=>function($role){
                    $placeStrNum = ($role->getLvl() + 1) * 2;
                    return '|' . str_repeat('-', $placeStrNum) . $role->getName();
                },
                'query_builder'=>function($repo){
                    $qb = $repo->createQueryBuilder('r');
                    $qb->orderBy('r.lft', 'ASC');
                    return $qb;
                },
                'placeholder'=>'请选择上级角色',
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
            'data_class' => 'Dm\Bundle\SecurityBundle\Entity\Role'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'dm_bundle_securitybundle_role';
    }
}
