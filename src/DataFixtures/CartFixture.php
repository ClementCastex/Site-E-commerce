<?php

namespace App\DataFixtures;

use App\Entity\Cart;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class CartFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Création de plusieurs objets Cart pour tester
        for ($i = 1; $i <= 5; $i++) {
            $cart = new Cart();
            $cart->setSessionId('session_' . uniqid());
            $cart->setCreatedAt(new \DateTimeImmutable());

            // Persister l'objet Cart
            $manager->persist($cart);
            
            $this->addReference('cart_' . $i, $cart);
        }

        // Exécuter toutes les persistances
        $manager->flush();
    }
}
