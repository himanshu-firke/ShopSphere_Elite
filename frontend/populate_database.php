<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

use Illuminate\Support\Facades\DB;

echo "=== POPULATING DATABASE WITH REAL DATA ===\n\n";

try {
    // Get category IDs
    $furnitureId = DB::table('categories')->where('slug', 'furniture')->value('id');
    $electronicsId = DB::table('categories')->where('slug', 'electronics')->value('id');
    $homeDecorId = DB::table('categories')->where('slug', 'home-decor')->value('id');

    echo "Categories found:\n";
    echo "- Furniture ID: $furnitureId\n";
    echo "- Electronics ID: $electronicsId\n";
    echo "- Home Decor ID: $homeDecorId\n\n";

    // Insert products directly
    $products = [
        [
            'name' => 'Modern Office Chair',
            'slug' => 'modern-office-chair',
            'description' => 'Comfortable ergonomic office chair with lumbar support and adjustable height. Perfect for long working hours.',
            'short_description' => 'Ergonomic office chair with lumbar support',
            'category_id' => $furnitureId,
            'price' => 6490.00,
            'sale_price' => 4990.00,
            'sku' => 'CHAIR-001',
            'stock_quantity' => 15,
            'is_active' => 1,
            'is_featured' => 1,
            'is_on_sale' => 1,
            'weight' => 12.5,
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'name' => 'Luxurious 3-Seater Sofa',
            'slug' => 'luxurious-3-seater-sofa',
            'description' => 'Premium fabric sofa perfect for living room. High-quality materials and comfortable seating for the whole family.',
            'short_description' => 'Premium fabric sofa for living room',
            'category_id' => $furnitureId,
            'price' => 24990.00,
            'sale_price' => 19990.00,
            'sku' => 'SOFA-001',
            'stock_quantity' => 8,
            'is_active' => 1,
            'is_featured' => 1,
            'is_on_sale' => 1,
            'weight' => 45.0,
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'name' => 'Smart LED TV 55 inch',
            'slug' => 'smart-led-tv-55-inch',
            'description' => '4K Ultra HD Smart LED TV with HDR support. Built-in WiFi, multiple streaming apps, and crystal clear picture quality.',
            'short_description' => '4K Ultra HD Smart LED TV with HDR',
            'category_id' => $electronicsId,
            'price' => 45990.00,
            'sale_price' => 39990.00,
            'sku' => 'TV-001',
            'stock_quantity' => 12,
            'is_active' => 1,
            'is_featured' => 1,
            'is_on_sale' => 1,
            'weight' => 18.5,
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'name' => 'Wooden Coffee Table',
            'slug' => 'wooden-coffee-table',
            'description' => 'Elegant wooden coffee table made from premium teak wood. Perfect centerpiece for your living room.',
            'short_description' => 'Elegant wooden coffee table',
            'category_id' => $furnitureId,
            'price' => 8990.00,
            'sale_price' => 7490.00,
            'sku' => 'TABLE-001',
            'stock_quantity' => 10,
            'is_active' => 1,
            'is_featured' => 0,
            'is_on_sale' => 1,
            'weight' => 25.0,
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'name' => 'Bluetooth Wireless Headphones',
            'slug' => 'bluetooth-wireless-headphones',
            'description' => 'Premium wireless headphones with noise cancellation and 30-hour battery life. Crystal clear sound quality.',
            'short_description' => 'Wireless headphones with noise cancellation',
            'category_id' => $electronicsId,
            'price' => 2990.00,
            'sale_price' => 2490.00,
            'sku' => 'HEAD-001',
            'stock_quantity' => 25,
            'is_active' => 1,
            'is_featured' => 1,
            'is_on_sale' => 1,
            'weight' => 0.3,
            'created_at' => now(),
            'updated_at' => now()
        ]
    ];

    // Insert products
    foreach ($products as $product) {
        $productId = DB::table('products')->insertGetId($product);
        echo "Created product: {$product['name']} (ID: $productId)\n";

        // Create product images
        $imageData = [
            'CHAIR-001' => '/images/products/chair-1.jpg',
            'SOFA-001' => '/images/products/sofa-1.jpg',
            'TV-001' => '/images/products/tv-1.jpg',
            'TABLE-001' => '/images/products/table-1.jpg',
            'HEAD-001' => '/images/products/headphones-1.jpg'
        ];

        if (isset($imageData[$product['sku']])) {
            DB::table('product_images')->insert([
                'product_id' => $productId,
                'image_path' => $imageData[$product['sku']],
                'alt_text' => $product['name'],
                'is_primary' => 1,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            echo "  - Added image: {$imageData[$product['sku']]}\n";
        }
    }

    echo "\n=== DATABASE POPULATION COMPLETE ===\n";
    echo "Categories: " . DB::table('categories')->count() . "\n";
    echo "Products: " . DB::table('products')->count() . "\n";
    echo "Product Images: " . DB::table('product_images')->count() . "\n";
    echo "\nYour MySQL database now contains real ecommerce data!\n";
    echo "You can view this data in phpMyAdmin or any MySQL client.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
