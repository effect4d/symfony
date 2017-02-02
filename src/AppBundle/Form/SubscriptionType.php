<?php 

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SubscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {        
        $builder->add('type', ChoiceType::class, [
            'choices'  => [
                'Not subscribe' => 'NO',
                'Email' => $options['email'],
                'Phone' => $options['phone'],
            ],
            'label' => 'Notice',
        ]);
            
    }

    public function configureOptions(OptionsResolver $resolver)
    {        
        $resolver->setRequired('email');
        $resolver->setRequired('phone');
    }
}
