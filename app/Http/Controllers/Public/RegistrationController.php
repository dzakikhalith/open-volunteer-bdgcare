<?php

namespace App\Http\Controllers\Public;

use App\Exceptions\RegistrationNotOpenException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRegistrationRequest;
use App\Models\Event;
use App\Services\RegistrationService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function __construct(private readonly RegistrationService $registrations) {}

    /**
     * Show the registration form for an event.
     */
    public function create(Event $event): View|RedirectResponse
    {
        abort_unless($event->is_published, 404);

        if (! $event->isRegistrationOpen()) {
            return redirect()
                ->route('events.show', $event)
                ->with('error', 'Pendaftaran untuk event ini sedang tidak dibuka.');
        }

        $event->load('category', 'activeQuestions');

        return view('public.registrations.create', compact('event'));
    }

    /**
     * Store a new volunteer registration.
     */
    public function store(StoreRegistrationRequest $request, Event $event): RedirectResponse
    {
        abort_unless($event->is_published, 404);

        if (! $event->isRegistrationOpen()) {
            return redirect()
                ->route('events.show', $event)
                ->with('error', 'Pendaftaran untuk event ini sedang tidak dibuka.');
        }

        try {
            $registration = $this->registrations->register($event, $request->validated());
        } catch (RegistrationNotOpenException $exception) {
            return redirect()
                ->route('events.show', $event)
                ->with('error', $exception->getMessage());
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'email' => 'Email tersebut sudah digunakan untuk mendaftar pada event ini.',
            ]);
        }

        return redirect()
            ->to(URL::signedRoute('registrations.show', $registration))
            ->with('success', 'Pendaftaran berhasil dikirim. Simpan kode pendaftaran untuk memantau status.');
    }
}
