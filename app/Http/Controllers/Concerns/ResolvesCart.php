<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Spec ref: §5 — every cart endpoint is "Guest or Auth".
 *
 * DESIGN DECISION: guest carts are identified by a client-generated
 * token (`X-Guest-Cart-Token` header), not Laravel's session ID. This
 * project's login flow regenerates the session ID (standard Breeze
 * security behavior), which would silently orphan a guest's cart the
 * moment they log in — making POST /api/cart/merge unable to find it.
 * A token the frontend stores itself (e.g. localStorage) survives
 * that boundary intact.
 *
 * Flow for the frontend:
 * 1. Call GET /api/cart before login. If no token was sent, the
 *    response includes an `X-Guest-Cart-Token` header — store it.
 * 2. Send that header on every subsequent guest cart request.
 * 3. After login, call POST /api/cart/merge with the same token
 *    (header or `guest_token` in the body) to fold it into the
 *    now-authenticated user's cart.
 */
trait ResolvesCart
{
    /**
     * @return array{0: Cart, 1: string|null} [$cart, $guestTokenToEcho]
     */
    protected function resolveCart(Request $request): array
    {
        if ($request->user()) {
            return [Cart::firstOrCreate(['user_id' => $request->user()->id]), null];
        }

        $token = $request->header('X-Guest-Cart-Token') ?: $request->query('guest_token');

        if (! $token) {
            $token = (string) Str::uuid();
        }

        $cart = Cart::firstOrCreate(['session_id' => $token]);

        // Always echo back when guest, so the frontend can sync
        // localStorage on every response, not just the first one.
        return [$cart, $token];
    }
}
