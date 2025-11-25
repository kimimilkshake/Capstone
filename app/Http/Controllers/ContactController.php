<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function send(Request $request)
    {
        // Validate
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'subject' => 'required',
            'message' => 'required',
        ]);

        // Email details
        $data = [
            'sender_name' => $request->name,
            'sender_email' => $request->email,
            'subject' => $request->subject,
            'body_message' => $request->message
        ];

        // Send email
        Mail::send('passenger.contact', $data, function ($message) use ($data) {
            $message->from($data['sender_email'], $data['sender_name']);
            $message->to('sophiaannu.cohon@gmail.com')
                    ->subject('Website Contact: '.$data['subject']);
        });

        return back()->with('success', 'Message sent! We will reply to your email.');
    }
}
