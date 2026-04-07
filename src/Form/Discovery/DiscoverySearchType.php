<?php
declare(strict_types=1);

namespace App\Form\Discovery;

use App\Dto\Discovery\DiscoveryMode;
use App\Dto\Discovery\DiscoveryQuery;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 * Builds the Symfony form type for the discovery search input surface.
 */
final class DiscoverySearchType extends AbstractType
{
    /**
     * Builds and configures the Symfony form definition for this discovery input surface.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $status = $builder->getData() instanceof DiscoveryQuery
            ? (string) (($builder->getData()->filters['status'] ?? ''))
            : '';

        $builder
            ->add('query', SearchType::class, ['required' => false])
            ->add('mode', ChoiceType::class, [
                'choices' => [
                    'Relevance' => DiscoveryMode::RELEVANCE,
                    'Governance' => DiscoveryMode::GOVERNANCE,
                    'Operations' => DiscoveryMode::OPERATIONS,
                    'Exploration' => DiscoveryMode::EXPLORATION,
                ],
            ])
            ->add('resource', ChoiceType::class, ['choices' => ['Global' => 'global', 'Project' => 'project', 'Offering' => 'offering', 'Document' => 'document']])
            ->add('status', ChoiceType::class, [
                'required' => false,
                'mapped' => false,
                'placeholder' => 'Any status',
                'data' => $status === '' ? null : $status,
                'choices' => [
                    'Active' => 'active',
                    'Draft' => 'draft',
                    'Archived' => 'archived',
                ],
            ])
            ->add('limit', IntegerType::class)
            ->add('offset', IntegerType::class)
            ->add('project_weight', TextType::class, [
                'required' => false,
                'mapped' => false,
                'empty_data' => '',
                'label' => 'Project weight',
            ])
            ->add('offering_weight', TextType::class, [
                'required' => false,
                'mapped' => false,
                'empty_data' => '',
                'label' => 'Offering weight',
            ])
            ->add('document_weight', TextType::class, [
                'required' => false,
                'mapped' => false,
                'empty_data' => '',
                'label' => 'Document weight',
            ])
            ->add('playbook_weight', TextType::class, [
                'required' => false,
                'mapped' => false,
                'empty_data' => '',
                'label' => 'Playbook weight',
            ])
            ->add('briefing_weight', TextType::class, [
                'required' => false,
                'mapped' => false,
                'empty_data' => '',
                'label' => 'Briefing weight',
            ]);
    }

    /**
     * Builds and configures the Symfony form definition for this discovery input surface.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => DiscoveryQuery::class, 'method' => 'GET', 'csrf_protection' => false]);
    }
}
