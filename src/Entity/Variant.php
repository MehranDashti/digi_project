<?php

namespace App\Entity;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Elasticsearch\ClientBuilder;
use Symfony\Component\Cache\Adapter\RedisAdapter;

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
     * @ORM\PostPersist
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $product = $this->getProduct();
        foreach ($product->getVariants()->toArray() as $key => $item) {
            $variants[$key]['id'] = $item->getId();
            $variants[$key]['price'] = $item->getPrice();
            $variants[$key]['color'] = $item->getColor();
        }

        $client = ClientBuilder::create()->build();
        $params = [
            'index' => 'digi_project',
            'type' => 'product',
            'id' => $this->getProduct()->getId(),
            'body' => [
                'doc' => [
                    'variants' => $variants,
                ]
            ]
        ];
        $client->update($params);
        $redis_client = RedisAdapter::createConnection(
            'redis://localhost'
        );
        $redis_client->set('variant_' . $this->getId(), json_encode([
            'color' => $this->getColor(),
            'price' => $this->getPrice(),
            'product_id' => $this->getProduct()->getId(),
        ]));

        return true;
    }

    /**
     * @ORM\PostUpdate
     */
    public function postUpdate()
    {
        $product = $this->getProduct();
        foreach ($product->getVariants()->toArray() as $key => $item) {
            $variants[$key]['id'] = $item->getId();
            $variants[$key]['price'] = $item->getPrice();
            $variants[$key]['color'] = $item->getColor();
        }

        $client = ClientBuilder::create()->build();
        $params = [
            'index' => 'digi_project',
            'type' => 'product',
            'id' => $this->getProduct()->getId(),
            'body' => [
                'doc' => [
                    'variants' => $variants,
                ]
            ]
        ];
        $client->update($params);
        $redis_client = RedisAdapter::createConnection(
            'redis://localhost'
        );
        $cache_variant = $redis_client->get('variant_' . $this->getId());
        if (is_null($cache_variant)) {
            $redis_client->del($this->getId());
        }
        $redis_client->set('variant_' . $this->getId(), json_encode([
            'color' => $this->getColor(),
            'price' => $this->getPrice(),
            'product_id' => $this->getProduct()->getId(),
        ]));

        return true;
    }

    /**
     * @ORM\PostRemove
     */
    public function postRemove()
    {
        $product = $this->getProduct();
        foreach ($product->getVariants()->toArray() as $key => $item) {
            $variants[$key]['id'] = $item->getId();
            $variants[$key]['price'] = $item->getPrice();
            $variants[$key]['color'] = $item->getColor();
        }

        $client = ClientBuilder::create()->build();
        $params = [
            'index' => 'digi_project',
            'type' => 'product',
            'id' => $this->getProduct()->getId(),
            'body' => [
                'doc' => [
                    'variants' => $variants,
                ]
            ]
        ];
        $client->update($params);
        $redis_client = RedisAdapter::createConnection(
            'redis://localhost'
        );
        $cache_variant = $redis_client->get('variant_' . $this->getId());
        if (is_null($cache_variant)) {
            $redis_client->del($this->getId());
        }
        return true;
    }
}
