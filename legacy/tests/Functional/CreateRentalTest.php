<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Rental\Rental;
use App\Repository\Data\RentalTypeRepository;
use App\Repository\Data\TownRepository;
use App\Repository\Rental\RentalRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[Group('e2e')]
final class CreateRentalTest extends WebTestCase
{
    private function clearRentals(): void
    {
        $container = static::getContainer();
        /** @var RentalRepository $rentalRepository */
        $rentalRepository = $container->get(RentalRepository::class);
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get(EntityManagerInterface::class);

        // Find and remove all draft rentals using the repository
        $draftRentals = $rentalRepository->findBy([
            'status' => 'draft',
        ]);
        foreach ($draftRentals as $rental) {
            $entityManager->remove($rental);
        }

        // Also remove any rental with our test slug to avoid conflicts
        $existingRental = $rentalRepository->findOneBy([
            'slug' => 'test-rental-creation',
        ]);
        if ($existingRental) {
            $entityManager->remove($existingRental);
        }

        $entityManager->flush();
    }

    public function testRentalCreationFlow(): void
    {
        $client = static::createClient();
        $this->clearRentals();

        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        /** @var RentalTypeRepository $rentalTypeRepository */
        $rentalTypeRepository = static::getContainer()->get(RentalTypeRepository::class);
        /** @var RentalRepository $rentalRepository */
        $rentalRepository = static::getContainer()->get(RentalRepository::class);

        $testTitle = 'Test Rental ' . uniqid();

        $user = $userRepository->findOneBy([]);
        $this->assertNotNull($user, 'Test user should exist');

        $rentalType = $rentalTypeRepository->findOneBy([]);
        $this->assertNotNull($rentalType, 'At least one rental type should exist');

        $client->loginUser($user);

        // Step 1: Configuration
        $crawler = $client->request('GET', '/deposez-votre-annonce/configuration');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Etape 1: On se jette à l\'eau');

        $form = $crawler->selectButton('Sauvegarder et continuer')->form();
        $form['rental_informations[rentalType]'] = (string) $rentalType->getId();
        $form['rental_informations[peopleCount]'] = '4';
        $form['rental_informations[bedroomCount]'] = '2';

        $client->submit($form);
        $this->assertResponseRedirects('/deposez-votre-annonce/equipements');

        // Step 2: Furnitures
        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Sauvegarder et continuer')->form();
        $client->submit($form);
        $this->assertResponseRedirects('/deposez-votre-annonce/description');

        // Step 3: Description
        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Sauvegarder et continuer')->form();
        $form['rental_description[title]'] = $testTitle;
        $form['rental_description[description]'] = 'This is a test rental created through functional testing to ensure the complete flow works correctly.';

        $client->submit($form);
        $this->assertResponseRedirects('/deposez-votre-annonce/adresse');

        // Step 4: Address
        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful();

        /** @var TownRepository $townRepository */
        $townRepository = static::getContainer()->get(TownRepository::class);
        $town = $townRepository->findOneBy([]);
        $this->assertNotNull($town, 'At least one town should exist');

        $form = $crawler->selectButton('Sauvegarder et continuer')->form();
        $form['rental_address[address]'] = '123 Test Street';
        $form['rental_address[town]'] = (string) $town->getId();

        $client->submit($form);
        $this->assertResponseRedirects('/deposez-votre-annonce/carte');

        // Step 5: Map (skip geolocation for test - in real app it's async)
        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful();

        // Since we can't execute JavaScript in tests, we'll navigate directly to the next step
        // In a real scenario, the fetch-geolocation controller would handle this
        $crawler = $client->request('GET', '/deposez-votre-annonce/photos');

        // Step 6: Pictures (skip actual upload, just navigate)
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Détails de votre location');

        // Navigate to next step without uploading (for test purposes)
        $crawler = $client->request('GET', '/deposez-votre-annonce/disponibilites');

        // Step 7: Availabilities
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Sauvegarder et continuer')->form();
        // Just submit with default values

        $client->submit($form);
        $this->assertResponseRedirects('/deposez-votre-annonce/calendrier');

        // Step 8: Calendar
        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Sauvegarder et continuer')->form();
        $client->submit($form);
        $this->assertResponseRedirects('/deposez-votre-annonce/taxes');

        // Note: Stopping test here due to Price form complexity in later steps
        // The taxes, prices, and conditions steps require Price entity initialization

        // Verify rental was created up to this point
        $rental = $rentalRepository->findLatestDraftRentalForUser((string) $user->getId());
        $this->assertInstanceOf(Rental::class, $rental);
        $this->assertNotNull($rental->getDescription(), 'Description should be saved');
        $this->assertSame($testTitle, $rental->getDescription()->getTitle());
        $this->assertNotNull($rental->getAddress(), 'Address should be saved');
        $this->assertSame('123 Test Street', $rental->getAddress()->getAddress());
        $townId = $town->getId();
        $this->assertNotNull($townId);
        $rentalTown = $rental->getAddress()->getTown();
        $this->assertNotNull($rentalTown);
        $this->assertSame($townId, $rentalTown->getId());
        $this->assertNotNull($rental->getConfiguration(), 'Configuration should be saved');
        $this->assertNotNull($rental->getPreferences(), 'Preferences should be saved');
    }

    public function testUserMustBeAuthenticatedToCreateRental(): void
    {
        $client = static::createClient();

        $client->request('GET', '/deposez-votre-annonce/configuration');
        $this->assertResponseRedirects('/login');
    }

    public function testDraftRentalIsReusedForSameUser(): void
    {
        $client = static::createClient();
        $this->clearRentals();

        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        /** @var RentalRepository $rentalRepository */
        $rentalRepository = static::getContainer()->get(RentalRepository::class);

        $user = $userRepository->findOneBy([]);
        $this->assertNotNull($user, 'Test user should exist');

        $client->loginUser($user);

        // First visit creates a draft rental
        $client->request('GET', '/deposez-votre-annonce/configuration');
        $this->assertResponseIsSuccessful();

        $rental1 = $rentalRepository->findLatestDraftRentalForUser((string) $user->getId());
        $this->assertInstanceOf(Rental::class, $rental1);

        // Second visit should reuse the same draft
        $client->request('GET', '/deposez-votre-annonce/configuration');
        $this->assertResponseIsSuccessful();

        $rental2 = $rentalRepository->findLatestDraftRentalForUser((string) $user->getId());
        $this->assertInstanceOf(Rental::class, $rental2);
        $this->assertSame($rental1->getId(), $rental2->getId(), 'Same draft rental should be reused');
    }
}
