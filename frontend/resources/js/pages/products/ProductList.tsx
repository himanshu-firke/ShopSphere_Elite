import React, { useState } from 'react';
import { FiGrid, FiList, FiFilter } from 'react-icons/fi';
import ProductCard from '../../components/products/ProductCard';
import FilterSidebar from '../../components/products/FilterSidebar';
import { useCart } from '../../contexts/CartContext';

// Mock products data
const mockProducts = [
    {
        id: 1,
        name: 'Modern Office Chair',
        price: 6490,
        originalPrice: 9999,
        image: '/images/products/chair-1.jpg',
        rating: 4.5,
        isNew: true,
        discount: 35
    },
    {
        id: 2,
        name: 'Luxurious 3-Seater Sofa',
        price: 24990,
        originalPrice: 34999,
        image: '/images/products/sofa-1.jpg',
        rating: 4.8,
        isNew: true,
        discount: 28
    },
    {
        id: 3,
        name: 'Queen Size Platform Bed',
        price: 18990,
        originalPrice: 27999,
        image: '/images/products/bed-1.jpg',
        rating: 4.6,
        discount: 32
    },
    {
        id: 4,
        name: '6-Seater Dining Set',
        price: 32990,
        originalPrice: 44999,
        image: '/images/products/dining-1.jpg',
        rating: 4.7,
        discount: 27
    },
    {
        id: 5,
        name: 'Modern Sliding Wardrobe',
        price: 28990,
        originalPrice: 39999,
        image: '/images/products/wardrobe-1.jpg',
        rating: 4.4,
        isNew: true,
        discount: 28
    },
    {
        id: 6,
        name: 'Study Desk with Shelves',
        price: 8990,
        originalPrice: 12999,
        image: '/images/products/desk-1.jpg',
        rating: 4.3,
        discount: 31
    },
    {
        id: 7,
        name: 'Fabric Recliner Sofa',
        price: 21990,
        originalPrice: 29999,
        image: '/images/products/sofa-2.jpg',
        rating: 4.6,
        discount: 27
    },
    {
        id: 8,
        name: 'King Size Storage Bed',
        price: 34990,
        originalPrice: 49999,
        image: '/images/products/bed-2.jpg',
        rating: 4.8,
        isNew: true,
        discount: 30
    },
    {
        id: 9,
        name: '4-Seater Glass Dining Set',
        price: 26990,
        originalPrice: 37999,
        image: '/images/products/dining-2.jpg',
        rating: 4.5,
        discount: 29
    },
    {
        id: 10,
        name: '4-Door Wardrobe',
        price: 31990,
        originalPrice: 44999,
        image: '/images/products/wardrobe-2.jpg',
        rating: 4.7,
        discount: 29
    },
    {
        id: 11,
        name: 'Computer Desk with Drawers',
        price: 9990,
        originalPrice: 14999,
        image: '/images/products/desk-2.jpg',
        rating: 4.4,
        discount: 33
    }
];

const filterSections = [
    {
        id: 'category',
        title: 'Category',
        type: 'checkbox' as const,
        options: [
            { id: 'office', label: 'Office Furniture', count: 120 },
            { id: 'living', label: 'Living Room', count: 85 },
            { id: 'bedroom', label: 'Bedroom', count: 55 },
            { id: 'dining', label: 'Dining Room', count: 40 },
        ]
    },
    {
        id: 'price',
        title: 'Price Range',
        type: 'range' as const,
        range: {
            min: 0,
            max: 50000,
            step: 1000
        }
    },
    {
        id: 'material',
        title: 'Material',
        type: 'checkbox' as const,
        options: [
            { id: 'wood', label: 'Wood', count: 250 },
            { id: 'metal', label: 'Metal', count: 120 },
            { id: 'fabric', label: 'Fabric', count: 95 },
            { id: 'leather', label: 'Leather', count: 45 },
        ]
    },
    {
        id: 'brand',
        title: 'Brand',
        type: 'checkbox' as const,
        options: [
            { id: 'nilkamal', label: 'Nilkamal', count: 180 },
            { id: 'supreme', label: 'Supreme', count: 120 },
            { id: 'godrej', label: 'Godrej', count: 90 },
        ]
    }
];

const sortOptions = [
    { id: 'featured', label: 'Featured' },
    { id: 'newest', label: 'Newest' },
    { id: 'price_low', label: 'Price: Low to High' },
    { id: 'price_high', label: 'Price: High to Low' },
    { id: 'rating', label: 'Highest Rated' }
];

const ProductList: React.FC = () => {
    const { addToCart } = useCart();
    const [isFilterOpen, setIsFilterOpen] = useState(false);
    const [selectedFilters, setSelectedFilters] = useState<Record<string, string[] | number[]>>({});
    const [sortBy, setSortBy] = useState('featured');
    const [viewMode, setViewMode] = useState<'grid' | 'list'>('grid');
    const [wishlistedItems, setWishlistedItems] = useState<number[]>([]);

    const handleFilterChange = (sectionId: string, value: string[] | number[]) => {
        setSelectedFilters(prev => ({
            ...prev,
            [sectionId]: value
        }));
    };

    const handleAddToCart = (productId: number) => {
        const product = mockProducts.find(p => p.id === productId);
        if (product) {
            addToCart({
                id: product.id,
                name: product.name,
                price: product.price,
                quantity: 1,
                image: product.image,
                maxQuantity: 10 // Default max quantity
            });
            console.log('Added to cart:', product.name);
        }
    };

    const handleToggleWishlist = (productId: number) => {
        setWishlistedItems(prev =>
            prev.includes(productId)
                ? prev.filter(id => id !== productId)
                : [...prev, productId]
        );
    };

    return (
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            {/* Header */}
            <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
                <h1 className="text-2xl font-bold text-gray-900">All Products</h1>

                <div className="flex items-center gap-4 w-full sm:w-auto">
                    {/* Mobile Filter Button */}
                    <button
                        className="sm:hidden flex items-center gap-2 px-4 py-2 border rounded-md"
                        onClick={() => setIsFilterOpen(!isFilterOpen)}
                    >
                        <FiFilter className="w-4 h-4" />
                        <span>Filters</span>
                    </button>

                    {/* Sort Dropdown */}
                    <select
                        value={sortBy}
                        onChange={(e) => setSortBy(e.target.value)}
                        className="flex-1 sm:flex-none border rounded-md py-2 px-4 bg-white"
                    >
                        {sortOptions.map(option => (
                            <option key={option.id} value={option.id}>
                                {option.label}
                            </option>
                        ))}
                    </select>

                    {/* View Mode Toggle */}
                    <div className="hidden sm:flex items-center gap-2 border rounded-md">
                        <button
                            className={`p-2 ${viewMode === 'grid' ? 'bg-gray-100' : ''}`}
                            onClick={() => setViewMode('grid')}
                        >
                            <FiGrid className="w-5 h-5" />
                        </button>
                        <button
                            className={`p-2 ${viewMode === 'list' ? 'bg-gray-100' : ''}`}
                            onClick={() => setViewMode('list')}
                        >
                            <FiList className="w-5 h-5" />
                        </button>
                    </div>
                </div>
            </div>

            <div className="flex gap-8">
                {/* Filter Sidebar */}
                <div className={`
                    fixed sm:relative inset-y-0 left-0 z-40 w-64 bg-white transform 
                    ${isFilterOpen ? 'translate-x-0' : '-translate-x-full'} 
                    sm:translate-x-0 transition-transform duration-300 ease-in-out
                `}>
                    <FilterSidebar
                        sections={filterSections}
                        selectedFilters={selectedFilters}
                        onFilterChange={handleFilterChange}
                    />
                </div>

                {/* Product Grid */}
                <div className="flex-1">
                    <div className={`
                        grid gap-6
                        ${viewMode === 'grid' ? 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3' : 'grid-cols-1'}
                    `}>
                        {mockProducts.map(product => (
                            <ProductCard
                                key={product.id}
                                {...product}
                                onAddToCart={handleAddToCart}
                                onToggleWishlist={handleToggleWishlist}
                                isWishlisted={wishlistedItems.includes(product.id)}
                            />
                        ))}
                    </div>

                    {/* Pagination */}
                    <div className="mt-8 flex justify-center">
                        <nav className="flex items-center gap-2">
                            <button className="px-3 py-1 border rounded-md hover:bg-gray-50">
                                Previous
                            </button>
                            <button className="px-3 py-1 bg-primary text-white rounded-md">
                                1
                            </button>
                            <button className="px-3 py-1 border rounded-md hover:bg-gray-50">
                                2
                            </button>
                            <button className="px-3 py-1 border rounded-md hover:bg-gray-50">
                                3
                            </button>
                            <button className="px-3 py-1 border rounded-md hover:bg-gray-50">
                                Next
                            </button>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default ProductList; 