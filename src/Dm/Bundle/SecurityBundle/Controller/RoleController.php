<?php

/*
 * 基于MIT开源协议发布
 *
 * (c) Efeen Cheung <261969254@qq.com>
 *
 * 有事请联系QQ:261969254, 微信:efeencheung, Github:efeencheung
 */

namespace Dm\Bundle\SecurityBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Dm\Bundle\SecurityBundle\Entity\Role;
use Dm\Bundle\SecurityBundle\Form\RoleType;

/**
 * 角色控制器.
 *
 * @Route("/admin/role")
 */
class RoleController extends Controller
{

    /**
     * 显示所有角色.
     *
     * @Route("/", name="role")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $dql = "SELECT r FROM DmSecurityBundle:Role r ";
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
     * 创建一个新角色.
     *
     * @Route("/", name="role_create")
     * @Method("POST")
     * @Template("DmSecurityBundle:Role:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Role();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            $this->addFlash('success', '角色创建成功');

            return $this->redirect($this->generateUrl('role_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * 生成一个创建角色的表单.
     *
     * @param Role $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Role $entity)
    {
        $form = $this->createForm(new RoleType(), $entity, array(
            'action' => $this->generateUrl('role_create'),
            'method' => 'POST',
        ));

        return $form;
    }

    /**
     * 显示一个角色创建表单
     *
     * @Route("/new", name="role_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Role();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * 显示一个Role的详细信息.
     *
     * @Route("/{id}", name="role_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DmSecurityBundle:Role')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('角色数据不存在.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * 显示一个角色的编辑表单.
     *
     * @Route("/{id}/edit", name="role_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DmSecurityBundle:Role')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('角色 数据不存在.');
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
     * 生成一个角色的编辑表单.
     *
     * @param Role $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Role $entity)
    {
        $form = $this->createForm(new RoleType(), $entity, array(
            'action' => $this->generateUrl('role_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        return $form;
    }
    
    /**
     * 更新一条角色数据.
     *
     * @Route("/{id}", name="role_update")
     * @Method("PUT")
     * @Template("DmSecurityBundle:Role:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DmSecurityBundle:Role')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Role数据不存在.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            $this->addFlash('success', '角色更新成功');

            return $this->redirect($this->generateUrl('role_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    
    /**
     * 删除一条Role数据.
     *
     * @Route("/{id}", name="role_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('DmSecurityBundle:Role')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('角色数据不存在.');
            }

            $em->remove($entity);
            $em->flush();

            $this->addFlash('success', '角色删除成功');
        }

        return $this->redirect($this->generateUrl('role'));
    }

    /**
     * 创建一个角色的删除表单.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('role_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
