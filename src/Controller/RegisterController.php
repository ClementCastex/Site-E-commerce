<?php


namespace App\Controller;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegisterController extends AbstractController
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    #[Route(path: '/register', name: 'app_register')]
public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response{
        if ($request->isMethod('POST')) {
            $user = new Users();
            $user->setUsername($request->request->get('username'));
            $user->setRole('ROLE_USER');

             // Validation du mot de passe et encodage
        $plainPassword = $request->request->get('password');
        $passwordConstraints = new Assert\Length(['min' => 6]);
        $validationErrors = $this->validator->validate($plainPassword, $passwordConstraints);

        if (count($validationErrors) > 0) {
            return $this->render('security/register.html.twig', [
                'errors' => $validationErrors,
            ]);
        }

            // Encoder le mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register.html.twig');
    }
}
