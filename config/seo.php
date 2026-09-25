<?php

return [

    'site_name' => env('SEO_SITE_NAME', env('APP_NAME', 'Ridhi Sidhi Garments')),

    'default_title' => env(
        'SEO_DEFAULT_TITLE',
        'Ridhi Sidhi Garments — Elegant Womenswear Online India'
    ),

    'default_description' => env(
        'SEO_DEFAULT_DESCRIPTION',
        'Shop elegant ethnic and contemporary womenswear at Ridhi Sidhi Garments. Curated styles, pan-India delivery, secure payment and easy returns.'
    ),

    'default_keywords' => env(
        'SEO_DEFAULT_KEYWORDS',
        'womenswear online india, ethnic wear for women, kurtas, sarees, dresses, co-ords, Ridhi Sidhi Garments'
    ),

    'twitter_handle' => env('SEO_TWITTER_HANDLE', ''),

    'og_default_image' => env('SEO_OG_IMAGE', '/images/fashion-hero.svg'),

    'locale' => env('SEO_LOCALE', 'en_IN'),

    'robots' => env('SEO_ROBOTS', 'index,follow'),

    'google_site_verification' => env('SEO_GOOGLE_SITE_VERIFICATION', ''),

    'noindex_routes' => [
        'cart.*',
        'checkout.*',
        'orders.*',
        'dashboard',
        'account.*',
        'login',
        'register',
        'password.*',
        'verification.*',
    ],

    'pages' => [
        'home' => [
            'title' => 'Ridhi Sidhi Garments — Elegant Womenswear Online India',
            'description' => 'Discover curated kurtas, sarees, dresses and contemporary womenswear with pan-India delivery.',
            'keywords' => 'womenswear online india, ethnic wear, kurtas, sarees, dresses, Ridhi Sidhi Garments',
        ],
        'products' => [
            'title' => 'Shop Womenswear Online | Ridhi Sidhi Garments',
            'description' => 'Browse curated ethnic and contemporary clothing for women. Filter by category and discover your next favourite look.',
            'keywords' => 'shop womenswear, ethnic clothing online, kurtas sarees dresses india',
        ],
        'privacy-policy' => [
            'title' => 'Privacy Policy | Ridhi Sidhi Garments',
            'description' => 'Read how Ridhi Sidhi Garments collects, uses and protects your personal data.',
            'keywords' => 'Ridhi Sidhi Garments privacy policy, data protection',
        ],
        'terms-and-conditions' => [
            'title' => 'Terms & Conditions | Ridhi Sidhi Garments',
            'description' => 'Terms and conditions for shopping womenswear at Ridhi Sidhi Garments.',
            'keywords' => 'Ridhi Sidhi Garments terms and conditions',
        ],
        'shipping-policy' => [
            'title' => 'Shipping Policy | Ridhi Sidhi Garments',
            'description' => 'Shipping timelines, delivery partners, tracking and serviceable pincodes for Ridhi Sidhi Garments orders.',
            'keywords' => 'womenswear shipping india, Ridhi Sidhi Garments shipping policy',
        ],
        'return-refund-policy' => [
            'title' => 'Return & Refund Policy | Ridhi Sidhi Garments',
            'description' => 'Returns and refunds on eligible clothing purchased from Ridhi Sidhi Garments.',
            'keywords' => 'clothing return policy, Ridhi Sidhi Garments refund policy',
        ],
        'cancellation-policy' => [
            'title' => 'Cancellation Policy | Ridhi Sidhi Garments',
            'description' => 'How to cancel a Ridhi Sidhi Garments order before dispatch.',
            'keywords' => 'Ridhi Sidhi Garments order cancellation',
        ],
    ],

    'templates' => [
        'product_title' => '{name} — Buy Online at {site}',
        'product_description' => 'Buy {name} online at Ridhi Sidhi Garments. {category} · ₹{price}. Curated quality and delivery across India. {stock}',
        'product_keywords' => '{name}, buy {name} online, {category} india, {brand} womenswear, Ridhi Sidhi Garments',
        'category_title' => '{name} — Shop Online | Ridhi Sidhi Garments',
        'category_description' => 'Shop {name} online at Ridhi Sidhi Garments with pan-India delivery and secure payment.',
        'category_keywords' => '{name} online india, buy {name_lower}, womenswear, Ridhi Sidhi Garments',
    ],

];
