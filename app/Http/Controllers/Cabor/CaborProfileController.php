<?php

namespace App\Http\Controllers\Cabor;

use App\Http\Controllers\Controller;
use App\Support\AccountProvisioner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
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

    protected function rules(?Model $item = null): array
    {
        $emailUnique = Rule::unique('users', 'email');
        if ($item?->user_id) {
            $emailUnique->ignore($item->user_id);
        }

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
                'required',
                'email',
                'max:255',
                $emailUnique,
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

        $data['cabang_olahraga_id'] = $request->user()->cabang_olahraga_id;
        $newPath = null;

        try {
            if ($request->hasFile('foto')) {
                $newPath = $request->file('foto')->store($this->jenis(), 'public');

                if ($newPath === false) {
                    throw new RuntimeException('Foto gagal disimpan.');
                }

                $data['foto_path'] = $newPath;
            }

            DB::transaction(function () use ($class, $data, &$item) {
                $item = $class::create($data);

                $user = AccountProvisioner::create(
                    $item->nama_lengkap,
                    $item->email,
                    $this->jenis(),
                    null,
                    $item->status === 'aktif',
                );

                $item->user()->associate($user);
                $item->save();
            });
        } catch (Throwable $exception) {
            if ($newPath) {
                Storage::disk('public')->delete($newPath);
            }

            throw $exception;
        }

        $role = $this->jenis();
        $password = config("account_defaults.passwords.{$role}");

        return redirect()
            ->route($this->indexRoute())
            ->with('success', 'Data '.$role.' dan akun login berhasil dibuat. Login: '.$data['email'].'. Password awal: '.$password.'.');
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

        $hadAccount = $item->user_id !== null;
        $data = $request->validate($this->rules($item));

        // Tidak boleh mengubah user_id atau cabang_olahraga_id lewat form.
        unset($data['foto']);

        $oldPath = $item->foto_path;
        $newPath = null;

        try {
            if ($request->hasFile('foto')) {
                $newPath = $request->file('foto')->store($this->jenis(), 'public');

                if ($newPath === false) {
                    throw new RuntimeException('Foto gagal disimpan.');
                }

                $data['foto_path'] = $newPath;
            }

            DB::transaction(function () use ($item, $data) {
                $item->update($data);

                $user = $item->user;
                if ($user) {
                    $user->update([
                        'name' => $item->nama_lengkap,
                        'email' => mb_strtolower(trim($item->email)),
                        'is_active' => $item->status === 'aktif',
                    ]);
                } else {
                    $user = AccountProvisioner::create(
                        $item->nama_lengkap,
                        $item->email,
                        $this->jenis(),
                        null,
                        $item->status === 'aktif',
                    );

                    $item->user()->associate($user);
                    $item->save();
                }
            });
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

        $role = $this->jenis();
        $successMessage = $hadAccount
            ? 'Data dan akun login berhasil diperbarui.'
            : 'Data dan akun login berhasil dibuat. Login: '.$data['email'].'. Password awal: '.config("account_defaults.passwords.{$role}").'.';

        return redirect()
            ->route($this->indexRoute())
            ->with('success', $successMessage);
    }

    public function destroy(
        Request $request,
        string $id
    ): RedirectResponse {
        $item = $this->findOwned($request, $id);

        Gate::authorize('delete', $item);

        $oldPath = $item->foto_path;

        // Akun tetap disimpan untuk jejak administrasi, tetapi dinonaktifkan
        // agar tidak dapat login tanpa profil.
        $item->user?->update(['is_active' => false]);
        $item->delete();

        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return redirect()
            ->route($this->indexRoute())
            ->with('success', 'Data berhasil dihapus.');
    }
}
