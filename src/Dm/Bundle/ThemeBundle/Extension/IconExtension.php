<?php

/*
 * 基于MIT开源协议发布
 *
 * (c) Efeen Cheung <261969254@qq.com>
 *
 * 有事请联系QQ:261969254, 微信:efeencheung, Github:efeencheung
 */

namespace Dm\Bundle\ThemeBundle\Extension; 

use Knp\Menu\Factory\ExtensionInterface;
use Knp\Menu\ItemInterface;    

/**
 * KnpMenu扩展
 *
 * 扩展KnpMenu, 在原有基础上添加Icon属性, 方便菜单带图标
 */
class IconExtension implements ExtensionInterface
{ 
    /**
     * 添加Icon属性
     */
    public function buildOptions(array $options = array())
    {
        if (!isset($options['icon'])) { 
            $options['icon'] = '';          
        }
        $options['extras']['icon'] = $options['icon'];

        return $options;
    }

    public function buildItem(ItemInterface $item, array $options)
    {
    }
}
