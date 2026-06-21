<?php

namespace App\Http\Controllers;

use App\Models\Newsletter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PageController extends Controller
{
    public function about()
    {
        return theme_view('pages.about');
    }

    public function contact()
    {
        return theme_view('pages.contact');
    }

    public function contactSend(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email',
            'message' => 'required',
        ]);

        try {
            Mail::raw("From: {$data['name']} ({$data['email']})\n\n{$data['message']}", function ($msg) {
                $msg->to(config('mail.from.address'))
                    ->subject('Contact Form: ' . config('app.name'));
            });
        } catch (\Exception $e) {
            // If mail not configured, still show success
        }

        return back()->with('success', 'Message sent! We\'ll get back to you soon.');
    }

    public function newsletter(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        Newsletter::firstOrCreate(
            ['email' => $request->email],
            ['domain_id' => config('app.domain_id'), 'active' => true]
        );

        return back()->with('success', 'Berlangganan newsletter berhasil!');
    }
}
