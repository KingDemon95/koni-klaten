<?php

namespace Tests\Feature;

use App\Models\Atlet;
use App\Models\CabangOlahraga;
use App\Models\Prestasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PublicStatisticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'atlet', 'guard_name' => 'web']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Storage::fake('local');
    }

    private function cabor(string $name, string $status = 'aktif'): CabangOlahraga
    {
        return CabangOlahraga::create([
            'nama' => $name,
            'status' => $status,
        ]);
    }

    private function prestasi(CabangOlahraga $cabor, array $attributes = []): Prestasi
    {
        return Prestasi::create(array_merge([
            'cabang_olahraga_id' => $cabor->id,
            'nama_kejuaraan' => 'Kejuaraan Uji '.$cabor->nama,
            'tingkat' => 'kabupaten',
            'hasil' => 'Juara 1',
            'tanggal_prestasi' => now()->subDays(2)->toDateString(),
            'bukti_path' => 'prestasi/uji/sertifikat.pdf',
            'bukti_original_name' => 'sertifikat.pdf',
            'status' => 'terverifikasi',
        ], $attributes));
    }

    public function test_statistics_only_count_verified_achievements_from_active_branches(): void
    {
        $activeCabor = $this->cabor('Bulutangkis');
        $inactiveCabor = $this->cabor('Cabor Nonaktif', 'nonaktif');

        $this->prestasi($activeCabor, ['nama_kejuaraan' => 'Emas Bulutangkis', 'jenis_medali' => 'emas']);
        $this->prestasi($activeCabor, ['nama_kejuaraan' => 'Perak Bulutangkis', 'jenis_medali' => 'perak']);
        $this->prestasi($activeCabor, [
            'nama_kejuaraan' => 'Pengajuan Belum Diverifikasi',
            'jenis_medali' => 'perunggu',
            'status' => 'menunggu_verifikasi',
        ]);
        $this->prestasi($inactiveCabor, ['nama_kejuaraan' => 'Prestasi Cabor Nonaktif', 'jenis_medali' => 'emas']);

        $this->get(route('public.statistik.index'))
            ->assertOk()
            ->assertViewHas('statistics', function (array $statistics): bool {
                return $statistics['prestasi'] === 2
                    && $statistics['emas'] === 1
                    && $statistics['perak'] === 1
                    && $statistics['perunggu'] === 0
                    && $statistics['medali'] === 2;
            })
            ->assertViewHas('ranking', fn ($ranking) => $ranking->count() === 1
                && $ranking->first()->nama === 'Bulutangkis')
            ->assertSeeText('Emas Bulutangkis')
            ->assertDontSeeText('Pengajuan Belum Diverifikasi')
            ->assertDontSeeText('Prestasi Cabor Nonaktif');
    }

    public function test_year_filter_limits_summary_ranking_and_recent_achievements(): void
    {
        $cabor = $this->cabor('Atletik');
        $currentYear = now()->year;
        $previousYear = $currentYear - 1;

        $this->prestasi($cabor, [
            'nama_kejuaraan' => 'Kejuaraan Tahun Ini',
            'tanggal_prestasi' => $currentYear.'-03-10',
            'jenis_medali' => 'emas',
        ]);
        $this->prestasi($cabor, [
            'nama_kejuaraan' => 'Kejuaraan Tahun Lalu',
            'tanggal_prestasi' => $previousYear.'-05-12',
            'jenis_medali' => 'perunggu',
        ]);

        $this->get(route('public.statistik.index', ['tahun' => (string) $currentYear]))
            ->assertOk()
            ->assertViewHas('selectedYear', $currentYear)
            ->assertViewHas('statistics', fn (array $statistics) => $statistics['prestasi'] === 1
                && $statistics['emas'] === 1
                && $statistics['perunggu'] === 0)
            ->assertViewHas('recentPrestasis', fn ($prestasis) => $prestasis->count() === 1
                && $prestasis->first()->nama_kejuaraan === 'Kejuaraan Tahun Ini')
            ->assertSeeText('Kejuaraan Tahun Ini')
            ->assertDontSeeText('Kejuaraan Tahun Lalu');
    }

    public function test_homepage_only_highlights_verified_achievements_from_active_branches(): void
    {
        $activeCabor = $this->cabor('Renang');
        $inactiveCabor = $this->cabor('Renang Nonaktif', 'nonaktif');

        $this->prestasi($activeCabor, ['nama_kejuaraan' => 'Prestasi Publik Terverifikasi']);
        $this->prestasi($activeCabor, [
            'nama_kejuaraan' => 'Prestasi Masih Menunggu',
            'status' => 'menunggu_verifikasi',
        ]);
        $this->prestasi($inactiveCabor, ['nama_kejuaraan' => 'Prestasi Cabor Nonaktif']);

        $this->get(route('public.home'))
            ->assertOk()
            ->assertSeeText('Prestasi Publik Terverifikasi')
            ->assertDontSeeText('Prestasi Masih Menunggu')
            ->assertDontSeeText('Prestasi Cabor Nonaktif')
            ->assertSee(route('public.statistik.index'));
    }

    public function test_array_year_filter_is_ignored_safely(): void
    {
        $cabor = $this->cabor('Voli');
        $this->prestasi($cabor, ['nama_kejuaraan' => 'Prestasi Voli']);

        $this->get(route('public.statistik.index', ['tahun' => ['2026']]))
            ->assertOk()
            ->assertViewHas('selectedYear', fn ($year) => $year === null)
            ->assertViewHas('statistics', fn (array $statistics) => $statistics['prestasi'] === 1);
    }

    public function test_atlet_can_submit_explicit_medal_type_and_invalid_types_are_rejected(): void
    {
        $cabor = $this->cabor('Taekwondo');
        $user = User::factory()->create(['name' => 'Atlet Statistik', 'is_active' => true]);
        $user->assignRole('atlet');
        Atlet::create([
            'cabang_olahraga_id' => $cabor->id,
            'user_id' => $user->id,
            'nama_lengkap' => 'Atlet Statistik',
            'jenis_kelamin' => 'L',
            'status' => 'aktif',
        ]);

        $basePayload = [
            'nama_kejuaraan' => 'Kejuaraan Taekwondo',
            'tingkat' => 'provinsi',
            'hasil' => 'Juara 1',
            'tanggal_prestasi' => now()->subDay()->toDateString(),
            'bukti' => UploadedFile::fake()->create('sertifikat.pdf', 100, 'application/pdf'),
        ];

        $this->actingAs($user)
            ->post(route('atlet.prestasi.store'), [...$basePayload, 'jenis_medali' => 'berlian'])
            ->assertSessionHasErrors('jenis_medali');

        $this->actingAs($user)
            ->post(route('atlet.prestasi.store'), [...$basePayload, 'jenis_medali' => 'emas'])
            ->assertRedirect(route('atlet.prestasi.index'));

        $this->assertDatabaseHas('prestasis', [
            'nama_kejuaraan' => 'Kejuaraan Taekwondo',
            'jenis_medali' => 'emas',
            'status' => 'menunggu_verifikasi',
        ]);

        $prestasi = Prestasi::query()->where('nama_kejuaraan', 'Kejuaraan Taekwondo')->firstOrFail();
        $prestasi->update(['status' => 'terverifikasi']);

        $this->get(route('public.statistik.index'))
            ->assertOk()
            ->assertViewHas('statistics', fn (array $statistics) => $statistics['emas'] === 1);
    }
}
