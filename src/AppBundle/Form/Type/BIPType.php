<?php
namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class BIPType extends AbstractType{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name');
        $builder->add('url');
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'AppBundle\Entity\Bip',
        );
    }
}

















