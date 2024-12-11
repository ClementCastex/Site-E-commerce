<?php

namespace App\Controller;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/order', name: 'admin_order_')]
class OrderAdminController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/list', name: 'list')]
    public function listOrders(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $orders = $this->entityManager->getRepository(Order::class)->findAll();

        return $this->render('admin/order/list.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/view/{id<\d+>}', name: 'view')]
    public function viewOrder(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
    
        $order = $this->entityManager->getRepository(Order::class)->find($id);
    
        if (!$order) {
            $this->addFlash('error', 'Commande non trouvée.');
            return $this->redirectToRoute('admin_order_list');
        }
    
        $orderItems = $order->getOrderItems();
        $totalPrice = 0;
    
        foreach ($orderItems as $item) {
            $totalPrice += $item->getPrice() * $item->getQuantity();
        }
    
        return $this->render('admin/order/view.html.twig', [
            'order' => $order,
            'orderItems' => $orderItems,
            'totalPrice' => $totalPrice,
        ]);
    } 
}