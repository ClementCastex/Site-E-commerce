<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    private $session;
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->session = $requestStack->getSession();
    }

    #[Route('/order', name: 'order_index')]
    public function index(Request $request): Response
    {
        $cart = $this->session->get('cart', []);
        if (empty($cart)) {
            return $this->redirectToRoute('cart_index');
        }

        return $this->render('order/index.html.twig');
    }

    #[Route('/order/confirm', name: 'order_confirm', methods: ['POST'])]
    public function confirm(Request $request): Response
    {
        // Vérifie que l'utilisateur est connecté
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour passer une commande.');
            return $this->redirectToRoute('app_login');
        }
    
        $cart = $this->session->get('cart', []);
        if (empty($cart)) {
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('cart_index');
        }
    
        // Crée la commande
        $order = new Order();
        $order->setUser($user);
        $order->setOrderNumber(uniqid('ORD-'));
        $order->setShippingAddress($request->request->get('shippingAddress'));
        $order->setPhoneNumber($request->request->get('phoneNumber'));
        $order->setStatus('Pending');
        $this->entityManager->persist($order);
    
        // Initialise le prix total
        $totalPrice = 0;
    
        // Traite les items du panier
        foreach ($cart as $item) {
            // Récupère le produit existant dans la base de données pour éviter la duplication
            $product = $this->entityManager->getRepository(Product::class)->find($item['product']->getId());
            $quantity = $item['quantity'];
    
            // Vérifie s'il y a suffisamment de stock pour le produit
            if ($product->getStockQuantity() < $quantity) {
                $this->addFlash('error', "Stock insuffisant pour le produit {$product->getName()}. Commande annulée.");
                return $this->redirectToRoute('cart_index');
            }
    
            // Crée un nouvel OrderItem
            $orderItem = new OrderItem();
            $orderItem->setOrderReference($order);
            $orderItem->setProduct($product);
            $orderItem->setQuantity($quantity);
            $orderItem->setPrice($product->getPrice());
            $this->entityManager->persist($orderItem);
    
            // Réduit le stock du produit en fonction de la quantité commandée
            $newStockQuantity = $product->getStockQuantity() - $quantity;
            $product->setStockQuantity($newStockQuantity);
            $this->entityManager->persist($product);
    
            // Calcule le prix total pour la commande
            $totalPrice += $product->getPrice() * $quantity;
        }
    
        // Sauvegarde la commande et met à jour les produits
        $this->entityManager->flush();
    
        // Vider le panier
        $this->session->remove('cart');
    
        // Affiche la page de confirmation de la commande avec le prix total
        return $this->render('order/confirmation.html.twig', [
            'order' => $order,
            'totalPrice' => $totalPrice,
        ]);
    }
}