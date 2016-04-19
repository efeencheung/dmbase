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

class AddAccessMapPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $em = $container->get('doctrine')->getManager();

        try {
            $accessControls = $em->getRepository('DmSecurityBundle:AccessControl')->findAll();
        } catch (\Exception $e) {
            return;
        }

        foreach ($accessControls as $accessControl) {
            $roleCollection = $accessControl->getRoles(); 
            $roles = array();
            foreach ($roleCollection as $role) {
               $roles[] = $role->getRole(); 
            }

            /* 获取匹配的HTTP方法，并过滤空值*/
            $methods = $accessControl->getMethods();
            $methods = array_filter(explode(',', $methods));

            $requestMatcher = $this->createRequestMatcher(
                $container,
                $accessControl->getPath(),
                $accessControl->getHost(),
                $methods,
                $accessControl->getIp()
            );

            $container->getDefinition('security.access_map')
                ->addMethodCall('add', array($requestMatcher, $roles));
        }
    }

    private function createRequestMatcher($container, $path = null, $host = null, $methods = array(), $ip = null, array $attributes = array())
    {
        if ($methods) {
            $methods = array_map('strtoupper', (array) $methods);
        }
        $serialized = serialize(array($path, $host, $methods, $ip, $attributes));
        $id = 'security.request_matcher.'.md5($serialized).sha1($serialized);
        // only add arguments that are necessary
        $arguments = array($path, $host, $methods, $ip, $attributes);
        while (count($arguments) > 0 && !end($arguments)) {
            array_pop($arguments);
        }
    
        $container
            ->register($id, '%security.matcher.class%')
            ->setPublic(false)
            ->setArguments($arguments)
        ;
        return new Reference($id);
    }
}
