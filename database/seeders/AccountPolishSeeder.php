<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\FollowedShop;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\UserVoucher;
use App\Models\Wishlist;
use Illuminate\Database\Seeder;

class AccountPolishSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::where('email', 'sophea@customer.zcomus.test')->first();

        if (! $customer) {
            $this->command?->warn('Run CustomerSeeder first — skipping account polish data.');
            return;
        }

        // Wishlist — a couple of products not already in the demo order/cart.
        Product::inRandomOrder()->take(2)->get()->each(function (Product $product) use ($customer) {
            Wishlist::firstOrCreate([
                'user_id' => $customer->id,
                'product_id' => $product->id,
            ]);
        });

        // Claim one of the seeded coupons into the customer's wallet.
        $coupon = Coupon::where('code', 'SAVE10')->first();
        if ($coupon) {
            UserVoucher::firstOrCreate([
                'user_id' => $customer->id,
                'coupon_id' => $coupon->id,
            ]);
        }

        // Notifications inbox.
        Notification::updateOrCreate(
            ['user_id' => $customer->id, 'title' => 'Order shipped'],
            [
                'body' => 'Your order is out for delivery.',
                'tone' => 'info',
                'icon' => 'truck',
                'read_at' => null,
            ]
        );

        Notification::updateOrCreate(
            ['user_id' => $customer->id, 'title' => 'Welcome to Zcomus'],
            [
                'body' => 'Thanks for joining — enjoy $5 off your first order with WELCOME5.',
                'tone' => 'success',
                'icon' => 'gift',
                'read_at' => now()->subDays(2),
            ]
        );

        // Follow a shop.
        $shop = Product::inRandomOrder()->first()?->shop;
        if ($shop) {
            FollowedShop::firstOrCreate([
                'user_id' => $customer->id,
                'shop_id' => $shop->id,
            ]);
        }

        // An order issue against the demo order from Step 3.
        $order = Order::where('user_id', $customer->id)->first();
        if ($order) {
            $order->issues()->firstOrCreate([
                'user_id' => $customer->id,
                'message' => 'One item arrived with a cracked case, requesting a replacement.',
            ], [
                'status' => 'open',
            ]);
        }
    }
}
