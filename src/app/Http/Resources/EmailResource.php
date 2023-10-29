<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmailResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'sender' => new SenderResource($this->sender),
            'inbox' => new InboxResource($this->inbox),
            'subject' => $this->subject,
            'created_at' => $this->created_at->format('c'),
            'read' => $this->read?->format('c'),
            'favorite' => $this->favorite,
            'has_html' => $this->has_html,
            'has_text' => $this->has_text,
            'size_in_bytes' => $this->size_in_bytes,
            'attachments' => $this->attachments,
        ];
    }
}
