<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'provider' => $this->provider,
            'amount' => (float) $this->amount,
            'status' => $this->status,
            'pay_url' => $this->pay_url,
            'qr_payload' => $this->qr_payload,
        ];
    }
}
