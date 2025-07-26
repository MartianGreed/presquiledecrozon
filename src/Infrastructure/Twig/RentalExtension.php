<?php

namespace App\Infrastructure\Twig;

use App\Entity\Rental\Rental;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class RentalExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('rental_markers_list', [$this, 'getMarkersList']),
        ];
    }

    /**
     * @param PaginationInterface<int, Rental> $pagination
     *
     * @return string
     * @throws \JsonException
     */
    public function getMarkersList(PaginationInterface $pagination): string
    {
        $items = [];

        foreach ($pagination as $rental) {
            $items[] = [
                'id' => $rental->getId(),
                'lat' => $rental->getGeolocation()?->getCoordinates()['lat'],
                'lng' => $rental->getGeolocation()?->getCoordinates()['lng'],
                'amount' => $rental->getWeeklyRate()?->__toString() . '/sem',
                'title' => $rental->getDescription()?->getTitle(),
            ];
        }

        return \json_encode($items, JSON_THROW_ON_ERROR);
    }
}