<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'title'           => $this->title,
            'content'         => $this->content,

            'status'          => $this->deriveStatus(),
            'category_id'     => $this->category_id,
            'category_name'   => $this->announcementCategory->name ?? null,

            'label_id'        => $this->label_id,
            'label_name'      => $this->announcementLabel->name ?? null,

            'recipient_count' => $this->recipient_count ?? 0,

            'published_at'    => $this->published_at?->toDateTimeString(),
            'expires_at'      => $this->expires_at?->toDateTimeString(),
            'created_at'      => $this->created_at?->toDateTimeString(),
            'updated_at'      => $this->updated_at?->toDateTimeString(),
            'author_username' => $this->announcementAuthor?->first()->authorable?->username,
            'author_profile' => $this->announcementAuthor?->first()->authorable?->profile_picture,
            'author_name'  => $this->announcementAuthor?->first()->authorable?->name,
            'author_first_name' => $this->announcementAuthor?->first()->authorable?->first_name,
            'author_last_name' => $this->announcementAuthor?->first()->authorable?->last_name
        ];
    }

    protected function deriveStatus(): string
    {
        if (is_null($this->published_at)) {
            return 'draft';
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return 'expired';
        }

        if ($this->published_at->isFuture()) {
            return 'scheduled';
        }

        return 'active';
    }

}
