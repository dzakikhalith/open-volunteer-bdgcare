@props(['title' => 'Belum ada data', 'description' => 'Saat ini belum ada data yang ditampilkan.'])

<div class="empty-state">
    <i class="bi bi-inbox fs-1 mb-2 d-block"></i>
    <h5 class="fw-semibold">{{ $title }}</h5>
    <p class="mb-0">{{ $description }}</p>
</div>
