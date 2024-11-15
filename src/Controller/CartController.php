<?php

namespace App\Controller;

use App\Entity\Product;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CartController extends AbstractController
{
    private $session;

    public function __construct(RequestStack $requestStack)
    {
        $this->session = $requestStack->getSession();
    }

    #[Route('/cart', name: 'cart_index')]
    public function index(): Response
    {
        $cart = $this->session->get('cart', []);
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['product']->getPrice() * $item['quantity'];
        }

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
            'total' => $total,
        ]);
    }

    #[Route('/cart/add/{id}', name: 'cart_add')]
    public function add(Product $product): Response
    {
        $cart = $this->session->get('cart', []);

        if (isset($cart[$product->getId()])) {
            $cart[$product->getId()]['quantity']++;
        } else {
            $cart[$product->getId()] = [
                'product' => $product,
                'quantity' => 1,
            ];
        }

        $this->session->set('cart', $cart);
        $this->addFlash('success', 'Produit ajouté au panier avec succès !');

        return $this->redirectToRoute('cart_index');
    }

    #[Route('/cart/remove/{id}', name: 'cart_remove')]
    public function remove(Product $product): Response
    {
        $cart = $this->session->get('cart', []);

        if (isset($cart[$product->getId()])) {
            unset($cart[$product->getId()]);
            $this->addFlash('success', 'Produit retiré du panier avec succès !');
        } else {
            $this->addFlash('warning', 'Le produit n\'est pas dans le panier.');
        }

        $this->session->set('cart', $cart);

        return $this->redirectToRoute('cart_index');
    }
    #[Route('/cart/update-quantity', name: 'cart_update_quantity', methods: ['POST'])]
    public function updateQuantity(Request $request): JsonResponse
    {
        $productId = $request->request->get('id');
        $quantity = (int) $request->request->get('quantity');

        $cart = $this->session->get('cart', []);
        $response = ['success' => false];

        if (isset($cart[$productId]) && $quantity > 0) {
            $cart[$productId]['quantity'] = $quantity;
            $this->session->set('cart', $cart);

            // Calcule le nouveau total du panier
            $total = 0;
            foreach ($cart as $item) {
                $total += $item['product']->getPrice() * $item['quantity'];
            }

            $response = [
                'success' => true,
                'newQuantity' => $quantity,
                'newTotal' => $total,
            ];
        }

        return new JsonResponse($response);
    }
}