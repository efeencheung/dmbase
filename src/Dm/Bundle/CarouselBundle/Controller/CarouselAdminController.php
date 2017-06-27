<?php

namespace Dm\Bundle\CarouselBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Dm\Bundle\CarouselBundle\Entity\Carousel;
use Dm\Bundle\CarouselBundle\Form\CarouselType;

/**
 * 轮播图控制器.
 *
 * @Route("/admin/carousel")
 */
class CarouselAdminController extends Controller
{

    /**
     * 显示所有轮播图.
     *
     * @Route("/", name="admin_carousel")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $qb = $em->createQueryBuilder();
        $qb ->select('c')
            ->from('DmCarouselBundle:Carousel', 'c')
            ->orderBy('c.carouselGroup', 'ASC')
            ;

        /*
         * 分类条件查询
         */
        $carouselGroupId = $request->query->get('group');
        if ($carouselGroupId) {
            $carouselGroup = $em->getRepository('DmCarouselBundle:CarouselGroup')->find($carouselGroupId);
            $qb ->where('c.carouselGroup = ?1')
                ->orderBy('c.sequence', 'DESC')
                ->setParameter(1, $carouselGroup);
        }

        $query = $qb->getQuery();
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, 
            $request->query->getInt('page', 1),
            10
        );

        return array(
            'pagination' => $pagination,
            'group' => $carouselGroupId,
        );
    }
    
    /**
     * 创建一个新轮播图.
     *
     * @Route("/", name="admin_carousel_create")
     * @Method("POST")
     * @Template("DmCarouselBundle:CarouselAdmin:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Carousel();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            $this->addFlash('success', '轮播图创建成功');

            return $this->redirect($this->generateUrl('admin_carousel'));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * 生成一个创建轮播图的表单.
     *
     * @param Carousel $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Carousel $entity)
    {
        $form = $this->createForm(new CarouselType(), $entity, array(
            'action' => $this->generateUrl('admin_carousel_create'),
            'method' => 'POST',
        ));

        return $form;
    }

    /**
     * 显示一个轮播图创建表单
     *
     * @Route("/new", name="admin_carousel_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Carousel();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * 显示一个Carousel的详细信息.
     *
     * @Route("/{id}", name="admin_carousel_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DmCarouselBundle:Carousel')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('轮播图数据不存在.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * 显示一个轮播图的编辑表单.
     *
     * @Route("/{id}/edit", name="admin_carousel_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DmCarouselBundle:Carousel')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('轮播图 数据不存在.');
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
     * 生成一个轮播图的编辑表单.
     *
     * @param Carousel $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Carousel $entity)
    {
        $form = $this->createForm(new CarouselType(), $entity, array(
            'action' => $this->generateUrl('admin_carousel_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        return $form;
    }
    
    /**
     * 更新一条轮播图数据.
     *
     * @Route("/{id}", name="admin_carousel_update")
     * @Method("PUT")
     * @Template("DmCarouselBundle:CarouselAdmin:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DmCarouselBundle:Carousel')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Carousel数据不存在.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            $this->addFlash('success', '轮播图更新成功');

            return $this->redirect($this->generateUrl('admin_carousel_edit', array('id'=>$id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    
    /**
     * 删除一条Carousel数据.
     *
     * @Route("/{id}", name="admin_carousel_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('DmCarouselBundle:Carousel')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('轮播图数据不存在.');
            }

            $em->remove($entity);
            $em->flush();

            $this->addFlash('success', '轮播图删除成功');
        }

        return $this->redirect($this->generateUrl('admin_carousel'));
    }

    /**
     * 创建一个轮播图的删除表单.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_carousel_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }

    /**
     * Ajax 行内编辑
     *
     * @Route("/inlineUpdate/{id}", name="admin_carousel_inline_update")
     * @Method("POST")
     */
    public function inlineUpdateAction(Request $request, $id)
    {
        $result = array(); // 返回操作结果

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('DmCarouselBundle:Carousel')->find($id);

        $name = $request->request->get('name');
        $value = $request->request->get('value');
        if (is_numeric($value)) {
            $value = intval($value);
        }

        $methodName = 'set' . ucfirst($name);
    
        call_user_method($methodName, $entity, $value);

        $errors = $this->get('validator')->validate($entity);
        if (count($errors) > 0) {
            $errMsgArr = array();
            foreach ($errors as $error) {
                $errMsgArr[] = $error->getMessage();
            }

            $result['status'] = 'error';
            $result['msg'] = implode($errMsgArr, "<br>");

            return new JsonResponse($result);
        }

        $em->persist($entity);
        $em->flush();

        $result['status'] = 'success';

        return new JsonResponse($result);
    }
}
