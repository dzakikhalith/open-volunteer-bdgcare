<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the dashboard statistics.
     */
    public function index(): View
    {
        $stats = [
            'total_events' => Event::count(),
            'open_events' => Event::where('recruitment_status', 'OPEN')->count(),
            'upcoming_events' => Event::where('event_start_at', '>=', now())->count(),
            'total_registrations' => Registration::count(),
            'pending_registrations' => Registration::where('status', Registration::STATUS_PENDING)->count(),
            'new_registrations' => Registration::where('registered_at', '>=', now()->subDays(7))->count(),
        ];

        $recentRegistrations = Registration::with('event')
            ->latest('registered_at')
            ->take(5)
            ->get();

        $registrationsPerEvent = Event::query()
            ->withCount('registrations')
            ->orderByDesc('registrations_count')
            ->take(5)
            ->get();

        $recentEvents = Event::query()
            ->with('category')
            ->withCount('registrations')
            ->latest('event_start_at')
            ->take(5)
            ->get();

        $maxRegistrations = max(1, (int) $registrationsPerEvent->max('registrations_count'));

        return view('admin.dashboard', compact(
            'stats',
            'recentRegistrations',
            'registrationsPerEvent',
            'recentEvents',
            'maxRegistrations',
        ));
    }
}
