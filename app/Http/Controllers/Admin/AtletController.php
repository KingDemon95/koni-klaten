<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Atlet;
use App\Models\CabangOlahraga;
use App\Support\AccountProvisioner;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Throwable;

class AtletController extends Controller
{
    /**
     * Daftar atlet.
     */
    public function index(Request $request): View
    {
        $query = Atlet::with('cabangOlahraga');

        $search = trim((string) $request->query('q', ''));
        $status = $request->query('status', '');
        $caborId = $request->query('cabor_id', '');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('no_telepon', 'like', "%{$search}%")
                    ->orWhereHas('cabangOlahraga', function ($cabor) use ($search) {
                        $cabor->where('nama', 'like', "%{$search}%")
                            ->orWhere('singkatan', 'like', "%{$search}%");
                    });
            });
        }

        if (in_array($status, ['aktif', 'nonaktif'], true)) {
            $query->where('status', $status);
        }

        if ($caborId !== '' && ctype_digit((string) $caborId)) {
            $query->where('cabang_olahraga_id', $caborId);
        }

        $atlets = $query
            ->orderBy('nama_lengkap')
            ->paginate(10)
            ->withQueryString();

        $total = Atlet::count();

        $aktif = Atlet::where('status', 'aktif')->count();

        $nonaktif = Atlet::where('status', 'nonaktif')->count();

        $cabors = CabangOlahraga::orderBy('nama')->get();

        return view('admin.atlet.index', compact(
            'atlets',
            'cabors',
            'search',
            'status',
            'caborId',
            'total',
            'aktif',
            'nonaktif'
        ));
    }


    /**
     * Form tambah atlet.
     */
    public function create(): View
    {
        $cabors = CabangOlahraga::where('status', 'aktif')
            ->orderBy('nama')
            ->get();

        return view('admin.atlet.create', [
            'atlet' => new Atlet(),
            'cabors' => $cabors,
        ]);
    }


    /**
     * Simpan atlet baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cabang_olahraga_id' => [
                'required',
                'integer',
                Rule::exists('cabang_olahraga', 'id')->where('status', 'aktif'),
            ],
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date', 'before_or_equal:today'],
            'alamat' => ['nullable', 'string', 'max:2000'],
            'no_telepon' => ['nullable', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $photoPath = null;
        if ($request->hasFile('foto')) {
            $photoPath = $request->file('foto')->store('atlet', 'public');
            $validated['foto_path'] = $photoPath;
        }

        unset($validated['foto']);

        try {
            DB::transaction(function () use ($validated, &$atlet) {
                $atlet = Atlet::create($validated);

                $user = AccountProvisioner::create(
                    $atlet->nama_lengkap,
                    $atlet->email,
                    'atlet',
                    null,
                    $atlet->status === 'aktif',
                );

                $atlet->user()->associate($user);
                $atlet->save();
            });
        } catch (Throwable $exception) {
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }
            throw $exception;
        }

        return redirect()
            ->route('admin.atlet.index')
            ->with('success', 'Data atlet dan akun login berhasil dibuat. Login: '.$validated['email'].'. Password awal: '.config('account_defaults.passwords.atlet').'.');
    }

    /**
     * Form edit atlet.
     */
    public function edit(Atlet $atlet): View
    {
        $cabors = CabangOlahraga::orderBy('nama')->get();

        return view('admin.atlet.edit', compact(
            'atlet',
            'cabors'
        ));
    }


    /**
     * Update data atlet.
     */
    public function update(
        Request $request,
        Atlet $atlet
    ): RedirectResponse {
        $hadAccount = $atlet->user_id !== null;
        $emailUnique = Rule::unique('users', 'email');
        if ($atlet->user_id) {
            $emailUnique->ignore($atlet->user_id);
        }

        $validated = $request->validate([
            'cabang_olahraga_id' => ['required', 'integer', 'exists:cabang_olahraga,id'],
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date', 'before_or_equal:today'],
            'alamat' => ['nullable', 'string', 'max:2000'],
            'no_telepon' => ['nullable', 'string', 'max:30'],
            'email' => [
                'required',
                'email',
                'max:255',
                $emailUnique,
            ],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $photoPath = null;
        if ($request->hasFile('foto')) {
            $photoPath = $request->file('foto')->store('atlet', 'public');
            $validated['foto_path'] = $photoPath;
        }
        unset($validated['foto']);

        $oldPhoto = $atlet->foto_path;

        try {
            DB::transaction(function () use ($atlet, $validated) {
                $atlet->update($validated);

                $user = $atlet->user;
                if ($user) {
                    $user->update([
                        'name' => $atlet->nama_lengkap,
                        'email' => mb_strtolower(trim($atlet->email)),
                        'is_active' => $atlet->status === 'aktif',
                    ]);
                } else {
                    $user = AccountProvisioner::create(
                        $atlet->nama_lengkap,
                        $atlet->email,
                        'atlet',
                        null,
                        $atlet->status === 'aktif',
                    );
                    $atlet->user()->associate($user);
                    $atlet->save();
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

        $successMessage = $hadAccount
            ? 'Data atlet dan akun login berhasil diperbarui.'
            : 'Data atlet dan akun login berhasil dibuat. Login: '.$validated['email'].'. Password awal: '.config('account_defaults.passwords.atlet').'.';

        return redirect()
            ->route('admin.atlet.index')
            ->with('success', $successMessage);
    }

    /**
     * Hapus atlet.
     */
    public function destroy(Atlet $atlet): RedirectResponse
    {
        if ($atlet->foto_path) {
            Storage::disk('public')->delete($atlet->foto_path);
        }

        $atlet->user?->update(['is_active' => false]);
        $atlet->delete();

        return redirect()
            ->route('admin.atlet.index')
            ->with('success', 'Data atlet berhasil dihapus.');
    }
}
