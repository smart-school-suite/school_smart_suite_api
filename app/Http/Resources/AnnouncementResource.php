<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
             'id' => $this->id,
             'title' => $this->title,
             'content' => $this->content,
             'status' => $this->status,
             'category_id' => $this->category_id,
             'category_name' => $this->announcementCategory->name ?? 0,
             'published_at' => $this->published_at,
             'label_id' => $this->label_id,
             'label_name' => $this->announcementLabel->name
        ];
    }
}
