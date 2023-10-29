<?php

namespace App\Listeners;

use App\Events\EmailReceived;
use App\Automation;
use App\Http\Resources\EmailResource;
use Illuminate\Support\Facades\Mail;
use App\Mail\ForwardEmail;
use Illuminate\Support\Facades\Http;

class AutomationListener
{
    /**
     * Handle the event.
     *
     * @param  EmailReceived  $event
     * @return void
     */
    public function handle(EmailReceived $event)
    {
        if (!config('mailcare.automations')) {
            return;
        }

        $automations = Automation::all();

        $actionDeleteEmail = false;
        foreach ($automations as $automation) {
            $headers = [];
            $headers['X-MailCare-Title'] = $automation->title;

            if (!empty($automation->subject)) {
                if (preg_match('#'.$automation->subject.'#i', $event->email->subject)) {
                    $headers['X-MailCare-Subject'] = 'HIT';
                } else {
                    continue;
                }
            }
            if (!empty($automation->sender)) {
                if (preg_match('#'.$automation->sender.'#i', $event->email->sender->email)) {
                    $headers['X-MailCare-Sender'] = 'HIT';
                } else {
                    continue;
                }
            }
            if (!empty($automation->inbox)) {
                if (preg_match('#'.$automation->inbox.'#i', $event->email->inbox->email)) {
                    $headers['X-MailCare-Inbox'] = 'HIT';
                } else {
                    continue;
                }
            }
            if ($automation->has_attachments) {
                if ($event->email->attachments->count() == 0) {
                    continue;
                }
                $headers['X-MailCare-Has-Attachments'] = 'HIT';
            }

            if ($automation->action_secret_token) {
                $headers['X-MailCare-Secret-Token'] = $automation->action_secret_token;
            }

            $automation->increment('emails_received');
            if ($automation->action_delete_email && $actionDeleteEmail == false) {
                $actionDeleteEmail = true;
            }

            if (config('mailcare.forward') && !empty($automation->action_email)) {
                try {
                    Mail::to($automation->action_email)->send(new ForwardEmail($event->email));
                    $automation->in_error = false;
                } catch (\Exception $e) {
                    $automation->in_error = true;
                }
            } else {
                $body = $automation->post_raw ?
                    file_get_contents($event->email->fullPath())
                    : (new EmailResource($event->email))->response()->content();
                $contentType = $automation->post_raw ? 'message/rfc2822' : 'application/json';

                $response = Http::withHeaders($headers)->withBody(
                    $body,
                    $contentType
                )->post($automation->action_url);

                if ($response->failed()) {
                    $automation->in_error = true;
                } else {
                    $automation->in_error = false;
                }
            }

            $automation->save();
        }

        if ($actionDeleteEmail) {
            $event->email->delete();
        }
    }
}
