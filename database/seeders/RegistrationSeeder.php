<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Database\Seeder;

class RegistrationSeeder extends Seeder
{
    /**
     * Seed example registrations for the demo events.
     */
    public function run(): void
    {
        $this->call(EventSeeder::class);

        $registrations = [
            'bakti-pendidikan-anak-bandung' => [
                [
                    'registration_code' => 'BC-DEMO-0001',
                    'full_name' => 'Siti Aminah',
                    'email' => 'siti.aminah@example.com',
                    'phone' => '081234567801',
                    'status' => Registration::STATUS_PENDING,
                    'registered_at' => now()->subDays(2),
                    'reviewed_at' => null,
                ],
                [
                    'registration_code' => 'BC-DEMO-0002',
                    'full_name' => 'Budi Santoso',
                    'email' => 'budi.santoso@example.com',
                    'phone' => '081234567802',
                    'status' => Registration::STATUS_REVIEWED,
                    'registered_at' => now()->subDays(2)->subHour(),
                    'reviewed_at' => now()->subDay(),
                ],
                [
                    'registration_code' => 'BC-DEMO-0003',
                    'full_name' => 'Rina Melati',
                    'email' => 'rina.melati@example.com',
                    'phone' => '081234567803',
                    'status' => Registration::STATUS_ACCEPTED,
                    'registered_at' => now()->subDays(3),
                    'reviewed_at' => now()->subDay(),
                ],
                [
                    'registration_code' => 'BC-DEMO-0004',
                    'full_name' => 'Dedi Kurniawan',
                    'email' => 'dedi.kurniawan@example.com',
                    'phone' => '081234567804',
                    'status' => Registration::STATUS_REJECTED,
                    'registered_at' => now()->subDays(3)->subHour(),
                    'reviewed_at' => now()->subDay(),
                ],
            ],
            'aksi-bersih-cikapundung' => [
                [
                    'registration_code' => 'BC-DEMO-0005',
                    'full_name' => 'Maya Sari',
                    'email' => 'maya.sari@example.com',
                    'phone' => '081234567805',
                    'status' => Registration::STATUS_PENDING,
                    'registered_at' => now()->subDay(),
                    'reviewed_at' => null,
                ],
                [
                    'registration_code' => 'BC-DEMO-0006',
                    'full_name' => 'Fajar Nugroho',
                    'email' => 'fajar.nugroho@example.com',
                    'phone' => '081234567806',
                    'status' => Registration::STATUS_ACCEPTED,
                    'registered_at' => now()->subDay()->subHour(),
                    'reviewed_at' => now()->subHours(6),
                ],
            ],
        ];

        foreach ($registrations as $slug => $items) {
            $event = Event::query()->where('slug', $slug)->first();

            if (! $event) {
                continue;
            }

            foreach ($items as $item) {
                Registration::query()->updateOrCreate(
                    [
                        'event_id' => $event->getKey(),
                        'email' => $item['email'],
                    ],
                    [
                        'registration_code' => $item['registration_code'],
                        'full_name' => $item['full_name'],
                        'phone' => $item['phone'],
                        'status' => $item['status'],
                        'registered_at' => $item['registered_at'],
                        'reviewed_at' => $item['reviewed_at'],
                    ],
                );
            }
        }
    }
}
