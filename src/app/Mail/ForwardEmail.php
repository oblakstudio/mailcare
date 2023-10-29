<?php

namespace App\Mail;

use App\Email;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use PhpMimeMailParser\Parser;

class ForwardEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $email;

    private $parser;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Email $email)
    {
        $this->email = $email;
        $this->parser = new Parser;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->parser->setPath($this->email->fullPath());

        $build = $this
            ->from($this->email->inbox->email)
            ->subject($this->parser->getHeader('subject'))
            ->view('emails.forward.html')->with([
                'html' => $this->parser->getMessageBody('html'),
            ])
            ->text('emails.forward.text')->with([
                'text' => $this->parser->getMessageBody('text'),
            ]);

        foreach ($this->parser->getAttachments() as $attachment) {
            $build->attachData(
                stream_get_contents($attachment->getStream()),
                $attachment->getFileName(),
                ['mime' => $attachment->getContentType()]
            );
        }

        return $build;
    }
}
