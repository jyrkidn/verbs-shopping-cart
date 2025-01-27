<?php

namespace App\Events;

use App\States\CartState;
use App\States\ItemState;
use Thunk\Verbs\Event;

class ItemAddedToCart extends Event
{
    public static int $itemLimit = 2;

    public static int $holdSeconds = 5;

    public ItemState $item;

    public CartState $cart;

    public int $quantity;

    public function validate()
    {
        $this->assert(! $this->cart->checkedOut, "Cart is already checked out");

        $this->assert(
            $this->item->available() >= $this->quantity,
            "Out of stock",
        );

        $this->assert(
            $this->cart->count($this->item->id) + $this->quantity <= self::$itemLimit,
            'Reached max. amount of stickers for this item'
        );
    }

    public function apply()
    {
        $this->cart->items[$this->item->id] = $this->quantity + ($this->cart->items[$this->item->id] ?? 0);

        $this->item->holds[$this->cart->id] ??= [
            'quantity' => 0,
            'expires' => time() + self::$holdSeconds,
        ];

        $this->item->holds[$this->cart->id]['quantity'] += $this->quantity;
    }
}
