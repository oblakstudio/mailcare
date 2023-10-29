<?php

namespace App\Console\Commands;

use App\Attachment;
use App\Email;
use App\Events\EmailReceived;
use App\Inbox;
use App\Sender;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use PhpMimeMailParser\Parser;

class ReceiveEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mailcare:email-receive {file=stream : The file of the email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Receive email from mail server to integrate to database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Receiving email ...');
        $file = $this->argument('file');
        $this->info("from $file");

        $parser = $this->getParser($file);

        $sender = Sender::updateOrCreate(
            [
                'email' => $parser->getAddresses('from')[0]['address'],
            ],
            [
                'display_name' => $parser->getAddresses('from')[0]['display'],
            ]
        );

        $inbox = Inbox::updateOrCreate(
            [
                'email' => $parser->getAddresses('to')[0]['address'],
            ],
            [
                'display_name' => $parser->getAddresses('to')[0]['display'],
            ]
        );

        $email = new Email;
        $email->subject = $parser->getHeader('subject');

        if (! empty($parser->getMessageBody('html'))) {
            $email->has_html = true;
        }
        if (! empty($parser->getMessageBody('text'))) {
            $email->has_text = true;
        }

        $email->sender()->associate($sender);

        $inbox->emails()->save($email);

        Storage::put($email->path(), ($file == 'stream') ? $this->rawEmail : file_get_contents($file));

        $email->size_in_bytes = Storage::size($email->path());

        $email->save();

        foreach ($parser->getAttachments() as $attachmentParsed) {
            $attachment = new Attachment;
            $attachment->email_id = $email->id;
            $attachment->headers_hashed = $attachment->hashHeaders($attachmentParsed->getHeaders());
            $attachment->file_name = $attachmentParsed->getFileName();
            $attachment->content_type = $attachmentParsed->getContentType();
            $attachment->size_in_bytes = strlen($attachmentParsed->getMimePartStr());
            $attachment->save();
        }

        event(new EmailReceived($email->fresh()));

        return 0;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getParser($file)
    {
        $parser = new Parser;

        if ($file == 'stream') {
            $this->info('from stream');
            $fd = fopen('php://stdin', 'r');
            $this->rawEmail = '';
            while (! feof($fd)) {
                $this->rawEmail .= fread($fd, 1024);
            }
            fclose($fd);
            $parser->setText($this->rawEmail);
        } else {
            $this->info("from path $file");
            $parser->setPath($file);
        }

        return $parser;
    }
}
