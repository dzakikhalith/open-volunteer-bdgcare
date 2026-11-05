@props(['status'])

@php
    $map = [
        'DRAFT' => ['label' => 'Draft', 'class' => 'text-bg-secondary'],
        'SCHEDULED' => ['label' => 'Segera Dibuka', 'class' => 'text-bg-warning'],
        'OPEN' => ['label' => 'Open Recruitment', 'class' => 'text-bg-success'],
        'CLOSED' => ['label' => 'Ditutup', 'class' => 'text-bg-danger'],
        'COMPLETED' => ['label' => 'Selesai', 'class' => 'text-bg-dark'],
        'PENDING' => ['label' => 'Menunggu Review', 'class' => 'text-bg-warning'],
        'REVIEWED' => ['label' => 'Sedang Direview', 'class' => 'text-bg-info'],
        'ACCEPTED' => ['label' => 'Diterima', 'class' => 'text-bg-success'],
        'REJECTED' => ['label' => 'Ditolak', 'class' => 'text-bg-danger'],
        'WITHDRAWN' => ['label' => 'Dibatalkan', 'class' => 'text-bg-secondary'],
    ];
    $data = $map[$status] ?? ['label' => $status, 'class' => 'text-bg-secondary'];
@endphp

<span class="badge badge-status {{ $data['class'] }}">{{ $data['label'] }}</span>
