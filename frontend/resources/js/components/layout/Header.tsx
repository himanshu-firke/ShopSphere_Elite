import React, { useState, useEffect, useRef } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { FiSearch, FiShoppingCart, FiUser, FiHeart, FiMenu, FiPhone, FiLogOut, FiSettings, FiPackage, FiX } from 'react-icons/fi';
import { useCart } from '../../contexts/CartContext';
import CartPopup from '../cart/CartPopup';

// Mock data - replace with actual auth state management
const mockUser = {
    isAuthenticated: true,
    firstName: 'John',
    lastName: 'Doe'
};

const Header: React.FC = () => {
    const navigate = useNavigate();
    const { state } = useCart();
    const searchRef = useRef<HTMLDivElement>(null);
    const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
    const [isSearchOpen, setIsSearchOpen] = useState(false);
    const [isProfileMenuOpen, setIsProfileMenuOpen] = useState(false);
    const [isCartOpen, setIsCartOpen] = useState(false);
    const [hoveredCategory, setHoveredCategory] = useState<string | null>(null);
    const [searchQuery, setSearchQuery] = useState('');
    const [showSuggestions, setShowSuggestions] = useState(false);
    const [searchSuggestions, setSearchSuggestions] = useState<Array<{
        id: number;
        name: string;
        category: string;
        image?: string;
    }>>([]);

    // Mock search suggestions - replace with API call
    const mockSuggestions = [
        { id: 1, name: 'Modern Office Chair', category: 'Office', image: '/images/products/chair-1.jpg' },
        { id: 2, name: 'Luxurious Sofa Set', category: 'Living Room', image: '/images/products/sofa-1.jpg' },
        { id: 3, name: 'Queen Size Bed', category: 'Bedroom', image: '/images/products/bed-1.jpg' },
    ];

    useEffect(() => {
        // Close suggestions on click outside
        const handleClickOutside = (event: MouseEvent) => {
            if (searchRef.current && !searchRef.current.contains(event.target as Node)) {
                setShowSuggestions(false);
            }
        };

        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    useEffect(() => {
        if (searchQuery.length >= 2) {
            // Simulate API call for suggestions
            const filtered = mockSuggestions.filter(item =>
                item.name.toLowerCase().includes(searchQuery.toLowerCase())
            );
            setSearchSuggestions(filtered);
            setShowSuggestions(true);
        } else {
            setShowSuggestions(false);
        }
    }, [searchQuery]);

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        if (searchQuery.trim()) {
            setShowSuggestions(false);
            setIsSearchOpen(false);
            navigate(`/search?q=${encodeURIComponent(searchQuery.trim())}`);
        }
    };

    const handleSuggestionClick = (suggestion: { id: number; name: string }) => {
        setSearchQuery('');
        setShowSuggestions(false);
        setIsSearchOpen(false);
        navigate(`/product/${suggestion.id}`);
    };

    const categories = {
        living: {
            title: 'Living Room',
            subcategories: ['Sofas', 'Coffee Tables', 'TV Units', 'Side Tables', 'Recliners']
        },
        bedroom: {
            title: 'Bedroom',
            subcategories: ['Beds', 'Wardrobes', 'Mattresses', 'Side Tables', 'Dressing Tables']
        },
        dining: {
            title: 'Dining Room',
            subcategories: ['Dining Sets', 'Dining Tables', 'Dining Chairs', 'Crockery Units']
        },
        office: {
            title: 'Office',
            subcategories: ['Office Chairs', 'Office Tables', 'Filing Cabinets', 'Bookcases']
        }
    };

    const handleLogout = () => {
        // Implement logout functionality
        console.log('Logging out...');
    };

    return (
        <header className="bg-white relative">
            {/* Top Bar */}
            <div className="bg-gray-100 py-2">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between items-center text-sm">
                        <div className="flex items-center space-x-4">
                            <span className="text-gray-600">Welcome to Kanha Furniture</span>
                            <a href="tel:1800-266-7070" className="text-gray-600 hover:text-primary flex items-center gap-1">
                                <FiPhone className="w-4 h-4" />
                                <span>1800-266-7070</span>
                            </a>
                        </div>
                        <div className="hidden md:flex items-center space-x-4">
                            <Link to="/track-order" className="text-gray-600 hover:text-primary">Track Order</Link>
                            <Link to="/find-store" className="text-gray-600 hover:text-primary">Find Store</Link>
                        </div>
                    </div>
                </div>
            </div>

            {/* Main Header */}
            <div className="border-b">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                    <div className="flex items-center justify-between">
                        {/* Logo */}
                        <Link to="/" className="flex-shrink-0">
                            <h1 className="text-2xl font-bold text-primary">Kanha Furniture</h1>
                        </Link>

                        {/* Search Bar */}
                        <div className="hidden md:flex flex-1 max-w-2xl mx-8" ref={searchRef}>
                            <div className="relative w-full">
                                <form onSubmit={handleSearch}>
                                    <input
                                        type="text"
                                        value={searchQuery}
                                        onChange={(e) => setSearchQuery(e.target.value)}
                                        onFocus={() => setShowSuggestions(true)}
                                        placeholder="Search for products..."
                                        className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                    />
                                    <button 
                                        type="submit"
                                        className="absolute right-3 top-1/2 transform -translate-y-1/2"
                                    >
                                        <FiSearch className="w-5 h-5 text-gray-500" />
                                    </button>
                                </form>

                                {/* Search Suggestions */}
                                {showSuggestions && searchSuggestions.length > 0 && (
                                    <div className="absolute top-full left-0 right-0 mt-1 bg-white border rounded-lg shadow-lg z-50">
                                        <ul className="py-2">
                                            {searchSuggestions.map((suggestion) => (
                                                <li key={suggestion.id}>
                                                    <button
                                                        onClick={() => handleSuggestionClick(suggestion)}
                                                        className="w-full px-4 py-2 hover:bg-gray-50 flex items-center gap-3"
                                                    >
                                                        {suggestion.image && (
                                                            <img
                                                                src={suggestion.image}
                                                                alt={suggestion.name}
                                                                className="w-10 h-10 object-cover rounded"
                                                            />
                                                        )}
                                                        <div className="text-left">
                                                            <div className="text-sm font-medium text-gray-900">
                                                                {suggestion.name}
                                                            </div>
                                                            <div className="text-xs text-gray-500">
                                                                in {suggestion.category}
                                                            </div>
                                                        </div>
                                                    </button>
                                                </li>
                                            ))}
                                            <li className="border-t mt-2 pt-2">
                                                <button
                                                    onClick={handleSearch}
                                                    className="w-full px-4 py-2 text-primary hover:bg-gray-50 text-sm"
                                                >
                                                    See all results for "{searchQuery}"
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Right Icons */}
                        <div className="flex items-center space-x-6">
                            <button className="md:hidden" onClick={() => setIsSearchOpen(!isSearchOpen)}>
                                <FiSearch className="w-6 h-6" />
                            </button>
                            <Link to="/wishlist" className="hidden md:flex items-center space-x-1">
                                <FiHeart className="w-6 h-6" />
                                <span className="text-sm">Wishlist</span>
                            </Link>
                            <button 
                                onClick={() => setIsCartOpen(true)}
                                className="flex items-center space-x-1"
                            >
                                <div className="relative">
                                    <FiShoppingCart className="w-6 h-6" />
                                    {state.items.length > 0 && (
                                        <span className="absolute -top-2 -right-2 bg-primary text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                                            {state.items.length}
                                        </span>
                                    )}
                                </div>
                                <span className="hidden md:inline text-sm">Cart</span>
                            </button>

                            {/* Profile Menu */}
                            {mockUser.isAuthenticated ? (
                                <div className="relative">
                                    <button
                                        onClick={() => setIsProfileMenuOpen(!isProfileMenuOpen)}
                                        className="flex items-center space-x-1 focus:outline-none"
                                    >
                                        <div className="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                                            <span className="text-sm font-medium text-gray-600">
                                                {mockUser.firstName[0]}{mockUser.lastName[0]}
                                            </span>
                                        </div>
                                        <span className="hidden md:inline text-sm">Account</span>
                                    </button>

                                    {/* Profile Dropdown */}
                                    {isProfileMenuOpen && (
                                        <div className="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                                            <div className="py-1" role="menu">
                                                <Link
                                                    to="/profile"
                                                    className="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                    role="menuitem"
                                                >
                                                    <FiUser className="mr-3 h-5 w-5" />
                                                    Profile
                                                </Link>
                                                <Link
                                                    to="/profile?tab=orders"
                                                    className="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                    role="menuitem"
                                                >
                                                    <FiPackage className="mr-3 h-5 w-5" />
                                                    Orders
                                                </Link>
                                                <Link
                                                    to="/profile?tab=settings"
                                                    className="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                    role="menuitem"
                                                >
                                                    <FiSettings className="mr-3 h-5 w-5" />
                                                    Settings
                                                </Link>
                                                <button
                                                    onClick={handleLogout}
                                                    className="flex w-full items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                    role="menuitem"
                                                >
                                                    <FiLogOut className="mr-3 h-5 w-5" />
                                                    Logout
                                                </button>
                                            </div>
                                        </div>
                                    )}
                                </div>
                            ) : (
                                <Link to="/login" className="hidden md:flex items-center space-x-1">
                                    <FiUser className="w-6 h-6" />
                                    <span className="text-sm">Login</span>
                                </Link>
                            )}
                            <button className="md:hidden" onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}>
                                <FiMenu className="w-6 h-6" />
                            </button>
                        </div>
                    </div>

                    {/* Mobile Search */}
                    {isSearchOpen && (
                        <div className="md:hidden mt-4" ref={searchRef}>
                            <form onSubmit={handleSearch} className="relative">
                                <input
                                    type="text"
                                    value={searchQuery}
                                    onChange={(e) => setSearchQuery(e.target.value)}
                                    placeholder="Search for products..."
                                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                />
                                <button 
                                    type="button"
                                    onClick={() => {
                                        setSearchQuery('');
                                        setIsSearchOpen(false);
                                    }}
                                    className="absolute right-12 top-1/2 transform -translate-y-1/2"
                                >
                                    <FiX className="w-5 h-5 text-gray-500" />
                                </button>
                                <button 
                                    type="submit"
                                    className="absolute right-3 top-1/2 transform -translate-y-1/2"
                                >
                                    <FiSearch className="w-5 h-5 text-gray-500" />
                                </button>
                            </form>

                            {/* Mobile Search Suggestions */}
                            {showSuggestions && searchSuggestions.length > 0 && (
                                <div className="absolute left-0 right-0 bg-white border-x border-b rounded-b-lg shadow-lg z-50 mt-1">
                                    <ul className="py-2">
                                        {searchSuggestions.map((suggestion) => (
                                            <li key={suggestion.id}>
                                                <button
                                                    onClick={() => handleSuggestionClick(suggestion)}
                                                    className="w-full px-4 py-2 hover:bg-gray-50 flex items-center gap-3"
                                                >
                                                    {suggestion.image && (
                                                        <img
                                                            src={suggestion.image}
                                                            alt={suggestion.name}
                                                            className="w-10 h-10 object-cover rounded"
                                                        />
                                                    )}
                                                    <div className="text-left">
                                                        <div className="text-sm font-medium text-gray-900">
                                                            {suggestion.name}
                                                        </div>
                                                        <div className="text-xs text-gray-500">
                                                            in {suggestion.category}
                                                        </div>
                                                    </div>
                                                </button>
                                            </li>
                                        ))}
                                        <li className="border-t mt-2 pt-2">
                                            <button
                                                onClick={handleSearch}
                                                className="w-full px-4 py-2 text-primary hover:bg-gray-50 text-sm"
                                            >
                                                See all results for "{searchQuery}"
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            )}
                        </div>
                    )}
                </div>
            </div>

            {/* Navigation Bar */}
            <nav className="hidden md:block bg-white border-b">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <ul className="flex">
                        {Object.entries(categories).map(([key, category]) => (
                            <li 
                                key={key}
                                className="relative group"
                                onMouseEnter={() => setHoveredCategory(key)}
                                onMouseLeave={() => setHoveredCategory(null)}
                            >
                                <Link 
                                    to={`/category/${key}`}
                                    className="block px-4 py-3 text-gray-700 hover:text-primary hover:bg-gray-50"
                                >
                                    {category.title}
                                </Link>

                                {/* Mega Menu Dropdown */}
                                {hoveredCategory === key && (
                                    <div className="absolute left-0 mt-0 w-64 bg-white border shadow-lg z-50">
                                        <div className="p-4">
                                            <h3 className="font-semibold text-gray-900 mb-2">{category.title}</h3>
                                            <ul className="space-y-2">
                                                {category.subcategories.map((sub, index) => (
                                                    <li key={index}>
                                                        <Link 
                                                            to={`/category/${key}/${sub.toLowerCase().replace(/\s+/g, '-')}`}
                                                            className="block text-gray-600 hover:text-primary"
                                                        >
                                                            {sub}
                                                        </Link>
                                                    </li>
                                                ))}
                                            </ul>
                                        </div>
                                    </div>
                                )}
                            </li>
                        ))}
                        <li>
                            <Link 
                                to="/products"
                                className="block px-4 py-3 text-gray-700 hover:text-primary hover:bg-gray-50"
                            >
                                All Products
                            </Link>
                        </li>
                    </ul>
                </div>
            </nav>

            {/* Mobile Menu */}
            {isMobileMenuOpen && (
                <div className="md:hidden bg-white border-t">
                    <div className="px-4 py-2">
                        <ul className="space-y-2">
                            {Object.entries(categories).map(([key, category]) => (
                                <li key={key}>
                                    <Link 
                                        to={`/category/${key}`}
                                        className="block py-2 text-gray-700 hover:text-primary"
                                    >
                                        {category.title}
                                    </Link>
                                </li>
                            ))}
                            <li>
                                <Link 
                                    to="/products"
                                    className="block py-2 text-gray-700 hover:text-primary"
                                >
                                    All Products
                                </Link>
                            </li>
                            {!mockUser.isAuthenticated && (
                                <li className="border-t pt-2">
                                    <Link to="/login" className="flex items-center space-x-2 text-gray-700 hover:text-primary">
                                        <FiUser className="w-5 h-5" />
                                        <span>Login / Register</span>
                                    </Link>
                                </li>
                            )}
                            <li>
                                <Link to="/wishlist" className="flex items-center space-x-2 text-gray-700 hover:text-primary">
                                    <FiHeart className="w-5 h-5" />
                                    <span>Wishlist</span>
                                </Link>
                            </li>
                        </ul>
                    </div>
                </div>
            )}

            {/* Cart Popup */}
            <CartPopup isOpen={isCartOpen} onClose={() => setIsCartOpen(false)} />
        </header>
    );
};

export default Header; 