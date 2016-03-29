<?php

/*
 * 基于MIT开源协议发布
 *
 * (c) Efeen Cheung <261969254@qq.com>
 *
 * 有事请联系QQ:261969254, 微信:efeencheung, Github:efeencheung
 */

namespace Dm\Bundle\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\ButtonTypeInterface;
use Symfony\Component\Form\SubmitButtonTypeInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Dm\Bundle\SecurityBundle\Form\UserGroupType;

class UserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['method'] == 'PUT') {
            $isUsernameDisabled = TRUE;
            $isPwdRequired = FALSE;
        } else {
            $isUsernameDisabled = FAlSE;
            $isPwdRequired = TRUE;
        }

        $builder
            ->add('username', 'text', array(
                'label'=>'用户名',
                'disabled'=>$isUsernameDisabled,
                'attr'=>array(
                    'data-parsley-pattern'=>'/^[0-9a-z]{4,18}$/',
                    'help'=>'用户名由4-18位字母和数字组成'
                )
            ))
            ->add('password', 'repeated', array(
                'required'=>$isPwdRequired,
                'type'=>'password',
                'invalid_message'=>'两次输入必须相同',
                'first_options'=>array(
                    'label'=>'密码',
                    'attr'=>array('data-parsley-length'=>'[4,18]')
                ), 
                'second_options'=>array(
                    'label'=>'确认密码', 
                    'attr'=>array('data-parsley-length'=>'[4,18]', 'data-parsley-equalto'=>'#dm_bundle_userbundle_user_password_first')
                )
            ))
            ->add('role', 'entity', array(
                'label'=>'用户角色',
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
                'placeholder'=>'请选择用户角色',
            ))
            ->add('showname', 'text', array('label'=>'显示名称'))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Dm\Bundle\UserBundle\Entity\User',
            'attr' => array('data-parsley-validate'=>'data-parsley-validate')
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'dm_bundle_userbundle_user';
    }
}
