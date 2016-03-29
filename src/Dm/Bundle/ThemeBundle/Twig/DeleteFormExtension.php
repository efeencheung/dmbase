<?php

/*
 * 基于MIT开源协议发布
 *
 * (c) Efeen Cheung <261969254@qq.com>
 *
 * 有事请联系QQ:261969254, 微信:efeencheung, Github:efeencheung
 */

namespace Dm\Bundle\ThemeBundle\Twig;

use Symfony\Component\Form\FormView;

/**
 * 生成一个通用的数据删除表单, 在表单中实现HTTP DELETE方法
 */
class DeleteFormExtension extends \Twig_Extension
{   
    /** 
     * Twig 类，渲染模板       
     *
     * @var \Twig_Environment  
     */ 
    private $twig;

    public function __construct(\Twig_Environment $twig)
    {   
        $this->twig = $twig;   
    }   

    /**
     * {@inheritdoc}           
     */ 
    public function getFunctions()  
    {   
        return array(          
            'form_delete' => new \Twig_Function_Method($this, 'renderDeleteForm', array('is_safe' => array('html'))),
        );
    }

    /**
     * @param FormView $form
     * @param integer $did
     */
    public function renderDeleteForm(FormView $form, $did)
    {
        return $this->twig->render('DmThemeBundle::form_delete.html.twig', array('form'=>$form, 'did'=>$did));
    }

    public function getName()  
    {
        return 'form_delete';
    }
}
