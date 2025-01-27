<?php

namespace App\Console\Commands;

use App\Events\CheckedOut;
use App\Events\ItemAddedToCart;
use App\Events\ItemRemovedFromCart;
use App\Events\ItemRestocked;
use App\States\CartState;
use App\States\ItemState;
use Closure;
use Exception;
use Illuminate\Console\Command;
use function Laravel\Prompts\error;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class ShopCommand extends Command
{
    protected $signature = 'shop';

    protected int $cartId;

    protected array $stickers;

    public function handle()
    {
        $this->setup();

        do {
            try {
                $action = $this->action();
                $this->getLaravel()->forgetScopedInstances();
                $action();
            } catch (Exception $exception) {
                error("Error: {$exception->getMessage()}");
            }
        } while (true);
    }

    protected function action(): Closure
    {
        $selection = select(
            label: "What would you like to do?",
            options: [
                'Add item to cart',
                'Remove item from cart',
                'Check out',
                'Restock items',
            ],
        );

        return match ($selection) {
            'Add item to cart' => function () {
                [$item, $quantity] = $this->selectSticker();
                ItemAddedToCart::commit(
                    cart: $this->cartId,
                    item: $item,
                    quantity: $quantity,
                );

                $cart = CartState::load($this->cartId);
                dump($cart->items, ItemState::load($item)->holds);
            },
            'Remove item from cart' => function () {
                [$item, $quantity] = $this->selectSticker();
                ItemRemovedFromCart::commit(
                    cart: $this->cartId,
                    item: $item,
                    quantity: $quantity,
                );
            },
            'Check out' => function () {
                CheckedOut::commit(cart: $this->cartId);
            },
            'Restock items' => function () {
                [$item, $quantity] = $this->selectSticker(defaultQuantity: 4);
                ItemRestocked::commit(
                    item: $item,
                    quantity: (int) $quantity,
                );

                $state = ItemState::load($item);

                \Laravel\Prompts\info("Stock updated to {$state->quantity}");
            },
        };
    }

    protected function selectSticker(int $defaultQuantity = 1): array
    {
        $sticker = select(
            label: 'Which sticker would you like?',
            options: $this->stickers,
        );

        $quantity = (int) text(
            label: 'Quantity',
            default: $defaultQuantity,
            required: true,
            validate: ['quantity' => 'required|int|min:1'],
        );

        return [$sticker, $quantity];
    }

    protected function setup()
    {
        $this->cartId = snowflake_id();

        $this->stickers = [
            1000 => '🦄 Unicorn',
            1001 => '🦊 Fox',
            1002 => '🐶 Dog',
            1003 => '🐱 Cat',
        ];
    }
}
