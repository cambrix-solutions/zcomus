<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Concerns\ApiResponds;
use App\Http\Controllers\Concerns\ResolvesCart;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Spec ref: §5, items 26–31.
 */
class CartController extends Controller
{
    use ApiResponds, ResolvesCart;

    private const CART_WITH = ['items.product.shop', 'items.variant'];

    /**
     * GET /api/cart
     */
    public function show(Request $request): JsonResponse
    {
        [$cart, $guestToken] = $this->resolveCart($request);

        return $this->cartResponse($cart, $guestToken);
    }

    /**
     * POST /api/cart   { product_id, qty, variant_id? }
     * Adding a product already in the cart increases its quantity
     * rather than creating a duplicate row (cart_items has a unique
     * index on cart_id+product_id+variant_id — see Step 2).
     */
    public function store(AddToCartRequest $request): JsonResponse
    {
        [$cart, $guestToken] = $this->resolveCart($request);

        $product = Product::findOrFail($request->validated('product_id'));
        $variant = $request->validated('variant_id')
            ? ProductVariant::find($request->validated('variant_id'))
            : null;

        $existing = $cart->items()
            ->where('product_id', $product->id)
            ->where('variant_id', $variant?->id)
            ->first();

        $requestedQty = ($existing?->qty ?? 0) + $request->validated('qty');
        $stock = $variant?->stock ?? $product->stock;

        if ($requestedQty > $stock) {
            return $this->respondMessage("Only {$stock} in stock.", 422);
        }

        $unitPrice = $variant?->price ?? $product->price;

        $cart->items()->updateOrCreate(
            ['product_id' => $product->id, 'variant_id' => $variant?->id],
            ['qty' => $requestedQty, 'unit_price' => $unitPrice]
        );

        return $this->cartResponse($cart->fresh(self::CART_WITH), $guestToken, 201);
    }

    /**
     * PATCH /api/cart/{productId}   { qty, variant_id? }
     *
     * GAP: the spec's URL only carries a product id, not a variant —
     * but a product can be in the cart multiple times under different
     * variants (cart_items is unique per product+variant, not just
     * product). If there's exactly one row for this product, `qty`
     * applies to it directly. If there's more than one (different
     * variants), `variant_id` becomes required to say which row —
     * one `qty` value can't sensibly apply to two different rows at
     * once.
     */
    public function update(UpdateCartItemRequest $request, int $productId): JsonResponse
    {
        [$cart, $guestToken] = $this->resolveCart($request);

        $rows = $cart->items()->where('product_id', $productId)->get();

        if ($rows->isEmpty()) {
            return $this->respondMessage('Item not found in cart', 404);
        }

        $item = $this->disambiguate($rows, $request->validated('variant_id'));

        if ($item === null) {
            return $this->respondMessage(
                'This product is in your cart with multiple variants — specify variant_id.',
                422
            );
        }

        $stock = $item->variant?->stock ?? $item->product->stock;

        if ($request->validated('qty') > $stock) {
            return $this->respondMessage("Only {$stock} in stock.", 422);
        }

        $item->update(['qty' => $request->validated('qty')]);

        return $this->cartResponse($cart->fresh(self::CART_WITH), $guestToken);
    }

    /**
     * DELETE /api/cart/{productId}
     * Removes every row for this product, regardless of variant —
     * unlike PATCH, "remove this product" isn't ambiguous even when
     * multiple variants are present, so no disambiguation is needed.
     */
    public function destroyItem(Request $request, int $productId): JsonResponse
    {
        [$cart, $guestToken] = $this->resolveCart($request);

        $cart->items()->where('product_id', $productId)->delete();

        return $this->cartResponse($cart->fresh(self::CART_WITH), $guestToken);
    }

    /**
     * DELETE /api/cart — clear cart
     */
    public function clear(Request $request): JsonResponse
    {
        [$cart, $guestToken] = $this->resolveCart($request);

        $cart->items()->delete();

        return $this->cartResponse($cart->fresh(self::CART_WITH), $guestToken);
    }

    /**
     * POST /api/cart/merge (Auth only — see ResolvesCart docblock for
     * the guest-token design this depends on)
     */
    public function merge(Request $request): JsonResponse
    {
        $userCart = Cart::firstOrCreate(['user_id' => $request->user()->id]);

        $token = $request->input('guest_token') ?: $request->header('X-Guest-Cart-Token');
        $guestCart = $token ? Cart::where('session_id', $token)->first() : null;

        if ($guestCart && $guestCart->id !== $userCart->id) {
            foreach ($guestCart->items as $guestItem) {
                $existing = $userCart->items()
                    ->where('product_id', $guestItem->product_id)
                    ->where('variant_id', $guestItem->variant_id)
                    ->first();

                $stock = $guestItem->variant?->stock ?? $guestItem->product->stock;
                $mergedQty = min(($existing?->qty ?? 0) + $guestItem->qty, $stock);

                $userCart->items()->updateOrCreate(
                    ['product_id' => $guestItem->product_id, 'variant_id' => $guestItem->variant_id],
                    ['qty' => $mergedQty, 'unit_price' => $guestItem->unit_price]
                );
            }

            $guestCart->delete(); // items cascade-delete with it
        }

        return $this->respond(new CartResource($userCart->fresh(self::CART_WITH)));
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\CartItem>  $rows
     */
    private function disambiguate($rows, ?int $variantId): mixed
    {
        if ($rows->count() === 1) {
            return $rows->first();
        }

        return $variantId ? $rows->firstWhere('variant_id', $variantId) : null;
    }

    private function cartResponse(Cart $cart, ?string $guestToken, int $status = 200): JsonResponse
    {
        $response = $this->respond(new CartResource($cart->loadMissing(self::CART_WITH)), 'OK', $status);

        if ($guestToken) {
            $response->headers->set('X-Guest-Cart-Token', $guestToken);
        }

        return $response;
    }
}
