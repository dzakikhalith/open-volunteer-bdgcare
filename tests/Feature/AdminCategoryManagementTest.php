<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get(route('admin.categories.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_non_admin_user_cannot_manage_categories(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.categories.index'))
            ->assertForbidden();
    }

    public function test_index_lists_categories_with_event_counts_and_search(): void
    {
        $category = Category::factory()->create(['name' => 'Lingkungan', 'slug' => 'lingkungan']);
        Event::factory()->for($category)->create();
        Category::factory()->create(['name' => 'Pendidikan', 'slug' => 'pendidikan']);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.categories.index', ['search' => 'Lingkungan']));

        $response->assertOk()
            ->assertSee('Lingkungan')
            ->assertDontSee('Pendidikan')
            ->assertViewHas('categories', function ($categories) use ($category): bool {
                $row = $categories->firstWhere('id', $category->id);

                return $row !== null && $row->events_count === 1;
            });
    }

    public function test_create_form_renders(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.categories.create'))
            ->assertOk()
            ->assertSee('Tambah Kategori');
    }

    public function test_store_creates_category_with_generated_slug(): void
    {
        $response = $this->actingAs($this->admin())->post(route('admin.categories.store'), [
            'name' => 'Pendidikan Anak',
            'description' => 'Kegiatan belajar untuk anak.',
        ]);

        $category = Category::sole();

        $response->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('success');

        $this->assertSame('pendidikan-anak', $category->slug);
        $this->assertSame('Kegiatan belajar untuk anak.', $category->description);
    }

    public function test_store_generates_unique_slug_when_name_already_exists(): void
    {
        Category::factory()->create(['name' => 'Lingkungan', 'slug' => 'lingkungan']);

        $this->actingAs($this->admin())->post(route('admin.categories.store'), [
            'name' => 'Lingkungan',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('categories', ['slug' => 'lingkungan-2']);
    }

    public function test_store_validates_required_and_unique_fields(): void
    {
        Category::factory()->create(['slug' => 'lingkungan']);

        $this->actingAs($this->admin())->post(route('admin.categories.store'), [
            'name' => '',
            'slug' => 'lingkungan',
        ])->assertSessionHasErrors(['name', 'slug']);

        $this->assertDatabaseCount('categories', 1);
    }

    public function test_edit_form_renders_existing_category(): void
    {
        $category = Category::factory()->create(['name' => 'Kesehatan']);

        $this->actingAs($this->admin())
            ->get(route('admin.categories.edit', $category))
            ->assertOk()
            ->assertSee('Kesehatan')
            ->assertSee('Simpan Perubahan');
    }

    public function test_update_changes_category_attributes(): void
    {
        $category = Category::factory()->create(['name' => 'Lama', 'slug' => 'lama']);

        $response = $this->actingAs($this->admin())->put(route('admin.categories.update', $category), [
            'name' => 'Kemanusiaan',
            'slug' => 'kemanusiaan',
            'description' => 'Bantuan untuk masyarakat.',
        ]);

        $response->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('success');

        $category->refresh();

        $this->assertSame('Kemanusiaan', $category->name);
        $this->assertSame('kemanusiaan', $category->slug);
        $this->assertSame('Bantuan untuk masyarakat.', $category->description);
    }

    public function test_destroy_removes_unused_category(): void
    {
        $category = Category::factory()->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('success');

        $this->assertModelMissing($category);
    }

    public function test_destroy_is_blocked_when_category_is_used_by_events(): void
    {
        $category = Category::factory()->create();
        Event::factory()->for($category)->create();

        $this->actingAs($this->admin())
            ->from(route('admin.categories.edit', $category))
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.edit', $category))
            ->assertSessionHas('error');

        $this->assertModelExists($category);
    }

    public function test_destroy_is_blocked_when_category_is_only_used_by_trashed_events(): void
    {
        $category = Category::factory()->create();
        Event::factory()->for($category)->create()->delete();

        $this->actingAs($this->admin())
            ->from(route('admin.categories.edit', $category))
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.edit', $category))
            ->assertSessionHas('error');

        $this->assertModelExists($category);
    }
}
