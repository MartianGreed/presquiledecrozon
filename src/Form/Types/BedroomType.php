<?php

namespace App\Form\Types;

use App\Repository\Data\BedRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

final class BedroomType extends AbstractType
{
    private BedRepository $bedRepository;

    public function __construct(BedRepository $bedRepository)
    {
        $this->bedRepository = $bedRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $beds = $this->bedRepository->findAll();

        foreach ($beds as $bed) {
            $builder->add($bed->getId(), CounterType::class, [
                'label' => $bed->getLabel(),
                'attr' => [
                    'data-action' => 'form-types--bedroom#changeBedCount'
                ]
            ]);
        }
    }

    public function getBlockPrefix()
    {
        return 'bedroom';
    }
}