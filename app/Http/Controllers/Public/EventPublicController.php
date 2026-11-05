<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventPublicController extends Controller
{
    /**
     * Display the published events with search, filters, and sorting.
     */
    public function index(Request $request): View
    {
        $query = Event::with('category')->published();

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->value();

            $query->where(function ($query) use ($search): void {
                $query->where('title', 'like', '%'.$search.'%')
                    ->orWhere('summary', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('category')) {
            $categorySlug = $request->string('category')->value();

            $query->whereHas('category', function ($query) use ($categorySlug): void {
                $query->where('slug', $categorySlug);
            });
        }

        if ($request->filled('status') && in_array($request->input('status'), Event::RECRUITMENT_STATUSES, true)) {
            $query->where('recruitment_status', $request->input('status'));
        }

        match ($request->input('sort', 'date_desc')) {
            'date_asc' => $query->orderBy('event_start_at'),
            'title_asc' => $query->orderBy('title'),
            'title_desc' => $query->orderByDesc('title'),
            default => $query->orderByDesc('event_start_at'),
        };

        $events = $query->paginate(9)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('public.events.index', compact('events', 'categories'));
    }

    /**
     * Display the detail page of a published event.
     */
    public function show(Event $event): View
    {
        if (! $event->is_published) {
            abort(404);
        }

        $event->load('category', 'activeQuestions');

        return view('public.events.show', [
            'event' => $event,
            'isRegistrationOpen' => $event->isRegistrationOpen(),
            'remainingQuota' => $event->remainingQuota(),
        ]);
    }
}
