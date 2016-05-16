<?php

/*
 * 基于MIT开源协议发布
 *
 * (c) Efeen Cheung <261969254@qq.com>
 *
 * 有事请联系QQ:261969254, 微信:efeencheung, Github:efeencheung
 */

namespace Dm\Bundle\UserBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Dm\Bundle\UserBundle\Entity\User;
use Dm\Bundle\UserBundle\Form\UserType;
use Dm\Bundle\SecurityBundle\Entity\Role;
use Dm\Bundle\MediaBundle\Entity\Image;

/**
 * 用户控制器.
 *
 * @Route("/admin/user")
 */
class UserController extends Controller
{

    /**
     * 显示所有用户.
     *
     * @Route("/", name="user")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $dql = "SELECT u FROM DmUserBundle:User u ";
        $query = $em->createQuery($dql);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, 
            $request->query->getInt('page', 1),
            10
        );

        return array(
            'pagination' => $pagination
        );
    }
    
    /**
     * 创建一个新用户.
     *
     * @Route("/", name="user_create")
     * @Method("POST")
     * @Template("DmUserBundle:User:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new User();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $factory = $this->get('security.encoder_factory');
            $encoder = $factory->getEncoder($entity);
            $password = $encoder->encodePassword($form->get('password')->getData(), $entity->getSalt());
            $entity->setPassword($password);

            $avatar = $entity->getAvatar();
            if ($avatar instanceof Image) {
                $entity->getAvatar()->setFilename($entity->getName() . '-头像');
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            $this->addFlash('success', '用户创建成功');

            return $this->redirect($this->generateUrl('user_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * 生成一个创建用户的表单.
     *
     * @param User $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(User $entity)
    {
        $form = $this->createForm(new UserType(), $entity, array(
            'action' => $this->generateUrl('user_create'),
            'method' => 'POST',
            'validation_groups' => array('create'),
        ));

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            $form->remove('role');
        }

        return $form;
    }

    /**
     * 显示一个用户创建表单
     *
     * @Route("/new", name="user_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new User();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * 显示一个User的详细信息.
     *
     * @Route("/{id}", name="user_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DmUserBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('用户数据不存在.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * 显示一个用户的编辑表单.
     *
     * @Route("/{id}/edit", name="user_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DmUserBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('用户 数据不存在.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * 生成一个用户的编辑表单.
     *
     * @param User $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(User $entity)
    {
        $form = $this->createForm(new UserType(), $entity, array(
            'action' => $this->generateUrl('user_update', array('id' => $entity->getId())),
            'method' => 'PUT',
            'validation_groups' => array('edit'),
        ));

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            $form->remove('role');
        }

        return $form;
    }
    
    /**
     * 更新一条用户数据.
     *
     * @Route("/{id}", name="user_update")
     * @Method("PUT")
     * @Template("DmUserBundle:User:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DmUserBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('User数据不存在.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $password = $entity->getPassword();

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $newPassword = $editForm->get('password')->getData();
            // 如果没有传入Password, 则删除Password的更新操作
            if (!$newPassword) {
                $entity->setPassword($password);
            } else {
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($entity);
                $password = $encoder->encodePassword($newPassword, $entity->getSalt());
                $entity->setPassword($password);
            }

            $avatar = $entity->getAvatar();
            if ($avatar instanceof Image) {
                $entity->getAvatar()->setFilename($entity->getName());
            }	

            $em->flush();

            $this->addFlash('success', '用户更新成功');

            return $this->redirect($this->generateUrl('user_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    
    /**
     * 删除一条User数据.
     *
     * @Route("/{id}", name="user_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('DmUserBundle:User')->find($id);
            $role = $entity->getRole();

            if ($role instanceof Role && $entity->getRole()->getRole() == 'ROLE_SUPER_ADMIN') {
                $this->addFlash('warning', '超级管理员无法删除');

                return $this->redirect($this->generateUrl('user_show', array('id'=>$id)));
            }

            if (!$entity) {
                throw $this->createNotFoundException('用户数据不存在.');
            }

            $em->remove($entity);
            $em->flush();

            $this->addFlash('success', '用户删除成功');
        }

        return $this->redirect($this->generateUrl('user'));
    }

    /**
     * 创建一个用户的删除表单.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('user_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
