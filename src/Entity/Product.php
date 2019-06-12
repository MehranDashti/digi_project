<?php

namespace App\Entity;

use App\Wrapper\ElasticWrapper;
use App\Wrapper\RedisWrapper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Elasticsearch\ClientBuilder;
use Symfony\Component\Cache\Adapter\RedisAdapter;

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
     * This method has been called when we want update particular product
     *
     * @ORM\PreUpdate
     * @author Mehram
     */
    public function preUpdate()
    {
        $this->setUpdatedAt(time());
    }

    /**
     * @ORM\PostPersist
     * @return bool
     */
    public function postPersist()
    {
        $data = [
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'variants' => [],
        ];
        ElasticWrapper::search()->indexDocument($this->getId(), $data);
        RedisWrapper::action()->initializeCache('product_' . $this->getId(), $data);

        return true;
    }

    /**
     * @return bool
     * @ORM\PostUpdate
     */
    public function postUpdate()
    {
        $data = [
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
        ];
        ElasticWrapper::search()->updateDocument($this->getId(), $data);
        RedisWrapper::action()->initializeCache('product_' . $this->getId(), $data, true);

        return true;
    }

    /**
     * @param LifecycleEventArgs $args
     * @return bool
     * @ORM\PreRemove
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        if (!empty($this->getVariants()->toArray())) {
            foreach ($this->getVariants()->toArray() as $item) {
                $entityManager = $args->getObjectManager();
                $entityManager->remove($item);
                $entityManager->flush();
                RedisWrapper::action()->deleteCache('variant_' . $item->getId());
            }
        }
        ElasticWrapper::search()->deleteDocument($this->getId());
        RedisWrapper::action()->deleteCache('product_' . $this->getId());

        return true;
    }

}
