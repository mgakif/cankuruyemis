@props(['slug', 'class' => '', 'hiddenFields' => []])

@php
    $form = \App\Models\Form::where('slug', $slug)->with('fields')->first();
    
    // Token parametresini al (eğer varsa)
    $formToken = request()->query('form');
    $submitUrl = route('form.submit', $form->slug);
    if ($formToken) {
        $submitUrl .= '?form=' . urlencode($formToken);
    }
@endphp

@if(!$form)
    <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
        <p class="text-yellow-800 dark:text-yellow-200 text-sm">
            Form bulunamadı: <strong>{{ $slug }}</strong>. Lütfen Filament admin panelinde bu slug ile bir form oluşturun.
        </p>
    </div>
@elseif($form->fields->isEmpty())
    <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
        <p class="text-yellow-800 dark:text-yellow-200 text-sm">
            Form alanları bulunamadı. Lütfen Filament admin panelinde "{{ $form->name }}" formuna alan ekleyin.
        </p>
    </div>
@else
<div class="{{ $class }}">
    @if($form->description)
        <div class="mb-6 prose prose-lg max-w-none dark:prose-invert prose-headings:text-text-dark dark:prose-headings:text-text-light prose-p:text-text-dark dark:prose-p:text-text-light prose-a:text-primary hover:prose-a:text-primary/80">
            {!! $form->description !!}
        </div>
    @endif
    
    <form
        data-form-widget
        data-submit-url="{{ $submitUrl }}"
        data-success-message="{{ $form->success_message ?? 'Form başarıyla gönderildi.' }}"
        @if($form->recaptcha && config('services.recaptcha.site_key'))
        data-recaptcha-enabled="true"
        @endif
        action="{{ $submitUrl }}"
        method="POST"
        enctype="multipart/form-data"
        class="space-y-6"
    >
        @csrf

        @foreach ($hiddenFields ?? [] as $name => $value)
            @if($value !== null)
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endif
        @endforeach

        @php
            $grouped = $form->fields->groupBy('step');
        @endphp

        <div id="form-steps">
            @foreach ($grouped as $step => $fields)
                <div class="step" data-step="{{ $step }}"
                    style="{{ $loop->first ? '' : 'display:none;' }}">
                    @if($grouped->count() > 1)
                        <div class="mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                            <h4 class="text-xl font-display font-bold text-text-light dark:text-text-dark">
                                {{ $fields->first()->group ?? 'Adım ' . $step }}
                            </h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Adım {{ $step }} / {{ $grouped->count() }}</p>
                        </div>
                    @endif
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach ($fields as $field)
                            <div class="{{ ($field->column_width ?? 12) == 6 ? 'md:col-span-1' : 'md:col-span-2' }}">
                                @include('forms._field', ['field' => $field])
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        @if($form->recaptcha && config('services.recaptcha.site_key'))
            <div class="mb-6">
                <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
                <div id="recaptcha-error" class="error-message" style="display: none;"></div>
            </div>
        @endif

        <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700 mt-8">
            <button
                type="button"
                data-form-prev
                class="px-6 py-3 bg-background-light dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 text-text-light dark:text-text-dark font-semibold rounded-xl transition-all"
                style="display: none;"
            >
                <span class="material-symbols-outlined align-middle mr-2">arrow_back</span>
                Geri
            </button>
            @if($grouped->count() > 1)
                <button
                    type="button"
                    data-form-next
                    class="ml-auto px-8 py-3 bg-primary hover:bg-primary/90 text-white font-bold text-lg rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1"
                >
                    <span data-form-next-label>İleri</span>
                    <span data-form-next-icon class="material-symbols-outlined align-middle ml-2">arrow_forward</span>
                </button>
            @else
                <button
                    type="submit"
                    data-form-submit
                    class="ml-auto px-8 py-3 bg-primary hover:bg-primary/90 text-white font-bold text-lg rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1"
                >
                    GÖNDER
                </button>
            @endif
        </div>
    </form>
</div>

@push('scripts')
@if($form->recaptcha && config('services.recaptcha.site_key'))
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endif
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const formWidgets = document.querySelectorAll('[data-form-widget]');
        
        formWidgets.forEach(formWidget => {
            const form = formWidget.closest('form');
            const steps = form.querySelectorAll('.step');
            const totalSteps = steps.length;
            let currentStep = 1;
            
            const prevBtn = form.querySelector('[data-form-prev]');
            const nextBtn = form.querySelector('[data-form-next]');
            const submitBtn = form.querySelector('[data-form-submit]');
            const nextLabel = form.querySelector('[data-form-next-label]');
            const nextIcon = form.querySelector('[data-form-next-icon]');
            
            const showStep = (step) => {
                steps.forEach(s => s.style.display = 'none');
                form.querySelector(`.step[data-step="${step}"]`).style.display = '';
                
                if (prevBtn) {
                    prevBtn.style.display = step > 1 ? 'inline-flex' : 'none';
                }
                
                if (nextBtn) {
                    if (step === totalSteps) {
                        nextLabel.textContent = 'Gönder';
                        nextIcon.style.display = 'none';
                    } else {
                        nextLabel.textContent = 'İleri';
                        nextIcon.style.display = 'inline';
                    }
                }
            };
            
            if (prevBtn) {
                prevBtn.addEventListener('click', () => {
                    if (currentStep > 1) {
                        currentStep--;
                        showStep(currentStep);
                    }
                });
            }
            
            if (nextBtn) {
                nextBtn.addEventListener('click', () => {
                    if (currentStep < totalSteps) {
                        currentStep++;
                        showStep(currentStep);
                    } else {
                        form.dispatchEvent(new Event('submit'));
                    }
                });
            }
            
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                // reCAPTCHA kontrolü
                const recaptchaEnabled = formWidget.hasAttribute('data-recaptcha-enabled');
                if (recaptchaEnabled) {
                    const recaptchaError = form.querySelector('#recaptcha-error');
                    let recaptchaResponse = '';
                    
                    // grecaptcha global değişkeni varsa kullan (Google reCAPTCHA v2)
                    if (typeof grecaptcha !== 'undefined') {
                        try {
                            recaptchaResponse = grecaptcha.getResponse();
                            console.log('grecaptcha.getResponse() result:', recaptchaResponse);
                            console.log('Response length:', recaptchaResponse ? recaptchaResponse.length : 0);
                        } catch (e) {
                            console.error('reCAPTCHA getResponse error:', e);
                        }
                    } else {
                        console.error('grecaptcha is not defined!');
                    }
                    
                    // Response yoksa form'dan input'u kontrol et
                    if (!recaptchaResponse) {
                        const recaptchaInput = form.querySelector('[name="g-recaptcha-response"]');
                        console.log('Checking input field:', recaptchaInput);
                        if (recaptchaInput) {
                            recaptchaResponse = recaptchaInput.value;
                            console.log('Input field value:', recaptchaResponse);
                        }
                    }
                    
                    if (!recaptchaResponse) {
                        console.error('No reCAPTCHA response found!');
                        if (recaptchaError) {
                            recaptchaError.textContent = 'Lütfen robot olmadığınızı doğrulayın.';
                            recaptchaError.style.display = 'block';
                        }
                        return;
                    }
                    
                    console.log('Final reCAPTCHA response to be sent:', recaptchaResponse);
                    
                    if (recaptchaError) {
                        recaptchaError.style.display = 'none';
                    }
                }
                
                const formData = new FormData(form);
                
                // reCAPTCHA response'unu manuel olarak ekle veya güncelle
                if (recaptchaEnabled && typeof grecaptcha !== 'undefined') {
                    try {
                        const recaptchaResponse = grecaptcha.getResponse();
                        
                        // Debug için
                        console.log('reCAPTCHA Response:', recaptchaResponse);
                        console.log('FormData has g-recaptcha-response:', formData.has('g-recaptcha-response'));
                        
                        if (recaptchaResponse) {
                            // Varsa güncelle, yoksa ekle
                            if (formData.has('g-recaptcha-response')) {
                                formData.set('g-recaptcha-response', recaptchaResponse);
                            } else {
                                formData.append('g-recaptcha-response', recaptchaResponse);
                            }
                        }
                    } catch (e) {
                        console.error('reCAPTCHA FormData append error:', e);
                    }
                }
                
                // Debug: FormData içeriğini göster (sadece geliştirme için)
                console.log('FormData contents:');
                for (let pair of formData.entries()) {
                    console.log(pair[0] + ': ' + pair[1]);
                }
                const errorElements = form.querySelectorAll('.error-message');
                errorElements.forEach(el => el.remove());
                
                const activeBtn = submitBtn || nextBtn;
                const originalHTML = activeBtn.innerHTML;
                activeBtn.disabled = true;
                activeBtn.innerHTML = '<span class="material-symbols-outlined align-middle mr-2 animate-spin">sync</span> Gönderiliyor...';
                
                try {
                    const response = await fetch(formWidget.getAttribute('data-submit-url'), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': form.querySelector('input[name=_token]').value,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (!response.ok) {
                        if (data.errors) {
                            for (let field in data.errors) {
                                const errorMessages = data.errors[field].join('<br>');
                                const inputElement = form.querySelector(`[name="${field}"]`);
                                if (inputElement) {
                                    const errorDiv = document.createElement('div');
                                    errorDiv.classList.add('error-message');
                                    errorDiv.innerHTML = errorMessages;
                                    inputElement.parentElement.appendChild(errorDiv);
                                }
                            }
                        }
                        throw new Error(data.message || 'Form gönderilirken bir hata oluştu.');
                    }
                    
                    // SweetAlert2 varsa kullan, yoksa alert
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Başarılı!',
                            text: formWidget.getAttribute('data-success-message'),
                            confirmButtonColor: '#f27f0d',
                        });
                    } else {
                        alert(formWidget.getAttribute('data-success-message'));
                    }
                    form.reset();
                    
                    // reCAPTCHA'yı reset et
                    if (recaptchaEnabled && typeof grecaptcha !== 'undefined') {
                        try {
                            grecaptcha.reset();
                        } catch (e) {
                            console.error('reCAPTCHA reset error:', e);
                        }
                    }
                    
                    currentStep = 1;
                    showStep(currentStep);
                } catch (error) {
                    // SweetAlert2 varsa kullan, yoksa alert
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Hata!',
                            html: error.message,
                            confirmButtonColor: '#ef4444',
                        });
                    } else {
                        alert(error.message);
                    }
                } finally {
                    activeBtn.disabled = false;
                    activeBtn.innerHTML = originalHTML;
                }
            });
            
            showStep(currentStep);
        });

        // File ve Image alanları için preview ve validation
        document.querySelectorAll('input[type="file"]').forEach(fileInput => {
            const fieldName = fileInput.name;
            const isImage = fileInput.accept && fileInput.accept.includes('image');
            const maxSize = 10 * 1024 * 1024; // 10MB default
            const allowedTypes = fileInput.accept ? fileInput.accept.split(',').map(t => t.trim()) : [];
            
            // Preview container oluştur
            const previewContainer = document.createElement('div');
            previewContainer.className = 'mt-3 grid grid-cols-2 md:grid-cols-4 gap-4';
            previewContainer.id = `${fieldName}_preview`;
            fileInput.parentElement.appendChild(previewContainer);
            
            // Dosya seçildiğinde
            fileInput.addEventListener('change', function(e) {
                const files = Array.from(e.target.files);
                previewContainer.innerHTML = '';
                
                files.forEach((file, index) => {
                    // Dosya boyutu kontrolü
                    if (file.size > maxSize) {
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'error-message col-span-full';
                        errorDiv.textContent = `${file.name} dosyası çok büyük. Maksimum boyut: ${(maxSize / 1024 / 1024).toFixed(0)}MB`;
                        previewContainer.appendChild(errorDiv);
                        return;
                    }
                    
                    // Dosya tipi kontrolü
                    if (allowedTypes.length > 0) {
                        const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
                        const mimeType = file.type;
                        
                        // JPG/JPEG için özel kontrol
                        const normalizedExtension = fileExtension === '.jpg' ? '.jpeg' : fileExtension;
                        const normalizedMime = mimeType === 'image/jpeg' ? 'image/jpeg' : mimeType;
                        
                        const isValidType = allowedTypes.some(type => {
                            // image/* kontrolü
                            if (type === 'image/*' && mimeType.startsWith('image/')) {
                                return true;
                            }
                            // Extension kontrolü (.jpg, .jpeg, .png, vb.)
                            if (type.includes(normalizedExtension) || type.includes(fileExtension)) {
                                return true;
                            }
                            // MIME type kontrolü
                            if (type === normalizedMime || type === mimeType) {
                                return true;
                            }
                            // image/jpeg için jpg extension kontrolü
                            if (type === 'image/jpeg' && (fileExtension === '.jpg' || fileExtension === '.jpeg')) {
                                return true;
                            }
                            return false;
                        });
                        
                        if (!isValidType) {
                            const errorDiv = document.createElement('div');
                            errorDiv.className = 'error-message col-span-full';
                            errorDiv.textContent = `${file.name} dosya tipi desteklenmiyor. İzin verilen tipler: ${allowedTypes.join(', ')}`;
                            previewContainer.appendChild(errorDiv);
                            return;
                        }
                    }
                    
                    // Preview oluştur
                    const previewItem = document.createElement('div');
                    previewItem.className = 'relative group';
                    
                    if (isImage && file.type.startsWith('image/')) {
                        // Image preview
                        const img = document.createElement('img');
                        img.src = URL.createObjectURL(file);
                        img.className = 'w-full h-32 object-cover rounded-lg border border-gray-200 dark:border-gray-700';
                        img.onload = () => URL.revokeObjectURL(img.src);
                        
                        const removeBtn = document.createElement('button');
                        removeBtn.type = 'button';
                        removeBtn.className = 'absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity';
                        removeBtn.innerHTML = '<span class="material-symbols-outlined text-sm">close</span>';
                        removeBtn.onclick = () => {
                            const dt = new DataTransfer();
                            Array.from(fileInput.files).forEach((f, i) => {
                                if (i !== index) dt.items.add(f);
                            });
                            fileInput.files = dt.files;
                            fileInput.dispatchEvent(new Event('change'));
                        };
                        
                        const fileName = document.createElement('p');
                        fileName.className = 'text-xs text-gray-600 dark:text-gray-400 mt-1 truncate';
                        fileName.textContent = file.name;
                        
                        previewItem.appendChild(img);
                        previewItem.appendChild(removeBtn);
                        previewItem.appendChild(fileName);
                    } else {
                        // File preview (icon)
                        const fileIcon = document.createElement('div');
                        fileIcon.className = 'w-full h-32 bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 flex flex-col items-center justify-center';
                        fileIcon.innerHTML = `
                            <span class="material-symbols-outlined text-4xl text-gray-400 dark:text-gray-600 mb-2">description</span>
                            <p class="text-xs text-gray-600 dark:text-gray-400 text-center px-2 truncate w-full">${file.name}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">${(file.size / 1024).toFixed(1)} KB</p>
                        `;
                        
                        const removeBtn = document.createElement('button');
                        removeBtn.type = 'button';
                        removeBtn.className = 'absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity';
                        removeBtn.innerHTML = '<span class="material-symbols-outlined text-sm">close</span>';
                        removeBtn.onclick = () => {
                            const dt = new DataTransfer();
                            Array.from(fileInput.files).forEach((f, i) => {
                                if (i !== index) dt.items.add(f);
                            });
                            fileInput.files = dt.files;
                            fileInput.dispatchEvent(new Event('change'));
                        };
                        
                        previewItem.appendChild(fileIcon);
                        previewItem.appendChild(removeBtn);
                    }
                    
                    previewContainer.appendChild(previewItem);
                });
            });
        });
        
        // İl/İlçe yükleme
        (async function() {
            try {
                const response = await fetch('{{ asset("json/il-ilce.json") }}');
                const data = await response.json();
                
                // İlleri unique olarak çıkar
                const iller = [...new Set(data.map(item => item.il))].sort();
                
                // İl select'lerini doldur
                document.querySelectorAll('[data-il-select]').forEach(ilSelect => {
                    const selectedIl = ilSelect.value; // Eski değeri sakla
                    
                    iller.forEach(il => {
                        const option = document.createElement('option');
                        option.value = il;
                        option.textContent = il;
                        if (il === selectedIl) {
                            option.selected = true;
                        }
                        ilSelect.appendChild(option);
                    });
                    
                    // İl seçiliyse ilçeleri yükle
                    if (selectedIl) {
                        const form = ilSelect.closest('form');
                        const ilceSelect = form.querySelector('[data-ilce-select]');
                        if (ilceSelect) {
                            const selectedIlce = ilceSelect.value; // Eski ilçe değerini sakla
                            
                            const ilceler = [...new Set(
                                data
                                    .filter(item => item.il === selectedIl)
                                    .map(item => item.ilce)
                            )].sort();
                            
                            ilceler.forEach(ilce => {
                                const option = document.createElement('option');
                                option.value = ilce;
                                option.textContent = ilce;
                                if (ilce === selectedIlce) {
                                    option.selected = true;
                                }
                                ilceSelect.appendChild(option);
                            });
                        }
                    }
                });
                
                // İl seçildiğinde ilçeleri yükle
                document.querySelectorAll('[data-il-select]').forEach(ilSelect => {
                    ilSelect.addEventListener('change', function() {
                        const selectedIl = this.value;
                        const form = this.closest('form');
                        
                        // Aynı form içindeki ilçe select'ini bul
                        const ilceSelect = form.querySelector('[data-ilce-select]');
                        if (!ilceSelect) return;
                        
                        // İlçe select'ini temizle
                        ilceSelect.innerHTML = '<option value="">İlçe Seç</option><option value="MERKEZ">MERKEZ</option>';
                        ilceSelect.value = ''; // Değeri sıfırla
                        
                        if (selectedIl) {
                            // Seçilen ile ait ilçeleri filtrele ve unique yap
                            const ilceler = [...new Set(
                                data
                                    .filter(item => item.il === selectedIl)
                                    .map(item => item.ilce)
                            )].sort();
                            
                            ilceler.forEach(ilce => {
                                const option = document.createElement('option');
                                option.value = ilce;
                                option.textContent = ilce;
                                ilceSelect.appendChild(option);
                            });
                        }
                    });
                });
            } catch (error) {
                console.error('İl/İlçe verileri yüklenirken hata oluştu:', error);
            }
        })();
    });
</script>
<style>
    .error-message {
        color: #ef4444;
        font-size: 12px;
        margin-top: 5px;
        padding: 4px 8px;
        background-color: #fee2e2;
        border-radius: 4px;
        border-left: 3px solid #ef4444;
    }
    .dark .error-message {
        color: #fca5a5;
        background-color: #7f1d1d;
        border-left: 3px solid #fca5a5;
    }
</style>
@endpush
@endif