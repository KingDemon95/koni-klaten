<?php

namespace App\Http\Controllers;

use App\Models\Publikasi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PublikasiController extends Controller
{
    public function index(Request $request): View
    {
        $searchInput = $request->query('q', '');
        $jenisInput = $request->query('jenis', '');
        $statusInput = $request->query('status', '');

        abort_unless(is_string($searchInput) && is_string($jenisInput) && is_string($statusInput), 422);
        abort_unless(in_array($jenisInput, ['', ...array_keys(Publikasi::jenisLabels())], true), 422);
        abort_unless(in_array($statusInput, ['', ...array_keys(Publikasi::statusLabels())], true), 422);

        $search = mb_substr(trim($searchInput), 0, 100);
        $jenis = $jenisInput;
        $status = $statusInput;

        $baseQuery = Publikasi::query();
        $query = (clone $baseQuery)->with(['creator', 'publisher']);

        if ($search !== '') {
            $query->where(function (Builder $nested) use ($search): void {
                $nested->where('judul', 'like', "%{$search}%")
                    ->orWhere('ringkasan', 'like', "%{$search}%")
                    ->orWhere('kategori', 'like', "%{$search}%");
            });
        }

        if ($jenis !== '') {
            $query->where('jenis', $jenis);
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        $items = $query->orderByDesc('updated_at')->paginate(12)->withQueryString();
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'draft' => (clone $baseQuery)->where('status', Publikasi::STATUS_DRAFT)->count(),
            'published' => (clone $baseQuery)->where('status', Publikasi::STATUS_PUBLISHED)->count(),
        ];

        return view('publikasi.index', $this->viewData($request) + compact(
            'items', 'search', 'jenis', 'status', 'stats'
        ));
    }

    public function create(Request $request): View
    {
        return view('publikasi.create', $this->viewData($request) + [
            'publikasi' => new Publikasi(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $media = $request->file('media');
        $thumbnail = $request->file('thumbnail');

        unset($validated['media'], $validated['thumbnail']);

        $publikasi = Publikasi::create($validated + [
            'slug' => $this->uniqueSlug($validated['judul']),
            'media_path' => $this->storeFile($media),
            'media_original_name' => $media?->getClientOriginalName(),
            'thumbnail_path' => $this->storeFile($thumbnail),
            'thumbnail_original_name' => $thumbnail?->getClientOriginalName(),
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
            'status' => Publikasi::STATUS_DRAFT,
        ]);

        return redirect()
            ->route($this->routePrefix($request).'.publikasi.show', $publikasi)
            ->with('success', 'Konten berhasil disimpan sebagai draft.');
    }

    public function show(Request $request, Publikasi $publikasi): View
    {
        return view('publikasi.show', $this->viewData($request) + compact('publikasi'));
    }

    public function edit(Request $request, Publikasi $publikasi): View
    {
        return view('publikasi.edit', $this->viewData($request) + compact('publikasi'));
    }

    public function update(Request $request, Publikasi $publikasi): RedirectResponse
    {
        $validated = $this->validatedData($request, $publikasi);
        $media = $request->file('media');
        $thumbnail = $request->file('thumbnail');

        unset($validated['media'], $validated['thumbnail']);

        if ($publikasi->judul !== $validated['judul']) {
            $validated['slug'] = $this->uniqueSlug($validated['judul'], $publikasi->id);
        }

        if ($media instanceof UploadedFile) {
            $this->deleteFile($publikasi->media_path);
            $validated['media_path'] = $this->storeFile($media);
            $validated['media_original_name'] = $media->getClientOriginalName();
        }

        if ($publikasi->jenis !== $validated['jenis'] && !($media instanceof UploadedFile)) {
            $this->deleteFile($publikasi->media_path);
            $validated['media_path'] = null;
            $validated['media_original_name'] = null;
        }

        if ($thumbnail instanceof UploadedFile) {
            $this->deleteFile($publikasi->thumbnail_path);
            $validated['thumbnail_path'] = $this->storeFile($thumbnail);
            $validated['thumbnail_original_name'] = $thumbnail->getClientOriginalName();
        }

        $validated['updated_by'] = $request->user()->id;
        $publikasi->update($validated);

        return redirect()
            ->route($this->routePrefix($request).'.publikasi.show', $publikasi)
            ->with('success', 'Konten berhasil diperbarui.');
    }

    public function destroy(Request $request, Publikasi $publikasi): RedirectResponse
    {
        $this->deleteFile($publikasi->media_path);
        $this->deleteFile($publikasi->thumbnail_path);
        $publikasi->delete();

        return redirect()
            ->route($this->routePrefix($request).'.publikasi.index')
            ->with('success', 'Konten dan file terkait berhasil dihapus.');
    }

    public function publish(Request $request, Publikasi $publikasi): RedirectResponse
    {
        abort_if($publikasi->jenis === Publikasi::JENIS_BERITA && blank($publikasi->konten), 422, 'Berita harus memiliki isi sebelum dipublikasikan.');
        abort_if(in_array($publikasi->jenis, [Publikasi::JENIS_FOTO, Publikasi::JENIS_VIDEO], true) && blank($publikasi->media_path), 422, 'Dokumentasi harus memiliki file media sebelum dipublikasikan.');

        $publikasi->update([
            'status' => Publikasi::STATUS_PUBLISHED,
            'published_at' => now(),
            'published_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return redirect()
            ->route($this->routePrefix($request).'.publikasi.show', $publikasi)
            ->with('success', 'Konten berhasil dipublikasikan ke website KONI Klaten.');
    }

    public function media(Request $request, Publikasi $publikasi, string $variant = 'media'): BinaryFileResponse
    {
        abort_unless(in_array($variant, ['media', 'thumbnail'], true), 404);
        $path = $variant === 'thumbnail' ? $publikasi->thumbnail_path : $publikasi->media_path;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        $fullPath = Storage::disk('local')->path($path);
        $mime = Storage::disk('local')->mimeType($path) ?: 'application/octet-stream';

        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function validatedData(Request $request, ?Publikasi $publikasi = null): array
    {
        $jenisInput = $request->input('jenis', $publikasi?->jenis ?? '');
        $jenis = is_string($jenisInput) ? $jenisInput : '';
        $mediaRequired = in_array($jenis, [Publikasi::JENIS_FOTO, Publikasi::JENIS_VIDEO], true)
            && (blank($publikasi?->media_path) || $jenis !== $publikasi?->jenis);

        $mediaRules = match ($jenis) {
            Publikasi::JENIS_FOTO => [$mediaRequired ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            Publikasi::JENIS_VIDEO => [$mediaRequired ? 'required' : 'nullable', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime', 'max:51200'],
            default => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        };

        return $request->validate([
            'judul' => ['required', 'string', 'max:180'],
            'jenis' => ['required', Rule::in(array_keys(Publikasi::jenisLabels()))],
            'kategori' => ['nullable', 'string', 'max:80'],
            'ringkasan' => ['nullable', 'string', 'max:500'],
            'konten' => [Rule::requiredIf($jenis === Publikasi::JENIS_BERITA), 'nullable', 'string', 'max:50000'],
            'media' => $mediaRules,
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
    }

    private function viewData(Request $request): array
    {
        return [
            'layout' => $this->routePrefix($request) === 'admin' ? 'layouts.admin' : 'layouts.staff',
            'routePrefix' => $this->routePrefix($request),
        ];
    }

    private function routePrefix(Request $request): string
    {
        $name = $request->route()?->getName() ?? '';

        foreach (['admin', 'staff'] as $prefix) {
            if (str_starts_with($name, $prefix.'.publikasi.')) {
                return $prefix;
            }
        }

        abort(403);
    }

    private function uniqueSlug(string $judul, ?int $ignoreId = null): string
    {
        $base = Str::slug($judul) ?: 'publikasi';
        $slug = $base;
        $suffix = 2;

        while (Publikasi::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn (Builder $query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    private function storeFile(?UploadedFile $file): ?string
    {
        return $file?->store('publikasi', 'local');
    }

    private function deleteFile(?string $path): void
    {
        if ($path) {
            Storage::disk('local')->delete($path);
        }
    }
}
