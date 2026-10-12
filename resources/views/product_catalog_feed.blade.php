<?php echo '<?xml version="1.0"?>'; ?>
<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">
    <channel>
        <title>{{env('APP_NAME')}}</title>
        <link>{{env('APP_URL')}}</link>
        <description>All Products from {{env('APP_NAME')}}</description>
        @if(count($products)>0)
            @foreach($products as $product)
                <item>
                    <g:id>{{$product->id??null}}</g:id>
                    <g:title>{{$product->name??null}}</g:title>
                    <g:description>
                        {{ \Illuminate\Support\Str::words($product->description ?? '', 150, '...') }}
                    </g:description>
                    <g:link>{{route('product.details',[$product->slug,$product->id])}}</g:link>
                    <g:image_link>{{$product->image?asset($product->get_image->file_url):""}}</g:image_link>
                    <g:brand>{{env('APP_NAME')}}</g:brand>
                    <g:condition>new</g:condition>
                    <g:availability>in stock</g:availability>
                    <g:price>{{$product->sale_price>0?$product->sale_price:$product->price}} BDT</g:price>
                </item>
            @endforeach
        @endif
    </channel>
</rss>
