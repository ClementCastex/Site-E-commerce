<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class ProductFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $product = new Product();
            $product->setName('Product ' . $i);
            $product->setDescription('Description of product ' . $i);
            $product->setPrice(mt_rand(10, 100) . '.99');
            $product->setImageUrl('https://via.placeholder.com/150');
            $product->setStockQuantity(mt_rand(1, 50));
            $product->setIsFeatured($i % 2 === 0); 

            $manager->persist($product);

            $this->addReference('product_' . $i, $product);
        }

        $manager->flush();
    }
}