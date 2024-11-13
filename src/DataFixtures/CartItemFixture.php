<?php 

namespace App\DataFixtures;

use App\Entity\CartItem;
use App\Entity\Cart;
use App\Entity\Product;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class CartItemFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Exemple : Ajouter des CartItems en lien avec des Cart et des Product
        for ($i = 1; $i <= 5; $i++) {
            $cartItem = new CartItem();

            // Utilisation de références pour lier les Cart et Product créés par d'autres fixtures
            $cart = $this->getReference('cart_' . rand(1, 5));
            $product = $this->getReference('product_' . rand(1, 5));

            $cartItem->setCart($cart);
            $cartItem->setProduct($product);
            $cartItem->setQuantity(rand(1, 5)); // Quantité aléatoire entre 1 et 5

            // Persister l'objet CartItem
            $manager->persist($cartItem);
        }

        // Exécuter toutes les persistances
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            CartFixture::class,
            ProductFixture::class,
        ];
    }
}