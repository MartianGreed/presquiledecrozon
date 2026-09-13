<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Panther\PantherTestCase;

#[Group('e2e')]
final class FavoriteTest extends PantherTestCase
{
    use BaseE2ETrait;

    public function testPersonaCanAddRentalToFavoriteList(): void
    {
        $email = 'adrienne.garnier@techer.net';
        $password = '123S3curedP4ssw0rd';

        $this->client->request('GET', '/login');
        $this->client->submitForm('Je me connecte', [
            'email' => $email,
            'password' => $password,
        ]);

        sleep(1);

        $this->assertNotEquals('https://127.0.0.1:8000/login', $this->client->getCurrentURL());

        $this->client->request('GET', '/');
        $this->waitForElement('.rental_list__item');

        $crawler = $this->client->getCrawler();
        $rentalItems = $crawler->filter('.rental_list__item');
        $this->assertGreaterThan(0, $rentalItems->count(), 'No rentals found on homepage');

        $firstRentalId = $rentalItems->first()->attr('id');
        if ($firstRentalId === null) {
            $this->fail('No rental ID found');
        }
        $rentalId = str_replace('rental-list-item-', '', $firstRentalId);

        $heartButton = $crawler->filter('#' . $firstRentalId . ' .rental_list__item__content__heart')->first();
        $this->assertCount(1, $crawler->filter('#' . $firstRentalId . ' .rental_list__item__content__heart'), 'Heart button not found');

        $dataController = $heartButton->attr('data-controller');
        $this->assertEquals('rental--favorites', $dataController, 'Heart button should have rental--favorites controller');

        $heartButton->click();

        sleep(2);

        try {
            $this->client->waitFor('#' . $firstRentalId . ' .rental_list__item__content__heart.is-favorite', 10);
        } catch (\Exception $e) {
            $this->takeDebugScreenshot('favorites_not_added');
            throw new \Exception('Heart button did not get is-favorite class. Check if user is logged in and JavaScript is working.', $e->getCode(), $e);
        }

        $this->client->request('GET', '/mon-compte/coups-de-coeur');
        $this->waitForElement('.rental_list__item');

        $favoritesPageCrawler = $this->client->getCrawler();
        $favoriteRental = $favoritesPageCrawler->filter('#rental-list-item-' . $rentalId);
        $this->assertCount(1, $favoriteRental, 'Rental not found in favorites list');
    }

    public function testPersonaCanRemoveRentalFromFavoriteList(): void
    {
        $email = 'theophile.dupre@denis.net';
        $password = '123S3curedP4ssw0rd';

        $this->login($email, $password);

        $this->client->request('GET', '/');
        $this->waitForElement('.rental_list__item');

        $crawler = $this->client->getCrawler();
        $rentalItems = $crawler->filter('.rental_list__item');
        $firstRentalId = $rentalItems->first()->attr('id');

        $heartButton = $crawler->filter('#' . $firstRentalId . ' .rental_list__item__content__heart')->first();
        $heartButton->click();
        $this->client->waitFor('#' . $firstRentalId . ' .rental_list__item__content__heart.is-favorite', 5);

        $heartButton->click();

        $this->client->wait(2);
        $updatedCrawler = $this->client->getCrawler();
        $updatedHeartButton = $updatedCrawler->filter('#' . $firstRentalId . ' .rental_list__item__content__heart')->first();
        $buttonClass = $updatedHeartButton->attr('class');
        $this->assertNotNull($buttonClass, 'Button class should not be null');
        $this->assertStringNotContainsString('is-favorite', $buttonClass, 'Heart button still has is-favorite class');
    }

    public function testAnonymousUserCannotAddRentalToFavoriteList(): void
    {
        $this->client->request('GET', '/');
        $this->waitForElement('.rental_list__item');

        $crawler = $this->client->getCrawler();
        $heartButtons = $crawler->filter('.rental_list__item__content__heart[data-controller="rental--favorites"]');
        $this->assertCount(0, $heartButtons, 'Heart buttons should not have data-controller attribute for non-logged-in users');
    }

    public function testPersonaCanAddMultipleRentalsToFavoriteList(): void
    {
        $email = 'ymasson@legall.com';
        $password = '123S3curedP4ssw0rd';

        $this->login($email, $password);

        $this->client->request('GET', '/');
        $this->waitForElement('.rental_list__item');

        $crawler = $this->client->getCrawler();
        $rentalItems = $crawler->filter('.rental_list__item');
        $this->assertGreaterThanOrEqual(2, $rentalItems->count(), 'Need at least 2 rentals for this test');

        $firstRentalId = $rentalItems->eq(0)->attr('id');
        $secondRentalId = $rentalItems->eq(1)->attr('id');

        $firstHeartButton = $crawler->filter('#' . $firstRentalId . ' .rental_list__item__content__heart')->first();
        $firstHeartButton->click();
        $this->client->waitFor('#' . $firstRentalId . ' .rental_list__item__content__heart.is-favorite', 5);

        $secondHeartButton = $crawler->filter('#' . $secondRentalId . ' .rental_list__item__content__heart')->first();
        $secondHeartButton->click();
        $this->client->waitFor('#' . $secondRentalId . ' .rental_list__item__content__heart.is-favorite', 5);

        $this->client->request('GET', '/mon-compte/coups-de-coeur');
        $this->waitForElement('.rental_list__item');

        $favoritesPageCrawler = $this->client->getCrawler();
        $favoriteItems = $favoritesPageCrawler->filter('.rental_list__item');
        $this->assertGreaterThanOrEqual(2, $favoriteItems->count(), 'Should have at least 2 rentals in favorites');
    }

    public function testFavoriteListPersistsAcrossSessions(): void
    {
        $email = 'gilles09@dijoux.org';
        $password = '123S3curedP4ssw0rd';

        $this->login($email, $password);

        $this->client->request('GET', '/');
        $this->waitForElement('.rental_list__item');

        $crawler = $this->client->getCrawler();
        $firstRentalId = $crawler->filter('.rental_list__item')->first()->attr('id');
        if ($firstRentalId === null) {
            $this->fail('No rental ID found');
        }
        $rentalId = str_replace('rental-list-item-', '', $firstRentalId);

        $heartButton = $crawler->filter('#' . $firstRentalId . ' .rental_list__item__content__heart')->first();
        $heartButton->click();
        $this->client->waitFor('#' . $firstRentalId . ' .rental_list__item__content__heart.is-favorite', 5);

        $this->client->request('GET', '/logout');

        $this->login($email, $password);

        $this->client->request('GET', '/mon-compte/coups-de-coeur');
        $this->waitForElement('.rental_list__item');

        $favoritesPageCrawler = $this->client->getCrawler();
        $favoriteRental = $favoritesPageCrawler->filter('#rental-list-item-' . $rentalId);
        $this->assertCount(1, $favoriteRental, 'Favorite should persist after logout and login');
    }
}