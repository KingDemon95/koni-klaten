<?php

namespace Tests\Feature;

use App\Models\Publikasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PublikasiManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin_koni', 'staff_koni', 'admin_cabor', 'atlet', 'pelatih', 'atlet_pelatih'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Storage::fake('local');
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role);

        return $user;
    }

    public function test_staff_can_create_and_directly_publish_news(): void
    {
        $staff = $this->userWithRole('staff_koni');

        $this->actingAs($staff)
            ->post(route('staff.publikasi.store'), [
                'judul' => 'KONI Klaten Gelar Pembinaan Atlet',
                'jenis' => 'berita',
                'kategori' => 'Kegiatan',
                'ringkasan' => 'Pembinaan atlet menjelang kejuaraan.',
                'konten' => 'KONI Kabupaten Klaten mengadakan kegiatan pembinaan atlet.',
            ])
            ->assertRedirect();

        $item = Publikasi::query()->where('judul', 'KONI Klaten Gelar Pembinaan Atlet')->firstOrFail();
        $this->assertSame(Publikasi::STATUS_DRAFT, $item->status);
        $this->assertSame($staff->id, $item->created_by);

        $this->actingAs($staff)
            ->post(route('staff.publikasi.publish', $item))
            ->assertRedirect(route('staff.publikasi.show', $item));

        $this->assertDatabaseHas('publikasis', [
            'id' => $item->id,
            'status' => Publikasi::STATUS_PUBLISHED,
            'published_by' => $staff->id,
        ]);

        $this->get(route('public.berita.show', $item->slug))
            ->assertOk()
            ->assertSee('KONI Klaten Gelar Pembinaan Atlet');
    }

    public function test_admin_koni_can_upload_and_publish_photo_documentation(): void
    {
        $admin = $this->userWithRole('admin_koni');

        $this->actingAs($admin)
            ->post(route('admin.publikasi.store'), [
                'judul' => 'Dokumentasi Kejuaraan Bulutangkis',
                'jenis' => 'foto',
                'kategori' => 'Kejuaraan',
                'ringkasan' => 'Suasana kejuaraan bulutangkis Kabupaten Klaten.',
                'media' => UploadedFile::fake()->image('kejuaraan.jpg'),
            ])
            ->assertRedirect();

        $item = Publikasi::query()->where('judul', 'Dokumentasi Kejuaraan Bulutangkis')->firstOrFail();
        $this->assertNotNull($item->media_path);
        Storage::disk('local')->assertExists($item->media_path);

        $this->actingAs($admin)
            ->post(route('admin.publikasi.publish', $item))
            ->assertRedirect(route('admin.publikasi.show', $item));

        $this->get(route('public.publikasi.media', $item->slug))->assertOk();
        $this->get(route('public.dokumentasi.show', $item->slug))->assertOk()->assertSee($item->judul);
    }

    public function test_public_pages_only_show_published_news_and_documentation(): void
    {
        $staff = $this->userWithRole('staff_koni');

        $published = Publikasi::create([
            'created_by' => $staff->id,
            'judul' => 'Berita Publik KONI',
            'slug' => 'berita-publik-koni',
            'jenis' => 'berita',
            'konten' => 'Isi berita yang boleh dibaca publik.',
            'status' => Publikasi::STATUS_PUBLISHED,
            'published_at' => now(),
            'published_by' => $staff->id,
        ]);

        $draft = Publikasi::create([
            'created_by' => $staff->id,
            'judul' => 'Berita Draft Internal',
            'slug' => 'berita-draft-internal',
            'jenis' => 'berita',
            'konten' => 'Konten yang belum boleh ditampilkan.',
            'status' => Publikasi::STATUS_DRAFT,
        ]);

        $this->get(route('public.berita.index'))
            ->assertOk()
            ->assertSee($published->judul)
            ->assertDontSee($draft->judul);

        $this->get(route('public.berita.show', $draft->slug))->assertNotFound();
        $this->get(route('public.publikasi.media', $draft->slug))->assertNotFound();
    }

    public function test_staff_can_upload_and_publish_video_documentation(): void
    {
        $staff = $this->userWithRole('staff_koni');

        $this->actingAs($staff)
            ->post(route('staff.publikasi.store'), [
                'judul' => 'Video Latihan Atlet Klaten',
                'jenis' => 'video',
                'kategori' => 'Latihan',
                'ringkasan' => 'Dokumentasi latihan bersama.',
                'media' => UploadedFile::fake()->create('latihan.mp4', 128, 'video/mp4'),
            ])
            ->assertRedirect();

        $item = Publikasi::query()->where('judul', 'Video Latihan Atlet Klaten')->firstOrFail();
        Storage::disk('local')->assertExists($item->media_path);

        $this->actingAs($staff)
            ->post(route('staff.publikasi.publish', $item))
            ->assertRedirect(route('staff.publikasi.show', $item));

        $this->get(route('public.dokumentasi.show', $item->slug))->assertOk()->assertSee('Video Latihan Atlet Klaten');
        $this->get(route('public.publikasi.media', $item->slug))->assertOk();
    }

    public function test_only_staff_and_admin_koni_can_manage_publication_content(): void
    {
        foreach (['admin_cabor', 'atlet', 'pelatih', 'atlet_pelatih'] as $role) {
            $user = $this->userWithRole($role);

            $this->actingAs($user)->get(route('staff.publikasi.index'))->assertForbidden();
            $this->actingAs($user)->get(route('admin.publikasi.index'))->assertForbidden();
        }
    }

    public function test_news_requires_body_and_photo_requires_an_image(): void
    {
        $staff = $this->userWithRole('staff_koni');

        $this->actingAs($staff)
            ->from(route('staff.publikasi.create'))
            ->post(route('staff.publikasi.store'), [
                'judul' => 'Berita Tanpa Isi',
                'jenis' => 'berita',
            ])
            ->assertSessionHasErrors('konten');

        $this->actingAs($staff)
            ->from(route('staff.publikasi.create'))
            ->post(route('staff.publikasi.store'), [
                'judul' => 'Foto Tanpa File',
                'jenis' => 'foto',
            ])
            ->assertSessionHasErrors('media');
    }
}
