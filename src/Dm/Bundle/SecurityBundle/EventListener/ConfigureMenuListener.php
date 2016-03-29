<?php

/*
 * 基于MIT开源协议发布
 *
 * (c) Efeen Cheung <261969254@qq.com>
 *
 * 有事请联系QQ:261969254, 微信:efeencheung, Github:efeencheung
 */

namespace Dm\Bundle\SecurityBundle\EventListener;

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

        if ($this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN')) {
            $roleMenu = $menu->addChild('角色管理', array('uri'=>'javascript:;','icon'=>'icon-users'));
            $roleMenu->addChild('角色列表', array('route' => 'role'));
            $roleMenu->addChild('角色添加', array('route' => 'role_new'));

            $accessMenu = $menu->addChild('访问控制管理', array('uri'=>'javascript:;','icon'=>'icon-link'));
            $accessMenu->addChild('访问控制列表', array('route' => 'accesscontrol'));
            $accessMenu->addChild('访问控制添加', array('route' => 'accesscontrol_new'));
        }
    }
}
