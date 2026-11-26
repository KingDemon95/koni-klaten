<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengurus;
use App\Models\CabangOlahraga;
use App\Models\User;
use App\Support\AccountProvisioner;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class StrukturOrganisasiController extends Controller
{
    /**
     * Daftar seluruh pengurus.
     */
    public function index(Request $request): View
    {
        $query = Pengurus::with(['cabangOlahraga', 'user']);

        $search = trim((string) $request->query('q', ''));
        $status = $request->query('status', '');
        $tingkat = $request->query('tingkat', '');
        $caborId = $request->query('cabor_id', '');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('jabatan', 'like', "%{$search}%")
                    ->orWhere('bidang', 'like', "%{$search}%")
                    ->orWhereHas('cabangOlahraga', function ($cabor) use ($search) {
                        $cabor->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        if ($tingkat === 'koni') {
            $query->whereNull('cabang_olahraga_id');
        } elseif ($tingkat === 'cabor') {
            $query->whereNotNull('cabang_olahraga_id');
        }

        if (in_array($status, ['aktif', 'nonaktif'], true)) {
            $query->where('status', $status);
        }

        if ($caborId !== '' && ctype_digit((string) $caborId)) {
            $query->where('cabang_olahraga_id', $caborId);
        }

        $penguruses = $query
            ->orderBy('urutan')
            ->orderBy('nama_lengkap')
            ->paginate(10)
            ->withQueryString();

        $total = Pengurus::count();

        $aktif = Pengurus::where('status', 'aktif')->count();

        $nonaktif = Pengurus::where('status', 'nonaktif')->count();

        $pengurusKoni = Pengurus::whereNull(
            'cabang_olahraga_id'
        )->count();

        $cabors = CabangOlahraga::orderBy('nama')->get();

        return view('admin.struktur-organisasi.index', compact(
            'penguruses',
            'cabors',
            'search',
            'status',
            'tingkat',
            'caborId',
            'total',
            'aktif',
            'nonaktif',
            'pengurusKoni'
        ));
    }


    /**
     * Form tambah pengurus.
     */
    public function create(): View
    {
        $cabors = CabangOlahraga::where('status', 'aktif')
            ->orderBy('nama')
            ->get();

        return view('admin.struktur-organisasi.create', [
            'pengurus' => new Pengurus(),
            'cabors' => $cabors,
        ]);
    }


    /**
     * Simpan pengurus.
     */
    public function store(Request $request): RedirectResponse
    {
        $makeStaffAccount = $request->boolean('buat_akun_staff');

        $validated = $request->validate([
            'cabang_olahraga_id' => [
                'nullable',
                'integer',
                Rule::exists('cabang_olahraga', 'id')->where('status', 'aktif'),
            ],
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'jabatan' => ['required', 'string', 'max:120'],
            'bidang' => ['nullable', 'string', 'max:120'],
            'jenis_kelamin' => ['nullable', Rule::in(['L', 'P'])],
            'periode_mulai' => ['nullable', 'integer', 'between:1900,2100'],
            'periode_selesai' => ['nullable', 'integer', 'between:1900,2100'],
            'no_telepon' => ['nullable', 'string', 'max:30'],
            'email' => $makeStaffAccount
                ? ['required', 'email', 'max:255', Rule::unique('users', 'email')]
                : ['nullable', 'email', 'max:255'],
            'buat_akun_staff' => ['nullable', 'boolean'],
            'urutan' => ['required', 'integer', 'min:0', 'max:999'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($makeStaffAccount && ! empty($validated['cabang_olahraga_id'])) {
            throw ValidationException::withMessages([
                'buat_akun_staff' => 'Akun Staff KONI hanya dapat dibuat untuk pengurus tingkat KONI, bukan pengurus cabang olahraga.',
            ]);
        }

        if (
            ($validated['periode_mulai'] ?? null) !== null &&
            ($validated['periode_selesai'] ?? null) !== null &&
            $validated['periode_selesai'] < $validated['periode_mulai']
        ) {
            throw ValidationException::withMessages([
                'periode_selesai' => 'Tahun akhir tidak boleh lebih kecil dari tahun awal.',
            ]);
        }

        $photoPath = null;
        if ($request->hasFile('foto')) {
            $photoPath = $request->file('foto')->store('pengurus', 'public');
            $validated['foto_path'] = $photoPath;
        }

        unset($validated['foto'], $validated['buat_akun_staff']);

        try {
            DB::transaction(function () use ($validated, $makeStaffAccount, &$pengurus) {
                $pengurus = Pengurus::create($validated);

                if ($makeStaffAccount) {
                    $user = AccountProvisioner::create(
                        $pengurus->nama_lengkap,
                        $pengurus->email,
                        'staff_koni',
                        null,
                        $pengurus->status === 'aktif',
                    );

                    $pengurus->user()->associate($user);
                    $pengurus->save();
                }
            });
        } catch (Throwable $exception) {
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }
            throw $exception;
        }

        $message = $makeStaffAccount
            ? 'Data pengurus dan akun Staff KONI berhasil dibuat. Login: '.$validated['email'].'. Password awal: '.config('account_defaults.passwords.staff_koni').'.'
            : 'Data pengurus berhasil ditambahkan.';

        return redirect()->route('admin.struktur-organisasi.index')->with('success', $message);
    }

    /**
     * Form edit pengurus.
     */
    public function edit(Pengurus $struktur_organisasi): View
    {
        $struktur_organisasi->load('user');
        $cabors = CabangOlahraga::orderBy('nama')->get();

        return view('admin.struktur-organisasi.edit', [
            'pengurus' => $struktur_organisasi,
            'cabors' => $cabors,
        ]);
    }


    /**
     * Update pengurus.
     */
    public function update(
        Request $request,
        Pengurus $struktur_organisasi
    ): RedirectResponse {
        $struktur_organisasi->load('user');
        $existingUser = $struktur_organisasi->user;
        $makeStaffAccount = $request->boolean('buat_akun_staff') || $existingUser !== null;

        $emailRules = ['nullable', 'email', 'max:255'];
        if ($makeStaffAccount) {
            $emailUnique = Rule::unique('users', 'email');
            if ($existingUser) {
                $emailUnique->ignore($existingUser->id);
            }

            $emailRules = [
                'required',
                'email',
                'max:255',
                $emailUnique,
            ];
        }

        $validated = $request->validate([
            'cabang_olahraga_id' => [
                'nullable',
                'integer',
                Rule::exists('cabang_olahraga', 'id'),
            ],
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'jabatan' => ['required', 'string', 'max:120'],
            'bidang' => ['nullable', 'string', 'max:120'],
            'jenis_kelamin' => ['nullable', Rule::in(['L', 'P'])],
            'periode_mulai' => ['nullable', 'integer', 'between:1900,2100'],
            'periode_selesai' => ['nullable', 'integer', 'between:1900,2100'],
            'no_telepon' => ['nullable', 'string', 'max:30'],
            'email' => $emailRules,
            'buat_akun_staff' => ['nullable', 'boolean'],
            'urutan' => ['required', 'integer', 'min:0', 'max:999'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($makeStaffAccount && ! empty($validated['cabang_olahraga_id'])) {
            throw ValidationException::withMessages([
                'buat_akun_staff' => 'Akun Staff KONI hanya dapat digunakan untuk pengurus tingkat KONI.',
            ]);
        }

        if (
            ($validated['periode_mulai'] ?? null) !== null &&
            ($validated['periode_selesai'] ?? null) !== null &&
            $validated['periode_selesai'] < $validated['periode_mulai']
        ) {
            throw ValidationException::withMessages([
                'periode_selesai' => 'Tahun akhir tidak boleh lebih kecil dari tahun awal.',
            ]);
        }

        $photoPath = null;
        if ($request->hasFile('foto')) {
            $photoPath = $request->file('foto')->store('pengurus', 'public');
            $validated['foto_path'] = $photoPath;
        }
        unset($validated['foto'], $validated['buat_akun_staff']);

        $oldPhoto = $struktur_organisasi->foto_path;

        try {
            DB::transaction(function () use ($struktur_organisasi, $validated, $makeStaffAccount, $existingUser) {
                $struktur_organisasi->update($validated);

                if ($existingUser) {
                    $existingUser->update([
                        'name' => $struktur_organisasi->nama_lengkap,
                        'email' => mb_strtolower(trim($struktur_organisasi->email)),
                        'is_active' => $struktur_organisasi->status === 'aktif',
                    ]);
                } elseif ($makeStaffAccount) {
                    $user = AccountProvisioner::create(
                        $struktur_organisasi->nama_lengkap,
                        $struktur_organisasi->email,
                        'staff_koni',
                        null,
                        $struktur_organisasi->status === 'aktif',
                    );

                    $struktur_organisasi->user()->associate($user);
                    $struktur_organisasi->save();
                }
            });
        } catch (Throwable $exception) {
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }
            throw $exception;
        }

        if ($photoPath && $oldPhoto) {
            Storage::disk('public')->delete($oldPhoto);
        }

        $successMessage = $existingUser
            ? 'Data pengurus dan akun Staff KONI berhasil diperbarui.'
            : ($makeStaffAccount
                ? 'Data pengurus dan akun Staff KONI berhasil dibuat. Login: '.$validated['email'].'. Password awal: '.config('account_defaults.passwords.staff_koni').'.'
                : 'Data pengurus berhasil diperbarui.');

        return redirect()
            ->route('admin.struktur-organisasi.index')
            ->with('success', $successMessage);
    }

    /**
     * Hapus pengurus.
     */
    public function destroy(
        Pengurus $struktur_organisasi
    ): RedirectResponse {

        if ($struktur_organisasi->foto_path) {
            Storage::disk('public')->delete(
                $struktur_organisasi->foto_path
            );
        }

        $struktur_organisasi->user?->update(['is_active' => false]);
        $struktur_organisasi->delete();

        return redirect()
            ->route('admin.struktur-organisasi.index')
            ->with('success', 'Data pengurus berhasil dihapus.');
    }
}
