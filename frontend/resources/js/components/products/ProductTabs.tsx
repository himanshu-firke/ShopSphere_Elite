import React, { useState } from 'react';
import { FiStar } from 'react-icons/fi';

interface Review {
    id: number;
    user: string;
    rating: number;
    date: string;
    comment: string;
}

interface Specification {
    label: string;
    value: string;
}

interface ProductTabsProps {
    description: string;
    specifications: Specification[];
    reviews: Review[];
    rating: {
        average: number;
        count: number;
        distribution: Record<number, number>;
    };
}

const ProductTabs: React.FC<ProductTabsProps> = ({
    description,
    specifications,
    reviews,
    rating
}) => {
    const [activeTab, setActiveTab] = useState('description');

    const tabs = [
        { id: 'description', label: 'Description' },
        { id: 'specifications', label: 'Specifications' },
        { id: 'reviews', label: `Reviews (${rating.count})` }
    ];

    const renderStars = (rating: number) => {
        return [...Array(5)].map((_, index) => (
            <FiStar
                key={index}
                className={`w-4 h-4 ${
                    index < rating ? 'fill-yellow-400 text-yellow-400' : 'text-gray-300'
                }`}
            />
        ));
    };

    return (
        <div className="mt-12">
            {/* Tabs */}
            <div className="border-b border-gray-200">
                <nav className="-mb-px flex space-x-8">
                    {tabs.map(tab => (
                        <button
                            key={tab.id}
                            onClick={() => setActiveTab(tab.id)}
                            className={`
                                whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm
                                ${
                                    activeTab === tab.id
                                        ? 'border-primary text-primary'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                }
                            `}
                        >
                            {tab.label}
                        </button>
                    ))}
                </nav>
            </div>

            {/* Tab Content */}
            <div className="py-6">
                {/* Description */}
                {activeTab === 'description' && (
                    <div className="prose max-w-none">
                        {description}
                    </div>
                )}

                {/* Specifications */}
                {activeTab === 'specifications' && (
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        {specifications.map((spec, index) => (
                            <div key={index} className="border-b pb-4 last:border-b-0">
                                <dt className="font-medium text-gray-900">{spec.label}</dt>
                                <dd className="mt-1 text-gray-500">{spec.value}</dd>
                            </div>
                        ))}
                    </div>
                )}

                {/* Reviews */}
                {activeTab === 'reviews' && (
                    <div>
                        {/* Rating Summary */}
                        <div className="flex items-center justify-between pb-6 border-b">
                            <div>
                                <div className="flex items-center">
                                    <span className="text-3xl font-bold text-gray-900">
                                        {rating.average.toFixed(1)}
                                    </span>
                                    <span className="text-gray-500 ml-2">out of 5</span>
                                </div>
                                <div className="flex items-center mt-1">
                                    {renderStars(rating.average)}
                                </div>
                                <div className="text-sm text-gray-500 mt-1">
                                    Based on {rating.count} reviews
                                </div>
                            </div>

                            {/* Rating Distribution */}
                            <div className="space-y-2">
                                {[5, 4, 3, 2, 1].map(stars => (
                                    <div key={stars} className="flex items-center text-sm">
                                        <span className="w-12">{stars} stars</span>
                                        <div className="w-48 h-2 mx-3 bg-gray-200 rounded-full">
                                            <div
                                                className="h-2 bg-yellow-400 rounded-full"
                                                style={{
                                                    width: `${(rating.distribution[stars] / rating.count) * 100}%`
                                                }}
                                            />
                                        </div>
                                        <span className="text-gray-500">
                                            {rating.distribution[stars]}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* Review List */}
                        <div className="mt-8 space-y-8">
                            {reviews.map(review => (
                                <div key={review.id} className="border-b pb-8 last:border-b-0">
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <div className="font-medium text-gray-900">
                                                {review.user}
                                            </div>
                                            <div className="flex items-center mt-1">
                                                {renderStars(review.rating)}
                                            </div>
                                        </div>
                                        <div className="text-sm text-gray-500">
                                            {review.date}
                                        </div>
                                    </div>
                                    <p className="mt-4 text-gray-500">{review.comment}</p>
                                </div>
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
};

export default ProductTabs; 