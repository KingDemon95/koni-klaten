<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengurus;
use App\Models\CabangOlahraga;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class StrukturOrganisasiController extends Controller
{
    /**
     * Daftar seluruh pengurus.
     */
    public function index(Request $request): View
    {
        $query = Pengurus::with('cabangOlahraga');

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
        $validated = $request->validate([

            'cabang_olahraga_id' => [
                'nullable',
                'integer',
                Rule::exists('cabang_olahraga', 'id')
                    ->where('status', 'aktif'),
            ],

            'nama_lengkap' => [
                'required',
                'string',
                'max:150',
            ],

            'jabatan' => [
                'required',
                'string',
                'max:120',
            ],

            'bidang' => [
                'nullable',
                'string',
                'max:120',
            ],

            'jenis_kelamin' => [
                'nullable',
                Rule::in(['L', 'P']),
            ],

            'periode_mulai' => [
                'nullable',
                'integer',
                'between:1900,2100',
            ],

            'periode_selesai' => [
                'nullable',
                'integer',
                'between:1900,2100',
            ],

            'no_telepon' => [
                'nullable',
                'string',
                'max:30',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'urutan' => [
                'required',
                'integer',
                'min:0',
                'max:999',
            ],

            'status' => [
                'required',
                Rule::in(['aktif', 'nonaktif']),
            ],

            'foto' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

        ]);

        if (
            $validated['periode_mulai'] !== null &&
            $validated['periode_selesai'] !== null &&
            $validated['periode_selesai'] < $validated['periode_mulai']
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'periode_selesai' =>
                        'Tahun akhir tidak boleh lebih kecil dari tahun awal.',
                ]);
        }

        if ($request->hasFile('foto')) {
            $validated['foto_path'] = $request
                ->file('foto')
                ->store('pengurus', 'public');
        }

        unset($validated['foto']);

        Pengurus::create($validated);

        return redirect()
            ->route('admin.struktur-organisasi.index')
            ->with('success', 'Data pengurus berhasil ditambahkan.');
    }


    /**
     * Form edit pengurus.
     */
    public function edit(Pengurus $struktur_organisasi): View
    {
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

        $validated = $request->validate([

            'cabang_olahraga_id' => [
                'nullable',
                'integer',
                'exists:cabang_olahraga,id',
            ],

            'nama_lengkap' => [
                'required',
                'string',
                'max:150',
            ],

            'jabatan' => [
                'required',
                'string',
                'max:120',
            ],

            'bidang' => [
                'nullable',
                'string',
                'max:120',
            ],

            'jenis_kelamin' => [
                'nullable',
                Rule::in(['L', 'P']),
            ],

            'periode_mulai' => [
                'nullable',
                'integer',
                'between:1900,2100',
            ],

            'periode_selesai' => [
                'nullable',
                'integer',
                'between:1900,2100',
            ],

            'no_telepon' => [
                'nullable',
                'string',
                'max:30',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'urutan' => [
                'required',
                'integer',
                'min:0',
                'max:999',
            ],

            'status' => [
                'required',
                Rule::in(['aktif', 'nonaktif']),
            ],

            'foto' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

        ]);

        if (
            $validated['periode_mulai'] !== null &&
            $validated['periode_selesai'] !== null &&
            $validated['periode_selesai'] < $validated['periode_mulai']
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'periode_selesai' =>
                        'Tahun akhir tidak boleh lebih kecil dari tahun awal.',
                ]);
        }

        if ($request->hasFile('foto')) {

            $newFoto = $request
                ->file('foto')
                ->store('pengurus', 'public');

            if ($struktur_organisasi->foto_path) {
                Storage::disk('public')->delete(
                    $struktur_organisasi->foto_path
                );
            }

            $validated['foto_path'] = $newFoto;
        }

        unset($validated['foto']);

        $struktur_organisasi->update($validated);

        return redirect()
            ->route('admin.struktur-organisasi.index')
            ->with('success', 'Data pengurus berhasil diperbarui.');
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

        $struktur_organisasi->delete();

        return redirect()
            ->route('admin.struktur-organisasi.index')
            ->with('success', 'Data pengurus berhasil dihapus.');
    }
}
