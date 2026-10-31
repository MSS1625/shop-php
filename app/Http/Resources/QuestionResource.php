<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    /**
     * پرسش پاسخ‌داده‌شده — پرسش و پاسخ فروشگاه کنار هم
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'body' => $this->body,
            'answer' => $this->answer,
            'answered_at' => $this->answered_at?->toIso8601String(),
            'user' => [
                'id' => $this->user_id,
                'name' => $this->whenLoaded('user', fn () => $this->user?->name),
            ],
            'verified_buyer' => (bool) ($this->resource->verified_buyer ?? false),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
