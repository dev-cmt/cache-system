<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Services\AppApiService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::defaultView('pagination::bootstrap-4');
        Paginator::defaultSimpleView('pagination::simple-bootstrap-4');

        View::composer('*', function ($view) {
            $api        = new AppApiService();
            $store      = [];
            $categories = collect([]);

            try {
                $store      = $api->getStore() ?? [];
                $topLevel   = collect($api->getCategories());

                // Build a flat collection: parent + all children so blade can
                // do ->where('parent_id', null) and ->where('parent_id', $id) etc.
                $flat = collect();
                foreach ($topLevel as $cat) {
                    $flat->push($cat);
                    foreach ($cat->children as $child) {
                        $flat->push($child);
                    }
                }
                $categories = $flat;
            } catch (\Throwable $e) {}

            $web_config = (object) [
                'name'                   => $store['name'] ?? 'Store',
                'site_name'              => $store['name'] ?? 'Store',
                'website_phone'          => $store['phone'] ?? null,
                'website_phone2'         => $store['phone2'] ?? null,
                'website_phone3'         => $store['phone3'] ?? null,
                'website_address'        => $store['address'] ?? null,
                'marquee_text'           => $store['marquee_text'] ?? null,
                'website_copyright_text' => $store['copyright_text'] ?? ('&copy; ' . date('Y') . ' ' . ($store['name'] ?? 'Store')),
                'currency_sign'          => $store['currency_sign'] ?? '৳',
                'fb_pixel'               => null,
                'get_logo'               => !empty($store['logo_url']) ? (object) ['file_url' => $store['logo_url']] : null,
            ];

            // Build fb_pixel HTML from the fb_pixels array returned by API
            $fbPixelHtml = '';
            foreach (($store['fb_pixels'] ?? []) as $pixel) {
                if (!empty($pixel['pixel_id']) && ($pixel['status'] ?? '0') === '1') {
                    $pid = htmlspecialchars($pixel['pixel_id']);
                    $fbPixelHtml .= "<!-- Facebook Pixel: {$pid} -->";
                }
            }
            if ($fbPixelHtml) {
                $web_config->fb_pixel = $fbPixelHtml;
            }

            $view->with('web_settings', $web_config);
            $view->with('categories', $categories);
            $view->with('store', $store);
            $view->with('globalSetting', [
                'fb_pixels'        => $store['fb_pixels'] ?? [],
                'google_trackings' => $store['google_trackings'] ?? [],
            ]);
        });
    }
}
