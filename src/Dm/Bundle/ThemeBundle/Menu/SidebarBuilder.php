<?php

/*
 * 基于MIT开源协议发布
 *
 * (c) Efeen Cheung <261969254@qq.com>
 *
 * 有事请联系QQ:261969254, 微信:efeencheung, Github:efeencheung
 */

namespace Dm\Bundle\ThemeBundle\Menu;

use Dm\Bundle\ThemeBundle\Event\ConfigureMenuEvent;
use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class SidebarBuilder extends ContainerAware
{
    public function build(FactoryInterface $factory)
    {
        $menu = $factory->createItem('root');
        $menu->setChildrenAttributes(array(
            'class'=>'page-sidebar-menu page-header-fixed',
            'data-keep-expanded'=>'false',
            'data-auto-scroll'=>'true',
            'data-slide-speed'=>'200'
        ));

        $menu->setCurrent($this->container->get('request')->getRequestUri());
        $menu->addChild('控制台', array('route'=>'dashboard', 'icon'=>'icon-home'));

        $this->container->get('event_dispatcher')->dispatch(
            ConfigureMenuEvent::CONFIGURE,
            new ConfigureMenuEvent($factory, $menu)
        );

        return $menu;
    }
}
