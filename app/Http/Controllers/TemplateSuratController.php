<?php

namespace App\Http\Controllers;

use App\Models\TemplateSurat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TemplateSuratController extends Controller
{
    public function index(Request $request): View
    {
        $prefix = $this->routePrefix($request);
        $isManager = in_array($prefix, ['admin', 'staff'], true);
        $query = TemplateSurat::query()->with('uploader')->latest();

        if (!$isManager) {
            $query->where('is_active', true);
        }

        $items = $query->paginate(12);

        return view('templates.index', [
            'layout' => match ($prefix) {
                'admin' => 'layouts.admin',
                'staff' => 'layouts.staff',
                'cabor' => 'layouts.cabor',
                'atlet' => 'layouts.atlet',
                'pelatih' => 'layouts.pelatih',
            },
            'routePrefix' => $prefix,
            'isManager' => $isManager,
            'items' => $items,
            'cabor' => $prefix === 'cabor' ? $request->user()->cabangOlahraga : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureManager($request);
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:180'],
            'deskripsi' => ['nullable', 'string', 'max:3000'],
            'file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);

        $file = $request->file('file');
        $path = $file->store('template-surat', 'local');

        TemplateSurat::create([
            'judul' => $validated['judul'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'file_path' => $path,
            'file_original_name' => $file->getClientOriginalName(),
            'uploaded_by' => $request->user()->id,
            'is_active' => true,
        ]);

        return redirect()->route($this->routePrefix($request).'.templates.index')->with('success', 'Template surat berhasil diunggah.');
    }

    public function update(Request $request, TemplateSurat $template): RedirectResponse
    {
        $this->ensureManager($request);
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:180'],
            'deskripsi' => ['nullable', 'string', 'max:3000'],
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);

        $data = [
            'judul' => $validated['judul'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $data['file_path'] = $file->store('template-surat', 'local');
            $data['file_original_name'] = $file->getClientOriginalName();
            Storage::disk('local')->delete($template->file_path);
        }

        $template->update($data);

        return redirect()->route($this->routePrefix($request).'.templates.index')->with('success', 'Template surat berhasil diperbarui.');
    }

    public function destroy(Request $request, TemplateSurat $template): RedirectResponse
    {
        $this->ensureManager($request);
        Storage::disk('local')->delete($template->file_path);
        $template->delete();

        return redirect()->route($this->routePrefix($request).'.templates.index')->with('success', 'Template surat berhasil dihapus.');
    }

    public function download(Request $request, TemplateSurat $template): StreamedResponse
    {
        $prefix = $this->routePrefix($request);
        if (!in_array($prefix, ['admin', 'staff'], true)) {
            abort_unless($template->is_active, 404);
        }

        abort_unless(Storage::disk('local')->exists($template->file_path), 404);

        return Storage::disk('local')->download($template->file_path, $template->file_original_name);
    }

    private function ensureManager(Request $request): void
    {
        abort_unless(in_array($this->routePrefix($request), ['admin', 'staff'], true), 403);
    }

    private function routePrefix(Request $request): string
    {
        $name = $request->route()?->getName() ?? '';

        foreach (['admin', 'staff', 'cabor', 'atlet', 'pelatih'] as $prefix) {
            if (str_starts_with($name, $prefix.'.templates.')) {
                return $prefix;
            }
        }

        abort(403);
    }
}
