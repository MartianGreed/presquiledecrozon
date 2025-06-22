<?php

namespace App\Infrastructure\Symfony\DataFixtures;

use App\Domain\Price;
use App\Domain\Rental\DTO\GeolocationDTO;
use App\Domain\Rental\RentalPreferences;
use App\Domain\Rental\Status;
use App\Entity\Data\Bed;
use App\Entity\Data\Furniture;
use App\Entity\Data\Linens;
use App\Entity\Data\RentalType;
use App\Entity\Data\Town;
use App\Entity\Media;
use App\Entity\Rental\Address;
use App\Entity\Rental\Bedroom;
use App\Entity\Rental\Condition;
use App\Entity\Rental\Configuration;
use App\Entity\Rental\Description;
use App\Entity\Rental\Gallery;
use App\Entity\Rental\Preferences;
use App\Entity\Rental\Rental;
use App\Entity\Rental\Tax;
use App\Entity\Rental\Unavailability;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Vich\UploaderBundle\Handler\UploadHandler;

class RentalFixtures extends AbstractFixtures implements FixtureGroupInterface, DependentFixtureInterface
{
    /** @var array<RentalType> */
    private array $rentalTypes;
    /** @var array<Bed> */
    private array $beds;
    /** @var array<Furniture> */
    private array $furnitures;
    /** @var array<Town> */
    private array $towns;
    /** @var array<Linens> */
    private array $linens;

    private SluggerInterface $slugger;
    private UploadHandler $uploadHandler;
    private LoggerInterface $logger;

    #[Required]
    public function setSlugger(SluggerInterface $slugger): void
    {
        $this->slugger = $slugger;
    }

    #[Required]
    public function setUploadeHandler(UploadHandler $handler): void
    {
        $this->uploadHandler = $handler;
    }

    #[Required]
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function load(ObjectManager $manager): void
    {
        $this->fetchData($manager);

        for ($i = 0; $i < 10; $i++) {
            $user = $this->getReference(UserFixtures::USER_REFERENCE . $i, User::class);
            assert($user instanceof User);

            for ($j = 0; $j < 2; $j++) {
                $rental = Rental::new($user);

                $rental = $this->createConfiguration($rental, $manager);
                $rental = $this->createFurnitures($rental);
                $rental = $this->createDescription($rental, $manager);
                $rental = $this->createAddress($rental, $manager);
                $rental = $this->createGeolocation($rental, $manager);
                $rental = $this->createPictures($rental, $manager);
                $rental = $this->createPreferences($rental, $manager);
                $rental = $this->createUnavailabilities($rental, $manager);
                $rental = $this->createTaxes($rental, $manager);
                $rental = $this->createPrices($rental, $manager);
                $rental = $this->createConditions($rental, $manager);

                $rental->setSlug($this->slugger->slug((string) $rental->getDescription()?->getTitle()));

                $rental->setStatus(Status::PUBLISHED);

                $manager->persist($rental);
            }

            $manager->flush();
        }
    }

    private function createConfiguration(Rental $rental, ObjectManager $manager): Rental
    {
        $configuration = new Configuration();

        $count = $this->faker->randomDigit();

        $configuration
            ->setType($this->rentalTypes[array_rand($this->rentalTypes)])
            ->setPeopleCount($this->faker->randomDigit())
            ->setRental($rental);

        $bedroomCount = 1;
        if (2 < $count) {
            $bedroomCount = $count / 2;
        }

        for ($i = 0; $i < $bedroomCount; $i++) {
            $bedroom = new Bedroom();

            $bedroom->addBed($this->beds[array_rand($this->beds)], $this->faker->randomDigit());
            if ($bedroomCount < 2) {
                $bedroom->addBed($this->beds[array_rand($this->beds)], $this->faker->randomDigit());
            }

            $manager->persist($bedroom);
            $configuration->addBedroom($bedroom);
        }

        $manager->persist($configuration);

        $rental->setConfiguration($configuration);

        return $rental;
    }

    private function createFurnitures(Rental $rental): Rental
    {
        $furnitures = array_rand($this->furnitures, $this->faker->numberBetween(5, \count($this->furnitures)));

        /** @phpstan-ignore-next-line */
        foreach ($furnitures as $furniture) {
            $rental->addFurniture($this->furnitures[$furniture]);
        }

        /** @var array<int, string> $customFurnitures */
        $customFurnitures = $this->faker->sentences($this->faker->randomDigit());
        $rental->setCustomFurnitures($customFurnitures);

        return $rental;
    }

    private function createDescription(Rental $rental, ObjectManager $manager): Rental
    {
        $description = new Description();

        $description->setTitle($this->faker->sentence(15, true));
        /** @phpstan-ignore-next-line */
        $description->setDescription($this->faker->paragraphs(5, true));

        $rental->saveDescription($description);

        $manager->persist($description);

        return $rental;
    }

    private function createAddress(Rental $rental, ObjectManager $manager): Rental
    {
        $address = new Address();
        $address
            ->setAddress($this->faker->streetAddress())
            ->setTown($this->towns[array_rand($this->towns)])
            ->setRental($rental);

        $rental->saveAddress($address);

        $manager->persist($address);

        return $rental;
    }

    private function createGeolocation(Rental $rental, ObjectManager $manager): Rental
    {
        return $rental->improveGeolocation(
            GeolocationDTO::new(
                $this->faker->latitude(48.15, 48.33),
                $this->faker->longitude(-4.18, -4.61),
                [
                    'viewport' => [
                        'northeast' => ['lat' => 48.333778, 'lng' => -4.563765],
                        'southwest' => ['lat' => 48.098610, 'lng' => -4.238295],
                    ],
                    'formatted_address' => 'Not used with fixtures data',
                    'place_id' => 'this_id_a_fake_place_id',
                ]
            )
        );
    }

    private function createPictures(Rental $rental, ObjectManager $manager): Rental
    {
        $gallery = new Gallery();

        $gallery
            ->setCover($this->createMedia('cover.jpg'))
            ->addPicture($this->createMedia('1.jpg'))
            ->addPicture($this->createMedia('2.jpg'))
            ->addPicture($this->createMedia('3.jpg'))
            ->addPicture($this->createMedia('4.jpg'))
            ->addPicture($this->createMedia('5.jpg'));

        $rental = $rental->createGallery($gallery);
        $manager->persist($gallery);

        return $rental;
    }

    private function createPreferences(Rental $rental, ObjectManager $manager): Rental
    {
        $preferences = new Preferences();

        $preferences
            ->setAcceptedLastBooking(RentalPreferences::acceptedLastBookingChoices()[array_rand(RentalPreferences::acceptedLastBookingChoices())])
            ->setMaxTimeBeforeBooking(RentalPreferences::maxTimeBeforeBookingChoices()[array_rand(RentalPreferences::maxTimeBeforeBookingChoices())])
            ->setBeginBookingAt(RentalPreferences::beginBookingAt()[array_rand(RentalPreferences::beginBookingAt())])
            ->setEndBookingAt(RentalPreferences::endBookingAt()[array_rand(RentalPreferences::endBookingAt())]);

        $rental->setPreferences($preferences);
        $manager->persist($preferences);

        return $rental;
    }

    private function createUnavailabilities(Rental $rental, ObjectManager $manager): Rental
    {
        $range = $this->faker->randomDigit();
        for ($i = 0; $i < $range; $i++) {
            $startDate = $this->faker->dateTimeBetween('now', '+1 years');
            $endDate = $startDate->add(new \DateInterval('P' . $this->faker->randomDigit() . 'D'));

            $unavailability = (new Unavailability())->setStartAt($startDate)->setEndAt($endDate)->setRental($rental);

            $rental->addUnavailability($unavailability);

            $manager->persist($unavailability);
        }

        return $rental;
    }

    private function createTaxes(Rental $rental, ObjectManager $manager): Rental
    {
        $tax = new Tax();
        $tax->setLocalTax('1,2%')
            ->setCleaningTax(new Price(50))
            ->setLinensTax(new Price(15));

        foreach ($this->linens as $linen) {
            $tax->addLinen($linen);
        }

        $manager->persist($tax);

        $rental->saveTax($tax);

        return $rental;
    }

    private function createPrices(Rental $rental, ObjectManager $manager): Rental
    {
        $rental->savePrices(new ArrayCollection([
            (new \App\Entity\Rental\Price())
                /** @phpstan-ignore-next-line */
                ->setRangeStart(\DateTime::createFromFormat('d/m', '15/06'))
                /** @phpstan-ignore-next-line */
                ->setRangeEnd(\DateTime::createFromFormat('d/m', '15/07'))
                ->setDailyRate(new Price($this->faker->numberBetween(60, 110)))
                ->setWeeklyRate(new Price($this->faker->numberBetween(600, 1100))),
            (new \App\Entity\Rental\Price())
                /** @phpstan-ignore-next-line */
                ->setRangeStart(\DateTime::createFromFormat('d/m', '16/07'))
                /** @phpstan-ignore-next-line */
                ->setRangeEnd(\DateTime::createFromFormat('d/m', '15/09'))
                ->setDailyRate(new Price($this->faker->numberBetween(70, 120)))
                ->setWeeklyRate(new Price($this->faker->numberBetween(700, 1200))),

        ]));

        $rental
            ->setDailyRate(new Price($this->faker->numberBetween(50, 70)))
            ->setWeeklyRate(new Price($this->faker->numberBetween(500, 700)));

        return $rental;
    }

    private function createConditions(Rental $rental, ObjectManager $manager): Rental
    {
        $condition = new Condition();

        $condition
            ->setAnimalsAccepted($this->faker->boolean())
            ->setSmokingAllowed($this->faker->boolean())
            /** @phpstan-ignore-next-line */
            ->setAdditionnalRules($this->faker->sentences());
        $rental = $rental->setCondition($condition);

        $manager->persist($condition);

        return $rental;
    }

    private function createMedia(string $name): Media
    {
        $filePath = __DIR__ . '/fixtures/rental/' . $name;
        
        if (!file_exists($filePath)) {
            $this->logger->error('Fixture image not found', ['path' => $filePath]);
            throw new \RuntimeException(sprintf('Fixture image not found: %s', $filePath));
        }
        
        try {
            $file = new UploadedFile($filePath, $name, 'image/jpeg', null, true);
            $media = (new Media())
                ->setFile($file)
                ->setName($file->getFilename())
                ->setSize($file->getSize());

            $this->logger->info('Uploading fixture image to CDN', [
                'filename' => $name,
                'size' => $file->getSize(),
            ]);
            
            $this->uploadHandler->upload($media, 'file');
            
            $this->logger->info('Successfully uploaded fixture image to CDN', [
                'filename' => $name,
                'media_id' => $media->getId(),
            ]);

            return $media;
        } catch (\Exception $e) {
            $this->logger->error('Failed to upload fixture image to CDN', [
                'filename' => $name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Re-throw the exception to fail the fixture loading
            throw new \RuntimeException(
                sprintf('Failed to upload image %s to CDN: %s', $name, $e->getMessage()),
                0,
                $e
            );
        }
    }

    public static function getGroups(): array
    {
        return ['rental'];
    }

    private function fetchData(ObjectManager $manager): void
    {
        $this->rentalTypes = $manager->getRepository(RentalType::class)->findAll();
        $this->beds = $manager->getRepository(Bed::class)->findAll();
        $this->furnitures = $manager->getRepository(Furniture::class)->findAll();
        $this->towns = $manager->getRepository(Town::class)->findAll();
        $this->linens = $manager->getRepository(Linens::class)->findAll();
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}
