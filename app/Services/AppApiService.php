<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * AppApiService
 *
 * Simple API client for store, products, categories, sliders, and shipping.
 */
class AppApiService
{
    protected string $baseUrl;
    protected int    $storeId;
    protected int    $cacheTtl = 86400; // 24 hours

    public function __construct()
    {
        $this->baseUrl = rtrim(env('IMS_APP_BASE_URL', 'http://127.0.0.1:8000'), '/');
        $this->storeId = (int) env('IMS_STORE_ID', 1);
    }

    public function clearCache(): bool
    {
        try {
            Cache::flush();
            return true;
        } catch (\Throwable $e) {
            Log::error("AppApiService clearCache error: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Simple cached GET request helper using global apiCall.
     */
    protected function cachedRequest(string $endpoint, array $data = []): ?array
    {
        $cacheKey = 'api_' . md5($endpoint . serialize($data));

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($endpoint, $data) {
            $res = apiCall($endpoint, 'GET', $data);
            return ($res && !empty($res['success']) && isset($res['data'])) ? $res : null;
        });
    }

    public function getStore(?int $storeId = null): ?array
    {
        $res = $this->cachedRequest('/api/v1/app/store/' . ($storeId ?? $this->storeId));
        if (!$res || empty($res['success']) || empty($res['data'])) return null;

        $store = $res['data'];
        $store['logo_url'] = $this->resolveMediaUrl($store['logo'] ?? null, $this->baseUrl ?? null);
        return $store;
    }

    public function getPage(string $slug, ?int $storeId = null): ?array
    {
        $res = $this->cachedRequest('/api/v1/app/page/' . ($storeId ?? $this->storeId) . '/' . rawurlencode($slug));

        if (!$res || !is_array($res['data'] ?? null)) {
            return null;
        }

        return $res['data'];
    }

    public function getSliders(?int $storeId = null): array
    {
        $res = $this->cachedRequest('/api/v1/app/sliders/' . ($storeId ?? $this->storeId));
        if (!$res || empty($res['success']) || empty($res['data'])) return [];

        return array_map(function (array $s) {
            $img = $this->resolveMediaUrl($s['desktop_image'] ?? $s['mobile_image'] ?? null);
            return (object) [
                'id'            => $s['id'] ?? null,
                'desktop_image' => $this->resolveMediaUrl($s['desktop_image'] ?? null),
                'mobile_image'  => $this->resolveMediaUrl($s['mobile_image'] ?? null),
                'offer_text'    => $s['offer_text'] ?? null,
                'title'         => $s['title'] ?? null,
                'details'       => $s['details'] ?? null,
                'button_text'   => $s['button_text'] ?? null,
                'button_url'    => $s['button_url'] ?? null,
                'slider_url'    => $s['button_url'] ?? null,
                'sort_order'    => (int) ($s['sort_order'] ?? 0),
                'image_url'     => $img,
                'image'         => $img,
                'get_img'       => $img ? (object) ['file_url' => $img] : null,
            ];
        }, $res['data']);
    }

    public function getCategories(?int $storeId = null): array
    {
        $id  = $storeId ?? $this->storeId;
        $res = $this->cachedRequest("/api/v1/app/categories/{$id}");
        if (!$res || empty($res['success']) || empty($res['data'])) return [];

        $mapChild = function (array $c): object {
            $img = $this->resolveMediaUrl($c['image'] ?? null);
            return (object) [
                'id'            => (int) ($c['id'] ?? 0),
                'parent_id'     => $c['parent_id'] ?? null,
                'name'          => $c['name'] ?? '',
                'category_name' => $c['name'] ?? '',
                'slug'          => $c['slug'] ?? '',
                'is_menu'       => (bool) ($c['is_menu'] ?? false),
                'is_home'       => (bool) ($c['is_home'] ?? false),
                'is_section'    => (bool) ($c['is_section'] ?? false),
                'is_footer'     => (bool) ($c['is_footer'] ?? false),
                'status'        => (bool) ($c['status'] ?? true),
                'image'         => $img,
                'image_url'     => $img,
                'get_image'     => $img ? (object) ['file_url' => $img] : null,
                'get_thumb'     => $img ? (object) ['file_url' => $img] : null,
                'children'      => collect([]),
            ];
        };

        return array_map(function (array $c) use ($id, $mapChild) {
            $img = $this->resolveMediaUrl($c['image'] ?? null) ?: $this->findCategoryFallbackImage((int) ($c['id'] ?? 0), $id);

            // Map raw children arrays → objects, then wrap in a Collection
            $children = collect(array_map($mapChild, $c['children'] ?? []));

            return (object) [
                'id'            => (int) ($c['id'] ?? 0),
                'parent_id'     => $c['parent_id'] ?? null,
                'name'          => $c['name'] ?? '',
                'category_name' => $c['name'] ?? '',
                'slug'          => $c['slug'] ?? '',
                'is_menu'       => (bool) ($c['is_menu'] ?? false),
                'is_home'       => (bool) ($c['is_home'] ?? false),
                'is_section'    => (bool) ($c['is_section'] ?? false),
                'is_footer'     => (bool) ($c['is_footer'] ?? false),
                'status'        => (bool) ($c['status'] ?? true),
                'image'         => $img,
                'image_url'     => $img,
                'get_image'     => $img ? (object) ['file_url' => $img] : null,
                'get_thumb'     => $img ? (object) ['file_url' => $img] : null,
                'children'      => $children,
                '_products'     => collect([]),
            ];
        }, $res['data']);
    }

    protected function findCategoryFallbackImage(int $catId, int $storeId): ?string
    {
        $res   = $this->cachedRequest("/api/v1/app/products/{$storeId}", ['page' => 1]);
        $items = isset($res['data']['data']) ? $res['data']['data'] : ($res['data'] ?? []);
        if (is_array($items)) {
            foreach ($items as $p) {
                $ids = is_string($p['category_ids'] ?? null) ? json_decode($p['category_ids'], true) : ($p['category_ids'] ?? []);
                if (in_array((string) $catId, array_map('strval', (array) $ids)) && !empty($p['image'])) {
                    return $this->resolveMediaUrl($p['image']);
                }
            }
        }
        return null;
    }

    public function getCategoryById(int $id, ?int $storeId = null): ?object
    {
        foreach ($this->getCategories($storeId) as $cat) {
            if ((int) $cat->id === $id) return $cat;
        }
        return null;
    }

    public function getProducts(?int $storeId = null, int $page = 1, array $extra = []): LengthAwarePaginator
    {
        $id      = $storeId ?? $this->storeId;
        $perPage = 28;
        $query   = array_merge(['page' => $page, 'per_page' => $perPage, 'limit' => $perPage], $extra);
        $res     = $this->cachedRequest("/api/v1/app/products/{$id}", $query);

        $d = $res['data'] ?? [];
        if (isset($d['data']) && is_array($d['data'])) {
            $items    = array_map([$this, 'mapProduct'], $d['data']);
            $total    = (int) ($d['total'] ?? count($items));
            $perPage  = (int) ($d['per_page'] ?? $perPage);
            $currPage = (int) ($d['current_page'] ?? $page);
        } elseif (is_array($d) && !empty($d)) {
            $all      = array_map([$this, 'mapProduct'], $d);
            $total    = count($all);
            $items    = array_slice($all, ($page - 1) * $perPage, $perPage);
            $currPage = $page;
        } else {
            $items    = [];
            $total    = 0;
            $currPage = $page;
        }

        return new LengthAwarePaginator($items, $total, $perPage, $currPage, [
            'path'     => Paginator::resolveCurrentPath(),
            'pageName' => 'page',
        ]);
    }

    public function getProductsByCategory(int $catId, ?int $storeId = null, int $page = 1): LengthAwarePaginator
    {
        // Try API-side category filter first
        $res = $this->getProducts($storeId, $page, ['category_id' => $catId]);

        // If API returned a non-empty response, check whether the results are
        // actually filtered. If total < full-catalogue size we trust the API.
        // Otherwise fall through to local filtering.
        $allPage1 = $this->getProducts($storeId, 1);
        $fullTotal = $allPage1->total();
        if ($res->count() > 0 && $res->total() < $fullTotal) {
            return $res;
        }

        // Fallback: scan all pages and filter by category_ids locally
        $matched = [];
        for ($p = 1; $p <= 50; $p++) {
            $pag = $this->getProducts($storeId, $p);
            if ($pag->count() === 0) break;
            foreach ($pag->items() as $item) {
                $catIds = array_map('strval', (array) ($item->category_ids ?? []));
                if (in_array((string) $catId, $catIds, true)) {
                    $matched[] = $item;
                }
            }
            if ($pag->currentPage() >= $pag->lastPage()) break;
        }

        $perPage = 20;
        $total   = count($matched);
        $items   = array_slice($matched, ($page - 1) * $perPage, $perPage);

        return new LengthAwarePaginator($items, $total, $perPage, $page, [
            'path'     => Paginator::resolveCurrentPath(),
            'pageName' => 'page',
        ]);
    }

    public function searchProducts(string $keyword, ?int $storeId = null, int $page = 1, ?int $categoryId = null): LengthAwarePaginator
    {
        $keyword = trim($keyword);

        // ── 1. Try API-side search (API may support q/search/category_id) ──────
        if ($keyword !== '') {
            $extra = ['q' => $keyword, 'search' => $keyword, 'keyword' => $keyword, 'name' => $keyword];
            if ($categoryId) $extra['category_id'] = $categoryId;
            $res = $this->getProducts($storeId, $page, $extra);

            // Accept API result only if it is narrower than the full catalogue
            $allPage1  = $this->getProducts($storeId, 1);
            $fullTotal = $allPage1->total();
            if ($res->count() > 0 && $res->total() < $fullTotal) {
                return $res;
            }
        }

        // ── 2. Local full-catalogue scan ─────────────────────────────────────
        // Split keyword into words for partial / any-word matching
        $words = $keyword !== '' ? array_filter(explode(' ', mb_strtolower($keyword))) : [];

        $matched = [];
        for ($p = 1; $p <= 50; $p++) {
            $pag = $this->getProducts($storeId, $p);
            if ($pag->count() === 0) break;
            foreach ($pag->items() as $item) {
                // ── Category filter ──────────────────────────────────────────
                if ($categoryId !== null) {
                    $catIds = array_map('strval', (array) ($item->category_ids ?? []));
                    if (!in_array((string) $categoryId, $catIds, true)) continue;
                }
                // ── Keyword filter ───────────────────────────────────────────
                if (empty($words)) {
                    // No keyword: category-only browse → include all
                    $matched[] = $item;
                    continue;
                }
                $haystack = mb_strtolower($item->name . ' ' . $item->slug . ' ' . ($item->description ?? '') . ' ' . ($item->product_tag ?? ''));
                // Match if ANY search word appears in the haystack
                $hit = false;
                foreach ($words as $w) {
                    if (mb_strpos($haystack, $w) !== false) { $hit = true; break; }
                }
                if ($hit) $matched[] = $item;
            }
            if ($pag->currentPage() >= $pag->lastPage()) break;
        }

        // ── 3. Score: items matching ALL words rank higher ────────────────────
        if (count($words) > 1 && count($matched) > 1) {
            usort($matched, function ($a, $b) use ($words) {
                $ha = mb_strtolower($a->name . ' ' . $a->slug);
                $hb = mb_strtolower($b->name . ' ' . $b->slug);
                $sa = 0; foreach ($words as $w) { if (mb_strpos($ha, $w) !== false) $sa++; }
                $sb = 0; foreach ($words as $w) { if (mb_strpos($hb, $w) !== false) $sb++; }
                return $sb <=> $sa;
            });
        }

        $perPage = 20;
        $total   = count($matched);
        $items   = array_slice($matched, ($page - 1) * $perPage, $perPage);

        return new LengthAwarePaginator($items, $total, $perPage, $page, [
            'path'     => Paginator::resolveCurrentPath(),
            'pageName' => 'page',
        ]);
    }

    public function getHotDeals(?int $storeId = null, int $limit = 42): array
    {
        $deals = [];
        for ($p = 1; $p <= 5; $p++) {
            $pag = $this->getProducts($storeId, $p);
            if ($pag->count() === 0) break;
            foreach ($pag->items() as $prod) {
                if ($prod->sale_price > 0) {
                    $deals[] = $prod;
                    if (count($deals) >= $limit) return $deals;
                }
            }
            if ($pag->currentPage() >= $pag->lastPage()) break;
        }
        return $deals;
    }

    public function getProductBySlugId(string $slug, int $id, ?int $storeId = null): ?object
    {
        $storeId  = $storeId ?? $this->storeId;
        $cacheKey = "api_product_{$id}_{$slug}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($slug, $id, $storeId) {
            for ($p = 1; $p <= 10; $p++) {
                $pag = $this->getProducts($storeId, $p);
                if ($pag->count() === 0) break;
                foreach ($pag->items() as $item) {
                    if ((int)$item->id === $id || ($slug && $item->slug === $slug)) {
                        return $item;
                    }
                }
                if ($pag->currentPage() >= $pag->lastPage()) break;
            }
            return null;
        });
    }

    /**
     * Resolve related product IDs while preserving the order returned by the API.
     */
    public function getRelatedProducts(array $productIds, int $excludeId = 0, ?int $storeId = null): Collection
    {
        $ids = collect($productIds)
            ->map(function ($id) {
                if (is_array($id)) {
                    $id = $id['id'] ?? null;
                } elseif (is_object($id)) {
                    $id = $id->id ?? null;
                }

                return filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
            })
            ->filter(fn ($id) => $id !== null && $id !== $excludeId)
            ->unique()
            ->values();

        return $ids
            ->map(fn (int $id) => $this->getProductBySlugId('', $id, $storeId))
            ->filter()
            ->values();
    }

    public function getShippingMethods(?int $storeId = null): array
    {
        $res = $this->cachedRequest('/api/v1/app/shipping-methods/' . ($storeId ?? $this->storeId));
        if ($res && !empty($res['success']) && !empty($res['data'])) {
            return array_map(fn($i) => (object) [
                'id'       => $i['id'] ?? 1,
                'store_id' => $i['store_id'] ?? null,
                'type'     => $i['type'] ?? null,
                'name'     => $i['name'] ?? $i['text'] ?? 'Standard Shipping',
                'text'     => $i['name'] ?? $i['text'] ?? 'Standard Shipping',
                'amount'   => (float) ($i['amount'] ?? 0),
                'status'   => (int) ($i['status'] ?? 1),
            ], $res['data']);
        }

        return [
            (object) ['id' => 1, 'store_id' => null, 'type' => 'inside_dhaka', 'name' => 'ঢাকার ভিতরে - ৳৭০', 'text' => 'ঢাকার ভিতরে - ৳৭০', 'amount' => 70, 'status' => 1],
            (object) ['id' => 2, 'store_id' => null, 'type' => 'outside_dhaka', 'name' => 'ঢাকার বাইরে - ৳১৩০', 'text' => 'ঢাকার বাইরে - ৳১৩০', 'amount' => 130, 'status' => 1],
        ];
    }

    public function getShippingMethodAmount($methodId, ?int $storeId = null): float
    {
        foreach ($this->getShippingMethods($storeId) as $m) {
            if ((int) $m->id === (int) $methodId) return (float) $m->amount;
        }
        return 0.0;
    }

    public function getReviews(?int $productId = null, ?int $storeId = null): Collection
    {
        $storeId = $storeId ?? $this->storeId;
        $res = $this->cachedRequest("/api/v1/app/reviews/{$storeId}");
        $items = $res['data'] ?? [];

        if (isset($items['data']) && is_array($items['data'])) {
            $items = $items['data'];
        }

        return collect(is_array($items) ? $items : [])
            ->filter(function (array $review) use ($productId) {
                return (int) ($review['status'] ?? 1) === 1
                    && ($productId === null || (int) ($review['product_id'] ?? 0) === $productId);
            })
            ->map(function (array $review) {
                $images = $review['image'] ?? [];
                $images = is_array($images) ? $images : (json_decode((string) $images, true) ?: [$images]);
                $resolvedImages = array_values(array_filter(array_map(function ($img) {
                    return $this->resolveMediaUrl($img);
                }, (array) $images)));

                return (object) [
                    'id'         => $review['id'] ?? null,
                    'product_id' => $review['product_id'] ?? null,
                    'name'       => $review['name'] ?? '',
                    'email'      => $review['email'] ?? null,
                    'rating'     => (int) ($review['rating'] ?? 0),
                    'summary'    => $review['summary'] ?? '',
                    'review'     => $review['summary'] ?? '', // alias for blade compatibility
                    'status'     => (int) ($review['status'] ?? 1),
                    'image'      => $resolvedImages,
                    'created_at' => $review['created_at'] ?? null,
                    'updated_at' => $review['updated_at'] ?? null,
                ];
            })
            ->values();
    }

    public function clearReviewsCache(?int $storeId = null): bool
    {
        $storeId = $storeId ?? $this->storeId;
        Cache::forget('api_' . md5("/api/v1/app/reviews/{$storeId}" . serialize([])));
        Cache::forget('api_' . md5("/api/v1/app/reviews/{$storeId}"));
        return true;
    }

    /**
     * Map raw product API response to a simple standard PHP object.
     */
    public function mapProduct(array $item): object
    {
        $id      = (int) ($item['id'] ?? 0);
        $sku     = $item['sku'] ?? '';
        $catIds  = is_string($item['category_ids'] ?? null) ? json_decode($item['category_ids'], true) : ($item['category_ids'] ?? []);
        $catIds  = (array) $catIds;
        $img     = $this->resolveMediaUrl($item['image'] ?? null);

        $gallery = $item['image_gallery'] ?? $item['gallery_images'] ?? [];
        if (is_string($gallery)) $gallery = json_decode($gallery, true) ?? [];
        $gallery = array_values(array_map(fn($g) => $this->resolveMediaUrl($g), (array) $gallery));

        // The app API may omit the legacy Eloquent relation, or return it as null.
        // Build the same shape expected by the storefront from categories/category_ids.
        $category = $item['get_category'] ?? null;
        if (is_array($category) && isset($category['category']) && is_array($category['category'])) {
            $category = $category['category'];
        }
        if (!is_array($category) && !empty($item['categories']) && is_array($item['categories'])) {
            $category = $item['categories'][0] ?? null;
        }
        if (!is_array($category) && !empty($catIds)) {
            foreach ($this->getCategories() as $candidate) {
                if ((string) $candidate->id === (string) $catIds[0]) {
                    $category = [
                        'id' => $candidate->id,
                        'category_name' => $candidate->name,
                        'name' => $candidate->name,
                        'slug' => $candidate->slug,
                        'image' => $candidate->image,
                    ];
                    break;
                }
            }
        }

        $attributes = array_map(function ($a) {
            $a = (array) $a;
            $vals = array_map(function ($v) {
                $v = (array) $v;
                return (object) [
                    'id'            => $v['id'] ?? null,
                    'name'          => $v['name'] ?? '',
                    'image'         => $this->resolveMediaUrl($v['image'] ?? null),
                    'variant_image' => $this->resolveMediaUrl($v['variant_image'] ?? null),
                    'sku'           => $v['sku'] ?? ($v['variant_sku'] ?? ''),
                ];
            }, $a['values'] ?? []);

            return (object) [
                'attribute_id'   => $a['attribute_id'] ?? null,
                'attribute_name' => $a['attribute_name'] ?? '',
                'has_image'      => (bool) ($a['has_image'] ?? false),
                'values'         => $vals,
                'raw_values'     => $vals,
            ];
        }, (array) ($item['attributes'] ?? []));

        $variants = array_map(function ($v) use ($id, $sku) {
            $v      = (array) $v;
            $vSku   = $v['variant_sku'] ?? ($v['sku'] ?? '');
            $suffix = ($sku && Str::startsWith($vSku, "{$sku}-")) ? substr($vSku, strlen($sku) + 1) : $vSku;
            $vPrice = (float) ($v['variant_price'] ?? $v['price'] ?? 0);
            $vStock = (int) ($v['variant_stock'] ?? $v['stock'] ?? 0);

            return (object) [
                'id'                    => (int) ($v['id'] ?? 0),
                'product_id'            => (int) ($v['product_id'] ?? $id),
                'attribute_item_ids'    => $v['attribute_item_ids'] ?? '[]',
                'variant_sku'           => $vSku,
                'variant_image'         => $this->resolveMediaUrl($v['variant_image'] ?? null),
                'variant_purchase_cost' => (float) ($v['variant_purchase_cost'] ?? 0),
                'variant_price'         => $vPrice,
                'variant_stock'         => $vStock,
                'price'                 => $vPrice,
                'sku'                   => $vSku,
                'variant'               => $suffix,
                'variant_suffix'        => $suffix,
                'stock'                 => $vStock,
            ];
        }, (array) ($item['get_product_attributes'] ?? $item['variants'] ?? []));

        $regPrice = (float) ($item['regular_price'] ?? $item['price'] ?? 0);

        return (object) array_merge($item, [
            'id'                     => $id,
            'product_id'             => $id,
            'name'                   => $item['name'] ?? '',
            'slug'                   => $item['slug'] ?? '',
            'sku'                    => $sku,
            'description'            => $item['description'] ?? null,
            'delivery_text'          => $item['delivery_text'] ?? null,
            'product_tag'            => $item['product_tag'] ?? '',
            'position'               => (int) ($item['position'] ?? 0),
            'free_shipping'          => (bool) ($item['free_shipping'] ?? false),
            'has_variant'            => (int) ($item['has_variant'] ?? 0),
            'category_ids'           => $catIds,
            'category_id'            => !empty($catIds) ? (int) $catIds[0] : null,
            'store_ids'              => (array) ($item['store_ids'] ?? []),
            'price'                  => $regPrice,
            'regular_price'          => $regPrice,
            'sale_price'             => (float) ($item['sale_price'] ?? 0),
            'product_cost'           => (float) ($item['product_cost'] ?? 0),
            'total_stock'            => (int) ($item['total_stock'] ?? 0),
            'stock'                  => (int) ($item['total_stock'] ?? 0),
            'stock_out'              => (int) ($item['stock_out'] ?? 0),
            'alert_quantity'         => (int) ($item['alert_quantity'] ?? 5),
            'status'                 => (int) ($item['status'] ?? 1),
            'image'                  => $img,
            'image_url'              => $img,
            'get_thumb'              => $img ? (object) ['file_url' => $img, '_is_api' => true] : null,
            'get_image'              => $img ? (object) ['file_url' => $img, '_is_api' => true] : null,
            'image_gallery'          => $gallery,
            'images'                 => $gallery,
            'gallery_images'         => !empty($gallery) ? $gallery : null,
            'attributes'             => $attributes,
            'get_product_attributes' => collect($variants),
            'get_attributes'         => collect($variants),
            'get_category'           => $category ? (object) [
                'id'            => $category['id'] ?? null,
                'category_name' => $category['category_name'] ?? $category['name'] ?? '',
                'name'          => $category['name'] ?? '',
                'slug'          => $category['slug'] ?? '',
                'image'         => isset($category['image']) ? $this->resolveMediaUrl($category['image']) : null,
                // Legacy nested shape: some blade templates use get_category->category->category_name
                'category'      => (object) [
                    'id'            => $category['id'] ?? null,
                    'category_name' => $category['category_name'] ?? $category['name'] ?? '',
                    'name'          => $category['name'] ?? '',
                    'slug'          => $category['slug'] ?? '',
                ],
            ] : null,
            'get_reviews'            => collect([]),
            'combo_products'         => $item['combo_products'] ?? null,
            '_raw'                   => $item,
        ]);
    }

    public function resolveMediaUrl(?string $path, ?string $customBase = null): ?string
    {
        if (!$path) return null;

        // API payloads can contain absolute localhost URLs from the source server.
        // Keep the path but serve it from the configured API host instead.
        if (Str::startsWith($path, ['http://', 'https://'])) {
            $parts = parse_url($path);
            $host = strtolower($parts['host'] ?? '');
            if (in_array($host, ['localhost', '127.0.0.1'], true)) {
                $path = ($parts['path'] ?? '') . (isset($parts['query']) ? '?' . $parts['query'] : '');
            } else {
                return $path;
            }
        }

        $baseUrl = $customBase ?: asset('/');
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }

    public function getStoreId(): int {
        return $this->storeId;
    }



    /**----------------------------------------------------------------
     * Recive and store product image upload from API request.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * ----------------------------------------------------------------
     */
    public function uploadProductImage(Request $request)
    {
        // Log::info($request->all());
        $url = trim($request->input('image_url') ?? '');
        $oldImageUrl = trim($request->input('old_image_url') ?? '');

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return response()->json(['error' => 'Invalid image_url'], 422);
        }

        try {
            $path = ltrim(parse_url($url, PHP_URL_PATH) ?? '', '/');

            if (!$path) {
                return response()->json(['error' => 'Invalid image path'], 422);
            }

            // Remove old image if exists
            $removeOldImage = function () use ($oldImageUrl, $path): void {
                if (!$oldImageUrl) {
                    return;
                }

                $oldFile = realpath(public_path(ltrim(parse_url($oldImageUrl, PHP_URL_PATH) ?: $oldImageUrl, '/')));
                $publicRoot = realpath(public_path());
                $newFile = realpath(public_path($path));
                $publicPrefix = $publicRoot ? rtrim($publicRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR : '';

                if ($oldFile && is_file($oldFile) && $publicPrefix
                    && stripos($oldFile, $publicPrefix) === 0 && $oldFile !== $newFile) {
                    @unlink($oldFile);
                }
            };

            // Already exists
            if (is_file(public_path($path))) {
                $removeOldImage();
                return response()->json([
                    'success' => true,
                    'url' => asset($path)
                ]);
            }

            // Download
            $res = Http::withoutVerifying()->timeout(15)->get($url);
            if (!$res->successful() || !$res->body()) {
                return response()->json(['error' => 'Download failed'], 422);
            }

            // Directory
            $dir = dirname($path) !== '.' ? dirname($path) : 'uploads';
            $uploadDir = public_path($dir);
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // File name
            $name = basename($path);
            if (!pathinfo($name, PATHINFO_EXTENSION)) {
                $mime = explode(';', $res->header('Content-Type'))[0] ?? '';
                $ext = [
                    'image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp',
                    'image/gif'=>'gif','image/svg+xml'=>'svg','image/bmp'=>'bmp','image/avif'=>'avif'
                ][$mime] ?? 'jpg';
                $name = Str::random(20) . '.' . $ext;
            }
            $target = $uploadDir . '/' . $name;
            if (!file_put_contents($target, $res->body())) {
                return response()->json(['error' => 'Save failed'], 500);
            }

            $removeOldImage();
            return response()->json([
                'success' => true,
                'url' => asset($dir . '/' . $name)
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
