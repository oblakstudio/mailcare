<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AutomationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'sender' => $this->sender,
            'inbox' => $this->inbox,
            'subject' => $this->subject,
            'has_attachments' => $this->has_attachments,
            'action_url' => $this->action_url,
            'action_email' => $this->action_email,
            'action_secret_token' => $this->action_secret_token,
            'action_delete_email' => $this->action_delete_email,
            'post_raw' => $this->post_raw,
            'emails_received' => $this->emails_received,
            'in_error' => $this->in_error,
            'created_at' => $this->created_at->format('c'),
            'updated_at' => $this->updated_at->format('c'),
        ];
    }
}
