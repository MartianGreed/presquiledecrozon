<?php

namespace App\Form\Types;

use App\Repository\Data\BedRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

final class BedroomType extends AbstractType
{
    public function __construct(
        private readonly BedRepository $bedRepository
    )
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $beds = $this->bedRepository->findAll();

        foreach ($beds as $bed) {
            $label = null === $bed->getSize() ? $bed->getLabel() : sprintf('%s %s', $bed->getLabel(), $bed->getDimensions());
            $builder->add((string) $bed->getId(), CounterType::class, [
                'label' => $label,
                'attr' => [
                    'data-action' => 'form-types--bedroom#changeBedCount',
                ],
            ]);
        }
    }

    public function getBlockPrefix(): string
    {
        return 'bedroom';
    }
}
