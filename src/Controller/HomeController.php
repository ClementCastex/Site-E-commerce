<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'home_index')]
    public function index(): Response
    {
        // Récupère les produits en avant
        $featuredProducts = $this->entityManager->getRepository(Product::class)->findBy(['isFeatured' => true]);

        // Récupère les derniers produits ajoutés (par exemple, les 5 derniers)
        $latestProducts = $this->entityManager->getRepository(Product::class)->findBy([], ['id' => 'DESC'], 3);

        return $this->render('home/index.html.twig', [
            'featuredProducts' => $featuredProducts,
            'latestProducts' => $latestProducts,
        ]);
    }
}