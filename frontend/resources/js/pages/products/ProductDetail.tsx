import React, { useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import { Helmet } from 'react-helmet-async';
import ProductGallery from '../../components/products/ProductGallery';
import ProductInfo from '../../components/products/ProductInfo';
import ProductTabs from '../../components/products/ProductTabs';
import RelatedProducts from '../../components/products/RelatedProducts';
import { useCart } from '../../contexts/CartContext';

interface ProductImage {
    id: number;
    url: string;
    alt: string;
}

interface ProductVariant {
    id: number;
    name: string;
    price: number;
    originalPrice: number;
    inStock: boolean;
    maxQuantity: number;
}

interface Specification {
    label: string;
    value: string;
}

interface Review {
    id: number;
    user: string;
    rating: number;
    date: string;
    comment: string;
}

interface Rating {
    average: number;
    count: number;
    distribution: Record<number, number>;
}

interface Product {
    id: number;
    name: string;
    sku: string;
    description: string;
    images: ProductImage[];
    variants: ProductVariant[];
    specifications: Specification[];
    rating: Rating;
    reviews: Review[];
}

// Mock data for testing
const mockProducts: Record<number, Product> = {
    1: {
        id: 1,
        name: 'Modern Office Chair',
        sku: 'CHAIR-001',
        description: `Experience unparalleled comfort with our ergonomically designed office chair. Perfect for long work hours, this chair features:

        • Premium breathable mesh back
        • Adjustable lumbar support
        • Multi-position tilt mechanism
        • Height-adjustable armrests
        • 360-degree swivel base
        • Heavy-duty nylon casters`,
        images: [
            { id: 1, url: '/images/products/chair-1.jpg', alt: 'Office Chair Front View' },
            { id: 2, url: '/images/products/chair-2.jpg', alt: 'Office Chair Side View' },
            { id: 3, url: '/images/products/chair-3.jpg', alt: 'Office Chair Back View' }
        ],
        variants: [
            { id: 1, name: 'Black', price: 6490, originalPrice: 9999, inStock: true, maxQuantity: 8 },
            { id: 2, name: 'Grey', price: 6490, originalPrice: 9999, inStock: true, maxQuantity: 5 }
        ],
        specifications: [
            { label: 'Material', value: 'High-quality mesh and premium foam padding' },
            { label: 'Dimensions', value: '26"W x 26"D x 38-42"H' },
            { label: 'Weight Capacity', value: '120 kg' },
            { label: 'Assembly', value: 'Easy assembly required, tools included' },
            { label: 'Warranty', value: '1 year manufacturer warranty' }
        ],
        rating: {
            average: 4.5,
            count: 128,
            distribution: {
                5: 80,
                4: 30,
                3: 10,
                2: 5,
                1: 3
            }
        },
        reviews: [
            {
                id: 1,
                user: 'Rahul S.',
                rating: 5,
                date: '2 weeks ago',
                comment: 'Excellent chair for work from home setup. Very comfortable for long hours.'
            },
            {
                id: 2,
                user: 'Priya M.',
                rating: 4,
                date: '1 month ago',
                comment: 'Good quality and comfortable. Assembly was a bit tricky though.'
            }
        ]
    },
    2: {
        id: 2,
        name: 'Luxurious 3-Seater Sofa',
        sku: 'SOFA-001',
        description: `Transform your living room with our premium 3-seater sofa. Features include:

        • High-density foam cushioning
        • Premium fabric upholstery
        • Solid wood frame
        • No-sag spring suspension
        • Removable seat cushions
        • Contemporary design`,
        images: [
            { id: 1, url: '/images/products/sofa-1.jpg', alt: 'Sofa Front View' },
            { id: 2, url: '/images/products/sofa-2.jpg', alt: 'Sofa Side View' }
        ],
        variants: [
            { id: 1, name: 'Beige', price: 24990, originalPrice: 34999, inStock: true, maxQuantity: 3 },
            { id: 2, name: 'Grey', price: 24990, originalPrice: 34999, inStock: true, maxQuantity: 4 },
            { id: 3, name: 'Blue', price: 26990, originalPrice: 36999, inStock: false, maxQuantity: 0 }
        ],
        specifications: [
            { label: 'Material', value: 'High-density foam and premium fabric' },
            { label: 'Dimensions', value: '80"L x 30"W x 36"H' },
            { label: 'Weight Capacity', value: '250 kg' },
            { label: 'Assembly', value: 'Easy assembly required, tools included' },
            { label: 'Warranty', value: '1 year manufacturer warranty' }
        ],
        rating: {
            average: 4.8,
            count: 250,
            distribution: {
                5: 180,
                4: 50,
                3: 15,
                2: 5,
                1: 0
            }
        },
        reviews: [
            {
                id: 1,
                user: 'Amit T.',
                rating: 5,
                date: '3 weeks ago',
                comment: 'Absolutely love this sofa! It\'s super comfortable and looks great in our living room.'
            },
            {
                id: 2,
                user: 'Neha P.',
                rating: 4,
                date: '1 month ago',
                comment: 'Very good quality and looks modern. Assembly was a bit tricky but manageable.'
            }
        ]
    },
    3: {
        id: 3,
        name: 'Queen Size Platform Bed',
        sku: 'BED-001',
        description: `Sleep in style with our modern platform bed. Features include:

        • Solid wood construction
        • Modern platform design
        • Built-in headboard
        • Under-bed storage space
        • Easy assembly
        • Premium finish`,
        images: [
            { id: 1, url: '/images/products/bed-1.jpg', alt: 'Bed Front View' },
            { id: 2, url: '/images/products/bed-2.jpg', alt: 'Bed Side View' }
        ],
        variants: [
            { id: 1, name: 'Walnut', price: 18990, originalPrice: 27999, inStock: true, maxQuantity: 6 },
            { id: 2, name: 'Wenge', price: 18990, originalPrice: 27999, inStock: true, maxQuantity: 4 }
        ],
        specifications: [
            { label: 'Material', value: 'Solid wood and premium finish' },
            { label: 'Dimensions', value: '60"L x 54"W x 48"H' },
            { label: 'Weight Capacity', value: '150 kg' },
            { label: 'Assembly', value: 'Easy assembly required, tools included' },
            { label: 'Warranty', value: '1 year manufacturer warranty' }
        ],
        rating: {
            average: 4.6,
            count: 180,
            distribution: {
                5: 120,
                4: 40,
                3: 15,
                2: 5,
                1: 0
            }
        },
        reviews: [
            {
                id: 1,
                user: 'Sneha R.',
                rating: 5,
                date: '2 months ago',
                comment: 'This bed is absolutely stunning! It\'s comfortable and looks very elegant.'
            },
            {
                id: 2,
                user: 'Rajesh K.',
                rating: 4,
                date: '1 month ago',
                comment: 'Good quality bed. Assembly was a bit tricky but overall satisfied.'
            }
        ]
    }
};

// Mock data for related products
const relatedProductsData = [
    {
        id: 4,
        name: '6-Seater Dining Set',
        price: 32990,
        originalPrice: 44999,
        image: '/images/products/dining-1.jpg',
        discount: 27
    },
    {
        id: 5,
        name: 'Modern Sliding Wardrobe',
        price: 28990,
        originalPrice: 39999,
        image: '/images/products/wardrobe-1.jpg',
        discount: 28
    },
    {
        id: 6,
        name: 'Study Desk with Shelves',
        price: 8990,
        originalPrice: 12999,
        image: '/images/products/desk-1.jpg',
        discount: 31
    },
    {
        id: 7,
        name: 'Fabric Recliner Sofa',
        price: 21990,
        originalPrice: 29999,
        image: '/images/products/sofa-2.jpg',
        discount: 27
    },
    {
        id: 8,
        name: 'King Size Storage Bed',
        price: 34990,
        originalPrice: 49999,
        image: '/images/products/bed-2.jpg',
        discount: 30
    }
];

const ProductDetail: React.FC = () => {
    const { id } = useParams<{ id: string }>();
    const { addToCart } = useCart();
    const [isWishlisted, setIsWishlisted] = useState(false);
    const [selectedVariant, setSelectedVariant] = useState<ProductVariant | null>(null);

    // Get product data based on ID
    const product = mockProducts[Number(id)];

    // Handle invalid product ID
    if (!product) {
        return (
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div className="text-center">
                    <h1 className="text-2xl font-bold text-gray-900 mb-4">Product Not Found</h1>
                    <p className="text-gray-500 mb-8">The product you're looking for doesn't exist or has been removed.</p>
                    <Link
                        to="/products"
                        className="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                    >
                        Continue Shopping
                    </Link>
                </div>
            </div>
        );
    }

    const handleAddToCart = (variantId: number, quantity: number) => {
        const variant = product.variants.find(v => v.id === variantId);
        if (!variant) return;

        addToCart({
            id: product.id,
            name: product.name,
            price: variant.price,
            quantity: quantity,
            image: product.images[0].url,
            maxQuantity: variant.maxQuantity
        });

        setSelectedVariant(variant);
    };

    const handleToggleWishlist = () => {
        setIsWishlisted(!isWishlisted);
        // Implement wishlist functionality
    };

    return (
        <>
            <Helmet>
                <title>{product.name} - Kanha Furniture</title>
                <meta name="description" content={product.description.split('\n')[0]} />
            </Helmet>

            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                {/* Breadcrumbs */}
                <nav className="mb-8">
                    <ol className="flex items-center space-x-2 text-sm">
                        <li>
                            <Link to="/" className="text-gray-500 hover:text-primary">Home</Link>
                        </li>
                        <li>
                            <span className="text-gray-400 mx-2">/</span>
                            <Link to="/products" className="text-gray-500 hover:text-primary">Products</Link>
                        </li>
                        <li>
                            <span className="text-gray-400 mx-2">/</span>
                            <span className="text-gray-900">{product.name}</span>
                        </li>
                    </ol>
                </nav>

                {/* Product Overview */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
                    {/* Left Column - Gallery */}
                    <ProductGallery images={product.images} />

                    {/* Right Column - Product Info */}
                    <ProductInfo
                        name={product.name}
                        sku={product.sku}
                        variants={product.variants}
                        description={product.description}
                        onAddToCart={handleAddToCart}
                        onToggleWishlist={handleToggleWishlist}
                        isWishlisted={isWishlisted}
                    />
                </div>

                {/* Product Tabs */}
                <ProductTabs
                    description={product.description}
                    specifications={product.specifications}
                    reviews={product.reviews}
                    rating={product.rating}
                />

                {/* Related Products */}
                <RelatedProducts
                    products={relatedProductsData}
                    currentProductId={Number(id)}
                />
            </div>
        </>
    );
};

export default ProductDetail; 