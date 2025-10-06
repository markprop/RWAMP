<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Display the homepage.
     */
    public function index()
    {
        return view('pages.index', [
            'title' => 'RWAMP - The Currency of Real Estate Investments',
            'description' => 'RWAMP is the official token for investing in real estate projects across Dubai, Pakistan, and Saudi Arabia. Powered by Mark Properties.',
            'keywords' => 'RWAMP, real estate, token, investment, Dubai, Pakistan, Saudi Arabia, Mark Properties',
            'ogTitle' => 'RWAMP - The Currency of Real Estate Investments',
            'ogDescription' => 'RWAMP is the official token for investing in real estate projects across Dubai, Pakistan, and Saudi Arabia.',
            'ogImage' => asset('images/logo.jpeg'),
            'twitterTitle' => 'RWAMP - The Currency of Real Estate Investments',
            'twitterDescription' => 'RWAMP is the official token for investing in real estate projects across Dubai, Pakistan, and Saudi Arabia.',
            'twitterImage' => asset('images/logo.jpeg'),
        ]);
    }

    /**
     * Display the about page.
     */
    public function about()
    {
        return view('pages.about', [
            'title' => 'About RWAMP - Real Estate Investment Token',
            'description' => 'Learn about RWAMP, the official token for real estate investments across Dubai, Pakistan, and Saudi Arabia. Powered by Mark Properties.',
            'keywords' => 'about RWAMP, real estate token, investment, Dubai, Pakistan, Saudi Arabia, Mark Properties',
        ]);
    }

    /**
     * Display the contact page.
     */
    public function contact()
    {
        return view('pages.contact', [
            'title' => 'Contact RWAMP - Get in Touch',
            'description' => 'Contact RWAMP for investment opportunities, reseller programs, and real estate project inquiries.',
            'keywords' => 'contact RWAMP, investment inquiry, reseller program, real estate contact',
        ]);
    }
}
