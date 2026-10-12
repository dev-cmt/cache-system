<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Services\AppApiService;

class HomeController extends Controller
{
    protected AppApiService $api;

    public function __construct(AppApiService $api)
    {
        $this->api = $api;
    }

    /**
     * Home Page — Storefront
     */
    public function index(Request $request)
    {
        return view('index', [
            'categories' => collect($this->api->getCategories()),
            'sliders'    => collect($this->api->getSliders()),
            'products'   => $this->api->getProducts(null, (int) $request->get('page', 1)),
            'hot_deal'   => collect($this->api->getHotDeals()),
        ]);
    }

    /**
     * Single Category Page
     */
    public function getCategory(Request $request, $id)
    {
        $category = $this->api->getCategoryById((int) $id);
        $title    = $category ? ($category->category_name ?? $category->name ?? 'Category') : 'Category';

        return view('single_category', [
            'data'       => $this->api->getProductsByCategory((int) $id, null, (int) $request->get('page', 1)),
            'cat_name'   => $title,
            'title'      => $title,
            'breadcrumb' => $title,
            'subtitle'   => "{$title} ক্যাটাগরির সকল সেরা কালেকশন",
        ]);
    }

    /**
     * All Hot Deals
     */
    public function allHotDeals(Request $request)
    {
        $deals = $this->api->getHotDeals();
        $page = (int) $request->get('page', 1);
        $perPage = 20;
        $total = count($deals);
        $items = array_slice($deals, ($page - 1) * $perPage, $perPage);

        $paginator = new LengthAwarePaginator($items, $total, $perPage, $page, [
            'path'     => Paginator::resolveCurrentPath(),
            'pageName' => 'page',
        ]);

        return view('all_hot_deals', [
            'data'        => $paginator,
            'title'       => '🔥 সব হট ডিলস (Special Offers)',
            'breadcrumb'  => 'হট ডিলস / All Hot Deals',
            'subtitle'    => 'সেরা মূল্যে আকর্ষণীয় সকল ডিসকাউন্ট ও অফারসমূহ',
            'empty_icon'  => 'fa-fire',
            'empty_title' => 'বর্তমানে কোনো অফার নেই',
        ]);
    }

    /**
     * Search Products
     */
    public function search(Request $request)
    {
        $query      = (string) $request->input('query', '');
        $categoryId = $request->filled('category') ? (int) $request->input('category') : null;
        $page       = (int) $request->get('page', 1);

        return view('searched_products', [
            'data'        => $this->api->searchProducts($query, null, $page, $categoryId),
            'query'       => $query,
            'category_id' => $categoryId,
            'title'      => "🔍 সার্চ ফলাফল: \"{$query}\"",
            'breadcrumb' => "সার্চ ফলাফল / \"{$query}\"",
            'subtitle'   => 'আপনার অনুসন্ধান অনুযায়ী প্রাপ্ত ফলাফল',
            'empty_desc' => 'আপনার অনুসন্ধানের সাথে মিলে এমন কোনো পণ্য পাওয়া যায়নি।',
        ]);
    }

    /**
     * AJAX Search Card Suggestions
     */
    public function ajaxSearch(Request $request)
    {
        $query      = trim((string) $request->input('query', ''));
        $categoryId = $request->input('category') ? (int) $request->input('category') : null;

        if (mb_strlen($query) < 1) {
            return response()->json(['results' => [], 'total' => 0]);
        }

        $paginator = $this->api->searchProducts($query, null, 1, $categoryId);
        $items = array_slice($paginator->items(), 0, 6);

        $results = array_map(function ($p) {
            $img = $p->image_url ?? ($p->get_thumb ? asset($p->get_thumb->file_url) : asset('frontEnd/images/no_image.png'));
            $price = (float) ($p->price ?? 0);
            $salePrice = (float) ($p->sale_price ?? 0);
            $url = route('product.details', [$p->slug ?: 'product', $p->id]);
            return [
                'id'         => $p->id,
                'name'       => $p->name,
                'slug'       => $p->slug,
                'image'      => $img,
                'price'      => $price,
                'sale_price' => $salePrice,
                'url'        => $url,
            ];
        }, $items);

        return response()->json([
            'results'      => $results,
            'total'        => method_exists($paginator, 'total') ? $paginator->total() : count($results),
            'view_all_url' => route('search', array_filter(['query' => $query, 'category' => $categoryId])),
        ]);
    }

    /**
     * Single Product Page
     */
    public function productDetails($slug, $id)
    {
        $data = $this->api->getProductBySlugId($slug, (int) $id);
        abort_if(!$data, 404);

        $qty = \Cart::get($id)->quantity ?? 1;
        $price = (float) number_format($data->sale_price ?: $data->price, 2, '.', '');

        session()->put('api_view_item_data', [
            'value'    => $price,
            'products' => json_encode([['item_id' => (string) $data->id, 'item_name' => $data->name, 'price' => $price, 'quantity' => (int) $qty]])
        ]);

        $reviews = $this->api->getReviews((int) $data->id);
        $data->get_reviews = $reviews;
        $relatedProductIds = $data->related_products ?? [];
        if (is_string($relatedProductIds)) {
            $relatedProductIds = json_decode($relatedProductIds, true) ?: [];
        }

        return view('single_product', [
            'data'             => $data,
            'product_reviews'  => $reviews,
            'related_prod'     => $this->api->getRelatedProducts((array) $relatedProductIds, (int) $data->id),
            'feature_prod'     => collect(array_slice($this->api->getProducts()->items(), 0, 3)),
            'shipping_methods' => collect($this->api->getShippingMethods()),
            'qty'              => $qty,
        ]);
    }

    /**
     * Add to Cart
     */
    public function addCart(Request $request, $id)
    {
        $product = $this->api->getProductBySlugId('', (int) $request->id);
        if (!$product) return back()->with('error', 'Product not found');

        $choiceAttrs = trim((string) $request->input('choice_attributes', ''));
        $variantValue = (string) $request->input('variant_value', '');
        $price = $product->sale_price > 0 ? $product->sale_price : $product->price;
        $variant = null;

        if ($choiceAttrs) {
            $variantKey = strtolower(str_replace([' ', ','], ['_', '-'], $choiceAttrs));
            $variantIds = array_values(array_filter(array_map('strval', explode(',', $variantValue))));
            $variant = $product->get_attributes->first(function ($item) use ($variantKey, $variantIds) {
                $sku = strtolower($item->variant_sku ?? '');
                $suf = strtolower($item->variant ?? '');
                $itemIds = json_decode($item->attribute_item_ids ?? '[]', true) ?: [];
                return ($variantIds && $itemIds && array_map('strval', $itemIds) === $variantIds)
                    || $suf === $variantKey
                    || $sku === $variantKey
                    || \Illuminate\Support\Str::endsWith($sku, "-$variantKey");
            });
        } elseif ($product->choice_attributes && $product->has_variant == 1) {
            $variant = $product->get_attributes->first();
        }

        // Variant Text Generation
        $selectedAttributes = (array) $request->input('attribute_item_id', []);
        $variantText = collect((array) $product->attributes)->map(function ($attribute) use ($selectedAttributes) {
            $attribute = (object) $attribute;
            $value = $selectedAttributes[$attribute->attribute_id] ?? '';
            $value = is_array($value) ? reset($value) : $value;
            return $attribute->attribute_name && $value ? $attribute->attribute_name . ': ' . $value : null;
        })->filter()->implode(', ');
        $variantText = $variantText ?: ($choiceAttrs ? str_replace(',', '-', $choiceAttrs) : ($variant->variant ?? null));

        $imageUrl = $product->image_url ?? ($product->get_thumb ? asset($product->get_thumb->file_url) : null);
        $slug     = $product->slug ?? '';
        $sku      = $variant->variant_sku ?? ($product->sku ?? '');
        $price    = $variant->price ?? $price;
        $quantity = (int) ($request->qty ?? 1);

        $attributes = [
            'sku'           => $sku,
            'variant'       => $variantText,
            'variant_value' => $variantValue ?: $variantText,
            'slug'          => $slug,
            'image'         => $imageUrl,
        ];

        if (\Cart::get($id)) {
            \Cart::update($id, ['quantity' => ['relative' => false, 'value' => $quantity], 'attributes' => $attributes]);
        } else {
            \Cart::add([
                'id'              => $product->id,
                'name'            => $product->name,
                'price'           => $price,
                'quantity'        => $quantity,
                'attributes'      => $attributes,
                'associatedModel' => (object) [
                    'id'        => (int) $product->id,
                    'name'      => $product->name,
                    'slug'      => $slug,
                    'sku'       => $sku,
                    'image'     => $imageUrl,
                    'get_thumb' => (object) ['file_url' => $product->image_url ?? ($product->get_thumb->file_url ?? '')],
                ]
            ]);
        }

        if ($request->order_now) {
            return redirect()->route('checkout')->with('success', 'Product Added Into Cart Successfully');
        }

        if ($request->add_cart) {
            session()->put('api_add_to_cart_data', [
                'value'    => (float) number_format($price, 2, '.', ''),
                'products' => json_encode([['item_id' => (string) $product->id, 'item_name' => $product->name, 'price' => $price, 'quantity' => $quantity]])
            ]);
            return back()->with(['success' => 'Product Added Into Cart Successfully', 'api_add_to_cart' => 'yes']);
        }

        return back()->with('error', 'Something Went Wrong!');
    }

    public function cartItemDelete($id, Request $request)
    {
        \Cart::remove($id);
        if ($request->ajax() || $request->wantsJson() || $request->has('ajax')) {
            return response()->json([
                'success'    => true,
                'cart_count' => \Cart::getContent()->count(),
                'subtotal'   => \Cart::getSubTotal(),
                'total'      => \Cart::getTotal(),
                'html'       => view('inc.order_summary')->render(),
            ]);
        }
        return back()->with('success', 'Cart Item Deleted Successfully');
    }

    public function cartItemPlus($id, Request $request)
    {
        if (\Cart::getContent()->count() > 0) \Cart::update($id, ['quantity' => 1]);
        $item = \Cart::get($id);
        if ($request->ajax() || $request->wantsJson() || $request->has('ajax')) {
            return response()->json([
                'success'    => true,
                'cart_count' => \Cart::getContent()->count(),
                'subtotal'   => \Cart::getSubTotal(),
                'total'      => \Cart::getTotal(),
                'item_qty'   => $item ? $item->quantity : 0,
                'item_sum'   => $item ? $item->getPriceSum() : 0,
                'html'       => view('inc.order_summary')->render(),
            ]);
        }
        return back();
    }

    public function cartItemMinus($id, Request $request)
    {
        if (\Cart::getContent()->count() > 0) {
            $item = \Cart::get($id);
            if ($item && $item->quantity > 1) {
                \Cart::update($id, ['quantity' => -1]);
            }
        }
        $item = \Cart::get($id);
        if ($request->ajax() || $request->wantsJson() || $request->has('ajax')) {
            return response()->json([
                'success'    => true,
                'cart_count' => \Cart::getContent()->count(),
                'subtotal'   => \Cart::getSubTotal(),
                'total'      => \Cart::getTotal(),
                'item_qty'   => $item ? $item->quantity : 0,
                'item_sum'   => $item ? $item->getPriceSum() : 0,
                'html'       => view('inc.order_summary')->render(),
            ]);
        }
        return back();
    }

    public function cartClear(Request $request)
    {
        \Cart::clear();
        if ($request->ajax() || $request->wantsJson() || $request->has('ajax')) {
            return response()->json([
                'success'    => true,
                'cart_count' => 0,
                'subtotal'   => 0,
                'total'      => 0,
                'html'       => view('inc.order_summary')->render(),
            ]);
        }
        return back();
    }

    public function getShippMeth(Request $request)
    {
        return response()->json($this->api->getShippingMethodAmount($request->id));
    }

    /**
     * Checkout Page
     */
    public function checkout()
    {
        $cart = \Cart::getContent();
        if ($cart->count() > 0) {
            $orderProd = [];
            $i = 0;
            foreach ($cart as $item) {
                $prodId = $item->id;
                if (!empty($item->associatedModel) && !($item->associatedModel instanceof \__PHP_Incomplete_Class)) {
                    $prodId = is_object($item->associatedModel) ? ($item->associatedModel->id ?? $item->id) : ($item->associatedModel['id'] ?? $item->id);
                }
                $orderProd[] = [
                    'index'     => $i++,
                    'item_id'   => (string) $prodId,
                    'item_name' => $item->name,
                    'price'     => (float) $item->price,
                    'quantity'  => (int) $item->quantity
                ];
            }
            session()->put('api_begin_checkout_data', [
                'value'    => (float) number_format(\Cart::getTotal(), 2, '.', ''),
                'products' => json_encode($orderProd)
            ]);
        }

        return view('checkout', [
            'shipping_methods' => collect($this->api->getShippingMethods()),
            'districts'        => [],
        ]);
    }

    /**
     * Place Order — Submits via API
     */
    public function placeOrder(Request $request)
    {
        $request->validate(['customer_phone' => 'required|min:11|max:11'], [
            'customer_phone.required' => 'অনুগ্রহ করে আপনার মোবাইল নাম্বারটি দিন',
            'customer_phone.min'      => 'আপনার ১১ সংখ্যার মোবাইল নাম্বারটি দিন',
            'customer_phone.max'      => 'আপনার ১১ সংখ্যার মোবাইল নাম্বারটি দিন',
        ]);

        $carts = \Cart::getContent();
        if ($carts->count() === 0) {
            return redirect()->route('home')->with('error', 'Please Select Products');
        }

        $invoice_id = 'OTH' . now()->format('dm') . strtoupper(Str::random(5));
        $orderProducts = [];
        $orderProd     = [];
        $i = 0;

        foreach ($carts as $item) {
            $sku = '';
            if (!empty($item->associatedModel) && !($item->associatedModel instanceof \__PHP_Incomplete_Class)) {
                $sku = is_object($item->associatedModel) ? ($item->associatedModel->sku ?? '') : ($item->associatedModel['sku'] ?? '');
            } elseif (isset($item->attributes['sku'])) {
                $sku = $item->attributes['sku'];
            }

            $orderProducts[] = [
                'product_id' => $item->id,
                'sku'        => $sku,
                'name'       => $item->name,
                'price'      => (float) $item->price,
                'qty'        => (int) $item->quantity,
                'attributes' => $item->attributes,
            ];
            $orderProd[] = [
                'index'     => $i++,
                'item_id'   => (string) $item->id,
                'item_name' => $item->name,
                'price'     => (float) number_format($item->price, 2, '.', ''),
                'quantity'  => (int) $item->quantity
            ];
        }

        $shippingCost = (float) ($request->shipping_cost ?? 0);
        $subTotal     = (float) \Cart::getSubTotal();
        $total        = (float) (\Cart::getTotal() + $shippingCost);

        $response = apiCall('api/v1/order/create', 'POST', [
            'session_id'       => $request->session()->getId(),
            'store_id'         => $this->api->getStoreId(),
            'invoice_id'       => $invoice_id,
            'customer_name'    => $request->customer_name,
            'customer_phone'   => $request->customer_phone,
            'customer_address' => $request->customer_address,
            'order_note'       => $request->order_note,
            'shipping_method'  => $request->shipping_method,
            'shipping_cost'    => $shippingCost,
            'sub_total'        => $subTotal,
            'total'            => $total,
            'ip_address'       => $request->ip(),
            'products'         => $orderProducts,
        ]);

        $responseData = $response['data'] ?? [];
        if (!isset($response['success']) || !$response['success']) {
            return redirect()->route('home')->with('error', $response['message'] ?? 'Failed to place order. Please try again.');
        }

        session()->put('api_purchase_data', [
            'customer_id'     => $responseData['customer_id'] ?? null,
            'full_name'       => $responseData['customer_name'] ?? null,
            'phone'           => $responseData['customer_phone'] ?? null,
            'email'           => $request->customer_email ?? null,
            'address_summary' => $responseData['customer_address'] ?? null,
            'invoice_id'      => $responseData['invoice_no'] ?? null,
            'sub_total'       => $responseData['sub_total'] ?? 0,
            'shipping_cost'   => $responseData['shipping_cost'] ?? 0,
            'products'        => json_encode($orderProd ?? []),
        ]);

        \Cart::clear();
        return redirect()->route('confirm.order')->with('success', 'Order Placed Successfully');
    }

    public function confirmOrder()
    {
        return view('order_confirmed');
    }

    public function confiirmOrder()
    {
        return view('order_confirmed');
    }

    /**
     * AJAX Get Variant Price, SKU & Image
     */
    public function ajaxGetVariant(Request $request)
    {
        $product  = $this->api->getProductBySlugId('', (int) $request->id);
        $variant  = '';
        $v        = '';
        $quantity = (int) ($request->quantity ?? 1);

        if ($request->choice_attributes) {
            foreach ($request->choice_attributes as $choice) {
                $v       .= '-' . str_replace(' ', '_', $choice);
                $variant .= '-' . $choice;
            }
            $v       = substr($v, 1);
            $variant = substr($variant, 1);

            $matched = null;
            if ($product && !empty($product->get_product_attributes)) {
                $matched = $product->get_product_attributes->first(function ($item) use ($v, $variant) {
                    $cleanV   = strtolower($v);
                    $cleanVar = strtolower($variant);
                    $sku      = strtolower($item->variant_sku ?? '');
                    $suf      = strtolower($item->variant ?? '');
                    return $suf === $cleanV || $suf === $cleanVar || $sku === $cleanV || \Illuminate\Support\Str::endsWith($sku, '-' . $cleanV) || \Illuminate\Support\Str::endsWith($sku, '-' . $cleanVar);
                });
            }

            $price   = $matched ? $matched->price : ($product ? ($product->sale_price ?: $product->price) : 0);
            $sku     = $matched ? $matched->sku : ($product ? $product->sku : '');
            $varImg  = $matched->variant_image ?? null;

            // If matched doesn't have variant_image, check attributes values for image
            if (!$varImg && $product && !empty($product->attributes)) {
                foreach ($product->attributes as $attr) {
                    $attr = (object) $attr;
                    if (!empty($attr->values)) {
                        foreach ($attr->values as $val) {
                            $val = (object) $val;
                            $valName = is_object($val) ? ($val->name ?? '') : (is_array($val) ? ($val['name'] ?? '') : $val);
                            if (in_array($valName, (array) ($request->choice_attributes ?? []), true)) {
                                $vImg = is_object($val) ? ($val->variant_image ?? $val->image ?? null) : null;
                                if ($vImg) {
                                    $varImg = $vImg;
                                    break 2;
                                }
                            }
                        }
                    }
                }
            }
        } else {
            $price   = $product ? ($product->sale_price ?: $product->price) : 0;
            $sku     = $product ? $product->sku : '';
            $varImg  = null;
        }

        return [
            'choice_attributes' => $variant,
            'subtotal'          => $price * $quantity,
            'price'             => $price,
            'sku'               => $sku,
            'variant_image'     => $varImg,
        ];
    }

    /**
     * Abandoned Cart via API
     */
    public function abandonedCart(Request $request)
    {
        $carts = \Cart::getContent();
        if ($carts->count() === 0) return;

        $items = [];
        foreach ($carts as $item) {
            $items[] = [
                'product_id' => $item->id,
                'name' => $item->name,
                'price' => $item->price,
                'quantity' => $item->quantity,
                'attributes' => $item->attributes
            ];
        }

        apiCall('/api/v1/incomplete/create', 'POST', [
            'session_id'       => $request->session()->getId(),
            'store_id'         => $this->api->getStoreId(),
            'customer_name'    => $request->data['name'] ?? null,
            'customer_phone'   => $request->data['phone'] ?? null,
            'customer_address' => $request->data['address'] ?? null,
            'shipping_cost'    => $request->data['shipping_cost'] ?? 0,
            'subtotal'         => \Cart::getSubTotal(),
            'total'            => \Cart::getTotal() + (float)($request->data['shipping_cost'] ?? 0),
            'items'            => $items,
        ]);
    }

    /**
     * Reviews
     */
    public function reviewStore(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id' => ['required', 'integer'],
                'name'       => ['required', 'string', 'max:255'],
                'email'      => ['nullable', 'email', 'max:255'],
                'rating'     => ['required', 'integer', 'min:1', 'max:5'],
                'summary'    => ['nullable', 'string'],
                'review'     => ['nullable', 'string'],
                'images.*'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            ]);

            $summary = trim((string) ($request->input('summary') ?: $request->input('review') ?: ''));

            $base64Images = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $base64Images[] = [
                        'name' => $file->getClientOriginalName(),
                        'data' => base64_encode(file_get_contents($file->getRealPath())),
                        'mime' => $file->getMimeType(),
                    ];
                }
            }

            $reviewData = [
                'store_id'   => $this->api->getStoreId(),
                'product_id' => (int) $validated['product_id'],
                'name'       => $validated['name'],
                'email'      => $validated['email'] ?? null,
                'summary'    => $summary,
                'rating'     => (int) $validated['rating'],
                'images'     => $base64Images,
            ];

            $response = apiCall('/api/v1/review/create', 'POST', $reviewData);

            $remoteSent = is_array($response) && !empty($response['success']);
            if (!$remoteSent) {
                $msg = is_array($response) && !empty($response['message']) ? $response['message'] : 'রিভিউ জমা দিতে সমস্যা হয়েছে। আবার চেষ্টা করুন।';
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 400);
            }

            $this->api->clearReviewsCache();

            return response()->json([
                'success'     => true,
                'message'     => 'আপনার রিভিউটি সফলভাবে জমা নেওয়া হয়েছে!',
                'remote_sent' => $remoteSent,
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function showReviews(Request $request)
    {
        $product = $this->api->getProductBySlugId('', (int) $request->id);
        abort_if(!$product, 404);

        $reviews = $this->api->getReviews((int) $product->id);
        $product->get_reviews = $reviews;

        return view('review_all', [
            'product' => $product,
            'reviews' => $reviews,
        ]);
    }

    public function productsCatalogFeed()
    {
        return response()->view('product_catalog_feed', [
            'products' => $this->api->getProducts(null, 1)->items(),
        ])->header('Content-Type', 'text/xml');
    }

    /**
     * Static CMS Pages
     */
    protected function getPageSetting(string $slug): string
    {
        $page = $this->api->getPage($slug) ?? [];

        return $page['content'] ?? $page['description'] ?? $page['body'] ?? '';
    }

    public function aboutUs()
    {
        $content = $this->getPageSetting('about-us');

        return view('pages.about_us', compact('content'));
    }

    public function returnPolicy()
    {
        $content = $this->getPageSetting('return-policy');

        return view('pages.return_policy', compact('content'));
    }

    public function deliveryPolicy()
    {
        $content = $this->getPageSetting('delivery-policy');

        return view('pages.delivery_policy', compact('content'));
    }

    public function contactUs()
    {
        return view('pages.contact_us');
    }
}
