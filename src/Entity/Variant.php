<?php

namespace App\Entity;

use App\Wrapper\ElasticWrapper;
use App\Wrapper\RedisWrapper;
use Doctrine\ORM\Mapping as ORM;
use Elasticsearch\ClientBuilder;

/**
 * @ORM\Entity(repositoryClass="App\Repository\VariantRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Variant
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $color;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $price;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Product", inversedBy="variants")
     */
    private $product;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getProduct(): ?product
    {
        return $this->product;
    }

    public function setProduct(?product $product): self
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return bool
     * @ORM\PostPersist
     */
    public function postPersist()
    {
        ElasticWrapper::search()->updateDocument($this->getProduct()->getId(), $this->fetchProductVariants());

        RedisWrapper::action()->initializeCache('variant_' . $this->getId(), [
            'color' => $this->getColor(),
            'price' => $this->getPrice(),
            'product_id' => $this->getProduct()->getId(),
        ]);

        return true;
    }

    /**
     * @return bool
     * @ORM\PostUpdate
     */
    public function postUpdate()
    {
        ElasticWrapper::search()->updateDocument($this->getProduct()->getId(), $this->fetchProductVariants());

        RedisWrapper::action()->initializeCache('variant_' . $this->getId(), [
            'color' => $this->getColor(),
            'price' => $this->getPrice(),
            'product_id' => $this->getProduct()->getId(),
        ], true);

        return true;
    }

    /**
     * @return bool
     * @ORM\PostRemove
     */
    public function postRemove()
    {
        ElasticWrapper::search()->updateDocument($this->getProduct()->getId(), $this->fetchProductVariants());
        RedisWrapper::action()->deleteCache('variant_' . $this->getId());
        return true;
    }

    /**
     * This method has been used for fetch variants product for particular product
     *
     * @return array
     * @author Mehran
     */
    private function fetchProductVariants(): array
    {
        $variants = [];
        $product = $this->getProduct();
        foreach ($product->getVariants()->toArray() as $key => $item) {
            $variants[$key]['id'] = $item->getId();
            $variants[$key]['price'] = $item->getPrice();
            $variants[$key]['color'] = $item->getColor();
        }

        return  [
            'variants' => $variants,
        ];
    }
}
