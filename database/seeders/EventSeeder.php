<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Stable slugs used to keep demo data idempotent.
     *
     * @var list<string>
     */
    public const DEMO_SLUGS = [
        'bakti-pendidikan-anak-bandung',
        'aksi-bersih-cikapundung',
        'donor-darah-bersama-bandung-care',
    ];

    /**
     * Seed example events for local development and demos.
     */
    public function run(): void
    {
        $this->call(CategorySeeder::class);

        $adminId = User::query()->where('role', 'admin')->value('id');

        $events = [
            [
                'slug' => 'bakti-pendidikan-anak-bandung',
                'category' => 'pendidikan',
                'title' => 'Bakti Pendidikan Anak Bandung',
                'summary' => 'Mengajar dan mendampingi anak-anak Bandung dalam kegiatan literasi dan kreativitas.',
                'description' => "Bandung Care mengajak volunteer untuk terlibat langsung dalam kegiatan belajar bersama anak-anak.\n\nKegiatan meliputi pendampingan membaca, kelas kreativitas, dan permainan edukatif. Volunteer akan dibagi ke dalam kelompok kecil dan didampingi oleh koordinator lapangan.",
                'location' => 'Rumah Belajar Bandung Care, Jl. Sukajadi No. 12, Bandung',
                'event_start_at' => now()->addDays(10)->setTime(8, 0),
                'event_end_at' => now()->addDays(10)->setTime(12, 0),
                'registration_start_at' => now()->subDays(3),
                'registration_end_at' => now()->addDays(7),
                'quota' => 30,
                'requirements' => "- Usia minimal 17 tahun\n- Bersedia mengikuti briefing daring\n- Memiliki komitmen dan empati kepada anak-anak",
                'recruitment_status' => 'OPEN',
            ],
            [
                'slug' => 'aksi-bersih-cikapundung',
                'category' => 'lingkungan',
                'title' => 'Aksi Bersih Cikapundung',
                'summary' => 'Kerja bakti membersihkan bantaran sungai dan edukasi pemilahan sampah.',
                'description' => "Kegiatan bersih-bersih sungai Cikapundung bersama warga dan komunitas lingkungan Bandung.\n\nVolunteer akan membantu memilah sampah, menanam tanaman penahan erosi, dan membagikan edukasi singkat kepada pengunjung.",
                'location' => 'Bantaran Sungai Cikapundung, Bandung',
                'event_start_at' => now()->addDays(21)->setTime(7, 0),
                'event_end_at' => now()->addDays(21)->setTime(11, 0),
                'registration_start_at' => now()->addDays(5),
                'registration_end_at' => now()->addDays(18),
                'quota' => 50,
                'requirements' => "- Usia minimal 16 tahun\n- Membawa pakaian ganti dan alas kaki tertutup\n- Siap bekerja di area terbuka",
                'recruitment_status' => 'SCHEDULED',
            ],
            [
                'slug' => 'donor-darah-bersama-bandung-care',
                'category' => 'kesehatan',
                'title' => 'Donor Darah Bersama Bandung Care',
                'summary' => 'Kegiatan donor darah bekerja sama dengan PMI Kota Bandung.',
                'description' => "Bandung Care bersama PMI Kota Bandung mengadakan kegiatan donor darah untuk membantu kebutuhan stok darah di rumah sakit.\n\nPendaftaran untuk periode ini telah ditutup. Terima kasih kepada seluruh pendonor yang telah berpartisipasi.",
                'location' => 'Aula Kecamatan Coblong, Bandung',
                'event_start_at' => now()->subDays(20)->setTime(8, 0),
                'event_end_at' => now()->subDays(20)->setTime(13, 0),
                'registration_start_at' => now()->subDays(40),
                'registration_end_at' => now()->subDays(25),
                'quota' => 100,
                'requirements' => "- Usia 17-60 tahun\n- Berat badan minimal 45 kg\n- Dalam kondisi sehat",
                'recruitment_status' => 'COMPLETED',
            ],
        ];

        foreach ($events as $data) {
            $category = Category::query()->where('slug', $data['category'])->firstOrFail();

            Event::query()->updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'category_id' => $category->getKey(),
                    'created_by' => $adminId,
                    'title' => $data['title'],
                    'summary' => $data['summary'],
                    'description' => $data['description'],
                    'location' => $data['location'],
                    'event_start_at' => $data['event_start_at'],
                    'event_end_at' => $data['event_end_at'],
                    'registration_start_at' => $data['registration_start_at'],
                    'registration_end_at' => $data['registration_end_at'],
                    'quota' => $data['quota'],
                    'requirements' => $data['requirements'],
                    'recruitment_status' => $data['recruitment_status'],
                    'is_published' => true,
                ],
            );
        }
    }
}
