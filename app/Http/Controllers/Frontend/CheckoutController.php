<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Concerns\ApiResponds;
use App\Http\Controllers\Concerns\AppliesCoupon;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\UserVoucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * Spec ref: §6, item 33.
 *
 * DEFERRED (flagged, not silently skipped): a cart with products from
 * more than one shop can't check out in one call. Splitting into
 * multiple orders would also mean splitting a single coupon discount
 * across multiple orders in some proportional way that isn't defined
 * anywhere in the spec — rather than invent that rule, this rejects
 * mixed-shop carts with a clear message. Matches the "single order =
 * one shop for now" note already in Step 3's README.
 */
class CheckoutController extends Controller
{
    use ApiResponds, AppliesCoupon;

    /** Flat placeholder — spec doesn't define a shipping calculation. */
    private const SHIPPING_FEE = 2.50;

    public function store(CheckoutRequest $request): JsonResponse
    {
        $user = $request->user();
        $cart = Cart::where('user_id', $user->id)->with('items.product', 'items.variant')->first();

        if (! $cart || $cart->items->isEmpty()) {
            return $this->respondMessage('Your cart is empty.', 422);
        }

        $shopIds = $cart->items->pluck('product.shop_id')->unique();
        if ($shopIds->count() > 1) {
            return $this->respondMessage(
                'Your cart has items from multiple shops. Please check out one shop at a time.',
                422
            );
        }

        $address = Address::where('id', $request->validated('address_id'))
            ->where('user_id', $user->id)
            ->first();

        if (! $address) {
            return $this->respondMessage('Address not found.', 404);
        }

        $coupon = null;
        if ($code = $request->validated('coupon_code')) {
            $coupon = Coupon::where('code', $code)->first();
        }

        try {
            $order = DB::transaction(function () use ($user, $cart, $address, $request, $coupon) {
                return $this->buildOrder($user, $cart, $address, $request, $coupon);
            });
        } catch (\RuntimeException $e) {
            return $this->respondMessage($e->getMessage(), 422);
        }

        $payment = $order->payments()->first();

        return $this->respond([
            'id' => $order->id,
            'order_code' => $order->order_code,
            'status' => $order->status,
            'payment_method' => $order->payment_method,
            'payment_status' => $order->payment_status,
            'subtotal' => (float) $order->subtotal,
            'shipping_fee' => (float) $order->shipping_fee,
            'discount' => (float) $order->discount,
            'total' => (float) $order->total,
            'payment' => new PaymentResource($payment),
        ], 'Order placed.', 201);
    }

    private function buildOrder($user, Cart $cart, Address $address, CheckoutRequest $request, ?Coupon $coupon): Order
    {
        $subtotal = 0.0;

        // Lock stock rows for the duration of the transaction so two
        // simultaneous checkouts on the last unit can't both succeed.
        foreach ($cart->items as $item) {
            $product = Product::lockForUpdate()->find($item->product_id);
            $variant = $item->variant_id ? ProductVariant::lockForUpdate()->find($item->variant_id) : null;
            $available = $variant?->stock ?? $product->stock;

            if ($item->qty > $available) {
                throw new \RuntimeException("\"{$product->name}\" only has {$available} left in stock.");
            }

            $subtotal += $item->qty * (float) $item->unit_price;
        }

        $subtotal = round($subtotal, 2);
        $couponResult = $this->evaluateCoupon($coupon, $subtotal);
        $discount = $couponResult['valid'] ? $couponResult['discount'] : 0.0;

        $shopId = $cart->items->first()->product->shop_id;
        $shippingFee = self::SHIPPING_FEE;
        $total = round($subtotal - $discount + $shippingFee, 2);

        $order = Order::create([
            'user_id' => $user->id,
            'shop_id' => $shopId,
            'address_id' => $address->id,
            'shipping_name' => $address->full_name,
            'shipping_phone' => $address->phone,
            'shipping_line1' => $address->line1,
            'shipping_city' => $address->city,
            'status' => 'placed',
            'payment_method' => $request->validated('payment_method'),
            'payment_status' => 'pending',
            'subtotal' => $subtotal,
            'shipping_fee' => $shippingFee,
            'discount' => $discount,
            'total' => $total,
            'coupon_code' => $couponResult['valid'] ? $coupon->code : null,
            'notes' => $request->validated('notes'),
        ]);

        foreach ($cart->items as $item) {
            $order->items()->create([
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'product_image' => $item->product->image,
                'sku' => $item->variant?->sku ?? $item->product->sku,
                'qty' => $item->qty,
                'unit_price' => $item->unit_price,
                'line_total' => round($item->qty * (float) $item->unit_price, 2),
            ]);

            if ($item->variant_id) {
                ProductVariant::where('id', $item->variant_id)->decrement('stock', $item->qty);
            } else {
                $item->product->decrement('stock', $item->qty);
                if ($item->product->fresh()->stock <= 0) {
                    $item->product->update(['status' => 'out_of_stock']);
                }
            }
        }

        $order->trackingEvents()->create([
            'status' => 'placed',
            'label' => 'Order placed',
            'happened_at' => now(),
        ]);

        Payment::create([
            'order_id' => $order->id,
            'provider' => $order->payment_method,
            'amount' => $total,
            'status' => 'pending',
        ]);

        if ($couponResult['valid']) {
            UserVoucher::where('user_id', $user->id)
                ->where('coupon_id', $coupon->id)
                ->whereNull('used_at')
                ->update(['used_at' => now()]);
        }

        $cart->items()->delete();

        return $order->fresh(['payments']);
    }
}
