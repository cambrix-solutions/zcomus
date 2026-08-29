<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::where('email', 'sophea@customer.zcomus.test')->first();
        $address = $customer?->addresses()->where('is_default', true)->first();
        $products = Product::inRandomOrder()->take(2)->get();

        if (! $customer || ! $address || $products->count() < 2) {
            $this->command?->warn('Run CustomerSeeder, AddressSeeder, and ProductSeeder first — skipping orders.');
            return;
        }

        $shop = $products->first()->shop;

        $subtotal = $products->sum(fn (Product $p) => (float) $p->price);
        $shippingFee = 2.50;
        $discount = 0;
        $total = $subtotal + $shippingFee - $discount;

        $order = Order::create([
            'user_id' => $customer->id,
            'shop_id' => $shop->id,
            'address_id' => $address->id,
            'shipping_name' => $address->full_name,
            'shipping_phone' => $address->phone,
            'shipping_line1' => $address->line1,
            'shipping_city' => $address->city,
            'status' => 'shipped',
            'payment_method' => 'aba',
            'payment_status' => 'paid',
            'subtotal' => $subtotal,
            'shipping_fee' => $shippingFee,
            'discount' => $discount,
            'total' => $total,
            'placed_at' => now()->subDay(),
            'paid_at' => now()->subDay()->addMinutes(5),
            'packed_at' => now()->subHours(18),
            'shipped_at' => now()->subHours(15),
        ]);

        foreach ($products as $product) {
            $order->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_image' => $product->image,
                'sku' => $product->sku,
                'qty' => 1,
                'unit_price' => $product->price,
                'line_total' => $product->price,
            ]);
        }

        // §20.11 tracking event sequence.
        $events = [
            ['status' => 'placed', 'label' => 'Order placed', 'happened_at' => $order->placed_at],
            ['status' => 'paid', 'label' => 'Payment confirmed', 'happened_at' => $order->paid_at],
            ['status' => 'packed', 'label' => 'Packed', 'happened_at' => $order->packed_at],
            ['status' => 'shipped', 'label' => 'Out for delivery', 'happened_at' => $order->shipped_at],
        ];

        foreach ($events as $event) {
            $order->trackingEvents()->create($event);
        }

        Payment::create([
            'order_id' => $order->id,
            'provider' => 'aba',
            'amount' => $order->total,
            'status' => 'completed',
            'provider_ref' => 'ABA-DEMO-' . $order->id,
        ]);
    }
}
