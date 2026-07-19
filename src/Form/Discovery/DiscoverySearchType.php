<?php

declare(strict_types=1);

namespace App\Discovering\Form\Discovery;

use App\Discovering\Dto\Discovery\DiscoveryMode;
use App\Discovering\Dto\Discovery\DiscoveryQuery;
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
        $query = $builder->getData() instanceof DiscoveryQuery
            ? $builder->getData()
            : new DiscoveryQuery();

        $status = is_scalar($query->filters['status'] ?? null)
            ? (string) $query->filters['status']
            : '';

        $builder
            ->add('query', SearchType::class, [
                'required' => false,
                'mapped' => false,
                'data' => $query->query,
            ])
            ->add('mode', ChoiceType::class, [
                'mapped' => false,
                'data' => $query->mode,
                'choices' => [
                    'Relevance' => DiscoveryMode::RELEVANCE,
                    'Governance' => DiscoveryMode::GOVERNANCE,
                    'Operations' => DiscoveryMode::OPERATIONS,
                    'Exploration' => DiscoveryMode::EXPLORATION,
                ],
            ])
            ->add('resource', ChoiceType::class, [
                'mapped' => false,
                'data' => $query->resource,
                'choices' => [
                    'Global' => 'global',
                    'Project' => 'project',
                    'Offering' => 'offering',
                    'Document' => 'document',
                ],
            ])
            ->add('status', ChoiceType::class, [
                'required' => false,
                'mapped' => false,
                'placeholder' => 'Any status',
                'data' => '' === $status ? null : $status,
                'choices' => [
                    'Active' => 'active',
                    'Draft' => 'draft',
                    'Archived' => 'archived',
                ],
            ])
            ->add('limit', IntegerType::class, [
                'mapped' => false,
                'data' => $query->limit,
            ])
            ->add('offset', IntegerType::class, [
                'mapped' => false,
                'data' => $query->offset,
            ])
            ->add('project_weight', TextType::class, $this->weightOptions($query, 'project', 'Project weight'))
            ->add('offering_weight', TextType::class, $this->weightOptions($query, 'offering', 'Offering weight'))
            ->add('document_weight', TextType::class, $this->weightOptions($query, 'document', 'Document weight'))
            ->add('playbook_weight', TextType::class, $this->weightOptions($query, 'playbook', 'Playbook weight'))
            ->add('briefing_weight', TextType::class, $this->weightOptions($query, 'briefing', 'Briefing weight'));
    }

    /**
     * Builds and configures the Symfony form definition for this discovery input surface.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DiscoveryQuery::class,
            'method' => 'GET',
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
    }

    /**
     * @return array{required: false, mapped: false, empty_data: '', label: string, data: string}
     */
    private function weightOptions(DiscoveryQuery $query, string $resource, string $label): array
    {
        $value = $query->resourceWeights[$resource] ?? null;

        return [
            'required' => false,
            'mapped' => false,
            'empty_data' => '',
            'label' => $label,
            'data' => is_float($value) || is_int($value) ? (string) $value : '',
        ];
    }
}
