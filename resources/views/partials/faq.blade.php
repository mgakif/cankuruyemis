@if(isset($faqs) && $faqs->isNotEmpty())
<section class="py-20 bg-background-light dark:bg-background-dark">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-serif font-bold text-primary dark:text-orange-100 mb-4">Sıkça Sorulan Sorular</h2>
            <p class="text-gray-600 dark:text-gray-400">Can Kuruyemiş hakkında merak edilenler.</p>
        </div>

        <div class="space-y-4" x-data="{ open: 0 }">
            @foreach($faqs as $index => $faq)
                <div class="rounded-2xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-surface-dark shadow-sm overflow-hidden">
                    <button type="button"
                            class="w-full flex items-center justify-between gap-4 px-6 py-5 text-left"
                            @click="open = open === {{ $index }} ? null : {{ $index }}"
                            :aria-expanded="open === {{ $index }}">
                        <span class="text-lg font-bold text-text-dark dark:text-white">{{ $faq->question }}</span>
                        <span class="material-icons-round text-primary shrink-0 transition-transform duration-300"
                              :class="open === {{ $index }} ? 'rotate-180' : ''">expand_more</span>
                    </button>
                    <div x-show="open === {{ $index }}" x-collapse>
                        <div class="px-6 pb-5 text-gray-600 dark:text-gray-300 leading-relaxed">
                            {!! nl2br(e($faq->answer)) !!}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
