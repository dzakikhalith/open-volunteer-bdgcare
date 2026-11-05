<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventRequest;
use App\Models\Category;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EventController extends Controller
{
    /**
     * Display a listing of the events.
     */
    public function index(Request $request): View
    {
        $query = Event::query()->with('category')->withCount('registrations');

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->value();
            $query->where(function ($query) use ($search) {
                $query->where('title', 'like', '%'.$search.'%')
                    ->orWhere('slug', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('category', function ($query) use ($request) {
                $query->where('slug', $request->input('category'));
            });
        }

        if ($request->filled('status')) {
            $query->where('recruitment_status', $request->input('status'));
        }

        $events = $query->latest('event_start_at')->paginate(10)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('admin.events.index', compact('events', 'categories'));
    }

    /**
     * Show the form for creating a new event.
     */
    public function create(): View
    {
        $event = new Event(['recruitment_status' => 'DRAFT']);
        $categories = Category::orderBy('name')->get();

        return view('admin.events.create', compact('event', 'categories'));
    }

    /**
     * Store a newly created event.
     */
    public function store(EventRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        unset($validated['poster'], $validated['remove_poster']);

        $validated['created_by'] = $request->user()->getKey();

        if ($request->hasFile('poster')) {
            $validated['poster_path'] = $request->file('poster')->store('event-posters', 'public');
        }

        $event = Event::create($validated);

        return redirect()
            ->route('admin.events.edit', $event)
            ->with('success', 'Event berhasil dibuat.');
    }

    /**
     * Show the form for editing the given event.
     */
    public function edit(Event $event): View
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.events.edit', compact('event', 'categories'));
    }

    /**
     * Update the given event.
     */
    public function update(EventRequest $request, Event $event): RedirectResponse
    {
        $validated = $request->validated();
        $removePoster = (bool) $validated['remove_poster'];
        unset($validated['poster'], $validated['remove_poster']);

        if ($request->hasFile('poster')) {
            if ($event->poster_path) {
                Storage::disk('public')->delete($event->poster_path);
            }

            $validated['poster_path'] = $request->file('poster')->store('event-posters', 'public');
        } elseif ($removePoster && $event->poster_path) {
            Storage::disk('public')->delete($event->poster_path);
            $validated['poster_path'] = null;
        }

        $event->update($validated);

        return redirect()
            ->route('admin.events.edit', $event)
            ->with('success', 'Event berhasil diperbarui.');
    }

    /**
     * Soft delete the given event.
     */
    public function destroy(Event $event): RedirectResponse
    {
        $event->delete();

        return redirect()
            ->route('admin.events.index')
            ->with('success', 'Event berhasil dihapus.');
    }
}
