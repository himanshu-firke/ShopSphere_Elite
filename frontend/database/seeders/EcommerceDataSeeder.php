<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Str;

class EcommerceDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Categories
        $furniture = Category::create([
            'name' => 'Furniture',
            'slug' => 'furniture',
            'description' => 'Quality furniture for your home and office',
            'image' => '/images/categories/furniture.jpg',
            'is_active' => true,
            'meta_title' => 'Furniture Collection',
            'meta_description' => 'Browse our premium furniture collection'
        ]);

        $electronics = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'description' => 'Latest electronic gadgets and appliances',
            'image' => '/images/categories/electronics.jpg',
            'is_active' => true,
            'meta_title' => 'Electronics Store',
            'meta_description' => 'Find the best electronic devices'
        ]);

        $homeDecor = Category::create([
            'name' => 'Home Decor',
            'slug' => 'home-decor',
            'description' => 'Beautiful home decoration items',
            'image' => '/images/categories/home-decor.jpg',
            'is_active' => true,
            'meta_title' => 'Home Decor Items',
            'meta_description' => 'Decorate your home with style'
        ]);

        // Create Products
        $products = [
            [
                'name' => 'Modern Office Chair',
                'slug' => 'modern-office-chair',
                'description' => 'Comfortable ergonomic office chair with lumbar support and adjustable height. Perfect for long working hours.',
                'short_description' => 'Ergonomic office chair with lumbar support',
                'category_id' => $furniture->id,
                'price' => 6490.00,
                'sale_price' => 4990.00,
                'sku' => 'CHAIR-001',
                'stock_quantity' => 15,
                'is_active' => true,
                'is_featured' => true,
                'is_on_sale' => true,
                'weight' => 12.5,
                'meta_title' => 'Modern Office Chair - Ergonomic Design',
                'meta_description' => 'Buy comfortable office chair with lumbar support'
            ],
            [
                'name' => 'Luxurious 3-Seater Sofa',
                'slug' => 'luxurious-3-seater-sofa',
                'description' => 'Premium fabric sofa perfect for living room. High-quality materials and comfortable seating for the whole family.',
                'short_description' => 'Premium fabric sofa for living room',
                'category_id' => $furniture->id,
                'price' => 24990.00,
                'sale_price' => 19990.00,
                'sku' => 'SOFA-001',
                'stock_quantity' => 8,
                'is_active' => true,
                'is_featured' => true,
                'is_on_sale' => true,
                'weight' => 45.0,
                'meta_title' => 'Luxurious 3-Seater Sofa',
                'meta_description' => 'Premium quality sofa for your living room'
            ],
            [
                'name' => 'Smart LED TV 55 inch',
                'slug' => 'smart-led-tv-55-inch',
                'description' => '4K Ultra HD Smart LED TV with HDR support. Built-in WiFi, multiple streaming apps, and crystal clear picture quality.',
                'short_description' => '4K Ultra HD Smart LED TV with HDR',
                'category_id' => $electronics->id,
                'price' => 45990.00,
                'sale_price' => 39990.00,
                'sku' => 'TV-001',
                'stock_quantity' => 12,
                'is_active' => true,
                'is_featured' => true,
                'is_on_sale' => true,
                'weight' => 18.5,
                'meta_title' => 'Smart LED TV 55 inch - 4K Ultra HD',
                'meta_description' => 'Buy 4K Smart TV with HDR and streaming apps'
            ],
            [
                'name' => 'Wooden Coffee Table',
                'slug' => 'wooden-coffee-table',
                'description' => 'Elegant wooden coffee table made from premium teak wood. Perfect centerpiece for your living room.',
                'short_description' => 'Elegant wooden coffee table',
                'category_id' => $furniture->id,
                'price' => 8990.00,
                'sale_price' => 7490.00,
                'sku' => 'TABLE-001',
                'stock_quantity' => 10,
                'is_active' => true,
                'is_featured' => false,
                'is_on_sale' => true,
                'weight' => 25.0,
                'meta_title' => 'Wooden Coffee Table - Premium Teak',
                'meta_description' => 'Elegant teak wood coffee table for living room'
            ],
            [
                'name' => 'Bluetooth Wireless Headphones',
                'slug' => 'bluetooth-wireless-headphones',
                'description' => 'Premium wireless headphones with noise cancellation and 30-hour battery life. Crystal clear sound quality.',
                'short_description' => 'Wireless headphones with noise cancellation',
                'category_id' => $electronics->id,
                'price' => 2990.00,
                'sale_price' => 2490.00,
                'sku' => 'HEAD-001',
                'stock_quantity' => 25,
                'is_active' => true,
                'is_featured' => true,
                'is_on_sale' => true,
                'weight' => 0.3,
                'meta_title' => 'Bluetooth Wireless Headphones',
                'meta_description' => 'Premium wireless headphones with noise cancellation'
            ],
            [
                'name' => 'Decorative Wall Mirror',
                'slug' => 'decorative-wall-mirror',
                'description' => 'Beautiful decorative wall mirror with ornate frame. Adds elegance and space to any room.',
                'short_description' => 'Decorative wall mirror with ornate frame',
                'category_id' => $homeDecor->id,
                'price' => 3490.00,
                'sale_price' => null,
                'sku' => 'MIRROR-001',
                'stock_quantity' => 18,
                'is_active' => true,
                'is_featured' => false,
                'is_on_sale' => false,
                'weight' => 3.5,
                'meta_title' => 'Decorative Wall Mirror',
                'meta_description' => 'Beautiful wall mirror with ornate frame'
            ]
        ];

        foreach ($products as $productData) {
            $product = Product::create($productData);

            // Create product images
            $imageUrls = [
                'CHAIR-001' => ['/images/products/chair-1.jpg', '/images/products/chair-2.jpg'],
                'SOFA-001' => ['/images/products/sofa-1.jpg', '/images/products/sofa-2.jpg'],
                'TV-001' => ['/images/products/tv-1.jpg', '/images/products/tv-2.jpg'],
                'TABLE-001' => ['/images/products/table-1.jpg'],
                'HEAD-001' => ['/images/products/headphones-1.jpg'],
                'MIRROR-001' => ['/images/products/mirror-1.jpg']
            ];

            if (isset($imageUrls[$product->sku])) {
                foreach ($imageUrls[$product->sku] as $index => $imageUrl) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $imageUrl,
                        'alt_text' => $product->name,
                        'is_primary' => $index === 0, // First image is primary
                        'sort_order' => $index + 1
                    ]);
                }
            }
        }

        $this->command->info('Ecommerce data seeded successfully!');
        $this->command->info('Created:');
        $this->command->info('- ' . Category::count() . ' categories');
        $this->command->info('- ' . Product::count() . ' products');
        $this->command->info('- ' . ProductImage::count() . ' product images');
    }
}
