<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Atlet;
use App\Models\CabangOlahraga;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

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
                Rule::exists('cabang_olahraga', 'id')
                    ->where('status', 'aktif'),
            ],

            'nama_lengkap' => [
                'required',
                'string',
                'max:150',
            ],

            'jenis_kelamin' => [
                'required',
                Rule::in(['L', 'P']),
            ],

            'tempat_lahir' => [
                'nullable',
                'string',
                'max:100',
            ],

            'tanggal_lahir' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'alamat' => [
                'nullable',
                'string',
                'max:2000',
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

        if ($request->hasFile('foto')) {
            $validated['foto_path'] = $request
                ->file('foto')
                ->store('atlet', 'public');
        }

        unset($validated['foto']);

        Atlet::create($validated);

        return redirect()
            ->route('admin.atlet.index')
            ->with('success', 'Data atlet berhasil ditambahkan.');
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

        $validated = $request->validate([

            'cabang_olahraga_id' => [
                'required',
                'integer',
                'exists:cabang_olahraga,id',
            ],

            'nama_lengkap' => [
                'required',
                'string',
                'max:150',
            ],

            'jenis_kelamin' => [
                'required',
                Rule::in(['L', 'P']),
            ],

            'tempat_lahir' => [
                'nullable',
                'string',
                'max:100',
            ],

            'tanggal_lahir' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'alamat' => [
                'nullable',
                'string',
                'max:2000',
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

        if ($request->hasFile('foto')) {

            $newFoto = $request
                ->file('foto')
                ->store('atlet', 'public');

            if ($atlet->foto_path) {
                Storage::disk('public')->delete($atlet->foto_path);
            }

            $validated['foto_path'] = $newFoto;
        }

        unset($validated['foto']);

        $atlet->update($validated);

        return redirect()
            ->route('admin.atlet.index')
            ->with('success', 'Data atlet berhasil diperbarui.');
    }


    /**
     * Hapus atlet.
     */
    public function destroy(Atlet $atlet): RedirectResponse
    {
        if ($atlet->foto_path) {
            Storage::disk('public')->delete($atlet->foto_path);
        }

        $atlet->delete();

        return redirect()
            ->route('admin.atlet.index')
            ->with('success', 'Data atlet berhasil dihapus.');
    }
}
