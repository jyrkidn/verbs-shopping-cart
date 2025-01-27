<?php

namespace App\States;

use Thunk\Verbs\State;

class ItemState extends State
{
    public int $quantity = 0;

    public array $holds = [];

    public function available(): int
    {
        return $this->quantity - collect($this->activeHolds())->sum('quantity');
    }

    public function activeHolds(): array
    {
        return $this->holds = array_filter($this->holds, fn ($hold) => $hold['expires'] > time());
    }
}
