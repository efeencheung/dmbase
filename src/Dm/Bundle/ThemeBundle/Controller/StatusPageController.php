<?php

/*
 * 基于MIT开源协议发布
 *
 * (c) Efeen Cheung <261969254@qq.com>
 *
 * 有事请联系QQ:261969254, 微信:efeencheung, Github:efeencheung
 */

namespace Dm\Bundle\ThemeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class StatusPageController extends Controller
{
    /**
     * @Route("/")
     */
    public function homeAction()
    {
        return $this->redirect($this->generateUrl('dashboard'));
    }

    /**
     * @Route("/denied")
     * @Template()
     */
    public function deniedAction()
    {
        return array();    
    }

    /**
     * @Route("/notfound")
     * @Template()
     */
    public function notfoundAction()
    {
        return array();   
    }

}
