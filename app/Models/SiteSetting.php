<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    /**
     * Default values used when a setting has not been stored yet.
     *
     * @var array<string, string>
     */
    public const DEFAULTS = [
        'site_name' => 'Bandung Care',
        'tagline' => 'Volunteer Recruitment Platform',
        'description' => 'Bandung Care adalah komunitas sosial yang bergerak dalam aksi kemanusiaan, pendidikan, lingkungan, dan pengabdian masyarakat untuk menciptakan Bandung yang lebih peduli dan berdaya.',
        'hero_title' => 'Jadilah Bagian dari Perubahan Baik.',
        'hero_description' => 'Bersama Bandung Care, setiap langkah kecil dapat membawa dampak besar bagi masyarakat.',
        'about_text' => 'Bandung Care adalah komunitas sosial yang bergerak dalam aksi kemanusiaan, pendidikan, lingkungan, dan pengabdian masyarakat untuk menciptakan Bandung yang lebih peduli dan berdaya.',
        'contact_email' => 'bandungcare@example.com',
        'contact_phone' => '',
        'contact_address' => 'Bandung, Jawa Barat',
        'instagram' => '@bandungcare',
        'footer_text' => 'Bersama Bandung Care, setiap langkah kecil dapat membawa dampak besar bagi masyarakat.',
    ];

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get every site setting merged with its default value.
     *
     * @return array<string, string>
     */
    public static function values(): array
    {
        $stored = static::query()->pluck('value', 'key')->all();

        return array_merge(self::DEFAULTS, array_filter($stored, fn ($value): bool => $value !== null));
    }

    /**
     * Get a single setting value.
     */
    public static function get(string $key, ?string $default = null): string
    {
        return static::values()[$key] ?? $default ?? '';
    }
}
