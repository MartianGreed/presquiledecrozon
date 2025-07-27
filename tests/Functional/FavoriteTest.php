<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Repository\Rental\RentalRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class FavoriteTest extends WebTestCase
{
    private function clearFavoriteList(): void
    {
        $container = static::getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $connection = $em->getConnection();
        $connection->executeStatement('TRUNCATE TABLE favorite CASCADE');
    }

    public function testPersonaCanAddRentalToFavoriteList(): void
    {
        $client = static::createClient();
        $this->clearFavoriteList();

        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        /** @var RentalRepository $rentalRepository */
        $rentalRepository = static::getContainer()->get(RentalRepository::class);

        $persona = $userRepository->findOneBy([]);
        $this->assertNotNull($persona, 'Test persona should exist');

        $rental = $rentalRepository->findOneBy([]);
        $this->assertNotNull($rental, 'At least one rental should exist');

        $client->loginUser($persona);

        $client->request(
            'POST',
            '/mon-compte/coup-de-coeur/' . $rental->getId()
        );

        $this->assertResponseIsSuccessful();
        $content = $client->getResponse()->getContent();
        if ($content === false) {
            $this->fail('Response content is false');
        }
        $response = json_decode($content, true);
        if (! is_array($response)) {
            $this->fail('Response is not a valid JSON array');
        }

        $this->assertArrayHasKey('favorite', $response);
        $this->assertIsString($response['favorite']);
        $this->assertEquals($rental->getId(), $response['favorite']);

        $client->request(
            'POST',
            '/mon-compte/coup-de-coeur/' . $rental->getId()
        );

        $this->assertResponseIsSuccessful();
        $content = $client->getResponse()->getContent();
        if ($content === false) {
            $this->fail('Response content is false');
        }
        $response = json_decode($content, true);
        if (! is_array($response)) {
            $this->fail('Response is not a valid JSON array');
        }
        $this->assertArrayHasKey('removed', $response);
        $this->assertIsBool($response['removed']);
        $this->assertTrue($response['removed']);
    }

    public function testPersonaCanCheckIfRentalIsInFavoriteList(): void
    {
        $client = static::createClient();
        $this->clearFavoriteList();

        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        /** @var RentalRepository $rentalRepository */
        $rentalRepository = static::getContainer()->get(RentalRepository::class);

        $persona = $userRepository->findOneBy([]);
        $this->assertNotNull($persona, 'Test persona should exist');

        $rental = $rentalRepository->findOneBy([]);
        $this->assertNotNull($rental, 'At least one rental should exist');

        $client->loginUser($persona);

        $client->request(
            'GET',
            '/mon-compte/is-favorite/' . $rental->getId()
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(204);

        $client->request(
            'POST',
            '/mon-compte/coup-de-coeur/' . $rental->getId()
        );

        $this->assertResponseIsSuccessful();

        $client->request(
            'GET',
            '/mon-compte/is-favorite/' . $rental->getId()
        );

        $this->assertResponseIsSuccessful();
        $content = $client->getResponse()->getContent();
        if ($content === false) {
            $this->fail('Response content is false');
        }
        $response = json_decode($content, true);
        if (! is_array($response)) {
            $this->fail('Response is not a valid JSON array');
        }
        $this->assertArrayHasKey('favorite', $response);
    }

    public function testPersonaCanViewFavoriteListPage(): void
    {
        $client = static::createClient();
        $this->clearFavoriteList();

        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        /** @var RentalRepository $rentalRepository */
        $rentalRepository = static::getContainer()->get(RentalRepository::class);

        $persona = $userRepository->findOneBy([]);
        $this->assertNotNull($persona, 'Test persona should exist');

        $rentals = $rentalRepository->findBy([], null, 2);
        $this->assertCount(2, $rentals, 'Need at least 2 rentals for this test');

        $client->loginUser($persona);

        foreach ($rentals as $rental) {
            $client->request(
                'POST',
                '/mon-compte/coup-de-coeur/' . $rental->getId()
            );
            $this->assertResponseIsSuccessful();
            $content = $client->getResponse()->getContent();
            if ($content === false) {
                $this->fail('Response content is false');
            }
            $response = json_decode($content, true);
            if (! is_array($response)) {
                $this->fail('Response is not a valid JSON array');
            }
            $this->assertArrayHasKey('favorite', $response, 'Should return favorite in response');
        }

        $crawler = $client->request('GET', '/mon-compte/coups-de-coeur');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Coups de coeur'); // UI uses French term

        $pageContent = $crawler->text();
        if (str_contains($pageContent, "Vous n'avez pas encore sauvegardé d'annonces")) {
            $this->fail("No favorites found on the page even though we just added them");
        }

        $rentalListItems = $crawler->filter('.rental_list__item');
        $this->assertGreaterThanOrEqual(2, $rentalListItems->count(), 'Should have at least 2 rentals in favorites');
    }

    public function testAnonymousUserCannotAddRentalToFavoriteList(): void
    {
        $client = static::createClient();
        $this->clearFavoriteList();

        /** @var RentalRepository $rentalRepository */
        $rentalRepository = static::getContainer()->get(RentalRepository::class);

        $rental = $rentalRepository->findOneBy([]);
        $this->assertNotNull($rental, 'At least one rental should exist');

        $client->request(
            'POST',
            '/mon-compte/coup-de-coeur/' . $rental->getId()
        );

        $this->assertResponseRedirects('/login');
    }
}
