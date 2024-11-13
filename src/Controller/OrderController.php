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
        $cart = $this->session->get('cart', []);
        if (empty($cart)) {
            return $this->redirectToRoute('cart_index');
        }


        $shippingAddress = $request->request->get('shippingAddress');
        $phoneNumber = $request->request->get('phoneNumber');


        $order = new Order();
        $order->setUser($this->getUser());
        $order->setOrderNumber(uniqid('ORD-'));
        $order->setShippingAddress($shippingAddress);
        $order->setPhoneNumber($phoneNumber);
        $order->setStatus('Pending');
        $this->entityManager->persist($order);


        foreach ($cart as $item) {
            $orderItem = new OrderItem();
            $orderItem->setOrderReference($order);
            $orderItem->setProduct($item['product']);
            $orderItem->setQuantity($item['quantity']);
            $orderItem->setPrice($item['product']->getPrice());
            $this->entityManager->persist($orderItem);
        }

        $this->entityManager->flush();


        $this->session->remove('cart');

        return $this->render('order/confirmation.html.twig', [
            'order' => $order,
        ]);
    }
}
