<?php

/*
 * 基于MIT开源协议发布
 *
 * (c) Efeen Cheung <261969254@qq.com>
 *
 * 有事请联系QQ:261969254, 微信:efeencheung, Github:efeencheung
 */

namespace Dm\Bundle\CarouselBundle\EventListener;

use Dm\Bundle\AdminBundle\Event\ConfigureMenuEvent;

class ConfigureMenuListener
{
    private$authorizationChecker;

    public function __construct($authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param \Dm\Bundle\AdminBundle\Event\ConfigureMenuEvent $event
     */
    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();

        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            $caroucelMenu = $menu->addChild('轮播图管理', array('uri'=>'javascript:;','icon'=>'icon-loop'));
            $caroucelMenu->addChild('轮播组管理', array('route' => 'admin_carouselgroup'));
            $caroucelMenu->addChild('轮播图管理', array('route' => 'admin_carousel'));
        }
    }
}
