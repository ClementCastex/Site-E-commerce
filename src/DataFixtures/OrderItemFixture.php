<?php

namespace App\DataFixtures;

use App\Entity\Order;
use App\Entity\Product;
use App\Entity\OrderItem;
use App\DataFixtures\ProductFixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class OrderItemFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $orderItem = new OrderItem();

            $order = $this->getReference('order_' . rand(1, 5));
            $product = $this->getReference('product_' . rand(1, 5));

            $orderItem->setOrderReference($order);
            $orderItem->setProduct($product);
            $orderItem->setQuantity(rand(1, 3));
            $orderItem->setPrice($product->getPrice()); // Prix du produit

            $manager->persist($orderItem);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            OrderFixture::class,
            ProductFixture::class,
        ];
    }
}
