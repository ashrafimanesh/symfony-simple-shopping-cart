<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrderRepository::class)
 * @ORM\Table(name="`order`")
 * @UniqueEntity(fields={"code"}, message="There is already taken")
 */
class Order
{

    /**
     * An order that is in progress, not placed yet.
     *
     * @var string
     */
    const STATUS_CART = 'cart';
    const STATUS_ORDERED = 'ordered';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity=OrderItem::class, mappedBy="order", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $items;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status = self::STATUS_CART;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="orders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $owner;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $discount;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $tax;

    /**
     * @ORM\Column(type="float")
     */
    private $total_price;

    /**
     * @ORM\Column(type="string", length=5, unique=true)
     */
    private $code;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Collection|OrderItem[]
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(OrderItem $item): self
    {
        if($item->getQuantity()<=0){
            dd($item);
            return $this;
        }
        foreach ($this->getItems() as $existingItem) {
            // The item already exists, update the quantity
            if ($existingItem->equals($item)) {
                $existingItem->setQuantity(
                    $item->getQuantity()
                );
                return $this;
            }
        }

        $this->items[] = $item;
        $item->setOrder($this);

        return $this;
    }

    public function removeItem(OrderItem $item): self
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getOrder() === $this) {
                $item->setOrder(null);
            }
        }

        return $this;
    }

    /**
     * Removes all items from the order.
     *
     * @return $this
     */
    public function removeItems(): self
    {
        foreach ($this->getItems() as $item) {
            $this->removeItem($item);
        }

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Calculates the order total.
     *
     * @return float
     */
    public function getTotal(): float
    {
        $total = 0;

        foreach ($this->getItems() as $item) {
            $total += $item->getTotal();
        }

        return $total;
    }

    public function getOwner()
    {
        return $this->owner;
    }

    public function setOwner($owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getDiscount()
    {
        return $this->discount;
    }

    public function setDiscount(float $discount): self
    {
        $this->discount = $discount;

        return $this;
    }

    public function getTax()
    {
        return $this->tax;
    }

    public function setTax($tax): self
    {
        $this->tax = $tax;

        return $this;
    }

    public function getTotalPrice()
    {
        return $this->total_price;
    }

    public function setTotalPrice(string $total_price): self
    {
        $this->total_price = $total_price;

        return $this;
    }

    public function existItem($productId)
    {
        /** @var OrderItem $item */
        foreach($this->items as $item){
            if($item->getProduct()->getId()==$productId){
                return $item;
            }
        }
        return false;
    }

    public function productQuantity($productId)
    {
        /** @var OrderItem $item */
        foreach($this->items as $item){
            if($item->getProduct()->getId()==$productId){
                return $item->getQuantity();
            }
        }
        return 0;
    }

    public function getTotalQuantity()
    {
        $quantity = 0;
        /** @var OrderItem $item */
        foreach($this->items as $item){
            $quantity+=$item->getQuantity();
        }
        return $quantity;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }
}
