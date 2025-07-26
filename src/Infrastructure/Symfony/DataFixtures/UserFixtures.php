<?php

namespace App\Infrastructure\Symfony\DataFixtures;

use App\Entity\Profile;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Service\Attribute\Required;

final class UserFixtures extends AbstractFixtures implements FixtureGroupInterface
{
    private const GENDER = [
        'M',
        'F',
    ];
    private const TITLE = [
        'M' => 'male',
        'F' => 'female',
    ];

    public const USER_REFERENCE = 'user_';

    private UserPasswordHasherInterface $passwordHasher;

    #[Required]
    public function setHasher(UserPasswordHasherInterface $passwordHasher): void
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 10; ++$i) {
            $user = new User();
            $profile = new Profile();

            $gender = self::GENDER[random_int(0, 1)];
            $title = self::TITLE[$gender];

            $profile
                ->setFirstname($this->faker->firstName($title))
                ->setLastname($this->faker->lastName())
                ->setBirthdate($this->faker->dateTimeThisCentury())
                ->setCellphone(str_replace(' ', '', $this->faker->phoneNumber()))
                ->setGender($gender)
            ;

            $user
                ->setEmail($this->faker->email())
                ->setRoles(['ROLE_USER'])
                ->setProfile($profile)
            ;

            $user->setPassword($this->passwordHasher->hashPassword($user, '123S3curedP4ssw0rd'));

            $this->addReference(self::USER_REFERENCE.$i, $user);

            $manager->persist($profile);
            $manager->persist($user);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['user', 'rental'];
    }
}
