<?php

namespace Dm\Bundle\AttachmentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class PictureAdminController extends Controller
{
    /**
     * @Route("/admin/picture/{id}/ajaxupdate", name="admin_picture_ajaxupdate" )
     * @Template()
     */
    public function ajaxUpdateAction()
    {
        return array();    
    }
}
