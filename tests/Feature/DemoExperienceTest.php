<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class DemoExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_have_keyboard_skip_link_and_current_page_navigation(): void
    {
        $this->get(route('public.cabor.index'))
            ->assertOk()
            ->assertSee('href="#konten-utama"', false)
            ->assertSee('id="konten-utama"', false)
            ->assertSee('aria-current="page"', false);
    }

    public function test_admin_dashboard_displays_global_error_feedback(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('admin_koni');

        $this->actingAs($user)
            ->withSession(['error' => 'Contoh pesan kesalahan demo'])
            ->get(route('dashboard.admin-koni'))
            ->assertOk()
            ->assertSeeText('Tindakan belum berhasil')
            ->assertSeeText('Contoh pesan kesalahan demo');
    }

    public function test_validation_errors_are_summarized_for_keyboard_and_mobile_users(): void
    {
        $emptyHtml = view('partials.demo-feedback', [
            'errors' => new ViewErrorBag(),
        ])->render();

        $this->assertStringNotContainsString(
            'Periksa kembali data yang diisi',
            $emptyHtml
        );

        $errors = (new ViewErrorBag())->put(
            'default',
            new MessageBag(['email' => 'Email wajib diisi.'])
        );

        $html = view('partials.demo-feedback', [
            'errors' => $errors,
        ])->render();

        $this->assertStringContainsString(
            'Periksa kembali data yang diisi',
            $html
        );
        $this->assertStringContainsString('Email wajib diisi.', $html);
        $this->assertStringContainsString('role="alert"', $html);
        $this->assertStringContainsString(
            'aria-labelledby="koni-validation-title"',
            $html
        );
    }

    public function test_demo_check_artisan_command_is_registered(): void
    {
        // Illuminate\Foundation\Console\Kernel does not provide has().
        // all() returns the registered Symfony console commands.
        $this->assertArrayHasKey('koni:demo-check', Artisan::all());
    }
}
