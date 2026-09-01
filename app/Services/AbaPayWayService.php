<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;

/**
 * ABA PayWay integration.
 *
 * REBUILT after the user provided ABA's own official sample
 * (PayWayApiCheckout.php + index.php) for this exact merchant setup —
 * this is the strongest source used in this integration so far: real
 * working reference code, not a doc fragment or a reconstruction.
 *
 * ============================================================
 * Architecture change from Step 12/13
 * ============================================================
 * Steps 12–13 tried to call ABA's API server-to-server from Laravel
 * and parse a JSON response (`/generate-qr`, then `/purchase` with a
 * payment_option). Both approaches got rejected — 404 (wrong path,
 * fixed), then 403 "Service is not enable" — because the QR Payment
 * API is a separate opt-in service most merchant accounts don't have
 * turned on by default.
 *
 * The confirmed sample does something different: Laravel's only job is
 * to generate a signed set of fields (`buildKhqrCheckoutFields()`
 * below). The BROWSER then builds a hidden form with those fields and
 * submits it via ABA's own `checkout2-0.js` plugin — see
 * frontend-reference/ for the adapted snippet. This is the default
 * path every merchant account has, no service toggle required, which
 * is almost certainly why it's what the other project actually uses.
 *
 * Laravel never calls ABA directly to start a payment anymore. It
 * still calls ABA to CHECK a transaction's status afterward
 * (checkTransactionStatus, unchanged from Step 13) — if that also
 * comes back "Service is not enable," that's a second, separate
 * service to ask ABA to enable, and Step 14's error surfacing will
 * show that plainly if it happens.
 *
 * ============================================================
 * Hash order — now CONFIRMED, not reconstructed
 * ============================================================
 * Verbatim from the provided sample:
 *   req_time . merchant_id . tran_id . amount . firstname . lastname
 *   . email . phone . payment_option
 * No items, currency, or callback_url in this hash — a much shorter
 * concatenation than what Step 12/13 guessed.
 *
 * Also corrected: `req_time` is a raw Unix timestamp (`time()` in the
 * sample), not the `YmdHis`-formatted string used in Steps 12–13.
 * Matched here for the purchase flow; left checkTransactionStatus()
 * as-is since it's a different, still-unconfirmed endpoint — flagging
 * that this format might need the same fix if it errors.
 */
class AbaPayWayService
{
    private string $baseUrl;
    private string $merchantId;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('payway.base_url'), '/');
        $this->merchantId = (string) config('payway.merchant_id');
        $this->apiKey = (string) config('payway.api_key');
    }

    /**
     * Builds the signed field set the FRONTEND needs to submit ABA's
     * hidden checkout form via checkout2-0.js. No HTTP call happens
     * here — see class docblock. `req_time` is regenerated fresh each
     * call since it's time-sensitive; don't cache this response.
     *
     * @return array<string, string>
     */
    public function buildKhqrCheckoutFields(Order $order): array
    {
        $reqTime = (string) time();
        $tranId = $order->order_code;
        $amount = number_format((float) $order->total, 2, '.', '');
        $firstName = explode(' ', $order->shipping_name)[0] ?? $order->shipping_name;
        $lastName = trim(substr($order->shipping_name, strlen($firstName))) ?: $firstName;
        $email = $order->user->email ?? '';
        $phone = $order->shipping_phone ?? '';
        $paymentOption = 'abapay_khqr';

        $hashPayload = $reqTime . $this->merchantId . $tranId . $amount
            . $firstName . $lastName . $email . $phone . $paymentOption;

        return [
            'api_url' => "{$this->baseUrl}/api/payment-gateway/v1/payments/purchase",
            'req_time' => $reqTime,
            'merchant_id' => $this->merchantId,
            'tran_id' => $tranId,
            'amount' => $amount,
            'firstname' => $firstName,
            'lastname' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'payment_option' => $paymentOption,
            'hash' => $this->buildHash($hashPayload),
        ];
    }

    /**
     * POST /api/payment-gateway/v1/payments/check-transaction-2
     * Unchanged from Step 13 — still a server-to-server pull, used
     * for local-dev status polling since the webhook can't reach a
     * hosts-file-only domain. Hash order here is still INFERRED, not
     * confirmed the way buildKhqrCheckoutFields() now is.
     *
     * @return array{success: bool, status?: string, message?: string}
     */
    public function checkTransactionStatus(Order $order): array
    {
        $reqTime = now()->format('YmdHis');
        $tranId = $order->order_code;

        $hash = $this->buildHash($reqTime . $this->merchantId . $tranId);

        $response = Http::post("{$this->baseUrl}/api/payment-gateway/v1/payments/check-transaction-2", [
            'req_time' => $reqTime,
            'merchant_id' => $this->merchantId,
            'tran_id' => $tranId,
            'hash' => $hash,
        ]);

        if (! $response->successful()) {
            \Log::warning('PayWay check-transaction-2 failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'PayWay request failed: HTTP ' . $response->status() . ' — ' . $response->body(),
            ];
        }

        $data = $response->json();

        return [
            'success' => true,
            'status' => $data['data']['payment_status'] ?? null,
            'raw' => $data,
        ];
    }

    /**
     * Webhook payload hash order — still unconfirmed, same as Step 13.
     */
    public function verifyWebhookHash(array $payload): bool
    {
        if (! isset($payload['hash'])) {
            return false;
        }

        $received = $payload['hash'];
        $payloadWithoutHash = collect($payload)->except('hash')->implode('');
        $expected = $this->buildHash($payloadWithoutHash);

        return hash_equals($expected, $received);
    }

    private function buildHash(string $payload): string
    {
        return base64_encode(hash_hmac('sha512', $payload, $this->apiKey, true));
    }
}
