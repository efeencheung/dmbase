<?php

namespace Dm\Bundle\AttachmentBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\ButtonTypeInterface;
use Symfony\Component\Form\SubmitButtonTypeInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Dm\Bundle\AttachmentBundle\Form\Type\ImageType;


class PictureType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', ImageType::class) ;
    }

    /**
     * Pass the image URL to the view
     *
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
		$image = $form->getData();
        if ($image !== null) {
            $path = $image->getWebPath();
            $vars = $view['file']->vars;
            $vars['value'] = $path;
            $view['file']->vars = $vars;
        }
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'attr' => array('data-parsley-validate'=>'data-parsley-validate'),
            'csrf_protection' => false,
            'data_class' => 'Dm\Bundle\AttachmentBundle\Entity\Picture'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'dm_image';
    }
}
