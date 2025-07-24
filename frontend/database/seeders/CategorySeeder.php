<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Electronics',
                'description' => 'Latest electronic gadgets and devices',
                'sort_order' => 1,
                'children' => [
                    [
                        'name' => 'Smartphones',
                        'description' => 'Mobile phones and accessories',
                        'sort_order' => 1,
                    ],
                    [
                        'name' => 'Laptops',
                        'description' => 'Portable computers and accessories',
                        'sort_order' => 2,
                    ],
                    [
                        'name' => 'Tablets',
                        'description' => 'Tablet computers and accessories',
                        'sort_order' => 3,
                    ],
                    [
                        'name' => 'Accessories',
                        'description' => 'Electronic accessories and peripherals',
                        'sort_order' => 4,
                    ],
                ]
            ],
            [
                'name' => 'Fashion',
                'description' => 'Clothing, shoes, and fashion accessories',
                'sort_order' => 2,
                'children' => [
                    [
                        'name' => 'Men\'s Clothing',
                        'description' => 'Clothing for men',
                        'sort_order' => 1,
                    ],
                    [
                        'name' => 'Women\'s Clothing',
                        'description' => 'Clothing for women',
                        'sort_order' => 2,
                    ],
                    [
                        'name' => 'Kids\' Clothing',
                        'description' => 'Clothing for children',
                        'sort_order' => 3,
                    ],
                    [
                        'name' => 'Shoes',
                        'description' => 'Footwear for all ages',
                        'sort_order' => 4,
                    ],
                ]
            ],
            [
                'name' => 'Home & Garden',
                'description' => 'Home improvement and garden supplies',
                'sort_order' => 3,
                'children' => [
                    [
                        'name' => 'Furniture',
                        'description' => 'Home and office furniture',
                        'sort_order' => 1,
                    ],
                    [
                        'name' => 'Kitchen & Dining',
                        'description' => 'Kitchen appliances and dining items',
                        'sort_order' => 2,
                    ],
                    [
                        'name' => 'Garden Tools',
                        'description' => 'Tools and equipment for gardening',
                        'sort_order' => 3,
                    ],
                ]
            ],
            [
                'name' => 'Sports & Outdoors',
                'description' => 'Sports equipment and outdoor gear',
                'sort_order' => 4,
                'children' => [
                    [
                        'name' => 'Fitness',
                        'description' => 'Fitness equipment and accessories',
                        'sort_order' => 1,
                    ],
                    [
                        'name' => 'Outdoor Recreation',
                        'description' => 'Camping and outdoor activities',
                        'sort_order' => 2,
                    ],
                    [
                        'name' => 'Team Sports',
                        'description' => 'Equipment for team sports',
                        'sort_order' => 3,
                    ],
                ]
            ],
            [
                'name' => 'Books & Media',
                'description' => 'Books, movies, and digital media',
                'sort_order' => 5,
                'children' => [
                    [
                        'name' => 'Books',
                        'description' => 'Physical and digital books',
                        'sort_order' => 1,
                    ],
                    [
                        'name' => 'Movies & TV',
                        'description' => 'DVDs, Blu-rays, and streaming content',
                        'sort_order' => 2,
                    ],
                    [
                        'name' => 'Music',
                        'description' => 'CDs, vinyl, and digital music',
                        'sort_order' => 3,
                    ],
                ]
            ],
        ];

        foreach ($categories as $categoryData) {
            $children = $categoryData['children'] ?? [];
            unset($categoryData['children']);

            $category = Category::create([
                'name' => $categoryData['name'],
                'slug' => Str::slug($categoryData['name']),
                'description' => $categoryData['description'],
                'sort_order' => $categoryData['sort_order'],
                'is_active' => true,
            ]);

            foreach ($children as $childData) {
                Category::create([
                    'name' => $childData['name'],
                    'slug' => Str::slug($childData['name']),
                    'description' => $childData['description'],
                    'sort_order' => $childData['sort_order'],
                    'parent_id' => $category->id,
                    'is_active' => true,
                ]);
            }
        }
    }
} 