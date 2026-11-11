<?php

namespace App\Http\Controllers\Cabor;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

abstract class CaborProfileController extends Controller
{
    abstract protected function modelClass(): string;

    abstract protected function relationName(): string;

    abstract protected function jenis(): string;

    protected function extraRules(): array
    {
        return [];
    }

    protected function rules(): array
    {
        return array_merge([
            'nama_lengkap' => [
                'required', 'string', 'max:150',
            ],
            'jenis_kelamin' => [
                'required', Rule::in(['L', 'P']),
            ],
            'tempat_lahir' => [
                'nullable', 'string', 'max:100',
            ],
            'tanggal_lahir' => [
                'nullable', 'date', 'before_or_equal:today',
            ],
            'alamat' => [
                'nullable', 'string', 'max:2000',
            ],
            'no_telepon' => [
                'nullable', 'string', 'max:30',
            ],
            'email' => [
                'nullable', 'email', 'max:255',
            ],
            'status' => [
                'required', Rule::in(['aktif', 'nonaktif']),
            ],
            'foto' => [
                'nullable', 'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ], $this->extraRules());
    }

    private function findOwned(
        Request $request,
        string $id
    ): Model {
        // Pastikan parameter URL berupa ID numerik.
        abort_unless(ctype_digit($id), 404);

        $cabor = $request->user()->cabangOlahraga;

        abort_unless($cabor !== null, 403);

        $relation = $this->relationName();

        // Cari hanya di dalam data cabor pengguna.
        // ID milik cabor lain akan menghasilkan 404.
        return $cabor->{$relation}()
            ->findOrFail((int) $id);
    }

    private function indexRoute(): string
    {
        return 'cabor.'.$this->jenis().'.index';
    }

    public function create(Request $request): View
    {
        $class = $this->modelClass();

        Gate::authorize('create', $class);

        return view('cabor.form', [
            'cabor' => $request->user()->cabangOlahraga,
            'item' => new $class(),
            'jenis' => $this->jenis(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $class = $this->modelClass();

        Gate::authorize('create', $class);

        $data = $request->validate($this->rules());

        // Jangan menerima user_id atau cabang_olahraga_id
        // dari request pengguna.
        unset($data['foto']);

        $data['cabang_olahraga_id'] =
            $request->user()->cabang_olahraga_id;

        $newPath = null;

        try {
            if ($request->hasFile('foto')) {
                $newPath = $request->file('foto')
                    ->store($this->jenis(), 'public');

                if ($newPath === false) {
                    throw new RuntimeException(
                        'Foto gagal disimpan.'
                    );
                }

                $data['foto_path'] = $newPath;
            }

            $class::create($data);

        } catch (Throwable $exception) {

            if ($newPath) {
                Storage::disk('public')->delete($newPath);
            }

            throw $exception;
        }

        return redirect()
            ->route($this->indexRoute())
            ->with('success', 'Data berhasil ditambahkan.');
    }

    public function edit(
        Request $request,
        string $id
    ): View {
        $item = $this->findOwned($request, $id);

        Gate::authorize('update', $item);

        return view('cabor.form', [
            'cabor' => $request->user()->cabangOlahraga,
            'item' => $item,
            'jenis' => $this->jenis(),
        ]);
    }

    public function update(
        Request $request,
        string $id
    ): RedirectResponse {
        $item = $this->findOwned($request, $id);

        Gate::authorize('update', $item);

        $data = $request->validate($this->rules());

        // Tidak boleh mengubah user_id atau
        // cabang_olahraga_id lewat form.
        unset($data['foto']);

        $oldPath = $item->foto_path;
        $newPath = null;

        try {
            if ($request->hasFile('foto')) {
                $newPath = $request->file('foto')
                    ->store($this->jenis(), 'public');

                if ($newPath === false) {
                    throw new RuntimeException(
                        'Foto gagal disimpan.'
                    );
                }

                $data['foto_path'] = $newPath;
            }

            $item->update($data);

        } catch (Throwable $exception) {

            if ($newPath) {
                Storage::disk('public')->delete($newPath);
            }

            throw $exception;
        }

        // Foto lama baru dihapus setelah update berhasil.
        if ($newPath && $oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return redirect()
            ->route($this->indexRoute())
            ->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(
        Request $request,
        string $id
    ): RedirectResponse {
        $item = $this->findOwned($request, $id);

        Gate::authorize('delete', $item);

        // Lindungi profil yang sudah digunakan oleh akun.
        if ($item->user_id !== null) {
            return redirect()
                ->route($this->indexRoute())
                ->with(
                    'error',
                    'Data tidak dapat dihapus karena sudah terhubung dengan akun pengguna.'
                );
        }

        $oldPath = $item->foto_path;

        $item->delete();

        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return redirect()
            ->route($this->indexRoute())
            ->with('success', 'Data berhasil dihapus.');
    }
}
