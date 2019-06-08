<?php

namespace App\Entity;

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
    public function postPersist()
    {
        $client = ClientBuilder::create()->build();
        $params = [
            'index' => 'digi_project',
            'type' => 'variant',
            'id' => $this->getId(),
            'body' => [
                'title' => $this->getProduct()->getTitle(),
                'description' => $this->getProduct()->getDescription(),
                'price' => $this->getColor(),
                'color' => $this->getColor(),
                'product_id' => $this->getProduct()->getId(),
            ]
        ];
        $client->index($params);

        $redis_client = RedisAdapter::createConnection(
            'redis://localhost'
        );
        $redis_client->set($this->getId(), json_encode([
            'title' => $this->getProduct()->getTitle(),
            'description' => $this->getProduct()->getDescription(),
            'price' => $this->getColor(),
            'color' => $this->getColor(),
            'product_id' => $this->getProduct()->getId(),
        ]));

        return true;
    }

    /**
     * @ORM\PostUpdate
     */
    public function postUpdate()
    {
        $client = ClientBuilder::create()->build();
        $params = [
            'index' => 'digi_project',
            'type' => 'variant',
            'id' => $this->getId(),
            'body' => [
                'doc' => [
                    'title' => $this->getProduct()->getTitle(),
                    'description' => $this->getProduct()->getDescription(),
                    'price' => $this->getPrice(),
                    'color' => $this->getColor(),
                    'product_id' => $this->getProduct()->getId(),
                ]
            ]
        ];
        $client->update($params);
        $redis_client = RedisAdapter::createConnection(
            'redis://localhost'
        );
        $cache_variant = $redis_client->get($this->getId());
        if ($cache_variant != null) {
            $redis_client->del($this->getId());
        }
        $redis_client->set($this->getId(), json_encode([
            'title' => $this->getProduct()->getTitle(),
            'description' => $this->getProduct()->getDescription(),
            'price' => $this->getColor(),
            'color' => $this->getColor(),
            'product_id' => $this->getProduct()->getId(),
        ]));

        return true;
    }

    /**
     * @ORM\PreRemove
     */
    public function preRemove()
    {
        $client = ClientBuilder::create()->build();
        $params = [
            'index' => 'digi_project',
            'type' => 'variant',
            'id' => $this->getId(),
        ];
        $client->delete($params);
        $redis_client = RedisAdapter::createConnection(
            'redis://localhost'
        );
        $cache_variant = $redis_client->get($this->getId());
        if ($cache_variant != null) {
            $redis_client->del($this->getId());
        }
        return true;
    }
}
