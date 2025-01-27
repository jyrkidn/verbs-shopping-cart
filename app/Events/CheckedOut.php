<?php

namespace App\Events;

use App\States\CartState;
use App\States\ItemState;
use Thunk\Verbs\Event;

class CheckedOut extends Event
{
    public CartState $cart;


    public function validate()
    {
        $this->assert(! $this->cart->checkedOut, "Cart is already checked out");

        foreach ($this->cart->items as $itemId => $quantity) {
            $item = ItemState::load($itemId);

            $held = $item->activeHolds()[$this->cart->id]['quantity'] ?? 0;

            $this->assert(
                $held + $item->available() >= $quantity,
                "Some items in your cart are out of stock",
            );
        }
    }

    public function handle()
    {
        // Do your application logic, e.g. send to api, send mail, etc.
        foreach ($this->cart->items as $itemId => $quantity) {
            $item = ItemState::load($itemId);

            $item->quantity -= $quantity;

            unset($item->holds[$this->cart->id]);
        }

        $this->cart->checkedOut = true;
    }
}
