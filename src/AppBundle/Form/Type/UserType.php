<?php
namespace AppBundle\Form\Type;

use FOS\UserBundle\Util\LegacyFormHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
public function buildForm(FormBuilderInterface $builder, array $options)
{
    $builder
    ->add('email')
    ->add('username')
    ->add('plainPassword');
}

public function configureOptions(OptionsResolver $resolver)
{
    $resolver->setDefaults(array(
    'data_class' => 'UserBundle\Entity\User',
    'csrf_token_id' => 'registration',
    'intention'  => 'registration',
    ));
}