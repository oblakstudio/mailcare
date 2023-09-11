<?php

namespace App\Filters;

use App\Inbox;
use App\Sender;

class EmailFilters extends Filters
{
    protected $filters = ['inbox', 'sender', 'subject', 'since', 'until', 'search', 'unread', 'favorite'];

    protected function inbox($email)
    {
        $inbox = Inbox::where('email', $email)->firstOrFail();

        return $this->builder->where('inbox_id', $inbox->id);
    }

    protected function sender($email)
    {
        $sender = Sender::where('email', $email)->firstOrFail();

        return $this->builder->where('sender_id', $sender->id);
    }

    protected function subject($subject)
    {
        $subject = str_replace('*', '%', $subject);
        return $this->builder->where('subject', 'like', "$subject");
    }

    protected function since($date)
    {
        return $this->builder->where('created_at', '>', $date);
    }

    protected function until($date)
    {
        return $this->builder->where('created_at', '<', $date);
    }

    protected function search($keywords)
    {
        $inboxes = Inbox::where('email', 'like', "%$keywords%")->pluck('id')->all();
        $senders = Sender::where('email', 'like', "%$keywords%")->pluck('id')->all();

        return $this->builder
            ->where(function ($query) use ($inboxes, $senders, $keywords) {
                $query
                ->orWhereIn('inbox_id', $inboxes)
                ->orWhereIn('sender_id', $senders)
                ->orWhere('subject', 'like', "%$keywords%");
            });
    }

    protected function unread()
    {
        return $this->builder->whereNull('read');
    }

    protected function favorite()
    {
        return $this->builder->where('favorite', true);
    }
}
