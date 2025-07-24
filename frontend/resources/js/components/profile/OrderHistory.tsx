import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { FiPackage, FiTruck, FiCheck, FiClock, FiDownload, FiStar } from 'react-icons/fi';

// Mock order data - replace with API call
const mockOrders = [
    {
        id: 'ORD123456',
        date: '2024-02-15',
        total: 34990,
        status: 'delivered',
        items: [
            {
                id: 1,
                name: 'Modern Office Chair',
                price: 6490,
                quantity: 1,
                image: '/images/products/chair-1.jpg',
                reviewed: true
            },
            {
                id: 2,
                name: 'Luxurious 3-Seater Sofa',
                price: 24990,
                quantity: 1,
                image: '/images/products/sofa-1.jpg',
                reviewed: false
            }
        ],
        tracking: {
            status: 'delivered',
            updates: [
                {
                    status: 'ordered',
                    date: '2024-02-15T10:30:00',
                    message: 'Order placed successfully'
                },
                {
                    status: 'confirmed',
                    date: '2024-02-15T11:15:00',
                    message: 'Order confirmed and payment received'
                },
                {
                    status: 'shipped',
                    date: '2024-02-16T14:20:00',
                    message: 'Order shipped via Express Delivery'
                },
                {
                    status: 'delivered',
                    date: '2024-02-18T16:45:00',
                    message: 'Order delivered successfully'
                }
            ]
        }
    },
    {
        id: 'ORD123457',
        date: '2024-02-10',
        total: 18990,
        status: 'shipped',
        items: [
            {
                id: 3,
                name: 'Queen Size Platform Bed',
                price: 18990,
                quantity: 1,
                image: '/images/products/bed-1.jpg',
                reviewed: false
            }
        ],
        tracking: {
            status: 'shipped',
            updates: [
                {
                    status: 'ordered',
                    date: '2024-02-10T15:20:00',
                    message: 'Order placed successfully'
                },
                {
                    status: 'confirmed',
                    date: '2024-02-10T16:05:00',
                    message: 'Order confirmed and payment received'
                },
                {
                    status: 'shipped',
                    date: '2024-02-12T11:30:00',
                    message: 'Order shipped via Standard Delivery'
                }
            ]
        }
    }
];

interface OrderItem {
    id: number;
    name: string;
    price: number;
    quantity: number;
    image: string;
    reviewed: boolean;
}

interface TrackingUpdate {
    status: 'ordered' | 'confirmed' | 'shipped' | 'delivered';
    date: string;
    message: string;
}

interface Order {
    id: string;
    date: string;
    total: number;
    status: 'ordered' | 'confirmed' | 'shipped' | 'delivered';
    items: OrderItem[];
    tracking: {
        status: 'ordered' | 'confirmed' | 'shipped' | 'delivered';
        updates: TrackingUpdate[];
    };
}

const OrderHistory: React.FC = () => {
    const [selectedOrder, setSelectedOrder] = useState<Order | null>(null);
    const [isReviewModalOpen, setIsReviewModalOpen] = useState(false);
    const [reviewProduct, setReviewProduct] = useState<OrderItem | null>(null);
    const [rating, setRating] = useState(0);
    const [reviewText, setReviewText] = useState('');

    const formatPrice = (amount: number) => {
        return new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            maximumFractionDigits: 0
        }).format(amount).replace('₹', '₹ ');
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('en-IN', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    };

    const formatDateTime = (dateString: string) => {
        return new Date(dateString).toLocaleString('en-IN', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'ordered':
                return 'bg-blue-100 text-blue-800';
            case 'confirmed':
                return 'bg-yellow-100 text-yellow-800';
            case 'shipped':
                return 'bg-purple-100 text-purple-800';
            case 'delivered':
                return 'bg-green-100 text-green-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const getStatusIcon = (status: string) => {
        switch (status) {
            case 'ordered':
                return <FiClock className="w-5 h-5" />;
            case 'confirmed':
                return <FiCheck className="w-5 h-5" />;
            case 'shipped':
                return <FiTruck className="w-5 h-5" />;
            case 'delivered':
                return <FiPackage className="w-5 h-5" />;
            default:
                return null;
        }
    };

    const handleReviewSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        // Implement review submission
        console.log('Review submitted:', { product: reviewProduct, rating, reviewText });
        setIsReviewModalOpen(false);
        setReviewProduct(null);
        setRating(0);
        setReviewText('');
    };

    return (
        <div className="space-y-6">
            {/* Orders List */}
            <div className="space-y-6">
                {mockOrders.map(order => (
                    <div key={order.id} className="bg-white rounded-lg shadow-sm border p-6">
                        {/* Order Header */}
                        <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-4">
                            <div>
                                <h2 className="text-lg font-semibold text-gray-900">
                                    Order #{order.id}
                                </h2>
                                <p className="text-sm text-gray-500">
                                    Placed on {formatDate(order.date)}
                                </p>
                            </div>
                            <div className="flex items-center gap-4">
                                <span className={`px-3 py-1 rounded-full text-sm font-medium ${getStatusColor(order.status)}`}>
                                    {order.status.charAt(0).toUpperCase() + order.status.slice(1)}
                                </span>
                                <button
                                    onClick={() => setSelectedOrder(order)}
                                    className="text-primary hover:text-primary-dark font-medium text-sm"
                                >
                                    Track Order
                                </button>
                            </div>
                        </div>

                        {/* Order Items */}
                        <div className="border-t pt-4">
                            <div className="space-y-4">
                                {order.items.map(item => (
                                    <div key={item.id} className="flex items-center gap-4">
                                        <img
                                            src={item.image}
                                            alt={item.name}
                                            className="w-20 h-20 object-cover rounded-lg"
                                        />
                                        <div className="flex-1">
                                            <Link
                                                to={`/product/${item.id}`}
                                                className="text-gray-900 font-medium hover:text-primary"
                                            >
                                                {item.name}
                                            </Link>
                                            <p className="text-gray-500">
                                                Quantity: {item.quantity}
                                            </p>
                                            <p className="text-gray-900 font-medium">
                                                {formatPrice(item.price)}
                                            </p>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            {order.status === 'delivered' && !item.reviewed && (
                                                <button
                                                    onClick={() => {
                                                        setReviewProduct(item);
                                                        setIsReviewModalOpen(true);
                                                    }}
                                                    className="text-primary hover:text-primary-dark"
                                                >
                                                    Write Review
                                                </button>
                                            )}
                                        </div>
                                    </div>
                                ))}
                            </div>
                            <div className="flex justify-between items-center mt-4 pt-4 border-t">
                                <div className="flex items-center gap-4">
                                    <button className="text-gray-600 hover:text-primary flex items-center gap-2">
                                        <FiDownload className="w-4 h-4" />
                                        <span>Invoice</span>
                                    </button>
                                </div>
                                <p className="text-lg font-bold text-gray-900">
                                    Total: {formatPrice(order.total)}
                                </p>
                            </div>
                        </div>
                    </div>
                ))}
            </div>

            {/* Tracking Modal */}
            {selectedOrder && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
                    <div className="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                        <div className="p-6">
                            <div className="flex justify-between items-center mb-6">
                                <h2 className="text-xl font-bold text-gray-900">
                                    Track Order #{selectedOrder.id}
                                </h2>
                                <button
                                    onClick={() => setSelectedOrder(null)}
                                    className="text-gray-400 hover:text-gray-500"
                                >
                                    <span className="sr-only">Close</span>
                                    <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div className="space-y-8">
                                {selectedOrder.tracking.updates.map((update, index) => (
                                    <div key={index} className="flex gap-4">
                                        <div className={`flex-shrink-0 w-10 h-10 rounded-full ${getStatusColor(update.status)} flex items-center justify-center`}>
                                            {getStatusIcon(update.status)}
                                        </div>
                                        <div>
                                            <h3 className="font-medium text-gray-900">
                                                {update.message}
                                            </h3>
                                            <p className="text-sm text-gray-500">
                                                {formatDateTime(update.date)}
                                            </p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            )}

            {/* Review Modal */}
            {isReviewModalOpen && reviewProduct && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
                    <div className="bg-white rounded-lg max-w-lg w-full">
                        <div className="p-6">
                            <div className="flex justify-between items-center mb-6">
                                <h2 className="text-xl font-bold text-gray-900">
                                    Write a Review
                                </h2>
                                <button
                                    onClick={() => {
                                        setIsReviewModalOpen(false);
                                        setReviewProduct(null);
                                    }}
                                    className="text-gray-400 hover:text-gray-500"
                                >
                                    <span className="sr-only">Close</span>
                                    <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div className="flex items-center gap-4 mb-6">
                                <img
                                    src={reviewProduct.image}
                                    alt={reviewProduct.name}
                                    className="w-20 h-20 object-cover rounded-lg"
                                />
                                <div>
                                    <h3 className="font-medium text-gray-900">
                                        {reviewProduct.name}
                                    </h3>
                                </div>
                            </div>

                            <form onSubmit={handleReviewSubmit}>
                                <div className="mb-6">
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Rating
                                    </label>
                                    <div className="flex items-center gap-2">
                                        {[1, 2, 3, 4, 5].map((star) => (
                                            <button
                                                key={star}
                                                type="button"
                                                onClick={() => setRating(star)}
                                                className={`text-2xl ${
                                                    star <= rating ? 'text-yellow-400' : 'text-gray-300'
                                                }`}
                                            >
                                                <FiStar
                                                    className={`w-8 h-8 ${
                                                        star <= rating ? 'fill-current' : ''
                                                    }`}
                                                />
                                            </button>
                                        ))}
                                    </div>
                                </div>

                                <div className="mb-6">
                                    <label
                                        htmlFor="review"
                                        className="block text-sm font-medium text-gray-700 mb-2"
                                    >
                                        Review
                                    </label>
                                    <textarea
                                        id="review"
                                        rows={4}
                                        value={reviewText}
                                        onChange={(e) => setReviewText(e.target.value)}
                                        className="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                                        placeholder="Write your review here..."
                                    />
                                </div>

                                <div className="flex justify-end gap-4">
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setIsReviewModalOpen(false);
                                            setReviewProduct(null);
                                        }}
                                        className="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        className="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-dark"
                                    >
                                        Submit Review
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default OrderHistory; 