<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class ProductFilter extends Component
{
    use WithPagination;

    private const DEFAULT_MIN_PRICE = 0;
    private const DEFAULT_MAX_PRICE = 1000;
    private const CHANNEL_ALL = 'all';
    private const CHANNEL_ONLINE = 'online';
    private const CHANNEL_STORE_ONLY = 'store_only';

    public $search = '';
    public $sortBy = 'popularity'; // popularity, price_asc, price_desc, newest
    public $minPrice = self::DEFAULT_MIN_PRICE;
    public $maxPrice = self::DEFAULT_MAX_PRICE;
    public $priceRangeMin = self::DEFAULT_MIN_PRICE;
    public $priceRangeMax = self::DEFAULT_MAX_PRICE;
    public $selectedChannel = self::CHANNEL_ALL;
    public $selectedCategories = [];
    public $selectedDietary = [];
    public $showFilters = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedChannel' => ['except' => self::CHANNEL_ALL],
        'sortBy' => ['except' => 'popularity'],
        'minPrice' => ['except' => self::DEFAULT_MIN_PRICE],
        'maxPrice' => ['except' => self::DEFAULT_MAX_PRICE],
        'selectedCategories' => ['except' => []],
        'selectedDietary' => ['except' => []],
    ];

    public function mount()
    {
        $this->normalizeFilters();

        $priceRange = Product::query()
            ->where('is_published', true)
            ->visibleOnSite()
            ->selectRaw('MIN(COALESCE(NULLIF(online_price, 0), store_price, price)) as min_price, MAX(COALESCE(NULLIF(online_price, 0), store_price, price)) as max_price')
            ->first();

        if ($priceRange) {
            $this->priceRangeMin = floor($priceRange->min_price ?? 0);
            $this->priceRangeMax = ceil($priceRange->max_price ?? 1000);

            if ((int) $this->minPrice === self::DEFAULT_MIN_PRICE) {
                $this->minPrice = $this->priceRangeMin;
            }

            if ((int) $this->maxPrice === self::DEFAULT_MAX_PRICE) {
                $this->maxPrice = $this->priceRangeMax;
            }
        }

        $this->clampPriceRange();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSortBy()
    {
        $this->resetPage();
    }

    public function updatingSelectedChannel()
    {
        $this->resetPage();
    }

    public function updatingSelectedCategories()
    {
        $this->resetPage();
    }

    public function updatingSelectedDietary()
    {
        $this->resetPage();
    }

    public function updatingMinPrice()
    {
        $this->resetPage();
    }

    public function updatingMaxPrice()
    {
        $this->resetPage();
    }

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    public function toggleCategory(string $categorySlug): void
    {
        $this->normalizeFilters();

        if (in_array($categorySlug, $this->selectedCategories, true)) {
            $this->selectedCategories = array_values(array_diff($this->selectedCategories, [$categorySlug]));
        } else {
            $this->selectedCategories[] = $categorySlug;
        }

        $this->resetPage();
    }

    public function setPriceRange(?int $minPrice = null, ?int $maxPrice = null): void
    {
        $this->minPrice = $minPrice ?? $this->priceRangeMin;
        $this->maxPrice = $maxPrice ?? $this->priceRangeMax;

        $this->clampPriceRange();
        $this->resetPage();
    }

    public function applyPriceRange(): void
    {
        $this->clampPriceRange();
        $this->resetPage();
    }

    public function resetPriceRange(): void
    {
        $this->minPrice = $this->priceRangeMin;
        $this->maxPrice = $this->priceRangeMax;
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'selectedCategories', 'selectedDietary', 'selectedChannel']);
        $this->selectedChannel = self::CHANNEL_ALL;
        $this->minPrice = $this->priceRangeMin;
        $this->maxPrice = $this->priceRangeMax;
        $this->resetPage();
    }

    public function render()
    {
        $showStoreOnlyProducts = Product::shouldShowStoreOnlyOnSite();

        $this->normalizeFilters($showStoreOnlyProducts);
        $this->clampPriceRange();

        $query = Product::query()
            ->where('is_published', true)
            ->visibleOnSite()
            ->with('category');

        if ($this->selectedChannel !== self::CHANNEL_ALL) {
            $query->where('sale_channel', $this->selectedChannel);
        }

        // Search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                    ->orWhere('barcode', 'like', '%' . $this->search . '%')
                    ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', '%' . $this->search . '%'))
                    ->orWhere('description', 'like', '%' . $this->search . '%')
                    ->orWhere('short_description', 'like', '%' . $this->search . '%');
            });
        }

        // Price range
        if ($this->minPrice != $this->priceRangeMin || $this->maxPrice != $this->priceRangeMax) {
            $query->whereBetween(\DB::raw('COALESCE(NULLIF(online_price, 0), store_price, price)'), [$this->minPrice, $this->maxPrice]);
        }

        if (!empty($this->selectedCategories)) {
            $query->whereHas('category', function ($categoryQuery) {
                $categoryQuery->whereIn('slug', $this->selectedCategories);
            });
        }

        // Sorting
        switch ($this->sortBy) {
            case 'price_asc':
                $query->orderByRaw('COALESCE(NULLIF(online_price, 0), store_price, price) asc');
                break;
            case 'price_desc':
                $query->orderByRaw('COALESCE(NULLIF(online_price, 0), store_price, price) desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            default: // popularity
                $query->orderBy('created_at', 'desc');
                break;
        }

        $products = $query->paginate(12);

        $categories = Product::query()
            ->where('is_published', true)
            ->visibleOnSite()
            ->when($this->selectedChannel !== self::CHANNEL_ALL, fn ($categoryQuery) => $categoryQuery->where('sale_channel', $this->selectedChannel))
            ->with('category')
            ->get()
            ->pluck('category')
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();

        $categoryCounts = Product::query()
            ->where('is_published', true)
            ->visibleOnSite()
            ->when($this->selectedChannel !== self::CHANNEL_ALL, fn ($categoryCountQuery) => $categoryCountQuery->where('sale_channel', $this->selectedChannel))
            ->join('product_categories', 'product_categories.id', '=', 'products.product_category_id')
            ->selectRaw('product_categories.slug, COUNT(*) as count')
            ->groupBy('product_categories.slug')
            ->pluck('count', 'slug');

        $channelCounts = Product::query()
            ->where('is_published', true)
            ->visibleOnSite()
            ->selectRaw('sale_channel, COUNT(*) as count')
            ->groupBy('sale_channel')
            ->pluck('count', 'sale_channel');

        return view('livewire.product-filter', [
            'products' => $products,
            'categories' => $categories,
            'categoryCounts' => $categoryCounts,
            'channelCounts' => $channelCounts,
            'showStoreOnlyProducts' => $showStoreOnlyProducts,
            'totalProducts' => Product::where('is_published', true)->visibleOnSite()->count(),
            'priceRangeMin' => $this->priceRangeMin,
            'priceRangeMax' => $this->priceRangeMax,
        ]);
    }

    private function normalizeFilters(?bool $showStoreOnlyProducts = null): void
    {
        $showStoreOnlyProducts ??= Product::shouldShowStoreOnlyOnSite();

        $allowedChannels = [self::CHANNEL_ALL, self::CHANNEL_ONLINE];

        if ($showStoreOnlyProducts) {
            $allowedChannels[] = self::CHANNEL_STORE_ONLY;
        }

        if (! in_array($this->selectedChannel, $allowedChannels, true)) {
            $this->selectedChannel = self::CHANNEL_ALL;
        }

        if (is_string($this->selectedCategories)) {
            $this->selectedCategories = array_filter([$this->selectedCategories]);
        }

        $this->selectedCategories = collect($this->selectedCategories)
            ->filter(fn ($category) => is_string($category) && $category !== '')
            ->values()
            ->all();
    }

    private function clampPriceRange(): void
    {
        $this->minPrice = max((int) $this->priceRangeMin, (int) $this->minPrice);
        $this->maxPrice = min((int) $this->priceRangeMax, (int) $this->maxPrice);

        if ($this->minPrice > $this->maxPrice) {
            $this->minPrice = $this->priceRangeMin;
            $this->maxPrice = $this->priceRangeMax;
        }
    }
}
