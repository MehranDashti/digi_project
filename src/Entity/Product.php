<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Elasticsearch\ClientBuilder;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Product
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
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $created_at;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $updated_at;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Variant", mappedBy="product")
     */
    private $variants;

    public function __construct()
    {
        $this->variants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedAt(): ?int
    {
        return $this->created_at;
    }

    public function setCreatedAt(?int $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?int
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?int $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**
     * @return Collection|Variant[]
     */
    public function getVariants(): Collection
    {
        return $this->variants;
    }

    public function addVariant(Variant $variant): self
    {
        if (!$this->variants->contains($variant)) {
            $this->variants[] = $variant;
            $variant->setProduct($this);
        }

        return $this;
    }

    public function removeVariant(Variant $variant): self
    {
        if ($this->variants->contains($variant)) {
            $this->variants->removeElement($variant);
            // set the owning side to null (unless already changed)
            if ($variant->getProduct() === $this) {
                $variant->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * This method has been called when we want add new the product
     *
     * @ORM\PrePersist
     * @author Mehram
     */
    public function prePersist()
    {
        $this->setCreatedAt(time());
        $this->setUpdatedAt(time());

    }

    /**
     * @ORM\PostPersist
     * @return bool
     */
    public function postPersist()
    {
        $client = ClientBuilder::create()->build();
        $params = [
            'index' => 'digi_project',
            'type' => 'product',
            'id' => $this->getId(),
            'body' => [
                'title' => $this->getTitle(),
                'description' => $this->getDescription(),
                'variants' => [],
            ]
        ];
        $client->index($params);

        $redis_client = RedisAdapter::createConnection(
            'redis://localhost'
        );
        $redis_client->set('product_' . $this->getId(), json_encode([
            'title' => $this->getTitle(),
            'description' => $this->getDescription()
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
            'type' => 'product',
            'id' => $this->getId(),
            'body' => [
                'doc' => [
                    'title' => $this->getTitle(),
                    'description' => $this->getDescription(),
                ]
            ]
        ];
        $client->update($params);

        $redis_client = RedisAdapter::createConnection(
            'redis://localhost'
        );
        $cache_variant = $redis_client->get('product_' . $this->getId());
        if (!is_null($cache_variant)) {
            $redis_client->del('product_' . $this->getId());
        }
        $redis_client->set('product_' . $this->getId(), json_encode([
            'title' => $this->getTitle(),
            'description' => $this->getDescription()
        ]));

        return true;
    }

    /**
     * @param LifecycleEventArgs $args
     * @ORM\PreRemove
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $redis_client = RedisAdapter::createConnection(
            'redis://localhost'
        );
        if (!is_null($this->getVariants()->toArray())) {
            foreach ($this->getVariants()->toArray() as $item) {
                $entityManager = $args->getObjectManager();
                $entityManager->remove($item);
                $entityManager->flush();
                $cache_variant = $redis_client->get('variant_' . $item->getId());
                if (!is_null($cache_variant)) {
                    $redis_client->del('variant_' . $item->getId());
                }
            }
        }
        $client = ClientBuilder::create()->build();
        $params = [
            'index' => 'digi_project',
            'type' => 'product',
            'id' => $this->getId(),
        ];
        $client->delete($params);

        $cache_variant = $redis_client->get('product_' . $this->getId());
        if (!is_null($cache_variant)) {
            $redis_client->del('product_' . $this->getId());
        }
    }
}
