<?php

namespace App\Http\Controllers\Cabor;

use App\Http\Controllers\Controller;
use App\Models\Pengurus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class PengurusController extends Controller
{
    /**
     * Aturan validasi sesuai tabel pengurus.
     */
    private function rules(): array
    {
        return [
            'nama_lengkap' => [
                'required', 'string', 'max:150',
            ],
            'jabatan' => [
                'required', 'string', 'max:120',
            ],
            'bidang' => [
                'nullable', 'string', 'max:120',
            ],
            'jenis_kelamin' => [
                'nullable', Rule::in(['L', 'P']),
            ],
            'periode_mulai' => [
                'nullable', 'integer', 'between:1900,2100',
            ],
            'periode_selesai' => [
                'nullable', 'integer', 'between:1900,2100',
            ],
            'no_telepon' => [
                'nullable', 'string', 'max:30',
            ],
            'email' => [
                'nullable', 'email', 'max:255',
            ],
            'urutan' => [
                'required', 'integer', 'min:0', 'max:999',
            ],
            'status' => [
                'required', Rule::in(['aktif', 'nonaktif']),
            ],
            'foto' => [
                'nullable', 'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ];
    }

    /**
     * Validasi tambahan untuk periode kepengurusan.
     */
    private function validatePeriod(array $data): void
    {
        $mulai = $data['periode_mulai'] ?? null;
        $selesai = $data['periode_selesai'] ?? null;

        if (
            $mulai !== null &&
            $selesai !== null &&
            (int) $selesai < (int) $mulai
        ) {
            throw ValidationException::withMessages([
                'periode_selesai' =>
                    'Tahun selesai tidak boleh lebih kecil dari tahun mulai.',
            ]);
        }
    }

    /**
     * Cari pengurus hanya di cabang milik pengguna.
     */
    private function findOwned(
        Request $request,
        string $pengurus
    ): Pengurus {
        abort_unless(ctype_digit($pengurus), 404);

        $cabor = $request->user()->cabangOlahraga;

        abort_unless($cabor !== null, 403);

        // ID pengurus cabor lain dan KONI Kabupaten
        // sama-sama menghasilkan 404.
        return $cabor->pengurus()
            ->findOrFail((int) $pengurus);
    }

    /**
     * Daftar pengurus cabang olahraga.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Pengurus::class);

        $cabor = $request->user()->cabangOlahraga;

        abort_unless($cabor !== null, 403);

        $statusInput = $request->query('status', '');

        $status = in_array(
            $statusInput,
            ['aktif', 'nonaktif'],
            true
        ) ? $statusInput : '';

        $query = $cabor->pengurus();

        if ($status !== '') {
            $query->where('status', $status);
        }

        $pengurus = $query
            ->orderBy('urutan')
            ->orderBy('nama_lengkap')
            ->paginate(10)
            ->withQueryString();

        $total = $cabor->pengurus()->count();

        $aktif = $cabor->pengurus()
            ->where('status', 'aktif')
            ->count();

        $nonaktif = $cabor->pengurus()
            ->where('status', 'nonaktif')
            ->count();

        return view('cabor.pengurus.index', compact(
            'cabor',
            'pengurus',
            'status',
            'total',
            'aktif',
            'nonaktif'
        ));
    }

    /**
     * Form tambah pengurus.
     */
    public function create(Request $request): View
    {
        Gate::authorize('create', Pengurus::class);

        return view('cabor.pengurus.form', [
            'cabor' => $request->user()->cabangOlahraga,
            'pengurus' => new Pengurus(),
        ]);
    }

    /**
     * Simpan pengurus baru.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Pengurus::class);

        $data = $request->validate($this->rules());

        $this->validatePeriod($data);

        // Field cabor tidak diambil dari request.
        // Data otomatis dimiliki cabor pengguna login.
        unset($data['foto']);

        $cabor = $request->user()->cabangOlahraga;

        abort_unless($cabor !== null, 403);

        $newPath = null;

        try {
            if ($request->hasFile('foto')) {
                $newPath = $request->file('foto')
                    ->store('pengurus', 'public');

                if ($newPath === false) {
                    throw new RuntimeException(
                        'Gagal menyimpan foto pengurus.'
                    );
                }

                $data['foto_path'] = $newPath;
            }

            // Relasi menambahkan cabang_olahraga_id
            // berdasarkan cabor akun yang sedang login.
            $cabor->pengurus()->create($data);

        } catch (Throwable $e) {
            if ($newPath) {
                Storage::disk('public')->delete($newPath);
            }

            throw $e;
        }

        return redirect()
            ->route('cabor.pengurus.index')
            ->with(
                'success',
                'Pengurus cabang olahraga berhasil ditambahkan.'
            );
    }

    /**
     * Form edit pengurus.
     */
    public function edit(
        Request $request,
        string $pengurus
    ): View {
        $item = $this->findOwned($request, $pengurus);

        Gate::authorize('update', $item);

        return view('cabor.pengurus.form', [
            'cabor' => $request->user()->cabangOlahraga,
            'pengurus' => $item,
        ]);
    }

    /**
     * Perbarui pengurus.
     */
    public function update(
        Request $request,
        string $pengurus
    ): RedirectResponse {
        $item = $this->findOwned($request, $pengurus);

        Gate::authorize('update', $item);

        $data = $request->validate($this->rules());

        $this->validatePeriod($data);

        // cabang_olahraga_id tidak divalidasi
        // maupun dimasukkan ke data perubahan.
        unset($data['foto']);

        $oldPath = $item->foto_path;
        $newPath = null;

        try {
            if ($request->hasFile('foto')) {
                $newPath = $request->file('foto')
                    ->store('pengurus', 'public');

                if ($newPath === false) {
                    throw new RuntimeException(
                        'Gagal menyimpan foto pengurus.'
                    );
                }

                $data['foto_path'] = $newPath;
            }

            $item->update($data);

        } catch (Throwable $e) {
            if ($newPath) {
                Storage::disk('public')->delete($newPath);
            }

            throw $e;
        }

        // Foto lama dihapus setelah update berhasil.
        if ($newPath && $oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return redirect()
            ->route('cabor.pengurus.index')
            ->with(
                'success',
                'Data pengurus berhasil diperbarui.'
            );
    }

    /**
     * Hapus pengurus.
     */
    public function destroy(
        Request $request,
        string $pengurus
    ): RedirectResponse {
        $item = $this->findOwned($request, $pengurus);

        Gate::authorize('delete', $item);

        $oldPath = $item->foto_path;

        // Data dihapus setelah kepemilikan diperiksa.
        $item->delete();

        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return redirect()
            ->route('cabor.pengurus.index')
            ->with(
                'success',
                'Pengurus cabang olahraga berhasil dihapus.'
            );
    }
}
