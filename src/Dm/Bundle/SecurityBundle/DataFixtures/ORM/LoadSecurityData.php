<?php

/*
 * 基于MIT开源协议发布
 *
 * (c) Efeen Cheung <261969254@qq.com>
 *
 * 有事请联系QQ:261969254, 微信:efeencheung, Github:efeencheung
 */

namespace Dm\Bundle\SecurityBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Dm\Bundle\SecurityBundle\Entity\Role;
use Dm\Bundle\SecurityBundle\Entity\AccessControl;

class LoadSecurityData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $superAdminRole = new Role();
        $superAdminRole->setName('超级管理员');
        $superAdminRole->setRole('ROLE_SUPER_ADMIN');
        $manager->persist($superAdminRole);
        $manager->flush();
        $this->addReference('super-admin-role', $superAdminRole);

        $adminRole = new Role();
        $adminRole->setName('管理员');
        $adminRole->setRole('ROLE_ADMIN');
        $adminRole->setParent($superAdminRole);
        $manager->persist($adminRole);
        $manager->flush();
        $this->addReference('admin-role', $adminRole);

        $userRole = new Role();
        $userRole->setName('普通用户');
        $userRole->setRole('ROLE_USER');
        $userRole->setParent($adminRole);
        $manager->persist($userRole);
        $manager->flush();
        $this->addReference('user-role', $userRole);

        $userAccessControl = new AccessControl();
        $userAccessControl->setName('用户管理');
        $userAccessControl->setPath('^/admin/user');
        $userAccessControl->addRole($adminRole);
        $manager->persist($userAccessControl);
        $manager->flush();

        $roleAccessControl = new AccessControl();
        $roleAccessControl->setName('角色管理');
        $roleAccessControl->setPath('^/admin/role');
        $roleAccessControl->addRole($superAdminRole);
        $manager->persist($roleAccessControl);
        $manager->flush();

        $accessAccessControl = new AccessControl();
        $accessAccessControl->setName('访问控制管理');
        $accessAccessControl->setPath('^/admin/accesscontrol');
        $accessAccessControl->addRole($superAdminRole);
        $manager->persist($accessAccessControl);
        $manager->flush();
    }

    public function getOrder()
    {
        return 1;
    }
}
