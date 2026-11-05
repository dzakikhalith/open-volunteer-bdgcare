@props(['event'])

<div class="card card-rounded h-100">
    <div class="ratio ratio-16x9">
        @if ($event->poster_path)
            <img src="{{ asset('storage/' . $event->poster_path) }}" class="card-img-top object-fit-cover" alt="{{ $event->title }}">
        @else
            <div class="d-flex align-items-center justify-content-center bg-light text-muted">
                <i class="bi bi-image fs-1"></i>
            </div>
        @endif
    </div>
    <div class="card-body d-flex flex-column">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <h5 class="card-title mb-0">{{ $event->title }}</h5>
            @include('components.status-badge', ['status' => $event->recruitment_status])
        </div>
        @if ($event->category)
            <span class="badge text-bg-light text-dark-teal mb-2">{{ $event->category->name }}</span>
        @endif
        <p class="card-text text-muted small mb-2">
            <i class="bi bi-calendar-event me-1"></i> {{ optional($event->event_start_at)->format('d M Y') }}
            @if ($event->event_end_at && $event->event_start_at && !$event->event_start_at->isSameDay($event->event_end_at))
                - {{ $event->event_end_at->format('d M Y') }}
            @endif
        </p>
        <p class="card-text text-muted small mb-2">
            <i class="bi bi-geo-alt me-1"></i> {{ $event->location }}
        </p>
        @if ($event->registration_end_at)
            <p class="card-text text-muted small mb-3">
                <i class="bi bi-clock me-1"></i> Batas pendaftaran: {{ $event->registration_end_at->format('d M Y H:i') }}
            </p>
        @endif
        <div class="mt-auto">
            <a href="{{ route('events.show', $event) }}" class="btn btn-outline-dark-teal w-100">Detail Event</a>
        </div>
    </div>
</div>
