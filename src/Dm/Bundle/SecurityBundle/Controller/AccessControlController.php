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
use Dm\Bundle\SecurityBundle\Entity\AccessControl;
use Dm\Bundle\SecurityBundle\Form\AccessControlType;

/**
 * 访问控制控制器.
 *
 * @Route("/admin/accesscontrol")
 */
class AccessControlController extends Controller
{

    /**
     * 显示所有访问控制.
     *
     * @Route("/", name="accesscontrol")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $dql = "SELECT a FROM DmSecurityBundle:AccessControl a ";
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
     * 创建一个新访问控制.
     *
     * @Route("/", name="accesscontrol_create")
     * @Method("POST")
     * @Template("DmSecurityBundle:AccessControl:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new AccessControl();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            $this->addFlash('success', '访问控制创建成功');

            return $this->redirect($this->generateUrl('accesscontrol_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * 生成一个创建访问控制的表单.
     *
     * @param AccessControl $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(AccessControl $entity)
    {
        $form = $this->createForm(new AccessControlType(), $entity, array(
            'action' => $this->generateUrl('accesscontrol_create'),
            'method' => 'POST',
        ));

        return $form;
    }

    /**
     * 显示一个访问控制创建表单
     *
     * @Route("/new", name="accesscontrol_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new AccessControl();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * 显示一个AccessControl的详细信息.
     *
     * @Route("/{id}", name="accesscontrol_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DmSecurityBundle:AccessControl')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('访问控制数据不存在.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * 显示一个访问控制的编辑表单.
     *
     * @Route("/{id}/edit", name="accesscontrol_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DmSecurityBundle:AccessControl')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('访问控制 数据不存在.');
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
     * 生成一个访问控制的编辑表单.
     *
     * @param AccessControl $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(AccessControl $entity)
    {
        $form = $this->createForm(new AccessControlType(), $entity, array(
            'action' => $this->generateUrl('accesscontrol_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        return $form;
    }
    
    /**
     * 更新一条访问控制数据.
     *
     * @Route("/{id}", name="accesscontrol_update")
     * @Method("PUT")
     * @Template("DmSecurityBundle:AccessControl:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DmSecurityBundle:AccessControl')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('AccessControl数据不存在.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            $this->addFlash('success', '访问控制更新成功');

            return $this->redirect($this->generateUrl('accesscontrol_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    
    /**
     * 删除一条AccessControl数据.
     *
     * @Route("/{id}", name="accesscontrol_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('DmSecurityBundle:AccessControl')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('访问控制数据不存在.');
            }

            $em->remove($entity);
            $em->flush();

            $this->addFlash('success', '访问控制删除成功');
        }

        return $this->redirect($this->generateUrl('accesscontrol'));
    }

    /**
     * 创建一个访问控制的删除表单.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('accesscontrol_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
