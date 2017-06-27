<?php

namespace Dm\Bundle\CarouselBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Dm\Bundle\CarouselBundle\Entity\CarouselGroup;
use Dm\Bundle\CarouselBundle\Form\CarouselGroupType;

/**
 * 轮播组控制器.
 *
 * @Route("/admin/carouselgroup")
 */
class CarouselGroupAdminController extends Controller
{

    /**
     * 显示所有轮播组.
     *
     * @Route("/", name="admin_carouselgroup")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $dql = "SELECT c FROM DmCarouselBundle:CarouselGroup c ";
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
     * 创建一个新轮播组.
     *
     * @Route("/", name="admin_carouselgroup_create")
     * @Method("POST")
     * @Template("DmCarouselBundle:CarouselGroupAdmin:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new CarouselGroup();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            $this->addFlash('success', '轮播组创建成功');

            return $this->redirect($this->generateUrl('admin_carouselgroup'));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * 生成一个创建轮播组的表单.
     *
     * @param CarouselGroup $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(CarouselGroup $entity)
    {
        $form = $this->createForm(new CarouselGroupType(), $entity, array(
            'action' => $this->generateUrl('admin_carouselgroup_create'),
            'method' => 'POST',
        ));

        return $form;
    }

    /**
     * 显示一个轮播组创建表单
     *
     * @Route("/new", name="admin_carouselgroup_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new CarouselGroup();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * 显示一个CarouselGroup的详细信息.
     *
     * @Route("/{id}", name="admin_carouselgroup_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DmCarouselBundle:CarouselGroup')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('轮播组数据不存在.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * 显示一个轮播组的编辑表单.
     *
     * @Route("/{id}/edit", name="admin_carouselgroup_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DmCarouselBundle:CarouselGroup')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('轮播组 数据不存在.');
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
     * 生成一个轮播组的编辑表单.
     *
     * @param CarouselGroup $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(CarouselGroup $entity)
    {
        $form = $this->createForm(new CarouselGroupType(), $entity, array(
            'action' => $this->generateUrl('admin_carouselgroup_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        return $form;
    }
    
    /**
     * 更新一条轮播组数据.
     *
     * @Route("/{id}", name="admin_carouselgroup_update")
     * @Method("PUT")
     * @Template("DmCarouselBundle:CarouselGroupAdmin:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DmCarouselBundle:CarouselGroup')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('CarouselGroup数据不存在.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            $this->addFlash('success', '轮播组更新成功');

            return $this->redirect($this->generateUrl('admin_carouselgroup_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    
    /**
     * 删除一条CarouselGroup数据.
     *
     * @Route("/{id}", name="admin_carouselgroup_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('DmCarouselBundle:CarouselGroup')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('轮播组数据不存在.');
            }

            $em->remove($entity);
            $em->flush();

            $this->addFlash('success', '轮播组删除成功');
        }

        return $this->redirect($this->generateUrl('admin_carouselgroup'));
    }

    /**
     * 创建一个轮播组的删除表单.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_carouselgroup_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
