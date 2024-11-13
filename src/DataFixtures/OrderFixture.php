<?php


namespace App\DataFixtures;

use App\Entity\Order;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class OrderFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $order = new Order();

            // Utilisation des références pour lier un utilisateur existant à cette commande
            $user = $this->getReference('user_' . rand(1, 5));
            $order->setUser($user);
            
            $order->setOrderNumber('ORD-' . strtoupper(uniqid()));
            $order->setShippingAddress('123 Test Street, Test City');
            $order->setPhoneNumber('123-456-7890');
            $order->setStatus('Pending');

            $manager->persist($order);

            // Enregistrer la référence pour les liaisons avec les OrderItem si besoin
            $this->addReference('order_' . $i, $order);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UsersFixture::class,
        ];
    }
}