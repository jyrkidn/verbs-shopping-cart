<?php

namespace App\Events;

use App\States\ItemState;
use Thunk\Verbs\Event;

class ItemRestocked extends Event
{
    public ItemState $item;
    public int $quantity;

    public function apply(): void
    {
        $this->item->quantity += $this->quantity;
    }
}
