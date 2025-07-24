import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import { Helmet } from 'react-helmet-async';
import { FiFilter, FiGrid, FiList } from 'react-icons/fi';
import ProductCard from '../../components/products/ProductCard';
import FilterSidebar from '../../components/products/FilterSidebar';

// Mock category data - replace with API call
const mockCategories = {
    living: {
        title: 'Living Room',
        description: 'Transform your living space with our stunning collection of sofas, coffee tables, TV units, and more.',
        banner: '/images/banners/living.jpg',
        subcategories: ['Sofas', 'Coffee Tables', 'TV Units', 'Side Tables', 'Recliners']
    },
    bedroom: {
        title: 'Bedroom',
        description: 'Create your perfect sanctuary with our range of beds, wardrobes, and bedroom furniture.',
        banner: '/images/banners/bedroom.jpg',
        subcategories: ['Beds', 'Wardrobes', 'Mattresses', 'Side Tables', 'Dressing Tables']
    },
    dining: {
        title: 'Dining Room',
        description: 'Gather in style with our collection of dining sets, tables, and chairs.',
        banner: '/images/banners/dining-set.jpg',
        subcategories: ['Dining Sets', 'Dining Tables', 'Dining Chairs', 'Crockery Units']
    },
    office: {
        title: 'Office',
        description: 'Create a productive workspace with our range of office furniture.',
        banner: '/images/banners/office.jpg',
        subcategories: ['Office Chairs', 'Office Tables', 'Filing Cabinets', 'Bookcases']
    }
};

// Mock products data - replace with API call
const mockProducts = [
    {
        id: 1,
        name: 'Modern Office Chair',
        price: 6490,
        originalPrice: 9999,
        image: '/images/products/chair-1.jpg',
        rating: 4.5,
        isNew: true,
        discount: 35,
        category: 'office'
    },
    {
        id: 2,
        name: 'Luxurious 3-Seater Sofa',
        price: 24990,
        originalPrice: 34999,
        image: '/images/products/sofa-1.jpg',
        rating: 4.8,
        isNew: true,
        discount: 28,
        category: 'living'
    },
    {
        id: 3,
        name: 'Queen Size Platform Bed',
        price: 18990,
        originalPrice: 27999,
        image: '/images/products/bed-1.jpg',
        rating: 4.6,
        discount: 32,
        category: 'bedroom'
    },
    {
        id: 4,
        name: '6-Seater Dining Set',
        price: 32990,
        originalPrice: 44999,
        image: '/images/products/dining-1.jpg',
        rating: 4.7,
        discount: 27,
        category: 'dining'
    }
];

const filterSections = [
    {
        id: 'subcategory',
        title: 'Type',
        type: 'checkbox' as const,
        options: [] // Will be populated based on category
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
    }
];

const sortOptions = [
    { id: 'featured', label: 'Featured' },
    { id: 'newest', label: 'Newest' },
    { id: 'price_low', label: 'Price: Low to High' },
    { id: 'price_high', label: 'Price: High to Low' },
    { id: 'rating', label: 'Highest Rated' }
];

const CategoryPage: React.FC = () => {
    const { categoryId } = useParams<{ categoryId: string }>();
    const category = categoryId ? mockCategories[categoryId as keyof typeof mockCategories] : null;

    const [isFilterOpen, setIsFilterOpen] = useState(false);
    const [selectedFilters, setSelectedFilters] = useState<Record<string, string[] | number[]>>({});
    const [sortBy, setSortBy] = useState('featured');
    const [viewMode, setViewMode] = useState<'grid' | 'list'>('grid');
    const [filteredProducts, setFilteredProducts] = useState(mockProducts);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        if (!category) return;

        // Simulate API call
        setIsLoading(true);
        setTimeout(() => {
            const products = mockProducts.filter(product => product.category === categoryId);
            setFilteredProducts(products);
            setIsLoading(false);
        }, 500);
    }, [categoryId, category]);

    if (!category) {
        return (
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div className="text-center">
                    <h1 className="text-2xl font-bold text-gray-900 mb-4">Category Not Found</h1>
                    <p className="text-gray-500">The category you're looking for doesn't exist.</p>
                </div>
            </div>
        );
    }

    // Update filter sections with category-specific subcategories
    const updatedFilterSections = [...filterSections];
    updatedFilterSections[0].options = category.subcategories.map(sub => ({
        id: sub.toLowerCase(),
        label: sub,
        count: Math.floor(Math.random() * 50) + 10 // Mock count
    }));

    const handleFilterChange = (sectionId: string, value: string[] | number[]) => {
        setSelectedFilters(prev => ({
            ...prev,
            [sectionId]: value
        }));
    };

    const handleAddToCart = (productId: number) => {
        console.log('Adding to cart:', productId);
        // Implement cart functionality
    };

    const handleToggleWishlist = (productId: number) => {
        console.log('Toggle wishlist:', productId);
        // Implement wishlist functionality
    };

    return (
        <>
            <Helmet>
                <title>{category.title} - Kanha Furniture</title>
                <meta name="description" content={category.description} />
            </Helmet>

            {/* Category Banner */}
            <div className="relative h-64 md:h-80 overflow-hidden">
                <img
                    src={category.banner}
                    alt={category.title}
                    className="w-full h-full object-cover"
                />
                <div className="absolute inset-0 bg-black bg-opacity-40 flex items-center">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
                        <h1 className="text-3xl md:text-4xl font-bold text-white mb-2">
                            {category.title}
                        </h1>
                        <p className="text-white text-opacity-90 max-w-xl">
                            {category.description}
                        </p>
                    </div>
                </div>
            </div>

            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                {/* Subcategory Navigation */}
                <div className="mb-8">
                    <div className="flex flex-wrap gap-2">
                        {category.subcategories.map((sub) => (
                            <Link
                                key={sub}
                                to={`/category/${categoryId}/${sub.toLowerCase().replace(/\s/g, '-')}`}
                                className="px-4 py-2 rounded-full border border-gray-300 text-sm hover:border-primary hover:text-primary transition-colors"
                            >
                                {sub}
                            </Link>
                        ))}
                    </div>
                </div>

                {/* Header */}
                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
                    <div>
                        <p className="text-gray-500">
                            {filteredProducts.length} products available
                        </p>
                    </div>

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
                            sections={updatedFilterSections}
                            selectedFilters={selectedFilters}
                            onFilterChange={handleFilterChange}
                        />
                    </div>

                    {/* Product Grid */}
                    <div className="flex-1">
                        {isLoading ? (
                            // Loading skeleton
                            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                                {[...Array(6)].map((_, index) => (
                                    <div key={index} className="animate-pulse">
                                        <div className="bg-gray-200 aspect-square rounded-lg mb-4" />
                                        <div className="h-4 bg-gray-200 rounded w-3/4 mb-2" />
                                        <div className="h-4 bg-gray-200 rounded w-1/2" />
                                    </div>
                                ))}
                            </div>
                        ) : filteredProducts.length === 0 ? (
                            // No results
                            <div className="text-center py-12">
                                <h2 className="text-xl font-semibold text-gray-900 mb-2">
                                    No products found
                                </h2>
                                <p className="text-gray-500">
                                    Try adjusting your filter criteria
                                </p>
                            </div>
                        ) : (
                            // Product grid
                            <div className={`grid gap-6 ${
                                viewMode === 'grid' 
                                    ? 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3' 
                                    : 'grid-cols-1'
                            }`}>
                                {filteredProducts.map(product => (
                                    <ProductCard
                                        key={product.id}
                                        {...product}
                                        onAddToCart={handleAddToCart}
                                        onToggleWishlist={handleToggleWishlist}
                                    />
                                ))}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
};

export default CategoryPage; 