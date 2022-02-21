<?php


namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ShoppingCart;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearCartCommand extends Command
{
    protected $signature = "clear:cart";

    protected $description = "Clear user cart data if it exceeds 1 hour";

    public function handle()
    {
        try {

            $userCarts = User::join('shopping_cart', 'users.id', '=', 'shopping_cart.user_id')
                ->whereRaw("shopping_cart.created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)")
                ->select(DB::raw('shopping_cart.id as shopping_cart_id'), 'product_id', 'amount')
                ->get();

            if ($userCarts->count() == 0) {
                $this->info("No carts found");
                return;
            }

            foreach ($userCarts as $userCart) {
                $cartItemAmount = $userCart->amount;

                $product = Product::find($userCart->product_id);

                if ($product) {
                    $product->increment('amount', $cartItemAmount);
                }

                $cartItem = ShoppingCart::find($userCart->shopping_cart_id);

                $cartItem->delete();
            }

            $this->info("All user carts have been cleared");
        } catch (\Exception $e) {
            $this->error("Error clearing user cart:" . $e->getMessage());
        }
    }
}
