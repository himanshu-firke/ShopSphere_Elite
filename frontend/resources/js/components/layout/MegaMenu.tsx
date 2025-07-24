import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { FiChevronDown } from 'react-icons/fi';

interface Category {
    name: string;
    slug: string;
    subcategories: {
        name: string;
        slug: string;
        items: {
            name: string;
            slug: string;
        }[];
    }[];
}

const categories: Category[] = [
    {
        name: 'Living Room',
        slug: 'living-room',
        subcategories: [
            {
                name: 'Seating',
                slug: 'seating',
                items: [
                    { name: 'Sofas', slug: 'sofas' },
                    { name: 'Recliners', slug: 'recliners' },
                    { name: 'Sofa Sets', slug: 'sofa-sets' },
                    { name: 'Chairs', slug: 'chairs' },
                ]
            },
            {
                name: 'Tables',
                slug: 'tables',
                items: [
                    { name: 'Coffee Tables', slug: 'coffee-tables' },
                    { name: 'Side Tables', slug: 'side-tables' },
                    { name: 'TV Units', slug: 'tv-units' },
                ]
            },
            {
                name: 'Storage',
                slug: 'storage',
                items: [
                    { name: 'Cabinets', slug: 'cabinets' },
                    { name: 'Bookshelves', slug: 'bookshelves' },
                    { name: 'Display Units', slug: 'display-units' },
                ]
            }
        ]
    },
    {
        name: 'Bedroom',
        slug: 'bedroom',
        subcategories: [
            {
                name: 'Beds',
                slug: 'beds',
                items: [
                    { name: 'Single Beds', slug: 'single-beds' },
                    { name: 'Double Beds', slug: 'double-beds' },
                    { name: 'Queen Size Beds', slug: 'queen-size-beds' },
                    { name: 'King Size Beds', slug: 'king-size-beds' },
                ]
            },
            {
                name: 'Storage',
                slug: 'storage',
                items: [
                    { name: 'Wardrobes', slug: 'wardrobes' },
                    { name: 'Chest of Drawers', slug: 'chest-of-drawers' },
                    { name: 'Bedside Tables', slug: 'bedside-tables' },
                ]
            },
            {
                name: 'Mattresses',
                slug: 'mattresses',
                items: [
                    { name: 'Spring Mattress', slug: 'spring-mattress' },
                    { name: 'Foam Mattress', slug: 'foam-mattress' },
                    { name: 'Coir Mattress', slug: 'coir-mattress' },
                ]
            }
        ]
    },
    // Add more categories as needed
];

const MegaMenu: React.FC = () => {
    const [activeCategory, setActiveCategory] = useState<string | null>(null);

    return (
        <nav className="relative bg-white border-b">
            <div className="container mx-auto px-4">
                <ul className="flex">
                    {categories.map((category) => (
                        <li
                            key={category.slug}
                            className="relative group"
                            onMouseEnter={() => setActiveCategory(category.slug)}
                            onMouseLeave={() => setActiveCategory(null)}
                        >
                            <Link
                                to={`/category/${category.slug}`}
                                className="flex items-center space-x-1 px-4 py-4 text-gray-700 hover:text-primary hover:bg-gray-50"
                            >
                                <span>{category.name}</span>
                                <FiChevronDown className="w-4 h-4" />
                            </Link>

                            {/* Mega Menu Dropdown */}
                            {activeCategory === category.slug && (
                                <div className="absolute left-0 w-screen max-w-7xl bg-white shadow-lg border rounded-b-lg z-50">
                                    <div className="grid grid-cols-3 gap-8 p-8">
                                        {category.subcategories.map((subcategory) => (
                                            <div key={subcategory.slug}>
                                                <h3 className="text-lg font-semibold text-gray-900 mb-4">
                                                    {subcategory.name}
                                                </h3>
                                                <ul className="space-y-2">
                                                    {subcategory.items.map((item) => (
                                                        <li key={item.slug}>
                                                            <Link
                                                                to={`/category/${category.slug}/${subcategory.slug}/${item.slug}`}
                                                                className="text-gray-600 hover:text-primary"
                                                            >
                                                                {item.name}
                                                            </Link>
                                                        </li>
                                                    ))}
                                                </ul>
                                            </div>
                                        ))}
                                    </div>
                                    <div className="bg-gray-50 p-4 rounded-b-lg">
                                        <Link
                                            to={`/category/${category.slug}`}
                                            className="text-primary hover:text-primary-dark font-medium"
                                        >
                                            View All {category.name} →
                                        </Link>
                                    </div>
                                </div>
                            )}
                        </li>
                    ))}
                </ul>
            </div>
        </nav>
    );
};

export default MegaMenu; 