@extends('layouts.admin')

@section('title', 'Pertanyaan Formulir - Bandung Care')
@section('page-title', 'Pertanyaan Formulir')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold text-dark-teal mb-1">Pertanyaan Formulir</h1>
            <p class="text-muted mb-0">{{ $event->title }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.events.questions.preview', $event) }}" class="btn btn-outline-dark-teal">
                <i class="bi bi-eye me-1"></i> Preview Formulir
            </a>
            <a href="{{ route('admin.events.questions.create', $event) }}" class="btn btn-cherry">
                <i class="bi bi-plus-lg me-1"></i> Tambah Pertanyaan
            </a>
            <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-outline-dark-teal">
                <i class="bi bi-pencil me-1"></i> Edit Event
            </a>
            <a href="{{ route('admin.events.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="card card-rounded">
        <div class="card-body">
            @if ($questions->isEmpty())
                @include('components.empty-state', [
                    'title' => 'Belum ada pertanyaan',
                    'description' => 'Tambahkan pertanyaan tambahan yang harus dijawab calon volunteer.',
                ])
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 70px;">Urutan</th>
                                <th>Pertanyaan</th>
                                <th>Tipe</th>
                                <th>Wajib</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($questions as $question)
                                <tr>
                                    <td>{{ $question->sort_order }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $question->label }}</div>
                                        <small class="text-muted">{{ $question->field_key }}</small>
                                        @if ($question->options)
                                            <div class="small text-muted mt-1">
                                                {{ count($question->options) }} pilihan
                                            </div>
                                        @endif
                                    </td>
                                    <td><code>{{ $question->type }}</code></td>
                                    <td>
                                        @if ($question->is_required)
                                            <span class="badge text-bg-primary">Wajib</span>
                                        @else
                                            <span class="badge text-bg-light text-dark">Opsional</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($question->is_active)
                                            <span class="badge text-bg-success">Aktif</span>
                                        @else
                                            <span class="badge text-bg-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            <form method="POST"
                                                action="{{ route('admin.events.questions.move', [$event, $question]) }}">
                                                @csrf
                                                <input type="hidden" name="direction" value="up">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary"
                                                    title="Naikkan" @disabled($loop->first)>
                                                    <i class="bi bi-arrow-up"></i>
                                                </button>
                                            </form>
                                            <form method="POST"
                                                action="{{ route('admin.events.questions.move', [$event, $question]) }}">
                                                @csrf
                                                <input type="hidden" name="direction" value="down">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary"
                                                    title="Turunkan" @disabled($loop->last)>
                                                    <i class="bi bi-arrow-down"></i>
                                                </button>
                                            </form>
                                            <a href="{{ route('admin.events.questions.edit', [$event, $question]) }}"
                                                class="btn btn-sm btn-outline-dark-teal" title="Edit pertanyaan">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST"
                                                action="{{ route('admin.events.questions.destroy', [$event, $question]) }}"
                                                onsubmit="return confirm('Hapus pertanyaan ini? Jawaban pendaftar yang sudah ada tidak dapat ditampilkan lagi.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    title="Hapus pertanyaan">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
