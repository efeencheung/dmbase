<?php

/*
 * 基于MIT开源协议发布
 *
 * (c) Efeen Cheung <261969254@qq.com>
 *
 * 有事请联系QQ:261969254, 微信:efeencheung, Github:efeencheung
 */

namespace Dm\Bundle\ContentBundle\EventListener;

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
            $contentMenu = $menu->addChild('内容', array('uri'=>'javascript:;','icon'=>'icon-folder'));
            $contentMenu->addChild('标签管理', array('route' => 'admin_tag'));
            $contentMenu->addChild('图文资讯', array('route' => 'admin_article'));
            $contentMenu->addChild('图片资讯', array('route' => 'admin_photo'));
        }
    }
}
