<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateRegistrationStatusRequest;
use App\Mail\RegistrationStatusChanged;
use App\Models\Event;
use App\Models\EventQuestion;
use App\Models\Registration;
use App\Models\RegistrationAnswer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class RegistrationController extends Controller
{
    /**
     * Display a listing of the registrations.
     */
    public function index(Request $request): View
    {
        $registrations = $this->filteredQuery($request)
            ->latest('registered_at')
            ->paginate(15)
            ->withQueryString();

        $events = Event::orderBy('title')->get(['id', 'title']);

        return view('admin.registrations.index', compact('registrations', 'events'));
    }

    /**
     * Export the filtered registrations as a CSV file.
     */
    public function export(Request $request): StreamedResponse
    {
        $registrations = $this->filteredQuery($request)
            ->with(['event', 'answers'])
            ->orderBy('registered_at')
            ->orderBy('id');

        $filename = 'pendaftar-bandung-care-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($registrations): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Kode Pendaftaran',
                'Nama Lengkap',
                'Email',
                'Telepon',
                'Event',
                'Tanggal Daftar',
                'Status',
                'Tanggal Review',
                'Jawaban Formulir',
            ], ',', '"', '\\');

            foreach ($registrations->lazy(200) as $registration) {
                $answers = $registration->answers
                    ->map(function ($answer): string {
                        $value = is_array($answer->answer_json)
                            ? implode(', ', array_map($this->sanitizeCsvValue(...), $answer->answer_json))
                            : $this->sanitizeCsvValue($answer->answer_text);

                        return $this->sanitizeCsvValue($answer->question_label_snapshot.': '.$value);
                    })
                    ->implode(' | ');

                fputcsv($handle, array_map($this->sanitizeCsvValue(...), [
                    $registration->registration_code,
                    $registration->full_name,
                    $registration->email,
                    $registration->phone,
                    $registration->event?->title,
                    optional($registration->registered_at)->format('Y-m-d H:i:s'),
                    $registration->status,
                    optional($registration->reviewed_at)->format('Y-m-d H:i:s'),
                    $answers,
                ]), ',', '"', '\\');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Display the given registration.
     */
    public function show(Registration $registration): View
    {
        $registration->load([
            'event.category',
            'answers',
            'statusHistories' => fn ($query) => $query->with('changedBy')->latest(),
        ]);

        return view('admin.registrations.show', compact('registration'));
    }

    /**
     * Download a file uploaded through a dynamic file question.
     */
    public function downloadAnswer(Registration $registration, RegistrationAnswer $answer): StreamedResponse
    {
        abort_unless($answer->registration_id === $registration->getKey(), 404);
        abort_unless($answer->question_type_snapshot === EventQuestion::TYPE_FILE, 404);

        $path = (string) $answer->answer_text;

        abort_if($path === '' || ! Storage::disk('local')->exists($path), 404);

        $filename = Str::slug($answer->question_label_snapshot).'.'.pathinfo($path, PATHINFO_EXTENSION);

        return Storage::disk('local')->download($path, $filename);
    }

    /**
     * Update the status of the given registration.
     */
    public function updateStatus(UpdateRegistrationStatusRequest $request, Registration $registration): RedirectResponse
    {
        $validated = $request->validated();

        if ($validated['status'] === $registration->status) {
            return back()->with('error', 'Status pendaftaran tidak berubah.');
        }

        DB::transaction(function () use ($registration, $validated): void {
            $oldStatus = $registration->status;

            $registration->update([
                'status' => $validated['status'],
                'reviewed_at' => in_array($validated['status'], [
                    Registration::STATUS_REVIEWED,
                    Registration::STATUS_ACCEPTED,
                    Registration::STATUS_REJECTED,
                ], true) ? now() : null,
            ]);

            $registration->statusHistories()->create([
                'changed_by' => auth()->id(),
                'old_status' => $oldStatus,
                'new_status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        $this->sendStatusNotification($registration->fresh());

        return back()->with('success', 'Status pendaftaran berhasil diperbarui.');
    }

    /**
     * Build the filtered registration query used by the list and the export.
     */
    private function filteredQuery(Request $request): Builder
    {
        $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ], [], [
            'date_from' => 'tanggal mulai',
            'date_to' => 'tanggal akhir',
        ]);

        return Registration::query()
            ->with('event')
            ->when($request->filled('search'), function (Builder $query) use ($request): void {
                $search = $request->string('search')->trim()->value();

                $query->where(function (Builder $query) use ($search): void {
                    $query->where('full_name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('registration_code', 'like', '%'.$search.'%');
                });
            })
            ->when($request->filled('event'), function (Builder $query) use ($request): void {
                $query->where('event_id', $request->integer('event'));
            })
            ->when($request->filled('status'), function (Builder $query) use ($request): void {
                $query->where('status', $request->input('status'));
            })
            ->when($request->filled('date_from'), function (Builder $query) use ($request): void {
                $query->where('registered_at', '>=', $request->date('date_from')->startOfDay());
            })
            ->when($request->filled('date_to'), function (Builder $query) use ($request): void {
                $query->where('registered_at', '<=', $request->date('date_to')->endOfDay());
            });
    }

    /**
     * Prevent spreadsheet formula injection when a cell starts with a
     * formula trigger character.
     */
    private function sanitizeCsvValue(mixed $value): string
    {
        $value = (string) ($value ?? '');

        if ($value !== '' && preg_match('/^[=+\-@\t\r]/', $value) === 1) {
            return "'".$value;
        }

        return $value;
    }

    /**
     * Notify the volunteer about the new registration status.
     */
    private function sendStatusNotification(Registration $registration): void
    {
        try {
            Mail::to($registration->email)->send(new RegistrationStatusChanged($registration));
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
