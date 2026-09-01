<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Concerns\ApiResponds;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReportOrderIssueRequest;
use App\Http\Resources\CartResource;
use App\Http\Resources\OrderListResource;
use App\Http\Resources\OrderResource;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Spec ref: §7, items 37–39, 41–42.
 */
class OrderController extends Controller
{
    use ApiResponds;

    private const LIST_WITH = ['shop', 'items'];
    private const DETAIL_WITH = ['shop', 'items', 'trackingEvents'];

    /**
     * GET /api/orders
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 24), 100) ?: 24;

        $orders = Order::where('user_id', $request->user()->id)
            ->with(self::LIST_WITH)
            ->latest('placed_at')
            ->paginate($perPage)
            ->withQueryString();

        return $this->respondPaginated($orders, OrderListResource::class);
    }

    /**
     * GET /api/orders/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $order = $this->findOwnedOrder($request, $id, self::DETAIL_WITH);

        if (! $order) {
            return $this->respondMessage('Order not found.', 404);
        }

        return $this->respond(new OrderResource($order));
    }

    /**
     * GET /api/orders/{id}/tracking
     */
    public function tracking(Request $request, int $id): JsonResponse
    {
        $order = $this->findOwnedOrder($request, $id, ['trackingEvents']);

        if (! $order) {
            return $this->respondMessage('Order not found.', 404);
        }

        return $this->respond($this->trackingPayload($order));
    }

    /**
     * GET /api/tracking/{code} (Auth or Public — implemented public,
     * since "without full account context" is the whole point)
     */
    public function trackByCode(string $code): JsonResponse
    {
        $order = Order::where('order_code', $code)->with('trackingEvents')->first();

        if (! $order) {
            return $this->respondMessage('Tracking code not found.', 404);
        }

        return $this->respond($this->trackingPayload($order));
    }

    /**
     * POST /api/orders/{id}/reorder
     * Re-adds each order item to the user's cart, at current live
     * price/stock — not the historical order price. Unavailable items
     * (deleted product, insufficient stock) are skipped, not fatal.
     */
    public function reorder(Request $request, int $id): JsonResponse
    {
        $order = $this->findOwnedOrder($request, $id, ['items.product', 'items']);

        if (! $order) {
            return $this->respondMessage('Order not found.', 404);
        }

        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);
        $added = 0;
        $skipped = 0;

        foreach ($order->items as $orderItem) {
            $product = $orderItem->product;

            if (! $product || ! in_array($product->status, ['listed', 'out_of_stock'])) {
                $skipped++;
                continue;
            }

            $existing = $cart->items()->where('product_id', $product->id)->whereNull('variant_id')->first();
            $qty = min(($existing?->qty ?? 0) + $orderItem->qty, $product->stock);

            if ($qty <= ($existing?->qty ?? 0)) {
                $skipped++;
                continue;
            }

            $cart->items()->updateOrCreate(
                ['product_id' => $product->id, 'variant_id' => null],
                ['qty' => $qty, 'unit_price' => $product->price]
            );
            $added++;
        }

        $message = $skipped > 0
            ? "Added {$added} item(s) to your cart — {$skipped} no longer available."
            : "Added {$added} item(s) to your cart.";

        return $this->respond(new CartResource($cart->fresh(['items.product.shop', 'items.variant'])), $message);
    }

    /**
     * POST /api/orders/{id}/issues   { message }
     */
    public function reportIssue(ReportOrderIssueRequest $request, int $id): JsonResponse
    {
        $order = $this->findOwnedOrder($request, $id);

        if (! $order) {
            return $this->respondMessage('Order not found.', 404);
        }

        $issue = $order->issues()->create([
            'user_id' => $request->user()->id,
            'message' => $request->validated('message'),
            'status' => 'open',
        ]);

        return $this->respond([
            'id' => $issue->id,
            'order_id' => $issue->order_id,
            'status' => $issue->status,
            'message' => $issue->message,
            'created_at' => $issue->created_at->toIso8601String(),
        ], 'Issue reported.', 201);
    }

    private function findOwnedOrder(Request $request, int $id, array $with = []): ?Order
    {
        return Order::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->with($with)
            ->first();
    }

    private function trackingPayload(Order $order): array
    {
        return [
            'order_code' => $order->order_code,
            'status' => $order->status,
            'carrier' => $order->carrier,
            'tracking_code' => $order->tracking_code,
            'events' => $order->trackingEvents->map(fn ($event) => [
                'status' => $event->status,
                'label' => $event->label,
                'happened_at' => $event->happened_at->toIso8601String(),
            ]),
        ];
    }
}
