<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Atlet;
use App\Models\CabangOlahraga;
use App\Models\Pelatih;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PenggunaController extends Controller
{
    private const ROLES = [
        'admin_koni',
        'staff_koni',
        'admin_cabor',
        'atlet',
        'pelatih',
    ];

    private const LEGACY_ROLE = 'atlet_pelatih';


    /**
     * Daftar pengguna.
     */
    public function index(Request $request): View
    {
        $searchInput = $request->query('q', '');
$role = $request->query('role', '');
$status = $request->query('status', '');

abort_unless(
    is_string($searchInput)
        && is_string($role)
        && is_string($status),
    422
);

$search = mb_substr(
    trim($searchInput),
    0,
    100
);
        $query = User::with([
            'roles',
            'cabangOlahraga',
            'atlet.cabangOlahraga',
            'pelatih.cabangOlahraga',
        ]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $allRoles = array_merge(
            self::ROLES,
            [self::LEGACY_ROLE]
        );

        if (in_array($role, $allRoles, true)) {
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }

        if ($status === 'aktif') {
            $query->where('is_active', true);
        } elseif ($status === 'nonaktif') {
            $query->where('is_active', false);
        }

        $penggunas = $query
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $total = User::count();

        $aktif = User::where('is_active', true)->count();

        $nonaktif = User::where('is_active', false)->count();

        return view('admin.pengguna.index', compact(
            'penggunas',
            'search',
            'role',
            'status',
            'total',
            'aktif',
            'nonaktif'
        ));
    }


    /**
     * Form tambah pengguna.
     */
    public function create(): View
    {
        $cabors = CabangOlahraga::where('status', 'aktif')
            ->orderBy('nama')
            ->get();

        $atlets = Atlet::with('cabangOlahraga')
            ->whereNull('user_id')
            ->where('status', 'aktif')
            ->orderBy('nama_lengkap')
            ->get();

        $pelatihs = Pelatih::with('cabangOlahraga')
            ->whereNull('user_id')
            ->where('status', 'aktif')
            ->orderBy('nama_lengkap')
            ->get();

        return view('admin.pengguna.create', [
            'pengguna' => new User(),
            'cabors' => $cabors,
            'atlets' => $atlets,
            'pelatihs' => $pelatihs,
        ]);
    }


    /**
     * Simpan pengguna baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],

            'role' => [
                'required',
                Rule::in(self::ROLES),
            ],

            'cabang_olahraga_id' => [
                'nullable',
                'required_if:role,admin_cabor',
                'integer',
                Rule::exists('cabang_olahraga', 'id')
                    ->where('status', 'aktif'),
            ],

            'atlet_id' => [
                'nullable',
                'required_if:role,atlet',
                'integer',
                'exists:atlets,id',
            ],

            'pelatih_id' => [
                'nullable',
                'required_if:role,pelatih',
                'integer',
                'exists:pelatihs,id',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],

        ]);

        DB::transaction(function () use ($validated) {

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],

                'cabang_olahraga_id' =>
                    $validated['role'] === 'admin_cabor'
                        ? $validated['cabang_olahraga_id']
                        : null,

                'is_active' => $validated['is_active'],
            ]);

            $user->syncRoles([$validated['role']]);

            // Hubungkan profil atlet.
            if ($validated['role'] === 'atlet') {

                $atlet = Atlet::whereKey($validated['atlet_id'])
                    ->where('status', 'aktif')
                    ->whereNull('user_id')
                    ->lockForUpdate()
                    ->first();

                if (!$atlet) {
                    throw ValidationException::withMessages([
                        'atlet_id' => 'Profil atlet sudah terhubung dengan akun lain atau tidak aktif.',
                    ]);
                }

                $atlet->user()->associate($user);
                $atlet->save();
            }

            // Hubungkan profil pelatih.
            if ($validated['role'] === 'pelatih') {

                $pelatih = Pelatih::whereKey($validated['pelatih_id'])
                    ->where('status', 'aktif')
                    ->whereNull('user_id')
                    ->lockForUpdate()
                    ->first();

                if (!$pelatih) {
                    throw ValidationException::withMessages([
                        'pelatih_id' => 'Profil pelatih sudah terhubung dengan akun lain atau tidak aktif.',
                    ]);
                }

                $pelatih->user()->associate($user);
                $pelatih->save();
            }

        });

        return redirect()
            ->route('admin.pengguna.index')
            ->with('success', 'Akun pengguna berhasil dibuat.');
    }


    /**
     * Form edit pengguna.
     */
    public function edit(User $pengguna): View
    {
        $pengguna->load([
            'roles',
            'cabangOlahraga',
            'atlet',
            'pelatih',
        ]);

        $cabors = CabangOlahraga::orderBy('nama')->get();

        $atlets = Atlet::with('cabangOlahraga')
            ->where(function ($q) use ($pengguna) {
                $q->whereNull('user_id')
                    ->orWhere('user_id', $pengguna->id);
            })
            ->orderBy('nama_lengkap')
            ->get();

        $pelatihs = Pelatih::with('cabangOlahraga')
            ->where(function ($q) use ($pengguna) {
                $q->whereNull('user_id')
                    ->orWhere('user_id', $pengguna->id);
            })
            ->orderBy('nama_lengkap')
            ->get();

        return view('admin.pengguna.edit', compact(
            'pengguna',
            'cabors',
            'atlets',
            'pelatihs'
        ));
    }


    /**
     * Perbarui pengguna.
     */
    public function update(
        Request $request,
        User $pengguna
    ): RedirectResponse {

        $allowedRoles = self::ROLES;

        // Pertahankan role lama agar akun lama tetap bisa diedit.
        if ($pengguna->hasRole(self::LEGACY_ROLE)) {
            $allowedRoles[] = self::LEGACY_ROLE;
        }

        $validated = $request->validate([

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($pengguna->id),
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],

            'role' => [
                'required',
                Rule::in($allowedRoles),
            ],

            'cabang_olahraga_id' => [
                'nullable',
                'required_if:role,admin_cabor',
                'integer',
                Rule::exists('cabang_olahraga', 'id')
                    ->where('status', 'aktif'),
            ],

            'atlet_id' => [
                'nullable',
                'required_if:role,atlet',
                'integer',
                'exists:atlets,id',
            ],

            'pelatih_id' => [
                'nullable',
                'required_if:role,pelatih',
                'integer',
                'exists:pelatihs,id',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],

        ]);

        $nextRole = $validated['role'];
        $nextActive = (bool) $validated['is_active'];

        // Cegah admin mengunci akunnya sendiri.
        if (
            $pengguna->id === auth()->id() &&
            (!$nextActive || $nextRole !== 'admin_koni')
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'role' => 'Anda tidak dapat menonaktifkan atau mengubah role akun Admin KONI yang sedang digunakan.',
                ]);
        }

        // Pastikan selalu ada minimal satu Admin KONI aktif.
        if (
            $pengguna->hasRole('admin_koni') &&
            $pengguna->is_active &&
            !($nextRole === 'admin_koni' && $nextActive)
        ) {
            $activeAdmins = User::where('is_active', true)
                ->whereHas('roles', function ($q) {
                    $q->where('name', 'admin_koni');
                })
                ->count();

            if ($activeAdmins <= 1) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'role' => 'Tidak dapat menonaktifkan atau mengubah role Admin KONI terakhir yang masih aktif.',
                    ]);
            }
        }

        DB::transaction(function () use ($pengguna, $validated, $nextRole) {

            $currentAtlet = $pengguna->atlet;
            $currentPelatih = $pengguna->pelatih;

            // Lepaskan relasi profil yang tidak lagi sesuai role.
            if ($nextRole !== self::LEGACY_ROLE) {

                if (
                    $currentAtlet &&
                    (
                        $nextRole !== 'atlet' ||
                        (int) $currentAtlet->id !== (int) ($validated['atlet_id'] ?? 0)
                    )
                ) {
                    $currentAtlet->update(['user_id' => null]);
                }

                if (
                    $currentPelatih &&
                    (
                        $nextRole !== 'pelatih' ||
                        (int) $currentPelatih->id !== (int) ($validated['pelatih_id'] ?? 0)
                    )
                ) {
                    $currentPelatih->update(['user_id' => null]);
                }
            }

            $data = [
                'name' => $validated['name'],
                'email' => $validated['email'],

                'cabang_olahraga_id' =>
                    $nextRole === 'admin_cabor'
                        ? $validated['cabang_olahraga_id']
                        : null,

                'is_active' => $validated['is_active'],
            ];

            // Password hanya diperbarui jika diisi.
            if (!empty($validated['password'])) {
                $data['password'] = $validated['password'];
            }

            $pengguna->update($data);

            $pengguna->syncRoles([$nextRole]);

            // Hubungkan profil atlet.
            if ($nextRole === 'atlet') {

                $atlet = Atlet::whereKey($validated['atlet_id'])
                    ->where('status', 'aktif')
                    ->where(function ($q) use ($pengguna) {
                        $q->whereNull('user_id')
                            ->orWhere('user_id', $pengguna->id);
                    })
                    ->lockForUpdate()
                    ->first();

                if (!$atlet) {
                    throw ValidationException::withMessages([
                        'atlet_id' => 'Profil atlet sudah digunakan akun lain.',
                    ]);
                }

                $atlet->update(['user_id' => $pengguna->id]);
            }

            // Hubungkan profil pelatih.
            if ($nextRole === 'pelatih') {

                $pelatih = Pelatih::whereKey($validated['pelatih_id'])
                    ->where('status', 'aktif')
                    ->where(function ($q) use ($pengguna) {
                        $q->whereNull('user_id')
                            ->orWhere('user_id', $pengguna->id);
                    })
                    ->lockForUpdate()
                    ->first();

                if (!$pelatih) {
                    throw ValidationException::withMessages([
                        'pelatih_id' => 'Profil pelatih sudah digunakan akun lain.',
                    ]);
                }

                $pelatih->update(['user_id' => $pengguna->id]);
            }

        });

        return redirect()
            ->route('admin.pengguna.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }


    /**
     * Hapus akun pengguna.
     */
    public function destroy(User $pengguna): RedirectResponse
    {
        // Cegah penghapusan akun sendiri.
        if ($pengguna->id === auth()->id()) {
            return back()->with(
                'error',
                'Anda tidak dapat menghapus akun yang sedang digunakan.'
            );
        }

        // Lindungi Admin KONI terakhir.
        if ($pengguna->hasRole('admin_koni') && $pengguna->is_active) {

            $activeAdmins = User::where('is_active', true)
                ->whereHas('roles', function ($q) {
                    $q->where('name', 'admin_koni');
                })
                ->count();

            if ($activeAdmins <= 1) {
                return back()->with(
                    'error',
                    'Admin KONI terakhir tidak dapat dihapus.'
                );
            }
        }

        $pengguna->delete();

        return redirect()
            ->route('admin.pengguna.index')
            ->with('success', 'Akun pengguna berhasil dihapus.');
    }
}
