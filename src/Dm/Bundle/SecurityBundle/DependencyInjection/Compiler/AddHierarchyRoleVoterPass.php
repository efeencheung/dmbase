<?php

/*
 * 基于MIT开源协议发布
 *
 * (c) Efeen Cheung <261969254@qq.com>
 *
 * 有事请联系QQ:261969254, 微信:efeencheung, Github:efeencheung
 */

namespace Dm\Bundle\SecurityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\Security\Http\AccessMap;

class AddHierarchyRoleVoterPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $em = $container->get('doctrine')->getManager();

        try {
            $roleRepo = $em->getRepository('DmSecurityBundle:Role');
            $roleCollection = $roleRepo->findAll();
        } catch (\Exception $e) {
            return;
        }

        $hierarchy = array();
        foreach ($roleCollection as $role) {
            $hierarchyRoleCollection = $roleRepo->children($role);
            $hierarchyRoles = array();
            foreach ($hierarchyRoleCollection as $hRole) {
                $hierarchyRoles[] = $hRole->getRole();
            }

            if (count($hierarchyRoles) > 0) {
                $hierarchy[$role->getRole()] = $hierarchyRoles;
            }
        }

        $container->getDefinition('security.role_hierarchy')->replaceArgument(0, $hierarchy);
        /* TODO 临时解决方案, 有可能Symfony解析是有BUG, 待查证 */
        if ($container->hasDefinition('security.access.simple_role_voter')) {
            $container->getDefinition('security.access.simple_role_voter')->setClass('%security.access.role_hierarchy_voter.class%')
                ->setArguments(array(new Reference('security.role_hierarchy')));
        }
    }
}
