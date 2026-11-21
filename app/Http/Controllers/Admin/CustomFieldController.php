<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomField;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CustomFieldController extends Controller
{
    public function index(): View
    {
        return view('admin.master-data.index', [
            'fields' => CustomField::query()
                ->orderBy('entity_type')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'entity_type' => ['required', 'in:atlet,pelatih'],
            'label' => ['required', 'string', 'max:120'],
            'type' => ['required', 'in:text,textarea,number,date,email,url,select,checkbox'],
            'options_text' => ['nullable', 'string', 'max:4000'],
            'is_required' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ]);

        $options = $this->parseOptions($validated['type'], $validated['options_text'] ?? null);
        $baseKey = substr(Str::slug($validated['label'], '_'), 0, 60);
        if ($baseKey === '') {
            throw ValidationException::withMessages([
                'label' => 'Label harus memuat huruf atau angka agar sistem dapat membuat kode field.',
            ]);
        }

        $fieldKey = $baseKey;
        $suffix = 2;
        while (CustomField::query()
            ->where('entity_type', $validated['entity_type'])
            ->where('field_key', $fieldKey)
            ->exists()) {
            $fieldKey = $baseKey.'_'.$suffix;
            $suffix++;
        }

        CustomField::query()->create([
            'entity_type' => $validated['entity_type'],
            'label' => $validated['label'],
            'field_key' => $fieldKey,
            'type' => $validated['type'],
            'options' => $options,
            'is_required' => $request->boolean('is_required'),
            'is_active' => true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.master-data.index')
            ->with('success', 'Field tambahan berhasil dibuat.');
    }

    public function update(Request $request, CustomField $customField): RedirectResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:120'],
            'type' => ['required', 'in:text,textarea,number,date,email,url,select,checkbox'],
            'options_text' => ['nullable', 'string', 'max:4000'],
            'is_required' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ]);

        $options = $this->parseOptions($validated['type'], $validated['options_text'] ?? null);

        $customField->update([
            'label' => $validated['label'],
            'type' => $validated['type'],
            'options' => $options,
            'is_required' => $request->boolean('is_required'),
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.master-data.index')
            ->with('success', 'Pengaturan field berhasil diperbarui.');
    }

    public function destroy(CustomField $customField): RedirectResponse
    {
        $customField->update(['is_active' => false]);

        return redirect()->route('admin.master-data.index')
            ->with('success', 'Field dinonaktifkan. Nilai lama tetap tersimpan pada data atlet atau pelatih.');
    }

    private function parseOptions(string $type, ?string $raw): ?array
    {
        if ($type !== 'select') {
            return null;
        }

        $options = collect(preg_split('/\r\n|\r|\n/', (string) $raw) ?: [])
            ->map(fn (string $option): string => trim($option))
            ->filter(fn (string $option): bool => $option !== '')
            ->unique()
            ->values();

        if ($options->count() < 2 || $options->count() > 50) {
            throw ValidationException::withMessages([
                'options_text' => 'Field pilihan harus memiliki 2 sampai 50 opsi yang berbeda, satu opsi per baris.',
            ]);
        }

        return $options->all();
    }
}
