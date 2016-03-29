<?php

/*
 * 基于MIT开源协议发布
 *
 * (c) Efeen Cheung <261969254@qq.com>
 *
 * 有事请联系QQ:261969254, 微信:efeencheung, Github:efeencheung
 */

namespace Dm\Bundle\UserBundle\EventListener;

use Dm\Bundle\ThemeBundle\Event\ConfigureMenuEvent;

class ConfigureMenuListener
{
    private$authorizationChecker;

    public function __construct($authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param \Dm\Bundle\ThemeBundle\Event\ConfigureMenuEvent $event
     */
    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();

        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            $userMenu = $menu->addChild('用户管理', array('uri'=>'javascript:;','icon'=>'icon-user'));
            $userMenu->addChild('用户列表', array('route' => 'user'));
            $userMenu->addChild('用户添加', array('route' => 'user_new'));
        }
    }
}
