<?php

namespace App\Http\Controllers;

use App\Models\Publikasi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PublicPublikasiController extends Controller
{
    public function beritaIndex(Request $request): View
    {
        [$search, $kategori] = $this->filters($request);

        $berita = Publikasi::query()
            ->published()
            ->where('jenis', Publikasi::JENIS_BERITA)
            ->with('publisher')
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $nested) use ($search): void {
                $nested->where('judul', 'like', "%{$search}%")
                    ->orWhere('ringkasan', 'like', "%{$search}%")
                    ->orWhere('konten', 'like', "%{$search}%");
            }))
            ->when($kategori !== '', fn (Builder $query) => $query->where('kategori', $kategori))
            ->orderByDesc('published_at')
            ->paginate(9)
            ->withQueryString();

        $categories = Publikasi::query()->published()->where('jenis', Publikasi::JENIS_BERITA)
            ->whereNotNull('kategori')->distinct()->orderBy('kategori')->pluck('kategori');

        return view('public.berita.index', compact('berita', 'search', 'kategori', 'categories'));
    }

    public function beritaShow(Publikasi $publikasi): View
    {
        abort_unless($publikasi->status === Publikasi::STATUS_PUBLISHED && $publikasi->jenis === Publikasi::JENIS_BERITA && $publikasi->published_at, 404);

        return view('public.berita.show', compact('publikasi'));
    }

    public function dokumentasiIndex(Request $request): View
    {
        [$search, $kategori] = $this->filters($request);
        $jenisInput = $request->query('jenis', '');
        abort_unless(is_string($jenisInput), 422);
        abort_unless(in_array($jenisInput, ['', Publikasi::JENIS_FOTO, Publikasi::JENIS_VIDEO], true), 422);

        $dokumentasi = Publikasi::query()
            ->published()
            ->whereIn('jenis', [Publikasi::JENIS_FOTO, Publikasi::JENIS_VIDEO])
            ->when($jenisInput !== '', fn (Builder $query) => $query->where('jenis', $jenisInput))
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $nested) use ($search): void {
                $nested->where('judul', 'like', "%{$search}%")
                    ->orWhere('ringkasan', 'like', "%{$search}%")
                    ->orWhere('kategori', 'like', "%{$search}%");
            }))
            ->when($kategori !== '', fn (Builder $query) => $query->where('kategori', $kategori))
            ->orderByDesc('published_at')
            ->paginate(12)
            ->withQueryString();

        $categories = Publikasi::query()->published()->whereIn('jenis', [Publikasi::JENIS_FOTO, Publikasi::JENIS_VIDEO])
            ->whereNotNull('kategori')->distinct()->orderBy('kategori')->pluck('kategori');

        return view('public.dokumentasi.index', compact('dokumentasi', 'search', 'kategori', 'categories', 'jenisInput'));
    }

    public function dokumentasiShow(Publikasi $publikasi): View
    {
        abort_unless($publikasi->status === Publikasi::STATUS_PUBLISHED && in_array($publikasi->jenis, [Publikasi::JENIS_FOTO, Publikasi::JENIS_VIDEO], true) && $publikasi->published_at, 404);

        return view('public.dokumentasi.show', compact('publikasi'));
    }

    public function media(Publikasi $publikasi, string $variant = 'media'): BinaryFileResponse
    {
        abort_unless($publikasi->status === Publikasi::STATUS_PUBLISHED && $publikasi->published_at, 404);
        abort_unless(in_array($variant, ['media', 'thumbnail'], true), 404);

        $path = $variant === 'thumbnail' ? $publikasi->thumbnail_path : $publikasi->media_path;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path), [
            'Content-Type' => Storage::disk('local')->mimeType($path) ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /** @return array{string, string} */
    private function filters(Request $request): array
    {
        $searchInput = $request->query('q', '');
        $kategoriInput = $request->query('kategori', '');
        abort_unless(is_string($searchInput) && is_string($kategoriInput), 422);

        return [
            mb_substr(trim($searchInput), 0, 100),
            mb_substr(trim($kategoriInput), 0, 80),
        ];
    }
}
