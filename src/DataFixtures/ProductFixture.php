<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class ProductFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $product = new Product();
        $product->setTitle("Car");
        $product->setDescription("The Benz company");
        $product->setUpdatedAt(time());
        $product->setCreatedAt(time());
        $manager->persist($product);
        $manager->flush();

        $product = new Product();
        $product->setTitle("Phone");
        $product->setDescription("The Iphone company");
        $product->setUpdatedAt(time());
        $product->setCreatedAt(time());
        $manager->persist($product);
        $manager->flush();

        $product = new Product();
        $product->setTitle("Airplane");
        $product->setDescription("The Boeing company");
        $product->setUpdatedAt(time());
        $product->setCreatedAt(time());
        $manager->persist($product);
        $manager->flush();

        $product = new Product();
        $product->setTitle("Chocolate");
        $product->setDescription("The Lomana Chocolate");
        $product->setUpdatedAt(time());
        $product->setCreatedAt(time());
        $manager->persist($product);
        $manager->flush();
    }
}
