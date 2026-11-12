<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\CabangOlahraga;
use App\Models\Publikasi;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = [];

        $addUrl = static function (
            string $location,
            mixed $lastModified = null,
            string $changeFrequency = 'weekly',
            string $priority = '0.6',
        ) use (&$urls): void {
            $urls[] = [
                'location' => $location,
                'last_modified' => $lastModified?->toAtomString(),
                'change_frequency' => $changeFrequency,
                'priority' => $priority,
            ];
        };

        $addUrl(route('public.home'), null, 'daily', '1.0');
        $addUrl(route('public.cabor.index'), null, 'weekly', '0.8');
        $addUrl(route('public.struktur.index'), null, 'monthly', '0.6');
        $addUrl(route('public.statistik.index'), null, 'weekly', '0.7');
        $addUrl(route('public.agenda.index'), null, 'daily', '0.8');
        $addUrl(route('public.berita.index'), null, 'daily', '0.8');
        $addUrl(route('public.dokumentasi.index'), null, 'weekly', '0.6');

        CabangOlahraga::query()
            ->where('status', 'aktif')
            ->orderBy('id')
            ->limit(1000)
            ->get(['id', 'updated_at'])
            ->each(fn (CabangOlahraga $cabor) => $addUrl(
                route('public.cabor.show', $cabor),
                $cabor->updated_at,
                'weekly',
                '0.7',
            ));

        Agenda::query()
            ->published()
            ->orderByDesc('updated_at')
            ->limit(1000)
            ->get(['id', 'slug', 'updated_at'])
            ->each(fn (Agenda $agenda) => $addUrl(
                route('public.agenda.show', $agenda->slug),
                $agenda->updated_at,
                'weekly',
                '0.7',
            ));

        Publikasi::query()
            ->published()
            ->where('jenis', Publikasi::JENIS_BERITA)
            ->orderByDesc('published_at')
            ->limit(1000)
            ->get(['id', 'slug', 'updated_at'])
            ->each(fn (Publikasi $publikasi) => $addUrl(
                route('public.berita.show', $publikasi->slug),
                $publikasi->updated_at,
                'monthly',
                '0.7',
            ));

        Publikasi::query()
            ->published()
            ->whereIn('jenis', [Publikasi::JENIS_FOTO, Publikasi::JENIS_VIDEO])
            ->orderByDesc('published_at')
            ->limit(1000)
            ->get(['id', 'slug', 'updated_at'])
            ->each(fn (Publikasi $publikasi) => $addUrl(
                route('public.dokumentasi.show', $publikasi->slug),
                $publikasi->updated_at,
                'monthly',
                '0.6',
            ));

        return response()
            ->view('sitemap.index', compact('urls'))
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
