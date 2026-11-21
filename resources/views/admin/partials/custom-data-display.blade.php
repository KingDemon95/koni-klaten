@php
    $displayValues = $profile->custom_data ?? [];
    $displayFields = ($customFields ?? collect())->filter(fn ($field) => array_key_exists($field->field_key, $displayValues));
@endphp

@if($displayFields->isNotEmpty())
    <section class="mt-6 rounded-xl border border-gray-200 bg-white p-6">
        <h3 class="mb-5 border-b border-gray-100 pb-4 font-black">Informasi Tambahan</h3>
        <dl class="grid gap-5 sm:grid-cols-2">
            @foreach($displayFields as $field)
                <div>
                    <dt class="text-xs font-semibold text-gray-500">{{ $field->label }}</dt>
                    <dd class="mt-1 whitespace-pre-line break-words text-sm">{{ is_bool($displayValues[$field->field_key]) ? ($displayValues[$field->field_key] ? 'Ya' : 'Tidak') : ($displayValues[$field->field_key] ?? '-') }}</dd>
                </div>
            @endforeach
        </dl>
    </section>
@endif
