<?php

namespace App\Controller\Rental;

use App\Entity\Rental\Rental;
use Symfony\Component\HttpFoundation\Request;

trait RentalStepDetectorTrait
{
    /**
     * Determines the next incomplete step in the rental creation process.
     * Returns the route name of the first incomplete step, or null if all steps are complete.
     */
    private function getNextIncompleteStep(Rental $rental): ?string
    {
        // Step 1: Configuration
        if (! $rental->getConfiguration() instanceof \App\Entity\Rental\Configuration) {
            return 'app_create_rental';
        }

        // Step 2: Furnitures - Skip as it's optional

        // Step 3: Description
        if (! $rental->getDescription() instanceof \App\Entity\Rental\Description) {
            return 'app_create_rental_description';
        }

        // Step 4: Address
        if (! $rental->getAddress() instanceof \App\Entity\Rental\Address) {
            return 'app_create_rental_address';
        }

        // Step 5: Geolocation (depends on address being set)
        if (! $rental->getGeolocation() instanceof \App\Entity\Rental\Geolocation) {
            return 'app_create_rental_map';
        }

        // Step 6: Gallery/Pictures
        if (! $rental->getGallery() instanceof \App\Entity\Rental\Gallery) {
            return 'app_create_rental_pictures';
        }

        // Step 7: Preferences/Availabilities
        if (! $rental->getPreferences() instanceof \App\Entity\Rental\Preferences) {
            return 'app_create_rental_availabilities';
        }

        // Step 8: Calendar/Unavailabilities - Skip as it's optional

        // Step 9: Tax
        if (! $rental->getTax() instanceof \App\Entity\Rental\Tax) {
            return 'app_create_rental_taxes';
        }

        // Step 10: Prices
        if ($rental->getPrices()->isEmpty() || ! $rental->getWeeklyRate() || ! $rental->getDailyRate()) {
            return 'app_create_rental_prices';
        }

        // Step 11: Conditions
        if (! $rental->getCondition() instanceof \App\Entity\Rental\Condition) {
            return 'app_create_rental_conditions';
        }

        // All steps complete
        return null;
    }

    /**
     * Checks if the request is coming from outside the rental creation workflow.
     * Returns true if the referrer is not from '/deposez-votre-annonce' path.
     */
    private function isComingFromOutsideWorkflow(Request $request): bool
    {
        $referer = $request->headers->get('referer', '');

        // If no referer, assume coming from outside
        if ($referer === null || $referer === '' || $referer === '0') {
            return true;
        }

        // Check if referer contains the rental creation path
        return ! str_contains($referer, '/deposez-votre-annonce');
    }
}
