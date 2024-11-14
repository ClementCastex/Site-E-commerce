<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/products', name: 'product_index')]
    public function index(): Response
    {
        $products = $this->entityManager->getRepository(Product::class)->findAll();
        return $this->render('product/index.html.twig', [
            'products' => $products,
        ]);
    }
    
    #[Route('/products/{id}', name: 'product_show', requirements: ['id' => '\d+'])] #[Route('/products/{id}', name: 'product_show', requirements: ['id' => '\d+'])]
    public function show(Product $product): Response
    {
        // Render la vue show avec les détails du produit
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }
    #[Route('/products/search', name: 'product_search', methods: ['GET'])]
    public function search(Request $request): Response
    {
        $keyword = $request->query->get('q', '');
        $products = [];

        if ($keyword) {
            $products = $this->entityManager->getRepository(Product::class)
                ->createQueryBuilder('p')
                ->where('p.name LIKE :keyword OR p.description LIKE :keyword')
                ->setParameter('keyword', '%' . $keyword . '%')
                ->getQuery()
                ->getResult();
        }

        return $this->render('product/search.html.twig', [
            'products' => $products,
            'keyword' => $keyword,
        ]);
    }
}