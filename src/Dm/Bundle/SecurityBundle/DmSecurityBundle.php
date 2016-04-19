<?php

/*
 * 基于MIT开源协议发布
 *
 * (c) Efeen Cheung <261969254@qq.com>
 *
 * 有事请联系QQ:261969254, 微信:efeencheung, Github:efeencheung
 */

namespace Dm\Bundle\SecurityBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Dm\Bundle\SecurityBundle\DependencyInjection\Compiler\AddAccessMapPass;
use Dm\Bundle\SecurityBundle\DependencyInjection\Compiler\AddHierarchyRoleVoterPass;

class DmSecurityBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
    
        parent::build($container);

        $container->addCompilerPass(new AddAccessMapPass(), PassConfig::TYPE_BEFORE_REMOVING);
        $container->addCompilerPass(new AddHierarchyRoleVoterPass(), PassConfig::TYPE_BEFORE_REMOVING);
    }
}
