<?php

namespace Database\Seeders;

use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Seeder;

class RegistrationStatusHistorySeeder extends Seeder
{
    /**
     * Seed status history entries for the demo registrations.
     */
    public function run(): void
    {
        $this->call(RegistrationSeeder::class);

        $adminId = User::query()->where('role', 'admin')->value('id');

        $notes = [
            Registration::STATUS_REVIEWED => 'Berkas pendaftaran sudah diperiksa.',
            Registration::STATUS_ACCEPTED => 'Selamat, pendaftaran Anda diterima.',
            Registration::STATUS_REJECTED => 'Mohon maaf, pendaftaran belum dapat diterima.',
            Registration::STATUS_WITHDRAWN => 'Pendaftar menarik diri dari kegiatan.',
        ];

        $registrations = Registration::query()
            ->whereIn('registration_code', [
                'BC-DEMO-0002',
                'BC-DEMO-0003',
                'BC-DEMO-0004',
                'BC-DEMO-0006',
            ])
            ->get();

        foreach ($registrations as $registration) {
            if ($registration->status === Registration::STATUS_PENDING) {
                continue;
            }

            $registration->statusHistories()->firstOrCreate(
                [
                    'registration_id' => $registration->getKey(),
                    'new_status' => $registration->status,
                ],
                [
                    'changed_by' => $adminId,
                    'old_status' => Registration::STATUS_PENDING,
                    'notes' => $notes[$registration->status] ?? null,
                ],
            );
        }
    }
}
