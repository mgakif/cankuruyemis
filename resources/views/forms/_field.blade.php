@if($field->type !== 'description')
    <label for="{{ $field->name }}" class="block text-sm font-medium text-text-dark dark:text-gray-300 mb-2 leading-normal">
        {{ $field->label }} @if ($field->required)
            <span class="text-red-500 dark:text-red-400">*</span>
        @endif
    </label>
@endif

@switch($field->type)
    @case('text')
    @case('email')
    @case('tel')
    @case('number')
    @case('date')
        <input 
            type="{{ $field->type }}" 
            class="form-input flex w-full rounded-lg border border-[#e8dbce] dark:border-gray-700 bg-[#fcfaf8] dark:bg-[#2c241b] text-text-dark dark:text-white focus:border-primary focus:ring-1 focus:ring-primary h-12 px-4 placeholder:text-[#9c7349] dark:placeholder:text-gray-500 text-base" 
            name="{{ $field->name }}" 
            id="{{ $field->name }}"
            value="{{ old($field->name, $field->default_value) }}" 
            placeholder="{{ $field->placeholder }}"
            @if ($field->required) required @endif>
    @break

    @case('textarea')
        <textarea 
            class="form-textarea flex w-full rounded-lg border border-[#e8dbce] dark:border-gray-700 bg-[#fcfaf8] dark:bg-[#2c241b] text-text-dark dark:text-white focus:border-primary focus:ring-1 focus:ring-primary min-h-[160px] p-4 placeholder:text-[#9c7349] dark:placeholder:text-gray-500 text-base resize-y" 
            name="{{ $field->name }}" 
            id="{{ $field->name }}"
            placeholder="{{ $field->placeholder }}" 
            rows="5"
            @if ($field->required) required @endif>{{ old($field->name, $field->default_value) }}</textarea>
    @break

    @case('select')
        <div class="relative">
            <select 
                class="w-full appearance-none px-5 py-4 pr-12 rounded-xl bg-background-light dark:bg-gray-700 border border-[#e8dbce] dark:border-gray-600 text-text-dark dark:text-text-light text-[15px] font-medium focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all cursor-pointer" 
                name="{{ $field->name }}" 
                id="{{ $field->name }}"
                @if ($field->required) required @endif>
                <option value="">{{ $field->placeholder ?? $field->name . ' seçiniz' }}</option>
                @if(isset($field->options['key']))
                    @foreach ($field->options['key'] as $option)
                        <option value="{{ $option }}" {{ old($field->name) == $option ? 'selected' : '' }}>{{ $option }}</option>
                    @endforeach
                @endif
            </select>
            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400 dark:text-gray-500">expand_more</span>
        </div>
    @break

    @case('radio')
        <div class="space-y-3">
            @if(isset($field->options['key']))
                @foreach ($field->options['key'] as $option)
                    <div class="flex items-center">
                        <input 
                            class="h-5 w-5 text-primary border-gray-200 dark:border-gray-600 rounded-full bg-background-light dark:bg-gray-700 focus:ring-primary focus:ring-2" 
                            type="radio" 
                            name="{{ $field->name }}" 
                            value="{{ $option }}"
                            id="{{ $field->name }}_{{ $loop->index }}"
                            {{ old($field->name) == $option ? 'checked' : '' }}
                            @if ($field->required) required @endif>
                        <label class="ml-3 text-[15px] font-medium text-gray-700 dark:text-gray-200 cursor-pointer" for="{{ $field->name }}_{{ $loop->index }}">
                            {{ $option }}
                        </label>
                    </div>
                @endforeach
            @endif
        </div>
    @break

    @case('checkbox')
        <div class="space-y-3">
            @if(isset($field->options['key']))
                @foreach ($field->options['key'] as $option)
                    <div class="flex items-center">
                        <input 
                            class="h-5 w-5 text-primary border-gray-200 dark:border-gray-600 rounded bg-background-light dark:bg-gray-700 focus:ring-primary focus:ring-2" 
                            type="checkbox" 
                            name="{{ $field->name }}[]" 
                            value="{{ $option }}"
                            id="{{ $field->name }}_{{ $loop->index }}"
                            {{ in_array($option, old($field->name, [])) ? 'checked' : '' }}
                            @if ($field->required) required @endif>
                        <label class="ml-3 text-[15px] font-medium text-gray-700 dark:text-gray-200 cursor-pointer" for="{{ $field->name }}_{{ $loop->index }}">
                            {{ $option }}
                        </label>
                    </div>
                @endforeach
            @endif
        </div>
    @break

    @case('file')
        <input 
            type="file" 
            name="{{ $field->name }}" 
            class="w-full px-5 py-4 rounded-xl bg-background-light dark:bg-gray-700 border border-[#e8dbce] dark:border-gray-600 text-text-dark dark:text-text-light text-[15px] font-medium focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:opacity-90 cursor-pointer"
            accept="{{ isset($field->options['file_types']) ? '.' . implode(',.', $field->options['file_types']) : '' }}" 
            @if(isset($field->options['multiple']) && $field->options['multiple']) multiple @endif
            @if ($field->required) required @endif>
    @break

    @case('image')
        @php
            $imageAccept = 'image/*';
            if (isset($field->options['file_types']) && is_array($field->options['file_types'])) {
                $imageTypes = [];
                foreach ($field->options['file_types'] as $type) {
                    $type = strtolower(trim($type));
                    // JPG için jpeg MIME type kullan
                    if ($type === 'jpg' || $type === 'jpeg') {
                        $imageTypes[] = 'image/jpeg';
                    } elseif ($type === 'png') {
                        $imageTypes[] = 'image/png';
                    } elseif ($type === 'gif') {
                        $imageTypes[] = 'image/gif';
                    } elseif ($type === 'webp') {
                        $imageTypes[] = 'image/webp';
                    } else {
                        $imageTypes[] = 'image/' . $type;
                    }
                }
                if (!empty($imageTypes)) {
                    $imageAccept = implode(',', $imageTypes);
                }
            }
        @endphp
        <input 
            type="file" 
            name="{{ $field->name }}" 
            class="w-full px-5 py-4 rounded-xl bg-background-light dark:bg-gray-700 border border-[#e8dbce] dark:border-gray-600 text-text-dark dark:text-text-light text-[15px] font-medium focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:opacity-90 cursor-pointer"
            accept="{{ $imageAccept }}" 
            @if(isset($field->options['multiple']) && $field->options['multiple']) multiple @endif
            @if ($field->required) required @endif>
    @break

    @case('il')
        <div class="relative">
            <select 
                @if ($field->required) required @endif 
                class="w-full appearance-none px-5 py-4 pr-12 rounded-xl bg-background-light dark:bg-gray-700 border border-[#e8dbce] dark:border-gray-600 text-text-dark dark:text-text-light text-[15px] font-medium focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all cursor-pointer" 
                data-il-select
                name="{{ $field->name }}">
                <option value="">İl Seç</option>
            </select>
            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400 dark:text-gray-500">expand_more</span>
        </div>
    @break

    @case('ilce')
        <div class="relative">
            <select 
                @if ($field->required) required @endif 
                class="w-full appearance-none px-5 py-4 pr-12 rounded-xl bg-background-light dark:bg-gray-700 border border-[#e8dbce] dark:border-gray-600 text-text-dark dark:text-text-light text-[15px] font-medium focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all cursor-pointer" 
                data-ilce-select
                name="{{ $field->name }}">
                <option value="">İlçe Seç</option>
            </select>
            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400 dark:text-gray-500">expand_more</span>
        </div>
    @break

    @case('description')
        <div class="prose prose-sm max-w-none dark:prose-invert prose-headings:text-text-dark dark:prose-headings:text-text-light prose-p:text-text-dark dark:prose-p:text-text-light prose-a:text-primary hover:prose-a:text-primary/80 mb-4">
            {!! nl2br($field->default_value ?: $field->placeholder ?: $field->label) !!}
        </div>
    @break

@endswitch
