<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Automation;
use Illuminate\Support\Str;
use App\Http\Resources\AutomationResource;
use App\Rules\Forward;

class AutomationsController extends Controller
{
    public function index()
    {
        return AutomationResource::collection(Automation::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required'],
            'has_attachment' => ['boolean'],
            'action_url' => ['required_without:action_email', 'url', 'nullable'],
            'action_email' => ['required_without:action_url', 'email', 'nullable', new Forward],
            'action_delete_email' => ['boolean'],
            'post_raw' => ['boolean'],
        ]);

        Automation::create([
            'id' => (string) Str::uuid(),
            'title' => $request->input('title'),
            'sender' => $request->input('sender'),
            'inbox' => $request->input('inbox'),
            'subject' => $request->input('subject'),
            'has_attachments' => $request->input('has_attachments'),
            'action_url' => $request->input('action_url'),
            'action_email' => $request->input('action_email'),
            'action_secret_token' => $request->input('action_secret_token'),
            'action_delete_email' => $request->input('action_delete_email'),
            'post_raw' => $request->input('post_raw'),
        ]);
    }

    public function update(Request $request, Automation $automation)
    {
        $request->validate([
            'title' => ['required'],
            'has_attachment' => ['boolean'],
            'action_url' => ['required_without:action_email', 'url', 'nullable'],
            'action_email' => ['required_without:action_url', 'email', 'nullable', new Forward],
            'action_delete_email' => ['boolean'],
            'post_raw' => ['boolean'],
        ]);

        $automation->update([
            'title' => $request->input('title'),
            'sender' => $request->input('sender'),
            'inbox' => $request->input('inbox'),
            'subject' => $request->input('subject'),
            'has_attachments' => $request->input('has_attachments'),
            'action_url' => $request->input('action_url'),
            'action_email' => $request->input('action_email'),
            'action_secret_token' => $request->input('action_secret_token'),
            'action_delete_email' => $request->input('action_delete_email'),
            'post_raw' => $request->input('post_raw'),
        ]);
    }

    public function destroy(Automation $automation)
    {
        $automation->delete();
    }
}
