<?php

namespace App\States;

use Thunk\Verbs\State;

class CartState extends State
{
    public array $items = [];

    public bool $checkedOut = false;

    public function count(int $itemId): int
    {
        return $this->items[$itemId] ?? 0;
    }
}
