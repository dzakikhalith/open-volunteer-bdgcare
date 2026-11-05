<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventQuestion;
use Illuminate\Database\Seeder;

class EventQuestionSeeder extends Seeder
{
    /**
     * Seed the dynamic registration questions for the demo events.
     */
    public function run(): void
    {
        $this->call(EventSeeder::class);

        $questions = [
            'bakti-pendidikan-anak-bandung' => [
                [
                    'label' => 'Motivasi Mengikuti Kegiatan',
                    'field_key' => 'motivation',
                    'type' => EventQuestion::TYPE_TEXTAREA,
                    'placeholder' => 'Ceritakan alasan Anda ingin bergabung',
                    'is_required' => true,
                ],
                [
                    'label' => 'Pengalaman Relawan',
                    'field_key' => 'experience',
                    'type' => EventQuestion::TYPE_TEXTAREA,
                    'placeholder' => 'Opsional',
                    'is_required' => false,
                ],
                [
                    'label' => 'Ukuran Kaos',
                    'field_key' => 'shirt_size',
                    'type' => EventQuestion::TYPE_SELECT,
                    'options' => ['S', 'M', 'L', 'XL'],
                    'is_required' => true,
                ],
                [
                    'label' => 'Bidang yang Diminati',
                    'field_key' => 'interests',
                    'type' => EventQuestion::TYPE_CHECKBOX,
                    'options' => ['Membaca', 'Menulis', 'Seni', 'Olahraga'],
                    'is_required' => false,
                ],
            ],
            'aksi-bersih-cikapundung' => [
                [
                    'label' => 'Motivasi Mengikuti Kegiatan',
                    'field_key' => 'motivation',
                    'type' => EventQuestion::TYPE_TEXTAREA,
                    'placeholder' => 'Ceritakan alasan Anda ingin bergabung',
                    'is_required' => true,
                ],
                [
                    'label' => 'Transportasi',
                    'field_key' => 'transportation',
                    'type' => EventQuestion::TYPE_RADIO,
                    'options' => ['Mandiri', 'Bersama komunitas'],
                    'is_required' => true,
                ],
                [
                    'label' => 'Ketersediaan Waktu',
                    'field_key' => 'availability',
                    'type' => EventQuestion::TYPE_TEXT,
                    'placeholder' => 'Contoh: pagi hingga siang',
                    'is_required' => false,
                ],
            ],
            'donor-darah-bersama-bandung-care' => [
                [
                    'label' => 'Golongan Darah',
                    'field_key' => 'blood_type',
                    'type' => EventQuestion::TYPE_SELECT,
                    'options' => ['A', 'B', 'AB', 'O'],
                    'is_required' => true,
                ],
                [
                    'label' => 'Riwayat Donor Terakhir',
                    'field_key' => 'last_donation',
                    'type' => EventQuestion::TYPE_DATE,
                    'is_required' => false,
                ],
            ],
        ];

        foreach ($questions as $slug => $items) {
            $event = Event::query()->where('slug', $slug)->first();

            if (! $event) {
                continue;
            }

            foreach ($items as $sortOrder => $item) {
                EventQuestion::query()->updateOrCreate(
                    [
                        'event_id' => $event->getKey(),
                        'field_key' => $item['field_key'],
                    ],
                    [
                        'label' => $item['label'],
                        'type' => $item['type'],
                        'placeholder' => $item['placeholder'] ?? null,
                        'help_text' => $item['help_text'] ?? null,
                        'is_required' => $item['is_required'],
                        'options' => $item['options'] ?? null,
                        'validation_rules' => $item['validation_rules'] ?? null,
                        'sort_order' => $sortOrder,
                        'is_active' => true,
                    ],
                );
            }
        }
    }
}
