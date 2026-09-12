<?php

namespace App\Controller\Profile;

use App\Controller\WithUserTrait;
use App\Entity\Profile;
use App\Form\Profile\UpdateProfileInformationsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class UpdateProfileInformationsController extends AbstractController
{
    use WithUserTrait;

    public function __construct(
        private readonly EntityManagerInterface $manager
    )
    {
    }

    #[Route('/mon-compte/informations', name: 'app_profile_informations')]
    public function __invoke(Request $request): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(UpdateProfileInformationsType::class, $user->getProfile());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Profile $profile */
            $profile = $form->getData();

            if (! $user->getProfile() instanceof \App\Entity\Profile) {
                $user->setProfile($profile);
                $this->manager->persist($profile);
            }
            $this->manager->flush();

            return $this->redirectToRoute('app_profile_informations');
        }

        return $this->render('profile/update_informations.html.twig', [
            'form' => $form,
        ]);
    }
}
