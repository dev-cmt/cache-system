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
 * High-performance API client for store, products, categories, sliders, and shipping
 * with multi-tier in-memory (runtime) and persistent caching.
 */
class AppApiService
{
    protected string $baseUrl;
    protected int    $storeId;
    protected int    $cacheTtl = 86400; // 24 hours

    /**
     * In-memory request-level cache to eliminate duplicate cache reads and
     * redundant calculations during a single HTTP request lifecycle.
     */
    protected static array $runtimeCache = [];

    public function __construct()
    {
        $this->baseUrl = rtrim(env('IMS_APP_BASE_URL', 'http://127.0.0.1:8000'), '/');
        $this->storeId = (int) env('IMS_STORE_ID', 1);
    }

    public function clearCache(): bool
    {
        try {
            Cache::flush();
            self::$runtimeCache = [];
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
        $id = $storeId ?? $this->storeId;
        $runtimeKey = "store_{$id}";
        if (isset(self::$runtimeCache[$runtimeKey])) {
            return self::$runtimeCache[$runtimeKey];
        }

        $cacheKey = "api_store_mapped_{$id}";

        $store = Cache::remember($cacheKey, $this->cacheTtl, function () use ($id) {
            $res = $this->cachedRequest('/api/v1/app/store/' . $id);
            if (!$res || empty($res['success']) || empty($res['data'])) return null;

            $s = $res['data'];
            $s['logo_url'] = $this->resolveMediaUrl($s['logo'] ?? null, $this->baseUrl ?? null);
            return $s;
        });

        self::$runtimeCache[$runtimeKey] = $store;
        return $store;
    }

    public function getPage(string $slug, ?int $storeId = null): ?array
    {
        $id = $storeId ?? $this->storeId;
        $res = $this->cachedRequest('/api/v1/app/page/' . $id . '/' . rawurlencode($slug));

        if (!$res || !is_array($res['data'] ?? null)) {
            return null;
        }

        return $res['data'];
    }

    public function getSliders(?int $storeId = null): array
    {
        $id = $storeId ?? $this->storeId;
        $runtimeKey = "sliders_{$id}";
        if (isset(self::$runtimeCache[$runtimeKey])) {
            return self::$runtimeCache[$runtimeKey];
        }

        $cacheKey = "api_sliders_mapped_{$id}";

        $sliders = Cache::remember($cacheKey, $this->cacheTtl, function () use ($id) {
            $res = $this->cachedRequest('/api/v1/app/sliders/' . $id);
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
        });

        self::$runtimeCache[$runtimeKey] = $sliders;
        return $sliders;
    }

    /**
     * Map category fallback images across all products in a single pass.
     */
    protected function getCategoryFallbackImagesMap(int $storeId): array
    {
        $res = $this->cachedRequest("/api/v1/app/products/{$storeId}");
        $items = isset($res['data']['data']) ? $res['data']['data'] : ($res['data'] ?? []);
        $map = [];
        if (is_array($items)) {
            foreach ($items as $p) {
                if (empty($p['image'])) continue;
                $ids = is_string($p['category_ids'] ?? null) ? json_decode($p['category_ids'], true) : ($p['category_ids'] ?? []);
                $img = $this->resolveMediaUrl($p['image']);
                foreach ((array) $ids as $cid) {
                    $cidInt = (int) $cid;
                    if (!isset($map[$cidInt])) {
                        $map[$cidInt] = $img;
                    }
                }
            }
        }
        return $map;
    }

    public function findCategoryFallbackImage(int $catId, int $storeId): ?string
    {
        $map = $this->getCategoryFallbackImagesMap($storeId);
        return $map[$catId] ?? null;
    }

    public function getCategories(?int $storeId = null): array
    {
        $id = $storeId ?? $this->storeId;
        $runtimeKey = "categories_{$id}";
        if (isset(self::$runtimeCache[$runtimeKey])) {
            return self::$runtimeCache[$runtimeKey];
        }

        $cacheKey = "api_categories_processed_{$id}";

        $categories = Cache::remember($cacheKey, $this->cacheTtl, function () use ($id) {
            $res = $this->cachedRequest("/api/v1/app/categories/{$id}");
            if (!$res || empty($res['success']) || empty($res['data'])) return [];

            $fallbackImages = $this->getCategoryFallbackImagesMap($id);

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

            return array_map(function (array $c) use ($id, $mapChild, $fallbackImages) {
                $cid = (int) ($c['id'] ?? 0);
                $img = $this->resolveMediaUrl($c['image'] ?? null) ?: ($fallbackImages[$cid] ?? null);

                $children = collect(array_map($mapChild, $c['children'] ?? []));

                return (object) [
                    'id'            => $cid,
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
        });

        self::$runtimeCache[$runtimeKey] = $categories;
        return $categories;
    }

    public function getCategoryById(int $id, ?int $storeId = null): ?object
    {
        foreach ($this->getCategories($storeId) as $cat) {
            if ((int) $cat->id === $id) return $cat;
        }
        return null;
    }

    /**
     * Fetch, map, and cache ALL products for the store once.
     * Builds instant O(1) hash maps by ID and by Slug.
     */
    public function getAllMappedProducts(?int $storeId = null): array
    {
        $id = $storeId ?? $this->storeId;
        $runtimeKey = "mapped_products_{$id}";
        if (isset(self::$runtimeCache[$runtimeKey])) {
            return self::$runtimeCache[$runtimeKey];
        }

        $cacheKey = "api_products_mapped_all_{$id}";

        $products = Cache::remember($cacheKey, $this->cacheTtl, function () use ($id) {
            $res = $this->cachedRequest("/api/v1/app/products/{$id}");
            if (!$res || empty($res['success']) || !isset($res['data'])) {
                return [];
            }
            $items = isset($res['data']['data']) ? $res['data']['data'] : ($res['data'] ?? []);
            if (!is_array($items)) return [];

            // Pre-build category dictionary so mapProduct avoids recursive calls
            $catMap = [];
            foreach ($this->getCategories($id) as $c) {
                $catMap[(string) $c->id] = [
                    'id'            => $c->id,
                    'category_name' => $c->name ?? $c->category_name ?? '',
                    'name'          => $c->name ?? $c->category_name ?? '',
                    'slug'          => $c->slug ?? '',
                    'image'         => $c->image ?? null,
                ];
            }

            return array_map(function ($item) use ($catMap) {
                return $this->mapProduct($item, $catMap);
            }, $items);
        });

        // Index in runtime memory for instant lookups
        foreach ($products as $item) {
            self::$runtimeCache["prod_by_id_{$id}_{$item->id}"] = $item;
            if (!empty($item->slug)) {
                self::$runtimeCache["prod_by_slug_{$id}_{$item->slug}"] = $item;
            }
        }

        self::$runtimeCache[$runtimeKey] = $products;
        return $products;
    }

    public function getProductById(int $id, ?int $storeId = null): ?object
    {
        $storeId = $storeId ?? $this->storeId;
        $key = "prod_by_id_{$storeId}_{$id}";
        if (isset(self::$runtimeCache[$key])) {
            return self::$runtimeCache[$key];
        }

        $this->getAllMappedProducts($storeId);

        return self::$runtimeCache[$key] ?? null;
    }

    public function getProductBySlugId(string $slug, int $id, ?int $storeId = null): ?object
    {
        $storeId = $storeId ?? $this->storeId;

        // O(1) Lookup by ID
        if ($id > 0) {
            $item = $this->getProductById($id, $storeId);
            if ($item) return $item;
        }

        // O(1) Lookup by Slug
        if ($slug !== '') {
            $slugKey = "prod_by_slug_{$storeId}_{$slug}";
            if (isset(self::$runtimeCache[$slugKey])) {
                return self::$runtimeCache[$slugKey];
            }

            $this->getAllMappedProducts($storeId);
            if (isset(self::$runtimeCache[$slugKey])) {
                return self::$runtimeCache[$slugKey];
            }
        }

        return null;
    }

    public function getProducts(?int $storeId = null, int $page = 1, array $extra = []): LengthAwarePaginator
    {
        $id  = $storeId ?? $this->storeId;
        $all = $this->getAllMappedProducts($id);

        if (!empty($extra['category_id'])) {
            $catIdStr = (string) $extra['category_id'];
            $all = array_values(array_filter($all, function ($p) use ($catIdStr) {
                $cids = array_map('strval', (array) ($p->category_ids ?? []));
                return in_array($catIdStr, $cids, true);
            }));
        }

        $perPage  = (int) ($extra['per_page'] ?? 28);
        $total    = count($all);
        $items    = array_slice($all, max(0, ($page - 1) * $perPage), $perPage);
        $currPage = $page;

        return new LengthAwarePaginator($items, $total, $perPage, $currPage, [
            'path'     => Paginator::resolveCurrentPath(),
            'pageName' => 'page',
        ]);
    }

    public function getProductsByCategory(int $catId, ?int $storeId = null, int $page = 1): LengthAwarePaginator
    {
        $id = $storeId ?? $this->storeId;
        $cacheKey = "api_cat_items_{$id}_{$catId}";

        $matched = Cache::remember($cacheKey, $this->cacheTtl, function () use ($id, $catId) {
            $all = $this->getAllMappedProducts($id);
            $catIdStr = (string) $catId;

            return array_values(array_filter($all, function ($item) use ($catIdStr) {
                $catIds = array_map('strval', (array) ($item->category_ids ?? []));
                return in_array($catIdStr, $catIds, true);
            }));
        });

        $perPage = 20;
        $total   = count($matched);
        $items   = array_slice($matched, max(0, ($page - 1) * $perPage), $perPage);

        return new LengthAwarePaginator($items, $total, $perPage, $page, [
            'path'     => Paginator::resolveCurrentPath(),
            'pageName' => 'page',
        ]);
    }

    public function searchProducts(string $keyword, ?int $storeId = null, int $page = 1, ?int $categoryId = null): LengthAwarePaginator
    {
        $id = $storeId ?? $this->storeId;
        $keyword = trim($keyword);
        $cacheKey = 'api_search_' . $id . '_' . ($categoryId ?? 'all') . '_' . md5(mb_strtolower($keyword));

        $matched = Cache::remember($cacheKey, $this->cacheTtl, function () use ($id, $keyword, $categoryId) {
            $all = $this->getAllMappedProducts($id);
            $words = $keyword !== '' ? array_filter(explode(' ', mb_strtolower($keyword))) : [];

            $filtered = [];
            foreach ($all as $item) {
                if ($categoryId !== null) {
                    $catIds = array_map('strval', (array) ($item->category_ids ?? []));
                    if (!in_array((string) $categoryId, $catIds, true)) continue;
                }

                if (empty($words)) {
                    $filtered[] = $item;
                    continue;
                }

                $haystack = mb_strtolower($item->name . ' ' . $item->slug . ' ' . ($item->description ?? '') . ' ' . ($item->product_tag ?? ''));
                $hit = false;
                foreach ($words as $w) {
                    if (mb_strpos($haystack, $w) !== false) {
                        $hit = true;
                        break;
                    }
                }
                if ($hit) {
                    $filtered[] = $item;
                }
            }

            if (count($words) > 1 && count($filtered) > 1) {
                usort($filtered, function ($a, $b) use ($words) {
                    $ha = mb_strtolower($a->name . ' ' . $a->slug);
                    $hb = mb_strtolower($b->name . ' ' . $b->slug);
                    $sa = 0; foreach ($words as $w) { if (mb_strpos($ha, $w) !== false) $sa++; }
                    $sb = 0; foreach ($words as $w) { if (mb_strpos($hb, $w) !== false) $sb++; }
                    return $sb <=> $sa;
                });
            }

            return $filtered;
        });

        $perPage = 20;
        $total   = count($matched);
        $items   = array_slice($matched, max(0, ($page - 1) * $perPage), $perPage);

        return new LengthAwarePaginator($items, $total, $perPage, $page, [
            'path'     => Paginator::resolveCurrentPath(),
            'pageName' => 'page',
        ]);
    }

    public function getHotDeals(?int $storeId = null, int $limit = 42): array
    {
        $id = $storeId ?? $this->storeId;
        $cacheKey = "api_hot_deals_{$id}_{$limit}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($id, $limit) {
            $all = $this->getAllMappedProducts($id);
            $deals = [];
            foreach ($all as $prod) {
                if ($prod->sale_price > 0) {
                    $deals[] = $prod;
                    if (count($deals) >= $limit) break;
                }
            }
            return $deals;
        });
    }

    /**
     * Resolve related product IDs while preserving the order returned by the API.
     */
    public function getRelatedProducts(array $productIds, int $excludeId = 0, ?int $storeId = null): Collection
    {
        $id = $storeId ?? $this->storeId;
        $ids = collect($productIds)
            ->map(function ($pid) {
                if (is_array($pid)) {
                    $pid = $pid['id'] ?? null;
                } elseif (is_object($pid)) {
                    $pid = $pid->id ?? null;
                }

                return filter_var($pid, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
            })
            ->filter(fn ($pid) => $pid !== null && $pid !== $excludeId)
            ->unique()
            ->values();

        return $ids
            ->map(fn (int $pid) => $this->getProductById($pid, $id))
            ->filter()
            ->values();
    }

    public function getShippingMethods(?int $storeId = null): array
    {
        $id = $storeId ?? $this->storeId;
        $runtimeKey = "shipping_methods_{$id}";
        if (isset(self::$runtimeCache[$runtimeKey])) {
            return self::$runtimeCache[$runtimeKey];
        }

        $cacheKey = "api_shipping_methods_mapped_{$id}";

        $methods = Cache::remember($cacheKey, $this->cacheTtl, function () use ($id) {
            $res = $this->cachedRequest('/api/v1/app/shipping-methods/' . $id);
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
        });

        self::$runtimeCache[$runtimeKey] = $methods;
        return $methods;
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
        $id = $storeId ?? $this->storeId;
        $runtimeKey = "reviews_{$id}";

        if (!isset(self::$runtimeCache[$runtimeKey])) {
            $cacheKey = "api_reviews_mapped_{$id}";
            self::$runtimeCache[$runtimeKey] = Cache::remember($cacheKey, $this->cacheTtl, function () use ($id) {
                $res = $this->cachedRequest("/api/v1/app/reviews/{$id}");
                $items = $res['data'] ?? [];

                if (isset($items['data']) && is_array($items['data'])) {
                    $items = $items['data'];
                }

                return collect(is_array($items) ? $items : [])
                    ->filter(fn (array $review) => (int) ($review['status'] ?? 1) === 1)
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
                            'review'     => $review['summary'] ?? '',
                            'status'     => (int) ($review['status'] ?? 1),
                            'image'      => $resolvedImages,
                            'created_at' => $review['created_at'] ?? null,
                            'updated_at' => $review['updated_at'] ?? null,
                        ];
                    })
                    ->values();
            });
        }

        $allReviews = self::$runtimeCache[$runtimeKey];
        if ($productId !== null) {
            return $allReviews->filter(fn ($r) => (int) ($r->product_id ?? 0) === $productId)->values();
        }

        return $allReviews;
    }

    public function clearReviewsCache(?int $storeId = null): bool
    {
        $storeId = $storeId ?? $this->storeId;
        Cache::forget('api_' . md5("/api/v1/app/reviews/{$storeId}" . serialize([])));
        Cache::forget('api_' . md5("/api/v1/app/reviews/{$storeId}"));
        Cache::forget("api_reviews_mapped_{$storeId}");
        unset(self::$runtimeCache["reviews_{$storeId}"]);
        return true;
    }

    /**
     * Map raw product API response to a standard PHP object.
     */
    public function mapProduct(array $item, array $categoriesById = []): object
    {
        $id      = (int) ($item['id'] ?? 0);
        $sku     = $item['sku'] ?? '';
        $catIds  = is_string($item['category_ids'] ?? null) ? json_decode($item['category_ids'], true) : ($item['category_ids'] ?? []);
        $catIds  = (array) $catIds;
        $img     = $this->resolveMediaUrl($item['image'] ?? null);

        $gallery = $item['image_gallery'] ?? $item['gallery_images'] ?? [];
        if (is_string($gallery)) $gallery = json_decode($gallery, true) ?? [];
        $gallery = array_values(array_map(fn($g) => $this->resolveMediaUrl($g), (array) $gallery));

        $category = $item['get_category'] ?? null;
        if (is_array($category) && isset($category['category']) && is_array($category['category'])) {
            $category = $category['category'];
        }
        if (!is_array($category) && !empty($item['categories']) && is_array($item['categories'])) {
            $category = $item['categories'][0] ?? null;
        }
        if (!is_array($category) && !empty($catIds)) {
            $targetCatId = (string) $catIds[0];
            if (isset($categoriesById[$targetCatId])) {
                $category = $categoriesById[$targetCatId];
            } else {
                foreach ($this->getCategories() as $candidate) {
                    if ((string) $candidate->id === $targetCatId) {
                        $category = [
                            'id'            => $candidate->id,
                            'category_name' => $candidate->name,
                            'name'          => $candidate->name,
                            'slug'          => $candidate->slug,
                            'image'         => $candidate->image,
                        ];
                        break;
                    }
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

    /**
     * Receive and store product image upload from API request.
     */
    public function uploadProductImage(Request $request)
    {
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

            if (is_file(public_path($path))) {
                $removeOldImage();
                return response()->json([
                    'success' => true,
                    'url' => asset($path)
                ]);
            }

            $res = Http::withoutVerifying()->timeout(15)->get($url);
            if (!$res->successful() || !$res->body()) {
                return response()->json(['error' => 'Download failed'], 422);
            }

            $dir = dirname($path) !== '.' ? dirname($path) : 'uploads';
            $uploadDir = public_path($dir);
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

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
