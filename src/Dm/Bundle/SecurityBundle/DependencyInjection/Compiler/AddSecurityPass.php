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

class AddSecurityPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        // 读取数据库链接
        $dbalConfig = new \Doctrine\DBAL\Configuration();
        $connectionParams = array(
            'dbname' => $container->getParameter('database_name'),
            'user' => $container->getParameter('database_user'),
            'password' => $container->getParameter('database_password'),
            'host' => $container->getParameter('database_host'),
            'driver' => 'pdo_mysql',
        );  
        $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $dbalConfig);

        /* 在编译中加入从数据库中读取的访问控制规则 */
        $sql = 'SELECT * FROM access_control';
        $accessControls = $conn->query($sql)->fetchAll();
        foreach ($accessControls as $access) {
            // 获取授权的ROLE
            $sql = 'SELECT r.role FROM roles_accesscontrols AS ra LEFT JOIN role AS r ON ra.role_id = r.id WHERE ra.access_control_id=?';
            $stmt = $conn->executeQuery($sql, array($access['id']));
            $roles = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            if ($access['methods']) {
                $methods = explode(',', $access['methods']);
            } else {
                $methods = array();
            }
            $requestMatcher = $this->createRequestMatcher(
                $container,
                $access['path'],
                $access['host'],
                $methods,
                $access['ip']
            );

            $container->getDefinition('security.access_map')
                ->addMethodCall('add', array($requestMatcher, $roles));
        }

        /* 在编译中加入从数据库获取的角色继承数据 */
        $sql = 'SELECT * FROM role WHERE lvl>0';
        $roles = $conn->fetchAll($sql);
        $hierarchy = array();
        foreach ($roles as $role) {
            $sql = 'SELECT role FROM role WHERE lft<? AND rgt>?';
            $stmt = $conn->executeQuery($sql, array($role['lft'], $role['rgt']));

            $hierarchy[$role['role']] = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        }
        $container->setParameter('security.role_hierarchy.roles', $hierarchy);
        $container->removeDefinition('security.access.simple_role_voter');
    }

    private function createRequestMatcher($container, $path = null, $host = null, $methods = array(), $ip = null, array $attributes = array())
    {
        if ($methods) {
            $methods = array_map('strtoupper', (array) $methods);
        }

        $serialized = serialize(array($path, $host, $methods, $ip, $attributes));
        $id = 'security.request_matcher.'.md5($serialized).sha1($serialized);

        if (isset($this->requestMatchers[$id])) {
            return $this->requestMatchers[$id];
        }

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

        return $this->requestMatchers[$id] = new Reference($id);
    }
}
