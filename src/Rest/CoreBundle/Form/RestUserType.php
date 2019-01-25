<?php

namespace Kunstmaan\Rest\CoreBundle\Form;

use Kunstmaan\AdminBundle\Form\UserType as AdminUserType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * UserType defines the form used for {@link User}
 */
class RestUserType extends AdminUserType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'kuma_rest_core_bundle_user';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array(
            'data_class' => 'Kunstmaan\Rest\CoreBundle\Entity\RestUser',
        ));
    }
}