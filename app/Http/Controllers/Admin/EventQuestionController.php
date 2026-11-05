<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventQuestionRequest;
use App\Models\Event;
use App\Models\EventQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EventQuestionController extends Controller
{
    /**
     * Display the questions of the given event.
     */
    public function index(Event $event): View
    {
        $questions = $event->questions()->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.questions.index', compact('event', 'questions'));
    }

    /**
     * Preview the public registration form built from the active questions.
     */
    public function preview(Event $event): View
    {
        $event->load('category', 'activeQuestions');

        return view('admin.questions.preview', compact('event'));
    }

    /**
     * Show the form for creating a new question.
     */
    public function create(Event $event): View
    {
        $question = new EventQuestion([
            'type' => EventQuestion::TYPE_TEXT,
            'is_active' => true,
            'sort_order' => ($event->questions()->max('sort_order') ?? 0) + 1,
        ]);

        return view('admin.questions.create', compact('event', 'question'));
    }

    /**
     * Store a newly created question.
     */
    public function store(EventQuestionRequest $request, Event $event): RedirectResponse
    {
        $validated = $request->validated();
        unset($validated['min_length'], $validated['max_length']);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $event->questions()->create($validated);

        return redirect()
            ->route('admin.events.questions.index', $event)
            ->with('success', 'Pertanyaan berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the given question.
     */
    public function edit(Event $event, EventQuestion $question): View
    {
        return view('admin.questions.edit', compact('event', 'question'));
    }

    /**
     * Update the given question.
     */
    public function update(EventQuestionRequest $request, Event $event, EventQuestion $question): RedirectResponse
    {
        $validated = $request->validated();
        unset($validated['min_length'], $validated['max_length']);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $question->update($validated);

        return redirect()
            ->route('admin.events.questions.index', $event)
            ->with('success', 'Pertanyaan berhasil diperbarui.');
    }

    /**
     * Move the given question one position up or down.
     */
    public function move(Request $request, Event $event, EventQuestion $question): RedirectResponse
    {
        abort_unless($question->event_id === $event->id, 404);

        $direction = $request->validate([
            'direction' => ['required', Rule::in(['up', 'down'])],
        ], [], [
            'direction' => 'arah perpindahan',
        ])['direction'];

        DB::transaction(function () use ($event, $question, $direction): void {
            $questions = $event->questions()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->values();

            $index = $questions->search(fn (EventQuestion $item): bool => $item->is($question));

            if ($index === false) {
                return;
            }

            $targetIndex = $direction === 'up' ? $index - 1 : $index + 1;

            if ($targetIndex < 0 || $targetIndex >= $questions->count()) {
                return;
            }

            $moved = $questions->splice($index, 1)->first();
            $questions->splice($targetIndex, 0, [$moved]);

            foreach ($questions as $position => $item) {
                $item->update(['sort_order' => $position + 1]);
            }
        });

        return redirect()
            ->route('admin.events.questions.index', $event)
            ->with('success', 'Urutan pertanyaan berhasil diperbarui.');
    }

    /**
     * Remove the given question.
     */
    public function destroy(Event $event, EventQuestion $question): RedirectResponse
    {
        $question->delete();

        return redirect()
            ->route('admin.events.questions.index', $event)
            ->with('success', 'Pertanyaan berhasil dihapus.');
    }
}
