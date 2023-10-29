<?php

namespace App\Http\Controllers;

use App\Email;
use PhpMimeMailParser\Parser;

class AttachmentsController extends ApiController
{
    public function show(Email $email, $attachmentId)
    {
        $attachment = $email->attachments()->findOrFail($attachmentId);

        $parser = new Parser;
        $parser->setPath($email->fullPath());

        $attachmentParsed = collect($parser->getAttachments())
            ->first(function ($attachmentParsed) use ($attachment) {
                return $attachment->headers_hashed === $attachment->hashHeaders($attachmentParsed->getHeaders());
            });

        if (! $attachmentParsed) {
            return $this->respondNotFound('Attachment not found.');
        }

        $data = stream_get_contents($attachmentParsed->getStream());

        return response($data)->header('Content-Type', $attachmentParsed->getContentType());
    }
}
