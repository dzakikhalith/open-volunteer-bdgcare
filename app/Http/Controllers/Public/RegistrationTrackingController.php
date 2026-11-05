<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RegistrationTrackingController extends Controller
{
    /**
     * Show the registration tracking form.
     */
    public function track(): View
    {
        return view('public.registrations.track');
    }

    /**
     * Look up a registration by its code and redirect to a signed status URL.
     */
    public function lookup(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'registration_code' => ['required', 'string', 'max:50'],
        ], [], [
            'registration_code' => 'kode pendaftaran',
        ]);

        $registration = Registration::query()
            ->where('registration_code', Str::upper(trim($validated['registration_code'])))
            ->first();

        if (! $registration) {
            return back()
                ->withInput()
                ->withErrors(['registration_code' => 'Kode pendaftaran tidak ditemukan.']);
        }

        return redirect()->to(URL::signedRoute('registrations.show', $registration));
    }

    /**
     * Show the status of a registration.
     *
     * Personal answers and contact details are intentionally not exposed on
     * this page. Only the minimum information needed to follow the process is
     * rendered, and the URL must carry a valid signature.
     */
    public function show(Registration $registration): View
    {
        $registration->load([
            'event',
            'statusHistories' => fn ($query) => $query->orderBy('created_at'),
        ]);

        return view('public.registrations.show', compact('registration'));
    }
}
