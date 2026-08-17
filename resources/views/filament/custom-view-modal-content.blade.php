<div style="padding:2rem; font-size:1.1rem;">
    <h3 style="margin-bottom:1.5rem;">Soru & Cevap</h3>
    @php
        $state = $record->data;
        if (is_string($state)) {
            $decoded = json_decode($state, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $state = $decoded;
            }
        }
        $fields = $record->form ? $record->form->fields()->orderBy('position')->get() : collect();
        $hiddenFields = array_diff_key($state, $fields->pluck('name')->flip()->toArray());
    @endphp

    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr>
                <th style="text-align:left; padding:8px; border-bottom:1px solid #eee; width:40%;">Soru</th>
                <th style="text-align:left; padding:8px; border-bottom:1px solid #eee;">Cevap</th>
            </tr>
        </thead>
        <tbody>
            @if (!empty($hiddenFields))

        <tbody>
            @foreach ($hiddenFields as $key => $value)
                <tr>
                    <td style="padding:8px; border-bottom:1px solid #f5f5f5;">
                        <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}</strong>
                    </td>
                    <td style="padding:8px; border-bottom:1px solid #f5f5f5;">
                        {{ $value }}
                    </td>
                </tr>
            @endforeach
            @endif
            @if ($fields->count())
                @foreach ($fields as $field)
                    @php
                        $key = $field->name;
                        $label = $field->label;
                        $value = $state[$key] ?? null;
                        $isFile = false;
                        $isImage = false;
                        if (is_array($value)) {
                            $value = implode(', ', array_filter($value));
                        } elseif (is_bool($value)) {
                            $value = $value ? 'Evet' : 'Hayır';
                        }
                        if (is_string($value) && ($field->type === 'file' || $field->type === 'image')) {
                            $isFile = true;
                            $isImage = $field->type === 'image' || preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $value);
                        }
                    @endphp
                    @if ($value !== null && $value !== '')
                        <tr>
                            <td style="padding:8px; border-bottom:1px solid #f5f5f5;">
                                <strong>{{ $label }}</strong>
                            </td>
                            <td style="padding:8px; border-bottom:1px solid #f5f5f5;">
                                @if ($isFile)
                                    @if ($isImage)
                                        <a href="{{ asset('storage/' . ltrim($value, '/')) }}" target="_blank">
                                            <img src="{{ asset('storage/' . ltrim($value, '/')) }}"
                                                alt="{{ $label }}"
                                                style="max-width:120px;max-height:120px;border-radius:4px;border:1px solid #eee;">
                                        </a>
                                    @else
                                        <a href="{{ asset('storage/' . ltrim($value, '/')) }}" target="_blank">Dosyayı
                                            Görüntüle / İndir</a>
                                    @endif
                                @else
                                    {{ $value }}
                                @endif
                            </td>
                        </tr>
                    @endif
                @endforeach
            @elseif (is_array($state))
                @foreach ($state as $key => $value)
                    @php
                        $isFile = false;
                        $isImage = false;
                        if (is_array($value)) {
                            $value = implode(', ', array_filter($value));
                        } elseif (is_bool($value)) {
                            $value = $value ? 'Evet' : 'Hayır';
                        }
                        if (
                            is_string($value) &&
                            (preg_match('/\.(jpg|jpeg|png|gif|webp|pdf|docx?|xlsx?|pptx?)$/i', $value) ||
                                str_starts_with($value, 'uploads/') ||
                                str_starts_with($value, 'storage/'))
                        ) {
                            $isFile = true;
                            $isImage = preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $value);
                        }
                    @endphp
                    @if ($value !== null && $value !== '')
                        <tr>
                            <td style="padding:8px; border-bottom:1px solid #f5f5f5;">
                                <strong>{{ ucfirst($key) }}</strong>
                            </td>
                            <td style="padding:8px; border-bottom:1px solid #f5f5f5;">
                                @if ($isFile)
                                    @if ($isImage)
                                        <a href="{{ asset('storage/' . ltrim($value, '/')) }}" target="_blank">
                                            <img src="{{ asset('storage/' . ltrim($value, '/')) }}"
                                                alt="{{ $key }}"
                                                style="max-width:120px;max-height:120px;border-radius:4px;border:1px solid #eee;">
                                        </a>
                                    @else
                                        <a href="{{ asset('storage/' . ltrim($value, '/')) }}" target="_blank">Dosyayı
                                            Görüntüle / İndir</a>
                                    @endif
                                @else
                                    {{ $value }}
                                @endif
                            </td>
                        </tr>
                    @endif
                @endforeach
            @endif
        </tbody>
    </table>
</div>
