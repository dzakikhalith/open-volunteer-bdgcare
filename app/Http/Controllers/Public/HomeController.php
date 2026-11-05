<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $events = Event::with('category')
            ->openForRegistration()
            ->orderBy('event_start_at')
            ->take(3)
            ->get();

        return view('public.home', compact('events'));
    }
}
