<?php

/*
 * 基于MIT开源协议发布
 *
 * (c) Efeen Cheung <261969254@qq.com>
 *
 * 有事请联系QQ:261969254, 微信:efeencheung, Github:efeencheung
 */

namespace Dm\Bundle\UserBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Dm\Bundle\UserBundle\Entity\User;

class LoadSecurityData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $normalUser = new User();
        $encoder = $this->container->get('security.encoder_factory')->getEncoder($normalUser);

        $normalUser->setUsername('normaluser');
        $normalUser->setPassword($encoder->encodePassword('normaluser', $normalUser->getSalt()));
        $normalUser->setShowname('普通用户');
        $normalUser->setRole($manager->merge($this->getReference('user-role')));
        $manager->persist($normalUser);

        $adminUser = new User();
        $adminUser->setUsername('admin');
        $adminUser->setPassword($encoder->encodePassword('admin', $adminUser->getSalt()));
        $adminUser->setShowname('管理员');
        $adminUser->setRole($manager->merge($this->getReference('admin-role')));
        $manager->persist($adminUser);

        $superAdminUser =  new User();
        $superAdminUser->setUsername('superadmin');
        $superAdminUser->setPassword($encoder->encodePassword('superadmin', $superAdminUser->getSalt()));
        $superAdminUser->setShowname('超级管理员');
        $superAdminUser->setRole($manager->merge($this->getReference('super-admin-role')));
        $manager->persist($superAdminUser);

        $manager->flush();
    }

    public function getOrder()
    {
        return 2;
    }
}
