<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Spec ref: §20.16. ASSUMPTION: `time` type is just "string" with no
 * format specified — using ISO8601, consistent with every other
 * timestamp in this API rather than a pre-formatted relative string
 * like "2 hours ago" (that's a display concern, better left to the
 * frontend so it can localize/update live rather than going stale
 * between page load and viewing).
 */
class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'tone' => $this->tone,
            'icon' => $this->icon,
            'time' => $this->created_at->toIso8601String(),
            'read_at' => $this->read_at?->toIso8601String(),
        ];
    }
}
