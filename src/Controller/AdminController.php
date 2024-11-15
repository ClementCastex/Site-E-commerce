<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/dashboard', name: 'dashboard')]
    public function dashboard(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('admin/dashboard.html.twig');
    }

    #[Route('/featured-products', name: 'featured_products')]
    public function manageFeaturedProducts(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $products = $this->entityManager->getRepository(Product::class)->findAll();

        if ($request->isMethod('POST')) {
            $featuredProductIds = $request->request->all('featured_products') ?: [];
            
            foreach ($products as $product) {
                $product->setIsFeatured(in_array($product->getId(), $featuredProductIds));
                $this->entityManager->persist($product);
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Produits en avant mis à jour avec succès.');
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('admin/featured_products.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/product/add', name: 'product_add')]
    public function addProduct(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
    
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
    
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($product);
            $this->entityManager->flush();
    
            $this->addFlash('success', 'Produit ajouté avec succès.');
            return $this->redirectToRoute('admin_dashboard');
        }
    
        return $this->render('admin/add_product.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/product/list', name: 'product_list')]
    public function listProducts(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $products = $this->entityManager->getRepository(Product::class)->findAll();

        return $this->render('admin/list_products.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/product/edit/{id}', name: 'product_edit')]
    public function editProduct(int $id, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $product = $this->entityManager->getRepository(Product::class)->find($id);
        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Produit modifié avec succès.');
            return $this->redirectToRoute('admin_product_list');
        }

        return $this->render('admin/edit_product.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
        ]);
    }

    #[Route('/product/delete/{id}', name: 'product_delete', methods: ['POST'])]
    public function deleteProduct(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $product = $this->entityManager->getRepository(Product::class)->find($id);
        if (!$product) {
            $this->addFlash('error', 'Produit non trouvé.');
            return $this->redirectToRoute('admin_product_list');
        }

        $this->entityManager->remove($product);
        $this->entityManager->flush();

        $this->addFlash('success', 'Produit supprimé avec succès.');
        return $this->redirectToRoute('admin_product_list');
    }

    #[Route('/product/toggle-featured/{id}', name: 'product_toggle_featured')]
    public function toggleFeatured(Product $product): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $product->setIsFeatured(!$product->isFeatured());
        $this->entityManager->flush();

        $this->addFlash('success', 'Statut de mise en avant mis à jour avec succès.');
        return $this->redirectToRoute('admin_dashboard');
    }
}
