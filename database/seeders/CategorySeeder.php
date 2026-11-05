<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Seed the example event categories used by Bandung Care.
     */
    public function run(): void
    {
        $categories = [
            'Pendidikan' => 'Kegiatan belajar mengajar, literasi, dan pengembangan pendidikan masyarakat.',
            'Lingkungan' => 'Aksi pelestarian lingkungan, penghijauan, dan pengelolaan sampah.',
            'Kemanusiaan' => 'Bantuan sosial dan respons darurat bagi masyarakat yang membutuhkan.',
            'Kesehatan' => 'Penyuluhan kesehatan, donor darah, dan layanan kesehatan komunitas.',
            'Pengabdian Masyarakat' => 'Pemberdayaan warga melalui kolaborasi dan program berbasis komunitas.',
            'Donasi dan Sosial' => 'Penggalangan donasi serta kegiatan sosial untuk sesama.',
        ];

        foreach ($categories as $name => $description) {
            Category::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'description' => $description],
            );
        }
    }
}
