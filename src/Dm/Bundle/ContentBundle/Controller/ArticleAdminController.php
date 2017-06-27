<?php

namespace Dm\Bundle\ContentBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Dm\Bundle\ContentBundle\Entity\Article;
use Dm\Bundle\ContentBundle\Form\ArticleType;

/**
 * 图文资讯控制器.
 *
 * @Route("/admin/article")
 */
class ArticleAdminController extends Controller
{

    /**
     * 显示所有图文资讯.
     *
     * @Route("/", name="admin_article")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb ->select('a')
            ->from('DmContentBundle:Article', 'a')
            ->andWhere('a.type=1')
            ->orderBy('a.publishedAt', 'DESC')
            ;

        /*
         * 标题条件查询
         */
        $keywords = $request->query->get('keywords');
        if ($keywords) {
            $qb ->andWhere('a.title like ?1')
                ->setParameter(1, '%'.$keywords.'%');
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
            'keywords' => $keywords
        );
    }
    
    /**
     * 创建一个新图文资讯.
     *
     * @Route("/", name="admin_article_create")
     * @Method("POST")
     * @Template("DmContentBundle:ArticleAdmin:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Article();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            $entity->setType(1);
            $content = $this->get('textarea_image_util')->replaceImageString($entity->getContent());
            $entity->setContent($content);

            $em->persist($entity);
            $em->flush();

            $this->addFlash('success', '图文资讯创建成功');

            return $this->redirect($this->generateUrl('admin_article_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * 生成一个创建图文资讯的表单.
     *
     * @param Article $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Article $entity)
    {
        $form = $this->createForm(new ArticleType(), $entity, array(
            'action' => $this->generateUrl('admin_article_create'),
            'method' => 'POST',
        ));

        return $form;
    }

    /**
     * 显示一个图文资讯创建表单
     *
     * @Route("/new", name="admin_article_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Article();
        $form   = $this->createCreateForm($entity);
//        dump($form->createView());die;

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * 显示一个Article的详细信息.
     *
     * @Route("/{id}", name="admin_article_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DmContentBundle:Article')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('图文资讯数据不存在.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * 显示一个图文资讯的编辑表单.
     *
     * @Route("/{id}/edit", name="admin_article_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DmContentBundle:Article')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('图文资讯 数据不存在.');
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
     * 生成一个图文资讯的编辑表单.
     *
     * @param Article $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Article $entity)
    {
        $form = $this->createForm(new ArticleType(), $entity, array(
            'action' => $this->generateUrl('admin_article_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        return $form;
    }
    
    /**
     * 更新一条图文资讯数据.
     *
     * @Route("/{id}", name="admin_article_update")
     * @Method("PUT")
     * @Template("DmContentBundle:ArticleAdmin:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DmContentBundle:Article')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Article数据不存在.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $content = $this->get('textarea_image_util')->replaceImageString($entity->getContent());
            $entity->setContent($content);

            $em->flush();

            $this->addFlash('success', '图文资讯更新成功');

            return $this->redirect($this->generateUrl('admin_article_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    
    /**
     * 删除一条Article数据.
     *
     * @Route("/{id}", name="admin_article_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('DmContentBundle:Article')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('图文资讯数据不存在.');
            }

            $em->remove($entity);
            $em->flush();

            $this->addFlash('success', '图文资讯删除成功');
        }

        return $this->redirect($this->generateUrl('admin_article'));
    }

    /**
     * 创建一个图文资讯的删除表单.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_article_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
