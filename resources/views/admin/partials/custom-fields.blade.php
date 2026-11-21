@if(isset($customFields) && $customFields->isNotEmpty())
    @php($customValues = old('custom_data', $values ?? []))
    <section class="rounded-xl border border-indigo-100 bg-indigo-50/40 p-5 sm:p-6">
        <div class="mb-5 border-b border-indigo-100 pb-4">
            <h3 class="font-black text-gray-900">Informasi Tambahan</h3>
            <p class="mt-1 text-xs text-gray-500">Field ini mengikuti pengaturan Master Data KONI.</p>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            @foreach($customFields as $field)
                @php($fieldValue = data_get($customValues, $field->field_key))
                <div class="{{ $field->type === 'textarea' ? 'sm:col-span-2' : '' }}">
                    <label for="custom_{{ $field->field_key }}" class="mb-2 block text-sm font-semibold text-gray-800">
                        {{ $field->label }}
                        @if($field->is_required)<span class="text-red-600">*</span>@endif
                    </label>

                    @if($field->type === 'textarea')
                        <textarea id="custom_{{ $field->field_key }}" name="custom_data[{{ $field->field_key }}]" rows="3" @required($field->is_required) maxlength="5000" class="w-full rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm">{{ $fieldValue }}</textarea>
                    @elseif($field->type === 'select')
                        <select id="custom_{{ $field->field_key }}" name="custom_data[{{ $field->field_key }}]" @required($field->is_required) class="w-full rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm">
                            <option value="">Pilih {{ strtolower($field->label) }}</option>
                            @foreach(($field->options ?? []) as $option)
                                <option value="{{ $option }}" @selected((string) $fieldValue === (string) $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                    @elseif($field->type === 'checkbox')
                        <div class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm">
                            <input id="custom_{{ $field->field_key }}" type="checkbox" name="custom_data[{{ $field->field_key }}]" value="1" @checked((bool) $fieldValue) @required($field->is_required) class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                            <span class="text-gray-600">Centang jika berlaku</span>
                        </div>
                    @else
                        <input id="custom_{{ $field->field_key }}" type="{{ in_array($field->type, ['text', 'number', 'date', 'email', 'url'], true) ? $field->type : 'text' }}" name="custom_data[{{ $field->field_key }}]" value="{{ $fieldValue }}" @required($field->is_required) @if($field->type === 'text') maxlength="255" @endif class="w-full rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm">
                    @endif

                    @error('custom_data.'.$field->field_key)
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach
        </div>
    </section>
@endif
