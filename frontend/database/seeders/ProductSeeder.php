<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create a vendor user
        $vendor = User::firstOrCreate(
            ['email' => 'vendor@example.com'],
            [
                'name' => 'Sample Vendor',
                'email' => 'vendor@example.com',
                'password' => bcrypt('password'),
                'role' => 'vendor',
                'email_verified_at' => now(),
            ]
        );

        $products = [
            [
                'name' => 'iPhone 15 Pro',
                'description' => 'The latest iPhone with advanced camera system and A17 Pro chip',
                'short_description' => 'Premium smartphone with cutting-edge features',
                'category' => 'Smartphones',
                'price' => 999.99,
                'compare_price' => 1099.99,
                'sku' => 'IPH15PRO-128',
                'stock_quantity' => 50,
                'is_featured' => true,
                'is_bestseller' => true,
                'is_new' => true,
                'tags' => ['smartphone', 'apple', 'iphone', '5g'],
            ],
            [
                'name' => 'MacBook Air M2',
                'description' => 'Ultra-thin laptop with M2 chip for exceptional performance',
                'short_description' => 'Lightweight laptop with powerful M2 processor',
                'category' => 'Laptops',
                'price' => 1199.99,
                'compare_price' => 1299.99,
                'sku' => 'MBA-M2-256',
                'stock_quantity' => 25,
                'is_featured' => true,
                'is_bestseller' => true,
                'tags' => ['laptop', 'apple', 'macbook', 'm2'],
            ],
            [
                'name' => 'Samsung Galaxy Tab S9',
                'description' => 'Premium Android tablet with S Pen and stunning display',
                'short_description' => 'High-performance tablet for productivity and entertainment',
                'category' => 'Tablets',
                'price' => 799.99,
                'compare_price' => 899.99,
                'sku' => 'SGT-S9-128',
                'stock_quantity' => 30,
                'is_featured' => true,
                'tags' => ['tablet', 'samsung', 'android', 's-pen'],
            ],
            [
                'name' => 'Wireless Bluetooth Headphones',
                'description' => 'Premium noise-cancelling headphones with 30-hour battery life',
                'short_description' => 'High-quality wireless headphones with noise cancellation',
                'category' => 'Accessories',
                'price' => 199.99,
                'compare_price' => 249.99,
                'sku' => 'WBH-PRO-001',
                'stock_quantity' => 100,
                'is_on_sale' => true,
                'sale_price' => 149.99,
                'tags' => ['headphones', 'wireless', 'bluetooth', 'noise-cancelling'],
            ],
            [
                'name' => 'Men\'s Casual T-Shirt',
                'description' => 'Comfortable cotton t-shirt perfect for everyday wear',
                'short_description' => 'Soft and breathable casual t-shirt',
                'category' => 'Men\'s Clothing',
                'price' => 24.99,
                'compare_price' => 29.99,
                'sku' => 'MCT-BLK-M',
                'stock_quantity' => 200,
                'is_on_sale' => true,
                'sale_price' => 19.99,
                'tags' => ['clothing', 'men', 't-shirt', 'casual'],
            ],
            [
                'name' => 'Women\'s Summer Dress',
                'description' => 'Elegant summer dress perfect for special occasions',
                'short_description' => 'Beautiful floral summer dress',
                'category' => 'Women\'s Clothing',
                'price' => 89.99,
                'compare_price' => 119.99,
                'sku' => 'WSD-FLR-M',
                'stock_quantity' => 75,
                'is_featured' => true,
                'tags' => ['clothing', 'women', 'dress', 'summer'],
            ],
            [
                'name' => 'Kids\' Running Shoes',
                'description' => 'Comfortable and durable running shoes for active kids',
                'short_description' => 'Lightweight running shoes for children',
                'category' => 'Shoes',
                'price' => 49.99,
                'compare_price' => 59.99,
                'sku' => 'KRS-BLK-6',
                'stock_quantity' => 150,
                'is_bestseller' => true,
                'tags' => ['shoes', 'kids', 'running', 'sports'],
            ],
            [
                'name' => 'Modern Coffee Table',
                'description' => 'Contemporary coffee table with storage shelf',
                'short_description' => 'Stylish coffee table for modern living rooms',
                'category' => 'Furniture',
                'price' => 299.99,
                'compare_price' => 399.99,
                'sku' => 'MCT-WAL-001',
                'stock_quantity' => 20,
                'is_featured' => true,
                'tags' => ['furniture', 'coffee-table', 'modern', 'storage'],
            ],
            [
                'name' => 'Kitchen Mixer Stand',
                'description' => 'Professional stand mixer for baking enthusiasts',
                'short_description' => 'Powerful kitchen mixer for all your baking needs',
                'category' => 'Kitchen & Dining',
                'price' => 399.99,
                'compare_price' => 499.99,
                'sku' => 'KMS-PRO-001',
                'stock_quantity' => 15,
                'is_bestseller' => true,
                'tags' => ['kitchen', 'mixer', 'baking', 'appliance'],
            ],
            [
                'name' => 'Yoga Mat Premium',
                'description' => 'Non-slip yoga mat with alignment lines',
                'short_description' => 'High-quality yoga mat for all types of yoga',
                'category' => 'Fitness',
                'price' => 39.99,
                'compare_price' => 49.99,
                'sku' => 'YM-PRE-001',
                'stock_quantity' => 300,
                'is_on_sale' => true,
                'sale_price' => 29.99,
                'tags' => ['fitness', 'yoga', 'mat', 'exercise'],
            ],
            [
                'name' => 'Camping Tent 4-Person',
                'description' => 'Spacious 4-person camping tent with weather protection',
                'short_description' => 'Durable camping tent for family adventures',
                'category' => 'Outdoor Recreation',
                'price' => 199.99,
                'compare_price' => 249.99,
                'sku' => 'CT-4P-001',
                'stock_quantity' => 40,
                'is_featured' => true,
                'tags' => ['camping', 'tent', 'outdoor', 'family'],
            ],
            [
                'name' => 'Bestselling Fiction Novel',
                'description' => 'Award-winning fiction novel that has captivated readers worldwide',
                'short_description' => 'Compelling story that will keep you reading all night',
                'category' => 'Books',
                'price' => 19.99,
                'compare_price' => 24.99,
                'sku' => 'BFN-001',
                'stock_quantity' => 500,
                'is_bestseller' => true,
                'is_new' => true,
                'tags' => ['books', 'fiction', 'bestseller', 'novel'],
            ],
        ];

        foreach ($products as $productData) {
            $categoryName = $productData['category'];
            $category = Category::where('name', $categoryName)->first();

            if (!$category) {
                continue; // Skip if category doesn't exist
            }

            $product = Product::create([
                'name' => $productData['name'],
                'slug' => Str::slug($productData['name']),
                'description' => $productData['description'],
                'short_description' => $productData['short_description'],
                'category_id' => $category->id,
                'vendor_id' => $vendor->id,
                'price' => $productData['price'],
                'compare_price' => $productData['compare_price'],
                'sku' => $productData['sku'],
                'stock_quantity' => $productData['stock_quantity'],
                'low_stock_threshold' => 10,
                'is_active' => true,
                'is_featured' => $productData['is_featured'] ?? false,
                'is_bestseller' => $productData['is_bestseller'] ?? false,
                'is_new' => $productData['is_new'] ?? false,
                'is_on_sale' => $productData['is_on_sale'] ?? false,
                'sale_price' => $productData['sale_price'] ?? null,
                'tags' => $productData['tags'],
                'weight' => rand(1, 10),
                'dimensions' => [
                    'length' => rand(10, 50),
                    'width' => rand(10, 30),
                    'height' => rand(5, 20),
                ],
            ]);
        }
    }
} 