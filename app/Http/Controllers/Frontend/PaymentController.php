<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Concerns\ApiResponds;
use App\Http\Controllers\Controller;
use App\Http\Requests\InitiatePaymentRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Services\AbaPayWayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Spec ref: §6, items 34–36.
 * See AbaPayWayService's docblock for the browser-driven flow this
 * now implements.
 */
class PaymentController extends Controller
{
    use ApiResponds;

    public function __construct(private readonly AbaPayWayService $payway) {}

    /**
     * POST /api/payments/initiate   { order_id }
     *
     * CHANGED: no longer calls ABA server-to-server. Returns a signed
     * `checkout` field set the FRONTEND submits directly to ABA via
     * their checkout2-0.js plugin — see frontend-reference/. The
     * `pay_url`/`qr_payload` fields from Steps 12–13 are gone; there's
     * nothing for Laravel to fetch anymore in this flow.
     */
    public function initiate(InitiatePaymentRequest $request): JsonResponse
    {
        $order = Order::where('id', $request->validated('order_id'))
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $order) {
            return $this->respondMessage('Order not found.', 404);
        }

        if ($order->payment_method === 'cod') {
            return $this->respondMessage('This order is Cash on Delivery and does not require online payment.', 422);
        }

        if ($order->payment_method === 'wing') {
            return $this->respondMessage(
                'Wing payments are not available yet — this requires a separate Wing integration, not ABA PayWay.',
                501
            );
        }

        $payment = Payment::firstOrCreate(
            ['order_id' => $order->id, 'status' => 'pending'],
            ['provider' => $order->payment_method, 'amount' => $order->total]
        );

        return $this->respond([
            'id' => $payment->id,
            'order_id' => $payment->order_id,
            'provider' => $payment->provider,
            'amount' => (float) $payment->amount,
            'status' => $payment->status,
            'checkout' => $this->payway->buildKhqrCheckoutFields($order),
        ]);
    }

    /**
     * GET /api/payments/{id}/status — unchanged from Step 13.
     */
    public function status(Request $request, int $id): JsonResponse
    {
        $payment = Payment::whereHas('order', fn ($q) => $q->where('user_id', $request->user()->id))
            ->with('order')
            ->find($id);

        if (! $payment) {
            return $this->respondMessage('Payment not found.', 404);
        }

        if ($payment->status === 'pending' && in_array($payment->provider, ['aba', 'khqr'])) {
            $this->syncFromPayWay($payment);
        }

        return $this->respond([
            'id' => $payment->id,
            'status' => $payment->status,
            'provider_ref' => $payment->provider_ref,
            'paid_at' => $payment->paid_at?->toIso8601String(),
        ]);
    }

    private function syncFromPayWay(Payment $payment): void
    {
        $result = $this->payway->checkTransactionStatus($payment->order);

        if (! $result['success'] || ! $result['status']) {
            return;
        }

        $newStatus = match ($result['status']) {
            'APPROVED' => 'completed',
            'DECLINED' => 'failed',
            default => 'pending',
        };

        if ($newStatus === 'pending') {
            return;
        }

        $payment->update([
            'status' => $newStatus,
            'paid_at' => $newStatus === 'completed' ? now() : null,
            'raw_payload' => $result['raw'] ?? null,
        ]);

        if ($newStatus === 'completed') {
            $order = $payment->order;
            $order->update(['payment_status' => 'paid', 'status' => 'paid', 'paid_at' => now()]);
            $order->trackingEvents()->create([
                'status' => 'paid',
                'label' => 'Payment confirmed',
                'happened_at' => now(),
            ]);
        }
    }

    /**
     * POST /api/payments/webhook/{provider} — unchanged from Step 13.
     */
    public function webhook(Request $request, string $provider): JsonResponse
    {
        if ($provider === 'aba' || $provider === 'khqr') {
            if (! $this->payway->verifyWebhookHash($request->all())) {
                return $this->respondMessage('Invalid signature.', 401);
            }
        }

        $payment = Payment::find($request->input('payment_id') ?? $request->input('tran_id'));

        if (! $payment || $payment->provider !== $provider) {
            return $this->respondMessage('Payment not found.', 404);
        }

        $status = $request->input('status') === 'success' ? 'completed' : 'failed';

        $payment->update([
            'status' => $status,
            'provider_ref' => $request->input('provider_ref'),
            'paid_at' => $status === 'completed' ? now() : null,
            'raw_payload' => $request->all(),
        ]);

        if ($status === 'completed') {
            $order = $payment->order;
            $order->update(['payment_status' => 'paid', 'status' => 'paid', 'paid_at' => now()]);
            $order->trackingEvents()->create([
                'status' => 'paid',
                'label' => 'Payment confirmed',
                'happened_at' => now(),
            ]);
        }

        return $this->respondMessage('OK');
    }
}
