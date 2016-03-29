<?php

/*
 * 基于MIT开源协议发布
 *
 * (c) Efeen Cheung <261969254@qq.com>
 *
 * 有事请联系QQ:261969254, 微信:efeencheung, Github:efeencheung
 */

namespace Dm\Bundle\SecurityBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * 该类读取并管理该Bundle的配置文件
 *
 * 想要了解更多请查看 {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class DmSecurityExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * 从数据库读取Access Control信息
     */
    private function createAuthorization($config, ContainerBuilder $container)
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

        $sql = 'SELECT * FROM ROLE';
        $roles = $conn->query($sql)->fetchAll();
        //\Symfony\Component\VarDumper\VarDumper::dump($container);die();

        foreach ($roles as $role) {
            $requestMatcher = new \Symfony\Component\HttpFoundation\RequestMatcher($role['pattern']);
            $roles = array($role['role']);

            $container->getDefinition('security.access_map')
                ->addMethodCall('add', array($requestMatcher, $roles));
        }
    }
}
