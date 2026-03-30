<?php

declare(strict_types=1);

namespace App\Form\Discovery;

use App\Dto\Discovery\DiscoveryQuery;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DiscoverySearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('term', SearchType::class, [
                'required' => false,
            ])
            ->add('resourceType', ChoiceType::class, [
                'required' => false,
                'placeholder' => 'All resource types',
                'choices' => [
                    'Project' => 'project',
                    'Offering' => 'offering',
                    'Document' => 'document',
                    'Category' => 'category',
                ],
            ])
            ->add('search', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DiscoveryQuery::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }
}
