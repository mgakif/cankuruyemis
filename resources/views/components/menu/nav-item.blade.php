@props(['item', 'level' => 0])

@php
    $hasChildren = !$item->children->isEmpty();
@endphp

@if($hasChildren)
    <div class="relative group"
         x-data="{
            open: false,
            closeTimer: null,
            enter() {
                clearTimeout(this.closeTimer);
                this.open = true;
            },
            leave() {
                this.closeTimer = setTimeout(() => {
                    this.open = false;
                }, 150);
            }
         }"
         @click.away="open = false">
        <div class="flex items-center gap-1" @mouseenter="enter()" @mouseleave="leave()">
            <a
                href="{{ $item->link }}"
                target="{{ $item->target }}"
                @php
                    $isHashLink = $item->link === '#' || str_ends_with($item->link, '#') || str_ends_with($item->link, '#/');
                @endphp
                @if($isHashLink)
                    @click.prevent="open = !open"
                @endif
                class="text-gray-600 dark:text-gray-300 font-medium hover:text-primary dark:hover:text-orange-200 transition-colors flex items-center gap-1 {{ $item->link_class }}"
            >
                {{ $item->name }}
            </a>
            <button
                @click.stop="open = !open"
                class="text-text-dark dark:text-text-light hover:text-primary transition-colors flex items-center -ml-1"
                aria-label="Toggle submenu"
            >
                <span class="material-symbols-outlined text-sm transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
            </button>
        </div>
        <div
            class="absolute top-full left-0 mt-2 w-48 bg-white dark:bg-background-dark rounded-lg shadow-lg border border-[#e8dbce] dark:border-white/10 transition-all duration-200 z-50"
            :class="open ? 'opacity-100 visible' : 'opacity-0 invisible'"
            @mouseenter="enter()"
            @mouseleave="leave()"
        >
            <div class="py-2">
                @foreach($item->children as $child)
                    @if(!$child->children->isEmpty())
                        <div class="relative group/sub" x-data="{ openSub: false }" @click.away="openSub = false">
                            <div class="flex items-center justify-between">
                                <a 
                                    href="{{ $child->link }}" 
                                    target="{{ $child->target }}"
                                    @php
                                        $isHashLink = $child->link === '#' || str_ends_with($child->link, '#') || str_ends_with($child->link, '#/');
                                    @endphp
                                    @if($isHashLink)
                                        @click.prevent="openSub = !openSub"
                                    @else
                                        @click.stop
                                    @endif
                                    class="flex-1 block px-4 py-2 text-sm text-text-dark dark:text-text-light hover:text-primary hover:bg-primary/5 transition-colors {{ $child->link_class }}"
                                >
                                    {{ $child->name }}
                                </a>
                                <button 
                                    @click.stop="openSub = !openSub"
                                    class="px-2 text-text-dark dark:text-text-light hover:text-primary transition-colors"
                                    aria-label="Toggle submenu"
                                >
                                    <span class="material-symbols-outlined text-xs transition-transform" :class="openSub ? 'rotate-90' : ''">chevron_right</span>
                                </button>
                            </div>
                            <div 
                                class="absolute top-0 left-full ml-2 w-48 bg-white dark:bg-background-dark rounded-lg shadow-lg border border-[#e8dbce] dark:border-white/10 transition-all duration-200 z-50"
                                :class="openSub ? 'opacity-100 visible' : 'opacity-0 invisible'"
                                @mouseenter="openSub = true"
                                @mouseleave="openSub = false"
                            >
                                <div class="py-2">
                                    @foreach($child->children as $subChild)
                                        <a 
                                            href="{{ $subChild->link }}" 
                                            target="{{ $subChild->target }}"
                                            class="block px-4 py-2 text-sm text-text-dark dark:text-text-light hover:text-primary hover:bg-primary/5 transition-colors {{ $subChild->link_class }}"
                                        >
                                            {{ $subChild->name }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <a 
                            href="{{ $child->link }}" 
                            target="{{ $child->target }}"
                            class="block px-4 py-2 text-sm text-text-dark dark:text-text-light hover:text-primary hover:bg-primary/5 transition-colors {{ $child->link_class }}"
                        >
                            {{ $child->name }}
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
@else
    <a 
        href="{{ $item->link }}" 
        target="{{ $item->target }}"
        class="text-text-dark dark:text-text-light hover:text-primary text-sm font-medium transition-colors {{ $item->link_class }}"
    >
        {{ $item->name }}
    </a>
@endif
