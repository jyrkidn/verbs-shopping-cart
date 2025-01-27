<?php

namespace App\Events;

use App\States\CartState;
use App\States\ItemState;
use Thunk\Verbs\Event;

class ItemRemovedFromCart extends Event
{
    public CartState $cart;

    public ItemState $item;

    public int $quantity;

    public function validate()
    {
        $this->assert(
            $this->cart->count($this->item->id) >= $this->quantity,
            "There are not {$this->quantity} items in the cart to remove",
        );
    }

    public function apply()
    {
        $this->cart->items[$this->item->id] -= $this->quantity;

        if (isset($this->item->holds[$this->cart->id])) {
            $this->item->holds[$this->cart->id]['quantity'] -= $this->quantity;
        }
    }
}
