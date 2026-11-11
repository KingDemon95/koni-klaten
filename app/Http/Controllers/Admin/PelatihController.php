<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pelatih;
use App\Models\CabangOlahraga;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class PelatihController extends Controller
{
    /**
     * Daftar pelatih.
     */
    public function index(Request $request): View
    {
        $query = Pelatih::with('cabangOlahraga');

        $search = trim((string) $request->query('q', ''));
        $status = $request->query('status', '');
        $caborId = $request->query('cabor_id', '');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('no_telepon', 'like', "%{$search}%")
                    ->orWhere('spesialisasi', 'like', "%{$search}%")
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

        $pelatihs = $query
            ->orderBy('nama_lengkap')
            ->paginate(10)
            ->withQueryString();

        $total = Pelatih::count();

        $aktif = Pelatih::where('status', 'aktif')->count();

        $nonaktif = Pelatih::where('status', 'nonaktif')->count();

        $cabors = CabangOlahraga::orderBy('nama')->get();

        return view('admin.pelatih.index', compact(
            'pelatihs',
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
     * Form tambah pelatih.
     */
    public function create(): View
    {
        $cabors = CabangOlahraga::where('status', 'aktif')
            ->orderBy('nama')
            ->get();

        return view('admin.pelatih.create', [
            'pelatih' => new Pelatih(),
            'cabors' => $cabors,
        ]);
    }


    /**
     * Simpan pelatih baru.
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

            'pendidikan_terakhir' => [
                'nullable',
                'string',
                'max:150',
            ],

            'spesialisasi' => [
                'nullable',
                'string',
                'max:150',
            ],

            'sertifikasi' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'pengalaman_tahun' => [
                'nullable',
                'integer',
                'min:0',
                'max:60',
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
                ->store('pelatih', 'public');
        }

        unset($validated['foto']);

        Pelatih::create($validated);

        return redirect()
            ->route('admin.pelatih.index')
            ->with('success', 'Data pelatih berhasil ditambahkan.');
    }


    /**
     * Form edit pelatih.
     */
    public function edit(Pelatih $pelatih): View
    {
        $cabors = CabangOlahraga::orderBy('nama')->get();

        return view('admin.pelatih.edit', compact(
            'pelatih',
            'cabors'
        ));
    }


    /**
     * Update data pelatih.
     */
    public function update(
        Request $request,
        Pelatih $pelatih
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

            'pendidikan_terakhir' => [
                'nullable',
                'string',
                'max:150',
            ],

            'spesialisasi' => [
                'nullable',
                'string',
                'max:150',
            ],

            'sertifikasi' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'pengalaman_tahun' => [
                'nullable',
                'integer',
                'min:0',
                'max:60',
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
                ->store('pelatih', 'public');

            if ($pelatih->foto_path) {
                Storage::disk('public')->delete($pelatih->foto_path);
            }

            $validated['foto_path'] = $newFoto;
        }

        unset($validated['foto']);

        $pelatih->update($validated);

        return redirect()
            ->route('admin.pelatih.index')
            ->with('success', 'Data pelatih berhasil diperbarui.');
    }


    /**
     * Hapus pelatih.
     */
    public function destroy(Pelatih $pelatih): RedirectResponse
    {
        if ($pelatih->foto_path) {
            Storage::disk('public')->delete($pelatih->foto_path);
        }

        $pelatih->delete();

        return redirect()
            ->route('admin.pelatih.index')
            ->with('success', 'Data pelatih berhasil dihapus.');
    }
}
