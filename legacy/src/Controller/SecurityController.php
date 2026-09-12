<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterUserType;
use App\Form\RequestResetPasswordType;
use App\Form\ResetPasswordType;
use App\Infrastructure\Symfony\Security\SecurityUserDTO;
use App\Service\RequestResetPasswordService;
use App\Service\ResetPasswordService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): never
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/creer-mon-compte', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $manager,
        MailerInterface $mailer,
        string $emailSender,
    ): Response {
        $form = $this->createForm(RegisterUserType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var SecurityUserDTO $dto */
            $dto = $form->getData();
            $user = new User();

            $user->setEmail($dto->email)->setPassword($passwordHasher->hashPassword($user, $dto->password));

            $manager->persist($user);
            $manager->flush();

            $email = (new TemplatedEmail())
                ->from($emailSender)
                ->to(new Address((string) $user->getEmail()))
                ->subject('Votre inscription a bien été prise en compte !')
                ->htmlTemplate('emails/user_registered.html.twig')
            ;

            $mailer->send($email);

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/reinitialisation-mot-de-passe', name: 'app_request_reset_password')]
    public function requestResetPassword(Request $request, RequestResetPasswordService $resetPasswordService): Response
    {
        $form = $this->createForm(RequestResetPasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $resetPasswordService->resetPassword(is_string($email) ? $email : '');

            return $this->redirectToRoute('app_request_reset_password_success');
        }

        return $this->render('security/request_reset_password.html.twig', [
            'form' => $form,
            'success_message' => false,
        ]);
    }

    #[Route('/reinitialisation-mot-de-passe-succes', name: 'app_request_reset_password_success')]
    public function requestResetPasswordSuccess(Request $request): Response
    {
        return $this->render('security/request_reset_password.html.twig', [
            'form' => null,
            'success_message' => true,
        ]);
    }

    #[Route('/reinitialisation/mot-de-passe', name: 'app_reset_password')]
    public function resetPassword(Request $request, ResetPasswordService $resetPasswordService): Response
    {
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('password')->getData();
            $resetPasswordService->resetPassword((string) $request->query->get('token'), is_string($password) ? $password : '');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig', [
            'form' => $form,
        ]);
    }
}
