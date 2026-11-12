<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CabangOlahraga;
use App\Models\User;
use App\Support\AccountProvisioner;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CabangOlahragaController extends Controller
{
    /**
     * Menampilkan daftar cabor.
     */
    public function index(Request $request): View
    {
        $query = CabangOlahraga::query();

        $search = trim((string) $request->query('q', ''));
        $status = $request->query('status', '');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('singkatan', 'like', "%{$search}%")
                    ->orWhere('nama_ketua', 'like', "%{$search}%");
            });
        }

        if (in_array($status, ['aktif', 'nonaktif'], true)) {
            $query->where('status', $status);
        }

        $cabors = $query
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        $total = CabangOlahraga::count();

        $aktif = CabangOlahraga::where('status', 'aktif')->count();

        $nonaktif = CabangOlahraga::where('status', 'nonaktif')->count();

        return view('admin.cabor.index', compact(
            'cabors',
            'search',
            'status',
            'total',
            'aktif',
            'nonaktif'
        ));
    }


    /**
     * Form tambah cabor.
     */
    public function create(): View
    {
        return view('admin.cabor.create', [
            'cabor' => new CabangOlahraga(),
            'adminAccount' => null,
        ]);
    }


    /**
     * Menyimpan cabor baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:150',
                'unique:cabang_olahraga,nama',
            ],

            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],

            'singkatan'  => ['nullable', 'string', 'max:30'],
            'deskripsi' => ['nullable', 'string', 'max:3000'],
            'nama_ketua' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:30'],
            'alamat' => ['nullable', 'string', 'max:2000'],

            'status' => [
                'required',
                Rule::in(['aktif', 'nonaktif']),
            ],

            'logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo_path'] = $request
                ->file('logo')
                ->store('cabor', 'public');
        }

        unset($validated['logo']);
        $adminName = $validated['admin_name'];
        $adminEmail = $validated['admin_email'];
        unset($validated['admin_name'], $validated['admin_email']);

        DB::transaction(function () use ($validated, $adminName, $adminEmail) {
            $cabor = CabangOlahraga::create($validated);

            AccountProvisioner::create(
                $adminName,
                $adminEmail,
                'admin_cabor',
                $cabor->id,
                $cabor->status === 'aktif',
            );
        });

        return redirect()
            ->route('admin.cabor.index')
            ->with('success', 'Cabang olahraga dan akun Admin Cabor berhasil dibuat. Login: '.$adminEmail.'. Password awal: '.config('account_defaults.passwords.admin_cabor').'.');
    }


    /**
     * Form edit cabor.
     */
    public function edit(CabangOlahraga $cabor): View
    {
        $adminAccount = User::query()
            ->where('cabang_olahraga_id', $cabor->id)
            ->whereHas('roles', fn ($query) => $query->where('name', 'admin_cabor'))
            ->first();

        return view('admin.cabor.edit', compact('cabor', 'adminAccount'));
    }


    /**
     * Memperbarui data cabor.
     */
    public function update(
        Request $request,
        CabangOlahraga $cabor
    ): RedirectResponse {

        $existingAdminAccount = User::query()
            ->where('cabang_olahraga_id', $cabor->id)
            ->whereHas('roles', fn ($query) => $query->where('name', 'admin_cabor'))
            ->first();

        $adminEmailUnique = Rule::unique('users', 'email');
        if ($existingAdminAccount) {
            $adminEmailUnique->ignore($existingAdminAccount->id);
        }

        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:150',
                Rule::unique('cabang_olahraga', 'nama')
                    ->ignore($cabor->id),
            ],

            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => [
                'required',
                'email',
                'max:255',
                $adminEmailUnique,
            ],

            'singkatan' => ['nullable', 'string', 'max:30'],
            'deskripsi' => ['nullable', 'string', 'max:3000'],
            'nama_ketua' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:30'],
            'alamat' => ['nullable', 'string', 'max:2000'],

            'status' => [
                'required',
                Rule::in(['aktif', 'nonaktif']),
            ],

            'logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        if ($request->hasFile('logo')) {

            $newLogo = $request
                ->file('logo')
                ->store('cabor', 'public');

            if ($cabor->logo_path) {
                Storage::disk('public')->delete($cabor->logo_path);
            }

            $validated['logo_path'] = $newLogo;
        }

        unset($validated['logo']);
        $adminName = $validated['admin_name'];
        $adminEmail = $validated['admin_email'];
        unset($validated['admin_name'], $validated['admin_email']);

        DB::transaction(function () use ($cabor, $validated, $adminName, $adminEmail) {
            $cabor->update($validated);

            $adminAccount = User::query()
                ->where('cabang_olahraga_id', $cabor->id)
                ->whereHas('roles', fn ($query) => $query->where('name', 'admin_cabor'))
                ->first();

            if ($adminAccount) {
                $adminAccount->update([
                    'name' => $adminName,
                    'email' => mb_strtolower(trim($adminEmail)),
                    'is_active' => $cabor->status === 'aktif',
                ]);
            } else {
                AccountProvisioner::create(
                    $adminName,
                    $adminEmail,
                    'admin_cabor',
                    $cabor->id,
                    $cabor->status === 'aktif',
                );
            }
        });

        $successMessage = $existingAdminAccount
            ? 'Data cabang olahraga dan akun Admin Cabor berhasil diperbarui.'
            : 'Data cabang olahraga dan akun Admin Cabor berhasil dibuat. Login: '.$adminEmail.'. Password awal: '.config('account_defaults.passwords.admin_cabor').'.';

        return redirect()
            ->route('admin.cabor.index')
            ->with('success', $successMessage);
    }


    /**
     * Menghapus cabor.
     */
    public function destroy(CabangOlahraga $cabor): RedirectResponse
    {
        if (
            $cabor->atlets()->exists() ||
            $cabor->pelatihs()->exists() ||
            $cabor->pengurus()->exists() ||
            User::where('cabang_olahraga_id', $cabor->id)->exists()
        ) {
            return redirect()
                ->route('admin.cabor.index')
                ->with(
                    'error',
                    'Cabor tidak dapat dihapus karena masih memiliki akun Admin Cabor, data atlet, pelatih, atau pengurus. Nonaktifkan cabor jika tidak digunakan.'
                );
        }

        if ($cabor->logo_path) {
            Storage::disk('public')->delete($cabor->logo_path);
        }

        $cabor->delete();

        return redirect()
            ->route('admin.cabor.index')
            ->with('success', 'Cabang olahraga berhasil dihapus.');
    }
}
