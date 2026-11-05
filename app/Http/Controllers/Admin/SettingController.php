<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Show the website settings form.
     */
    public function edit(): View
    {
        $settings = SiteSetting::values();

        return view('admin.settings.edit', compact('settings'));
    }

    /**
     * Update the website information shown to visitors.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'site_name' => ['required', 'string', 'max:100'],
            'tagline' => ['nullable', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:2000'],
            'hero_title' => ['nullable', 'string', 'max:150'],
            'hero_description' => ['nullable', 'string', 'max:500'],
            'about_text' => ['nullable', 'string', 'max:2000'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_address' => ['nullable', 'string', 'max:255'],
            'instagram' => ['nullable', 'string', 'max:100'],
            'footer_text' => ['nullable', 'string', 'max:500'],
        ], [], [
            'site_name' => 'nama situs',
            'tagline' => 'tagline',
            'description' => 'deskripsi',
            'hero_title' => 'judul hero',
            'hero_description' => 'deskripsi hero',
            'about_text' => 'teks tentang kami',
            'contact_email' => 'email kontak',
            'contact_phone' => 'telepon kontak',
            'contact_address' => 'alamat kontak',
            'instagram' => 'instagram',
            'footer_text' => 'teks footer',
        ]);

        foreach ($validated as $key => $value) {
            SiteSetting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value],
            );
        }

        return redirect()
            ->route('admin.settings.edit')
            ->with('success', 'Pengaturan website berhasil diperbarui.');
    }
}
