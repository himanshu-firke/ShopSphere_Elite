import React, { useState, useEffect } from 'react';
import { useSearchParams } from 'react-router-dom';
import { Helmet } from 'react-helmet-async';
import { FiFilter, FiGrid, FiList } from 'react-icons/fi';
import ProductCard from '../../components/products/ProductCard';
import FilterSidebar from '../../components/products/FilterSidebar';

// Mock data - replace with actual API call
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
    // Add more mock products...
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
    }
];

const sortOptions = [
    { id: 'relevance', label: 'Best Match' },
    { id: 'newest', label: 'Newest' },
    { id: 'price_low', label: 'Price: Low to High' },
    { id: 'price_high', label: 'Price: High to Low' },
    { id: 'rating', label: 'Highest Rated' }
];

const SearchResults: React.FC = () => {
    const [searchParams] = useSearchParams();
    const query = searchParams.get('q') || '';
    
    const [isFilterOpen, setIsFilterOpen] = useState(false);
    const [selectedFilters, setSelectedFilters] = useState<Record<string, string[] | number[]>>({});
    const [sortBy, setSortBy] = useState('relevance');
    const [viewMode, setViewMode] = useState<'grid' | 'list'>('grid');
    const [filteredProducts, setFilteredProducts] = useState(mockProducts);
    const [isLoading, setIsLoading] = useState(false);

    useEffect(() => {
        // Simulate API call with loading state
        setIsLoading(true);
        setTimeout(() => {
            // Filter products based on search query (case-insensitive)
            const filtered = mockProducts.filter(product =>
                product.name.toLowerCase().includes(query.toLowerCase())
            );
            setFilteredProducts(filtered);
            setIsLoading(false);
        }, 500);
    }, [query]);

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
                <title>Search Results for "{query}" - Kanha Furniture</title>
                <meta name="description" content={`Search results for "${query}" at Kanha Furniture. Find the best furniture for your home.`} />
            </Helmet>

            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                {/* Header */}
                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">
                            Search Results for "{query}"
                        </h1>
                        <p className="text-gray-500 mt-1">
                            {filteredProducts.length} results found
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
                            sections={filterSections}
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
                                    No results found
                                </h2>
                                <p className="text-gray-500">
                                    Try adjusting your search or filter criteria
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

export default SearchResults; 