<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\Variant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class VariantFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $variant = new Variant();
        $variant->setColor("red");
        $variant->setPrice("10000000");
        $variant->setProduct($manager->getRepository(Product::class)->findOneBy(['id' => 1]));
        $variant->setUpdatedAt(time());
        $variant->setCreatedAt(time());
        $manager->persist($variant);
        $manager->flush();

        $variant = new Variant();
        $variant->setColor("Gold");
        $variant->setPrice("50000");
        $variant->setProduct($manager->getRepository(Product::class)->findOneBy(['id' => 2]));
        $variant->setUpdatedAt(time());
        $variant->setCreatedAt(time());
        $manager->persist($variant);
        $manager->flush();

        $variant = new Variant();
        $variant->setColor("white");
        $variant->setPrice("14590000");
        $variant->setProduct($manager->getRepository(Product::class)->findOneBy(['id' => 3]));
        $variant->setUpdatedAt(time());
        $variant->setCreatedAt(time());
        $manager->persist($variant);
        $manager->flush();

        $variant = new Variant();
        $variant->setColor("brown");
        $variant->setPrice("100");
        $variant->setProduct($manager->getRepository(Product::class)->findOneBy(['id' => 4]));
        $variant->setUpdatedAt(time());
        $variant->setCreatedAt(time());
        $manager->persist($variant);
        $manager->flush();
    }
}
