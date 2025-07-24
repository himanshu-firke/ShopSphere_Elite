<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cart Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can configure various settings for the shopping cart functionality.
    |
    */

    // Tax rate (10% by default)
    'tax_rate' => env('CART_TAX_RATE', 0.10),

    // Base shipping rate
    'base_shipping_rate' => env('CART_BASE_SHIPPING_RATE', 10.00),

    // Weight-based shipping multiplier
    'weight_shipping_multiplier' => env('CART_WEIGHT_SHIPPING_MULTIPLIER', 0.50),

    // Maximum items per product in cart
    'max_quantity_per_item' => env('CART_MAX_QUANTITY_PER_ITEM', 99),

    // Cart expiration time for guest users (in hours)
    'guest_cart_expiration' => env('CART_GUEST_EXPIRATION_HOURS', 72),

    // Whether to merge cart items when user logs in
    'merge_cart_on_login' => env('CART_MERGE_ON_LOGIN', true),

    // Whether to keep cart items in stock when added to cart
    'reserve_stock_on_add' => env('CART_RESERVE_STOCK', false),

    // How long to reserve stock for (in minutes)
    'stock_reservation_time' => env('CART_STOCK_RESERVATION_MINUTES', 15),

    // Maximum number of items in wishlist
    'max_wishlist_items' => env('CART_MAX_WISHLIST_ITEMS', 100),
]; 